<?php
/**
 * Load testing framework (only needed on OLDER versions of PHPUnit.
 * With v3.5 and newer, the following 2 lines can be deleted:
 */
$bypassWarning = TRUE; // bypass PHPUnit/Framework warning error (works on edited localhost code ... will have to customize Bamboo to do the same if the next line can't be removed
if (file_exists('PHPUnit/Framework.php') && ! file_exists('PHPUnit/Autoload.php'))
  require_once 'PHPUnit/Framework.php';
/**
 * Set up some prerequisites
 */
define('TESTCWD', realpath(dirname(__FILE__)) . '/');
define('DIR_FS_CATALOG', realpath(dirname(__FILE__) . '/../../'));
define('DIR_FS_INCLUDES', realpath(dirname(__FILE__) . '/../../') . '/includes/');
define('CWD', DIR_FS_INCLUDES . '../');
define('DIR_WS_CLASSES', '/includes/classes/');
define('DIR_WS_FUNCTIONS', '/includes/functions/');
if (strpos(@ini_get('include_path'), '.') === false) {
  @ini_set('include_path', '.' . PATH_SEPARATOR . @ini_get('include_path'));
}
if (file_exists(TESTCWD . 'localTestSetup.php'))
  require_once TESTCWD . 'localTestSetup.php';

define('IS_ADMIN_FLAG', FALSE);
require_once (DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php');
require (DIR_FS_CATALOG . '/includes/classes/class.notifier.php');
require_once (DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general.php');
require_once (DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcPassword.php');
require_once (DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'password_funcs.php');
