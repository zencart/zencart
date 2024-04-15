<?php
/**
 * ot_group_pricing order-total module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2024 May 09 Modified in v2.0.1 $
 */

 use Zencart\ModuleSupport\OrderTotalCCBase;
 use Zencart\ModuleSupport\OrderTotalCCConcerns;
 use Zencart\ModuleSupport\OrderTotalCCContract;

class ot_group_pricing extends OrderTotalCCBase implements OrderTotalCCContract
{

  use OrderTotalCCConcerns;

  public string $code = 'ot_group_pricing';

  public string $defineName = 'GROUP_PRICING';

  public function process(): void
  {
    global $order, $currencies, $db;
    $order_total = $this->get_order_total();
    $od_amount = $this->calculate_deductions($order_total['total']);
    $this->deduction = isset($od_amount['total']) ? $od_amount['total'] : 0;
    if (isset($od_amount['total']) && $od_amount['total'] > 0) {
      $tax = 0;
      foreach($order->info['tax_groups'] as $key => $value) {
        if (isset($od_amount['tax_groups'][$key])) {
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
    global $db, $order, $zco_notifier;
    $od_amount = array();
    if ($order_total == 0 || !zen_is_logged_in() || zen_in_guest_checkout()) {
        $zco_notifier->notify('NOTIFY_OT_GROUP_PRICING_DEDUCTION_OVERRIDE', ['order_total' => $order_total], $od_amount);
        return $od_amount;
    }
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
          if ($this->include_tax === 'true') {
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

      $zco_notifier->notify(
        'NOTIFY_OT_GROUP_PRICING_DEDUCTION_OVERRIDE_FINAL',
        [
            'customers_group_pricing' => (int)$group_query->fields['customers_group_pricing'],
            'group_percentage' => $group_discount->fields['group_percentage'],
            'orderTotal' => $orderTotal,
            'gift_vouchers' => $gift_vouchers,
            'tax_calc_method' => $this->calculate_tax,
            'order_info' => $order->info,
        ],
        $od_amount
      );
    }
    return $od_amount;
  }

  function help() {
       return array('link' => 'https://docs.zen-cart.com/user/order_total/group_pricing/'); 
  }
 }
