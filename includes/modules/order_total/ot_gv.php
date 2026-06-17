<?php
/**
 * ot_gv order-total module
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Dec 13 Modified in v2.2.1 $
 */
/**
 * Enter description here...
 *
 * @since ZC v1.0.3
 */
class ot_gv
{
    /**
     * $_check is used to check that configuration keys are set up
     */
    protected int $_check = 0;
    /**
     * $calculate_tax determines how tax should be applied to coupon Standard, Credit Note, None
     */
    protected string $calculate_tax;
    /**
     * $checkbox is the output to request the amount of gift vouchers the user wants to redeem
     */
    protected string $checkbox = '';
    /**
     * $code determines the internal 'code' name used to designate "this" order total module
     */
    public string $code = 'ot_gv';
    /**
     * $credit_class flag to indicate order totals method is a credit class
     */
    public bool $credit_class = true;
    /**
     * $credit_tax: if 'true' tax is to be calculated on purchased GVs
     */
    protected string $credit_tax;
    /**
     * $deduction amount of deduction calculated/afforded while being applied to an order
     */
    protected float|int $deduction = 0;
    /**
     * $description is a soft name for this order total method
     */
    public string $description = 'Gift Voucher Handling';
    /**
     * $include_shipping allow shipping costs to be discounted by coupon if 'true'
     */
    public string $include_shipping = 'false';
    /**
     * $include_tax allow tax to be discounted by coupon if 'true'
     */
    public string $include_tax = 'false';
    /**
     * $sort_order is the order priority of this order total module when displayed
     */
    public ?int $sort_order = null;
    /**
     * $tax_class is the Tax class to be applied to the coupon cost
     */
    public int $tax_class = 0;
    /**
     * $title is the displayed name for this order total method
     */
    public string $title = 'Gift Vouchers';
    /**
     * $output is an array of the display elements used on checkout pages
     */
    public array $output = [];

    /**
     * Gift Vouchers
     */
    public function __construct()
    {
        global $currencies;
        $this->code = 'ot_gv';
        $this->title = MODULE_ORDER_TOTAL_GV_TITLE;
        $this->description = MODULE_ORDER_TOTAL_GV_DESCRIPTION;

        $sort_order = zen_config('MODULE_ORDER_TOTAL_GV_SORT_ORDER');
        if (null === $sort_order) {
            $this->sort_order = null;
            return;
        }
        $this->sort_order = (int)$sort_order;

        $this->include_shipping = zen_config('MODULE_ORDER_TOTAL_GV_INC_SHIPPING');
        $this->include_tax = zen_config('MODULE_ORDER_TOTAL_GV_INC_TAX');
        $this->calculate_tax = zen_config('MODULE_ORDER_TOTAL_GV_CALC_TAX');
        $this->credit_tax = zen_config('MODULE_ORDER_TOTAL_GV_CREDIT_TAX');
        $this->tax_class  = (int)zen_config('MODULE_ORDER_TOTAL_GV_TAX_CLASS');

        $this->credit_class = true;

        if (!(isset($_SESSION['cot_gv']) && !empty(ltrim($_SESSION['cot_gv'], ' 0'))) || $_SESSION['cot_gv'] == '0') {
            $_SESSION['cot_gv'] = '0.00';
        }

        if (IS_ADMIN_FLAG !== true && zen_is_logged_in() && !zen_in_guest_checkout()) {
            $cot_gv = number_format($currencies->normalizeValue($_SESSION['cot_gv']), 2);
            $gv_account_balance = $this->user_has_gv_account($_SESSION['customer_id']);
            $this->checkbox =
                MODULE_ORDER_TOTAL_GV_USER_PROMPT .
                '<input type="text" size="6" onkeyup="submitFunction()" name="cot_gv" value="' . $cot_gv . '" onfocus="if (this.value == \'' . $cot_gv . '\') this.value = \'\';">' .
                (
                    $gv_account_balance > 0
                    ? '<br>' . MODULE_ORDER_TOTAL_GV_USER_BALANCE . $currencies->format($gv_account_balance)
                    : ''
                );
        }

        $this->output = [];
        if (IS_ADMIN_FLAG === true) {
            if ($this->include_tax === 'true' && $this->calculate_tax !== 'None') {
                $this->title .= '<span class="alert">' . MODULE_ORDER_TOTAL_GV_INCLUDE_ERROR . '</span>';
            }
        }
    }

