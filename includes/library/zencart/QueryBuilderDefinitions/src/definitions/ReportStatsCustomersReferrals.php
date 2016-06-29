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
 * Class ReportStatsCustomersReferrals
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class ReportStatsCustomersReferrals extends AbstractLeadDefinition
{
    /**
     *
     */
    public function initQueryAndLayout()
    {

        $customersName = function ($resultItem) {
            return $resultItem['customers_firstname'] . ' ' . $resultItem['customers_lastname'];
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
                    'addColumns' => 'orders_id, order_total, date_purchased',
                ),
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_REFERRALS,
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
                'customers_name',
                'customers_referral',
                'date_purchased',
                'orders_id',
                'order_total',
            ),
            'extraRowActions' => array(
                array(
                    'key' => 'edit_order',
                    'link' => array(
                        'cmd' => FILENAME_ORDERS,
                        'params' => array(
                            array(
                                'type' => 'item',
                                'name' => 'orders_id',
                                'value' => 'orders_id'
                            )
                        )
                    ),
                    'linkText' => TEXT_LINK_EDIT_ORDER
                ),
            ),
            'fields' => array(
                'customers_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => 'id',
                            'align' => 'left'
                        )
                    )
                ),
                'customers_firstname' => array(
                    'bindVarsType' => 'string',
                ),
                'customers_lastname' => array(
                    'bindVarsType' => 'string',
                ),
                'customers_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_CUSTOMER,
                            'align' => 'left'
                        )
                    )
                ),
                'customers_referral' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_REFERRAL_CODE,
                            'type' => 'text',
                            'align' => 'left'
                        )
                    )
                ),
                'orders_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_ORDER_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'order_total' => array(
                    'bindVarsType' => 'float',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_ORDER_TOTAL,
                            'align' => 'left'
                        )
                    )
                ),
                'date_purchased' => array(
                    'parentTable' => TABLE_ORDERS,
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_ORDER_DATE,
                            'type' => 'dateRange',
                            'align' => 'left'
                        )
                    )
                ),
            ),
        );
    }
}
