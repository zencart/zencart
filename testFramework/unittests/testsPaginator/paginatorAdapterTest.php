<?php
/**
 * File contains paginator adapter test cases
 *
 * @package tests
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcTestCase.php');
use ZenCart\Paginator\adapters\SqlQuery;

/**
 * Testing Library
 */
class testPaginationAdapterCase extends zcTestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\Paginator', DIR_CATALOG_LIBRARY . 'zencart/Paginator/src');
    }

    public function testRunAdapter()
    {
        $db = $this->getMockBuilder('queryFactory')->getMock();
        $db0 = clone($db);
        $db1 = array(array('foo' => 'bar'), array('foo' => 'bar1'));
        $db0->fields = array('total' => '2');
        $db->method('execute')
            ->will($this->onConsecutiveCalls($db0, $db1));
        $data = array('mainSql' => '', 'countSql' => '', 'dbConn' => $db);
        $parameters = array('pagingVarName' => 'page', 'currentItem' => 1, 'itemsPerPage' => 2);
        $ds = new SqlQuery($data, $parameters);
        $dsr = $ds->getResults();
        $this->assertTrue(is_array($dsr));
        $this->assertTrue($dsr['totalItemCount'] == 2);
        $this->assertTrue(count($dsr['resultList']) == 2);
    }

    public function testStaticBuild()
    {
        $db = $this->getMockBuilder('queryFactory')->getMock();
        $db0 = clone($db);
        $db1 = array(array('foo' => 'bar'), array('foo' => 'bar1'));
        $db0->fields = array('total' => '2');
        $db->method('execute')
            ->will($this->onConsecutiveCalls($db0, $db1));
        $data = array('sqlQueries' => array('main' => '', 'count' => ''), 'dbConn' => $db);

    }
}
