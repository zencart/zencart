<?php
/**
 * Class SpecialsIndex
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\Platform\listingBox\boxes;
/**
 * Class SpecialsIndex
 * @package ZenCart\Platform\listingBox\boxes
 */
class SpecialsIndex extends AbstractListingBox
{
    /**
     *
     */
    public function initQueryAndLayout()
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
                    'parameters' => array('cPath'=>$this->request->readGet('cPath'))
                ),
            ),
            'queryLimit' => MAX_DISPLAY_SPECIAL_PRODUCTS_INDEX,
            'joinTables' => array(
                'TABLE_SPECIALS' => array(
                    'table' => TABLE_SPECIALS,
                    'alias' => 's',
                    'type' => 'left',
                    'addColumns' => true
                ),
                'TABLE_PRODUCTs_DESCRIPTION' => array(
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
            'boxTitle' => sprintf(TABLE_HEADING_SPECIALS_INDEX, strftime('%B')),
            'formatter' => array('class' => 'Columnar',
                                 'template' => 'tpl_listingbox_columnar_default.php',
                                 'params' => array(
                                     'columnCount' => SHOW_PRODUCT_INFO_COLUMNS_SPECIALS_PRODUCTS),
            ),
        );
    }

}
