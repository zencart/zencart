<?php
/**
 * ot_coupon order-total module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: pRose on charmes 2024 Aug 29 Modified in v2.1.0-alpha2 $
 */

/*
 * NOTE: Notifier NOTIFY_OT_COUPON_CALCS_FINISHED formerly had its first parameter return an array with a 'coupon' entry which was a queryFactoryResult. It is now just an array of the ->fields values
 */

/**
 * Order Total class to handle discount coupons
 */
class ot_coupon extends base
{
    /**
     * $_check is used to check the configuration key set up
     * @var int
     */
    protected $_check;
    /**
     * $calculate_tax determines how tax should be applied to coupon Standard, Credit Note, None
     * @var string
     */
    public $calculate_tax;
    /**
     * $code determines the internal 'code' name used to designate "this" order total module
     * @var string
     */
    public $code;
    /**
     * $coupon_code is the coupon_code under consideration while being applied to an order
     * @var string
     */
    protected $coupon_code;
    /**
     * $credit_class flag to indicate order totals method is a credit class
     * @var boolean
     */
    public $credit_class;
    /**
     * $deduction amount of deduction calculated/afforded while being applied to an order
     * @var float|null
     */
    public $deduction;
    /**
     * $description is a soft name for this order total method
     * @var string
     */
    public $description;
    /**
     * $header the module box header
     * @var string
     */
    public $header;
    /**
     * $include_shipping allow shipping costs to be discounted by coupon if 'true'
     * @var string
     */
    public $include_shipping;
    /**
     * $include_tax allow tax to be discounted by coupon if 'true'
     * @var string
     */
    public $include_tax;
    /**
     * $sort_order is the order priority of this order total module when displayed
     * @var int
     */
    public $sort_order;
    /**
     * $tax_class is the Tax class to be applied to the coupon cost
     * @var
     */
    public $tax_class;
    /**
     * $title is the displayed name for this order total method
     * @var string
     */
    public $title;
    /**
     * $output is an array of the display elements used on checkout pages
     * @var array
     */
    public $output = [];
    /**
     * $user_prompt not used = ''
     * @var string
     */
    public $user_prompt;
    /**
     * $validation_errors is an array of error messages from coupon validation
     * @var array
     */
    protected $validation_errors = [];

    function __construct()
    {
        $valid = true;
        $this->notify('NOTIFY_OT_COUPON_START', true, $valid);
        if (!$valid) {
            return false;
        }
        $this->code = 'ot_coupon';
        $this->header = MODULE_ORDER_TOTAL_COUPON_HEADER;
        $this->title = MODULE_ORDER_TOTAL_COUPON_TITLE;
        $this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
        $this->credit_class = true;
        $this->user_prompt = '';
        $this->sort_order = defined('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER') ? MODULE_ORDER_TOTAL_COUPON_SORT_ORDER : null;
        if (null === $this->sort_order) return false;

        $this->include_shipping = MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING;
        $this->include_tax = MODULE_ORDER_TOTAL_COUPON_INC_TAX;
        $this->calculate_tax = MODULE_ORDER_TOTAL_COUPON_CALC_TAX;
        $this->tax_class = MODULE_ORDER_TOTAL_COUPON_TAX_CLASS;
        if (IS_ADMIN_FLAG === true) {
            if ($this->include_tax == 'true' && $this->calculate_tax != "None") {
                $this->title .= '<span class="alert">' . MODULE_ORDER_TOTAL_COUPON_INCLUDE_ERROR . '</span>';
            }
        }
    }

    /**
     * Produces final deduction values,
     * updates $order amounts,
     * and generates the $this->output for showing discount information on checkout pages
     */
    function process()
    {
        global $order, $currencies;

        if (empty($_SESSION['cc_id'])) {
            return;
        }

        $od_amount = ['tax' => 0, 'total' => 0];

        $order_total = $this->get_order_total(isset($_SESSION['cc_id']) ? $_SESSION['cc_id'] : '');

        if ($order_total['orderTotal'] > 0) {
            $od_amount = $this->calculate_deductions();
        }
        $this->deduction = $od_amount['total'];

        if ($od_amount['total'] > 0) {
            $tax = 0;
            foreach ($order->info['tax_groups'] as $key => $value) {
                if (isset($od_amount['tax_groups'][$key])) {
                    $order->info['tax_groups'][$key] -= $od_amount['tax_groups'][$key];
                    $order->info['tax_groups'][$key] = zen_round($order->info['tax_groups'][$key], $currencies->get_decimal_places($_SESSION['currency']));
                    $tax += $od_amount['tax_groups'][$key];
                }
            }
            // free shipping for free shipping 'S' or percentage off and free shipping 'E' or amount off and free shipping 'O'
            if (in_array($od_amount['type'], ['S', 'E', 'O'])) {
                $order->info['shipping_cost'] = 0;
            }

            $order->info['total'] -= $od_amount['total'];
            $order->info['coupon_amount'] = $od_amount['total'];

            if (DISPLAY_PRICE_WITH_TAX != 'true') {
                $order->info['total'] -= $tax;
            }
            $order->info['tax'] -= $tax;

            if ($order->info['total'] < 0) $order->info['total'] = 0;

            $this->output[] = [
                'title' => $this->title . ': ' . $this->coupon_code . ' :',
                'text' => '-' . $currencies->format($od_amount['total']),
                'value' => $od_amount['total']
            ];
        }
    }

