<?php
/**
 * index.php -- This is the main hub file for the Zen Cart installer
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Thu Oct 4 22:17:16 2012 -0400 Modified in v1.5.2 $
 */

  define('IS_ADMIN_FLAG',false);
/*
 * Ensure that the include_path can handle relative paths, before we try to load any files
 */
  if (!strstr(ini_get('include_path'), '.')) ini_set('include_path', '.' . PATH_SEPARATOR . ini_get('include_path'));

/**
 * Bypass PHP file caching systems if active, since it interferes with files changed by zc_install
 */
//APC
// if (ini_get('apc.enabled') == 1) {
//   if (@ini_get('apc.enable') == 1) @ini_set('apc.filters', '-configure\.php$');
//   $test1 = realpath(dirname(basename(__FILE__)) . '/../includes/configure.php');
//   $test2 = realpath(dirname(basename(__FILE__)) . '/../admin/includes/configure.php');
//   if (file_exists($test1)) $filesToDecache[] = $test1;
//   if (file_exists($test2)) $filesToDecache[] = $test2;
//   if (sizeof($filesToDecache)) apc_delete_file($filesToDecache);
// }
if (function_exists('apc_clear_cache')) @apc_clear_cache();
//XCACHE
if (function_exists('xcache_clear_cache')) @xcache_clear_cache();
//EA
if (@ini_get('eaccelerator.enable') == 1) {
  @ini_set('eaccelerator.filter', '!*/configure.php');
  $info = eaccelerator_info();
  //if ($info['version'] < '0.9.5.3')
   @ini_set('eaccelerator.enable', 0);
}

/*
 * Initialize system core components
 */
  require('includes/application_top.php');

  /* This is for debug purposes to run installer from command line. Set to true to enable it:  */
  if (false) {
    if ($argc > 0) {
      for ($i=1;$i<$argc;$i++) {
        $it = preg_split("/=/",$argv[$i]);
        $_GET[$it[0]] = $it[1];
        // parse_str($argv[$i],$tmp);
        // $_REQUEST = array_merge($_REQUEST, $tmp);
      }
    }
if (!isset($_GET) && isset($_SERVER["argc"]) && $_SERVER["argc"] > 1) {
  for($i=1;$i<$_SERVER["argc"];$i++) {
    list($key, $val) = explode('=', $_SERVER["argv"][$i]);
    $_GET[$key] = $_REQUEST[$key] = $val;
  }
}
  }

  // init vars:
	$zc_first_field = '';

  // begin processing page-specific actions
  if (!isset($_GET['main_page']) || !zen_not_null($_GET['main_page'])) $_GET['main_page'] = 'index';
  $current_page = $_GET['main_page'];
  $page_directory = 'includes/modules/pages/' . $current_page;
  $language_page_directory = 'includes/languages/' . $language . '/';
  require($language_page_directory . $current_page . '.php');
  require('includes/languages/' . $language . '.php');

  require($page_directory . '/header_php.php');
  require(DIR_WS_INSTALL_TEMPLATE . 'common/html_header.php');
  require(DIR_WS_INSTALL_TEMPLATE . 'common/main_template_vars.php');
  require(DIR_WS_INSTALL_TEMPLATE . 'common/tpl_main_page.php');
