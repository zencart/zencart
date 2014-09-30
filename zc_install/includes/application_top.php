<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

@ini_set("arg_separator.output", "&");
@set_time_limit(250);

if (file_exists(DIR_FS_INSTALL . 'includes/localConfig.php')) {
  require (DIR_FS_INSTALL . 'includes/localConfig.php');
}

$val = getenv('HABITAT');
$habitat = ($val == 'zencart' || (isset($_SERVER['USER']) && $_SERVER['USER'] == 'vagrant'));
if ($habitat) {
  define('DEVELOPER_MODE', true);
}

$controller = 'main';
/* detect CLI params */
if (isset($argc) && $argc > 0) {
  for ($i=1;$i<$argc;$i++) {
    $it = preg_split("/=/",$argv[$i]);
    $_GET[$it[0]] = (isset($it[1])) ? $it[1] : $it[0];
    // parse_str($argv[$i],$tmp);
    // $_REQUEST = array_merge($_REQUEST, $tmp);
    if ($it[0] == 'cli') $controller = 'cli';
    if ($it[0] == 'v' || $it[0] == 'verbose') $debug_logging = 'screen';
  }
}
if (!isset($_GET) && isset($_SERVER["argc"]) && $_SERVER["argc"] > 1) {
  for($i=1;$i<$_SERVER["argc"];$i++) {
    list($key, $val) = explode('=', $_SERVER["argv"][$i]);
    $_GET[$key] = $_REQUEST[$key] = $val;
    if ($key == 'cli') $controller = 'cli';
    if ($key == 'v' || $key == 'verbose') $debug_logging = 'screen';
  }
}

/**
 * set the level of system-inspection logging -- can by overridden by adding ?v={mode} to command line, for non-ajax steps, or generically set in localConfig.php
 */
if (!isset($debug_logging)) $debug_logging = 'file';
if (isset($_GET['v']) && in_array($_GET['v'], array('screen', '1', 'true', 'TRUE'))) $debug_logging = 'screen';
define('VERBOSE_SYSTEMCHECKER', $debug_logging);
if (VERBOSE_SYSTEMCHECKER == 'screen' && $controller == 'cli') echo 'Verbose mode enabled.' . "\n";

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
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
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
if (ini_get('date.timezone') == '' && @date_default_timezone_get() == '')
{
  include ('../includes/extra_configures/set_time_zone.php');
}
// re-test
if (ini_get('date.timezone') == '' && @date_default_timezone_get() == '')
{
  die('ERROR: date.timezone is not set in php.ini. You have two options: 1-Edit /includes/extra_configures/set_time_zone.php to set the $TZ variable manually, or 2-Contact your hosting company to set the timezone correctly in the server PHP configuration before continuing.');
} else
{
  @date_default_timezone_set(date_default_timezone_get());
}

/*
 * Bypass PHP file caching systems if active, since it interferes with files changed by zc_install (such as progress.json and configure.php)
 */
if (!isset($_GET['cacheignore'])) {
  //APC
  if (function_exists('apc_clear_cache')) @apc_clear_cache();
  //XCACHE
  //@TODO - find a way to prevent admin login prompts with xcache
  // if (function_exists('xcache_clear_cache')) @xcache_clear_cache();
  //EA
  if (@ini_get('eaccelerator.enable') == 1) {
    @ini_set('eaccelerator.enable', 0);
  }
}

// define the project version
require (DIR_FS_INSTALL . 'includes/version.php');
/**
 * include the list of extra configure files
 */
if ($za_dir = @dir(DIR_FS_INSTALL . 'includes/extra_configures')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('~^[^\._].*\.php$~i', $zv_file) > 0) {
      /**
       * load any user/contribution specific configuration files.
       */
      include(DIR_FS_INSTALL . 'includes/extra_configures/' . $zv_file);
    }
  }
  $za_dir->close();
}
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