    /**
     * Calculate totals for display
     *
     * @since ZC v1.0.3
     */
    public function process(): void
    {
        global $order, $currencies;

        if (!empty($_SESSION['cot_gv'])) {
            $order_total_details = $this->get_order_total_details();
            $od_amount = $this->calculate_deductions($order_total_details);
            $this->deduction = $od_amount['total'];
            if ($od_amount['total'] > 0) {
                $tax = 0;
                foreach ($order->info['tax_groups'] as $key => $value) {
                    if (isset($od_amount['tax_groups'][$key])) {
                        $order->info['tax_groups'][$key] -= $od_amount['tax_groups'][$key];
                        $tax += $od_amount['tax_groups'][$key];
                    }
                }
                $order->info['total'] -= $od_amount['total'];
                if ($this->calculate_tax === 'Standard') {
                    $order->info['total'] -= $tax;
                }
                if ($order->info['total'] < 0) {
                    $order->info['total'] = 0;
                }
                $order->info['tax'] -= $od_amount['tax'];
                // prepare order-total output for display and storing to invoice
                $this->output[] = [
                    'title' => $this->title . ':',
                    // &#8209; is a non-break-hyphen so displays with number
                    'text' => '&#8209;' . $currencies->format($od_amount['total']),
                    'value' => $od_amount['total'],
                ];
            }
        }
    }

    /**
     * Reset any GV values, effectively cancelling all GV's applied during current login session
     * @since ZC v1.3.0
     */
    public function clear_posts(): void
    {
        unset($_SESSION['cot_gv']);
    }

