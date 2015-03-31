<?php
/**
 * Class TypeFilterMusicGenre
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\Platform\listingBox\filters;
/**
 * Class TypeFilterMusicGenre
 * @package ZenCart\Platform\listingBox\filters
 */
class TypeFilterMusicGenre extends AbstractTypeFilter
{
    /**
     * @param $productQuery
     * @return mixed
     */
    public function handleParameterFilters($productQuery)
    {
        $productQuery['selectList'] [] = "m.music_genre_name as manufacturers_name";

        $productQuery['joinTables'] ['TABLE_PRODUCT_MUSIC_EXTRA'] = array(
            'table' => TABLE_PRODUCT_MUSIC_EXTRA,
            'alias' => 'pme',
            'type' => 'LEFT',
            'fkeyFieldLeft' => 'products_id'
        );
        $productQuery['joinTables'] ['TABLE_MUSIC_GENRE'] = array(
            'table' => TABLE_MUSIC_GENRE,
            'alias' => 'm',
            'type' => 'LEFT',
            'fkeyFieldLeft' => 'music_genre_id',
            'fkeyTable' => 'TABLE_PRODUCT_MUSIC_EXTRA'
        );
        if ($this->request->readGet('music_genre_id')) {
            $productQuery['whereClauses'] [] = array(
                'table' => TABLE_MUSIC_GENRE,
                'field' => 'music_genre_id',
                'value' => (int)$this->request->readGet('music_genre_id'),
                'type' => 'AND'
            );
            if ($this->request->readGet('filter_id')) {
                $productQuery ['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
                    'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                    'alias' => 'p2c',
                    'type' => 'LEFT',
                    'fkeyFieldLeft' => 'products_id'
                );
                $productQuery ['whereClauses'] [] = array(
                    'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                    'field' => 'categories_id',
                    'value' => (int)$this->request->readGet('filter_id'),
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
                'value' => (int)$this->params['currentCategoryId'],
                'type' => 'AND'
            );
            if ($this->request->readGet('filter_id')) {
                $productQuery['whereClauses'] [] = array(
                    'table' => TABLE_MUSIC_GENRE,
                    'field' => 'music_genre_id',
                    'value' => (int)$this->request->readGet('filter_id'),
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
        return 'music_genre';
    }

    /**
     * @return string
     */
    protected function getDefaultFilterSql()
    {
        $sql = "SELECT DISTINCT m.music_genre_id AS id, m.music_genre_name AS name
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCT_MUSIC_EXTRA . " pme, " . TABLE_MUSIC_GENRE . " m
                WHERE p.products_status = 1
                AND pme.music_genre_id = m.music_genre_id
                AND p.products_id = p2c.products_id
                AND pme.products_id = p.products_id
                AND p2c.categories_id = '" . (int)$this->params['currentCategoryId'] . "'
                ORDER BY m.music_genre_name";
        return $sql;
    }

    /**
     *
     */
    protected function getTypeFilterSql()
    {
        $sql = "SELECT DISTINCT c.categories_id AS id, cd.categories_name AS name
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_MUSIC_EXTRA . " pme, " . TABLE_MUSIC_GENRE . " m
                WHERE p.products_status = 1
                AND p.products_id = pme.products_id
                AND pme.products_id = p2c.products_id
                AND pme.music_genre_id = m.music_genre_id
                AND p2c.categories_id = c.categories_id
                AND p2c.categories_id = cd.categories_id
                AND cd.language_id = '" . (int)$_SESSION ['languages_id'] . "'
                AND m.music_genre_id = '" . (int)$this->request->readGet('music_genre_id') . "'
                ORDER BY cd.categories_name";
        return $sql;
    }
}
