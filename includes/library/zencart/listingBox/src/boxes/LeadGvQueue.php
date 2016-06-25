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
        $currencyFormat = function ($item, $key, $pkey) {
            $currencies = new \currencies();
            return $currencies->format($item[$key]);
        };

        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_COUPON_GV_QUEUE,
                'alias' => 'cgq',
                'fkeyFieldLeft' => 'unique_id',
            ),
            'joinTables' => array(
                'TABLE_CUSTOMERS' => array(
                    'table' => TABLE_CUSTOMERS,
                    'alias' => 'c',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'customer_id',
                    'fkeyFieldRight' => 'customers_id',
                    'selectColumns' => array('customers_firstname', 'customers_lastname')
                ),
            ),
            'whereClauses' => array(
                array(
                    'type' => 'AND',
                    'table' => TABLE_COUPON_GV_QUEUE,
                    'field' => 'release_flag',
                    'value' => "'N'"
                ),
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS,
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

            'extraHandlerTemplates' => array('partials/tplReleaseGvHandler.php'),
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
                        )
                    ),
                    'linkText' => TEXT_LINK_RELEASE,
                    'linkParameters' => array(
                        array(
                            'type' => 'data-item',
                            'value' => 'unique_id'
                        ),
                    )
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
                            'type' => 'text',
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
                    ),
                    'fieldFormatter' => array(
                        'callable' => $currencyFormat
                    ),
                ),
                'date_created' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_DATE_PURCHASED,
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
