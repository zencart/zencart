<?php
/**
 * File contains paginator adapter test cases
 *
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
require_once(__DIR__ . '/../support/zcPaginatorTestCase.php');
use ZenCart\Platform\Paginator\adapters\QueryFactory;
/**
 * Testing Library
 */
class testPaginationAdapterCase extends zcPaginatorTestCase
{
    public function setUp()
    {
        parent::setUp();
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/mysql/query_factory.php';
        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\Platform', DIR_CATALOG_LIBRARY . 'zencart/platform/src');
    }

    public function testRunAdapter()
    {
        $db = $this->getMock('queryFactory');
        $db0 = clone($db);
        $db1 = array(array('foo'=>'bar'), array('foo'=>'bar1'));
        $db0->fields = array('total'=>'2');
        $db->method('execute')
            ->will($this->onConsecutiveCalls($db0, $db1));
        $data = array('sqlQueries'=>array('main'=>'', 'count'=>''), 'dbConn'=>$db);
        $parameters = array('pagingVarName'=>'page', 'currentItem'=>1, 'itemsPerPage'=>2);
        $ds = new QueryFactory($data, $parameters);
        $dsr = $ds->getResults();
        $this->assertTrue(is_array($dsr));
        $this->assertTrue($dsr['totalItemCount'] == 2);
        $this->assertTrue(count($dsr['resultList']) == 2);
    }

    public function testStaticBuild()
    {
        $db = $this->getMock('queryFactory');
        $db0 = clone($db);
        $db1 = array(array('foo'=>'bar'), array('foo'=>'bar1'));
        $db0->fields = array('total'=>'2');
        $db->method('execute')
            ->will($this->onConsecutiveCalls($db0, $db1));
        $data = array('sqlQueries'=>array('main'=>'', 'count'=>''), 'dbConn'=>$db);

    }
}