    /**
     * Reset any variables related to this module
     */
    function clear_posts()
    {
        unset($_POST['dc_redeem_code']);
        unset($_SESSION['cc_id']);
    }

    /**
     * Per order_total class, this function is not used. See process() instead.
     */
    function pre_confirmation_check()
    {
        //
    }

    /**
     * This function is not used by this module
     *
     * @return bool
     */
    function selection_test()
    {
        return false;
    }

    /**
     * Prepare input field data for coupon code redemption on checkout_payment page
     *
     * @return array
     */
    function credit_selection()
    {
        global $discount_coupon;

        $valid = true;
        $this->notify('NOTIFY_OT_COUPON_CREDIT_SELECTION', true, $valid);
        if (!$valid) {
            return;
        }

        $couponLink = '';
        if (!empty($discount_coupon->fields['coupon_code']) && !empty($_SESSION['cc_id'])) {
            $coupon_code = $discount_coupon->fields['coupon_code'];
            $couponLink = $this->generateCouponPopupLink($_SESSION['cc_id'], $coupon_code);
        }

        // note that the placement of the redeem code can be moved within the array on the instructions or the title
        $selection = [
            'id' => $this->code,
            'module' => $this->title,
            'redeem_instructions' => MODULE_ORDER_TOTAL_COUPON_REDEEM_INSTRUCTIONS .
                (!empty($coupon_code) ? MODULE_ORDER_TOTAL_COUPON_REMOVE_INSTRUCTIONS : '') .
                (!empty($coupon_code) ? '<p>' . MODULE_ORDER_TOTAL_COUPON_TEXT_CURRENT_CODE . $couponLink . '</p><br>' : ''),
            'fields' => [
                [
                    'title' => MODULE_ORDER_TOTAL_COUPON_TEXT_ENTER_CODE,
                    'field' => zen_draw_input_field('dc_redeem_code', '', 'id="disc-' . $this->code . '" onkeyup="submitFunction(0,0)"'),
                    'tag' => 'disc-' . $this->code
                ]
            ]
        ];

        return $selection;
    }

    /**
     * Make a link to the coupon-help popup page.
     * This link is used in status messages and error messages.
     * The referred page displays information about the coupon's valid uses.
     *
     * @param int $coupon_id
     * @param string $coupon_code
     * @return string
     */
    protected function generateCouponPopupLink($coupon_id, $coupon_code)
    {
        global $request_type;

        $couponLink = '<a href="javascript:couponpopupWindow(\'' .
            zen_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $coupon_id, $request_type) .
            '\')"' .
            ' title="' . TEXT_COUPON_LINK_TITLE . '"' .
            '>' . $coupon_code . '</a>';

        $this->notify('NOTIFY_OT_COUPON_GENERATE_POPUP_LINK', ['coupon_id' => $coupon_id, 'coupon_code' => $coupon_code], $couponLink);

