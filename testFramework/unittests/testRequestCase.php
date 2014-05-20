<?php
/**
 * File contains zcRequest test cases
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * Testing Library
 */
class testRequestCase extends PHPUnit_Framework_TestCase
{
  public function setup()
  {
    require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.zcRequest.php');
    require_once('requestSupport.php');
  }
  public function testRequestInitEmpty()
  {
    global $systemContext;
    $systemContext = 'store';
    $_GET = array();
    $_POST = array();
    zcRequest::init();
    $this->assertTrue(count(zcRequest::all('all')) == 0);
    $this->assertTrue(count(zcRequest::all('get')) == 0);
    $this->assertTrue(count(zcRequest::all('post')) == 0);
  }
  public function testRequestInitSet()
  {
    global $systemContext;
    $systemContext = 'store';
    $_GET = array(
        'action' => 'test1',
        'blah' => 'x'
    );
    $_POST = array(
        'cPath' => 'test1',
        'action' => 'test2'
    );
    zcRequest::init();
    $this->assertTrue(count(zcRequest::all('all')) == 3);
    $this->assertTrue(count(zcRequest::all('get')) == 2);
    $this->assertTrue(count(zcRequest::all('post')) == 2);
    $systemContext = 'admin';
    $_GET = array(
        'action' => 'test1',
        'blah' => 'x'
    );
    $_POST = array(
        'cPath' => 'test1',
        'action' => 'test2'
    );
    zcRequest::init();
    $this->assertTrue(count(zcRequest::all('all')) == 3);
    $this->assertTrue(count(zcRequest::all('get')) == 2);
    $this->assertTrue(count(zcRequest::all('post')) == 2);
  }
  public function testRequestGet()
  {
    global $systemContext;
    $systemContext = 'store';
    $_GET = array(
        'action' => 'test1',
        'default' => '<>\'',
        'defaultArray' => array(
            '<>\'',
            '<>\''
        ),
        'keyword' => '<>\'',
        'products_id' => '<>\'&&valid*',
        'blah' => 'x'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::readGet('action') == 'test1');
    $this->assertTrue(zcRequest::readGet('blah') == 'x');
    $this->assertTrue(zcRequest::readGet('default') == '');
    $defaultArray = zcRequest::readGet('defaultArray');
    $this->assertTrue($defaultArray [0] == '');
    $this->assertTrue(zcRequest::readGet('keyword') == '\'');
    $this->assertTrue(zcRequest::readGet('products_id') == 'valid');
    $this->assertTrue(zcRequest::get('blah', null, 'get') == 'x');
    $this->assertTrue(zcRequest::readGet('products_id', 'invalid') == 'valid');
    $this->assertTrue(zcRequest::readGet('notexists', 'exists') == 'exists');
    try {
      zcRequest::readGet('notexists');
      $this->fail("Expected exception not thrown");
    } catch ( Exception $e ) {
      $this->assertEquals(0, $e->getCode());
      $this->assertEquals("Exception: Could not zcRequest::get paramName = notexists", $e->getMessage());
    }
    $this->assertTrue(zcRequest::get('notexists1', 'exists', 'get') == 'exists');
    $systemContext = 'admin';
    $_GET = array(
        'action' => 'test1',
        'products_id' => '<>\'&&valid*',
        'default' => '<>\'',
        'blah' => 'x'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::readGet('action') == 'test1');
    $this->assertTrue(zcRequest::readGet('blah') == 'x');
    $this->assertTrue(zcRequest::readGet('products_id') == 'valid');
    $this->assertTrue(zcRequest::readGet('default') == '<>\'');
    $this->assertTrue(zcRequest::get('blah', null, 'get') == 'x');
  }
  public function testRequestPost()
  {
    global $systemContext;
    $systemContext = 'store';
    $_POST = array(
        'cPath' => 'test1',
        'action' => 'test2'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::readPost('action') == 'test2');
    $this->assertTrue(zcRequest::readPost('cPath') == 'test1');
    $this->assertTrue(zcRequest::get('cPath', null, 'post') == 'test1');
    $this->assertTrue(zcRequest::readPost('notexists', 'exists') == 'exists');
    $this->assertTrue(zcRequest::get('notexists1', 'exists', 'post') == 'exists');
    $systemContext = 'admin';
    $_POST = array(
        'cPath' => 'test1',
        'action' => 'test2'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::readPost('action') == 'test2');
    $this->assertTrue(zcRequest::readPost('cPath') == 'test1');
    $this->assertTrue(zcRequest::get('cPath', null, 'post') == 'test1');
  }
  public function testRequestHas()
  {
    global $systemContext;
    $systemContext = 'store';
    $_POST = array(
        'cPath' => 'test1',
        'action' => 'test2'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::hasPost('action'));
    $this->assertFalse(zcRequest::hasPost('cPat'));
    $this->assertTrue(zcRequest::has('action', 'post'));
    $this->assertFalse(zcRequest::has('cPat', 'post'));
    $systemContext = 'admin';
    $_POST = array(
        'cPath' => 'test1',
        'action' => 'test2'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::hasPost('action'));
    $this->assertFalse(zcRequest::hasPost('cPat'));
    $this->assertTrue(zcRequest::has('action', 'post'));
    $this->assertFalse(zcRequest::has('cPat', 'post'));
    $systemContext = 'store';
    $_GET = array(
        'cPath' => 'test1',
        'action' => 'test2'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::hasGet('action'));
    $this->assertFalse(zcRequest::hasGet('cPat'));
    $this->assertTrue(zcRequest::has('action', 'get'));
    $this->assertFalse(zcRequest::has('cPat', 'get'));
    $systemContext = 'admin';
    $_GET = array(
        'cPath' => 'test1',
        'action' => 'test2'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::hasGet('action'));
    $this->assertFalse(zcRequest::hasGet('cPat'));
    $this->assertTrue(zcRequest::has('action', 'get'));
    $this->assertFalse(zcRequest::has('cPat', 'get'));
  }
  public function testRequestAll()
  {
    global $systemContext;
    unset($_GET);
    unset($_POST);
    $systemContext = 'store';
    $_GET = array(
        'action' => 'test1',
        'default' => '<>\'',
        'defaultArray' => array(
            '<>\'',
            '<>\''
        ),
        'keyword' => '<>\'',
        'products_id' => '<>\'&&valid*',
        'blah' => 'x'
    );
    $_POST = array(
        'cPath' => 'test1',
        'action1' => 'test2'
    );
    zcRequest::init();
    $result = zcRequest::all('all');
    $this->assertTrue($result ['action'] == 'test1');
    $this->assertTrue($result ['action1'] == 'test2');
    $this->assertTrue(count($result) == 8);
    $result = zcRequest::all('get');
    $this->assertTrue($result ['action'] == 'test1');
    $this->assertTrue(! isset($result ['action1']));
    $this->assertTrue(count($result) == 6);
    $result = zcRequest::all('post');
    $this->assertTrue(count($result) == 2);
    $this->assertTrue($result ['action1'] == 'test2');
    $this->assertTrue(! isset($result ['action']));
  }
  public function testRequestSet()
  {
    global $systemContext;
    $systemContext = 'store';
    $_GET = array(
        'action' => 'test1',
        'default' => '<>\'',
        'defaultArray' => array(
            '<>\'',
            '<>\''
        ),
        'keyword' => '<>\'',
        'products_id' => '<>\'&&valid*',
        'blah' => 'x'
    );
    $_POST = array(
        'cPath' => 'test1',
        'action1' => 'test2'
    );
    zcRequest::init();
    zcRequest::set('action', 'new');
    zcRequest::set('action-get', 'new1', 'get');
    zcRequest::set('action-post', 'new2', 'post');
    zcRequest::set('action-all', 'newall', 'all');
    $this->assertTrue(zcRequest::readGet('action') == 'new');
    $this->assertTrue(zcRequest::readGet('action-get') == 'new1');
    $this->assertTrue(zcRequest::readPost('action-post') == 'new2');
    $this->assertTrue(zcRequest::readGet('action-all') == 'newall');
    $this->assertTrue(zcRequest::readPost('action-all') == 'newall');
  }
  public function testRequestApplyEntrySanitizer()
  {
    global $systemContext;
    $systemContext = 'store';
    $_GET = array(
        'dummy' => '<>&&test1&&<>'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::readGet('dummy') == '&&test1&&');
    $entryParams = array(
        'source' => 'get',
        'type' => 'regexFilter',
        'parameters' => array(
            'regex' => '/[^\/0-9a-zA-Z_:@.-]/'
        )
    );
    zcRequest::applyEntrySanitizer('dummy', $entryParams);
    $this->assertTrue(zcRequest::readGet('dummy') == 'test1');
  }
  public function testRequestApplyGroupSanitizer()
  {
    global $systemContext;
    $systemContext = 'store';
    $_GET = array(
        'dummy' => '<>&&test1&&<>'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::readGet('dummy') == '&&test1&&');
    $entryParams = array(
        'source' => 'get',
        'type' => 'regexFilter',
        'parameters' => array(
            'regex' => '/[^\/0-9a-zA-Z_:@.-]/'
        )
    );
    $groupList = array(
        'dummy'
    );
    zcRequest::applyGroupSanitizer($groupList, $entryParams);
    $this->assertTrue(zcRequest::readGet('dummy') == 'test1');
  }
  public function testRequestExternalSanitizers()
  {
    global $systemContext;
    $systemContext = 'store';
    $_GET = array(
        'dummy' => '<>&&test1&&<>'
    );
    zcRequest::init();
    $this->assertTrue(zcRequest::readGet('dummy') == '&&test1&&');
    $entryParams = array(
        'source' => 'get',
        'type' => 'someSanitizerClass::sanitizerTestSanitizer',
        'parameters' => array(
            'regex' => '/[<>t]/'
        )
    );
    zcRequest::applyEntrySanitizer('dummy', $entryParams);
    $this->assertTrue(zcRequest::readGet('dummy') == '&&es1&&');
    $_GET = array(
        'dummy' => '<>&&test1&&<>'
    );
    zcRequest::init();
    $someAnonymousSanitizer = function ($value, $parameters)
    {
      $result = preg_replace($parameters ['regex'], '', $value);
      return $result;
    };
    $entryParams = array(
        'source' => 'get',
        'type' => $someAnonymousSanitizer,
        'parameters' => array(
            'regex' => '/[<>e]/'
        )
    );
    zcRequest::applyEntrySanitizer('dummy', $entryParams);
    $this->assertTrue(zcRequest::readGet('dummy') == '&&tst1&&');
  }
}