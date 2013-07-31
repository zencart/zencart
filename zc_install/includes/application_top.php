<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

@ini_set("arg_separator.output", "&");

// Check PHP version
if (version_compare(PHP_VERSION, '5.2.14', '<'))
{
  require (DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'templates/tpl_php_version_problem.php');
  die('');
}
if (file_exists(DIR_FS_INSTALL . 'includes/localConfig.php'))
  require (DIR_FS_INSTALL . 'includes/localConfig.php');

/**
 * set the level of system-inspection logging -- can by overridden by adding ?v={mode} to command line, for non-ajax steps, or generically set in localConfig.php
 */
if (!isset($debug_logging)) $debug_logging = 'file';
if (isset($_GET['v']) && in_array($_GET['v'], array('screen', '1', 'true', 'TRUE'))) $debug_logging = 'screen';
define('VERBOSE_SYSTEMCHECKER', $debug_logging);

/**
 * read some file locations from the "store / catalog" configure.php
 */
require (DIR_FS_INSTALL . 'includes/classes/class.zcConfigureFileReader.php');
$configReader = new zcConfigureFileReader(DIR_FS_ROOT . 'includes/configure.php');
if (!defined('DIR_FS_LOGS')) {
  // Use the systemChecker to see if one is defined in the store configure.php
  $logDir = $configReader->getDefine('DIR_FS_LOGS');
  if (!isset($logDir)) $logDir = DIR_FS_ROOT . 'logs';
  define('DIR_FS_LOGS', $logDir);
}
if (!defined('DIR_FS_SQL_CACHE')) {
  // Use the systemChecker to see if one is defined in the store configure.php
  $logDir = $configReader->getDefine('DIR_FS_SQL_CACHE');
  if (!isset($logDir)) $logDir = DIR_FS_ROOT . 'cache';
  define('DIR_FS_SQL_CACHE', $logDir);
}
if (!defined('DIR_FS_DOWNLOAD_PUBLIC')) {
  // Use the systemChecker to see if one is defined in the store configure.php
  $logDir = $configReader->getDefine('DIR_FS_DOWNLOAD_PUBLIC');
  if (!isset($logDir)) $logDir = DIR_FS_ROOT . 'pub';
  define('DIR_FS_DOWNLOAD_PUBLIC', $logDir);
}

/**
 * set the level of error reporting
 */
if (!defined('DEBUG_LOG_FOLDER')) define('DEBUG_LOG_FOLDER', DIR_FS_LOGS); 
error_reporting(version_compare(PHP_VERSION, 5.3, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE : version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT : E_ALL & ~E_NOTICE);
$debug_logfile_path = DEBUG_LOG_FOLDER . '/zcInstallDEBUG-' . time() . '-' . mt_rand(1000, 999999) . '.log';
@ini_set('log_errors', 1);
@ini_set('log_errors_max_len', 0);
@ini_set('error_log', $debug_logfile_path);
if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true)
{
  @ini_set('display_errors', 1);  // to screen
} else
{
  @ini_set('display_errors', 0);
}
/**
 * Timezone problem detection
 */
if (PHP_VERSION >= '5.3' && ini_get('date.timezone') == '' && @date_default_timezone_get() == '')
{
  die('ERROR: date.timezone is not set in php.ini. Please contact your hosting company to set the timezone in the server PHP configuration before continuing.');
} elseif (PHP_VERSION >= '5.1')
{
  @date_default_timezone_set(date_default_timezone_get());
}

/*
 * check settings for, and then turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
 */
if (version_compare(PHP_VERSION, 5.4, '<'))
{
  $php_magic_quotes_runtime = (@get_magic_quotes_runtime() > 0) ? 'ON' : 'OFF';
  if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime'))
    set_magic_quotes_runtime(0);
  $val = @ini_get('magic_quotes_sybase');
  if (is_string($val) && strtolower($val) == 'on')
    $val = 1;
  $php_magic_quotes_sybase = ((int)$val > 0) ? 'ON' : 'OFF';
  if ((int)$val != 0)
    @ini_set('magic_quotes_sybase', 0);
  unset($val);
}

