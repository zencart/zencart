<?php
/**
 * File contains Paginator test cases
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcPaginatorTestCase.php');
use ZenCart\Platform\Paginator\Paginator;
/**
 * Testing Library
 */
class testPaginationCase extends zcPaginatorTestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'sessions.php');
        require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'html_output.php');
        define('SEARCH_ENGINE_FRIENDLY_URLS', false);
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\Platform', DIR_CATALOG_LIBRARY . 'zencart/platform/src');
    }

    public function testMain()
    {
        $r = $this->getMockBuilder('\\ZenCart\\Platform\\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $db = $this->getMock('queryFactory');
        $db0 = clone($db);
        $db1 = array(array('foo'=>'bar'), array('foo'=>'bar1'));
        $db0->fields = array('total'=>'2');
        $db->method('execute')
            ->will($this->onConsecutiveCalls($db0, $db1));

        $adapterData = array('dbConn'=>$db, 'mainSql'=>'', 'countSql'=>'');
        $scrollerParams = array('cmd'=>'index');
        $p = new Paginator($r);
        $p->setScrollerParams($scrollerParams);
        $p->setAdapterParams(array());
        $p->doPagination($adapterData, 'QueryFactory', 'Standard');
        $a = $p->getAdapter();
        $s = $p->getScroller();
    }
}
