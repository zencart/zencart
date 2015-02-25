<?php
/**
 * File contains support for paginator unit tests
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */

/**
 * Testing Library
 */
abstract class zcPaginatorTestCase extends PHPUnit_Framework_TestCase
{
    public function run(PHPUnit_Framework_TestResult $result = NULL)
    {
        $this->setPreserveGlobalState(false);
        return parent::run($result);
    }

    public function setUp()
    {
        // Define some pre-requisites
        global $zco_notifier;
        define('IS_ADMIN_FLAG', 'TRUE');
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
        // Load required common files
        require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php');
        require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php');
        if(!defined('HTTP_SERVER'))
            define('HTTP_SERVER', 'http://zencart-git.local');
        if(!defined('DIR_WS_CATALOG'))
            define('DIR_WS_CATALOG', '/');
        if(!defined('DIR_WS_HTTPS_CATALOG'))
            define('DIR_WS_HTTPS_CATALOG', '/ssl/');

        // Configure required language defines
        if(!defined('CONNECTION_TYPE_UNKNOWN'))
            define('CONNECTION_TYPE_UNKNOWN', 'Unknown Connection \'%s\' Found: %s');

        require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'filenames.php');

        $zco_notifier = new notifier;
        if(IS_ADMIN_FLAG)
        {
            require_once(DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'general.php');
            require_once(DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'html_output.php');
        }
        else
        {
            require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general.php');
        }

        require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcPassword.php');
        require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'password_funcs.php');


    }
}
