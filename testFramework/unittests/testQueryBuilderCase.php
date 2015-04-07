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
require_once('support/zcTestCase.php');

class testQueryBuilderCase extends zcTestCase
{
    public function setUp()
    {
        parent::setUp();
        require DIR_CATALOG_LIBRARY . 'aura/autoload/src/Loader.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\Platform', DIR_CATALOG_LIBRARY . 'zencart/platform/src');
        define('TABLE_PRODUCTS', 'products');
    }
    public function testInstantiate()
    {
        $o = new ZenCart\Platform\QueryBuilder(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 11);
    }
    public function testSimpleProcessQuery()
    {
        $o = new ZenCart\Platform\QueryBuilder(array());
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 11);
    }
    public function testPaginatedProcessQuery()
    {
        $o = new ZenCart\Platform\QueryBuilder(array('isPaginated'=>true));
        $o->processQuery(array('isPaginated'=>true));
        $p = $o->getParts();
        $this->assertTrue(count($p) == 11);
    }
    public function testMainTableProcessQuery()
    {
        $o = new ZenCart\Platform\QueryBuilder(array('mainTable'=>true));
        $o->processQuery(array('isPaginated'=>true));
        $p = $o->getParts();
        $this->assertTrue(count($p) == 11);
    }
    public function testJoinTableProcessQuery()
    {
        $o = new ZenCart\Platform\QueryBuilder(array('joinTables'=>array(array('table'=>'join', 'alias'=>'j', 'type'=>'left', 'customAnd'=>'', 'addColumns'=>'id'))));
        $o->processQuery(array('isPaginated'=>true));
        $p = $o->getParts();
        $this->assertTrue(count($p) == 11);
    }
    public function testWhereProcessQuery()
    {
        $o = new ZenCart\Platform\QueryBuilder(array('whereClauses'=>array(array('test'=>'=', 'value'=>1, 'type'=>'', 'table'=>'products', 'index'=>'', 'field'=>'id'))));
        $o->processQuery(array('whereClauses'=>array('test'=>'=')));
        $p = $o->getParts();
        $this->assertTrue(count($p) == 11);
    }
    public function testOrderBysProcessQuery()
    {
        $o = new ZenCart\Platform\QueryBuilder(array('orderBys'=>array(array('type'=>'asc', 'field'=>'id'))));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 11);
    }
    public function testSelectListProcessQuery()
    {
        $o = new ZenCart\Platform\QueryBuilder(array('selectList'=>array('id')));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 11);
    }
    public function testBindVarsProcessQuery()
    {
        $o = new ZenCart\Platform\QueryBuilder(array('bindVars'=>array(array(':id:', 0, 'string'))));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 11);
    }
}
