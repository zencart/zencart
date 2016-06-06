<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   Modified in v1.6.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * load the system wide functions
 *
 * @package admin
**/
<<<<<<< HEAD
  require(DIR_WS_FUNCTIONS . 'functions_helpers.php');
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general.php');
  require(DIR_WS_FUNCTIONS . 'functions_admin_menu.php');
  require(DIR_WS_FUNCTIONS . 'functions_crud.php');
  require(DIR_WS_FUNCTIONS . 'functions_system.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_email.php');
=======
require(DIR_WS_FUNCTIONS . 'functions_helpers.php');
require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general.php');
require(DIR_WS_FUNCTIONS . 'functions_admin_menu.php');
require(DIR_WS_FUNCTIONS . 'functions_crud.php');
require(DIR_WS_FUNCTIONS . 'functions_system.php');
require(DIR_WS_FUNCTIONS . 'html_output.php');
require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_email.php');
>>>>>>> refs/remotes/zencart/v160

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
$e = (substr(HTTP_SERVER, 0, 5) == 'https') ? '1' : '0';
if (SSLPWSTATUSCHECK == '') {
  $sql = "UPDATE " . TABLE_CONFIGURATION . " set configuration_value = '".$e.':'.$e."', last_modified = now() where configuration_key = 'SSLPWSTATUSCHECK'";
  $db->Execute($sql);
  die('One-time auto-configuration completed. Please refresh the page.');
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
<<<<<<< HEAD
=======

>>>>>>> refs/remotes/zencart/v160
