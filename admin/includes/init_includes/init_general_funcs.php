<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Mar 23 Modified in v2.1.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// customization for the design layout
define('BOX_WIDTH', 125); // how wide the boxes should be in pixels (default: 125)


require 'includes/init_includes/init_pci_settings.php';

require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'database.php';

require DIR_WS_FUNCTIONS . 'general.php';
require DIR_WS_FUNCTIONS . 'functions_mfa.php';

require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general_shared.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_attributes.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_files.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_traffic.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_strings.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_search.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_addresses.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_dates.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_products.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_categories.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_prices.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_taxes.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_gvcoupons.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_customers.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_customer_groups.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_lookups.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_urls.php';

require DIR_WS_FUNCTIONS . 'html_output.php';

require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_email.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_ezpages.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'plugin_support.php';
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'password_funcs.php';

/**
 * meta-tag editing functions
 */
require DIR_WS_FUNCTIONS . 'functions_metatags.php';


// include the list of extra functions
foreach (zen_get_files_in_directory(DIR_WS_FUNCTIONS . 'extra_functions') as $file) {
    require $file;
}

if (isset($_GET) & sizeof($_GET) > 0 ) {
  foreach ($_GET as $key=>$value) {
    $_GET[$key] = strip_tags($value);
  }
}

// check for SSL configuration changes:
if (!defined('SSLPWSTATUSCHECK')) die('database upgrade required. please run the 1.3.9-to-1.5.0 upgrade via zc_install');
$e = (substr(HTTP_SERVER, 0, 5) == 'https') ? '1' : '0';
if (SSLPWSTATUSCHECK == '') {
  $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '".$e.':'.$e."', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
  $db->Execute($sql);
  die('<meta http-equiv="Refresh" content="0">One-time auto-configuration completed. Please refresh the page.');
}
list($a, $c) = explode(':', SSLPWSTATUSCHECK); $a = (int)$a; $c = (int)$c;
if ($a == 0) {
  if ($c == 0 && $e == 1) { // was nonSSL but now is SSL, so need to exp pwds
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '1:" . $e . "', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
    $db->Execute($sql);
    $sql = "UPDATE " . TABLE_ADMIN . " set pwd_last_change_date = '1990-01-01 14:02:22'";
    $db->Execute($sql);
  }
  if ($c == 1 && $e == 0) { // was nonSSL then SSL and now nonSSL again, so recording that we're now nonSSL
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '0:". $e . "', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
    $db->Execute($sql);
  }
} else if ($a == 1) {  // == 1
  if ($c == 1 && $e == 0) {  // was SSL, but is now nonSSL, so recording the change
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '0:". $e . "', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
    $db->Execute($sql);
  }
  if ($c == 0 && $e == 1) {  // was changed to SSL last time checked, so recording that is all SSL now
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '1:" . $e . "', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
    $db->Execute($sql);
  }
}
unset($a,$c,$e);
// end ssl config change detection
