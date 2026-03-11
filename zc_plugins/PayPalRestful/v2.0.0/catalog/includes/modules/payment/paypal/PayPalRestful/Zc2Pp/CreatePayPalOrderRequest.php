<?php
/**
 * A class to 'convert' a Zen Cart order to a PayPal order-creation request payload
 * for the PayPalRestful (paypalr) Payment Module
 *
 * @copyright Copyright 2023-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Last updated: v1.3.1
 */
namespace PayPalRestful\Zc2Pp;

use PayPalRestful\Common\ErrorInfo;
use PayPalRestful\Common\Helpers;
use PayPalRestful\Common\Logger;
use PayPalRestful\Zc2Pp\Address;
use PayPalRestful\Zc2Pp\Amount;
use PayPalRestful\Zc2Pp\Name;

// -----
// Create a PayPal order request, as documented here: https://developer.paypal.com/docs/api/orders/v2/#orders_create
//
class CreatePayPalOrderRequest extends ErrorInfo
{
    /**
     * Debug interface, shared with the PayPalRestfulApi class.
     */
    protected $log; //- An instance of the Logger class, logs debug tracing information.

    /**
     * Local "Amount" class; it's got the to-be-used currency for the PayPal order
     * stashed in a static variable!
     */
    protected $amount;

    /**
     * The currency-code in which the PayPal order is to be 'built'.
     */
    protected $paypalCurrencyCode;

    /**
     * The request to be submitted to a v2/orders/create PayPal endpoint.
     */
    protected $request;

    /**
     * The items' pricing 'breakdown' elements, gathered by getItems
     * and subsequently used by getOrderTotals.
     */
    protected $itemBreakdown = [
        'item_onetime_charges' => 0.0,
        'item_total' => 0,
        'item_tax_total' => 0,
        'all_products_virtual' => true,
        'breakdown_mismatch' => [],  //- NOTE, only has values if a breakdown-mismatch was found!
    ];

    /**
     * The overall discount applied to the order (both shipping and items).
     * Set by getOrderAmountAndBreakdown and used by buildLevel2Level3Data for
     * the level-3 data.
     */
    protected $overallDiscount = 0.0;

