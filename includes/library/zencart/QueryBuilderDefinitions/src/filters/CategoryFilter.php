<?php
/**
 * Class CategoryFilter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\QueryBuilderDefinitions\filters;

/**
 * Class CategoryFilter
 * @package ZenCart\QueryBuilderDefinitions\filters
 */
class CategoryFilter extends AbstractFilter implements FilterInterface
{
    /**
     * @param array $listingQuery
     * @return array
     */
    public function filterItem(array $listingQuery)
    {
        $manufacturers_id = $this->request->readGet('manufacturers_id', 0);
        $new_products_category_id = $this->params['new_products_category_id'];
        $cPath = $this->request->readGet('cPath');
        $categoryId = null;
        if (!$this->canBuildCategoryFilter($manufacturers_id, $new_products_category_id)) {
            return $listingQuery;
        }
        if ($manufacturers_id > 0 && $this->request->readGet('filter_id', 0) > 0) {
            $categoryId = $this->request->readGet('filter_id');
        }
        if ($cPath != '') {
            $categoryId = zenGetLeafCategory($cPath);
        }
        if (!isset($categoryId)) {
            return $listingQuery;
        }
        $listingQuery['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'alias' => 'ptc',
            'type' => 'left',
            'addColumns' => FALSE
        );
        $listingQuery ['tableAliases'] [TABLE_PRODUCTS_TO_CATEGORIES] = 'ptc';
        $categories = zenGetCategoryArrayWithChildren($categoryId);
        $listingBoxCategoryList = implode(',', $categories);
        $listingQuery['whereClauses'] [] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'field' => 'categories_id',
            'value' => $listingBoxCategoryList,
            'type' => 'AND',
            'test' => 'IN'
        );
        return $listingQuery;
    }

    /**
     * @param $manufacturers_id
     * @param $new_products_category_id
     * @return bool
     */
    protected function canBuildCategoryFilter($manufacturers_id, $new_products_category_id)
    {
        $npc = ($new_products_category_id != '0');
        $mfi = ($manufacturers_id > 0 && $this->request->readGet('filter_id', 0) == 0);
        $oef = ($this->request->readGet('piece_genre_id', 0) > 0 || $this->request->readGet('record_company_id', 0) > 0);
        $retVal = ($mfi || $oef || $npc);
        return $retVal;
    }
} 
