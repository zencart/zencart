<?php
/**
 * Class Index
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class ReportStatsCustomers
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class ReportStatsCustomers extends AbstractLeadDefinition
{
    /**
     *
     */
    public function initQueryAndLayout()
    {

        $customersName = function ($resultItem) {
            return $resultItem['customers_firstname'] . ' ' . $resultItem['customers_lastname'];
        };
        $currencyFormat = function ($item, $key, $pkey) {
            $currencies = new \currencies();
            return $currencies->format($item['ordersum']);
        };
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_CUSTOMERS,
                'alias' => 'c',
                'fkeyFieldLeft' => 'customers_id',
            ),
            'joinTables' => array(
                'TABLE_ORDERS' => array(
                    'table' => TABLE_ORDERS,
                    'alias' => 'o',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'customers_id',
                    'fkeyFieldRight' => 'customers_id',
                ),
                'TABLE_ORDERS_PRODUCTS' => array(
                    'table' => TABLE_ORDERS_PRODUCTS,
                    'fkeyTable' => 'TABLE_ORDERS',
                    'alias' => 'op',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'orders_id',
                    'fkeyFieldRight' => 'orders_id',
                ),
            ),
            'selectList' => array('sum(op.products_quantity * op.final_price)+sum(op.onetime_charges)  as ordersum'),
            'groupBys' => array('customers_id'),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_CUSTOMERS,
                    'pagingVarSrc' => 'post'
                )
            ),

            'derivedItems' => array(
                array(
                    'context' => 'list',
                    'field' => 'customers_name',
                    'handler' => $customersName
                ),
            ),
        );

        $this->outputLayout = array(


            'listMap' => array(
                'customers_id',
                'customers_name',
                'ordersum',
            ),
            'fields' => array(
                'customers_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_NUMBER,
                            'align' => 'left'
                        )
                    )
                ),
                'customers_name' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_CUSTOMERS,
                            'align' => 'right',
                            //                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'customers_firstname' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_CUSTOMERS_NAME,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
                'customers_lastname' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_CUSTOMERS_NAME,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
                'ordersum' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_TOTAL_PURCHASED,
                            'align' => 'left',
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $currencyFormat
                    ),
                ),
            ),
        );
    }
}