/*
 * Bypass PHP file caching systems if active, since it interferes with files changed by zc_install (such as progress.json and configure.php)
 */
//APC
if (function_exists('apc_clear_cache')) @apc_clear_cache();
//XCACHE
if (function_exists('xcache_clear_cache')) @xcache_clear_cache();
//EA
if (@ini_get('eaccelerator.enable') == 1) {
  @ini_set('eaccelerator.enable', 0);
}

// define the project version
require (DIR_FS_INSTALL . 'includes/version.php');

// set php_self in the local scope
require (DIR_FS_ROOT . 'includes/classes/class.base.php');
require (DIR_FS_ROOT . 'includes/classes/class.notifier.php');
require (DIR_FS_INSTALL . 'includes/functions/general.php');
require (DIR_FS_INSTALL . 'includes/functions/password_funcs.php');
require(DIR_FS_INSTALL . 'includes/languages/languages.php');
zen_sanitize_request();
/**
 * set the type of request (secure or not)
 */
$request_type = (((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1'))) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_BY']) && strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']), 'SSL') !== false) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && (strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') !== false || strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), str_replace('https://', '', HTTPS_SERVER)) !== false)) ||
                 (isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:') ||
                 (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL'] == '1' || strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on')) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'ssl' || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')) ||
                 (isset($_SERVER['HTTP_SSLSESSIONID']) && $_SERVER['HTTP_SSLSESSIONID'] != '') ||
                 (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) ? 'SSL' : 'NONSSL';

/*
 * debug params
 */
define('ZC_UPG_DEBUG',  (!isset($_GET['debug'])  && !isset($_POST['debug'])  || (isset($_POST['debug'])  && $_POST['debug'] == '')) ? false : true);
define('ZC_UPG_DEBUG2', (!isset($_GET['debug2']) && !isset($_POST['debug2']) || (isset($_POST['debug2']) && $_POST['debug2'] == '')) ? false : true);
define('ZC_UPG_DEBUG3', (!isset($_GET['debug3']) && !isset($_POST['debug3']) || (isset($_POST['debug3']) && $_POST['debug3'] == '')) ? false : true);



/*
 * template determination
 */
define('DIR_WS_INSTALL_TEMPLATE', 'includes/template/');
require (DIR_FS_INSTALL . 'includes/classes/class.systemChecker.php');
require (DIR_FS_ROOT . 'includes/classes/vendors/yaml/lib/class.sfYaml.php');
require (DIR_FS_INSTALL . 'includes/classes/class.zcRegistry.php');
require (DIR_FS_ROOT . 'includes/classes/vendors/yaml/lib/class.sfYamlParser.php');
require (DIR_FS_ROOT . 'includes/classes/vendors/yaml/lib/class.sfYamlInline.php');
if (!isset($_GET['main_page'])) $_GET['main_page'] = 'index';
$current_page = preg_replace('/[^a-z0-9_]/', '', $_GET['main_page']);
if ($current_page == '' || !file_exists('includes/modules/pages/' . $current_page)) $_GET['main_page'] = $current_page = 'index';
$page_directory = 'includes/modules/pages/' . $current_page;
/*
 * language determination
 */
$language = NULL;
if (isset($_POST['lng']))
{
  $lng = preg_replace('/[^a-zA-Z_]/', '', $_POST['lng']);
  if ($lng == '')
  {
    $lng = 'en_us';
  }
  if (!file_exists(DIR_FS_INSTALL . 'includes/languages/' . $languagesInstalled[$lng][fileName] . '.php'))
  {
    $lng = 'en_us';
  }
} else
{
  $lng = (isset($_GET['lng']) && $_GET['lng'] != '') ? preg_replace('/[^a-zA-Z_]/', '', $_GET['lng']) : 'en_us';
  if ($lng == '')
  {
    $lng = 'en_us';
  }
  if (!file_exists(DIR_FS_INSTALL . 'includes/languages/' . $languagesInstalled[$lng][fileName] . '.php'))
  {
    $lng = 'en_us';
  }
}
$lng_short = substr($lng, 0, strpos($lng, '_'));
require(DIR_FS_INSTALL . 'includes/languages/' . $languagesInstalled[$lng][fileName] . '.php');
