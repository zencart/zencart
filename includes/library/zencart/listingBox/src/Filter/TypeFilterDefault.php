<?php
/**
 * Class TypeFilterDefault
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Filter;
/**
 * Class TypeFilterDefault
 * @package ZenCart\ListingBox\Filter
 */
class TypeFilterDefault extends AbstractTypeFilter
{
    /**
     * @param $productQuery
     * @return mixed
     */
    public function handleParameterFilters($productQuery)
    {
        $request = $this->diContainer->get('request');

        $productQuery['selectList'] [] = "m.manufacturers_name";

        $productQuery['joinTables'] ['TABLE_MANUFACTURERS'] = array(
            'table' => TABLE_MANUFACTURERS,
            'alias' => 'm',
            'type' => 'LEFT',
            'fkeyFieldLeft' => 'manufacturers_id'
        );
        if ($request->has('manufacturers_id') && $request->readGet('manufacturers_id') != '') {
            $productQuery ['whereClauses'] [] = array(
                'table' => TABLE_MANUFACTURERS,
                'field' => 'manufacturers_id',
                'value' => (int)$request->readGet('manufacturers_id'),
                'type' => 'AND'
            );
            if ($request->has('filter_id') && zen_not_null($request->readGet('filter_id'))) {
                $productQuery['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
                    'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                    'alias' => 'p2c',
                    'type' => 'LEFT',
                    'fkeyFieldLeft' => 'products_id'
                );
                $productQuery['whereClauses'] [] = array(
                    'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                    'field' => 'categories_id',
                    'value' => (int)$request->readGet('filter_id'),
                    'type' => 'AND'
                );
            }
        } else {
            $productQuery['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
                'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                'alias' => 'p2c',
                'type' => 'LEFT',
                'fkeyFieldLeft' => 'products_id'
            );
            $productQuery['whereClauses'] [] = array(
                'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                'field' => 'categories_id',
                'value' => (int)$this->currentCategoryId,
                'type' => 'AND'
            );
            if ($request->has('filter_id') && zen_not_null($request->readGet('filter_id'))) {
                $productQuery['whereClauses'] [] = array(
                    'table' => TABLE_MANUFACTURERS,
                    'field' => 'manufacturers_id',
                    'value' => (int)$request->readGet('filter_id'),
                    'type' => 'AND'
                );
            }
        }
        return $productQuery;
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
                AND p2c.categories_id = '" . (int)$this->currentCategoryId . "'
                ORDER BY m.manufacturers_name";
        return $sql;

    }

    /**
     * @return string
     */
    protected function getTypeFilterSql()
    {
        $request = $this->diContainer->get('request');

        $sql = "SELECT DISTINCT c.categories_id AS id, cd.categories_name AS name
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                WHERE p.products_status = 1
                AND p.products_id = p2c.products_id
                AND p2c.categories_id = c.categories_id
                AND p2c.categories_id = cd.categories_id
                AND cd.language_id = '" . (int)$_SESSION ['languages_id'] . "'
                AND p.manufacturers_id = '" . (int)$request->readGet('manufacturers_id') . "'
                ORDER BY cd.categories_name";
        return $sql;
    }
}
