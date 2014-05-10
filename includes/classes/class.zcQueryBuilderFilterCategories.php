<?php
/**
 * File contains just the zcQueryBuilderFilterCategories class
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcQueryBuilderFilterCategories
 *
 * @package classes
 */
class zcQueryBuilderFilterCategories extends zcAbstractQueryBuilderFilterBase
{
  public function filterItem()
  {
    global $manufacturers_id, $new_products_category_id, $cPath;

    $categoryId = NULL;
    if (! ((($manufacturers_id > 0 && zcRequest::readGet('filter_id', 0) == 0) || zcRequest::readGet('music_genre_id', 0) > 0 || zcRequest::readGet('record_company_id', 0) > 0) || (! isset($new_products_category_id) || $new_products_category_id == '0'))) {
      if ($manufacturers_id > 0 && zcRequest::readGet('filter_id', 0) > 0) {
        $categoryId = zcRequest::readGet('filter_id');
      } elseif (isset($cPath) && $cPath != '') {
        $categoryId = zenGetLeafCategory($cPath);
      }
      if (isset($categoryId)) {
        $this->parts ['joinTables'] ['TABLE_PRODUCTS_TO_CATEGORIES'] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'alias' => 'ptc',
            'type' => 'left',
            'addColumns' => FALSE
        );
        $this->parts ['tableAliases'] [TABLE_PRODUCTS_TO_CATEGORIES] = 'ptc';
        $categories = zenGetCategoryArrayWithChildren($categoryId);
        $listingBoxCategoryList = implode(',', $categories);
        $this->parts ['whereClauses'] [] = array(
            'table' => TABLE_PRODUCTS_TO_CATEGORIES,
            'field' => 'categories_id',
            'value' => $listingBoxCategoryList,
            'type' => 'AND',
            'test' => 'IN'
        );
      }
    }
  }
}