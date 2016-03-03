<?php
/**
 * header code, mainly concerned with adding to messagestack when certain warnings are applicable
 *
 * @package templateStructure
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Wed Dec 30 14:19:47 2015 -0500 Modified in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// give the visitors a message that the website will be down at ... time
if ( (WARN_BEFORE_DOWN_FOR_MAINTENANCE == 'true') && (DOWN_FOR_MAINTENANCE == 'false') ) {
  $messageStack->add('header', TEXT_BEFORE_DOWN_FOR_MAINTENANCE . PERIOD_BEFORE_DOWN_FOR_MAINTENANCE);
}

// this will let the admin know that the website is DOWN FOR MAINTENANCE to the public
if ( (DOWN_FOR_MAINTENANCE == 'true') && (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) ) {
  $messageStack->add('header', TEXT_ADMIN_DOWN_FOR_MAINTENANCE, 'warning');
}

// Alerting about payment modules in testing/debug mode

if (defined('MODULE_PAYMENT_PAYPAL_IPN_DEBUG') && (MODULE_PAYMENT_PAYPAL_IPN_DEBUG == 'true' || MODULE_PAYMENT_PAYPAL_TESTING == 'Test')) {
  $messageStack->add('header', 'PAYPAL IS IN TESTING MODE', 'warning');
}
if ((defined('MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS') && MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS == 'True' && defined('MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE') && MODULE_PAYMENT_AUTHORIZENET_AIM_TESTMODE == 'Test') || (defined('MODULE_PAYMENT_AUTHORIZENET_STATUS') && MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True' && defined('MODULE_PAYMENT_AUTHORIZENET_TESTMODE') && MODULE_PAYMENT_AUTHORIZENET_TESTMODE =='Test' ) ) {
  $messageStack->add('header', 'AUTHORIZENET IS IN TESTING MODE', 'warning');
}
if (defined('MODULE_SHIPPING_USPS_SERVER') &&   MODULE_SHIPPING_USPS_SERVER == 'test' ) {
  $messageStack->add('header', 'USPS IS IN TESTING MODE', 'warning');
}

// Alerts for EZ-Pages
if (EZPAGES_STATUS_HEADER == '2' && (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR']))) {
  $messageStack->add('header', TEXT_EZPAGES_STATUS_HEADER_ADMIN, 'caution');
}
if (EZPAGES_STATUS_FOOTER == '2' && (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR']))) {
  $messageStack->add('header', TEXT_EZPAGES_STATUS_FOOTER_ADMIN, 'caution');
}
if (EZPAGES_STATUS_SIDEBOX == '2' && (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR']))) {
  $messageStack->add('header', TEXT_EZPAGES_STATUS_SIDEBOX_ADMIN, 'caution');
}
if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
  $messageStack->add('header', 'STRICT ERROR REPORTING IS ON', 'warning');
}


// if down for maintenance, prevent indexing
if ( (DOWN_FOR_MAINTENANCE == 'true') && (!strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) ) {
  header("HTTP/1.1 503 Service Unavailable");
}

/* Check for invalid countries in customer's address book.
 * If a customer is logged in, check to see that the customers' address(es) still contain valid countries.
 * If not, redirect to the address-book page for changes.
 */
if ($_SESSION['customer_id'] && $_GET['main_page'] != FILENAME_ADDRESS_BOOK_PROCESS && $_GET['main_page'] != FILENAME_LOGOFF) {
  $addresses_query = "SELECT address_book_id, entry_country_id as country_id, entry_firstname as firstname, entry_lastname as lastname
                      FROM   " . TABLE_ADDRESS_BOOK . "
                      WHERE  customers_id = :customersID
                      ORDER BY firstname, lastname";

  $addresses_query = $db->bindVars($addresses_query, ':customersID', $_SESSION['customer_id'], 'integer');
  $addresses = $db->Execute($addresses_query);

  while (!$addresses->EOF) {
    if (zen_get_country_name($addresses->fields['country_id'], TRUE) == '') {
      $messageStack->add_session('addressbook', sprintf(ERROR_TEXT_COUNTRY_DISABLED_PLEASE_CHANGE, zen_get_country_name($addresses->fields['country_id'], FALSE)), 'error');
      zen_redirect (zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $addresses->fields['address_book_id'], 'SSL'));
    }
    $addresses->MoveNext();
  }
}
