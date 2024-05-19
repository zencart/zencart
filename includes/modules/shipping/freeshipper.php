<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 May 15 Modified in v2.0.1 $
 */

//
class freeshipper extends ZenShipping
{
    function __construct()
    {
        $this->code = 'freeshipper';
        $this->title = MODULE_SHIPPING_FREESHIPPER_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_FREESHIPPER_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_FREESHIPPER_SORT_ORDER') ? MODULE_SHIPPING_FREESHIPPER_SORT_ORDER : null;
        if (null === $this->sort_order) {
            return false;
        }

        $this->icon = '';
        $this->tax_class = MODULE_SHIPPING_FREESHIPPER_TAX_CLASS;

        // enable only when entire cart is free shipping
//      if ($_SESSION['cart']->in_cart_check('product_is_always_free_shipping','1') == $_SESSION['cart']->count_contents()) {
        if (zen_get_shipping_enabled($this->code)) {
            $this->enabled = ((MODULE_SHIPPING_FREESHIPPER_STATUS == 'True') ? true : false);
        }

        $this->update_status();
    }

    /**
     * Perform various checks to see whether this module should be visible
     */
    function update_status()
    {
        global $order, $db;
        if (!$this->enabled) {
            return;
        }
        if (IS_ADMIN_FLAG === true) {
            return;
        }

        if ((int)MODULE_SHIPPING_FREESHIPPER_ZONE > 0) {
            $check_flag = false;
            $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_FREESHIPPER_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while (!$check->EOF) {
                if ($check->fields['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
                $check->MoveNext();
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }

        if ($this->enabled) {
            // -----
            // Give a watching observer the opportunity to disable the overall shipping module.
            //
            $this->notify('NOTIFY_SHIPPING_FREESHIPPER_UPDATE_STATUS', [], $this->enabled);
        }
    }

    function quote($method = ''): array
    {
        global $order;

        $this->quotes = [
            'id' => $this->code,
            'module' => MODULE_SHIPPING_FREESHIPPER_TEXT_TITLE,
            'methods' => [
                [
                    'id' => $this->code,
                    'title' => MODULE_SHIPPING_FREESHIPPER_TEXT_WAY,
                    'cost' => MODULE_SHIPPING_FREESHIPPER_COST + MODULE_SHIPPING_FREESHIPPER_HANDLING,
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

    function check()
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_FREESHIPPER_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    function get_configuration_errors()
    {
        if (!zen_check_for_misconfigured_downloads()) {
            return TEXT_DOWNLOADABLE_PRODUCTS_MISCONFIGURED;
        }
    }

    function install(): void
    {
        global $db;
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Free Shipping', 'MODULE_SHIPPING_FREESHIPPER_STATUS', 'True', 'Do you want to offer Free shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Free Shipping Cost', 'MODULE_SHIPPING_FREESHIPPER_COST', '0.00', 'What is the Shipping cost?', '6', '6', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_FREESHIPPER_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_FREESHIPPER_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_FREESHIPPER_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_FREESHIPPER_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
    }

    function help()
    {
        return ['link' => 'https://docs.zen-cart.com/user/shipping/free_shipping/'];
    }

    function keys(): array
    {
        return ['MODULE_SHIPPING_FREESHIPPER_STATUS', 'MODULE_SHIPPING_FREESHIPPER_COST', 'MODULE_SHIPPING_FREESHIPPER_HANDLING', 'MODULE_SHIPPING_FREESHIPPER_TAX_CLASS', 'MODULE_SHIPPING_FREESHIPPER_ZONE', 'MODULE_SHIPPING_FREESHIPPER_SORT_ORDER'];
    }
}
