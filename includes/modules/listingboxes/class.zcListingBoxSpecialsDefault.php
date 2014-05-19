<?php
/**
 * zcListingBoxSpecialsDefault
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcListingBoxSpecialsDefault
 *
 * @package classes
 */
class zcListingBoxSpecialsDefault extends zcAbstractListingBoxBase
{
  /**
   * (non-PHPdoc)
   * @see zcAbstractListingBoxBase::initProductQueryAndOutputLayout()
   */
  public function initProductQueryAndOutputLayout()
  {
    $this->productQuery = array(
        'isRandom' => FALSE,
        'isPaginated' => TRUE,
        'paginationQueryLimit' => MAX_DISPLAY_SPECIAL_PRODUCTS_INDEX,
        'filters' => array(
            array(
                'requestHandler' => 'zcQueryBuilderFilterDisplayOrderSorter',
                'parameters' => array(
                    'defaultSortOrder' => PRODUCT_SPECIALS_LIST_SORT_DEFAULT
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
        'joinTables' => array(
            'TABLE_SPECIALS' => array(
                'table' => TABLE_SPECIALS,
                'alias' => 's',
                'type' => 'left',
                'addColumns' => TRUE
            ),
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
                'table' => TABLE_PRODUCTS,
                'field' => 'products_status',
                'value' => 1,
                'type' => 'AND'
            ),
            array(
                'table' => TABLE_SPECIALS,
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
    return BOX_HEADING_SPECIALS;
    ;
  }
  /**
   */
  public function getColumnCount()
  {
    return SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS;
  }
}