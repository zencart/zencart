<?php
/**
 * ot_group_pricing order-total module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2024 May 09 Modified in v2.0.1 $
 */

class ot_group_pricing {

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
     * $credit_class flag to indicate order totals method is a credit class
     * @var boolean
     */
    public $credit_class;
    /**
     * $deduction amount of deduction calculated/afforded while being applied to an order
     * @var float|null
     */
    public $deduction;
    /**
     * $description is a soft name for this order total method
     * @var string 
     */
    public $description;
    /**
     * $include_shipping allow shipping costs to be discounted by coupon if 'true'
     * @var string
     */
    public $include_shipping;
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
     * $output is an array of the display elements used on checkout pages
     * @var array
     */
    public $output = [];

  function __construct() {
    $this->code = 'ot_group_pricing';
    $this->title = MODULE_ORDER_TOTAL_GROUP_PRICING_TITLE;
    $this->description = MODULE_ORDER_TOTAL_GROUP_PRICING_DESCRIPTION;
    $this->sort_order = defined('MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER') ? MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER : null;
    if (null === $this->sort_order) return false;

    $this->include_shipping = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING;
    $this->credit_class = true;
    $this->output = array();
  }

    /**
     * Produces final deduction values,
     * updates $order amounts,
     * and generates the $this->output for showing discount information on checkout pages
     */
  function process() {
    global $order, $currencies, $db;
    $order_total = $this->get_order_total();
    $od_amount = $this->calculate_deductions($order_total['total']);
    $this->deduction = isset($od_amount['total']) ? $od_amount['total'] : 0;
    if (isset($od_amount['total']) && $od_amount['total'] > 0) {
        if ($this->include_shipping === 'true') {
            $order->info['shipping_cost'] -= $od_amount['shipping'];
            $order->info['shipping_tax'] -= $od_amount['ShippingTax'];
        }
        $tax = 0;
        // Update all tax rates in order object
        foreach($order->info['tax_groups'] as $key => $value) {
            if (isset($od_amount['tax_groups'][$key])) {
                $order->info['tax_groups'][$key] -= $od_amount['tax_groups'][$key];
                $tax += $od_amount['tax_groups'][$key];
                $order->info['tax_subtotals'][$key]['subtotal'] -= $od_amount['total'];
                if (isset($od_amount['shipping_tax_groups']) && array_key_exists($key, $od_amount['shipping_tax_groups']) && $this->include_shipping === 'true') {
                    $order->info['shipping_tax_groups'][$key] -= $od_amount['shipping_tax_groups'][$key];
                }
            }
        }
      $order->info['total'] -= DISPLAY_PRICE_WITH_TAX !== 'true' ? $od_amount['total'] + $tax : $od_amount['total'];
      if ($order->info['total'] < 0) $order->info['total'] = 0;
      $order->info['tax'] = $order->info['tax'] - $tax;

      $this->output[] = array('title' => $this->title . ':',
      'text' => '-' . $currencies->format($od_amount['total'], true, $order->info['currency'], $order->info['currency_value']),
      'value' => $od_amount['total']);

    }
  }
  
    /**
     * Calculate eligible total amounts against which discounts will be applied
     *
     * @return array
     */
  function get_order_total() {
    global  $order;
    $order_total_tax = $order->info['tax'];
    $order_total = $order->info['total'];
    if ($this->include_shipping !== 'true') {
        $order_total -= $order->info['shipping_cost'];
    }
    $orderTotalFull = $order_total;
    $order_total -= DISPLAY_PRICE_WITH_TAX !== 'true' ? $order->info['tax'] : 0;

    $order_total = [
        'totalFull' => $orderTotalFull,
        'total' => $order_total,
        'tax' => $order_total_tax,
        'taxGroups' => $order->info['tax_groups'],
        'shipping' => $order->info['shipping_cost'],
        'ShippingTax' => $order->info['shipping_tax'],
        'ShippingTaxGroups'=>$order->info['shipping_tax_groups'],
    ];

    return $order_total;
  }
  
    /**
     * Calculate actual deductions on total and taxes
     *
     * @return array $od_amount
     */
  function calculate_deductions($order_total) {
    global $db;
    $od_amount = array();
    if ($order_total === 0 || !zen_is_logged_in() || zen_in_guest_checkout()) {
        return $od_amount;
    }
    $orderTotal = $this->get_order_total();
    $group_query = $db->Execute("select customers_group_pricing from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
    if ($group_query->fields['customers_group_pricing'] != '0') {
        $group_discount = $db->Execute("select group_name, group_percentage from " . TABLE_GROUP_PRICING . "
                                        where group_id = '" . (int)$group_query->fields['customers_group_pricing'] . "'");
        $od_amount['total'] = ($orderTotal['total'] - $_SESSION['cart']->gv_only()) * $group_discount->fields['group_percentage'] / 100;
        $ratio = $od_amount['total']/$order_total;
        if ($this->include_shipping === 'true') {
            $od_amount['shipping'] = $orderTotal['shipping'] * $ratio;
            $od_amount['ShippingTax'] = $orderTotal['ShippingTax'] * $ratio;
        } else {
            $od_amount['shipping'] = 0;
            $od_amount['ShippingTax'] = 0;
        }
        $tax_deduct = 0;
        foreach ($orderTotal['taxGroups'] as $key=>$value) {
            $tax = $value;
            if (isset($_SESSION['shipping_tax_description']) &&  $_SESSION['shipping_tax_description'][0] != '') {
                foreach ($_SESSION['shipping_tax_description'] as $ind => $descr) {
                    if ($descr === $key) {
                        if ($this->include_shipping !== 'true') {
                            $tax -= $orderTotal['ShippingTaxGroups'][$key];
                        } else {
                            $od_amount['shipping_tax_groups'][$key] = $orderTotal['ShippingTaxGroups'][$key] * $ratio;
                        }
                    }
                }
            }
            $od_amount['tax_groups'][$key] = $tax * $ratio;
            $tax_deduct += $od_amount['tax_groups'][$key];
        }
        $od_amount['tax'] = $tax_deduct;
    }
    return $od_amount;
  }

  /**
   * @TODO - Per order_total class, this function is not used. See process() instead.
   */
  function pre_confirmation_check($order_total) {
    global $order;
    $od_amount = $this->calculate_deductions($order_total);
    $order->info['total'] = $order->info['total'] - $od_amount['total'];
    return $od_amount['total'] + (DISPLAY_PRICE_WITH_TAX === 'true' ? 0 : $od_amount['tax']);
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
    /**
    * Check install status
    *
    * @return bool
    */
  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS'");
      $this->_check = $check_query->RecordCount();
    }

    return $this->_check;
  }
    /**
    * @return array of this modules constants (settings)
    */
  function keys() {
    return array('MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS', 'MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING');
  }
    /**
    * Install module keys in database
    *
    */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('This module is installed', 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER', '290', 'Sort order of display.', '6', '2', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function ,date_added) values ('Include Shipping', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'false', 'Include Shipping value in amount before discount calculation?', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
  }

  function help() {
       return array('link' => 'https://docs.zen-cart.com/user/order_total/group_pricing/'); 
  }
    /**
    * Uninstall
    *
    */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }
}
