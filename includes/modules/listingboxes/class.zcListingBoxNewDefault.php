<?php
/**
 * zcListingBoxNewDefault
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
/**
 * class zcListingBoxNewDefault
 *
 * @package classes
 */
class zcListingBoxNewDefault extends zcAbstractListingBoxBase
{
  /**
   * (non-PHPdoc)
   *
   * @see zcAbstractListingBoxBase::initProductQueryAndOutputLayout()
   */
  public function initProductQueryAndOutputLayout()
  {
    $this->productQuery = array(
        'isRandom' => FALSE,
        'isPaginated' => TRUE,
        'filters' => array(
            array(
                'requestHandler' => 'zcQueryBuilderFilterDisplayOrderSorter',
                'parameters' => array(
                    'defaultSortOrder' => PRODUCT_NEW_LIST_SORT_DEFAULT
                )
            )
        ),
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
        'paginationQueryLimit' => MAX_DISPLAY_NEW_PRODUCTS,
        'joinTables' => array(
            'TABLE_PRODUCTs_DESCRIPTION' => array(
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
                'custom' => zen_get_new_date_range ()
            )
    )
    );
    $this->outputLayout = array(
        'formatter'=>'zcListingBoxFormatterListStandard',
        'imageListingWidth' => IMAGE_PRODUCT_NEW_LISTING_WIDTH,
        'imageListingHeight' => IMAGE_PRODUCT_NEW_LISTING_HEIGHT,
        'definePrefix' => 'PRODUCT_NEW_'
    );
  }
  /**
   * (non-PHPdoc)
   *
   * @see zcAbstractListingBoxBase::initTitle()
   */
  public function initTitle()
  {
    $title = sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B'));
    if ($this->inCategories) {
      $categoryTitle = zen_get_categories_name((int)$this->categoryId);
      $title = sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B')) . ($categoryTitle != '' ? ' - ' . $categoryTitle : '');
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