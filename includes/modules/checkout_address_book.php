<?php
/**
 * checkout_address_book.php
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Aug 08 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$customer = new Customer;
$addresses = $customer->getFormattedAddressBookList();

$radio_buttons = count($addresses);

$zco_notifier->notify('NOTIFY_MODULE_END_CHECKOUT_ADDRESS_BOOK', $customer, $addresses);
