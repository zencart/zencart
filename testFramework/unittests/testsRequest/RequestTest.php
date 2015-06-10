<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');
use ZenCart\Platform\Request;

/**
 * Testing Library
 */
class testRequest extends zcTestCase
{
    public function setUp()
    {
        parent::setUp();
//        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
//        $loader = new \Aura\Autoload\Loader;
//        $loader->register();
//        $loader->addPrefix('\Aura\Web', DIR_CATALOG_LIBRARY . 'aura/web/src');
//        $loader->addPrefix('\ZenCart\Platform', DIR_CATALOG_LIBRARY . 'zencart/platform/src');
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

    /**
     * @dataProvider getQueryParams
     */
    public function testRequestGet($param, $expected, $default = null)
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
        $this->assertEquals($expected, $zcRequest->readGet($param, $default));
    }

    /**
     * data provider for testRequestGet
     * @return array
     */
    public function getQueryParams()
    {
        return array(
            array('action', 'test1'),
            array('blah', 'x'),
            array('notexists', 'exists', 'exists'),
            array('missing', null),
        );
    }

    /**
     * @dataProvider getPostParams
     */
    public function testRequestPost($param, $expected, $default = null)
    {
        $_POST = array(
            'cPath' => 'test1',
            'action' => 'test2'
        );
        $zcRequest = new Request();
        $this->assertEquals($expected, $zcRequest->readPost($param, $default));
    }

    /**
     * data provider for testRequestPost
     * @return array
     */
    public function getPostParams()
    {
        return array(
            array('action', 'test2'),
            array('cPath', 'test1'),
            array('notexists', 'exists', 'exists'),
            array('missing', null),
        );
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
