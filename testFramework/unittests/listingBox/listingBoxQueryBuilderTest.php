<?php
require_once(__DIR__ . '/../support/zcListingBoxTestCase.php');

/**
 * Testing Library
 */
class testListingBoxQueryBuilder extends zcListingBoxTestCase
{
    public function testQueryBuilderDefault()
    {

        $di = $this->simpleInstantiation();
        $productQuery = array(
            'isPaginated' => true,
            'joinTables' => array(
                'TABLE_FEATURED' => array(
                    'table' => TABLE_FEATURED,
                    'alias' => 'f',
                    'type' => 'left',
                    'addColumns' => true
                ),
                'TABLE_PRODUCTs_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => TRUE
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_FEATURED,
                    'field' => 'status',
                    'value' => 1,
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_status',
                    'value' => 1,
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'field' => 'language_id',
                    'value' => $_SESSION ['languages_id'],
                    'type' => 'AND'
                )
            ),
            'orderBys' => array(
                array(
                    'field' => 'products_id DESC',
                    'table' => TABLE_PRODUCTS,
                    'type' => 'custom'
                )
            ),

            'mainTable' => array('table' => TABLE_PRODUCTS, 'alias' => 'p', 'fkeyFieldLeft' => 'products_id'));
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $parts = $qb->getParts();
        $this->assertEquals($parts['bindVars'], array());
        $this->assertEquals($parts['mainTableName'], 'products');
        $this->assertEquals($parts['tableAliases']['products'], 'p');
        $query = $qb->getQuery();
        $this->assertTrue(array_key_exists('select', $query));
        $this->assertTrue(array_key_exists('joins', $query));
        $this->assertTrue(array_key_exists('table', $query));
        $this->assertTrue(array_key_exists('where', $query));
        $this->assertTrue(array_key_exists('orderBy', $query));
        $this->assertEquals($qb->getMainQuery(), 'SELECT p.*, f.*, pd.* FROM (products AS p LEFT JOIN featured AS f ON p.products_id = f.products_id LEFT JOIN products_description AS pd ON p.products_id = pd.products_id ) WHERE 1 AND f.status = 1 AND p.products_status = 1 AND pd.language_id = 1 ORDER BY p.products_id DESC ');
    }

    public function testQueryBuilderNoJoins()
    {
        $di = $this->simpleInstantiation();

        $productQuery = array(
            'isPaginated' => true,
            'mainTable' => array('table' => TABLE_PRODUCTS, 'alias' => 'p', 'fkeyFieldLeft' => 'products_id'));
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $parts = $qb->getParts();
        $this->assertEquals($parts['bindVars'], array());
        $this->assertEquals($parts['mainTableName'], 'products');
        $this->assertEquals($parts['tableAliases']['products'], 'p');
        $this->assertEquals($qb->getMainQuery(), 'SELECT p.* FROM products AS p  WHERE 1');
    }

    public function testQueryBuilderSelectList()
    {
        $di = $this->simpleInstantiation();

        $productQuery = array(
            'isPaginated' => true,
            'selectList' => array('p.products_name'),
            'mainTable' => array('table' => TABLE_PRODUCTS, 'alias' => 'p', 'fkeyFieldLeft' => 'products_id'));
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);

        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $parts = $qb->getParts();
        $this->assertEquals($parts['bindVars'], array());
        $this->assertEquals($parts['mainTableName'], 'products');
        $this->assertEquals($parts['tableAliases']['products'], 'p');
        $this->assertEquals($qb->getMainQuery(), 'SELECT p.*, p.products_name FROM products AS p  WHERE 1');
    }

    public function testQueryBuilderFkeyStuff()
    {
        $di = $this->simpleInstantiation();
        $productQuery = array(
            'isPaginated' => true,
            'joinTables' => array(
                'TABLE_TAX_RATES' => array(
                    'table' => TABLE_TAX_RATES,
                    'alias' => 'tr',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_tax_class_id',
                    'fkeyFieldRight' => 'tax_class_id',
                    'addColumns' => FALSE
                ),
                'TABLE_ZONES_TO_GEO_ZONES' => array(
                    'table' => TABLE_ZONES_TO_GEO_ZONES,
                    'alias' => 'gz',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'tax_zone_id',
                    'fkeyFieldRight' => 'geo_zone_id',
                    'fkeyTable' => 'TABLE_TAX_RATES',
                    'customAnd' => 'AND (gz.zone_country_id IS NULL OR gz.zone_country_id = 0 OR gz.zone_country_id = :zoneCountryId:) AND (gz.zone_id IS NULL OR gz.zone_id = 0 OR gz.zone_id = :zoneId:)',
                    'addColumns' => FALSE
                ),
                'TABLE_PRODUCT_MUSIC_EXTRA' => array(
                    'table' => TABLE_PRODUCT_MUSIC_EXTRA,
                    'alias' => 'pme',
                    'type' => 'LEFT',
                    'fkeyFieldLeft' => 'products_id'
                ),
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => TRUE
                ),
                'TABLE_MUSIC_GENRE' => array(
                    'table' => TABLE_MUSIC_GENRE,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'music_genre_id',
                    'fkeyTable' => 'TABLE_PRODUCT_MUSIC_EXTRA'
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'field' => 'language_id',
                    'value' => $_SESSION ['languages_id'],
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_status',
                    'value' => 1,
                    'type' => 'AND'
                )
            ));
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);

        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $parts = $qb->getParts();
        $this->assertEquals($parts['bindVars'], array());
        $this->assertEquals($parts['mainTableName'], 'products');
        $this->assertEquals($parts['tableAliases']['products'], 'p');
        $this->assertEquals($qb->getMainQuery(), 'SELECT p.*, pd.* FROM (products AS p LEFT JOIN tax_rates AS tr ON p.products_tax_class_id = tr.tax_class_id LEFT JOIN zones_to_geo_zones AS gz ON tr.tax_zone_id = gz.geo_zone_id  AND (gz.zone_country_id IS NULL OR gz.zone_country_id = 0 OR gz.zone_country_id = :zoneCountryId:) AND (gz.zone_id IS NULL OR gz.zone_id = 0 OR gz.zone_id = :zoneId:) LEFT JOIN product_music_extra AS pme ON p.products_id = pme.products_id LEFT JOIN products_description AS pd ON p.products_id = pd.products_id LEFT JOIN music_genre AS m ON pme.music_genre_id = m.music_genre_id ) WHERE 1 AND pd.language_id = 1 AND p.products_status = 1');
    }


    public function testQueryBuilderCustomWhere()
    {
        $di = $this->simpleInstantiation();
        $productQuery = array(
            'isPaginated' => true,
            'joinTables' => array(
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => TRUE
                ),
                'TABLE_MANUFACTURERS' => array(
                    'table' => TABLE_MANUFACTURERS,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'manufacturers_id',
                    'addColumns' => TRUE
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'field' => 'language_id',
                    'value' => $_SESSION ['languages_id'],
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_status',
                    'value' => 1,
                    'type' => 'AND'
                ),
                array(
                    'custom' => zen_get_new_date_range()
                )
            ));

        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $parts = $qb->getParts();
        $this->assertEquals($parts['bindVars'], array());
        $this->assertEquals($parts['mainTableName'], 'products');
        $this->assertEquals($parts['tableAliases']['products'], 'p');
        $this->assertEquals($qb->getMainQuery(), 'SELECT p.*, pd.*, m.* FROM (products AS p LEFT JOIN products_description AS pd ON p.products_id = pd.products_id LEFT JOIN  AS m ON p.manufacturers_id = m.manufacturers_id ) WHERE 1 AND pd.language_id = 1 AND p.products_status = 1  ');
    }

    public function testQueryBuilderWhereIn()
    {
        $di = $this->simpleInstantiation();
        $productQuery = array(
            'isPaginated' => true,
            'joinTables' => array(
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => TRUE
                ),
                'TABLE_MANUFACTURERS' => array(
                    'table' => TABLE_MANUFACTURERS,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'manufacturers_id',
                    'addColumns' => TRUE
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'field' => 'language_id',
                    'value' => $_SESSION ['languages_id'],
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_status',
                    'value' => 1,
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_id',
                    'value' => '1,2',
                    'type' => 'AND',
                    'test' => 'IN'
                ),
            ));
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $parts = $qb->getParts();
        $this->assertEquals($parts['bindVars'], array());
        $this->assertEquals($parts['mainTableName'], 'products');
        $this->assertEquals($parts['tableAliases']['products'], 'p');
        $this->assertEquals($qb->getMainQuery(), 'SELECT p.*, pd.*, m.* FROM (products AS p LEFT JOIN products_description AS pd ON p.products_id = pd.products_id LEFT JOIN  AS m ON p.manufacturers_id = m.manufacturers_id ) WHERE 1 AND pd.language_id = 1 AND p.products_status = 1 AND p.products_id IN ( 1,2 ) ');
    }

    public function testQueryBuilderWhereLike()
    {
        $di = $this->simpleInstantiation();
        $productQuery = array(
            'isPaginated' => true,
            'joinTables' => array(
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => TRUE
                ),
                'TABLE_MANUFACTURERS' => array(
                    'table' => TABLE_MANUFACTURERS,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'manufacturers_id',
                    'addColumns' => TRUE
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'field' => 'language_id',
                    'value' => $_SESSION ['languages_id'],
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_status',
                    'value' => 1,
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_name',
                    'value' => '%test%',
                    'type' => 'AND',
                    'test' => 'LIKE'
                ),
            ));
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $parts = $qb->getParts();
        $this->assertEquals($parts['bindVars'], array());
        $this->assertEquals($parts['mainTableName'], 'products');
        $this->assertEquals($parts['tableAliases']['products'], 'p');
        $this->assertEquals($qb->getMainQuery(), 'SELECT p.*, pd.*, m.* FROM (products AS p LEFT JOIN products_description AS pd ON p.products_id = pd.products_id LEFT JOIN  AS m ON p.manufacturers_id = m.manufacturers_id ) WHERE 1 AND pd.language_id = 1 AND p.products_status = 1 AND p.products_name LIKE %test% ');
    }

    public function testQueryBuilderOrderMysql()
    {
        $di = $this->simpleInstantiation();
        $productQuery = array(
            'isPaginated' => true,
            'joinTables' => array(
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => TRUE
                ),
                'TABLE_MANUFACTURERS' => array(
                    'table' => TABLE_MANUFACTURERS,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'manufacturers_id',
                    'addColumns' => TRUE
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'field' => 'language_id',
                    'value' => $_SESSION ['languages_id'],
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_status',
                    'value' => 1,
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_name',
                    'value' => '%test%',
                    'type' => 'AND',
                    'test' => 'LIKE'
                ),
            ),
            'orderBys' => array(
                array(
                    'field' => 'RAND()',
                    'type' => 'mysql'
                ),
            )
        );
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $parts = $qb->getParts();
        $this->assertEquals($parts['bindVars'], array());
        $this->assertEquals($parts['mainTableName'], 'products');
        $this->assertEquals($parts['tableAliases']['products'], 'p');
        $this->assertEquals($qb->getMainQuery(), 'SELECT p.*, pd.*, m.* FROM (products AS p LEFT JOIN products_description AS pd ON p.products_id = pd.products_id LEFT JOIN  AS m ON p.manufacturers_id = m.manufacturers_id ) WHERE 1 AND pd.language_id = 1 AND p.products_status = 1 AND p.products_name LIKE %test%  ORDER BY  RAND() ');
    }


    public function testExecuteQueryNotPaginated()
    {
        $di = $this->simpleInstantiation();
        $productQuery = array(
            'isPaginated' => false
        );
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $qb->executeQuery();
    }

    public function testExecuteQueryPaginated()
    {
        $di = $this->simpleInstantiation();
        $this->mockPaginator();
        $productQuery = array(
            'isPaginated' => true
        );
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $dim = $this->mockDerivedItemManager();
        $di->set('derivedItemManager', $dim);
        $p = $this->mockPaginator();
        $di->set('paginator', $p);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $qb->executeQuery();
    }

    public function testQueryBuilderBindVars()
    {
        $di = $this->simpleInstantiation();
        $productQuery = array(
            'isPaginated' => false,
            'joinTables' => array(
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => TRUE
                ),
                'TABLE_MANUFACTURERS' => array(
                    'table' => TABLE_MANUFACTURERS,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'manufacturers_id',
                    'addColumns' => TRUE
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'field' => 'language_id',
                    'value' => ':languageId:',
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_status',
                    'value' => 1,
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_name',
                    'value' => '%test%',
                    'type' => 'AND',
                    'test' => 'LIKE'
                ),
            ),
            'orderBys' => array(
                array(
                    'field' => 'RAND()',
                    'type' => 'mysql'
                ),
            ),
            'bindVars' => array(
                array(
                    ':languageId:',
                    1,
                    'integer'
                )
            )
        );
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $qb->executeQuery();
        $parts = $qb->getParts();
        $this->assertEquals($parts['mainTableName'], 'products');
        $this->assertEquals($parts['tableAliases']['products'], 'p');
    }
    public function testQueryBuilderSetParts()
    {
        $di = $this->simpleInstantiation();

        $productQuery = array(
            'isPaginated' => true,
            'selectList' => array('p.products_name'),
            'mainTable' => array('table' => TABLE_PRODUCTS, 'alias' => 'p', 'fkeyFieldLeft' => 'products_id'));
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);

        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $qb->setParts(array('testParts'=>'test'));
        $parts = $qb->getParts();
        $this->assertEquals($parts['testParts'], 'test');
    }
    public function testQueryBuilderBindVarsPaginated()
    {
        $di = $this->simpleInstantiation();
        $productQuery = array(
            'isPaginated' => true,
            'joinTables' => array(
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => TRUE
                ),
                'TABLE_MANUFACTURERS' => array(
                    'table' => TABLE_MANUFACTURERS,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'manufacturers_id',
                    'addColumns' => TRUE
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'field' => 'language_id',
                    'value' => ':languageId:',
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_status',
                    'value' => 1,
                    'type' => 'AND'
                ),
                array(
                    'table' => TABLE_PRODUCTS,
                    'field' => 'products_name',
                    'value' => '%test%',
                    'type' => 'AND',
                    'test' => 'LIKE'
                ),
            ),
            'orderBys' => array(
                array(
                    'field' => 'RAND()',
                    'type' => 'mysql'
                ),
            ),
            'bindVars' => array(
                array(
                    ':languageId:',
                    1,
                    'integer'
                )
            )
        );
        $listingBox = $this->mockListingBox(null, $productQuery);
        $di->set('listingBox', $listingBox);
        $p = $this->mockPaginator();
        $dim = $this->mockDerivedItemManager();
        $di->set('derivedItemManager', $dim);
        $di->set('paginator', $p);
        $qb = new \ZenCart\Platform\QueryBuilder();
        $qb->init($di);
        $qb->processQuery();
        $qb->executeQuery();
        $result = $qb->getResultItems();
        $this->assertTrue(count($result) === 1);
    }
}
