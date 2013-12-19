<?php
/**
 * zcDashboardWidgetNewOrders Class.
 *
 * @package classes
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Scott Wilson Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcDashboardWidgetNewOrders Class
 *
 * @package classes
 */
class zcDashboardWidgetNewOrders extends zcDashboardWidgetBase
{
  public function prepareContent()
  {
    global $db;
    $tplVars = array();
    $orders = $db->Execute("select o.orders_id as orders_id, o.customers_name as customers_name, o.customers_id, o.date_purchased as date_purchased, o.currency, o.currency_value, ot.class, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and class = 'ot_total') order by orders_id DESC limit 5");

    while (!$orders->EOF) {
      $name = $orders->fields['customers_name'];
      $order_value = $orders->fields['order_total'];
      $order_date = zen_date_short($orders->fields['date_purchased']);
      $tplVars['content'][] = array('text'=> '<a href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $orders->fields['orders_id'], 'NONSSL') . '">' . $name . '</a><br />' . $order_date, 'value'=>$order_value);
      $orders->MoveNext();
    }
    return $tplVars;
  }
}