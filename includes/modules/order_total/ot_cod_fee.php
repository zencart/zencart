<?php
/**
 * ot_cod_fee order-total module
 *
 * @package orderTotal
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright (c) 2002 Thomas Plänkers http://www.oscommerce.at
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sun Jan 7 21:31:50 2018 -0500 Modified in v1.5.6 $
 */
/**
 * COD-FEE Order Totals Module
 *
 */

  class ot_cod_fee {
    var $title, $output;

    function __construct() {
      $this->code = 'ot_cod_fee';
      $this->title = MODULE_ORDER_TOTAL_COD_TITLE;
      $this->description = MODULE_ORDER_TOTAL_COD_DESCRIPTION;
      $this->enabled = (defined('MODULE_ORDER_TOTAL_COD_STATUS') && MODULE_ORDER_TOTAL_COD_STATUS == 'true');
      $this->sort_order = defined('MODULE_ORDER_TOTAL_COD_SORT_ORDER') ? MODULE_ORDER_TOTAL_COD_SORT_ORDER : null;
      if (null === $this->sort_order) return false;

      $this->output = array();
    }

    function process() {
      global $order, $currencies, $cod_cost, $cod_country, $shipping;

      if ($this->enabled == true) {
        //Will become true, if cod can be processed.
        $cod_country = false;

        //check if payment method is cod. If yes, check if cod is possible.

        if ($_SESSION['payment'] == 'cod') {
          //process installed shipping modules
          if (substr_count($_SESSION['shipping']['id'], 'flat') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_FLAT));
          if (substr_count($_SESSION['shipping']['id'], 'free') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_FREE));
          if (substr_count($_SESSION['shipping']['id'], 'freeshipper') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_FREESHIPPER));
          if (substr_count($_SESSION['shipping']['id'], 'freeoptions') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_FREEOPTIONS));
          if (substr_count($_SESSION['shipping']['id'], 'item') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_ITEM));
          if (substr_count($_SESSION['shipping']['id'], 'perweightunit') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_PERWEIGHTUNIT));
          if (substr_count($_SESSION['shipping']['id'], 'table') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_TABLE));
          if (substr_count($_SESSION['shipping']['id'], 'ups') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_UPS));
          if (substr_count($_SESSION['shipping']['id'], 'usps') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_USPS));
          if (substr_count($_SESSION['shipping']['id'], 'fedex') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_FEDEX));
          if (substr_count($_SESSION['shipping']['id'], 'zones') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_ZONES));
          if (substr_count($_SESSION['shipping']['id'], 'ap') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_AP));
          if (substr_count($_SESSION['shipping']['id'], 'dp') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_DP));

          //satt inn av Pompel
          if (substr_count($_SESSION['shipping']['id'], 'servicepakke') !=0) $cod_zones = preg_split("/[:,]/", str_replace(' ', '', MODULE_ORDER_TOTAL_COD_FEE_SERVICEPAKKE));

            for ($i = 0, $n=count($cod_zones); $i < $n; $i++) {
              if ($cod_zones[$i] == $order->delivery['country']['iso_code_2']) {
                  $cod_cost = $cod_zones[$i + 1];
                  $cod_country = true;
                  //print('match' . $i . ': ' . $cod_cost);
                  break;
                } elseif ($cod_zones[$i] == '00') {
                  $cod_cost = $cod_zones[$i + 1];
                  $cod_country = true;
                  //print('match' . $i . ': ' . $cod_cost);
                  break;
                } else {
                  //print('no match');
                }
              $i++;
            }
          } else {
            //COD selected, but no shipping module which offers COD
          }

        if ($cod_country) {
          $cod_tax_address = zen_get_tax_locations();
          $tax = zen_get_tax_rate(MODULE_ORDER_TOTAL_COD_TAX_CLASS, $cod_tax_address['country_id'], $cod_tax_address['zone_id']);
          $order->info['total'] += $cod_cost;
          if ($tax > 0) {
            $tax_description = zen_get_tax_description(MODULE_ORDER_TOTAL_COD_TAX_CLASS, $cod_tax_address['country_id'], $cod_tax_address['zone_id']);
            $order->info['tax'] += zen_calculate_tax($cod_cost, $tax);
            $order->info['tax_groups'][$tax_description] += zen_calculate_tax($cod_cost, $tax);
            $order->info['total'] += zen_calculate_tax($cod_cost, $tax);
            if (DISPLAY_PRICE_WITH_TAX == 'true') {
              $cod_cost += zen_calculate_tax($cod_cost, $tax);
            }
          }

          $this->output[] = array('title' => $this->title . ':',
                                  'text' => $currencies->format($cod_cost, true,  $order->info['currency'], $order->info['currency_value']),
                                  'value' => $cod_cost);
        } else {
//Following code should be improved if we can't get the shipping modules disabled, who don't allow COD
// as well as countries who do not have cod
//          $this->output[] = array('title' => $this->title . ':',
//                                  'text' => 'No COD for this module.',
//                                  'value' => '');
        }
      }
    }

    function check() {
      global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_COD_STATUS'");
        $this->_check = $check_query->RecordCount();
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_COD_STATUS', 'MODULE_ORDER_TOTAL_COD_SORT_ORDER', 'MODULE_ORDER_TOTAL_COD_FEE_FLAT', 'MODULE_ORDER_TOTAL_COD_FEE_FREE', 'MODULE_ORDER_TOTAL_COD_FEE_FREESHIPPER', 'MODULE_ORDER_TOTAL_COD_FEE_FREEOPTIONS', 'MODULE_ORDER_TOTAL_COD_FEE_PERWEIGHTUNIT', 'MODULE_ORDER_TOTAL_COD_FEE_ITEM', 'MODULE_ORDER_TOTAL_COD_FEE_TABLE', 'MODULE_ORDER_TOTAL_COD_FEE_UPS', 'MODULE_ORDER_TOTAL_COD_FEE_USPS', 'MODULE_ORDER_TOTAL_COD_FEE_ZONES', 'MODULE_ORDER_TOTAL_COD_FEE_AP', 'MODULE_ORDER_TOTAL_COD_FEE_DP', 'MODULE_ORDER_TOTAL_COD_FEE_SERVICEPAKKE', 'MODULE_ORDER_TOTAL_COD_FEE_FEDEX', 'MODULE_ORDER_TOTAL_COD_TAX_CLASS');
    }

    function install() {
      global $db;
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display COD', 'MODULE_ORDER_TOTAL_COD_STATUS', 'true', 'Do you want this module to display?', '6', '1','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_COD_SORT_ORDER', '950', 'Sort order of display.', '6', '2', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for FLAT', 'MODULE_ORDER_TOTAL_COD_FEE_FLAT', 'AT:3.00,DE:3.58,00:9.99', 'FLAT: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '3', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for Free Shipping by default', 'MODULE_ORDER_TOTAL_COD_FEE_FREE', 'US:3.00', 'Free by default: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '3', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for Free Shipping Module - (freeshipper)', 'MODULE_ORDER_TOTAL_COD_FEE_FREESHIPPER', 'CA:4.50,US:3.00,00:9.99', 'Free Module: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '3', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for Free-Options Shipping Module - (freeoptions)', 'MODULE_ORDER_TOTAL_COD_FEE_FREEOPTIONS', 'CA:4.50,US:3.00,00:9.99', 'Free+Options: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '3', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for Per Weight Unit Shipping Module - (perweightunit)', 'MODULE_ORDER_TOTAL_COD_FEE_PERWEIGHTUNIT', 'CA:4.50,US:3.00,00:9.99', 'Per Weight Unit: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '3', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for ITEM', 'MODULE_ORDER_TOTAL_COD_FEE_ITEM', 'AT:3.00,DE:3.58,00:9.99', 'ITEM: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '4', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for TABLE', 'MODULE_ORDER_TOTAL_COD_FEE_TABLE', 'AT:3.00,DE:3.58,00:9.99', 'TABLE: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '5', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for UPS', 'MODULE_ORDER_TOTAL_COD_FEE_UPS', 'CA:4.50,US:3.00,00:9.99', 'UPS: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '6', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for USPS', 'MODULE_ORDER_TOTAL_COD_FEE_USPS', 'CA:4.50,US:3.00,00:9.99', 'USPS: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '7', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for ZONES', 'MODULE_ORDER_TOTAL_COD_FEE_ZONES', 'CA:4.50,US:3.00,00:9.99', 'ZONES: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '8', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for Austrian Post', 'MODULE_ORDER_TOTAL_COD_FEE_AP', 'AT:3.63,00:9.99', 'Austrian Post: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '9', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for German Post', 'MODULE_ORDER_TOTAL_COD_FEE_DP', 'DE:3.58,00:9.99', 'German Post: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '10', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for Servicepakke', 'MODULE_ORDER_TOTAL_COD_FEE_SERVICEPAKKE', 'NO:69', 'Servicepakke: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '11', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('COD Fee for FedEx', 'MODULE_ORDER_TOTAL_COD_FEE_FEDEX', 'US:3.00', 'FedEx: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)', '6', '12', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_ORDER_TOTAL_COD_TAX_CLASS', '0', 'Use the following tax class on the COD fee.', '6', '25', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    }


    function remove() {
      global $db;
      $keys = '';
      $keys_array = $this->keys();
      $keys_size = sizeof($keys_array);
      for ($i=0; $i<$keys_size; $i++) {
        $keys .= "'" . $keys_array[$i] . "',";
      }
      $keys = substr($keys, 0, -1);

      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in (" . $keys . ")");
    }
  }
