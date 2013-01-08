<?php
/**
 * application_top.php Common actions carried out at the start of each page invocation.
 *
 * Initializes common classes & methods. Controlled by an array which describes
 * the elements to be initialised and the order in which that happens.
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Fri Jul 6 11:57:44 2012 -0400 Modified in v1.5.1 $
 */
/**
 * inoculate against hack attempts which waste CPU cycles
 */
$contaminated = (isset($_FILES['GLOBALS']) || isset($_REQUEST['GLOBALS'])) ? true : false;
$paramsToAvoid = array('GLOBALS', '_COOKIE', '_ENV', '_FILES', '_GET', '_POST', '_REQUEST', '_SERVER', '_SESSION', 'HTTP_COOKIE_VARS', 'HTTP_ENV_VARS', 'HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_POST_FILES', 'HTTP_RAW_POST_DATA', 'HTTP_SERVER_VARS', 'HTTP_SESSION_VARS');
$paramsToAvoid[] = 'autoLoadConfig';
$paramsToAvoid[] = 'mosConfig_absolute_path';
$paramsToAvoid[] = 'hash';
$paramsToAvoid[] = 'main';
foreach($paramsToAvoid as $key) {
  if (isset($_GET[$key]) || isset($_POST[$key]) || isset($_COOKIE[$key])) {
    $contaminated = true;
    break;
  }
}
$paramsToCheck = array('main_page', 'cPath', 'products_id', 'language', 'currency', 'action', 'manufacturers_id', 'pID', 'pid', 'reviews_id', 'filter_id', 'zenid', 'sort', 'number_of_uploads', 'notify', 'page_holder', 'chapter', 'alpha_filter_id', 'typefilter', 'disp_order', 'id', 'key', 'music_genre_id', 'record_company_id', 'set_session_login', 'faq_item', 'edit', 'delete', 'search_in_description', 'dfrom', 'pfrom', 'dto', 'pto', 'inc_subcat', 'payment_error', 'order', 'gv_no', 'pos', 'addr', 'error', 'count', 'error_message', 'info_message', 'cID', 'page', 'credit_class_error_code');
if (!$contaminated) {
  foreach($paramsToCheck as $key) {
    if (isset($_GET[$key]) && !is_array($_GET[$key])) {
      if (substr($_GET[$key], 0, 4) == 'http' || strstr($_GET[$key], '//')) {
        $contaminated = true;
        break;
      }
      $len = (in_array($key, array('zenid', 'error_message', 'payment_error'))) ? 255 : 43;
      if (isset($_GET[$key]) && strlen($_GET[$key]) > $len) {
        $contaminated = true;
        break;
      }
    }
  }
}
unset($paramsToCheck, $paramsToAvoid, $key);
if ($contaminated)
{
  header('HTTP/1.1 406 Not Acceptable');
  exit(0);
}
unset($contaminated, $len);
/* *** END OF INNOCULATION *** */
/**
 * boolean used to see if we are in the admin script, obviously set to false here.
 */
define('IS_ADMIN_FLAG', false);
/**
 * integer saves the time at which the script started.
 */
define('PAGE_PARSE_START_TIME', microtime());
//  define('DISPLAY_PAGE_PARSE_TIME', 'true');
@ini_set("arg_separator.output","&");
/**
 * Set the local configuration parameters - mainly for developers
 */
if (file_exists('includes/local/configure.php')) {
  /**
   * load any local(user created) configure file.
   */
  include('includes/local/configure.php');
}
/**
 * boolean if true the autoloader scripts will be parsed and their output shown. For debugging purposes only.
 */
define('DEBUG_AUTOLOAD', false);
/**
 * set the level of error reporting
 *
 * Note STRICT_ERROR_REPORTING should never be set to true on a production site. <br />
 * It is mainly there to show php warnings during testing/bug fixing phases.<br />
 */
if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
  @ini_set('display_errors', TRUE);
  error_reporting(version_compare(PHP_VERSION, 5.3, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE : version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT : E_ALL & ~E_NOTICE);
} else {
  error_reporting(0);
}
/*
 * turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
 */
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);
if (version_compare(PHP_VERSION, 5.4, '<') && @ini_get('magic_quotes_sybase') != 0) @ini_set('magic_quotes_sybase', 0);
/**
 * check for and include load application parameters
 */
if (file_exists('includes/configure.php')) {
  /**
   * load the main configure file.
   */
  include('includes/configure.php');
} else {
  $problemString = 'includes/configure.php not found';
  require('includes/templates/template_default/templates/tpl_zc_install_suggested_default.php');
  exit;
}
/**
 * if main configure file doesn't contain valid info (ie: is dummy or doesn't match filestructure, display assistance page to suggest running the installer)
 */
if (!defined('DIR_FS_CATALOG') || !is_dir(DIR_FS_CATALOG.'/includes/classes')) {
  $problemString = 'includes/configure.php file contents invalid.  ie: DIR_FS_CATALOG not valid or not set';
  require('includes/templates/template_default/templates/tpl_zc_install_suggested_default.php');
  exit;
}
/**
 * include the list of extra configure files
 */
if ($za_dir = @dir(DIR_WS_INCLUDES . 'extra_configures')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('~^[^\._].*\.php$~i', $zv_file) > 0) {
      /**
       * load any user/contribution specific configuration files.
       */
      include(DIR_WS_INCLUDES . 'extra_configures/' . $zv_file);
    }
  }
  $za_dir->close();
  unset($za_dir);
}
$autoLoadConfig = array();
if (isset($loaderPrefix)) {
 $loaderPrefix = preg_replace('/[^a-z_]/', '', $loaderPrefix);
} else {
  $loaderPrefix = 'config';
}
$loader_file = $loaderPrefix . '.core.php';
require('includes/initsystem.php');
/**
 * determine install status
 */
if (( (!file_exists('includes/configure.php') && !file_exists('includes/local/configure.php')) ) || (DB_TYPE == '') || (!file_exists('includes/classes/db/' .DB_TYPE . '/query_factory.php')) || !file_exists('includes/autoload_func.php')) {
  $problemString = 'includes/configure.php file empty or file not found, OR wrong DB_TYPE set, OR cannot find includes/autoload_func.php which suggests paths are wrong or files were not uploaded correctly';
  require('includes/templates/template_default/templates/tpl_zc_install_suggested_default.php');
  header('location: zc_install/index.php');
  exit;
}
/**
 * load the autoloader interpreter code.
*/
require('includes/autoload_func.php');
/**
 * load the counter code
**/
if ($spider_flag == false) {
// counter and counter history
  require(DIR_WS_INCLUDES . 'counter.php');
}
// get customers unique IP that paypal does not touch
$customers_ip_address = $_SERVER['REMOTE_ADDR'];
if (!isset($_SESSION['customers_ip_address'])) {
  $_SESSION['customers_ip_address'] = $customers_ip_address;
}
