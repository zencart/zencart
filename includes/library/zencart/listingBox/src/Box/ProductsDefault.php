<?php
/**
 * Class ProductsDefault
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Box;
/**
 * Class ProductsDefault
 * @package ZenCart\ListingBox\Box
 */
class ProductsDefault extends AbstractListingBox
{
    /**
     *
     */
    public function __construct()
    {
        $this->productQuery = array(
            'isRandom' => FALSE,
            'isPaginated' => TRUE,
            'filters' => array(
                array(
                    'name' => 'AlphaFilter',
                    'parameters' => array()
                ),
                array(
                    'name' => 'TypeFilter',
                    'parameters' => array()
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
            'formatter' => array('class' => 'TabularProduct',
                                 'template' => 'tpl_listingbox_tabular_default.php',
            ),
        );
    }

    /**
     * @return string
     */
    public function initTitle()
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getColumnCount()
    {
        return SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS;
    }
}
