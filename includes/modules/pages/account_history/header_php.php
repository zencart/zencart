<?php
/**
 * Header code file for the Account History page
 *
 * @package page
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 3160 2006-03-11 01:37:18Z drbyte $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ACCOUNT_HISTORY');


if (!$_SESSION['customer_id']) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

$orders_total = zen_count_customer_orders();

if ($orders_total > 0) {
  $history_query_raw = "SELECT o.orders_id, o.date_purchased, o.delivery_name,
                               o.billing_name, ot.text as order_total, s.orders_status_name
                        FROM   " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s
                        WHERE      o.customers_id = :customersID
                        AND        o.orders_id = ot.orders_id
                        AND        ot.class = 'ot_total'
                        AND        o.orders_status = s.orders_status_id
                        AND        s.language_id = :languagesID
                        ORDER BY   orders_id DESC";

  $history_query_raw = $db->bindVars($history_query_raw, ':customersID', $_SESSION['customer_id'], 'integer');
  $history_query_raw = $db->bindVars($history_query_raw, ':languagesID', $_SESSION['languages_id'], 'integer');
  $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_ORDER_HISTORY);
  $history = $db->Execute($history_split->sql_query);

  $accountHistory = array();
  $accountHasHistory = true;
  while (!$history->EOF) {
    $products_query = "SELECT count(*) AS count
                       FROM   " . TABLE_ORDERS_PRODUCTS . "
                       WHERE  orders_id = :ordersID";

    $products_query = $db->bindVars($products_query, ':ordersID', $history->fields['orders_id'], 'integer');
    $products = $db->Execute($products_query);

    if (zen_not_null($history->fields['delivery_name'])) {
      $order_type = TEXT_ORDER_SHIPPED_TO;
      $order_name = $history->fields['delivery_name'];
    } else {
      $order_type = TEXT_ORDER_BILLED_TO;
      $order_name = $history->fields['billing_name'];
    }
    $extras = array('order_type'=>$order_type,
    'order_name'=>$order_name,
    'product_count'=>$products->fields['count']);
    $accountHistory[] = array_merge($history->fields, $extras);
    $history->moveNext();
  }
} else {
  $accountHasHistory = false;
}
// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ACCOUNT_HISTORY');
?>