<?php
/**
 * Class SearchResults
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Box;
/**
 * Class SearchResults
 * @package ZenCart\ListingBox\Box
 */
class SearchResults extends AbstractListingBox
{
    /**
     *
     */
    public function __construct()
    {
        $this->productQuery = array(
            'isRandom' => false,
            'isDistinct' => true,
            'isPaginated' => true,
            'filters' => array(
                array(
                    'name' => 'AlphaFilter',
                    'parameters' => array()
                ),
                array(
                    'name' => 'SearchResults',
                    'parameters' => array()
                ),
                array(
                    'name' => 'TypeFilter',
                    'parameters' => array()
                ),
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
            'formatter' => array('class' => 'TabularProduct',
                                 'template' => 'tpl_listingbox_tabular_default.php',
            )
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
