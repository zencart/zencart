<?php
/**
 * File contains Paginator Scroller test cases
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcPaginatorTestCase.php');
use ZenCart\Platform\Paginator\scrollers\Standard;
/**
 * Testing Library
 */
class testPaginationScrollerCase extends zcPaginatorTestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'sessions.php');
        require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'html_output.php');
        define('SEARCH_ENGINE_FRIENDLY_URLS', false);
        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\Platform', DIR_CATALOG_LIBRARY . 'zencart/platform/src');
    }

    public function testRunScrollerWithResults()
    {
        $ds = $this->getMockBuilder('\\ZenCart\\Platform\\Paginator\\adapters\\QueryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $ds->method('getResults')->willReturn(array('totalPages'=>10, 'currentItem'=>1, 'itemsPerPage'=>10, 'resultList'=>array()));
        $params = array('pagingVarName'=>'page', 'scrollerLinkParams'=>'', 'itemsPerPage'=>'10', 'currentItem'=>'1', 'currentPage'=>'1', 'maxPageLinks'=>'10', 'cmd'=>'countries');
        $scroller = new Standard($ds, $params);
        $dsr = $scroller->getResults();
        $this->assertTrue(is_array($dsr));
        $this->assertTrue(is_array($dsr['linkList']));
        $this->assertTrue($dsr['hasItems']);
        $this->assertTrue($dsr['nextPage'] == 2);
        $this->assertTrue($dsr['prevPage'] == 0);
    }
    public function testRunScrollerWithNoResults()
    {
        $ds = $this->getMockBuilder('\\ZenCart\\Platform\\Paginator\\adapters\\QueryFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $ds->method('getResults')->willReturn(array('totalPages'=>0, 'currentItem'=>1, 'itemsPerPage'=>10, 'resultList'=>array()));
        $params = array('pagingVarName'=>'page', 'scrollerLinkParams'=>'', 'itemsPerPage'=>'10', 'currentItem'=>'0', 'currentPage'=>'0', 'maxPageLinks'=>'10', 'totalPages'=>'0', 'cmd'=>'countries');
        $scroller = new Standard($ds, $params);
        $dsr = $scroller->getResults();
        $this->assertTrue(is_array($dsr));
        $this->assertTrue(is_array($dsr['linkList']));
        $this->assertFalse($dsr['hasItems']);
        $this->assertTrue($dsr['nextPage'] == 1);
        $this->assertTrue($dsr['prevPage'] == -1);
    }
}
