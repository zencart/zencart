<?php
/**
 * Class AllDefault
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\Platform\listingBox\boxes;
/**
 * Class AllDefault
 * @package ZenCart\Platform\listingBox\boxes
 */
class AllDefault extends AbstractListingBox
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->productQuery = array(
            'isRandom' => false,
            'isPaginated' => true,
            'pagination' => array('adapterParams' => array('itemsPerPage' => MAX_DISPLAY_PRODUCTS_ALL)),
            'filters' => array(
                array(
                    'name' => 'DisplayOrderSorter',
                    'parameters' => array(
                        'defaultSortOrder' => PRODUCT_ALL_LIST_SORT_DEFAULT
                    )
                ),
                array(
                    'name' => 'CategoryFilter',
                    'parameters' => array(
                        'new_products_category_id' => $GLOBALS['new_products_category_id'],
                        'cPath' => $this->request->readGet('cPath', '')
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
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => true
                ),
                'TABLE_MANUFACTURERS' => array(
                    'table' => TABLE_MANUFACTURERS,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'manufacturers_id',
                    'addColumns' => true
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
            'boxTitle' => TABLE_HEADING_ALL_PRODUCTS,
            'formatter' => array('class' => 'ListStandard',
                                 'template' => 'tpl_listingbox_productliststd_default.php',
                                 'params' => array(
                                     'imageListingWidth' => IMAGE_PRODUCT_ALL_LISTING_WIDTH,
                                     'imageListingHeight' => IMAGE_PRODUCT_ALL_LISTING_HEIGHT,
                                     'definePrefix' => 'PRODUCT_ALL_')),
        );
    }
}
