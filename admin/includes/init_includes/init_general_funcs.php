<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Fri Jul 6 11:57:44 2012 -0400 Modified in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * load the system wide functions
 *
 * @package admin
**/
// customization for the design layout
  define('BOX_WIDTH', 125); // how wide the boxes should be in pixels (default: 125)

// Define how do we update currency exchange rates
// Possible values are 'ecb', 'boc', 'oanda', 'xe', or '' (to disable the option).  HOWEVER: Note that using "xe" or "oanda" subjects you to TOS terms requiring you to subscribe to their services. Use at your own risk.
  define('CURRENCY_SERVER_PRIMARY', 'ecb');
  define('CURRENCY_SERVER_BACKUP', 'boc');

// include the database functions
  require(DIR_WS_FUNCTIONS . 'database.php');

// define our general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'functions_prices.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');
  require(DIR_WS_FUNCTIONS . 'functions_customers.php'); // partial copy of catalog functions customers for now
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_email.php');

/**
 * require the plugin support functions
 */
require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'plugin_support.php');

/**
 * Per-Page meta-tag editing functions
 */
require(DIR_WS_FUNCTIONS . 'functions_metatags.php');


// include the list of extra functions
  if ($za_dir = @dir(DIR_WS_FUNCTIONS . 'extra_functions')) {
    while ($zv_file = $za_dir->read()) {
      if (preg_match('~^[^\._].*\.php$~i', $zv_file) > 0) {
        require(DIR_WS_FUNCTIONS . 'extra_functions/' . $zv_file);
      }
    }
    $za_dir->close();
  }
if (isset($_GET) & sizeof($_GET) > 0 ) {
  foreach ($_GET as $key=>$value) {
    $_GET[$key] = strip_tags($value);
  }
}

// check for SSL configuration changes:
if (!defined('SSLPWSTATUSCHECK')) die('database upgrade required. please run the 1.3.9-to-1.5.0 upgrade via zc_install');
list($a, $b, $c) = explode(':', SSLPWSTATUSCHECK); $a = (int)$a; $b = (int)$b; $c = (int)$c;
$d = (ENABLE_SSL_ADMIN == 'true') ? '1' : '0';
$e = (substr(HTTP_SERVER, 0, 5) == 'https') ? '1' : '0';
$f = ':'.$d.':'.$e;
if ($a == 0) {
  if (($b == 0 && $d == 1) || ($c == 0 && $e == 1)) {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '1" . $f . "', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
    $db->Execute($sql);
    $sql = "UPDATE " . TABLE_ADMIN . " set pwd_last_change_date = '1990-01-01 14:02:22'";
    $db->Execute($sql);
  }
  if (($b == 1 && $d == 0) || ($c == 1 && $e == 0)) {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '0". $f . "', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
    $db->Execute($sql);
  }
} else if ($a == 1) {  // == 1
  if (($b == 1 && $d == 0) || ($c == 1 && $e == 0)) {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '0". $f . "', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
    $db->Execute($sql);
  }
  if (($b == 0 && $d == 1) || ($c == 0 && $e == 1)) {
    $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '1" . $f . "', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
    $db->Execute($sql);
  }
}
unset($a,$b,$c,$d,$e,$f);
// end ssl config change detection