<?php
/**
 * index.php -- This is the main hub file for the Zen Cart installer
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: index.php 17018 2010-07-27 07:25:41Z drbyte $
 */

  define('IS_ADMIN_FLAG',false);
/*
 * Ensure that the include_path can handle relative paths, before we try to load any files
 */
  if (!strstr(ini_get('include_path'), '.')) ini_set('include_path', '.' . PATH_SEPARATOR . ini_get('include_path'));
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