    // -----
    // Constructor.  "Converts" a Zen Cart order into an PayPal /orders/create object.
    //
    // Note: The $order_info and $ot_diffs arrays are created by the payment-module's auto.paypalrestful.php observer.
    //
    public function __construct(string $ppr_type, \order $order, array $cc_info, array $order_info, array $ot_diffs)
    {
        // Instantiate any ErrorInfo dependencies
        parent::__construct();

        $this->log = new Logger();

        global $currencies;
        $this->amount = new Amount($order->info['currency']);
        $this->paypalCurrencyCode = $this->amount->getDefaultCurrencyCode();

        $this->log->write(
            "CreatePayPalOrderRequest::__construct($ppr_type, ...) starts ...\n" .
            "Order's info: " . Logger::logJSON($order->info) . "\n" .
            'order_info: ' . Logger::logJSON($order_info) . "\n" .
            'ot_diffs: ' . Logger::logJSON($ot_diffs)
        );

        if (MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE === 'Final Sale' || ($ppr_type !== 'card' && MODULE_PAYMENT_PAYPALR_TRANSACTION_MODE === 'Auth Only (Card-Only)')) {
            $intent = 'CAPTURE';
        } else {
            $intent = 'AUTHORIZE';
        }
        $this->request = [
            'intent' => $intent,
            'purchase_units' => [
                [
                    'invoice_id' =>
                        'PPR-' .
                        date('YmdHis') . '-' .
                        $_SESSION['customer_id'] . '-' .
                        Helpers::getCustomerNameSuffix() . '-' .
                        bin2hex(random_bytes(4)),
                ],
            ],
        ];
        $this->request['purchase_units'][0]['items'] = $this->getItems($order->products);
        $this->request['purchase_units'][0]['amount'] = $this->getOrderAmountAndBreakdown($order, $order_info, $ot_diffs);

        // -----
        // Set soft-descriptor override if defined. Else it will use the branding details already in the PayPal account.
        if (defined('MODULE_PAYMENT_PAYPALR_SOFT_DESCRIPTOR') && MODULE_PAYMENT_PAYPALR_SOFT_DESCRIPTOR !== '') {
            $this->request['purchase_units'][0]['soft_descriptor'] = substr(MODULE_PAYMENT_PAYPALR_SOFT_DESCRIPTOR, 0, 22);
        }

        // -----
        // The 'shipping' element is included *only if* the order's got one or more
        // physical items to be shipped.
        //
        if ($this->itemBreakdown['all_products_virtual'] === false) {
            $shipping_address_info = $this->getShippingAddressInfo($order);
            if (count($shipping_address_info) !== 0) {
                $this->request['purchase_units'][0]['shipping'] = $this->getShippingAddressInfo($order);
            }
        }

        if ($this->countItems() === 0) {
            unset($this->request['purchase_units'][0]['items']);
        }

        // -----
        // Validate that the order's amount-breakdown actually adds up (it might not for
        // various tax situations and/or if a coupon is present). If the breakdown doesn't
        // match the order's total, the order will be submitted *without the breakdown* which
        // (unfortunately) could result in the order not being protected by PayPal.
        //
        $this->validateOrderAmounts();

        // -----
        // If this is a request to pay via PayPal Wallet, add the 'paypal' payment source
        // to the order-creation request;
        //
        if ($ppr_type !== 'card') {
            $this->request['payment_source']['paypal'] = $this->buildPayPalPaymentSource($order);
        // -----
        // If this is a request to pay for via a credit card, add the 'card' payment source
        // to the order-creation request.
        //
        } else {
            $this->request['payment_source']['card'] = $this->buildCardPaymentSource($order, $cc_info);

            // -----
            // See if there's information that could be added as level 2/3 data
            // for the card purchase, adding that information if so.
            //
            $supplementary_data = $this->buildLevel2Level3Data($this->request['purchase_units'][0]);
            if (count($supplementary_data) !== 0) {
                $this->request['purchase_units'][0]['supplementary_data'] = $supplementary_data;
            }
        }

        $this->log->write("\nCreatePayPalOrderRequest::__construct($ppr_type, ...) finished, request:\n" . Logger::logJSON($this->request, true, true));
    }
    protected function validateOrderAmounts()
    {
        $purchase_amount = $this->request['purchase_units'][0]['amount'];
        $summed_amount = 0;
        foreach ($purchase_amount['breakdown'] as $name => $amount) {
            if ($name === 'discount') {
                $summed_amount -= $amount['value'];
            } else {
                $summed_amount += $amount['value'];
            }
        }
        if (number_format((float)$summed_amount, $this->amount->getCurrencyDecimals(), '.', '') !== $purchase_amount['value']) {
            $this->log->write("\n***--> CreatePayPalOrderRequest, amount mismatch ($summed_amount vs. {$purchase_amount['value']}). No items or cost breakdown included in the submission to PayPal. Error amount:\n" . Logger::logJSON($purchase_amount));
            $this->itemBreakdown['breakdown_mismatch'] = $purchase_amount;
            unset(
                $this->request['purchase_units'][0]['amount']['breakdown'],
                $this->request['purchase_units'][0]['items']
            );
        }
    }

    // -----
    // Retrieve the generated request.
    //
    public function get()
    {
        return $this->request;
    }

    // -----
    // Retrieve the breakdown-mismatch array. If not empty, indicates that there
    // was a mismatch between the calculated order breakdown and the order's total.
    //
    public function getBreakdownMismatch(): array
    {
        return $this->itemBreakdown['breakdown_mismatch'];
    }

