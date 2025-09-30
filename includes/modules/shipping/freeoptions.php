<?php

/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 May 15 Modified in v2.0.1 $
 */
class freeoptions extends ZenShipping
{
    public function __construct()
    {
        $this->code = 'freeoptions';
        $this->title = MODULE_SHIPPING_FREEOPTIONS_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_FREEOPTIONS_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER') ? MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER : null;
        if (null === $this->sort_order) {
            return false;
        }

        $this->icon = '';
        $this->tax_class = MODULE_SHIPPING_FREEOPTIONS_TAX_CLASS;
        $this->tax_basis = MODULE_SHIPPING_FREEOPTIONS_TAX_BASIS;

        // disable only when entire cart is free shipping
        if (zen_get_shipping_enabled($this->code)) {
            $this->enabled = ((MODULE_SHIPPING_FREEOPTIONS_STATUS == 'True') ? true : false);
        } else {
            $this->enabled = false;
        }

        $this->update_status();
    }

    /**
     * Perform various checks to see whether this module should be visible
     */
    public function update_status()
    {
        if ($this->enabled === false || IS_ADMIN_FLAG === true) {
            return;
        }

        $this->checkEnabledForZone(MODULE_SHIPPING_FREEOPTIONS_ZONE);

        // -----
        // If still enabled, check to see if any "Free Options" should be presented to the customer.
        //
        if ($this->enabled === true) {
            $this->checkForFreeOptions();
        }
    }

    // -----
    // This function checks to see if the order's total, weight or number-of-items qualifies for the
    // Free Options shipping method.
    //
    protected function checkForFreeOptions()
    {
        global $order;
        
        // -----
        // Convert each of the min/max configured values into their floating-point equivalent.
        //
        $total_min = (float)MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN;
        $total_max = (float)MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX;
        $weight_min = (float)MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN;
        $weight_max = (float)MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX;
        $items_min = (float)MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN;
        $items_max = (float)MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX;

        // -----
        // First, see if any of the 3 options for free shipping are configured.  If none are configured, there's no quote
        // to be returned.
        //
        $freeoptions_total = ($total_min != 0 || $total_max != 0);
        $freeoptions_weight = ($weight_min != 0 || $weight_max != 0);
        $freeoptions_items = ($items_min != 0 || $items_max != 0);
        $this->debug[] = [$freeoptions_total, $freeoptions_weight, $freeoptions_items];

        $this->enabled = ($freeoptions_total === true || $freeoptions_weight === true || $freeoptions_items === true);
        if ($this->enabled === false) {
            return;
        }

        // -----
        // If freeoptions on the order's total is requested ...
        //
        if ($freeoptions_total === true) {
            $cart_total = $_SESSION['cart']->show_total();
            if ($total_min != 0 && $total_max != 0) {
                $freeoptions_total = ($cart_total >= $total_min && $cart_total <= $total_max);
            } elseif ($total_min != 0) {
                $freeoptions_total = ($cart_total >= $total_min);
            } else {
                $freeoptions_total = ($cart_total <= $total_max);
            }
            $this->debug[] = ['total', $cart_total, $freeoptions_total, MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN, MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX];
        }

        // -----
        // If freeoptions on the order's weight is requested ...
        //
        if ($freeoptions_weight === true) {
            $order_weight = round($_SESSION['cart']->show_weight(), 9);
            if ($weight_min != 0 && $weight_max != 0) {
                $freeoptions_weight = ($order_weight >= $weight_min && $order_weight <= $weight_max);
            } elseif ($weight_min != 0) {
                $freeoptions_weight = ($order_weight >= $weight_min);
            } else {
                $freeoptions_weight = ($order_weight <= $weight_max);
            }
            $this->debug[] = ['weight', $order_weight, $freeoptions_weight, MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN, MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX];
        }

        // -----
        // If freeoptions on the order's number of items is requested ...
        //
        if ($freeoptions_items === true) {
            $num_items = $_SESSION['cart']->count_contents();
            if ($items_min != 0 && $items_max != 0) {
                $freeoptions_items = ($num_items >= $items_min && $num_items <= $items_max);
            } elseif ($items_min != 0) {
                $freeoptions_items = ($num_items >= $items_min);
            } else {
                $freeoptions_items = ($num_items <= $items_max);
            }
            $this->debug[] = ['items', $num_items, $freeoptions_items, MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN, MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX];
        }

        // -----
        // The 'freeoptions' shipping method is enabled if at least one of the 3 configured options
        // are met.
        //
        $this->enabled = ($freeoptions_total === true || $freeoptions_weight === true || $freeoptions_items === true);

        if ($this->enabled) {
            // -----
            // Give a watching observer the opportunity to disable the overall shipping module.
            //
            $this->notify('NOTIFY_SHIPPING_FREEOPTIONS_UPDATE_STATUS', [], $this->enabled);
        }
    }

