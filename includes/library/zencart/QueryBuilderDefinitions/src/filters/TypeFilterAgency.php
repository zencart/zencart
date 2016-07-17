<?php
/**
 * Class TypeFilterAgency
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\QueryBuilderDefinitions\filters;

/**
 * Class TypeFilterAgency
 * @package ZenCart\QueryBuilderDefinitions\filters
 */
class TypeFilterAgency extends AbstractTypeFilter
{
    /**
     * @param $listingQuery
     * @return mixed
     */
    public function handleParameterFilters($listingQuery)
    {
        $listingQuery['selectList'] [] = "r.agency_name as manufacturers_name";

        $listingQuery['joinTables'] ['TABLE_PRODUCT_PIECE_EXTRA'] = array(
            'table' => TABLE_PRODUCT_PIECE_EXTRA,
            'alias' => 'pme',
            'type' => 'LEFT',
            'fkeyFieldLeft' => 'products_id'
        );
        $listingQuery['joinTables'] ['TABLE_AGENCY'] = array(
            'table' => TABLE_AGENCY,
            'alias' => 'r',
            'type' => 'LEFT',
            'fkeyFieldLeft' => 'agency_id',
            'fkeyTable' => 'TABLE_PRODUCT_PIECE_EXTRA'
        );
        if ($this->request->readGet('agency_id')) {
            $listingQuery['whereClauses'] [] = array(
                'table' => TABLE_AGENCY,
                'field' => 'agency_id',
                'value' => (int)$this->request->readGet('agency_id'),
                'type' => 'AND'
            );
            if ($this->request->readGet('filter_id')) {
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
            if ($this->request->readGet('filter_id')) {
                $listingQuery ['whereClauses'] [] = array(
                    'table' => TABLE_AGENCY,
                    'field' => 'agency_id',
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
        return 'agency';
    }

    /**
     * @return string
     */
    protected function getDefaultFilterSql()
    {
        $sql = "SELECT DISTINCT r.agency_id AS id, r.agency_name AS name
                FROM  " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCT_PIECE_EXTRA . " pme, " . TABLE_AGENCY . " r
                WHERE p.products_status = 1
                AND pme.agency_id = r.agency_id
                AND p.products_id = p2c.products_id
                AND pme.products_id = p.products_id
                AND p2c.categories_id = '" . (int)$this->params['currentCategoryId'] . "'
                ORDER BY r.agency_name";
        return $sql;
    }

    /**
     * @return string
     */
    protected function getTypeFilterSql()
    {
        $sql = "SELECT DISTINCT c.categories_id AS id, cd.categories_name AS name
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_PIECE_EXTRA . " pme, " . TABLE_AGENCY . " r
                WHERE p.products_status = 1
                AND p.products_id = pme.products_id
                AND pme.products_id = p2c.products_id
                AND pme.agency_id = r.agency_id
                AND p2c.categories_id = c.categories_id
                AND p2c.categories_id = cd.categories_id
                AND cd.language_id = '" . (int)$_SESSION ['languages_id'] . "'
                AND r.agency_id = '" . (int)$this->request->readGet('agency_id') . "'
                ORDER BY cd.categories_name";
        return $sql;
    }
}
