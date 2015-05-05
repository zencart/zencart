<?php
/**
 * Class ProductsDefault
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\Platform\listingBox\boxes;
/**
 * Class ProductsDefault
 * @package ZenCart\Platform\listingBox\boxes
 */
class ProductsDefault extends AbstractListingBox
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->productQuery = array(
            'isRandom' => false,
            'isPaginated' => true,
            'pagination' => array('adapterParams' => array('itemsPerPage' => MAX_DISPLAY_PRODUCTS_LISTING)),
            'filters' => array(
                array(
                    'name' => 'AlphaFilter',
                    'parameters' => array()
                ),
                array(
                    'name' => 'TypeFilter',
                    'parameters' => array('currentCategoryId' => $GLOBALS['current_category_id'])
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
}
