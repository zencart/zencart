<?php
/**
 * File contains zcRequest test cases
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once('support/zcCatalogTestCase.php');

/**
 * Testing Library
 */
class testRequestCase extends zcCatalogTestCase
{
    public function setUp()
    {
        parent::setUp();
        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\Aura\Web', DIR_CATALOG_LIBRARY . 'aura/web/src');

        require_once(DIR_CATALOG_LIBRARY . 'zencart/platform/src/Request.php');
        require_once('support/requestSupport.php');
    }

    public function testRequestInitEmpty()
    {
        $_GET = array();
        $_POST = array();
        $zcRequest = new Request();
        $this->assertTrue(count($zcRequest->all('get')) == 0);
        $this->assertTrue(count($zcRequest->all('post')) == 0);
    }

    public function testRequestInitSet()
    {
        $_GET = array(
            'action' => 'test1',
            'blah' => 'x'
        );
        $_POST = array(
            'cPath' => 'test1',
            'action' => 'test2'
        );
        $zcRequest = new Request();
        $this->assertTrue(count($zcRequest->all('get')) == 2);
        $this->assertTrue(count($zcRequest->all('post')) == 2);
    }

    public function testRequestGet()
    {
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
        $zcRequest = new Request();
        $this->assertTrue($zcRequest->readGet('action') == 'test1');
        $this->assertTrue($zcRequest->readGet('blah') == 'x');
        $this->assertTrue($zcRequest->get('blah', null, 'get') == 'x');
        $this->assertTrue($zcRequest->readGet('notexists', 'exists') == 'exists');
        try {
            $zcRequest->readGet('notexists');
            $this->fail("Expected exception not thrown");
        } catch (Exception $e) {
            $this->assertEquals(0, $e->getCode());
            $this->assertEquals("Exception: Could not Request::get paramName = notexists", $e->getMessage());
        }
        $this->assertTrue($zcRequest->get('notexists1', 'exists', 'get') == 'exists');
    }

    public function testRequestPost()
    {
        $_POST = array(
            'cPath' => 'test1',
            'action' => 'test2'
        );
        $zcRequest = new Request();
        $this->assertTrue($zcRequest->readPost('action') == 'test2');
        $this->assertTrue($zcRequest->readPost('cPath') == 'test1');
        $this->assertTrue($zcRequest->get('cPath', null, 'post') == 'test1');
        $this->assertTrue($zcRequest->readPost('notexists', 'exists') == 'exists');
        $this->assertTrue($zcRequest->get('notexists1', 'exists', 'post') == 'exists');
    }

    public function testRequestHas()
    {
        $_POST = array(
            'cPath' => 'test1',
            'action' => 'test2'
        );
        $_GET = array(
            'cPath1' => 'test1',
            'action1' => 'test2'
        );
        $zcRequest = new Request();
        $this->assertTrue($zcRequest->has('action', 'post'));
        $this->assertFalse($zcRequest->has('cPat', 'post'));
        $this->assertTrue($zcRequest->has('action1'));
        $this->assertFalse($zcRequest->has('cPat'));
        $this->assertTrue($zcRequest->has('action1', 'get'));
        $this->assertFalse($zcRequest->has('cPat', 'get'));
    }

    public function testRequestHasException()
    {
        $_POST = array(
            'cPath' => 'test1',
            'action' => 'test2'
        );
        $_GET = array(
            'cPath1' => 'test1',
            'action1' => 'test2'
        );
        $zcRequest = new Request();
        try {
            $zcRequest->has('cPat', 'foo');
        } catch (Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->fail();
    }

    public function testRequestAll()
    {
        unset($_GET);
        unset($_POST);
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
        $zcRequest = new Request();
        $result = $zcRequest->all('get');
        $this->assertTrue($result ['action'] == 'test1');
        $this->assertTrue(!isset($result ['action1']));
        $this->assertTrue(count($result) == 6);
        $result = $zcRequest->all('post');
        $this->assertTrue(count($result) == 2);
        $this->assertTrue($result ['action1'] == 'test2');
        $this->assertTrue(!isset($result ['action']));
    }
    public function testRequestAllException()
    {
        unset($_GET);
        unset($_POST);
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
        $zcRequest = new Request();
        try {
            $zcRequest->all('foo');
        } catch (Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->fail();
    }
    public function testGetWebFactoryRequest()
    {
        unset($_GET);
        unset($_POST);
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
        $zcRequest = new Request();
        $result = $zcRequest->getWebFactoryRequest();
        $this->assertInstanceOf('\Aura\Web\Request', $result);
    }

}