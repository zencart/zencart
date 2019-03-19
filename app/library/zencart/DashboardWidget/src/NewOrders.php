<?php
/**
 * NewOrders Dashboard Widget
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:  $
 */

namespace ZenCart\DashboardWidget;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/**
 * Class NewOrders
 * @package ZenCart\DashboardWidget
 */
class NewOrders extends AbstractWidget
{
  public function prepareContent()
  {
    global $db;
    $tplVars = array();
    $orders = $db->Execute("select o.orders_id, o.customers_name, o.customers_id, o.date_purchased, o.currency, o.currency_value, ot.class, ot.text as order_total 
         from " . TABLE_ORDERS . " o 
         left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and class = 'ot_total') 
         order by orders_id DESC 
         limit 15");

    while (!$orders->EOF) {
      $name = $orders->fields['customers_name'];
      $order_value = $orders->fields['order_total'];
      $order_date = zen_date_short($orders->fields['date_purchased']);
      $tplVars['content'][] = array('text'=> '<a href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $orders->fields['orders_id']) . '">' . $name . '</a><br />' . $order_date, 'value'=>$order_value);
      $orders->MoveNext();
    }
    return $tplVars;
  }
}
