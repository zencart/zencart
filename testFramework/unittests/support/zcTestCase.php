<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */

/**
 * Class zcTestCase
 */
abstract class zcTestCase extends PHPUnit_Framework_TestCase
{
    // This allows us to run in full isolation mode including
    // classes, functions, and defined statements
    public function run(PHPUnit_Framework_TestResult $result = null)
    {
        $this->setPreserveGlobalState(false);

        return parent::run($result);
    }

    public function setUp()
    {
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', false);
        }

        if (!defined('TESTCWD')) {
            define('TESTCWD', realpath(__DIR__ . '/../') . '/');
        }
        if (!defined('DIR_FS_CATALOG')) {
            define('DIR_FS_CATALOG', realpath(__DIR__ . '/../../../') . '/');
        }
        if (!defined('DIR_FS_INCLUDES')) {
            define('DIR_FS_INCLUDES', DIR_FS_CATALOG . 'includes/');
        }
        if (!defined('CWD')) {
            define('CWD', DIR_FS_INCLUDES . '../');
        }

        if (strpos(@ini_get('include_path'), '.') === false) {
            @ini_set('include_path', '.' . PATH_SEPARATOR . @ini_get('include_path'));
        }

        date_default_timezone_set('UTC');

        if (file_exists(TESTCWD . 'localTestSetup.php')) {
            require_once TESTCWD . 'localTestSetup.php';
        }

        if (!defined('DIR_WS_CATALOG')) {
            define('DIR_WS_CATALOG', '/');
        }

        if (!defined('DIR_CATALOG_LIBRARY')) {
            define('DIR_CATALOG_LIBRARY', DIR_FS_INCLUDES . 'library/');
        }

        if (!defined('DIR_WS_ADMIN')) {
            define('DIR_WS_ADMIN', '/admin/');
        }
        if (!defined('DIR_FS_ADMIN')) {
            define('DIR_FS_ADMIN', DIR_FS_CATALOG . 'admin/');
        }
        if (!defined('DIR_WS_CATALOG')) {
            define('DIR_WS_CATALOG', '/');
        }
        if (!defined('DIR_WS_HTTPS_CATALOG')) {
            define('DIR_WS_HTTPS_CATALOG', '/ssl/');
        }

        require_once(DIR_FS_INCLUDES . 'defined_paths.php');
        require_once(DIR_FS_INCLUDES . 'database_tables.php');
        require_once(DIR_FS_INCLUDES . 'filenames.php');
        require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.base.php');
        require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.notifier.php');


        if (!array_key_exists('zco_notifier', $GLOBALS)) {
            $GLOBALS['zco_notifier'] = new notifier();
        }


        if (!defined('HTTP_SERVER')) {
            define('HTTP_SERVER', 'http://zencart-git.local');
        }
        if (!defined('HTTPS_SERVER')) {
            define('HTTPS_SERVER', 'https://zencart-git.local');
        }
        if (!defined('HTTP_CATALOG_SERVER')) {
            define('HTTP_CATALOG_SERVER', 'http://zencart-git.local');
        }
        if (!defined('HTTPS_CATALOG_SERVER')) {
            define('HTTPS_CATALOG_SERVER', 'https://zencart-git.local');
        }

        if (!defined('SESSION_FORCE_COOKIE_USE')) {
            define('SESSION_FORCE_COOKIE_USE', 'False');
        }
        if (!defined('SESSION_USE_FQDN')) {
            define('SESSION_USE_FQDN', 'True');
        }


        if (!defined('CONNECTION_TYPE_UNKNOWN')) {
            define('CONNECTION_TYPE_UNKNOWN', 'Unknown Connection \'%s\' Found: %s');
        }


        if (!function_exists('zen_session_name')) {
            eval('function zen_session_name($name = \'\') { return \'zenid\'; }');
        }
//        if(!function_exists('zen_session_name'))
//            eval('function zen_session_name($name = \'\') { return \'zenadminid\'; }');
        if (!function_exists('zen_session_id')) {
            eval('function zen_session_id($sessid = \'\') { return \'1234567890\'; }');
        }


        require_once DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
    }


    public function mockIterator(PHPUnit_Framework_MockObject_MockObject $iteratorMock, array $items)
    {
        $iteratorData = new \stdClass();
        $iteratorData->array = $items;
        $iteratorData->position = 0;

        $iteratorMock->expects($this->any())
            ->method('rewind')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position = 0;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('current')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->array[$iteratorData->position];
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('key')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('next')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position++;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('valid')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position]);
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('count')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return sizeof($iteratorData->array);
                    }
                )
            );

        return $iteratorMock;
    }

    protected function assertURLGenerated($url, $expected)
    {
        return $this->assertEquals($expected, $url, 'An incorrect URL was generated.');
    }

    public function initDiStuff()
    {
        $config1 = new AuraWeb();
        $config2 = new ZenCartCommon();
        $builder = new \Aura\Di\ContainerBuilder();
        $di = $builder->newConfiguredInstance(array($config1, $config2));
        return $di;
    }

}
