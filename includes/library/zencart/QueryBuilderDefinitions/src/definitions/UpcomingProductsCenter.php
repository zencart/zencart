<?php
/**
 * Class UpcomingProductsCenter
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class UpcomingProductsCenter
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class UpcomingProductsCenter extends AbstractDefinition
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $zenDateShort = function ($parameters) {
            return zen_date_short($parameters['item'][$parameters['field']]);
        };

        $productHreflink = function ($parameters) {
            $link = zen_href_link(zen_get_info_page($parameters['item']['products_id']), 'cpath=' . $parameters['item']['productCpath'] . '&products_id=' . $parameters['item']['products_id']);
            $link = '<a href="' . $link . '">' . $parameters['item']['products_name'] . '</a>';
            return $link;
        };

        $this->listingQuery = array(
            'isDistinct' => false,
            'queryLimit' => MAX_DISPLAY_UPCOMING_PRODUCTS,
            'derivedItems' => array(
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
            'filters' => array(
                array(
                    'name' => 'CategoryFilter',
                    'parameters' => array(
                        'new_products_category_id' => $GLOBALS['new_products_category_id'],
                    )
                ),
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
                    'value' => $this->request->getSession()->get('languages_id'),
                    'type' => 'AND'
                ),
                array(
                    'custom' => zen_get_upcoming_date_range()
                )
            ),
            'orderBys' => array(
                array(
                    'field' => call_user_func(function () {
                        $sort = (EXPECTED_PRODUCTS_FIELD == 'date_expected') ? 'products_date_available' : 'products_name';
                        $sort .= (EXPECTED_PRODUCTS_SORT == 'asc') ? ' asc ' : ' desc ';
                        return $sort;
                    }),
                    'type' => 'custom'
                )
            )
        );
        $this->outputLayout = array(
            'boxTitle' => TABLE_HEADING_UPCOMING_PRODUCTS,
            'formatter' => array('class' => 'TabularCustom',
                                 'template' => 'tpl_listingbox_tabular_basic.php',
                                 'params' => array(
                                     'ignoreMultiAddToCart' => true,
                                 ),
                                 'sortMainPage' => SHOW_PRODUCT_INFO_MAIN_UPCOMING 
            ),
            'columns' => array(
                'products_name' => array(
                    'title' => TABLE_HEADING_PRODUCTS,
                    'col_params' => 'style="text-align:left"',
                    'formatter' => $productHreflink
                ),
                'products_date_available' => array(
                    'title' => TABLE_HEADING_DATE_EXPECTED,
                    'col_params' => 'style="text-align:right"',
                    'formatter' => $zenDateShort
                )
            )
        );
    }
}