    // -----
    // 'Convert' the order's products into the PayPal 'items' array.
    //
    protected function getItems(array $order_products): array
    {
        $item_errors = false;
        $items = [];
        foreach ($order_products as $next_product) {
            // -----
            // Grab the product's 'id' and 'name', for use in any message logs that might arise.
            //
            $products_id = $next_product['id'] ?? 0;
            $name = $next_product['products_name'] ?? $next_product['name'] ?? '';
            $sku = $next_product['products_model'] ?? $next_product['model'] ?? '';

            // -----
            // If the product has attributes, append only the attribute's value to
            // the product's name as there's a 127-character limit for an item's
            // name in the PayPal API.
            //
            if (!empty($next_product['attributes'])) {
                $attribute_values = [];
                foreach ($next_product['attributes'] as $next_attribute) {
                    $attribute_values[] = $next_attribute['value'];
                }
                $name .= ': ' . implode('|', $attribute_values);
            }

            // -----
            // PayPal supports *only* integer-quantities in the order's item list,
            // so if any quantity is not an integer value, the items' array
            // can't be included in the PayPal order request.  Noting that this
            // will be an issue for sites that sell fabric or cheeses, for instance.
            //
            $quantity = (string)$next_product['qty'];
            if (ctype_digit($quantity) === false) {
                $item_errors = true;
                $this->log->write("!**-> getItems: Product #$products_id ($name) has a non-integer quantity ($quantity); item details cannot be included.");
                continue;
            }

            // -----
            // For the item list to be included, all items must have names that are at least
            // 1-character long.
            //
            if ($name === '') {
                $item_errors = true;
                $this->log->write("!**-> getItems: Product #$products_id ($name) is empty; item details cannot be included.");
                continue;
            }

            // -----
            // Determine the product's tax-rate as a percentage value.
            //
            $tax_rate = $next_product['tax'] / 100;
            $products_price = $this->getRateConvertedValue($next_product['final_price']);
            $product_is_physical = $this->isProductPhysical($next_product);
            $item = [
                'name' => substr($name, 0, 127),
                'sku' => substr($sku, 0, 127),   
                'quantity' => $quantity,
                'category' => ($product_is_physical === true) ? 'PHYSICAL_GOODS' : 'DIGITAL_GOODS',
                'unit_amount' => $this->amount->setValue($products_price),
                'tax' => $this->amount->setValue($products_price * $tax_rate),
            ];

            // -----
            // If the product is physical, indicate as such for use by the getShipping method.
            //
            if ($product_is_physical === true) {
                $this->itemBreakdown['all_products_virtual'] = false;
            }

            // -----
            // Unfortunately, PayPal has no concept of one-time charges for a product.  They'll be
            // summed up and will be noted in the PayPal order as part of the 'handling fee'.
            //
            $this->itemBreakdown['item_onetime_charges'] += $next_product['onetime_charges'] * (1.00 + $tax_rate);

            // -----
            // Add the current item to the items' array.
            //
            $items[] = $item;
        }

        return ($item_errors === true) ? [] : $items;
    }

    // -----
    // Determine if the specified product is a "physical" one, i.e. it's
    //
    // 1. Not marked as being virtual.
    // 2. Not a "Gift Certificate".
    // 3. Got no selected download attribute(s).
    //
    protected function isProductPhysical(array $product): bool
    {
        if ($product['products_virtual'] === 1 || strpos($product['model'], 'GIFT') === 0) {
            return false;
        }

        if (empty($product['attributes'])) {
            return true;
        }

        $attributes_where = [];
        foreach ($product['attributes'] as $next_att) {
            $attributes_where[] = '(options_id = ' . (int)$next_att['option_id'] . ' AND options_values_id = ' . (int)$next_att['value_id'] . ')';
        }

        global $db;
        $downloads = $db->Execute(
            "SELECT products_attributes_id
               FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . "
              WHERE products_attributes_id IN (
                    SELECT products_attributes_id
                      FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                     WHERE products_id = " . (int)$product['id'] . "
                       AND (" . implode(' OR ', $attributes_where) . "
                       )
                    )"
        );
        return $downloads->EOF;
    }

