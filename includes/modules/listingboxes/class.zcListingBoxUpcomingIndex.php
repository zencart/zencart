<?php
/**
 * zcListingBoxUpcomingIndex
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * class zcListingBoxUpcomingIndex
 *
 * @package classes
 */
class zcListingBoxUpcomingIndex extends zcAbstractListingBoxBase
{
  /**
   * (non-PHPdoc)
   *
   * @see zcAbstractListingBoxBase::initProductQueryAndOutputLayout()
   */
  public function initProductQueryAndOutputLayout ()
  {
    $zenDateShort = function  ($parameters)
    {
      return zen_date_short ( $parameters['item'][$parameters['field']] );
    };

    $productHreflink = function  ($parameters)
    {
      $link = zen_href_link ( zen_get_info_page ( $parameters['item'][$parameters['field']] ), 'cpath=' . $parameters['item']['productCpath'] . '&products_id=' . $parameters['item']['products_id'] );
      $link = '<a href="' . $link . '">' . $parameters['item']['products_name'] . '</a>';
      return $link;
    };

    $this->productQuery = array(
        'isDistinct'=>true,
        'queryLimit' => MAX_DISPLAY_UPCOMING_PRODUCTS,
        'joinTables' => array(
            'TABLE_PRODUCTS_DESCRIPTION' => array(
                'table' => TABLE_PRODUCTS_DESCRIPTION,
                'alias' => 'pd',
                'type' => 'left',
                'fkeyFieldLeft' => 'products_id',
                'addColumns' => true
            )
        ),
        'filters' => array(
            array(
                'requestHandler' => 'zcQueryBuilderFilterCategories'
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
                'table' => TABLE_PRODUCTS_DESCRIPTION,
                'field' => 'language_id',
                'value' => $_SESSION ['languages_id'],
                'type' => 'AND'
            ),
            array(
                'custom' => zen_get_upcoming_date_range ()
            )
        ),
        'orderBys' => array(
            array(
                'field' => call_user_func(function() {
                         $sort = (EXPECTED_PRODUCTS_FIELD == 'date_expected') ? 'products_date_available' : 'products_name';
                         $sort .= (EXPECTED_PRODUCTS_SORT == 'asc') ? ' asc ' : ' desc ';
                         return $sort;
                }),
                'type' => 'custom'
            )
        )
    );
    $this->outputLayout = array(
        'formatter'=>'zcListingBoxFormatterTabularCustom',
        'columns' => array(
            'products_name' => array(
                'title' => TABLE_HEADING_PRODUCTS,
                'col_params' => 'style="text-align:left"',
                'formatter' => $productHreflink
            ),
            'products_date_available' => array(
                'title' => TABLE_HEADING_DATE_EXPECTED,
                'col_params' => 'style="text-align:right"',
                'formatter' => $zenDateShort
            )
        )
    );
  }
  /**
   * (non-PHPdoc)
   *
   * @see zcAbstractListingBoxBase::initTitle()
   */
  public function initTitle ()
  {
    $this->title = TABLE_HEADING_UPCOMING_PRODUCTS;
  }
}