<?php
/**
 * checkout_address_book.php
 *
 * @package modules
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Jan 06 Modified in v1.5.6b $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$radio_buttons = 0;

$addresses_query = "select address_book_id, entry_firstname as firstname, entry_lastname as lastname,
                                 entry_company as company, entry_street_address as street_address,
                                 entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                                 entry_state as state, entry_zone_id as zone_id,
                                 entry_country_id as country_id
                          from " . TABLE_ADDRESS_BOOK . "
                          where customers_id = '" . (int)$_SESSION['customer_id'] . "'";

$addresses = $db->Execute($addresses_query);
if (!$addresses->EOF) $radio_buttons = $addresses->recordCount();

$zco_notifier->notify('NOTIFY_MODULE_END_CHECKOUT_ADDRESS_BOOK', $addresses_query, $addresses);
