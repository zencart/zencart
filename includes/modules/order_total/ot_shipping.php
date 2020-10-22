<?php
/**
 * ot_shipping order-total module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Aug 21 Modified in v1.5.7 $
 */
class ot_shipping extends base
{
    public    $code,
              $title,
              $description,
              $sort_order,
              $output;
    protected $_check;

    public function __construct() 
    {
        global $order, $currencies;
        $this->code = 'ot_shipping';
        $this->title = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
        $this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
        $this->sort_order = defined('MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER') ? (int)MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER : null;
        if (null === $this->sort_order) {
            return false;
        }
        $this->output = array();
    }

    public function process() 
    {
        global $order, $currencies;
 
        $this->output = array();
        unset($_SESSION['shipping_tax_description']);
        
        if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
            $pass = false;
            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
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
                    break;
            }

            if ($pass &&  ($order->info['total'] - $order->info['shipping_cost']) >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
                $order->info['shipping_method'] = $this->title;
                $order->info['total'] -= $order->info['shipping_cost'];
                $order->info['shipping_cost'] = 0;
            }
        }
        $module = (isset($_SESSION['shipping']) && isset($_SESSION['shipping']['id'])) ? substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_')) : '';
        if (is_object($order) && !empty($order->info['shipping_method'])) {
            // -----
            // Give an external tax-handler to make modifications to the shipping tax.
            //
            $external_shipping_tax_handler = false;
            $shipping_tax = 0;
            $shipping_tax_description = '';
            $this->notify(
                'NOTIFY_OT_SHIPPING_TAX_CALCS', 
                array(), 
                $external_shipping_tax_handler, 
                $shipping_tax, 
                $shipping_tax_description
            );

            if ($external_shipping_tax_handler === true || ($module !== 'free' && $GLOBALS[$module]->tax_class > 0)) {
                if ($external_shipping_tax_handler !== true) {
                    if (!isset($GLOBALS[$module]->tax_basis)) {
                        $shipping_tax_basis = STORE_SHIPPING_TAX_BASIS;
                    } else {
                        $shipping_tax_basis = $GLOBALS[$module]->tax_basis;
                    }

                    if ($shipping_tax_basis == 'Billing') {
                        $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
                        $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
                    } elseif ($shipping_tax_basis == 'Shipping') {
                        $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                        $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                    } else {
                        if (STORE_ZONE == $order->billing['zone_id']) {
                            $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
                            $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->billing['country']['id'], $order->billing['zone_id']);
                        } elseif (STORE_ZONE == $order->delivery['zone_id']) {
                            $shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                            $shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                        } else {
                            $shipping_tax = 0;
                        }
                    }
                }
                $shipping_tax_amount = zen_calculate_tax($order->info['shipping_cost'], $shipping_tax);
                $order->info['shipping_tax'] += $shipping_tax_amount;
                $order->info['tax'] += $shipping_tax_amount;
                if (!isset($order->info['tax_groups'][$shipping_tax_description])) {
                    $order->info['tax_groups'][$shipping_tax_description] = 0;
                }
                $order->info['tax_groups'][$shipping_tax_description] += $shipping_tax_amount;
                $order->info['total'] += $shipping_tax_amount;
                $_SESSION['shipping_tax_description'] = $shipping_tax_description;
                $_SESSION['shipping_tax_amount'] =  $shipping_tax_amount;
                if (DISPLAY_PRICE_WITH_TAX == 'true') {
                    $order->info['shipping_cost'] += $shipping_tax_amount;
                }
            }

            if (isset($_SESSION['shipping']['id']) && $_SESSION['shipping']['id'] == 'free_free') {
                $order->info['shipping_method'] = FREE_SHIPPING_TITLE;
                $order->info['shipping_cost'] = 0;
            }
            
            $this->output[] = array(
                'title' => $order->info['shipping_method'] . ':',
                'text' => $currencies->format($order->info['shipping_cost'], true, $order->info['currency'], $order->info['currency_value']),
                'value' => $order->info['shipping_cost']
            );
        }
    }

    public function check() 
    {
        global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_ORDER_TOTAL_SHIPPING_STATUS' LIMIT 1");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    public function keys() 
    {
        return array(
            'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 
            'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', 
            'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 
            'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', 
            'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION'
        );
    }

    public function install() 
    {
        global $db;
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('This module is installed', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', '', 6, 1,'zen_cfg_select_option(array(\'true\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Sort Order', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '200', 'Sort order of display.', 6, 2, now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Allow Free Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Do you want to allow free shipping?', 6, 3, 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) VALUES ('Free Shipping For Orders Over', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 'Provide free shipping for orders over the set amount.', 6, 4, 'currencies->format', now())");
        $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Provide Free Shipping For Orders Made', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Provide free shipping for orders sent to the set destination.', 6, 5, 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
    }

    public function remove() 
    {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
    }
}
