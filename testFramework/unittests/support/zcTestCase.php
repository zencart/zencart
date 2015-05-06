<?php
/**
 * File contains framework test cases
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * Testing Library
 */
abstract class zcTestCase extends PHPUnit_Framework_TestCase
{
  // This allows us to run in full isolation mode including
  // classes, functions, and defined statements
  public function run(PHPUnit_Framework_TestResult $result = NULL)
  {
    $this->setPreserveGlobalState(false);
    return parent::run($result);
  }

  public function setUp()
  {
    if(!defined('IS_ADMIN_FLAG')) define('IS_ADMIN_FLAG', false);

    // Define some pre-requisites
    if(!defined('TESTCWD')) define('TESTCWD', realpath(__DIR__ . '/../') . '/');
    if(!defined('DIR_FS_CATALOG')) define('DIR_FS_CATALOG', realpath(__DIR__ . '/../../../') . '/');
    if(!defined('DIR_FS_INCLUDES')) define('DIR_FS_INCLUDES', DIR_FS_CATALOG . 'includes/');
    if(!defined('CWD')) define('CWD', DIR_FS_INCLUDES . '../');
    if (!defined('DIR_CATALOG_LIBRARY')) define('DIR_CATALOG_LIBRARY', DIR_FS_INCLUDES . 'library/');

    if (strpos(@ini_get('include_path'), '.') === false)
    {
      @ini_set('include_path', '.' . PATH_SEPARATOR . @ini_get('include_path'));
    }

    if (file_exists(TESTCWD . 'localTestSetup.php'))
      require_once TESTCWD . 'localTestSetup.php';

    // Configure some additional paths if not already configured
    if(!defined('DIR_WS_ADMIN')) define('DIR_WS_ADMIN', '/admin/');
    if(!defined('DIR_FS_ADMIN')) define('DIR_FS_ADMIN', DIR_FS_CATALOG . 'admin/');
    if(!defined('DIR_WS_CATALOG')) define('DIR_WS_CATALOG', '/');
    if(!defined('DIR_WS_HTTPS_CATALOG')) define('DIR_WS_HTTPS_CATALOG', '/ssl/');

    // Configure the rest of the paths if needed
    require_once(DIR_FS_INCLUDES . 'defined_paths.php');
    require_once('zcCatalogTestCase.php');
    require_once('zcAdminTestCase.php');

    // Load required common files
    require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php');
    require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php');

    if(IS_ADMIN_FLAG)
    {
      require_once(DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'general.php');
    }
    else
    {
      require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general.php');
    }

    require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcPassword.php');
    require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'password_funcs.php');

    // Constants for attribute dependencies
    if (!defined('PRODUCTS_OPTIONS_TYPE_SELECT')) define('PRODUCTS_OPTIONS_TYPE_SELECT', '0');
    if (!defined('PRODUCTS_OPTIONS_TYPE_TEXT')) define('PRODUCTS_OPTIONS_TYPE_TEXT', '1');
    if (!defined('PRODUCTS_OPTIONS_TYPE_RADIO')) define('PRODUCTS_OPTIONS_TYPE_RADIO', '2');
    if (!defined('PRODUCTS_OPTIONS_TYPE_CHECKBOX')) define('PRODUCTS_OPTIONS_TYPE_CHECKBOX', '3');
    if (!defined('PRODUCTS_OPTIONS_TYPE_FILE')) define('PRODUCTS_OPTIONS_TYPE_FILE', '4');
    if (!defined('PRODUCTS_OPTIONS_TYPE_READONLY')) define('PRODUCTS_OPTIONS_TYPE_READONLY', '5');

  }
}
