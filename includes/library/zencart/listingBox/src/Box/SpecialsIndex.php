<?php
/**
 * Class SpecialsIndex
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\ListingBox\Box;
/**
 * Class SpecialsIndex
 * @package ZenCart\ListingBox\Box
 */
class SpecialsIndex extends AbstractListingBox
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
            'queryLimit' => MAX_DISPLAY_SPECIAL_PRODUCTS_INDEX,
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
                    'value' => $_SESSION['languages_id'],
                    'type' => 'AND'
                )
            ),
            'orderBys' => array(
                array(
                    'field' => 'RAND()',
                    'type' => 'mysql'
                ),
                array(
                    'field' => 'specials_date_added DESC',
                    'table' => TABLE_SPECIALS,
                    'type' => 'custom'
                )
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
        return sprintf(TABLE_HEADING_SPECIALS_INDEX, strftime('%B'));
    }

    /**
     * @return mixed
     */
    public function getColumnCount()
    {
        return SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS;
    }
}
