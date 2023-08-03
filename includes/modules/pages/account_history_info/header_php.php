<?php
/**
 * Header code file for the Account History Information/Details page (which displays details for a single specific order)
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jun 12 Modified in v1.5.8-alpha $
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

require DIR_WS_CLASSES . 'order.php';
$order = new order($_GET['order_id']);
$statusArray = $order->statuses;    //- For compatability with pre-existing templates

$customer = new Customer;
if (!$customer->isSameAsLoggedIn($order->info['customer_id'])) {
     zen_redirect(zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
}

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2, zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
$breadcrumb->add(sprintf(NAVBAR_TITLE_3, $_GET['order_id']));

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ACCOUNT_HISTORY_INFO');