    /**
     * Check for validity of redemption amounts and recalculate order totals to include proposed GV redemption deductions
     *
     * @TODO - Per order_total class, this function is not used. See process() instead.
     * @since ZC v1.0.3
     */
    public function pre_confirmation_check($order_total): int|float
    {
        global $order, $currencies, $messageStack;

        // clean out negative values and strip common currency symbols
        $_SESSION['cot_gv'] = preg_replace('/[^0-9.,%]/', '', $_SESSION['cot_gv']);

        if ($_SESSION['cot_gv'] > 0) {
            // if cot_gv value contains any invalid characters, throw error
            if (preg_match('/[^0-9\,.]/', trim($_SESSION['cot_gv']))) {
                $messageStack->add_session('checkout_payment', TEXT_INVALID_REDEEM_AMOUNT, 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }

            // if requested redemption amount is greater than value of credits on account, throw error
            if ($_SESSION['cot_gv'] > $currencies->value($this->user_has_gv_account($_SESSION['customer_id']))) {
                $messageStack->add_session('checkout_payment', TEXT_INVALID_REDEEM_AMOUNT, 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }

            $od_amount = $this->calculate_deductions($this->get_order_total_details());
            $order->info['total'] = $order->info['total'] - $od_amount['total'];
            if (zen_config('DISPLAY_PRICE_WITH_TAX') !== 'true') {
                $order->info['total'] -= $od_amount['tax'];
            }
            return $od_amount['total'] + $od_amount['tax'];
        }
        return 0;
    }

    /**
     * if customer has a GV balance, then we display the input field to allow entry of desired GV redemption amount
     * @since ZC v1.0.3
     */
    public function use_credit_amount(): string
    {
        if ($this->user_has_gv_account($_SESSION['customer_id'])) {
            return $this->checkbox;
        }
        return '';
    }

    /**
     * queue or release newly-purchased GV's
     * @since ZC v1.0.3
     */
    public function update_credit_account($i): void
    {
        global $db, $order, $insert_id;

        $ordered_product = $order->products[$i];

        // only act on newly-purchased gift certificates
        if (str_starts_with($ordered_product['model'] ?? '', 'GIFT')) {
            // determine how much GV was purchased
            // check if GV was purchased on Special
            $gv_original_price = (new Product((int)$ordered_product['id']))->get('products_price');
            // if prices differ assume Special and get Special Price

            // Do not use this on GVs Priced by Attribute
            if (zen_config('MODULE_ORDER_TOTAL_GV_SPECIAL') === 'true'
              && $gv_original_price != 0 && $gv_original_price != $ordered_product['final_price']
              && !zen_get_products_price_is_priced_by_attributes((int)$ordered_product['id'])
            ) {
                $gv_order_amount = $gv_original_price * $ordered_product['qty'];
            } else {
                $gv_order_amount = $ordered_product['final_price'] * $ordered_product['qty'];
            }

            // if tax is to be calculated on purchased GVs, calculate it
            if ($this->credit_tax === 'true') {
                $gv_order_amount *= (100 + $ordered_product['tax']) / 100;
            }

            if (zen_config('MODULE_ORDER_TOTAL_GV_QUEUE') === 'false') {
                // GV_QUEUE is false so release amount to account immediately
                $gv_result = $this->user_has_gv_account($_SESSION['customer_id']);
                $customer_gv = false;
                $total_gv_amount = 0;
                if ($gv_result) {
                    $total_gv_amount = $gv_result;
                    $customer_gv = true;
                }
                $total_gv_amount += $gv_order_amount;
                if ($customer_gv === true) {
                    $db->Execute("UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET amount = '" . $total_gv_amount . "' WHERE customer_id = " . (int)$_SESSION['customer_id'] . " LIMIT 1");
                } else {
                    $db->Execute("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) VALUES (" . (int)$_SESSION['customer_id'] . ", '" . $total_gv_amount . "')");
                }
            } else {
                // GV_QUEUE is true - so queue the gv for release by store owner
                $db->Execute(
                    "INSERT INTO " . TABLE_COUPON_GV_QUEUE . "
                        (customer_id, order_id, amount, date_created, ipaddr)
                     VALUES
                        (" . (int)$_SESSION['customer_id'] . ", " . (int)$insert_id . ", '" . $gv_order_amount . "', NOW(), '" . $_SERVER['REMOTE_ADDR'] . "')"
                );
            }
        }
    }

    /**
    * check system to see if GVs should be made available or not. If true, then supply GV-selection fields on checkout pages
     * @since ZC v1.0.3
    */
    public function credit_selection(): array
    {
        global $db, $order;

        // -----
        // Don't offer a GV payment for orders that contain **only** GV's!
        //
        $only_gvs_in_order = true;
        foreach ($order->products as $next_product) {
            if (str_starts_with($next_product['model'] ?? '', 'GIFT') === false) {
                $only_gvs_in_order = false;
                break;
            }
        }
        if ($only_gvs_in_order === true) {
            return [];
        }

        $selection = [];
        $gv_query = $db->Execute("SELECT coupon_id FROM " . TABLE_COUPONS . " WHERE coupon_type = 'G' AND coupon_active = 'Y' LIMIT 1");
        // checks to see if any GVs are in the system and active or if the current customer has any GV balance
        if (!$gv_query->EOF || $this->use_credit_amount()) {
            $selection = [
                'id' => $this->code,
                'module' => $this->title,
                'redeem_instructions' => MODULE_ORDER_TOTAL_GV_REDEEM_INSTRUCTIONS,
                'checkbox' => $this->use_credit_amount(),
                'fields' => [
                    [
                        'title' => MODULE_ORDER_TOTAL_GV_TEXT_ENTER_CODE,
                        'field' => zen_draw_input_field('gv_redeem_code', '', 'id="disc-' . $this->code . '" onkeyup="submitFunction(0,0)"'),
                        'tag' => 'disc-' . $this->code,
                    ],
                ],
            ];
        }
        return $selection;
    }

    /**
     * Verify that the customer has entered a valid redemption amount, and return the amount that can be applied to this order
     * @since ZC v1.0.3
     */
    public function apply_credit(): float|int|null
    {
        global $db, $order;

        $gv_payment_amount = 0;
        // check for valid redemption amount vs available credit for current customer
        if (!empty($_SESSION['cot_gv'])) {
            $gv_result = $db->Execute("SELECT amount FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE customer_id = " . (int)$_SESSION['customer_id']);
            // obtain final "deduction" amount
            $gv_payment_amount = $this->deduction;
            // determine amount of GV to redeem based on available balance minus qualified/calculated deduction suitable to this order
            $gv_amount = (!$gv_result->EOF ? $gv_result->fields['amount'] : 0) - $gv_payment_amount;
            // reduce customer's GV balance by the amount redeemed
            $db->Execute("UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET amount = '" . $gv_amount . "' WHERE customer_id = " . (int)$_SESSION['customer_id'], 1);
        }

        // clear GV redemption flag since it's already been claimed and deducted
        $_SESSION['cot_gv'] = false;

        // send back the amount of GV used for payment on this order
        return $gv_payment_amount;
    }

    /**
     * Check to see if redemption code has been entered and redeem if valid
     * @since ZC v1.0.3
     */
    public function collect_posts(): void
    {
        global $db, $currencies, $messageStack;

        // if we have no GV amount selected, set it to 0
        // if requested redemption amount is greater than value of credits on account, throw error
        if ($_SESSION['cot_gv'] > $currencies->value($this->user_has_gv_account($_SESSION['customer_id']))) {
            $messageStack->add_session('checkout_payment', TEXT_INVALID_REDEEM_AMOUNT . ' - ' . number_format($_SESSION['cot_gv'], 2), 'error');
            $_SESSION['cot_gv'] = 0.00;
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }
        if (isset($_POST['cot_gv']) && $_POST['cot_gv'] == 0) {
            $_SESSION['cot_gv'] = '0.00';
        }

        if (!empty($_POST['submit_redeem_x']) && empty($_POST['gv_redeem_code'])) {
            $messageStack->add_session('checkout_payment', ERROR_NO_REDEEM_CODE, 'error');
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }

        // if we have a GV redemption code submitted, process it
        if (!empty($_POST['gv_redeem_code'])) {
            // check for validity
            $_POST['gv_redeem_code'] = preg_replace('/[^0-9a-zA-Z]/', '', $_POST['gv_redeem_code']);
            $gv_result = $db->Execute(
                "SELECT coupon_id, coupon_type, coupon_amount
                   FROM " . TABLE_COUPONS . "
                  WHERE coupon_code = '" . zen_db_prepare_input($_POST['gv_redeem_code']) . "'
                    AND coupon_type = 'G'"
            );
            if (!$gv_result->EOF) {
                $redeem_query = $db->Execute("SELECT * FROM " . TABLE_COUPON_REDEEM_TRACK . " WHERE coupon_id = '" . (int)$gv_result->fields['coupon_id'] . "'");
                // if already redeemed, throw error
                if (!$redeem_query->EOF && $gv_result->fields['coupon_type'] === 'G') {
                    $messageStack->add_session('checkout_payment', ERROR_NO_INVALID_REDEEM_GV, 'error');
                    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
                }
            } else {
                // if not valid redemption code, throw error
                $messageStack->add_session('checkout_payment', ERROR_NO_INVALID_REDEEM_GV, 'error');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }

            // if valid, add redeemed amount to customer's GV balance and mark as redeemed
            if ($gv_result->fields['coupon_type'] === 'G') {
                $gv_amount = $gv_result->fields['coupon_amount'];
                $coupon_id = (int)$gv_result->fields['coupon_id'];
                // Things to set
                // ip address of claimant
                // customer id of claimant
                // date
                // redemption flag
                // now update customer account with gv_amount
                $gv_amount_result = $db->Execute("SELECT amount FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE customer_id = " . (int)$_SESSION['customer_id'] . " LIMIT 1");
                $customer_gv = false;
                $total_gv_amount = $gv_amount;
                if (!$gv_amount_result->EOF) {
                    $total_gv_amount = $gv_amount_result->fields['amount'] + $gv_amount;
                    $customer_gv = true;
                }
                $db->Execute("UPDATE " . TABLE_COUPONS . " SET coupon_active = 'N' WHERE coupon_id = " . $coupon_id, 1);
                $db->Execute(
                    "INSERT INTO  " . TABLE_COUPON_REDEEM_TRACK . "
                        (coupon_id, customer_id, redeem_date, redeem_ip)
                     VALUES
                        (" . $coupon_id . ", " . (int)$_SESSION['customer_id'] . ", now(), '" . $_SERVER['REMOTE_ADDR'] . "')"
                );
                if ($customer_gv === true) {
                    // already has gv_amount so update
                    $db->Execute("UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET amount = '" . $total_gv_amount . "' WHERE customer_id = " . (int)$_SESSION['customer_id'] . " LIMIT 1");
                } else {
                    // no gv_amount so insert
                    $db->Execute(
                        "INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . "
                            (customer_id, amount)
                         VALUES
                            (" . (int)$_SESSION['customer_id'] . ", '" . $total_gv_amount . "')"
                    );
                }
                $messageStack->add_session('redemptions', ERROR_REDEEMED_AMOUNT . $currencies->format($gv_amount), 'success');
                zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
            }
        }
    }

    /**
     * Calculate GV claim amount (GV amounts are always based on the STORE's default currency value)
     * @since ZC v1.0.3
     */
    protected function calculate_credit($save_total_cost): float|int
    {
        global $db, $order, $currencies;

        // calculate value based on default currency
        $gv_payment_amount = $currencies->normalizeValue($_SESSION['cot_gv']);
        $gv_payment_amount = $currencies->value($gv_payment_amount, true, zen_config('DEFAULT_CURRENCY'));
        $full_cost = $save_total_cost - $gv_payment_amount;
        if ($full_cost < 0) {
            $gv_payment_amount = $save_total_cost;
        }
        return zen_round($gv_payment_amount, 2);
    }

    /**
     * @since ZC v1.3.8
     */
    protected function calculate_deductions($order_total): array
    {
        global $order;

        $od_amount = [];
        $deduction = $this->calculate_credit($order_total['total']);
        $od_amount['total'] = $deduction;
        switch ($this->calculate_tax) {
            case 'None':
                $remainder = $order->info['total'] - $od_amount['total'];
                $tax_deduct = $order->info['tax'] - $remainder;
                // division by 0
                if ($order->info['tax'] <= 0) {
                    $ratio_tax = 0;
                } else {
                    $ratio_tax = $tax_deduct / $order->info['tax'];
                }
                $tax_deduct = 0;
                $od_amount['tax'] = $tax_deduct;
                break;
            case 'Standard':
                if ($od_amount['total'] >= $order_total['total']) {
                    $ratio = 1;
                } else {
                    $ratio = ($order_total['total'] > 0) ? ($od_amount['total'] / $order_total['total']) : 0;
                }
                $tax_deduct = 0;
                foreach ($order_total['tax_groups'] as $key => $value) {
                    $od_amount['tax_groups'][$key] = $value * $ratio;
                    $tax_deduct += $od_amount['tax_groups'][$key];
                }
                $od_amount['tax'] = $tax_deduct;
                break;
            case 'Credit Note':
                $od_amount['total'] = $deduction;
                $tax_rate = zen_get_tax_rate($this->tax_class);
                $od_amount['tax'] = zen_calculate_tax($deduction, $tax_rate);
                $tax_description = zen_get_tax_description($this->tax_class);
                $od_amount['tax_groups'][$tax_description] = $od_amount['tax'];
                break;
            default:
                break;
        }
        return $od_amount;
    }

    /**
     * Check to see whether the current customer has a GV balance available
     * Returns amount of GV balance on account
     * @since ZC v1.0.3
     */
    protected function user_has_gv_account($c_id): float|int|string
    {
        global $db;
        $gv_result = $db->ExecuteNoCache("SELECT amount FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE customer_id = " . (int)$c_id . " LIMIT 1");
        if (!$gv_result->EOF) {
            return $gv_result->fields['amount'];
        }
        return 0; // use 0 because 'false' was preventing checkout_payment from continuing
    }

    /**
     * Recalculates base order-total amount for use in deduction calculations
     * @since ZC v1.0.3
     */
    protected function get_order_total()
    {
        return $this->get_order_total_details()['total'];
    }

    /**
     * Recalculates order-total details for use in deduction calculations.
     *
     * @since ZC v3.0.0
     */
    protected function get_order_total_details(): array
    {
        global $order;

        $order_total = $order->info['total'];
        $tax_groups = $order->info['tax_groups'] ?? [];
        $shipping_tax_details = $this->get_shipping_tax_details();
        $shipping_tax_amount = $shipping_tax_details['amount'];
        $shipping_cost = (float)$order->info['shipping_cost'];
        $shipping_cost_ex_tax = $shipping_cost;

        if (zen_config('DISPLAY_PRICE_WITH_TAX') === 'true' && $shipping_tax_amount > 0) {
            $shipping_cost_ex_tax -= $shipping_tax_amount;
        }

        // if we are not supposed to include tax in credit calculations, subtract it out
        if ($this->include_tax !== 'true') {
            $order_total -= $order->info['tax'];
        }

        // if we are not supposed to include shipping amount in credit calcs, subtract it out
        if ($this->include_shipping !== 'true') {
            if ($this->include_tax === 'true') {
                $order_total -= $shipping_cost;
                if (zen_config('DISPLAY_PRICE_WITH_TAX') !== 'true' && $shipping_tax_amount > 0) {
                    $order_total -= $shipping_tax_amount;
                }
            } else {
                $order_total -= $shipping_cost_ex_tax;
            }

            if ($shipping_tax_details['description'] !== '' && isset($tax_groups[$shipping_tax_details['description']])) {
                $tax_groups[$shipping_tax_details['description']] -= $shipping_tax_amount;
                if ($tax_groups[$shipping_tax_details['description']] < 0) {
                    $tax_groups[$shipping_tax_details['description']] = 0;
                }
            }
        }

        // check gv_amount in cart and do not allow GVs to pay for GVs
        $chk_gv_amount = 0;
        foreach ($_SESSION['cart']->get_products() as $next_product) {
            if (str_starts_with($next_product['model'] ?? '', 'GIFT')) {
                // determine how much GV was purchased
                $chk_gv_amount += ($next_product['price'] * $next_product['quantity']);
            }
        }

        // reduce Order Total less GVs
        return [
            'total' => $order_total - $chk_gv_amount,
            'tax_groups' => $tax_groups,
        ];
    }

    /**
     * Calculates shipping-tax details for use in deduction calculations.
     *
     * @since ZC v3.0.0
     */
    protected function get_shipping_tax_details(): array
    {
        global $order;

        static $shipping_tax_details;

        if (isset($shipping_tax_details)) {
            return $shipping_tax_details;
        }

        $shipping_tax_details = [
            'amount' => !empty($order->info['shipping_tax']) ? (float)$order->info['shipping_tax'] : 0.0,
            'description' => !empty($_SESSION['shipping_tax_description']) ? (string)$_SESSION['shipping_tax_description'] : '',
        ];

        if ($shipping_tax_details['amount'] > 0 && $shipping_tax_details['description'] !== '') {
            return $shipping_tax_details;
        }

        if (empty($order->info['shipping_cost']) || empty($_SESSION['shipping']['id'])) {
            return $shipping_tax_details;
        }

        $module = substr((string)$_SESSION['shipping']['id'], 0, strpos((string)$_SESSION['shipping']['id'], '_'));
        if ($module === '' || $module === 'free' || empty($GLOBALS[$module]->tax_class) || (int)$GLOBALS[$module]->tax_class <= 0) {
            return $shipping_tax_details;
        }

        $shipping_tax_basis = $GLOBALS[$module]->tax_basis ?? zen_config('STORE_SHIPPING_TAX_BASIS');
        $store_zone = zen_config('STORE_ZONE');
        if ($shipping_tax_basis === 'Billing') {
            $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
            $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
        } elseif ($shipping_tax_basis === 'Shipping') {
            $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
            $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        } elseif ($store_zone == $order->billing['zone_id']) {
            $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
            $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
        } elseif ($store_zone == $order->delivery['zone_id']) {
            $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
            $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        } else {
            $shipping_tax = 0;
            $shipping_tax_description = '';
        }

        if ($shipping_tax_details['amount'] <= 0) {
            $shipping_tax_details['amount'] = zen_calculate_tax($order->info['shipping_cost'], $shipping_tax);
        }
        $shipping_tax_details['description'] = $shipping_tax_description;

        return $shipping_tax_details;
    }

    /**
     * Check whether the module is configured as enabled.
     *
     * @since ZC v1.0.3
     */
    public function check(): int
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_ORDER_TOTAL_GV_STATUS'");
            $this->_check = $check_query->RecordCount();
        }

        if ($this->_check) {
            // move switch for admin-display of queue in header from lang file to module settings
            $result = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN'");
            if ($result->EOF) {
                $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Queue in Admin header?', 'MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN', 'true', 'Show Queue button on all pages of Admin?<br>(Will auto-hide if nothing in queue, and will auto-display on \'Orders\' screen, regardless of this setting)', 6, 3,'zen_cfg_select_option([\'true\', \'false\'], ', now())");
            }
            $result = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_ORDER_TOTAL_GV_SPECIAL'");
            if ($result->EOF) {
                $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Gift Voucher Specials', 'MODULE_ORDER_TOTAL_GV_SPECIAL', 'false', 'Do you want to allow Gift Voucher to be placed on Special?', 6, 3, 'zen_cfg_select_option([\'true\', \'false\'], ', now())");
            }
        }

        return $this->_check;
    }

    /**
     * Configuration keys used for this module.
     *
     * @since ZC v1.0.3
     */
    public function keys(): array
    {
        return [
            'MODULE_ORDER_TOTAL_GV_STATUS',
            'MODULE_ORDER_TOTAL_GV_SORT_ORDER',
            'MODULE_ORDER_TOTAL_GV_QUEUE',
            'MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN',
            'MODULE_ORDER_TOTAL_GV_INC_SHIPPING',
            'MODULE_ORDER_TOTAL_GV_INC_TAX',
            'MODULE_ORDER_TOTAL_GV_CALC_TAX',
            'MODULE_ORDER_TOTAL_GV_TAX_CLASS',
            'MODULE_ORDER_TOTAL_GV_CREDIT_TAX',
            'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID',
            'MODULE_ORDER_TOTAL_GV_SPECIAL',
        ];
    }

    /**
     * Install the module's configuration settings.
     *
     * @since ZC v1.0.3
     */
    public function install(): void
    {
        global $db;
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_GV_STATUS', 'true', '', 6, 1,'zen_cfg_select_option([\'true\'], ', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', '840', 'Sort order of display.', 6, 2, now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Queue Purchases', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'true', 'Do you want to queue purchases of the Gift Voucher?', 6, 3,'zen_cfg_select_option([\'true\', \'false\'], ', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Show Queue in Admin header?', 'MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN', 'true', 'Show Queue button on all pages of Admin?<br>(Will auto-hide if nothing in queue, and will auto-display on \'Orders\' screen, regardless of this setting)', 6, 3,'zen_cfg_select_option([\'true\', \'false\'], ', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Include Shipping', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'true', 'Include Shipping in calculation', 6, 5, 'zen_cfg_select_option([\'true\', \'false\'], ', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Include Tax', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'false', 'Include Tax in calculation.', 6, 6, 'zen_cfg_select_option([\'true\', \'false\'], ', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None', 'Re-Calculate Tax', 6, 7,'zen_cfg_select_option([\'None\', \'Standard\', \'Credit Note\'], ', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Tax Class', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', '0', 'Use the following tax class when treating Gift Voucher as Credit Note.', 6, 0, 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) VALUES ('Credit including Tax', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX', 'false', 'Add tax to purchased Gift Voucher when crediting to Account', 6, 8, 'zen_cfg_select_option([\'true\', \'false\'], ', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('Set Order Status', 'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID', '0', 'Set the status of orders made where GV covers full payment', 6, 0, 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())"
        );
        $db->Execute(
            "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Gift Voucher Specials', 'MODULE_ORDER_TOTAL_GV_SPECIAL', 'false', 'Do you want to allow Gift Voucher to be placed on Special?', 6, 3, 'zen_cfg_select_option([\'true\', \'false\'], ', now())"
        );
    }

    /**
     * @since ZC v1.5.8
     */
    public function help(): array
    {
        return ['link' => 'https://docs.zen-cart.com/user/order_total/gift_certificates/'];
    }

    /**
     * Clear the module's configuration settings, which effectively removes the module from use.
     *
     * @since ZC v1.0.3
     */
    public function remove(): void
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }
}