        return $couponLink;
    }

    /**
     * When on checkout_confirmation, process POSTed dc_redeem_code for validity or requested removal
     * Also displays messageStack error alerts, and performs redirects back to Payment page if invalid
     */
    function collect_posts()
    {
        global $messageStack;

        $coupon_code = isset($_POST['dc_redeem_code']) ? trim($_POST['dc_redeem_code']) : '';

        // Check whether the customer has requested to un-apply the coupon. This will redirect, which halts further execution.
        $this->remove_coupon_if_requested($coupon_code);

        // @TODO get rid of the use of $discount_coupon here; might be as simple as using $_SESSION['cc_id'] since that's what's used to set this
        global $discount_coupon;

        if (empty($coupon_code) && empty($discount_coupon->fields['coupon_code'])) {
            return;
        }

        // $discount_coupon might be set externally, and if it is then we use that if POST is empty
        if (empty($coupon_code) && !empty($discount_coupon->fields['coupon_code'])) {
            $coupon_code = $discount_coupon->fields['coupon_code'];
        }

        if (empty($coupon_code)) {
            $coupon_code = "UNKNOWN_COUPON";
        }

        $coupon_id = $this->performValidations($coupon_code);
        $this->setMessageStackValidationAlerts();


        // display all error messages
        if (!empty($this->validation_errors)) {
            $this->clear_posts();
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
        }

        // if not redirected yet, it must be valid, so now we assign it to the session
        if (!empty($coupon_id)) {
            $_SESSION['cc_id'] = $coupon_id;
            $messageStack->add('checkout', TEXT_VALID_COUPON, 'success');
        }
    }

    /**
     * Return validation errors array.
     * Used by external calls to validation when wanting to do something other than stuffing the errors onto the messageStack
     *
     * @return array
     */
    function getValidationErrors()
    {
        return $this->validation_errors;
    }

    /**
     * Set validation errors into messageStack
     *
     * @param int $limit max number of errors to push onto the messageStack. (Sometimes only want one instead of everything.)
     * @param string $stack messageStack location/array to populate with error messages
     * @param string $alertLevel what kind of alert should be generated. Default is 'caution'
     */
    function setMessageStackValidationAlerts($limit = 0, $stack = 'redemptions', $alertLevel = 'caution')
    {
        global $messageStack;
        $i = 0;

        foreach ($this->validation_errors as $errorMessage) {
            $messageStack->add_session($stack, $errorMessage, $alertLevel);
            $i++;
            if (!empty($limit) && $i > $limit) {
                break;
            }
        }
    }

    /**
     * Validate supplied $coupon_code for validity in database and against
     * any configured restrictions on products, customers, number of uses, dates, address zones, etc.
     *
     * @param string $coupon_code
     * @return int|null|void
     */
    function performValidations($coupon_code)
    {
        global $currencies;
        $this->validation_errors = [];

        $coupon_details = $this->getCouponDetailsFromDb($coupon_code);

        if (empty($coupon_details) || $coupon_details['coupon_active'] !== 'Y') {
            if (!$this->isCodeEqualToRemoveCode($coupon_code)) {
                $this->validation_errors[] = sprintf(TEXT_INVALID_REDEEM_COUPON, $coupon_code);
            }
            return;
        }

        $this->notify('NOTIFY_OT_COUPON_COUPON_INFO', ['coupon_result' => $coupon_details, 'code' => $coupon_code]);

        // get popup link to insert into validation error messages
        $dc_link = $this->generateCouponPopupLink($coupon_details['coupon_id'], $coupon_code);


        if (!empty($_SESSION['cart']->contents)) {
            $validMinimumPurchaseAmount = $this->validateCouponMinimumPurchaseAmount($coupon_details);
            if (!$validMinimumPurchaseAmount) {
                $this->validation_errors[] = sprintf(TEXT_INVALID_REDEEM_COUPON_MINIMUM, (empty($this->validation_errors) ? $dc_link : $coupon_code), $currencies->format($coupon_details['coupon_minimum_order']));
                // return;
            }

            $validForProductsInCart = $this->validateCouponProductRestrictions($coupon_details['coupon_id']);
            if (!$validForProductsInCart) {
                $this->clear_posts();
                $this->validation_errors[] = sprintf(TEXT_INVALID_COUPON_PRODUCT, (empty($this->validation_errors) ? $dc_link : $coupon_code));
                // return;
            }

            $validMaxOrdersLimit = $this->validateCouponMaxOrdersLimit($coupon_details);
            if (!$validMaxOrdersLimit) {
                $this->validation_errors[] = sprintf(TEXT_INVALID_COUPON_ORDER_LIMIT, (empty($this->validation_errors) ? $dc_link : $coupon_code), $coupon_details['coupon_order_limit']);
            }
        }

        $validStartDate = $this->validateCouponStartDate($coupon_details);
        if (!$validStartDate) {
            $this->validation_errors[] = sprintf(TEXT_INVALID_STARTDATE_COUPON, (empty($this->validation_errors) ? $dc_link : $coupon_code), zen_date_short($coupon_details['coupon_start_date']));
            // return;
        }

        $validEndDate = $this->validateCouponEndDate($coupon_details);
        if (!$validEndDate) {
            $this->validation_errors[] = sprintf(TEXT_INVALID_FINISHDATE_COUPON, (empty($this->validation_errors) ? $dc_link : $coupon_code), zen_date_short($coupon_details['coupon_expire_date']));
            // return;
        }

        $validNotExceededNumberOfUses = $this->validateCouponMaximumUses($coupon_details);
        if (!$validNotExceededNumberOfUses) {
            $this->validation_errors[] = sprintf(TEXT_INVALID_USES_COUPON, (empty($this->validation_errors) ? $dc_link : $coupon_code), $coupon_details['uses_per_coupon']);
            // return;
        }

        $validNotExceededNumberOfUsesByCustomer = $this->validateCouponUsesPerCustomer($coupon_details);
//        $coupon_uses_per_customer_exceeded_guest_checkout = $this->validateCouponUsesPerGuestCheckoutCustomer($coupon_details);
        if (!$validNotExceededNumberOfUsesByCustomer) {
            $this->validation_errors[] = sprintf(TEXT_INVALID_USES_USER_COUPON, (empty($this->validation_errors) ? $dc_link : $coupon_code), $coupon_details['uses_per_user']);
            // return;
        }

        global $order;
        if ($order !== null) {
            $validForAddress = $this->validateCouponForAddress($coupon_details);
            if (!$validForAddress) {
                $this->validation_errors[] = sprintf(TEXT_REMOVE_REDEEM_COUPON_ZONE, (empty($this->validation_errors) ? $dc_link : $coupon_code));
            }
        }

        return $coupon_details['coupon_id'];
    }

    /**
     * This function is not used by this module
     *
     * @return bool
     */
    function use_credit_amount()
    {
        return false;
    }

    /**
     * This function is not used by this module
     *
     * @param $i
     * @return bool
     */
    function update_credit_account($i)
    {
        return false;
    }

    /**
     * Track coupon redemption
     */
    function apply_credit()
    {
        global $db, $insert_id;
        $cc_id = empty($_SESSION['cc_id']) ? 0 : (int)$_SESSION['cc_id'];
        if (!empty($this->deduction)) {
            $db->Execute("INSERT INTO " . TABLE_COUPON_REDEEM_TRACK . "
                    (coupon_id, redeem_date, redeem_ip, customer_id, order_id)
                    VALUES ('" . (int)$cc_id . "', now(), '" . $_SERVER['REMOTE_ADDR'] . "', '" . (int)$_SESSION['customer_id'] . "', '" . (int)$insert_id . "')");
        }
        $_SESSION['cc_id'] = '';
    }

    /**
     * Calculate actual deductions according to configured coupon rules
     *
     * Also sets $this->coupon_code
     *
     * @return array $od_amount
     */
    function calculate_deductions()
    {
        global $db, $currencies;

        $od_amount = ['tax' => 0, 'total' => 0];
        if (empty($_SESSION['cc_id'])) {
            return $od_amount;
        }

        $currencyDecimalPlaces = $currencies !== null ? $currencies->get_decimal_places($_SESSION['currency']) : 2;

        $result = $db->Execute("SELECT * FROM " . TABLE_COUPONS . " WHERE coupon_id = " . (int)$_SESSION['cc_id']);

        if ($result->RecordCount() < 1 || empty($result->fields['coupon_code'])) {
            return $od_amount;
        }
        $coupon_details = $result->fields;

        $this->coupon_code = $coupon_details['coupon_code'];

        $orderTotalDetails = $this->get_order_total($coupon_details['coupon_id']);

        $orderAmountToCompareAgainstCouponMinimum = (string)$orderTotalDetails['orderTotal'];

        $orderAmountTotal = (string)$orderTotalDetails['orderTotal'];  // coupon is applied against value of only qualifying/restricted products in cart
        if ($coupon_details['coupon_calc_base'] == 1) {
            $orderAmountToCompareAgainstCouponMinimum = (string)$orderTotalDetails['totalFull']; // coupon minimum comparison includes sale items that may not be included in deduction
        }


//echo 'ot_coupon coupon_total: ' . $coupon_details['coupon_calc_base'] . '<br>$orderTotalDetails[orderTotal]: ' . $orderTotalDetails['orderTotal'] . '<br>$orderTotalDetails[totalFull]: ' . $orderTotalDetails['totalFull'] . '<br>$orderAmountTotal: ' . $orderAmountTotal . '<br><br>$coupon_details[coupon_minimum_order]: ' . $coupon_details['coupon_minimum_order'] . '<br>$orderAmountToCompareAgainstCouponMinimum: ' . $orderAmountToCompareAgainstCouponMinimum . '<br>';

        // @TODO - adjust all Totals to use $orderAmountTotal but strong review for what total applies where for Percentage, Amount, etc.

        if ($orderTotalDetails['orderTotal'] > 0) {

            if ((string)$orderAmountToCompareAgainstCouponMinimum >= $coupon_details['coupon_minimum_order']) {

                // Default to 1 here if fixed-rate discounts are applied "per order" (not per each product)
                $coupon_product_count = 1;
                // If coupon is set to calculate amounts based on per-each-product, count the number of products (multiplied by qty) in the cart
                if ($coupon_details['coupon_product_count'] > 0 && in_array($coupon_details['coupon_type'], ['F', 'O'])) {
                    $products = $_SESSION['cart']->get_products();
                    $coupon_product_count = 0;
                    foreach ($products as $product) {
                        if (CouponValidation::is_product_valid((int)$product['id'], (int)$coupon_details['coupon_id'])) {
                            $coupon_product_count += $_SESSION['cart']->get_quantity($product['id']);
                        }
                    }
                    //  $messageStack->add_session('checkout_payment', 'Coupon products-count: ' . $coupon_product_count, 'caution');
                }

                // Determine return values for discount amounts based on coupon type
                $coupon_includes_free_shipping = false;
                $od_amount['type'] = $coupon_details['coupon_type'];

                switch ($coupon_details['coupon_type']) {
                    case 'S': // Free Shipping
                        $od_amount['total'] = $orderTotalDetails['shipping'];
                        $od_amount['tax'] = ($this->calculate_tax == 'Standard') ? $orderTotalDetails['shippingTax'] : 0;
                        if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] != '') {
                            $od_amount['tax_groups'][$_SESSION['shipping_tax_description']] = $od_amount['tax'];
                        }
                        // early-return skips further processing for type 'S'
                        return $od_amount;
                        break;
                    case 'P': // percentage
//                        $od_amount['total'] = zen_round($orderTotalDetails['orderTotal']*($coupon_details['coupon_amount']/100), $currencyDecimalPlaces);
                        $od_amount['total'] = zen_round($orderAmountTotal * ($coupon_details['coupon_amount'] / 100), $currencyDecimalPlaces);
//                        $ratio = $od_amount['total']/$orderTotalDetails['orderTotal'];
                        $ratio = $od_amount['total'] / $orderAmountTotal;
                        break;
                    case 'E': // percentage & Free Shipping
//                        $od_amount['total'] = zen_round($orderTotalDetails['orderTotal']*($coupon_details['coupon_amount']/100), $currencyDecimalPlaces);
                        $od_amount['total'] = zen_round($orderAmountTotal * ($coupon_details['coupon_amount'] / 100), $currencyDecimalPlaces);
                        // add in Free Shipping
                        $coupon_includes_free_shipping = true;
                        $od_amount['tax'] = ($this->calculate_tax == 'Standard') ? $orderTotalDetails['shippingTax'] : 0;
                        $ratio = $od_amount['total'] / $orderAmountTotal;
                        if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] != '') {
                            $od_amount['tax_groups'][$_SESSION['shipping_tax_description']] = $od_amount['tax'];
                        }
                        break;
                    case 'F': // Fixed amount Off
//                        $od_amount['total'] = zen_round($coupon_details['coupon_amount'] * ($orderTotalDetails['orderTotal']>0), $currencyDecimalPlaces);
                        $od_amount['total'] = zen_round(($coupon_details['coupon_amount'] > $orderTotalDetails['orderTotal'] ? $orderTotalDetails['orderTotal'] : $coupon_details['coupon_amount']) * ($orderTotalDetails['orderTotal'] > 0) * $coupon_product_count, $currencyDecimalPlaces);
//                        $ratio = $od_amount['total']/$orderTotalDetails['orderTotal'];
                        $ratio = $od_amount['total'] / $orderAmountTotal;
                        break;
                    case 'O': // Both Fixed amount off & Free Shipping
//                        $od_amount['total'] = zen_round($coupon_details['coupon_amount'] * ($orderTotalDetails['orderTotal']>0), $currencyDecimalPlaces);
                        $od_amount['total'] = zen_round(($coupon_details['coupon_amount'] > $orderTotalDetails['orderTotal'] ? $orderTotalDetails['orderTotal'] : $coupon_details['coupon_amount']) * ($orderTotalDetails['orderTotal'] > 0) * $coupon_product_count, $currencyDecimalPlaces);
                        //$od_amount['total'] = zen_round($coupon_details['coupon_amount'] * ($orderAmountTotal>0), $currencyDecimalPlaces);
                        // add in Free Shipping
                        $coupon_includes_free_shipping = true;
                        $od_amount['tax'] = ($this->calculate_tax == 'Standard') ? $orderTotalDetails['shippingTax'] : 0;
                        $ratio = $od_amount['total'] / $orderAmountTotal;
                        if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] != '') {
                            $od_amount['tax_groups'][$_SESSION['shipping_tax_description']] = $od_amount['tax'];
                        }
                        break;
                    case 'G': // GV / Gift Certificate
                    default:
                        // n/a
                }

                // adjust for tax
                switch ($this->calculate_tax) {
                    case 'Standard':
                        if ($od_amount['total'] >= $orderTotalDetails['orderTotal']) $ratio = 1;
                        foreach ($orderTotalDetails['orderTaxGroups'] as $key => $value) {
                            $this_tax = $orderTotalDetails['orderTaxGroups'][$key];
                            if ($this->include_shipping != 'true') {
                                if (isset($_SESSION['shipping_tax_description']) && $_SESSION['shipping_tax_description'] == $key) {
                                    $this_tax -= $orderTotalDetails['shippingTax'];
                                }
                            }
                            $od_amount['tax_groups'][$key] = zen_round($this_tax * $ratio, $currencyDecimalPlaces);
                            $od_amount['tax'] += $od_amount['tax_groups'][$key];
                        }
                        if (DISPLAY_PRICE_WITH_TAX == 'true' && $coupon_details['coupon_type'] == 'F') {
                            $od_amount['total'] += $od_amount['tax'];
                        }
                        break;
                    case 'Credit Note':
                        $tax_rate = zen_get_tax_rate($this->tax_class);
                        $od_amount['tax'] = zen_calculate_tax($od_amount['total'], $tax_rate);
                        $tax_description = zen_get_tax_description($this->tax_class);
                        $od_amount['tax_groups'][$tax_description] = $od_amount['tax'];
                        break;
                    case 'None':
                    default:
                        break;
                }

                // adjust for free-shipping
                if ($coupon_includes_free_shipping) {
                    $od_amount['total'] += $orderTotalDetails['shipping'];
                }
            }
        }

        // -----
        // Let an observer know that the coupon-related calculations have finished, providing read-only
        // copies of (a) the base coupon information, (b) the results from 'get_order_total' and this
        // method's return values.
        //
        $this->notify('NOTIFY_OT_COUPON_CALCS_FINISHED', ['coupon' => $coupon_details, 'order_totals' => $orderTotalDetails, 'od_amount' => $od_amount], $coupon_details);

