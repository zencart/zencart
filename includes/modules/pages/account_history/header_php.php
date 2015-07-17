<?php
/**
 * Header code file for the Account History page
 *
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: modified in v1.6.0 $
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

    $history_query_count = "SELECT COUNT(*) as total
                        FROM   " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s
                        WHERE      o.customers_id = :customersID
                        AND        o.orders_id = ot.orders_id
                        AND        ot.class = 'ot_total'
                        AND        o.orders_status = s.orders_status_id
                        AND        s.language_id = :languagesID";

    $history_query_count = $db->bindVars($history_query_count, ':customersID', $_SESSION['customer_id'], 'integer');
    $history_query_count = $db->bindVars($history_query_count, ':languagesID', $_SESSION['languages_id'], 'integer');

    $class = NAMESPACE_PAGINATOR . '\\Paginator';
    $paginator = new $class($zcRequest);
    $paginator->setAdapterParams(array('itemsPerPage'=>MAX_DISPLAY_ORDER_HISTORY));
    $paginator->setScrollerParams(array('navLinkText'=>TEXT_DISPLAY_NUMBER_OF_ORDERS));
    $adapterDate = array('dbConn'=>$db, 'mainSql'=>$history_query_raw, 'countSql'=>$history_query_count);
    $paginator->doPagination($adapterDate);
    $result = $paginator->getScroller()->getResults();
    $tplVars['listingBox']['paginator'] = $result;

  $accountHistory = array();
  $accountHasHistory = true;
  foreach($result['resultList'] as $history) {
    $products_query = "SELECT count(*) AS count
                       FROM   " . TABLE_ORDERS_PRODUCTS . "
                       WHERE  orders_id = :ordersID";

    $products_query = $db->bindVars($products_query, ':ordersID', $history['orders_id'], 'integer');
    $products = $db->Execute($products_query);

    if (zen_not_null($history['delivery_name'])) {
      $order_type = TEXT_ORDER_SHIPPED_TO;
      $order_name = $history['delivery_name'];
    } else {
      $order_type = TEXT_ORDER_BILLED_TO;
      $order_name = $history['billing_name'];
    }
    $extras = array('order_type'=>$order_type,
    'order_name'=>$order_name,
    'product_count'=>$products->fields['count']);
    $accountHistory[] = array_merge($history, $extras);
//    $history->moveNext();
  }
} else {
  $accountHasHistory = false;
}
// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ACCOUNT_HISTORY');

