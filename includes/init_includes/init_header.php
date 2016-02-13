<?php
/**
 * header code, mainly concerned with adding to messagestack when certain warnings are applicable
 *
 * @package templateStructure
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * set which precautions should be checked
 */
/**
 * should a message be displayed if install directory exists
 */
define('WARN_INSTALL_EXISTENCE', 'true');
/**
 * should a message be displayed if  config directory is writeable
 */
define('WARN_CONFIG_WRITEABLE', 'true');
/**
 * should a message be displayed if sql cache directory not writeable
 */
define('WARN_SQL_CACHE_DIRECTORY_NOT_WRITEABLE', 'true');
/**
 * should a message be displayed if session.autostart is on in php.ini
 */
define('WARN_SESSION_AUTO_START', 'true');
/**
 * should a message be displayed if download directory not readable
 */
define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');
/**
 * should a message be displayed if system detects version problem with the database
 */
define('WARN_DATABASE_VERSION_PROBLEM','true');
// check if the installer directory exists, and warn of its existence
if (WARN_INSTALL_EXISTENCE == 'true') {
  $check_path = realpath(dirname(basename($PHP_SELF)) . '/zc_install');
  if (is_dir($check_path)) {
    $messageStack->add('header', sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, ($check_path == '' ? '..../zc_install' : $check_path)), 'warning');
  }
}

// check if the configure.php file is writeable
if (WARN_CONFIG_WRITEABLE == 'true') {
  $check_path = realpath(dirname(basename($PHP_SELF)) . '/includes/configure.php');
  if (file_exists($check_path) && is__writeable($check_path)) {
    $messageStack->add('header', sprintf(WARNING_CONFIG_FILE_WRITEABLE, ($check_path == '' ? '..../includes/configure.php' : $check_path)), 'warning');
  }
}

// check if the sql cache folder is writeable
if (WARN_SQL_CACHE_DIRECTORY_NOT_WRITEABLE == 'true' && strtolower(SQL_CACHE_METHOD) == 'file') {
  if (!is_dir(DIR_FS_SQL_CACHE)) {
    $messageStack->add('header', WARNING_SQL_CACHE_DIRECTORY_NON_EXISTENT, 'warning');
  } elseif (!is_writeable(DIR_FS_SQL_CACHE)) {
    $messageStack->add('header', WARNING_SQL_CACHE_DIRECTORY_NOT_WRITEABLE, 'warning');
  }
}

// give the visitors a message that the website will be down at ... time
if ( (WARN_BEFORE_DOWN_FOR_MAINTENANCE == 'true') && (DOWN_FOR_MAINTENANCE == 'false') ) {
  $messageStack->add('header', TEXT_BEFORE_DOWN_FOR_MAINTENANCE . PERIOD_BEFORE_DOWN_FOR_MAINTENANCE);
}

// this will let the admin know that the website is DOWN FOR MAINTENANCE to the public
if ( (DOWN_FOR_MAINTENANCE == 'true') && (strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) ) {
  $messageStack->add('header', TEXT_ADMIN_DOWN_FOR_MAINTENANCE, 'warning');
}

// check session.auto_start is disabled
if ( (function_exists('ini_get')) && (WARN_SESSION_AUTO_START == 'true') ) {
  if (ini_get('session.auto_start') == '1') {
    $messageStack->add('header', WARNING_SESSION_AUTO_START, 'warning');
  }
}

// to warn if the "downloads" folder is not readable (ie: not found, etc)
if ( (WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true') && (DOWNLOAD_ENABLED == 'true') ) {
  if (!is_dir(DIR_FS_DOWNLOAD)) {
    $messageStack->add('header', WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT, 'warning');
  }
}

// check database version against source code
$zv_db_patch_ok = true; // we start with true
if (WARN_DATABASE_VERSION_PROBLEM != 'false') {
  $result = $db->Execute("SELECT project_version_major, project_version_minor FROM " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Database'");
  $zv_db_patch_level_found = $result->fields['project_version_major']. '.' . $result->fields['project_version_minor'];
  $zv_db_patch_level_expected = EXPECTED_DATABASE_VERSION_MAJOR . '.' . EXPECTED_DATABASE_VERSION_MINOR;
  if ($zv_db_patch_level_expected=='.' || ($zv_db_patch_level_found < $zv_db_patch_level_expected) ) {
    $zv_db_patch_ok = false;
    $messageStack->add('header', WARNING_DATABASE_VERSION_OUT_OF_DATE, 'warning');
  }
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
$skipCountryCheck = false;
$zco_notifier->notify('NOTIFY_INIT_HEADER_CHECK_COUNTRY', array(), $skipCountryCheck);
if (!$skipCountryCheck) {
    if ($_SESSION['customer_id'] && zcRequest::readGet('main_page') != FILENAME_ADDRESS_BOOK_PROCESS && zcRequest::readGet('main_page') != FILENAME_LOGOFF) {
        $addresses_query = "SELECT address_book_id, entry_country_id AS country_id, entry_firstname AS firstname, entry_lastname AS lastname
                      FROM   " . TABLE_ADDRESS_BOOK . "
                      WHERE  customers_id = :customersID
                      ORDER BY firstname, lastname";

        $addresses_query = $db->bindVars($addresses_query, ':customersID', $_SESSION['customer_id'], 'integer');
        $addresses = $db->Execute($addresses_query);

        while (!$addresses->EOF) {
            if (zen_get_country_name($addresses->fields['country_id'], true) == '') {
                $messageStack->add_session('addressbook',
                    sprintf(ERROR_TEXT_COUNTRY_DISABLED_PLEASE_CHANGE,
                        zen_get_country_name($addresses->fields['country_id'], false)),
                    'error');
                zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $addresses->fields['address_book_id'],
                    'SSL'));
            }
            $addresses->MoveNext();
        }
    }
}
$zcTplManager = new \ZenCart\View\TplVarManager();
$zcView = new \ZenCart\View\View($zcTplManager, $messageStack, $breadcrumb);
