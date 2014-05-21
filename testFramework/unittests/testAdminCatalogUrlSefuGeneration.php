<?php
/**
 * File contains URL generation test cases for the catalog side of Zen Cart
 *
 * @package tests
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once('support/zcUrlGenerationTestCase.php');
/**
 * Testing Library
 */
class testAdminCatalogUrlSefuGeneration extends zcUrlGenerationTestCase
{

  public function setUp()
  {
    if(!defined('IS_ADMIN_FLAG'))
      define('IS_ADMIN_FLAG', true);

    if(!defined('ENABLE_SSL'))
      define('ENABLE_SSL', 'true');
    if(!defined('SEARCH_ENGINE_FRIENDLY_URLS'))
      define('SEARCH_ENGINE_FRIENDLY_URLS', 'true');
    if(!defined('SESSION_FORCE_COOKIE_USE'))
      define('SESSION_FORCE_COOKIE_USE', 'False');
    if(!defined('SESSION_USE_FQDN'))
      define('SESSION_USE_FQDN', 'True');

    parent::setUp();
  }

  public function testUrlFunctionsExist()
  {
    $this->assertTrue(function_exists('zen_catalog_href_link'), 'zen_catalog_href_link() did not exist');
  }

  /**
   * @depends testUrlFunctionsExist
   */
  public function testHomePage()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(),
      HTTP_SERVER . DIR_WS_CATALOG
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT),
      HTTP_SERVER . DIR_WS_CATALOG
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/test/test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testStaticUrlGeneration()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link('ipn_main_handler.php', '', 'NONSSL', true, true, true),
      HTTP_SERVER . DIR_WS_CATALOG . 'ipn_main_handler.php'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link('ipn_main_handler.php', 'type=test', 'NONSSL', true, true, true),
      HTTP_SERVER . DIR_WS_CATALOG . 'ipn_main_handler.php/type/test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testValidCategoryUrlsFilters()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1&sort=20a&alpha_filter_id=65'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/cPath/1/sort/20a/alpha_filter_id/65'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1_8&sort=20a&alpha_filter_id=65'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/cPath/1_8/sort/20a/alpha_filter_id/65'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1', 'sort' => '20a', 'alpha_filter_id' => '65')),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/cPath/1/sort/20a/alpha_filter_id/65'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1_8', 'sort' => '20a', 'alpha_filter_id' => '65')),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFAULT . '/cPath/1_8/sort/20a/alpha_filter_id/65'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testDefinePageUrls()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFINE_PAGE_2),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFINE_PAGE_2
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFINE_PAGE_3),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFINE_PAGE_3
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFINE_PAGE_4),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php/main_page/' . FILENAME_DEFINE_PAGE_4
    );
  }
}