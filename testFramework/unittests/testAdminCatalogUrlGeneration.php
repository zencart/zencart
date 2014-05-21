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
class testAdminCatalogUrlGeneration extends zcUrlGenerationTestCase
{

  public function setUp()
  {
    if(!defined('IS_ADMIN_FLAG'))
      define('IS_ADMIN_FLAG', true);

    if(!defined('ENABLE_SSL'))
      define('ENABLE_SSL', 'true');
    if(!defined('SEARCH_ENGINE_FRIENDLY_URLS'))
      define('SEARCH_ENGINE_FRIENDLY_URLS', 'false');
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
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testHomePageSsl()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, null, 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test', 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testUnknownSchemaPage()
  {
    // Write error logs to DIR_FS_LOGS
    @ini_set('log_errors', 1);          // store to file
    @ini_set('log_errors_max_len', 0);  // unlimited length of message output
    @ini_set('display_errors', 0);      // do not output errors to screen/browser/client
    @ini_set('error_log', TESTCWD . 'log-myDEBUG.txt');

    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, null, 'OTHER'),
      HTTP_SERVER . DIR_WS_CATALOG
    );

    if(file_exists(TESTCWD . 'log-myDEBUG.txt')) {
      unlink(TESTCWD . 'log-myDEBUG.txt');
    }
    else {
     $this->assert('Failed to log to error_log');
    }
    @ini_set('error_log', '');

    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, null, 'NONSSL', false),
      HTTP_SERVER . DIR_WS_CATALOG
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
      HTTP_SERVER . DIR_WS_CATALOG
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
  }

  /**
   * @depends testNoAddSessionWhenSwitchingProtocolAndServers
   */
  public function testNoAddSessionWhenSidDefined() {
    $GLOBALS['session_started'] = true;
    define('SID', 'zenadminid=1234567890');
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT),
      HTTP_SERVER . DIR_WS_CATALOG
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectLeadingQuerySeparator()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, '&test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, '&&test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, '?test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, '??test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, '?&test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, '&?test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectTrailingQuerySeparator()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test?'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test??'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test?&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&?'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectMultipleAmpersandsInQuery()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );

    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, '&&test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, '&&&test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, '&&&&test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );

    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectAmpersandEntitiesInQuery()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectMixedAmpersandAndAmbersandEntitiesInQuery()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&amp;&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&amp;&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&amp;&&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&&&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&amp;&&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'test=test&&amp;&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
  }

  /**
   * @depends testHomePageSsl
   */
  public function testStaticUrlGeneration()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link('ipn_main_handler.php', '', 'SSL', true, true, true),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'ipn_main_handler.php'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link('ipn_main_handler.php', 'type=test', 'SSL', true, true, true),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'ipn_main_handler.php?type=test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testValidCategoryUrls()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1_8'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1')),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1_8')),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8'
    );
  }

  /**
   * @depends testValidCategoryUrls
   */
  public function testValidCategoryUrlsFilters()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1&sort=20a&alpha_filter_id=65'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1&amp;sort=20a&amp;alpha_filter_id=65'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1_8&sort=20a&alpha_filter_id=65'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8&amp;sort=20a&amp;alpha_filter_id=65'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1', 'sort' => '20a', 'alpha_filter_id' => '65')),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1&amp;sort=20a&amp;alpha_filter_id=65'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1_8', 'sort' => '20a', 'alpha_filter_id' => '65')),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8&amp;sort=20a&amp;alpha_filter_id=65'
    );
  }

  /**
   * @depends testHomePageSsl
   */
  public function testValidCategoryUrlsSsl()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1', 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, 'cPath=1_8', 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1'), 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFAULT, array('cPath' => '1_8'), 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=' . FILENAME_DEFAULT . '&amp;cPath=1_8'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testValidEzPageUrls()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_EZPAGES, 'id=1'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_EZPAGES . '&amp;id=1'
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_EZPAGES, 'id=1&chapter=10'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_EZPAGES . '&amp;id=1&amp;chapter=10'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testDefinePageUrls()
  {
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFINE_PAGE_2),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFINE_PAGE_2
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFINE_PAGE_3),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=' . FILENAME_DEFINE_PAGE_3
    );
    $this->assertURLGenerated(
      zen_catalog_href_link(FILENAME_DEFINE_PAGE_4),
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
      zen_catalog_href_link(FILENAME_DEFAULT, '', 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG
    );

    $GLOBALS['zcURLTestObserver']->mode = zcURLTestObserver::$CHANGE_NOTHING;
  }
}