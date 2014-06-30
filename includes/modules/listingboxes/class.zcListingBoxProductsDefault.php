<?php
/**
 * zcListingBoxProductsDefault
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcListingBoxProductsDefault
 *
 * @package classes
 */
class zcListingBoxProductsDefault extends zcAbstractListingBoxBase
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
                'requestHandler' => 'zcQueryBuilderFilterAlphaFilter'
            ),
            array(
                'requestHandler' => 'zcQueryBuilderFilterTypeFilter'
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
        'paginationQueryLimit' => MAX_DISPLAY_PRODUCTS_LISTING,
        'joinTables' => array(
            'TABLE_PRODUCTS_DESCRIPTION' => array(
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
            ),
            array(
                'table' => TABLE_PRODUCTS,
                'field' => 'products_status',
                'value' => 1,
                'type' => 'AND'
            )
        )
    );
    $this->outputLayout = array(
        'formatter'=>'zcListingBoxFormatterTabularProduct',
    );
  }
  /**
   * (non-PHPdoc)
   *
   * @see zcAbstractListingBoxBase::initTitle()
   */
  public function initTitle()
  {
    return '';
  }
  /**
   */
  public function getColumnCount()
  {
    return SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS;
  }
}