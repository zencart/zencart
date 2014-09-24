<?php
/**
 * File contains zcRequest test cases
 *
 * @package tests
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcPaginatorTestCase.php');
use ZenCart\Paginator\dataSources\Mysqli;
/**
 * Testing Library
 */
class testPaginationDataSourceCase extends zcPaginatorTestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\Paginator', DIR_CATALOG_LIBRARY . 'zencart/paginator/src');
    }

    public function testInstantiate()
    {
        new Mysqli();
    }
    public function testRunDataSource()
    {
        $db = $this->getMock('queryFactory');
        $db0 = clone($db);
        $db1 = array(array('foo'=>'bar'), array('foo'=>'bar1'));
        $db0->fields = array('total'=>'2');
        $db->method('execute')
            ->will($this->onConsecutiveCalls($db0, $db1));
        $data = array('sqlQueries'=>array('main'=>'', 'count'=>''), 'dbConn'=>$db);
        $parameters = array('pagingVariableName'=>'page', 'currentItem'=>1, 'itemsPerPage'=>2);
        $ds = new Mysqli();
        $ds->init($data, $parameters);
        $ds->process();
        $dsr = $ds->getDsResults();
        $this->assertTrue(is_array($dsr));
        $this->assertTrue($dsr['itemCount'] == 2);
        $this->assertTrue(count($dsr['resultList']) == 2);
    }

}
