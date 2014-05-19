<?php
/**
 * zcListingBoxFeaturedIndex
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcListingBoxFeaturedIndex
 *
 * @package classes
 */
class zcListingBoxFeaturedIndex extends zcAbstractListingBoxBase
{
  /**
   * (non-PHPdoc)
   *
   * @see zcAbstractListingBoxBase::initProductQueryAndOutputLayout()
   */
  public function initProductQueryAndOutputLayout()
  {
    $this->productQuery = array(
        'derivedItems' => array(
            array(
                'field' => 'displayPrice',
                'handler' => 'displayPriceBuilder'
            ),
            array(
                'field' => 'productCpath',
                'handler' => 'productCpathBuilder'
            )
        ),
        'filters' => array(
            array(
                'requestHandler' => 'zcQueryBuilderFilterCategories'
            )
        ),
        'queryLimit' => MAX_DISPLAY_SEARCH_RESULTS_FEATURED,
        'joinTables' => array(
            'TABLE_FEATURED' => array(
                'table' => TABLE_FEATURED,
                'alias' => 'f',
                'type' => 'left',
                'addColumns' => TRUE
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
                'table' => TABLE_PRODUCTS,
                'field' => 'products_status',
                'value' => 1,
                'type' => 'AND'
            ),
            array(
                'table' => TABLE_FEATURED,
                'field' => 'status',
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
                'field' => 'RAND()',
                'type' => 'mysql'
            ),
        )
    );
    $this->outputLayout = array(
        'formatter'=>'zcListingBoxFormatterColumnar',
    );
  }
  /**
   * (non-PHPdoc)
   *
   * @see zcAbstractListingBoxBase::initTitle()
   */
  public function initTitle()
  {
    $title = TABLE_HEADING_FEATURED_PRODUCTS;
    if ($this->inCategories) {
      $categoryTitle = zen_get_categories_name((int)$this->categoryId);
      $title = TABLE_HEADING_FEATURED_PRODUCTS . ($categoryTitle != '' ? ' - ' . $categoryTitle : '');
    }
    return $title;
  }
  /**
   */
  public function getColumnCount()
  {
    return SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS;
  }
}