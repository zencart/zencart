<?php
/**
 * Class NewDefault
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
namespace ZenCart\ListingBox\Box;
/**
 * Class NewDefault
 * @package ZenCart\ListingBox\Box
 */
class NewDefault extends AbstractListingBox
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
                    'name' => 'DisplayOrderSorter',
                    'parameters' => array(
                        'defaultSortOrder' => PRODUCT_NEW_LIST_SORT_DEFAULT
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
            'paginationQueryLimit' => MAX_DISPLAY_NEW_PRODUCTS,
            'joinTables' => array(
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
                ),
                array(
                    'custom' => zen_get_new_date_range()
                )
            )
        );
        $this->setOutputLayout(array(
            'formatter' => array('class' => 'ListStandard',
                                 'template' => 'tpl_listingbox_productliststd_default.php',
                                 'params' => array(
                                     'imageListingWidth' => IMAGE_PRODUCT_NEW_LISTING_WIDTH,
                                     'imageListingHeight' => IMAGE_PRODUCT_NEW_LISTING_HEIGHT,
                                     'definePrefix' => 'PRODUCT_NEW_')),
        ));
    }

    /**
     * @return string
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
     * @return mixed
     */
    public function getColumnCount()
    {
        return SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS;
    }
}
