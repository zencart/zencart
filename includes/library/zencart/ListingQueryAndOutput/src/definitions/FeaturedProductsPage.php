<?php
/**
 * Class FeaturedProductsPage
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  wilt  New in v1.6.0 $
 */
namespace ZenCart\ListingQueryAndOutput\definitions;

/**
 * Class FeaturedProductsPage
 * @package ZenCart\ListingQueryAndOutput\definitions
 */
class FeaturedProductsPage extends AbstractDefinition
{
    /**
     *
     */
    public function initQueryAndOutput()
    {
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_PRODUCTS,
                'alias' => 'p',
                'fkeyFieldLeft' => 'products_id',
            ),
            'isPaginated' => true,
            'pagination' => array('adapterParams'=>array('itemsPerPage' => MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS )),
            'filters' => array(
                array(
                    'name' => 'DisplayOrderSorter',
                    'parameters' => array(
                        'defaultSortOrder' => PRODUCT_FEATURED_LIST_SORT_DEFAULT
                    ),
                ),
            ),
            'derivedItems' => array(
                array(
                    'field' => 'productCpath',
                    'handler' => 'productCpathBuilder'
                ),
                array( // must happen after productCpathBuilder
                    'field' => 'link',
                    'handler' => 'productLinkBuilder'
                ),
                array( // must happen after productLinkBuilder
                    'field' => 'displayPrice',
                    'handler' => 'displayPriceBuilder'
                ),
                array( // must happen after displayPriceBuilder
                    'field' => 'displayFreeTag',
                    'handler' => 'displayFreeTagBuilder'
                ),
                array( // must happen after displayPriceBuilder
                    'field' => 'priceBlock',
                    'handler' => 'priceBlockBuilder'
                ),
            ),
            'joinTables' => array(
                'TABLE_FEATURED' => array(
                    'table' => TABLE_FEATURED,
                    'alias' => 'f',
                    'type' => 'left',
                    'addColumns' => true
                ),
                'TABLE_MANUFACTURERS' => array(
                    'table' => TABLE_MANUFACTURERS,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'manufacturers_id',
                    'addColumns' => true
                ),
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
                    'table' => TABLE_FEATURED,
                    'field' => 'status',
                    'value' => 1,
                    'type' => 'AND'
                ),
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
                )
            )
        );
        $this->outputLayout = array(
            'boxTitle' => TABLE_HEADING_FEATURED_PRODUCTS,
            'formatter' => array('class' => 'ListStandard',
                                 'template' => 'tpl_listingbox_productliststd.php',
                                 'params' => array(
                                     'imageListingWidth' => IMAGE_FEATURED_PRODUCTS_LISTING_WIDTH,
                                     'imageListingHeight' => IMAGE_FEATURED_PRODUCTS_LISTING_HEIGHT,
                                     'definePrefix' => 'PRODUCT_FEATURED_')),
        );
    }
}
