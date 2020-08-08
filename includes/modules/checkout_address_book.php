<?php
/**
 * checkout_address_book.php
 *
 * @package modules
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.5.8 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$customer = new Customer;
$addresses = $customer->getFormattedAddressBookList();

$radio_buttons = count($addresses);

$zco_notifier->notify('NOTIFY_MODULE_END_CHECKOUT_ADDRESS_BOOK', $customer, $addresses);
