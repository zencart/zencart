<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 03 Modified in v2.1.0-beta1 $
 */

/**
 * Enter description here...
 *
 */
class table extends ZenShipping
{
    /**
     * constructor
     *
     * @return table
     */
    function __construct()
    {
        global $db;

        $this->code = 'table';
        $this->title = MODULE_SHIPPING_TABLE_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_TABLE_SORT_ORDER') ? MODULE_SHIPPING_TABLE_SORT_ORDER : null;
        if (null === $this->sort_order) {
            return false;
        }

        $this->icon = '';
        $this->tax_class = MODULE_SHIPPING_TABLE_TAX_CLASS;
        $this->tax_basis = MODULE_SHIPPING_TABLE_TAX_BASIS;
        // disable only when entire cart is free shipping
        if (zen_get_shipping_enabled($this->code)) {
            $this->enabled = (MODULE_SHIPPING_TABLE_STATUS == 'True');
        }

        if ($this->enabled) {
            // check MODULE_SHIPPING_TABLE_HANDLING_METHOD is in
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_TABLE_HANDLING_METHOD'");
            if ($check_query->EOF) {
                $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handling Per Order or Per Box', 'MODULE_SHIPPING_TABLE_HANDLING_METHOD', 'Order', 'Do you want to charge Handling Fee Per Order or Per Box?', '6', '0', 'zen_cfg_select_option(array(\'Order\', \'Box\'), ', now())");
            }
        }

        $this->update_status();
    }

    /**
     * Perform various checks to see whether this module should be visible
     */
    function update_status()
    {
        global $order, $db;
        if ($this->enabled === false || IS_ADMIN_FLAG === true) {
            return;
        }

        if ((int)MODULE_SHIPPING_TABLE_ZONE > 0) {
            $check_flag = false;
            $check = $db->Execute(
                "SELECT zone_id
                   FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                  WHERE geo_zone_id = " . (int)MODULE_SHIPPING_TABLE_ZONE . "
                    AND zone_country_id = " . (int)($order->delivery['country']['id'] ?? -1) . "
                  ORDER BY zone_id"
            );
            foreach ($check as $next_zone) {
                if ($next_zone['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($next_zone['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag === false) {
                $this->enabled = false;
            }
        }

        if ($this->enabled) {
            // -----
            // Give a watching observer the opportunity to disable the overall shipping module.
            //
            $this->notify('NOTIFY_SHIPPING_TABLE_UPDATE_STATUS', [], $this->enabled);
        }
    }

    /**
     *  Obtain quote from shipping system/calculations
     *
     * @param string $method
     * @return unknown
     */
    function quote($method = ''): array
    {
        global $order, $shipping_weight, $shipping_num_boxes, $total_count;

        // shipping adjustment
        switch (MODULE_SHIPPING_TABLE_MODE) {
            case 'price':
                $order_total = $_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices();
                break;
            case 'weight':
                $order_total = $shipping_weight;
                break;
            case ('item'):
                $order_total = $total_count - $_SESSION['cart']->free_shipping_items();
                break;
        }

        $order_total_amount = $_SESSION['cart']->show_total() - $_SESSION['cart']->free_shipping_prices();

        $table_cost = preg_split("/[:,]/", MODULE_SHIPPING_TABLE_COST);
        $size = count($table_cost);
        $shipping = 0;
        for ($i = 0, $n = $size; $i < $n; $i += 2) {
            if (round($order_total, 9) <= $table_cost[$i]) {
                if (str_ends_with($table_cost[$i + 1], '%')) {
                    $shipping = (rtrim($table_cost[$i + 1], '%') / 100) * $order_total_amount;
                } else {
                    $shipping = $table_cost[$i + 1];
                }
                break;
            }
        }

        $show_box_weight = '';
        if (MODULE_SHIPPING_TABLE_MODE === 'weight') {
            $shipping = $shipping * $shipping_num_boxes;
            // show boxes if weight
            switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
                case 0:
                    $show_box_weight = '';
                    break;
                case 1:
                    $show_box_weight = ' (' . $shipping_num_boxes . ' ' . TEXT_SHIPPING_BOXES . ')';
                    break;
                case 2:
                    $show_box_weight = ' (' . number_format($shipping_weight * $shipping_num_boxes, 2) . TEXT_SHIPPING_WEIGHT . ')';
                    break;
                default:
                    $show_box_weight = ' (' . $shipping_num_boxes . ' x ' . number_format($shipping_weight, 2) . TEXT_SHIPPING_WEIGHT . ')';
                    break;
            }
        }

        $this->quotes = [
            'id' => $this->code,
            'module' => MODULE_SHIPPING_TABLE_TEXT_TITLE . $show_box_weight,
            'methods' => [
                [
                    'id' => $this->code,
                    'title' => MODULE_SHIPPING_TABLE_TEXT_WAY,
                    'cost' => $shipping + (MODULE_SHIPPING_TABLE_HANDLING_METHOD === 'Box' ? MODULE_SHIPPING_TABLE_HANDLING * $shipping_num_boxes : MODULE_SHIPPING_TABLE_HANDLING),
                ],
            ],
        ];

        if ($this->tax_class > 0) {
            $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        }

        if (!empty($this->icon)) {
            $this->quotes['icon'] = zen_image($this->icon, $this->title);
        }

        return $this->quotes;
    }

    /**
     * Check to see whether module is installed
     *
     * @return unknown
     */
    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_TABLE_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    /**
     * Install the shipping module and its configuration settings
     *
     */
    function install(): void
    {
        global $db;
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Table Method', 'MODULE_SHIPPING_TABLE_STATUS', 'True', 'Do you want to offer table rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Shipping Table', 'MODULE_SHIPPING_TABLE_COST', '25:8.50,50:5.50,10000:0.00', 'The shipping cost is based on the total cost or weight of items or count of the items. Example: 25:8.50,50:5.50,etc.. Up to 25 charge 8.50, from there to 50 charge 5.50, etc<br>You can also use percentage amounts, such 25:8.50,35:5%,40:9.50,10000:7% to charge a percentage value of the Order Total', '6', '0', 'zen_cfg_textarea(', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Table Method', 'MODULE_SHIPPING_TABLE_MODE', 'weight', 'The shipping cost may be calculated based on the total weight of the items ordered, the total price of the items ordered, or the total number of items ordered.', '6', '0', 'zen_cfg_select_option(array(\'weight\', \'price\', \'item\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_TABLE_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handling Per Order or Per Box', 'MODULE_SHIPPING_TABLE_HANDLING_METHOD', 'Order', 'Do you want to charge Handling Fee Per Order or Per Box?', '6', '0', 'zen_cfg_select_option(array(\'Order\', \'Box\'), ', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_TABLE_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_TABLE_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br>Shipping - Based on customers Shipping Address<br>Billing Based on customers Billing address<br>Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_TABLE_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_TABLE_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
    }

    /**
     * Internal list of configuration keys used for configuration of the module
     *
     * @return unknown
     */
    function keys(): array
    {
        return [
            'MODULE_SHIPPING_TABLE_STATUS',
            'MODULE_SHIPPING_TABLE_COST',
            'MODULE_SHIPPING_TABLE_MODE',
            'MODULE_SHIPPING_TABLE_HANDLING',
            'MODULE_SHIPPING_TABLE_HANDLING_METHOD',
            'MODULE_SHIPPING_TABLE_TAX_CLASS',
            'MODULE_SHIPPING_TABLE_TAX_BASIS',
            'MODULE_SHIPPING_TABLE_ZONE',
            'MODULE_SHIPPING_TABLE_SORT_ORDER',
        ];
    }

    function help()
    {
        return ['link' => 'https://docs.zen-cart.com/user/shipping/table/'];
    }
}
