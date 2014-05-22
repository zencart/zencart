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
class testAdminUrlGeneration extends zcUrlGenerationTestCase
{
  public function setUp()
  {
  	if(!defined('IS_ADMIN_FLAG'))
  	  define('IS_ADMIN_FLAG', true);

    if(!defined('ENABLE_SSL'))
      define('ENABLE_SSL', 'true');
    if(!defined('SESSION_USE_FQDN'))
      define('SESSION_USE_FQDN', 'True');

    parent::setUp();
  }

  public function testUrlFunctionsExist()
  {
    $this->assertTrue(function_exists('zen_href_link'), 'zen_href_link() did not exist');
    $reflect = new ReflectionFunction('zen_href_link');
    $this->assertEquals(4, $reflect->getNumberOfParameters());
    $params = array('page', 'parameters', 'connection', 'add_session_id');
    foreach($reflect->getParameters() as $param) {
      $this->assertTrue(in_array($param->getName(), $params));
    }

    $this->assertTrue(function_exists('zen_admin_href_link'), 'zen_href_link() did not exist');
    $reflect = new ReflectionFunction('zen_admin_href_link');
    $this->assertEquals(3, $reflect->getNumberOfParameters());
    $params = array('page', 'parameters', 'add_session_id');
    foreach($reflect->getParameters() as $param) {
    	$this->assertTrue(in_array($param->getName(), $params));
    }
  }

  /**
   * @depends testUrlFunctionsExist
   */
  public function testAdminPage()
  {
  	$this->assertURLGenerated(
  	  zen_href_link(),
  	  HTTP_SERVER . DIR_WS_ADMIN
  	);
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_DEFAULT),
  	  HTTP_SERVER . DIR_WS_ADMIN
  	);
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_DEFAULT, 'test=test'),
  	  HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  }

  /**
   * @depends testAdminPage
   */
  public function testAdminPageSsl()
  {
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_DEFAULT, null, 'SSL'),
  	  HTTP_SERVER . DIR_WS_ADMIN
  	);
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_DEFAULT, 'test=test', 'SSL'),
  	  HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  }

  /**
   * @depends testAdminPage
   */
  public function testUnknownSchemaPage()
  {
  	// Write error logs to DIR_FS_LOGS
  	@ini_set('log_errors', 1);          // store to file
  	@ini_set('log_errors_max_len', 0);  // unlimited length of message output
  	@ini_set('display_errors', 0);      // do not output errors to screen/browser/client
  	@ini_set('error_log', TESTCWD . 'log-myDEBUG.txt');

  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_DEFAULT, null, 'OTHER'),
  	  HTTP_SERVER . DIR_WS_ADMIN
  	);

  	if(file_exists(TESTCWD . 'log-myDEBUG.txt')) {
      unlink(TESTCWD . 'log-myDEBUG.txt');
  	}
  	else {
      $this->assert('Failed to log to error_log');
  	}
  	@ini_set('error_log', '');

  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_DEFAULT, null, 'NONSSL', false),
  	  HTTP_SERVER . DIR_WS_ADMIN
  	);
  }

  /**
   * @depends testAdminPage
   */
  public function testAddSessionWhenSidDefined() {
  	$GLOBALS['session_started'] = true;
  	define('SID', 'zenadminid=1234567890');
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_DEFAULT),
  	  HTTP_SERVER . DIR_WS_ADMIN . '?zenadminid=1234567890'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_DEFAULT, 'test=test'),
  	  HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zenadminid=1234567890'
  	);
  }

  /**
   * @depends testAdminPage
   */
  public function testAutoCorrectLeadingQuerySeparator()
  {
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, '&test=test'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, '&&test=test'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, '?test=test'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, '??test=test'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, '?&test=test'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, '&?test=test'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  }

  /**
   * @depends testAdminPage
   */
  public function testAutoCorrectTrailingQuerySeparator()
  {
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test?'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test??'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test?&'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&?'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test'
  	);
  }

  /**
   * @depends testAdminPage
   */
  public function testAutoCorrectMultipleAmpersandsInQuery()
  {
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);

  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, '&&test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, '&&&test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, '&&&&test=test&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);

  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&zen-cart=the-art-of-e-commerce&&&&'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  }

  /**
   * @depends testAdminPage
   */
  public function testAutoCorrectAmpersandEntitiesInQuery()
  {
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&amp;&amp;&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  }

  /**
   * @depends testAdminPage
   */
  public function testAutoCorrectMixedAmpersandAndAmbersandEntitiesInQuery()
  {
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&&&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&&&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&amp;&&amp;zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  	$this->assertURLGenerated(
      zen_href_link(FILENAME_DEFAULT, 'test=test&&amp;&zen-cart=the-art-of-e-commerce'),
      HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_DEFAULT . '&amp;test=test&amp;zen-cart=the-art-of-e-commerce'
  	);
  }

  /**
   * @depends testAdminPage
   */
  public function testConfigurationURLs()
  {
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_CONFIGURATION),
  	  HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION
  	);
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_CONFIGURATION, 'gID=1'),
  	  HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_CONFIGURATION, 'gID=1&cID=1'),
  	  HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_CONFIGURATION, 'gID=1&cID=1&action=edit'),
  	  HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=edit'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_CONFIGURATION, array('gID' => '1', 'cID' => '1', 'action' => 'edit')),
  	  HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=edit'
  	);
  	$this->assertURLGenerated(
  	  zen_href_link(FILENAME_CONFIGURATION, array('gID' => '1', 'cID' => '1', 'action' => 'save')),
  	  HTTP_SERVER . DIR_WS_ADMIN . 'index.php?cmd=' . FILENAME_CONFIGURATION . '&amp;gID=1&amp;cID=1&amp;action=save'
  	);
  }
}
