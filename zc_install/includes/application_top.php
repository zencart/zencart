<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
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
 * set the level of error reporting
 */
error_reporting(version_compare(PHP_VERSION, 5.3, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE : version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT : E_ALL & ~E_NOTICE);
$debug_logfile_path = DEBUG_LOG_FOLDER . '/zcInstallDEBUG-' . time() . '-' . mt_rand(1000, 999999) . '.log';
@ini_set('log_errors', 1);
@ini_set('log_errors_max_len', 0);
@ini_set('error_log', $debug_logfile_path);
if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true)
{
  @ini_set('display_errors', 1);
} else
{
  @ini_set('display_errors', 0);
}
/**
 * Timezone problem detection
 */
if (PHP_VERSION >= '5.3' && ini_get('date.timezone') == '')
{
  die('ERROR: date.timezone not set in php.ini. Please contact your hosting company to set the timezone in the server PHP configuration before continuing.');
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
$request_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == '1') || (isset($_SERVER['HTTP_X_FORWARDED_BY']) && strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']), 'SSL')) || (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL')) || (isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) ? 'SSL' : 'NONSSL';

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
$current_page = $_GET['main_page'];
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
require(DIR_FS_INSTALL . 'includes/languages/' . $languagesInstalled[$lng][fileName] . '.php');
