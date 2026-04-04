<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcUnitTestCase;
use Tests\Support\zcURLTestObserver;

class AdminCatalogUrlGenerationTest extends zcUnitTestCase
{

    public function setUp(): void
    {
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', true);
        }
        parent::setUp();
        require DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'general.php';
        require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_strings.php';
        require DIR_FS_ADMIN . 'includes/functions/html_output.php';
        require_once(TESTCWD . 'Support/zcURLTestObserver.php');
        $GLOBALS['zcURLTestObserver'] = new zcURLTestObserver();

        if (!defined('ENABLE_SSL')) {
            define('ENABLE_SSL', 'true');
        }
        if (!defined('ENABLE_SSL_CATALOG')) {
            define('ENABLE_SSL_CATALOG', 'true');
        }
        if (!defined('SEARCH_ENGINE_FRIENDLY_URLS')) {
            define('SEARCH_ENGINE_FRIENDLY_URLS', 'false');
        }
        if (!defined('SESSION_FORCE_COOKIE_USE')) {
            define('SESSION_FORCE_COOKIE_USE', 'False');
        }
        if (!defined('SESSION_USE_FQDN')) {
            define('SESSION_USE_FQDN', 'True');
        }

    }

    public function testUrlFunctionsExist()
    {
        $this->assertTrue(function_exists('zen_catalog_href_link'), 'zen_catalog_href_link() did not exist');
        $reflect = new ReflectionFunction('zen_catalog_href_link');
        $this->assertEquals(3, $reflect->getNumberOfParameters());
        $params = array(
            'page',
            'parameters',
            'connection',
        );
        foreach ($reflect->getParameters() as $param) {
            $this->assertTrue(in_array($param->getName(), $params));
        }
    }

    /**
     * @depends testUrlFunctionsExist
     */
    public function testHomePage()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'test=test'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testHomePageSsl()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, null, 'SSL'),
            HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'test=test', 'SSL'),
            HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testNoAddSessionWhenSwitchingProtocolAndServers()
    {
        $GLOBALS['session_started'] = true;
        $GLOBALS['https_domain'] = 'dummy.local';
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'test=test'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testNoAddSessionWhenSwitchingProtocolAndServers
     */
    public function testNoAddSessionWhenSidDefined()
    {
        if (PHP_VERSION_ID >= 80401) {
            $this->markTestSkipped('IgnoredAfterPHP841');
        }
        $GLOBALS['session_started'] = true;
        define('SID', 'zenadminid=1234567890');
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'test=test'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testValidCategoryUrls()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1'
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1_8'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8'
        );
    }

    /**
     * @depends testValidCategoryUrls
     */
    public function testValidCategoryUrlsFilters()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1&sort=20a&alpha_filter_id=65'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1&amp;sort=20a&amp;alpha_filter_id=65'
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1_8&sort=20a&alpha_filter_id=65'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8&amp;sort=20a&amp;alpha_filter_id=65'
        );
    }

    /**
     * @depends testHomePageSsl
     */
    public function testValidCategoryUrlsSsl()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1', 'SSL'),
            HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1'
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1_8', 'SSL'),
            HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testValidEzPageUrls()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_EZPAGES, 'id=1'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_EZPAGES . '&amp;id=1'
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_EZPAGES, 'id=1&chapter=10'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_EZPAGES . '&amp;id=1&amp;chapter=10'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testDefinePageUrls()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFINE_PAGE_2),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFINE_PAGE_2
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFINE_PAGE_3),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFINE_PAGE_3
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFINE_PAGE_4),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFINE_PAGE_4
        );
    }

    /**
     * @depends testHomePageSsl
     */
    public function testObserverCannotDowngradeFromSsl()
    {
        $GLOBALS['zcURLTestObserver']->mode = zcURLTestObserver::$CHANGE_CONNECTION;

        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, '', 'SSL'),
            HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT
        );

        $GLOBALS['zcURLTestObserver']->mode = zcURLTestObserver::$CHANGE_NOTHING;
    }
}