    // -----
    // For seller-protection to be activated, the order's amounts need to be
    // broken into various elements.
    //
    protected function getOrderAmountAndBreakdown(\order $order, array $order_info, array $ot_diffs): array
    {
        $amount = $this->setRateConvertedValue($order_info['total']);
        if ($this->countItems() === 0) {
            return $amount;
        }

        // -----
        // Record the order's product/item overall price and tax as well
        // as the shipping cost (it'll include any tax associated with the
        // shipping).
        //
        $item_total = 0;
        $item_tax_total = 0;
        foreach ($this->request['purchase_units'][0]['items'] as $next_item) {
            $item_total += $next_item['quantity'] * $next_item['unit_amount']['value'];
            $item_tax_total += $next_item['quantity'] * $next_item['tax']['value'];
        }
        $shipping_total = (float)($order->info['shipping_cost'] + $order_info['shipping_tax']);
        $breakdown = [
            'item_total' => $this->amount->setValue($item_total),
            'shipping' => $this->setRateConvertedValue($shipping_total),
            'tax_total' => $this->amount->setValue($item_tax_total),
        ];

        // -----
        // Calculate any handling fees (including products' onetime-charges) and,
        // if non-zero, include that value in the breakdown.
        //
        $handling_total = $this->calculateHandling($ot_diffs);
        if ($handling_total > 0) {
            $breakdown['handling'] = $this->setRateConvertedValue($handling_total);
        }

        // -----
        // Calculate any insurance associated with the order and, if non-zero, include
        // that value in the breakdown.
        //
        $insurance_total = $this->calculateInsurance($ot_diffs);
        if ($insurance_total > 0) {
            $breakdown['insurance'] = $this->setRateConvertedValue($insurance_total);
        }

        // -----
        // Calculate any shipping-discount associated with the order and, if non-zero, include
        // that value in the breakdown.
        //
        $shipping_discount_total = ($order_info['free_shipping_coupon'] === false) ? 0.0 : $shipping_total;
        if ($shipping_discount_total > 0) {
            $breakdown['shipping_discount'] = $this->setRateConvertedValue($shipping_discount_total);
        }

        // -----
        // Calculate any discounts (e.g. coupons, gift-vouchers or group-pricing) associated
        // with the order and, if non-zero, include that value in the breakdown.
        //
        $discount_total = $this->calculateDiscount($ot_diffs) - $shipping_discount_total;
        if ($discount_total > 0) {
            $breakdown['discount'] = $this->setRateConvertedValue($discount_total);
        }
        $amount['breakdown'] = $breakdown;

        // -----
        // Sum up the overall order-discount for possible use in providing the Level 2/3
        // information for credit-card payments.
        //
        $this->overallDiscount = (float)($shipping_discount_total + $discount_total);

        return $amount;
    }

    // -----
    // Separate 'calculators' for the 'handling', 'insurance' and 'discount' amounts
    // for the order.
    //
    protected function calculateHandling(array $ot_diffs): float
    {
        return $this->itemBreakdown['item_onetime_charges'] + $this->calculateOrderElementValue(MODULE_PAYMENT_PAYPALR_HANDLING_OT . ', ot_loworderfee', $ot_diffs);
    }
    protected function calculateInsurance(array $ot_diffs): float
    {
        return $this->calculateOrderElementValue(MODULE_PAYMENT_PAYPALR_INSURANCE_OT, $ot_diffs);
    }
    protected function calculateDiscount(array $ot_diffs): float
    {
        return abs($this->calculateOrderElementValue(MODULE_PAYMENT_PAYPALR_DISCOUNT_OT . ', ot_coupon, ot_gv, ot_group_pricing', $ot_diffs));
    }
    protected function calculateOrderElementValue(string $ot_class_names, array $ot_diffs): float
    {
        $total_classes = explode(',', str_replace(' ', '', $ot_class_names));

        $value = 0.0;
        foreach (array_keys($ot_diffs) as $next_ot_class) {
            if (!in_array($next_ot_class, $total_classes)) {
                continue;
            }
            $diff = $ot_diffs[$next_ot_class]['diff'];
            $value += $diff['total'];
        }
        return $value;
    }

