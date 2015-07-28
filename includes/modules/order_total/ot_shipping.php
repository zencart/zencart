<?php
/**
 * ot_shipping order-total module
 *
 * @package orderTotal
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ot_shipping.php 19002 wilt  Modified in v1.6.0 $
 */

  class ot_shipping {
    var $title, $output;

    function __construct() {
      global $order, $currencies;
      $this->code = 'ot_shipping';
      $this->title = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
      $this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
      $this->sort_order = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;
      unset($_SESSION['shipping_tax_description']);
      $this->output = array();
      if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
        switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
          case 'national':
            if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
          case 'international':
            if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
          case 'both':
            $pass = true; break;
          default:
            $pass = false; break;
        }

        if ( ($pass == true) && ( ($order->info['total'] - $order->info['shipping_cost']) >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
          $order->info['shipping_method'] = $this->title;
          $order->info['total'] -= $order->info['shipping_cost'];
          $order->info['shipping_cost'] = 0;
        }
      }
      $module = (isset($_SESSION['shipping']) && isset($_SESSION['shipping']['id'])) ? substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_')) : '';
      if (is_object(($order)) && zen_not_null($order->info['shipping_method'])) {
        if ($GLOBALS[$module]->tax_class > 0) {
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
          $shipping_tax_amount = zen_calculate_tax($order->info['shipping_cost'], $shipping_tax);
          $order->info['shipping_tax'] += $shipping_tax_amount;
          $order->info['tax'] += $shipping_tax_amount;
          $order->info['tax_groups']["$shipping_tax_description"] += zen_calculate_tax($order->info['shipping_cost'], $shipping_tax);
          $order->info['total'] += zen_calculate_tax($order->info['shipping_cost'], $shipping_tax);
          $_SESSION['shipping_tax_description'] =  $shipping_tax_description;
          $_SESSION['shipping_tax_amount'] =  $shipping_tax_amount;
          if (DISPLAY_PRICE_WITH_TAX == 'true') $order->info['shipping_cost'] += zen_calculate_tax($order->info['shipping_cost'], $shipping_tax);
        }

        if (isset($_SESSION['shipping']['id']) && $_SESSION['shipping']['id'] == 'free_free') {
          $order->info['shipping_method'] = FREE_SHIPPING_TITLE;
          $order->info['shipping_cost'] = 0;
        }

      }
    }

    function process() {
      global $order, $currencies;
      $this->output[] = array('title' => $order->info['shipping_method'] . ':',
                              'text' => $currencies->format($order->info['shipping_cost'], true, $order->info['currency'], $order->info['currency_value']),
                              'value' => $order->info['shipping_cost']);
    }

    function check() {
      global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_SHIPPING_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION');
    }

    function install() {
      global $db;
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('This module is installed', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '200', 'Sort order of display.', '6', '2', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow Free Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Do you want to allow free shipping?', '6', '3', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Free Shipping For Orders Over', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 'Provide free shipping for orders over the set amount.', '6', '4', 'currencies->format', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Provide Free Shipping For Orders Made', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Provide free shipping for orders sent to the set destination.', '6', '5', 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
    }

    function remove() {
      global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
