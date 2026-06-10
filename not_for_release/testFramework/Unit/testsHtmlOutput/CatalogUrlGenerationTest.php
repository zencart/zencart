<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

use PHPUnit\Framework\Attributes\Depends;
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

        require_once TESTCWD . 'Support/zcURLTestObserver.php';
        $GLOBALS['zcURLTestObserver'] = new zcURLTestObserver();
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_general_shared.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_urls.php';
        require_once DIR_FS_CATALOG . 'includes/functions/functions_strings.php';
        require_once DIR_FS_CATALOG . 'includes/functions/html_output.php';
        $this->initializeConfigRepositories();
        $GLOBALS['session_started'] = false;
        $GLOBALS['http_domain'] = zen_get_top_level_domain(HTTP_SERVER);

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

    private function initializeConfigRepositories(): void
    {
        if (!array_key_exists('configurationRepository', $GLOBALS)) {
            $GLOBALS['configurationRepository'] = new class {
                public function get(string $key): mixed
                {
                    return defined($key) ? constant($key) : null;
                }
            };
        }

        if (!array_key_exists('productTypeLayoutRepository', $GLOBALS)) {
            $GLOBALS['productTypeLayoutRepository'] = new class {
                public function get(string $key): mixed
                {
                    return defined($key) ? constant($key) : null;
                }
            };
        }
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

    #[Depends('testUrlFunctionsExist')]
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
    }

    #[Depends('testHomePage')]
    public function testExplicitSslConnectionUsesCurrentServer()
    {
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, '', 'SSL'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT
        );
        $this->assertURLGenerated(
            zen_href_link(FILENAME_DEFAULT, 'test=test', 'SSL'),
            HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
        );
    }

    #[Depends('testExplicitSslConnectionUsesCurrentServer')]
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

    #[Depends('testHomePage')]
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

    #[Depends('testHomePage')]
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

    #[Depends('testExplicitSslConnectionUsesCurrentServer')]
    public function testStaticUrlGeneration()
    {
        $this->assertURLGenerated(
            zen_href_link('ajax.php', '', 'SSL', true, true, true),
            HTTP_SERVER . DIR_WS_CATALOG . 'ajax.php'
        );
        $this->assertURLGenerated(
            zen_href_link('ajax.php', 'type=test', 'SSL', true, true, true),
            HTTP_SERVER . DIR_WS_CATALOG . 'ajax.php?type=test'
        );
    }

    #[Depends('testHomePage')]
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

    #[Depends('testValidCategoryUrls')]
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

    #[Depends('testHomePage')]
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

    #[Depends('testHomePage')]
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
}
