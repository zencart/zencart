<?php
/**
 * zcListingBoxSearchResults
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcListingBoxSearchResults
 *
 * @package classes
 */
class zcListingBoxSearchResults extends zcAbstractListingBoxBase
{
  /**
   * (non-PHPdoc)
   *
   * @see zcAbstractListingBoxBase::initProductQueryAndOutputLayout()
   */
  public function initProductQueryAndOutputLayout()
  {
    $this->productQuery = array(
        'isRandom' => false,
        'isDistinct' => true,
        'isPaginated' => true,
        'filters' => array(
            array(
                'requestHandler' => 'zcQueryBuilderFilterAlphaFilter'
            ),
            array(
                'requestHandler' => 'zcQueryBuilderFilterSearchResultsFilter'
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
            ),
            'TABLE_MANUFACTURERS' => array(
                'table' => TABLE_MANUFACTURERS,
                'alias' => 'm',
                'type' => 'left',
                'fkeyFieldLeft' => 'manufacturers_id',
                'addColumns' => TRUE
            ),
            'TABLE_PRODUCTS_TO_CATEGORIES' => array(
                'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                'alias' => 'p2c',
                'type' => 'left',
                'fkeyFieldLeft' => 'products_id',
                'addColumns' => FALSE
            ),
            'TABLE_META_TAGS_PRODUCTS_DESCRIPTION' => array(
                'table' => TABLE_META_TAGS_PRODUCTS_DESCRIPTION,
                'alias' => 'mtpd',
                'type' => 'left',
                'fkeyFieldLeft' => 'products_id',
                'addColumns' => FALSE
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