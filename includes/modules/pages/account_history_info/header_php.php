<?php
/**
 * Header code file for the Account History Information/Details page (which displays details for a single specific order)
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Aug 02 Modified in v1.5.7 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ACCOUNT_HISTORY_INFO');

if (!zen_is_logged_in()) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if (empty($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
  zen_redirect(zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
}

$customer_info_query = "SELECT customers_id
                        FROM   " . TABLE_ORDERS . "
                        WHERE  orders_id = :ordersID
                        LIMIT 1";

$customer_info_query = $db->bindVars($customer_info_query, ':ordersID', $_GET['order_id'], 'integer');
$customer_info = $db->Execute($customer_info_query);

if ($customer_info->fields['customers_id'] != $_SESSION['customer_id']) {
  zen_redirect(zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
}

$statuses_query = "SELECT os.orders_status_name, osh.*
                   FROM   " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh
                   WHERE      osh.orders_id = :ordersID
                   AND        osh.orders_status_id = os.orders_status_id
                   AND        os.language_id = :languagesID
                   AND        osh.customer_notified >= 0
                   ORDER BY   osh.date_added";

$statuses_query = $db->bindVars($statuses_query, ':ordersID', $_GET['order_id'], 'integer');
$statuses_query = $db->bindVars($statuses_query, ':languagesID', $_SESSION['languages_id'], 'integer');
$statuses = $db->Execute($statuses_query);
$statusArray = array();

while (!$statuses->EOF) {
  $statusArray[] = $statuses->fields;
  $statuses->MoveNext();
}


require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2, zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
$breadcrumb->add(sprintf(NAVBAR_TITLE_3, $_GET['order_id']));

require(DIR_WS_CLASSES . 'order.php');
$order = new order($_GET['order_id']);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ACCOUNT_HISTORY_INFO');
