<?php
/**
 * downloads module - prepares information for use in downloadable files delivery
 *
 * @package modules
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: downloads.php 3018 2006-02-12 21:04:04Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (!($_GET['main_page']==FILENAME_ACCOUNT_HISTORY_INFO)) {
  // Get last order id for checkout_success
  $orders_lookup_query = "select orders_id
                     from " . TABLE_ORDERS . "
                     where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                     order by orders_id desc limit 1";

  $orders_lookup = $db->Execute($orders_lookup_query);
  $last_order = $orders_lookup->fields['orders_id'];
} else {
  $last_order = $_GET['order_id'];
}

// Now get all downloadable products in that order
$downloads_query = "select date_format(o.date_purchased, '%Y-%m-%d') as date_purchased_day,
                             opd.download_maxdays, op.products_name, opd.orders_products_download_id,
                             opd.orders_products_filename, opd.download_count, opd.download_maxdays
                      from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, "
. TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
                      where o.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                      and (o.orders_status >= '" . DOWNLOADS_CONTROLLER_ORDERS_STATUS . "'
                      and o.orders_status <= '" . DOWNLOADS_CONTROLLER_ORDERS_STATUS_END . "')
                      and o.orders_id = '" . (int)$last_order . "'
                      and o.orders_id = op.orders_id
                      and op.orders_products_id = opd.orders_products_id
                      and opd.orders_products_filename != ''";

$downloads = $db->Execute($downloads_query);

// If there is a download in the order and they cannot get it, tell customer about download rules
$downloads_check_query = $db->Execute("select o.orders_id, opd.orders_products_download_id
                          from " .
TABLE_ORDERS . " o, " .
TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
                          where
                          o.orders_id = opd.orders_id
                          and o.orders_id = '" . (int)$last_order . "'
                          and opd.orders_products_filename != ''
                          ");
?>