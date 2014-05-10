<?php
/**
 * zcListingBoxNewIndex
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcListingBoxNewIndex
 *
 * @package classes
 */
class zcListingBoxNewIndex extends zcAbstractListingBoxBase
{
  /**
   * (non-PHPdoc)
   *
   * @see zcAbstractListingBoxBase::initProductQueryAndOutputLayout()
   */
  public function initProductQueryAndOutputLayout()
  {
    $this->productQuery = array(
        'isRandom' => TRUE,
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
        'queryLimit' => MAX_DISPLAY_NEW_PRODUCTS,
        'joinTables' => array(
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
                'table' => TABLE_PRODUCTS_DESCRIPTION,
                'field' => 'language_id',
                'value' => $_SESSION ['languages_id'],
                'type' => 'AND'
            )
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