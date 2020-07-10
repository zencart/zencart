<?php
/**
 * ot_tax order-total module
 *
 * @package orderTotal
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sun Jan 7 21:31:50 2018 -0500 Modified in v1.5.6 $
 */
  class ot_tax {
    var $title, $output;

    function __construct() {
      $this->code = 'ot_tax';
      $this->title = MODULE_ORDER_TOTAL_TAX_TITLE;
      $this->description = MODULE_ORDER_TOTAL_TAX_DESCRIPTION;
      $this->sort_order = defined('MODULE_ORDER_TOTAL_TAX_SORT_ORDER') ? MODULE_ORDER_TOTAL_TAX_SORT_ORDER : null;
      if (null === $this->sort_order) return false;

      $this->output = array();
    }

    function process() {
      global $order, $currencies;

      $taxDescription = '';
      $taxValue = 0;
      if (STORE_TAX_DISPLAY_STATUS == 1)
      {
        $taxAddress = zen_get_tax_locations();
        $result = zen_get_all_tax_descriptions($taxAddress['country_id'], $taxAddress['zone_id']);
        if (count($result) > 0)
        {
          foreach ($result as $description)
          {
            if (!isset($order->info['tax_groups'][$description]))
            {
              $order->info['tax_groups'][$description] = 0;
            }
          }
        }
      }
      if (count($order->info['tax_groups']) > 1 && isset($order->info['tax_groups'][0])) unset($order->info['tax_groups'][0]);
      foreach($order->info['tax_groups'] as $key => $value) {
        if (SHOW_SPLIT_TAX_CHECKOUT == 'true')
        {
          if ($value > 0 or ($value == 0 && STORE_TAX_DISPLAY_STATUS == 1 )) {
            $this->output[] = array('title' => ((is_numeric($key) && $key == 0) ? TEXT_UNKNOWN_TAX_RATE :  $key) . ':',
                                    'text' => $currencies->format($value, true, $order->info['currency'], $order->info['currency_value']),
                                    'value' => $value);
          }
        } else
        {
          if ($value > 0 || ($value == 0 && STORE_TAX_DISPLAY_STATUS == 1))
          {
            $taxDescription .= ((is_numeric($key) && $key == 0) ? TEXT_UNKNOWN_TAX_RATE :  $key) . ' + ';
            $taxValue += $value;
          }
        }
      }
      if (SHOW_SPLIT_TAX_CHECKOUT != 'true' && ($taxValue > 0 or STORE_TAX_DISPLAY_STATUS == 1))
      {
        $this->output[] = array(
                        'title' => substr($taxDescription, 0 , strlen($taxDescription)-3) . ':' ,
                        'text' => $currencies->format($taxValue, true, $order->info['currency'], $order->info['currency_value']) ,
                        'value' => $taxValue);
      }
    }

    function check() {
	  global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TAX_STATUS'");
        $this->_check = $check_query->RecordCount();
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_TAX_STATUS', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER');
    }

    function install() {
	  global $db;
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('This module is installed', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '300', 'Sort order of display.', '6', '2', now())");
    }

    function remove() {
	  global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
  }
