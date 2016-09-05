<?php
/**
 * Class TypeFilterDefault
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingQueryAndOutput\filters;

/**
 * Class TypeFilterDefault
 * @package ZenCart\ListingQueryAndOutput\filters
 */
class TypeFilterDefault extends AbstractTypeFilter
{
    /**
     * @param $listingQuery
     * @return mixed
     */
    public function handleParameterFilters($listingQuery)
    {
        $listingQuery['selectList'] [] = "m.manufacturers_name";

        $listingQuery['joinTables'] ['TABLE_MANUFACTURERS'] = array(
            'table' => TABLE_MANUFACTURERS,
            'alias' => 'm',
            'type' => 'LEFT',
            'fkeyFieldLeft' => 'manufacturers_id'
        );
        if ($this->request->readGet('manufacturers_id', '') != '') {
            $listingQuery ['whereClauses'] [] = array(
                'table' => TABLE_MANUFACTURERS,
                'field' => 'manufacturers_id',
                'value' => (int)$this->request->readGet('manufacturers_id'),
                'type' => 'AND'
            );
            if ($this->request->readGet('filter_id', '') != '') {
                $listingQuery['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
                    'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                    'alias' => 'p2c',
                    'type' => 'LEFT',
                    'fkeyFieldLeft' => 'products_id'
                );
                $listingQuery['whereClauses'] [] = array(
                    'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                    'field' => 'categories_id',
                    'value' => (int)$this->request->readGet('filter_id'),
                    'type' => 'AND'
                );
            }
        } else {
            $listingQuery['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
                'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                'alias' => 'p2c',
                'type' => 'LEFT',
                'fkeyFieldLeft' => 'products_id'
            );
            $listingQuery['whereClauses'] [] = array(
                'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                'field' => 'categories_id',
                'value' => (int)$this->params['currentCategoryId'],
                'type' => 'AND'
            );
            if ($this->request->readGet('filter_id', '') != '') {
                $listingQuery['whereClauses'] [] = array(
                    'table' => TABLE_MANUFACTURERS,
                    'field' => 'manufacturers_id',
                    'value' => (int)$this->request->readGet('filter_id'),
                    'type' => 'AND'
                );
            }
        }
        return $listingQuery;
    }

    /**
     * @return string
     */
    protected function getGetTypeParam()
    {
        return 'manufacturers_id';
    }

    /**
     * @return string
     */
    protected function getDefaultFilterSql()
    {
        $sql = "SELECT DISTINCT m.manufacturers_id AS id, m.manufacturers_name AS name
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m
                WHERE p.products_status = 1
                AND p.manufacturers_id = m.manufacturers_id
                AND p.products_id = p2c.products_id
                AND p2c.categories_id = '" . (int)$this->params['currentCategoryId'] . "'
                ORDER BY m.manufacturers_name";
        return $sql;

    }

    /**
     * @return string
     */
    protected function getTypeFilterSql()
    {
        $sql = "SELECT DISTINCT c.categories_id AS id, cd.categories_name AS name
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                WHERE p.products_status = 1
                AND p.products_id = p2c.products_id
                AND p2c.categories_id = c.categories_id
                AND p2c.categories_id = cd.categories_id
                AND cd.language_id = '" . (int)$_SESSION ['languages_id'] . "'
                AND p.manufacturers_id = '" . (int)$this->request->readGet('manufacturers_id') . "'
                ORDER BY cd.categories_name";
        return $sql;
    }
}
