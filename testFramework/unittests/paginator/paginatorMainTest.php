<?php
/**
 * File contains zcRequest test cases
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
use ZenCart\Paginator\Paginator;

require_once(__DIR__ . '/../support/zcPaginatorTestCase.php');

/**
 * Testing Library
 */
class testPaginatorCase extends zcPaginatorTestCase
{
    public function setUp()
    {
        parent::setUp();
        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\Aura\Web', DIR_CATALOG_LIBRARY . 'aura/web/src');
        $loader->addPrefix('\ZenCart\Paginator', DIR_CATALOG_LIBRARY . 'zencart/paginator/src');
        $loader->addPrefix('\ZenCart\Platform', DIR_CATALOG_LIBRARY . 'zencart/platform/src');
        if (!defined('TEXT_DISPLAY_NUMBER_OF_ENTRIES')) {
            define('TEXT_DISPLAY_NUMBER_OF_ENTRIES', 'Items To Display');
        }
    }

    public function testDefaultInstantiate()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        new Paginator($request);
    }

    public function testInstantiateValidateFailNoHelpers()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $t = new Paginator($request, array(), array());
        try {
            $t->validate();
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
    public function testInstantiateValidateFailWithDataSource()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $t = new Paginator($request, array(), array(), 'mysqli');
        try {
            $t->validate();
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testInstantiateValidateWithHelpers()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $t = new Paginator($request, array(), array(), 'mysqli', 'standard');
        $t->validate();
    }
    public function testInstantiateValidateWithMockHelpers()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $request->method('get')->willReturn(1);
        $ds = $this->getMockBuilder('\\ZenCart\\Paginator\\dataSources\\Mysqli')
            ->disableOriginalConstructor()
            ->getMock();
        $s = $this->getMockBuilder('\\ZenCart\\Paginator\\scrollers\\Standard')
            ->disableOriginalConstructor()
            ->getMock();
        $t = new Paginator($request, array(), array(), $ds, $s);
        $t->validate();
    }
    public function testProcessWithSimpleMockHelpers()
    {
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $request->method('get')->willReturn(1);
        $ds = $this->getMockBuilder('\\ZenCart\\Paginator\\dataSources\\Mysqli')
            ->disableOriginalConstructor()
            ->getMock();
        $ds->method('getDsResults')->willReturn(array());
        $s = $this->getMockBuilder('\\ZenCart\\Paginator\\scrollers\\Standard')
            ->disableOriginalConstructor()
            ->getMock();
        $s->method('getScrollerResults')->willReturn(array());
        $t = new Paginator($request, array(), array(), $ds, $s);
        $t->init();
        $t->process();
        $p = $t->getParams();
        $this->assertTrue($p['pagingVarName'] == 'page');
        $this->assertTrue($p['pagingVarSrc'] == 'get');
        $this->assertTrue($p['itemsPerPage'] == 15);
        $this->assertTrue($p['currentPage'] == 1);
    }
    public function testPagingWithExplicitMockResults()
    {
        $dsResultArray = array('itemCount'=>31);
        $sResultArray = array();
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $request->method('get')->willReturn(1);
        $ds = $this->getMockBuilder('\\ZenCart\\Paginator\\dataSources\\Mysqli')

            ->getMock();
        $ds->method('getDsResults')->willReturn($dsResultArray);
        $s = $this->getMockBuilder('\\ZenCart\\Paginator\\scrollers\\Standard')

            ->getMock();
        $s->method('getScrollerResults')->willReturn($sResultArray);
        $t = new Paginator($request, array(), array(), $ds, $s);
        $t->process();
        $p = $t->getParams();
        $this->assertTrue($p['pagingVarName'] == 'page');
        $this->assertTrue($p['pagingVarSrc'] == 'get');
        $this->assertTrue($p['itemsPerPage'] == 15);
        $this->assertTrue($p['currentPage'] == 1);
    }
    public function testPagingTestSet1()
    {
        $dsResultArray = array('itemCount'=>7, 'totalPages'=>2, 'resultList'=>array());
        $params = array('cmd'=>'countries', 'itemsPerPage'=>5);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $request->method('get')->willReturn(1);
        $ds = $this->getMockBuilder('\\ZenCart\\Paginator\\dataSources\\Mysqli')
            ->getMock();
        $ds->method('getDsResults')->willReturn($dsResultArray);
        $t = new Paginator($request, array(), $params, $ds, 'standard');
        $t->process();
        $p = $t->getParams();
        $items = $t->getItems();
        $this->assertTrue($p['itemsPerPage'] == 5);
        $this->assertTrue($p['currentPage'] == 1);
        $this->assertTrue($p['currentItem'] == 1);
    }
    public function testPagingTestSet2()
    {
        $dsResultArray = array('itemCount'=>7, 'totalPages'=>2, 'resultList'=>array());
        $params = array('cmd'=>'countries', 'itemsPerPage'=>5);
        $request = $this->getMock('\\ZenCart\\Platform\\Request');
        $request->method('get')->willReturn(2);
        $ds = $this->getMockBuilder('\\ZenCart\\Paginator\\dataSources\\Mysqli')
            ->getMock();
        $ds->method('getDsResults')->willReturn($dsResultArray);
        $t = new Paginator($request, array(), $params, $ds, 'standard');
        $t->process();
        $p = $t->getParams();
        $t->getItems();
        $t->showPaginator();
        $this->assertTrue($p['itemsPerPage'] == 5);
        $this->assertTrue($p['currentPage'] == 2);
        $this->assertTrue($p['currentItem'] == 6);
    }
}
