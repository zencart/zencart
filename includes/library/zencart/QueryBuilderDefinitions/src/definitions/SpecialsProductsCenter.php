<?php
/**
 * Class SpecialsProductsCenter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  wilt  New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class SpecialsProductsCenter
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class SpecialsProductsCenter extends AbstractDefinition
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
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
                                 'template' => 'tpl_listingbox_columnar.php',
                                 'params' => array(
                                     'columnCount' => SHOW_PRODUCT_INFO_COLUMNS_SPECIALS_PRODUCTS),
            ),
        );
    }

}
