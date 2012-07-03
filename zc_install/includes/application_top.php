<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: application_top.php 19969 2011-11-08 17:03:26Z drbyte $
 */
/**
 * ensure odd settings are disabled and set required defaults
 */
//@ini_set("session.auto_start","0");
//@ini_set("session.use_trans_sid","0");

@ini_set("arg_separator.output","&");

// Check PHP version
if (version_compare(PHP_VERSION, '5.2.14', '<'))
{
  require('includes/templates/template_default/templates/tpl_php_version_problem.php');
  die('');
}


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
 * Set the installer configuration parameters
 */
include('includes/installer_params.php');
/**
 * set the level of error reporting
 */
error_reporting(version_compare(PHP_VERSION, 5.3, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE : version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT : E_ALL & ~E_NOTICE);
$debug_logfile_path = DEBUG_LOG_FOLDER . '/zcInstallDEBUG-' . time() . '-' . mt_rand(1000,999999) . '.log';
@ini_set('log_errors', 1);
@ini_set('log_errors_max_len', 0);
@ini_set('error_log', $debug_logfile_path);
if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
  @ini_set('display_errors', 1);
} else {
  @ini_set('display_errors', 0);
}
/**
 * Timezone problem detection
 */
if (PHP_VERSION >= '5.3' && ini_get('date.timezone') == '')
{
  die('ERROR: date.timezone not set in php.ini. Please contact your hosting company to set the timezone in the server PHP configuration before continuing.');
} elseif (PHP_VERSION >= '5.1') {
  $baseTZ = date_default_timezone_get();
  date_default_timezone_set($baseTZ);
  unset($baseTZ);
}

/*
 * check settings for, and then turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
 */
if (version_compare(PHP_VERSION, 5.4, '<')) {
  $php_magic_quotes_runtime = (@get_magic_quotes_runtime() > 0) ? 'ON' : 'OFF';
  if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);
  $val = @ini_get('magic_quotes_sybase');
  if (is_string($val) && strtolower($val) == 'on') $val = 1;
  $php_magic_quotes_sybase = ((int)$val > 0) ? 'ON' :'OFF';
  if ((int)$val != 0) @ini_set('magic_quotes_sybase', 0);
  unset($val);
}
/**
 * boolean used to see if we are in the admin script, obviously set to false here.
 */
if (!defined('IS_ADMIN_FLAG')) define('IS_ADMIN_FLAG', false);

// define the project version
require('version.php');

// set php_self in the local scope
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
require('../includes/classes/class.base.php');
require('../includes/classes/class.notifier.php');
require('includes/functions/general.php');

/**
 * set the type of request (secure or not)
 */
$request_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == '1') || (isset($_SERVER['HTTP_X_FORWARDED_BY']) && strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']),'SSL')) || (isset($_SERVER['HTTP_X_FORWARDED_HOST']) &&  strstr(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']),'SSL')) || (isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' )   )  ? 'SSL' : 'NONSSL';
/**
 * require the session handling functions
 */
if (!defined('STORE_SESSIONS')) define('STORE_SESSIONS', 'file');
require('includes/functions/sessions.php');
/**
 * set the session name and save path
 */
zen_session_name('zenInstallId');
zen_session_save_path(SESSION_WRITE_DIRECTORY);
/**
 * set the session cookie parameters
 */
session_set_cookie_params(0, '/');
/**
 * set the session ID if it exists
 */
if (isset($_POST[zen_session_name()])) {
  zen_session_id($_POST[zen_session_name()]);
} elseif ( ($request_type == 'SSL') && isset($_GET[zen_session_name()]) ) {
  zen_session_id($_GET[zen_session_name()]);
}
zen_session_start();
$session_started = true;

/*
 * initialize the message stack for message alerts
 */
require('includes/classes/message_stack.php');
$messageStack = new messageStack;
/*
 * activate installer
 */
require('includes/classes/installer.php');
$zc_install = new installer;

$zc_install->error = false;
$zc_install->fatal_error = false;
$zc_install->error_list = array();

if ((!isset($_GET['main_page']) || $_GET['main_page'] == 'index') || (isset($_GET['reset']) && $_GET['reset'] == 1)) $zc_install->resetConfigKeys();

/*
 * check validity of session data
 */
if (isset($_GET['main_page']) && !in_array($_GET['main_page'], array('', 'index', 'license', 'inspect', 'time_out', 'store_setup', 'admin_setup', 'finished')) ) {
  if (!isset($_SESSION['installerConfigKeys']) || sizeof($_SESSION['installerConfigKeys']) < 1 || !isset($_SESSION['installerConfigKeys']['DIR_FS_SQL_CACHE'])) {
    header('location: index.php?main_page=time_out' . zcInstallAddSID() );
  }
}

/*
 * language determination
 */
$language = (isset($_GET['language']) && $_GET['language'] != '') ? preg_replace('/[^a-zA-Z_]/', '', $_GET['language']) : $zc_install->getConfigKey('language');
if ($language == '') $language = 'english';
if (!file_exists('includes/languages/' . $language . '.php')) {
  $zc_install->throwException('Specified language file not found. Defaulting to english. (' . 'includes/languages/' . $language . '.php)');
  $language = 'english';
}
$zc_install->setConfigKey('language', $language);
/*
 * template determination
 */
define('DIR_WS_INSTALL_TEMPLATE', 'includes/templates/template_default/');

define('ZC_UPG_DEBUG',  (!isset($_GET['debug'])  && !isset($_POST['debug'])  || (isset($_POST['debug'])  && $_POST['debug'] == '')) ? false : true);
define('ZC_UPG_DEBUG2', (!isset($_GET['debug2']) && !isset($_POST['debug2']) || (isset($_POST['debug2']) && $_POST['debug2'] == '')) ? false : true);
define('ZC_UPG_DEBUG3', (!isset($_GET['debug3']) && !isset($_POST['debug3']) || (isset($_POST['debug3']) && $_POST['debug3'] == '')) ? false : true);


