<?php
/**
 * Class TypeFilterPieceStyle
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\QueryBuilderDefinitions\filters;

/**
 * Class TypeFilterPieceStyle
 * @package ZenCart\QueryBuilderDefinitions\filters
 */
class TypeFilterPieceStyle extends AbstractTypeFilter
{
    /**
     * @param $listingQuery
     * @return mixed
     */
    public function handleParameterFilters($listingQuery)
    {
        $listingQuery['selectList'] [] = "m.piece_style_name as manufacturers_name";

        $listingQuery['joinTables'] ['TABLE_PRODUCT_PIECE_EXTRA'] = array(
            'table' => TABLE_PRODUCT_PIECE_EXTRA,
            'alias' => 'pme',
            'type' => 'LEFT',
            'fkeyFieldLeft' => 'products_id'
        );
        $listingQuery['joinTables'] ['TABLE_PIECE_STYLE'] = array(
            'table' => TABLE_PIECE_STYLE,
            'alias' => 'm',
            'type' => 'LEFT',
            'fkeyFieldLeft' => 'piece_style_id',
            'fkeyTable' => 'TABLE_PRODUCT_PIECE_EXTRA'
        );
        if ($this->request->readGet('piece_style_id')) {
            $listingQuery['whereClauses'] [] = array(
                'table' => TABLE_PIECE_STYLE,
                'field' => 'piece_style_id',
                'value' => (int)$this->request->readGet('piece_style_id'),
                'type' => 'AND'
            );
            if ($this->request->readGet('filter_id')) {
                $listingQuery ['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
                    'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                    'alias' => 'p2c',
                    'type' => 'LEFT',
                    'fkeyFieldLeft' => 'products_id'
                );
                $listingQuery ['whereClauses'] [] = array(
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
                $listingQuery['whereClauses'] [] = array(
                    'table' => TABLE_PIECE_STYLE,
                    'field' => 'piece_style_id',
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
        return 'piece_style';
    }

    /**
     * @return string
     */
    protected function getDefaultFilterSql()
    {
        $sql = "SELECT DISTINCT m.piece_style_id AS id, m.piece_style_name AS name
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCT_PIECE_EXTRA . " pme, " . TABLE_PIECE_STYLE . " m
                WHERE p.products_status = 1
                AND pme.piece_style_id = m.piece_style_id
                AND p.products_id = p2c.products_id
                AND pme.products_id = p.products_id
                AND p2c.categories_id = '" . (int)$this->params['currentCategoryId'] . "'
                ORDER BY m.piece_style_name";
        return $sql;
    }

    /**
     *
     */
    protected function getTypeFilterSql()
    {
        $sql = "SELECT DISTINCT c.categories_id AS id, cd.categories_name AS name
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_PIECE_EXTRA . " pme, " . TABLE_PIECE_STYLE . " m
                WHERE p.products_status = 1
                AND p.products_id = pme.products_id
                AND pme.products_id = p2c.products_id
                AND pme.piece_style_id = m.piece_style_id
                AND p2c.categories_id = c.categories_id
                AND p2c.categories_id = cd.categories_id
                AND cd.language_id = '" . (int)$_SESSION ['languages_id'] . "'
                AND m.piece_style_id = '" . (int)$this->request->readGet('piece_style_id') . "'
                ORDER BY cd.categories_name";
        return $sql;
    }
}
