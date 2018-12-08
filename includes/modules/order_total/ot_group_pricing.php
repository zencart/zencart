<?php
/**
 * ot_group_pricing order-total module
 *
 * @package orderTotal
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 Tue May 8 00:42:18 2018 -0400 Modified in v1.5.6 $
 */

class ot_group_pricing {
  var $title, $output;

  function __construct() {
    $this->code = 'ot_group_pricing';
    $this->title = MODULE_ORDER_TOTAL_GROUP_PRICING_TITLE;
    $this->description = MODULE_ORDER_TOTAL_GROUP_PRICING_DESCRIPTION;
    $this->sort_order = defined('MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER') ? MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER : null;
    if (null === $this->sort_order) return false;

    $this->include_shipping = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING;
    $this->include_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX;
    $this->calculate_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX;
    $this->tax_class = MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS;
    $this->credit_class = true;
    $this->output = array();
  }

  function process() {
    global $order, $currencies, $db;
    $order_total = $this->get_order_total();
    $od_amount = $this->calculate_deductions($order_total['total']);
    $this->deduction = isset($od_amount['total']) ? $od_amount['total'] : 0;
    if (isset($od_amount['total']) && $od_amount['total'] > 0) {
      $tax = 0;
      foreach($order->info['tax_groups'] as $key => $value) {
        if ($od_amount['tax_groups'][$key]) {
          $order->info['tax_groups'][$key] -= $od_amount['tax_groups'][$key];
          $tax += $od_amount['tax_groups'][$key];
        }
      }
      $order->info['total'] = $order->info['total'] - $od_amount['total'];
      if (DISPLAY_PRICE_WITH_TAX == 'true') {
        $od_amount['total'] += $tax;
      }
      if ($this->calculate_tax == "Standard") $order->info['total'] -= $tax;
      if ($order->info['total'] < 0) $order->info['total'] = 0;
      $order->info['tax'] = $order->info['tax'] - $tax;
      $this->output[] = array('title' => $this->title . ':',
      'text' => '-' . $currencies->format($od_amount['total'], true, $order->info['currency'], $order->info['currency_value']),
      'value' => $od_amount['total']);

    }
  }
  function get_order_total() {
    global  $order;
    $order_total_tax = $order->info['tax'];
    $order_total = $order->info['total'];
    if ($this->include_shipping != 'true') $order_total -= $order->info['shipping_cost'];
    if ($this->include_tax != 'true') $order_total -= $order->info['tax'];
    if (DISPLAY_PRICE_WITH_TAX == 'true' && $this->include_shipping != 'true')
    {
      $order_total += $order->info['shipping_tax'];
    }
    $taxGroups = array();
    foreach ($order->info['tax_groups'] as $key=>$value) {
      if (isset($_SESSION['shipping_tax_description']) && $key == $_SESSION['shipping_tax_description'])
      {
        if ($this->include_shipping != 'true')
        {
          $value -= $order->info['shipping_tax'];
        }
      }
      $taxGroups[$key] = $value;
    }
    $orderTotalFull = $order_total;
    $order_total = array('totalFull'=>$orderTotalFull, 'total'=>$order_total, 'tax'=>$order_total_tax, 'taxGroups'=>$taxGroups);
    return $order_total;
  }
  function calculate_deductions($order_total) {
    global $db, $order;
    $od_amount = array();
    if ($order_total == 0) return $od_amount;
    $orderTotal = $this->get_order_total();
    $orderTotalTax = $orderTotal['tax'];
    $taxGroups = $orderTotal['taxGroups'];
    $group_query = $db->Execute("select customers_group_pricing from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
    if ($group_query->fields['customers_group_pricing'] != '0') {
      $group_discount = $db->Execute("select group_name, group_percentage from " . TABLE_GROUP_PRICING . "
                                      where group_id = '" . (int)$group_query->fields['customers_group_pricing'] . "'");
      $gift_vouchers = $_SESSION['cart']->gv_only();
      $discount = ($orderTotal['total'] - $gift_vouchers) * $group_discount->fields['group_percentage'] / 100;
//      echo "discout = $discount<br>";
      $od_amount['total'] = round($discount, 2);
      $ratio = $od_amount['total']/$order_total;
      /**
       * when calculating the ratio add some insignificant values to stop divide by zero errors
       */
      switch ($this->calculate_tax) {
        case 'None':
          if ($this->include_tax) {
            foreach ($order->info['tax_groups'] as $key=>$value) {
              $od_amount['tax_groups'][$key] = $order->info['tax_groups'][$key] * $ratio;
            }
          }
        break;
        case 'Standard':
          if ($od_amount['total'] >= $order_total) {
            $ratio = 1;
          }
          $adjustedTax = $orderTotalTax * $ratio;
          if ($order->info['tax'] == 0) return $od_amount;
          $ratioTax = ($orderTotalTax != 0 ) ? $adjustedTax/$orderTotalTax : 0;
          $tax_deduct = 0;
          foreach ($taxGroups as $key=>$value) {
            $od_amount['tax_groups'][$key] = $value * $ratioTax;
            $tax_deduct += $od_amount['tax_groups'][$key];
          }
          $od_amount['tax'] = $tax_deduct;
        break;
        case 'Credit Note':
          $tax_rate = zen_get_tax_rate($this->tax_class);
          $od_amount['tax'] = zen_calculate_tax($od_amount['total'], $tax_rate);
          $tax_description = zen_get_tax_description($this->tax_class);
          $od_amount['tax_groups'][$tax_description] = $od_amount['tax'];
        break;
      }
    }
    return $od_amount;
  }
  function pre_confirmation_check($order_total) {
    global $order;
    $od_amount = $this->calculate_deductions($order_total);
    $order->info['total'] = $order->info['total'] - $od_amount['total'];
    return $od_amount['total'] + (DISPLAY_PRICE_WITH_TAX == 'true' ? 0 : $od_amount['tax']);
  }

  function credit_selection() {
    $selection = false;
    return $selection;
  }

  function collect_posts() {
  }

  function update_credit_account($i) {
  }

  function apply_credit() {
  }
  /**
   * Enter description here...
   *
   */
  function clear_posts() {
  }
  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS'");
      $this->_check = $check_query->RecordCount();
    }

    return $this->_check;
  }

  function keys() {
    return array('MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS', 'MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX', 'MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX', 'MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS');
  }

  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('This module is installed', 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER', '290', 'Sort order of display.', '6', '2', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Include Shipping', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'false', 'Include Shipping value in amount before discount calculation?', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Include Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX', 'true', 'Include Tax value in amount before discount calculation?', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX', 'Standard', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS', '0', 'Use the following tax class when treating Group Discount as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
  }

  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }
}
