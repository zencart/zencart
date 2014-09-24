<?php
/**
 * Class CategoryFilter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Filter;
/**
 * Class CategoryFilter
 * @package ZenCart\ListingBox\Filter
 */
class CategoryFilter extends AbstractFilter
{
    /**
     * @param array $productQuery
     * @return array
     */
    public function filterItem(array $productQuery)
    {
        $manufacturers_id = $this->diContainer->get('globalRegistry')['manufacturers_id'];
        $new_products_category_id = $this->diContainer->get('globalRegistry')['new_products_category_id'];
        $cPath = $this->diContainer->get('globalRegistry')['cPath'];
        $request = $this->diContainer->get('request');
        $categoryId = NULL;
        if (!$this->canBuildCategoryFilter($manufacturers_id, $new_products_category_id)) {
            return $productQuery;
        }
        if ($manufacturers_id > 0 && $request->readGet('filter_id', 0) > 0) {
            $categoryId = $request->readGet('filter_id');
        }
        if (isset($cPath) && $cPath != '') {
            $categoryId = zenGetLeafCategory($cPath);
        }
        if (!isset($categoryId)) {
            return $productQuery;
        }
        $productQuery['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'alias' => 'ptc',
            'type' => 'left',
            'addColumns' => FALSE
        );
        $productQuery ['tableAliases'] [TABLE_PRODUCTS_TO_CATEGORIES] = 'ptc';
        $categories = zenGetCategoryArrayWithChildren($categoryId);
        $listingBoxCategoryList = implode(',', $categories);
        $productQuery['whereClauses'] [] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'field' => 'categories_id',
            'value' => $listingBoxCategoryList,
            'type' => 'AND',
            'test' => 'IN'
        );
        return $productQuery;
    }

    /**
     * @param $manufacturers_id
     * @param $new_products_category_id
     * @return bool
     */
    protected function canBuildCategoryFilter($manufacturers_id, $new_products_category_id)
    {
        $request = $this->diContainer->get('request');
        $npc = (isset($new_products_category_id) && $new_products_category_id != '0');
        $mfi = ($manufacturers_id > 0 && $request->readGet('filter_id', 0) == 0);
        $oef = ($request->readGet('music_genre_id', 0) > 0 || $request->readGet('record_company_id', 0) > 0);
        $retVal = ($mfi || $oef || $npc);
        return $retVal;
    }
} 
