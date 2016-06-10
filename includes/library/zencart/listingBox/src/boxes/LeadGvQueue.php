<?php
/**
 * Class Index
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\ListingBox\boxes;

/**
 * Class LeadCountries
 * @package ZenCart\ListingBox\boxes
 */
class LeadGvQueue extends AbstractLeadListingBox
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
                'TABLE_COUPON_GV_QUEUE' => array(
                    'table' => TABLE_COUPON_GV_QUEUE,
                    'alias' => 'cgq',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'customers_id',
                    'fkeyFieldRight' => 'customer_id',
                    'selectColumns' => array('unique_id', 'order_id', 'amount', 'date_created')
                ),
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_COUNTRIES,
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

            'allowEdit' => false,
            'allowAdd' => false,
            'showActionLinkListList' => false,
            'relatedLinks' => array(
                array(
                    'text' => BOX_COUPON_ADMIN,
                    'href' => zen_href_link(FILENAME_COUPON_ADMIN)
                ),
            ),
            'extraRowActions' => array(
                array(
                    'key' => 'release_gv',
                    'link' => array(
                        'cmd' => FILENAME_GV_QUEUE,
                        'params' => array(
                            array(
                                'type' => 'text',
                                'name' => 'action',
                                'value' => 'release'
                            ),
                            array(
                                'type' => 'item',
                                'name' => 'gid',
                                'value' => 'unique_id'
                            )
                        )
                    ),
                    'linkText' => TEXT_LINK_RELEASE
                ),
            ),
            'listMap' => array(
                'customers_name',
                'order_id',
                'amount',
                'date_created',
            ),
            'fields' => array(
                'customers_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_CUSTOMERS,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
                'order_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_ORDERS_ID,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
                'amount' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_VOUCHER_VALUE,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
                'date_created' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_VOUCHER_VALUE,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
                'customers_firstname' => array(
                    'bindVarsType' => 'string',
                ),
                'customers_lastname' => array(
                    'bindVarsType' => 'string',
                ),
            ),
            'formatter' => array('class' => 'AdminLead')
        );
    }
}
