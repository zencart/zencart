<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
/**
 * Testing Library
 */
require_once(__DIR__ . '/../support/zcTestCase.php');

class testQueryBuilder extends zcTestCase
{
    public function setUp()
    {
        parent::setUp();
        require DIR_FS_CATALOG . 'includes/functions/functions_general.php';
        $loader = new \Aura\Autoload\Loader;
        $loader->register();
        $loader->addPrefix('\ZenCart\QueryBuilder', DIR_CATALOG_LIBRARY . 'zencart/QueryBuilder/src');
    }

    public function testInstantiate()
    {
        $o = new ZenCart\QueryBuilder\QueryBuilder(null);
        $p = $o->getParts();
        $this->assertTrue(!isset($p));
    }

    public function testSimpleProcessQuery()
    {
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array());
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
        $this->assertTrue(count($o->getQuery()) == 8);
    }

    public function testPaginatedProcessQuery()
    {
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array('isPaginated' => true));
        $o->processQuery(array('isPaginated' => true));
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
    }

    public function testMainTableProcessQuery()
    {
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array('mainTable' => true));
        $o->processQuery(array('isPaginated' => true));
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
    }

    public function testJoinTableProcessQuery()
    {
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array(
            'joinTables' => array(
                array(
                    'table' => 'join',
                    'alias' => 'j',
                    'type' => 'left',
                    'customAnd' => '',
                    'addColumns' => 'id'
                )
            )
        ));
        $o->processQuery(array('isPaginated' => true));
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
    }

    public function testJoinTableProcessQueryWithFkeyTable()
    {
        define('main', 'products');
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array(
            'joinTables' => array(
                array(
                    'table' => 'join',
                    'alias' => 'j',
                    'type' => 'left',
                    'customAnd' => '',
                    'addColumns' => 'id',
                    'fkeyTable' => 'main',
                    'fkeyFieldLeft' => 'id',
                    'fkeyFieldRight' => 'id'
                )
            )
        ));
        $o->processQuery(array('isPaginated' => true));
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
    }

    public function testWhereProcessQuery()
    {
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array(
            'whereClauses' => array(
                array(
                    'test' => '=',
                    'value' => 1,
                    'type' => '',
                    'table' => 'products',
                    'index' => '',
                    'field' => 'id'
                )
            )
        ));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array(
            'whereClauses' => array(
                array(
                    'value' => 1,
                    'type' => '',
                    'table' => 'products',
                    'index' => '',
                    'field' => 'id'
                )
            )
        ));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array(
            'whereClauses' => array(
                array(
                    'custom' => ' AND 1 = 1',
                    'value' => 1,
                    'type' => '',
                    'table' => 'products',
                    'index' => '',
                    'field' => 'id'
                )
            )
        ));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
    }

    public function testOrderBysProcessQuery()
    {
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array('orderBys' => array(array('type' => 'asc', 'field' => 'id'))));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array('orderBys' => array(array('type' => 'mysql', 'field' => 'id'))));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array('orderBys' => array(array('type' => 'asc', 'field' => 'id', 'table' =>'products'))));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
    }

    public function testSelectListProcessQuery()
    {
        $o = new ZenCart\QueryBuilder\QueryBuilder(null, array('selectList' => array('id')));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);
    }

    public function testProcessBindVars()
    {
        $qf = $this->getMock('queryFactory');
        $o = new ZenCart\QueryBuilder\QueryBuilder($qf, array('bindVars' => array(array('id', 1, 'integer'))));
        $o->processQuery(array());
        $p = $o->getParts();
        $this->assertTrue(count($p) == 12);

    }

    public function testSetParts()
    {
        $o = new ZenCart\QueryBuilder\QueryBuilder(null);
        $p = $o->getParts();
        $this->assertTrue(!isset($p));
        $o->setParts(array('test' => 1));
        $p = $o->getParts();
        $this->assertTrue($p['test'] == 1);
    }

}