    // -----
    // Return the "Free Options" quote, as requested.
    //
    public function quote($method = ''): array
    {
        global $order;

        // -----
        // Note: Only requested by the shipping class if previous processing has indicated that the
        // module is enabled!
        //
        $this->quotes = [
            'id' => $this->code,
            'module' => MODULE_SHIPPING_FREEOPTIONS_TEXT_TITLE,
            'methods' => [
                [
                    'id' => $this->code,
                    'title' => MODULE_SHIPPING_FREEOPTIONS_TEXT_WAY,
                    'cost' => (float)MODULE_SHIPPING_FREEOPTIONS_COST + (float)MODULE_SHIPPING_FREEOPTIONS_HANDLING,
                ],
            ],
        ];

        if ($this->tax_class > '0') {
            $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        }

        if (!empty($this->icon)) {
            $this->quotes['icon'] = zen_image($this->icon, $this->title);
        }

        return $this->quotes;
    }

    public function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_SHIPPING_FREEOPTIONS_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    public function get_configuration_errors()
    {
        if (!zen_check_for_misconfigured_downloads()) {
            return TEXT_DOWNLOADABLE_PRODUCTS_MISCONFIGURED;
        }
    }

    public function install(): void
    {
        global $db;
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Free Options Shipping', 'MODULE_SHIPPING_FREEOPTIONS_STATUS', 'True', 'Free Options is used to display a Free Shipping option when other Shipping Modules are displayed.
It can be based on: Always show, Order Total, Order Weight or Order Item Count.
The Free Options module does not show when Free Shipper is displayed.<br><br>
Setting Total to >= 0.00 and <= nothing (leave blank) will activate this module to show with all shipping modules, except for Free Shipping - freeshipper.<br><br>
NOTE: Leaving all settings for Total, Weight and Item count blank will deactivate this module.<br><br>
NOTE: Free Shipping Options does not display if Free Shipping is used based on 0 weight is Free Shipping.
See: freeshipper<br><br>Do you want to offer per freeoptions rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Shipping Cost', 'MODULE_SHIPPING_FREEOPTIONS_COST', '0.00', 'The shipping cost will be $0.00', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Handling Fee', 'MODULE_SHIPPING_FREEOPTIONS_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Total >=', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN', '0.00', 'Free Shipping when Total >=', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Total <=', 'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX', '', 'Free Shipping when Total <=', '6', '0', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Weight >=', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN', '', 'Free Shipping when Weight >=', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Weight <=', 'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX', '', 'Free Shipping when Weight <=', '6', '0', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Item Count >=', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN', '', 'Free Shipping when Item Count >=', '6', '0', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Item Count <=', 'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX', '', 'Free Shipping when Item Count <=', '6', '0', now())");

        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Tax Class', 'MODULE_SHIPPING_FREEOPTIONS_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Tax Basis', 'MODULE_SHIPPING_FREEOPTIONS_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br>Shipping - Based on customers Shipping Address<br>Billing Based on customers Billing address<br>Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Shipping Zone', 'MODULE_SHIPPING_FREEOPTIONS_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
    }

    public function help()
    {
        return ['link' => 'https://docs.zen-cart.com/user/shipping/free_shipping/'];
    }

    public function keys(): array
    {
        return [
            'MODULE_SHIPPING_FREEOPTIONS_STATUS',
            'MODULE_SHIPPING_FREEOPTIONS_COST',
            'MODULE_SHIPPING_FREEOPTIONS_HANDLING',
            'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MIN',
            'MODULE_SHIPPING_FREEOPTIONS_TOTAL_MAX',
            'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MIN',
            'MODULE_SHIPPING_FREEOPTIONS_WEIGHT_MAX',
            'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MIN',
            'MODULE_SHIPPING_FREEOPTIONS_ITEMS_MAX',
            'MODULE_SHIPPING_FREEOPTIONS_TAX_CLASS',
            'MODULE_SHIPPING_FREEOPTIONS_TAX_BASIS',
            'MODULE_SHIPPING_FREEOPTIONS_ZONE',
            'MODULE_SHIPPING_FREEOPTIONS_SORT_ORDER',
        ];
    }
}
