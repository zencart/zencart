<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use Tests\Support\zcUnitTestCase;
use Tests\Support\zcURLTestObserver;

/**
 * Testing Library
 */
class CatalogUrlGenerationTest extends zcUnitTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        require_once(TESTCWD . 'Support/zcURLTestObserver.php');
        $GLOBALS['zcURLTestObserver'] = new zcURLTestObserver();
        require DIR_FS_CATALOG . 'includes/functions/functions_general.php';
        require DIR_FS_CATALOG . 'includes/functions/functions_urls.php';
        require DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require DIR_FS_CATALOG . 'includes/functions/html_output.php';
        if (!array_key_exists('https_domain', $GLOBALS)) {
            $GLOBALS['https_domain'] = zen_get_top_level_domain(HTTPS_SERVER);
        }
        if (!array_key_exists('request_type', $GLOBALS)) {
            $GLOBALS['request_type'] = 'SSL';
        }
        if (!array_key_exists('session_started', $GLOBALS)) {
            $GLOBALS['session_started'] = false;
        }
        if (!array_key_exists('http_domain', $GLOBALS)) {
            $GLOBALS['http_domain'] = zen_get_top_level_domain(HTTP_SERVER);
        }

        if (!defined('ENABLE_SSL')) {
            define('ENABLE_SSL', 'true');
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

        parent::setUp();
    }

    public function testUrlFunctionsExist()
    {
        $this->assertTrue(function_exists('zen_href_link'), 'zen_href_link() did not exist');
        $reflect = new ReflectionFunction('zen_href_link');
        $this->assertEquals(7, $reflect->getNumberOfParameters());
        $params = array(
            'page',
            'parameters',
            'connection',
            'add_session_id',
            'search_engine_safe',
            'static',
            'use_dir_ws_catalog'
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
//        $this->assertURLGenerated(
//            zen_href_link(FILENAME_DEFAULT),
//            HTTP_SERVER . DIR_WS_CATALOG
//        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
//        $this->expectErrorMessage('zen_href_link(, , NONSSL), unable to determine the page link.');
//        zen_href_link();
    }

    /**
     * @depends testHomePage
     */
    public function testHomePageSsl()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, null, 'SSL'),
            HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test', 'SSL'),
            HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testAddSessionWhenSwitchingProtocolAndServers()
    {
        $GLOBALS['session_started'] = true;
        $GLOBALS['https_domain'] = 'dummy.local';
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;zenid=1234567890'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zenid=1234567890'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, null, 'NONSSL', false),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT
        );
    }

    /**
     * @depends testAddSessionWhenSwitchingProtocolAndServers
     */
    public function testAddSessionWhenSidDefined()
    {
        if (PHP_VERSION_ID >= 80401) {
            $this->markTestSkipped('IgnoredAfterPHP841');
        }
        $GLOBALS['session_started'] = true;
        define('SID', 'zenid=1234567890');
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;zenid=1234567890'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zenid=1234567890'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testAutoCorrectLeadingQuerySeparator()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&test=test'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&test=test'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testAutoCorrectTrailingQuerySeparator()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test?'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test??'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test?&'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&?'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testAutoCorrectMultipleAmpersandsInQuery()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );

        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&test=test&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&&test=test&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '&&&&test=test&&zen-cart=the-art-of-e-commerce'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );

        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&&'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
        );
    }

    /**
     * @depends testHomePageSsl
     */
    public function testStaticUrlGeneration()
    {
        $this->assertURLGenerated(
            zen_href_link('ipn_main_handler.php', '', 'SSL', true, true, true),
            HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'ipn_main_handler.php'
        );
        $this->assertURLGenerated(
            zen_href_link('ipn_main_handler.php', 'type=test', 'SSL', true, true, true),
            HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'ipn_main_handler.php?type=test'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testValidCategoryUrls()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'cPath=1'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'cPath=1_8'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8'
        );
    }

    /**
     * @depends testValidCategoryUrls
     */
    public function testValidCategoryUrlsFilters()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'cPath=1&sort=20a&alpha_filter_id=65'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1&amp;sort=20a&amp;alpha_filter_id=65'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'cPath=1_8&sort=20a&alpha_filter_id=65'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8&amp;sort=20a&amp;alpha_filter_id=65'
        );
    }

    /**
     * @depends testHomePageSsl
     */
    public function testValidCategoryUrlsSsl()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'cPath=1', 'SSL'),
            HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'cPath=1_8', 'SSL'),
            HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testValidEzPageUrls()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_EZPAGES, 'id=1'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_EZPAGES . '&amp;id=1'
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_EZPAGES, 'id=1&chapter=10'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_EZPAGES . '&amp;id=1&amp;chapter=10'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testDefinePageUrls()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFINE_PAGE_2),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFINE_PAGE_2
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFINE_PAGE_3),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFINE_PAGE_3
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFINE_PAGE_4),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFINE_PAGE_4
        );
    }

    /**
     * @depends testHomePageSsl
     */
    public function testObserverCannotDowngradeFromSsl()
    {
        $GLOBALS['zcURLTestObserver']->mode = zcURLTestObserver::$CHANGE_CONNECTION;

        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '', 'SSL'),
            HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT
        );

        $GLOBALS['zcURLTestObserver']->mode = zcURLTestObserver::$CHANGE_NOTHING;
    }
}
