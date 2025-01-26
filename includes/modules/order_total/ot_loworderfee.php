<?php
/**
 * ot_total order-total module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 26 Modified in v2.1.0-beta1 $
 */
class ot_loworderfee
{
    /**
     * $_check is used to check the configuration key set up
     * @var int
     */
    protected $_check;
    /**
     * $code determines the internal 'code' name used to designate "this" order total module
     * @var string
     */
    public $code;
    /**
     * $description is a soft name for this order total method
     * @var string
     */
    public $description;
    /**
     * $sort_order is the order priority of this order total module when displayed
     * @var int
     */
    public $sort_order;
    /**
     * $title is the displayed name for this order total method
     * @var string
     */
    public $title;
    /**
     * $enabled determines whether this module shows or not... during checkout.
     * @var boolean
     */
    public $enabled;
    /**
     * $output is an array of the display elements used on checkout pages
     * @var array
     */
    public $output = [];

    function __construct()
    {
        $this->code = 'ot_loworderfee';
        $this->title = MODULE_ORDER_TOTAL_LOWORDERFEE_TITLE;
        $this->description = MODULE_ORDER_TOTAL_LOWORDERFEE_DESCRIPTION;
        $this->enabled = $this->isEnabled();
        $this->sort_order = defined('MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER') ? MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER : null;
        if (null === $this->sort_order) return false;
    }

    function process()
    {
        global $order, $currencies;
        if ($this->enabled === false) {
            return;
        }
        switch (MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION) {
            case 'national':
                if ($order->delivery['country_id'] == STORE_COUNTRY) {
                    $pass = true;
                }
                break;
            case 'international':
                if ($order->delivery['country_id'] != STORE_COUNTRY) {
                    $pass = true;
                }
                break;
            case 'both':
                $pass = true;
                break;
            default:
                $pass = false;
                break;
        }

//        if ( ($pass == true) && ( ($order->info['total'] - $order->info['shipping_cost']) < MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER) ) {
        if ($pass == true && $order->info['subtotal'] < MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER) {
            $charge_it = 'true';
            $cart_content_type = $_SESSION['cart']->get_content_type();
            $gv_content_only = $_SESSION['cart']->gv_only();
            if ($cart_content_type === 'physical' || $cart_content_type === 'mixed') {
                $charge_it = 'true';
            } else {
                // check to see if everything is virtual, if so - skip the low order fee.
                if ($cart_content_type === 'virtual' && MODULE_ORDER_TOTAL_LOWORDERFEE_VIRTUAL === 'true') {
                    $charge_it = 'false';
                    if ($gv_content_only > 0 && MODULE_ORDER_TOTAL_LOWORDERFEE_GV === 'false') {
                        $charge_it = 'true';
                    }
                }

                if ($gv_content_only > 0 && MODULE_ORDER_TOTAL_LOWORDERFEE_GV === 'true') {
                    // check to see if everything is gift voucher, if so - skip the low order fee.
                    $charge_it = 'false';
                    if ($cart_content_type === 'virtual' && MODULE_ORDER_TOTAL_LOWORDERFEE_VIRTUAL === 'false') {
                        $charge_it = 'true';
                    }
                }
            }

            if ($charge_it === 'true') {
                $tax_address = zen_get_tax_locations();
                $tax = zen_get_tax_rate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $tax_address['country_id'], $tax_address['zone_id']);
                $tax_description = zen_get_tax_description(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $tax_address['country_id'], $tax_address['zone_id']);

                // calculate from flat fee or percentage
                if (str_ends_with(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, '%')) {
                    $low_order_fee = $order->info['subtotal'] * rtrim(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, '%') / 100;
                } else {
                    $low_order_fee = MODULE_ORDER_TOTAL_LOWORDERFEE_FEE;
                }

                $order->info['tax'] += zen_calculate_tax($low_order_fee, $tax);
                if (!isset($order->info['tax_groups'][$tax_description])) {
                    $order->info['tax_groups'][$tax_description] = 0;
                }
                $order->info['tax_groups'][$tax_description] += zen_calculate_tax($low_order_fee, $tax);
                $order->info['total'] += $low_order_fee + zen_calculate_tax($low_order_fee, $tax);
                if (DISPLAY_PRICE_WITH_TAX === 'true') {
                    $low_order_fee += zen_calculate_tax($low_order_fee, $tax);
                }

                $this->output[] = [
                    'title' => $this->title . ':',
                    'text' => $currencies->format($low_order_fee, true, $order->info['currency'], $order->info['currency_value']),
                    'value' => $low_order_fee,
                ];
            }
        }
    }

    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query =
                "SELECT configuration_value
                   FROM " . TABLE_CONFIGURATION . "
                  WHERE configuration_key = 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS'";

            $check_query = $db->Execute($check_query);
            $this->_check = $check_query->RecordCount();
        }

        return $this->_check;
    }

    function keys()
    {
        return [
            'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS',
            'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER',
            'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE',
            'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER',
            'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE',
            'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION',
            'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS',
            'MODULE_ORDER_TOTAL_LOWORDERFEE_VIRTUAL',
            'MODULE_ORDER_TOTAL_LOWORDERFEE_GV',
        ];
    }

    function install()
    {
        global $db;
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('This module is installed', 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 'true', '', '6', '1','zen_cfg_select_option([\'true\'], ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER', '400', 'Sort order of display.', '6', '2', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow Low Order Fee', 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE', 'false', 'Do you want to allow low order fees?', '6', '3', 'zen_cfg_select_option([\'true\', \'false\'], ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Order Fee For Orders Under', 'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER', '50', 'Add the low order fee to orders under this amount.', '6', '4', 'currencies->format', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Order Fee', 'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE', '5', 'For Percentage Calculation - include a % Example: 10%<br>For a flat amount just enter the amount - Example: 5 for $5.00', '6', '5', '', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Attach Low Order Fee On Orders Made', 'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION', 'both', 'Attach low order fee for orders sent to the set destination.', '6', '6', 'zen_cfg_select_option([\'national\', \'international\', \'both\'], ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS', '0', 'Use the following tax class on the low order fee.', '6', '7', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('No Low Order Fee on Virtual Products', 'MODULE_ORDER_TOTAL_LOWORDERFEE_VIRTUAL', 'false', 'Do not charge Low Order Fee when cart is Virtual Products Only', '6', '8', 'zen_cfg_select_option([\'true\', \'false\'], ', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('No Low Order Fee on Gift Vouchers', 'MODULE_ORDER_TOTAL_LOWORDERFEE_GV', 'false', 'Do not charge Low Order Fee when cart is Gift Vouchers Only', '6', '9', 'zen_cfg_select_option([\'true\', \'false\'], ', now())");
    }

    function remove()
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }

    public function isEnabled(): bool
    {
        if (!defined('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS') || !defined('MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE')) {
            return false;
        }
        if (MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS !== 'true' || MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE !== 'true') {
            return false;
        }
        return true;
    }
  }
