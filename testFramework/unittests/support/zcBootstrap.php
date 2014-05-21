<?php
/**
 * Unit testing common setup actions
 */
$bypassWarning = TRUE; // bypass PHPUnit/Framework warning error (works on edited localhost code ... will have to customize Bamboo to do the same if the next line can't be removed
if (file_exists('PHPUnit/Framework.php') && ! file_exists('PHPUnit/Autoload.php'))
  require_once 'PHPUnit/Framework.php';

/**
 * Set up some prerequisites
 */
define('TESTCWD', realpath(dirname(__FILE__)) . '/');
define('DIR_FS_CATALOG', realpath(dirname(__FILE__) . '/../../') . '/');
define('DIR_FS_INCLUDES', DIR_FS_CATALOG . 'includes/');
define('CWD', DIR_FS_INCLUDES . '../');

if (strpos(@ini_get('include_path'), '.') === false) {
  @ini_set('include_path', '.' . PATH_SEPARATOR . @ini_get('include_path'));
}

if (file_exists(TESTCWD . 'localTestSetup.php'))
  require_once TESTCWD . 'localTestSetup.php';

// Configure some additional paths if not already configured
if(!defined('DIR_FS_ADMIN')) define('DIR_FS_ADMIN', DIR_FS_CATALOG . 'admin/');
if(!defined('DIR_WS_CATALOG')) define('DIR_WS_CATALOG', '/');
if(!defined('DIR_WS_HTTPS_CATALOG')) define('DIR_WS_HTTPS_CATALOG', '/ssl/');

// Configure the rest of the paths if needed
require_once(DIR_FS_INCLUDES . 'defined_paths.php');

define('IS_ADMIN_FLAG', FALSE);
require_once (DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php');
require_once (DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php');
require_once (DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general.php');
require_once (DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcPassword.php');
require_once (DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'password_funcs.php');
