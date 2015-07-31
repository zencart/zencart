<?php
/**
 * Class NewProductsCenter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  wilt  New in v1.6.0 $
 */
namespace ZenCart\ListingBox\boxes;
/**
 * Class NewProductsCenter
 * @package ZenCart\ListingBox\boxes
 */
class NewProductsCenter extends AbstractListingBox
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
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
                    'name' => 'CategoryFilter',
                    'parameters' => array()
                ),
            ),
            'queryLimit' => MAX_DISPLAY_NEW_PRODUCTS,
            'joinTables' => array(
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => true
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
                    'custom' => zen_get_new_date_range()
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
            'boxTitle' => sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B')),
            'formatter' => array('class' => 'Columnar',
                                 'template' => 'tpl_listingbox_columnar.php',
                                 'params' => array(
                                     'columnCount' => SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS),
            ),
        );
    }
}
