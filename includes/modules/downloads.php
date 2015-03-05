<?php
/**
 * downloads module - prepares information for use in downloadable files delivery
 *
 * @package modules
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: downloads.php  Modified in v1.6.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$last_order = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
$customer_lookup_method = 'customerid';
$show_footer_link_to_my_account = ($current_page != FILENAME_ACCOUNT_HISTORY_INFO);

// adjustments for inquiries for customers without accounts
if (isset($order) && isset($_POST['order_id'])) {
  $last_order = $_POST['order_id'];
  $customer_lookup_method = 'email';
  $show_footer_link_to_my_account = false;
}

if ($last_order == 0 && $current_page != FILENAME_ACCOUNT_HISTORY_INFO) {
  // Get last order id for checkout_success
  $orders_lookup_query = "select orders_id
                     from " . TABLE_ORDERS . "
                     where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                     order by orders_id desc limit 1";

  $orders_lookup = $db->Execute($orders_lookup_query);
  $last_order = $orders_lookup->fields['orders_id'];
}

$downloads = array();

// If there is a download in the order and they cannot get it, tell customer about download rules
$downloads_check_query = $db->Execute("select o.orders_id, opd.orders_products_download_id
                          from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
                          where o.orders_id = opd.orders_id
                          and o.orders_id = '" . (int)$last_order . "'
                          and opd.orders_products_filename != ''
                          ");

$downloadsOnThisOrder = $downloads_check_query->RecordCount();

if ($downloadsOnThisOrder) {
  if ($customer_lookup_method == 'email') {
    $lookup_clause = " AND o.customers_email_address = :email_address ";
    $lookup_clause = $db->bindVars($lookup_clause, ':email_address', $_SESSION['email_address'], 'string');
  }
  if ($customer_lookup_method == 'customerid') {
    $lookup_clause = " AND o.customers_id = '" . (int)$_SESSION['customer_id'] . "'";
  }
  // Now get all downloadable products in that order
  $downloads_query = "select date_format(o.date_purchased, '%Y-%m-%d') as date_purchased_day,
                             opd.download_maxdays, op.products_name, opd.orders_products_download_id,
                             opd.orders_products_filename, opd.download_count, opd.download_maxdays
                        from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
                        where o.orders_id = '" . (int)$last_order . "'
                        and (o.orders_status >= '" . DOWNLOADS_CONTROLLER_ORDERS_STATUS . "'
                        and o.orders_status <= '" . DOWNLOADS_CONTROLLER_ORDERS_STATUS_END . "')" .
                        $lookup_clause . "
                        and o.orders_id = op.orders_id
                        and op.orders_products_id = opd.orders_products_id
                        and opd.orders_products_filename != ''";

  $result = $db->Execute($downloads_query);

  $numberOfDownloads = $result->RecordCount();

  while (!$result->EOF) {
    $data = $result->fields;
    $data['service'] = 'local';
    $data['filename'] = $data['orders_products_filename'];
    list($dt_year, $dt_month, $dt_day) = explode('-', $result->fields['date_purchased_day']);
    $data['expiry_timestamp'] = mktime(23, 59, 59, $dt_month, $dt_day + $result->fields['download_maxdays'], $dt_year);
    $data['expiry'] = date('Y-m-d H:i:s', $data['expiry_timestamp']);
    $data['downloads_remaining'] = $data['download_count'];
    $data['unlimited_downloads'] = ($data['download_maxdays'] == 0);
    $data['file_exists'] = file_exists(DIR_FS_DOWNLOAD . $data['orders_products_filename']);
    $data['is_downloadable'] = $data['file_exists'] && ($data['downloads_remaining'] > 0 && $data['expiry_timestamp'] > time()) || $data['unlimited_downloads'];
    $data['link_url'] = zen_href_link(FILENAME_DOWNLOAD, 'order=' . $last_order . '&id=' . $data['orders_products_download_id']);

    $data['filesize'] = ($data['file_exists']) ? filesize(DIR_FS_DOWNLOAD . $data['orders_products_filename']) : 0;
    if ($data['filesize'] == 0) {
      $zv_filesize_units = 'Unknown';
    } else {
      $zv_filesize = $data['filesize'];
      if ($zv_filesize >= 1024) {
        $zv_filesize = number_format($zv_filesize/1024/1024,1);
        $zv_filesize_units = TEXT_FILESIZE_MEGS;
      } else {
        $zv_filesize = number_format($zv_filesize);
        $zv_filesize_units = TEXT_FILESIZE_BYTES;
      }
    }
    $data['filesize'] = $zv_filesize;
    $data['filesize_units'] = $zv_filesize_units;

    // pubsub
    $zco_notifier->notify('NOTIFY_MODULE_DOWNLOAD_TEMPLATE_DETAILS', $result->fields, $data);

    $downloads[] = $data;
    $result->MoveNext();
  }
}

$downloadsNotAvailableYet = $downloadsOnThisOrder && sizeof($downloads) < 1;

