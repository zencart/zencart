<?php
/**
 * Header code file for the customer's Account page
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Aug 03 Modified in v2.0.0-alpha1 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ACCOUNT');

if (!zen_is_logged_in()) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

$breadcrumb->add(NAVBAR_TITLE);

$customer = new Customer;
$ordersArray = $customer->getOrderHistory($max = 3);

$gv_balance = $customer->getData('gv_balance');
$customer_has_gv_balance = !empty($gv_balance);
$customer_gv_balance = !is_null($gv_balance) ? $currencies->format($gv_balance) : false;

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ACCOUNT');