//    print_r($order->info);
//    print_r($orderTotalDetails);echo "<br><br>";
//    echo 'RATIo = '. $ratio;
//    print_r($od_amount);
        return $od_amount;
    }

    /**
     * Calculate eligible total amounts against which discounts will be applied
     *
     * @param int $coupon_id
     */
    function get_order_total($coupon_id): array
    {
        global $order;
        $orderTaxGroups = $order->info['tax_groups'] ?? [];
        $orderTotalTax = $order->info['tax'] ?? 0;
        $orderTotal = $order->info['total'] ?? 0;

        $coupon_id = (int)$coupon_id;

        // for products which are not applicable for this coupon, calculate their value in the cart and reduce it from the final order-total that the coupon's discounts will apply to
        $products = $_SESSION['cart']->get_products();
        $i = 0;
        foreach ($products as $product) {
            $i++;
            $is_product_valid = (CouponValidation::is_product_valid((int)$product['id'], $coupon_id) && CouponValidation::is_coupon_valid_for_sales((int)$product['id'], $coupon_id));

            $this->notify('NOTIFY_OT_COUPON_PRODUCT_VALIDITY', ['is_product_valid' => $is_product_valid, 'i' => $i]);

            // @TODO - defer this to the shopping_cart class so product price calculations are handled in one central place
            if (!$is_product_valid) {
                $products_tax = zen_get_tax_rate($product['tax_class_id']);
                $productsTaxAmount = (zen_calculate_tax($product['final_price'], $products_tax)) * $product['quantity'];

                $orderTotal -= $product['final_price'] * $product['quantity'];

                if ($this->include_tax === 'true' || DISPLAY_PRICE_WITH_TAX === 'true') {
                    $orderTotal -= $productsTaxAmount;
                }
                $orderTotalTax -= $productsTaxAmount;
                $tax_description = zen_get_tax_description($product['tax_class_id']);
                if (empty($orderTaxGroups[$tax_description])) {
                    $orderTaxGroups[$tax_description] = 0 - $productsTaxAmount;
                } else {
                    $orderTaxGroups[$tax_description] -= $productsTaxAmount;
                }
            }
        }

        // shipping/tax
        if ($this->include_shipping !== 'true') {
            $orderTotal -= $order->info['shipping_cost'] ?? 0;
            if (!empty($_SESSION['shipping_tax_description'])) {
                $orderTotalTax -= $order->info['shipping_tax'] ?? 0;
            }
        }
        if (DISPLAY_PRICE_WITH_TAX !== 'true') {
            $orderTotal -= $orderTotalTax;
        }

        // change what total is used for Discount Coupon Minimum
        $orderTotalFull = $order->info['total'] ?? 0;
        //echo 'Current $orderTotalFull: ' . $orderTotalFull . ' shipping_cost: ' . $order->info['shipping_cost'] . '<br>';
        $orderTotalFull -= $order->info['shipping_cost'] ?? 0;
        //echo 'Current $orderTotalFull less shipping: ' . $orderTotalFull . '<br>';
        $orderTotalFull -= $orderTotalTax;
        //echo 'Current $orderTotalFull less taxes: ' . $orderTotalFull . '<br>';
        // left for total order amount ($orderTotalDetails['totalFull']) vs qualified order amount ($order_total['orderTotal']) - to include both in array
        // add total order amount ($orderTotalFull) to array for $order_total['totalFull'] vs $order_total['orderTotal']
        $return = [
            'totalFull' => $orderTotalFull,
            'orderTotal' => $orderTotal,
            'orderTaxGroups' => $orderTaxGroups,
            'orderTax' => $orderTotalTax,
            'shipping' => $order->info['shipping_cost'] ?? 0,
            'shippingTax' => $order->info['shipping_tax'] ?? 0,
        ];
        $this->notify('NOTIFY_OT_COUPON_ORDER_TOTAL_FINISHED', null, $return);
        return $return;
    }

    /**
     * Check install status
     *
     * @return bool
     */
    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_ORDER_TOTAL_COUPON_STATUS'");
            $this->_check = $check_query->RecordCount();
        }

        return $this->_check;
    }

    /**
     * @return array
     */
    function keys()
    {
        return ['MODULE_ORDER_TOTAL_COUPON_STATUS', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS'];
    }

    function install()
    {
        global $db;
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '280', 'Sort order of display.', '6', '2', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Include Shipping', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'true', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Include Tax', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'false', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Tax Class', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '0', 'Use the following tax class when treating Discount Coupon as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    }

    /**
     * Uninstall
     *
     */
    function remove()
    {
        global $db;
        $keys = implode("','", $this->keys());

        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " where configuration_key IN ('" . $keys . "')");
    }

    /**
     * Remove discount coupon by request
     *
     * Redirects back to checkout_payment on success
     *
     * @param string $coupon_code
     */
    public function remove_coupon_if_requested($coupon_code = '')
    {
        global $messageStack;

        if (empty($coupon_code)) {
            $coupon_code = isset($_POST['dc_redeem_code']) ? trim($_POST['dc_redeem_code']) : '';
        }

        if (empty($coupon_code)) {
            return;
        }

        if (!defined('TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER')) define('TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER', 'REMOVE');

        if ($this->isCodeEqualToRemoveCode($coupon_code)) {

            $this->remove_coupon_from_current_session();

            $messageStack->add_session('checkout_payment', TEXT_REMOVE_REDEEM_COUPON, 'caution');
        }
    }

    private function isCodeEqualToRemoveCode($code) {
        return (strtoupper($code) == TEXT_COMMAND_TO_DELETE_CURRENT_COUPON_FROM_ORDER);
    }

    /**
     * Remove coupon from session/order and trigger notifier
     */
    public function remove_coupon_from_current_session()
    {
        $this->clear_posts();

        $this->notify('NOTIFY_OT_COUPON_COUPON_REMOVED');
    }

    /**
     * @param string $coupon_code
     * @return array
     */
    protected function getCouponDetailsFromDb($coupon_code = '')
    {
        global $db;

        $sql = "SELECT *
                FROM " . TABLE_COUPONS . "
                WHERE coupon_code= :couponCodeEntered
                AND coupon_type != 'G'";

        $sql = $db->bindVars($sql, ':couponCodeEntered', $coupon_code, 'string');

        $result = $db->Execute($sql, 1);

        return $result->RecordCount() ? $result->fields : null;
    }

    /**
     * look through the items in the cart to see if this coupon is valid for any item in the cart
     *
     * @param int $coupon_id coupon_id from coupons table
     * @return bool
     */
    protected function validateCouponProductRestrictions($coupon_id)
    {
        $products = $_SESSION['cart']->get_products();

        $found_valid = null;
        $this->notify('NOTIFY_COUPON_VALIDATION_PRODUCT_RESTRICTIONS', $coupon_id, $products, $found_valid);
        if ($found_valid !== null) {
            return $found_valid;
        }
        $coupon_id = (int)$coupon_id;

        $found_valid = false;
        foreach ($products as $product) {
            if (CouponValidation::is_product_valid((int)$product['id'], $coupon_id) && CouponValidation::is_coupon_valid_for_sales((int)$product['id'], $coupon_id)) {
                $found_valid = true;
                break;
            }
        }

        return $found_valid;
    }

    /**
     * Check whether the customer has placed more orders than the coupon's allowed limit.
     * This is mainly to encourage shopping by "new" customers.
     *
     * @param array $coupon_details
     * @return bool
     */
    protected function validateCouponMaxOrdersLimit($coupon_details)
    {
        global $db;

        // zero means no limit set on the coupon
        if (empty($coupon_details['coupon_order_limit'])) {
            return true;
        }

        // Find out how many orders the customer has placed
        $sql = "SELECT orders_id FROM " . TABLE_ORDERS . " WHERE customers_id = " . (int)$_SESSION['customer_id'];
        $result = $db->Execute($sql);

        // must have less orders than the coupon's allowed limit
        return !($result->RecordCount() > $coupon_details['coupon_order_limit']);
    }

    /**
     * Check whether the coupon's start date is valid
     *
     * @TODO - what about zero-dates or null dates?
     *
     * @param array $coupon_details
     * @return bool
     */
    protected function validateCouponStartDate($coupon_details)
    {
        if (date_create(date("Y-m-d")) < date_create($coupon_details['coupon_start_date'])) {
            return false;
        }

        return true;
    }

    /**
     * Check whether the coupon's expiry date is valid
     *
     * @TODO - what about zero-dates or null dates?
     *
     * @param array $coupon_details
     * @return bool
     */
    protected function validateCouponEndDate($coupon_details)
    {
        if (date_create(date("Y-m-d")) > date_create($coupon_details['coupon_expire_date'])) {
            return false;
        }

        return true;
    }

    /**
     * Check whether the coupon's minimum_order amount has been reached
     *
     * @param array $coupon_details
     * @return bool
     */
    protected function validateCouponMinimumPurchaseAmount($coupon_details)
    {
        // 0 means unlimited
        if (empty($coupon_details['coupon_minimum_order'])) {
            return true;
        }

        $orderTotalDetails = $this->get_order_total($coupon_details['coupon_id']);

        $orderAmountToCompareAgainstCouponMinimum = (string)$orderTotalDetails['orderTotal'];

        $orderAmountTotal = (string)$orderTotalDetails['orderTotal'];  // coupon is applied against value of only qualifying/restricted products in cart
        if ($coupon_details['coupon_calc_base'] == 1) {
            $orderAmountToCompareAgainstCouponMinimum = (string)$orderTotalDetails['totalFull']; // coupon minimum comparison includes sale items that may not be included in deduction
        }

//echo 'Product: ' . $orderTotalDetails['orderTotal'] . ' Order: ' . $orderTotalDetails['totalFull'] . ' $orderAmountTotal: ' . $orderAmountTotal . '<br>';

// ALTERNATE POTENTIAL RULES
// for total order amount vs qualified order amount just switch the commented lines
//        if ((string)$orderTotalDetails['totalFull'] < $coupon_details['coupon_minimum_order'])
//        if ((string)$orderTotalDetails['orderTotal'] < $coupon_details['coupon_minimum_order'])
//        if ($orderAmountTotal > 0 && $orderAmountTotal < $coupon_details['coupon_minimum_order'])

        if ($orderAmountTotal > 0 && $orderAmountToCompareAgainstCouponMinimum < $coupon_details['coupon_minimum_order']) {
            // $order_total['orderTotal'] . ' vs ' . $order_total['totalFull']
            return false;
        }

        return true;
    }


    /**
     * Check whether this coupon has been used (by anybody) more than the maximum number of times allowed
     *
     * @param array $coupon_details
     * @return bool
     */
    protected function validateCouponMaximumUses($coupon_details)
    {
        global $db;

        // 0 means unlimited
        if (empty($coupon_details['uses_per_coupon'])) {
            return true;
        }

        $sql = "SELECT count(coupon_id) as total_uses_of_coupon
                FROM " . TABLE_COUPON_REDEEM_TRACK . "
                WHERE coupon_id = " . (int)$coupon_details['coupon_id'];

        $result = $db->Execute($sql);

        return ($result->fields['total_uses_of_coupon'] < $coupon_details['uses_per_coupon']);
    }

    /**
     * Check whether coupon has been used by this customer more times than the allowed per-customer limit
     *
     * @param array $coupon_details
     * @param int|null $customer_id
     * @return bool
     */
    protected function validateCouponUsesPerCustomer($coupon_details, ?int $customer_id = null)
    {
        global $db;

        // 0 means unlimited
        if (empty($coupon_details['uses_per_user'])) {
            return true;
        }

        if (empty($customer_id) && zen_is_logged_in()) {
            $customer_id = (int)$_SESSION['customer_id'];
        }

        // NOTE: prior to v158 eligibility during guest checkout was checked via the NOTIFY_OT_COUPON_USES_PER_USER_CHECK Notifier
        if (empty($customer_id) && zen_in_guest_checkout()) {
            $customer_id = 0;

            $guest_result = $this->validateCouponUsesPerGuestCheckoutCustomer($coupon_details);

            if ($guest_result !== null) {
                return $guest_result;
            }
        }

        $sql = "SELECT coupon_id
                FROM " . TABLE_COUPON_REDEEM_TRACK . "
                WHERE coupon_id = " . (int)$coupon_details['coupon_id'] . "
                AND customer_id = " . (int)$customer_id;

        $result = $db->Execute($sql);

        $valid = ($result->RecordCount() < $coupon_details['uses_per_user']);

        // NOTE: Prior to v158 this Notifier hook was used to alter $valid status if in Guest Checkout in plugins such as OPC
        $this->notify('NOTIFY_OT_COUPON_USES_PER_USER_CHECK', $coupon_details, $valid);

        return $valid;
    }

    /**
     * @TODO
     * Check whether coupon has been used by this Guest Checkout customer more times than the allowed per-customer limit
     *
     * @param array $coupon_details
     * @return bool|null
     */
    protected function validateCouponUsesPerGuestCheckoutCustomer($coupon_details)
    {
        if (!zen_in_guest_checkout()) {
            return null;
        }

        // NOTE: prior to v158 eligibility during guest checkout was checked via the NOTIFY_OT_COUPON_USES_PER_USER_CHECK Notifier in validateCouponUsesPerCustomer()

        $valid = null;
        $this->notify('NOTIFY_OT_COUPON_USES_PER_CUSTOMER_GUEST_CHECKOUT_CHECK', $coupon_details, $valid);

        return $valid;
    }

    /**
     * Check whether the coupon is valid for the customer's address, based on coupon zone-restrictions
     *
     * @param array $coupon_details
     * @return bool
     */
    protected function validateCouponForAddress($coupon_details)
    {
        global $db, $order;

        // 0 means no restrictions set
        if (empty($coupon_details['coupon_zone_restriction'])) {
            return true;
        }

        // determine zone restrictions based on Delivery or Billing address
        switch ($coupon_details['coupon_type']) {
            case 'S': // shipping
            case 'O': // amount off and free shipping
            case 'E': // percentage and Free Shipping
                // use delivery address
                $check_zone_country_id = $order->delivery['country']['id'];
                $check_zone_id = $order->delivery['zone_id'];
                break;
            case 'F': // amount
            case 'P': // percentage
            case 'G': // GV coupon
            default:
                // use billing address
                $check_zone_country_id = $order->billing['country']['id'];
                $check_zone_id = $order->billing['zone_id'];
                break;
        }

        $sql = "SELECT zone_id, zone_country_id
                FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                WHERE geo_zone_id = " . (int)$coupon_details['coupon_zone_restriction'] . "
                AND zone_country_id = " . (int)$check_zone_country_id . "
                ORDER BY zone_id";
        $results = $db->Execute($sql);

        foreach ($results as $result) {
            if ($result['zone_id'] < 1) {
                return true;
            }

            if ($result['zone_id'] == $check_zone_id) {
                return true;
            }
        }

        return false;
    }
    function help() {
       return array('link' => 'https://docs.zen-cart.com/user/order_total/coupons/');
    }
}
