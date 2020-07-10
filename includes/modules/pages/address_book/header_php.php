<?php
/**
 * Header code file for the Address Book page
 *
 * @package page
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Apr 30 Modified in v1.5.6b $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ADDRESS_BOOK');

if (!zen_is_logged_in()) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

$addresses_query = "SELECT address_book_id, entry_firstname as firstname, entry_lastname as lastname,
                           entry_company as company, entry_street_address as street_address,
                           entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                           entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id
                    FROM   " . TABLE_ADDRESS_BOOK . "
                    WHERE  customers_id = :customersID
                    ORDER BY firstname, lastname";

$addresses_query = $db->bindVars($addresses_query, ':customersID', $_SESSION['customer_id'], 'integer');
$addresses = $db->Execute($addresses_query);

$addressArray = array();

while (!$addresses->EOF) {
  $format_id = zen_get_address_format_id($addresses->fields['country_id']);

  $addressArray[] = array(
      'firstname'=>$addresses->fields['firstname'],
      'lastname'=>$addresses->fields['lastname'],
      'address_book_id'=>$addresses->fields['address_book_id'],
      'format_id'=>$format_id,
      'address'=>$addresses->fields,
      );
  $addresses->MoveNext();
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ADDRESS_BOOK');
