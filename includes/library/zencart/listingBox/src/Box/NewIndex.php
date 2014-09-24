<?php
/**
 * Class NewIndex
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Box;
/**
 * Class NewIndex
 * @package ZenCart\ListingBox\Box
 */
class NewIndex extends AbstractListingBox
{
    /**
     *
     */
    public function __construct()
    {
        $this->productQuery = array(
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
                'TABLE_PRODUCTs_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
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
            'formatter' => array('class' => 'Columnar',
                                 'template' => 'tpl_listingbox_columnar_default.php',
            ),
        );
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
