<?php
/**
 * File contains URL generation test cases for the catalog side of Zen Cart
 *
 * @package tests
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * Testing Library
 */
class testCatalogUrlGeneration extends PHPUnit_Framework_TestCase
{

  public function setUp()
  {
    // Configure URL environment
    if(!defined('HTTP_SERVER'))
      define('HTTP_SERVER', 'http://zencart-git.local');
    if(!defined('HTTPS_SERVER'))
      define('HTTPS_SERVER', 'https://zencart-git.local');
    if(!defined('ENABLE_SSL'))
      define('ENABLE_SSL', 'true');
    if(!defined('SEARCH_ENGINE_FRIENDLY_URLS'))
      define('SEARCH_ENGINE_FRIENDLY_URLS', 'false');
    if(!defined('SESSION_FORCE_COOKIE_USE'))
      define('SESSION_FORCE_COOKIE_USE', 'False');
    if(!defined('SESSION_USE_FQDN'))
      define('SESSION_USE_FQDN', 'True');

    // Configure required language defines
    if(!defined('CONNECTION_TYPE_UNKNOWN'))
      define('CONNECTION_TYPE_UNKNOWN', 'Unknown Connection \'%s\' Found: %s');

    // Load some required globals
    if(!array_key_exists('zco_notifier', $GLOBALS))
      $GLOBALS['zco_notifier'] = new notifier();
    if(!array_key_exists('request_type', $GLOBALS))
      $GLOBALS['request_type'] = 'SSL';
    if(!array_key_exists('session_started', $GLOBALS))
      $GLOBALS['session_started'] = false;
    if(!array_key_exists('http_domain', $GLOBALS))
      $GLOBALS['http_domain'] = zen_get_top_level_domain(HTTP_SERVER);
    if(!array_key_exists('https_domain', $GLOBALS))
      $GLOBALS['https_domain'] = zen_get_top_level_domain(HTTPS_SERVER);

    // Need these two functions (no namespace so using eval to create)
    if(!function_exists('zen_session_name'))
      eval('function zen_session_name($name = \'\') { return \'zenid\'; }');
    if(!function_exists('zen_session_id'))
      eval('function zen_session_id($sessid = \'\') { return \'1234567890\'; }');

    // Load required files
    require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'html_output.php');
  }

  protected function assertURLGenerated($url, $expected)
  {
    return $this->assertTrue(
    $url == $expected,
      'An incorrect URL was generated:' . PHP_EOL . $url . PHP_EOL .
      'The expected URL was:'  . PHP_EOL . $expected . PHP_EOL
    );
  }

  public function testUrlFunctionExists()
  {
    $this->assertTrue(function_exists('zen_href_link'), 'zen_href_link() did not exist');
  }

  /**
   * @depends testUrlFunctionExists
   */
  public function testHomePage()
  {
    $this->assertURLGenerated(
      zen_href_link('index'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testSslPage()
  {
    $this->assertURLGenerated(
      zen_href_link('index', null, 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=index'
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
      zen_href_link('index', null, 'OTHER'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index'
    );

    if(file_exists(TESTCWD . 'log-myDEBUG.txt')) {
      unlink(TESTCWD . 'log-myDEBUG.txt');
    }
    else {
     $this->assert('Failed to log to error_log');
    }
    @ini_set('error_log', '');
  }

  /**
   * @depends testHomePage
   */
   public function testAddSessionWhenSwitchingProtocolAndServers() {
   	$GLOBALS['session_started'] = true;
   	$GLOBALS['https_domain'] = 'dummy.local';
  	$this->assertURLGenerated(
  	  zen_href_link('index'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;zenid=1234567890'
  	);
  }

  /**
   * @depends testAddSessionWhenSwitchingProtocolAndServers
   */
  public function testAddSessionWhenSidDefined() {
  	$GLOBALS['session_started'] = true;
  	define('SID', 'zenid=1234567890');
  	$this->assertURLGenerated(
  	  zen_href_link('index'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;zenid=1234567890'
  	);
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectLeadingQuerySeparator()
  {
    $this->assertURLGenerated(
      zen_href_link('index', '&test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', '&&test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', '?test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', '??test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', '?&test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', '&?test=test'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectTrailingQuerySeparator()
  {
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test&&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test?'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test??'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test?&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test&?'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectMultipleAmpersandsInQuery()
  {
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test&&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test&&&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );

    $this->assertURLGenerated(
      zen_href_link('index', '&&test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_href_link('index', '&&&test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_href_link('index', '&&&&test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );

    $this->assertURLGenerated(
      zen_href_link('index', 'test=test&&zen-cart=the-art-of-e-commerce&&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test&&zen-cart=the-art-of-e-commerce&&&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'test=test&&zen-cart=the-art-of-e-commerce&&&&'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectAmpersandEntitiesInQuery()
  {
    $this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&amp;zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&amp;&amp;zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
    $this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&amp;&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
    );
  }

  /**
   * @depends testHomePage
   */
  public function testAutoCorrectMixedAmpersandAndAmbersandEntitiesInQuery()
  {
  	$this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&amp;&zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&amp;&&zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&amp;&&&zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&&amp;zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&&&amp;zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&&&&amp;zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&amp;&&amp;zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
    $this->assertURLGenerated(
  	  zen_href_link('index', 'test=test&&amp;&zen-cart=the-art-of-e-commerce'),
  	  HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  }

  /**
   * @depends testHomePage
   */
  public function testValidCategoryUrls()
  {
  	$this->assertURLGenerated(
  			zen_href_link('index', 'cPath=1'),
  			HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;cPath=1'
  	);
  	$this->assertURLGenerated(
  			zen_href_link('index', 'cPath=1_8'),
  			HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;cPath=1_8'
  	);
  	$this->assertURLGenerated(
  			zen_href_link('index', array('cPath' => '1')),
  			HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;cPath=1'
  	);
  	$this->assertURLGenerated(
  			zen_href_link('index', array('cPath' => '1_8')),
  			HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;cPath=1_8'
  	);
  }

  /**
   * @depends testValidCategoryUrls
   */
  public function testValidCategoryUrlsFilters()
  {
    $this->assertURLGenerated(
      zen_href_link('index', 'cPath=1&sort=20a&alpha_filter_id=65'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;cPath=1&amp;sort=20a&amp;alpha_filter_id=65'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'cPath=1_8&sort=20a&alpha_filter_id=65'),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;cPath=1_8&amp;sort=20a&amp;alpha_filter_id=65'
    );
    $this->assertURLGenerated(
      zen_href_link('index', array('cPath' => '1', 'sort' => '20a', 'alpha_filter_id' => '65')),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;cPath=1&amp;sort=20a&amp;alpha_filter_id=65'
    );
    $this->assertURLGenerated(
      zen_href_link('index', array('cPath' => '1_8', 'sort' => '20a', 'alpha_filter_id' => '65')),
      HTTP_SERVER . DIR_WS_CATALOG . 'index.php?main_page=index&amp;cPath=1_8&amp;sort=20a&amp;alpha_filter_id=65'
    );
  }

  /**
   * @depends testSslPage
   */
  public function testValidCategoryUrlsSsl()
  {
    $this->assertURLGenerated(
      zen_href_link('index', 'cPath=1', 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=index&amp;cPath=1'
    );
    $this->assertURLGenerated(
      zen_href_link('index', 'cPath=1_8', 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=index&amp;cPath=1_8'
    );
    $this->assertURLGenerated(
      zen_href_link('index', array('cPath' => '1'), 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=index&amp;cPath=1'
    );
    $this->assertURLGenerated(
      zen_href_link('index', array('cPath' => '1_8'), 'SSL'),
      HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . 'index.php?main_page=index&amp;cPath=1_8'
    );
  }

}
