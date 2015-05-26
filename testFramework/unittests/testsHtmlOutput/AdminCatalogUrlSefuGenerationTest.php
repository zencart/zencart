<?php
/**
 * File contains URL generation test cases for the catalog side of Zen Cart
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @package tests
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

/**
 * Testing Library
 */
class testAdminCatalogUrlSefuGeneration extends zcTestCase
{

    public function setUp()
    {
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', true);
        }
        parent::setUp();
        require DIR_FS_ADMIN . 'includes/functions/general.php';
        require DIR_FS_ADMIN . 'includes/functions/html_output.php';

        if (!defined('ENABLE_SSL')) {
            define('ENABLE_SSL', 'true');
        }
        if (!defined('SEARCH_ENGINE_FRIENDLY_URLS')) {
            define('SEARCH_ENGINE_FRIENDLY_URLS', 'true');
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
        $this->assertEquals(6, $reflect->getNumberOfParameters());
        $params = array(
            'page',
            'parameters',
            'connection',
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
        $this->assertURLGenerated(
            zen_catalog_href_link(),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'test=test'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/test/test'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testStaticUrlGeneration()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link('ipn_main_handler.php', '', 'NONSSL', true, true, true),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'ipn_main_handler.php'
        );
        $this->assertURLGenerated(
            zen_catalog_href_link('ipn_main_handler.php', 'type=test', 'NONSSL', true, true, true),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'ipn_main_handler.php/type/test'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testValidCategoryUrlsFilters()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1&sort=20a&alpha_filter_id=65'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/cPath/1/sort/20a/alpha_filter_id/65'
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1_8&sort=20a&alpha_filter_id=65'),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/cPath/1_8/sort/20a/alpha_filter_id/65'
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1', 'sort' => '20a', 'alpha_filter_id' => '65')),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/cPath/1/sort/20a/alpha_filter_id/65'
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1_8', 'sort' => '20a', 'alpha_filter_id' => '65')),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/cPath/1_8/sort/20a/alpha_filter_id/65'
        );
    }

    /**
     * @depends testHomePage
     */
    public function testDefinePageUrls()
    {
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFINE_PAGE_2),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFINE_PAGE_2
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFINE_PAGE_3),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFINE_PAGE_3
        );
        $this->assertURLGenerated(
            zen_catalog_href_link(FILENAME_DEFINE_PAGE_4),
            HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFINE_PAGE_4
        );
    }
}