    protected function setRateConvertedValue($value): array
    {
        return $this->amount->setValue($this->getRateConvertedValue($value));
    }

    protected function getRateConvertedValue($value): string
    {
        global $currencies;

        return number_format((float)$currencies->rateAdjusted($value, true, $this->paypalCurrencyCode), 2, '.', '');
    }

    // -----
    // Gets the shipping element of a to-be-created order.  Note that this method
    // is not called (!) when the order's virtual!
    //
    protected function getShippingAddressInfo(\order $order): array
    {
        global $order;

        return [
            'type' => 'SHIPPING',
            'name' => Name::get($order->delivery),
            'address' => Address::get($order->delivery),
        ];
    }

    protected function countItems(): int
    {
        return count($this->request['purchase_units'][0]['items']);
    }

    protected function buildPayPalPaymentSource(\order $order): array
    {
        $payment_source = [
            'name' => [
                'given_name' => $order->billing['firstname'],
                'surname' => $order->billing['lastname'],
            ],
            'email_address' => $order->customer['email_address'],
            'address' => Address::get($order->billing),
        ];
        return $payment_source;
    }

    protected function buildCardPaymentSource(\order $order, array $cc_info): array
    {
        $payment_source = [
            'name' => $cc_info['name'],
            'number' => $cc_info['number'],
            'security_code' => $cc_info['security_code'],
            'expiry' => $cc_info['expiry_year'] . '-' . $cc_info['expiry_month'],
            'billing_address' => Address::get($order->billing),
            'experience_context' => [
                'return_url' => $cc_info['redirect'] . '?op=3ds_return',
                'cancel_url' => $cc_info['redirect'] . '?op=3ds_cancel',
            ],
        ];
        if (isset($_POST['ppr_cc_sca_always']) || (defined('MODULE_PAYMENT_PAYPALR_SCA_ALWAYS') && MODULE_PAYMENT_PAYPALR_SCA_ALWAYS === 'true')) {
            $payment_source['attributes']['verification']['method'] = 'SCA_ALWAYS'; //- Defaults to 'SCA_WHEN_REQUIRED' for live environment
        }
        return $payment_source;
    }

    protected function buildLevel2Level3Data(array $purchase_unit): array
    {
        if (isset($purchase_unit['amount']['breakdown']['tax_total'])) {
            $level_2 = [
                'tax_total' => $purchase_unit['amount']['breakdown']['tax_total'],
            ];
        }
        $level_3 = [];

        // -----
        // Note: Although undocumented in PayPal's API, apparently their endpoint
        // "doesn't like" values with intervening spaces!
        //
        if (SHIPPING_ORIGIN_ZIP !== '') {
            $level_3['ships_from_postal_code'] = str_replace(' ', '', SHIPPING_ORIGIN_ZIP);
        }
        if (!empty($purchase_unit['items'])) {
            $level_3['line_items'] = $purchase_unit['items'];
        }
        if (isset($purchase_unit['amount']['breakdown']['shipping'])) {
            $level_3['shipping_amount'] = $purchase_unit['amount']['breakdown']['shipping'];
        }
        if ($this->overallDiscount != 0) {
            $level_3['discount_amount'] = $this->setRateConvertedValue($this->overallDiscount);
        }
        if (isset($purchase_unit['shipping']['address'])) {
            $level_3['shipping_address'] = $purchase_unit['shipping']['address'];
        }

        if (!isset($level_2) || empty($level_3)) {
            return [];
        }

        $supplementary_data = [
            'card' => [
            ],
        ];
        if (isset($level_2)) {
            $supplementary_data['card']['level_2'] = $level_2;
        }
        if (!empty($level_3)) {
            $supplementary_data['card']['level_3'] = $level_3;
        }
        return $supplementary_data;
    }
}
