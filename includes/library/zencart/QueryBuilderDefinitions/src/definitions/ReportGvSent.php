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
 * Class ReportGvSent
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class ReportGvSent extends AbstractLeadDefinition
{
    /**
     * @todo consider putting helpers like $dateShort into a trait
     */
    public function initQueryAndLayout()
    {

        $sendersName = function ($resultItem) {
            return $resultItem['sent_firstname'] . ' ' . $resultItem['sent_lastname'];
        };
        $dateShort = function ($item, $key, $pkey) {
            return zen_date_short($item[$key]);
        };
        $redeemDateFormat = function ($item, $key, $pkey) {
            $result = TEXT_INFO_NOT_REDEEMED;
            if ($item['redeem_date'] != '') {
                $result = zen_date_short($item['redeem_date']);
            }
            return $result;
        };


        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_COUPONS,
                'alias' => 'c',
                'fkeyFieldLeft' => 'coupon_id',
            ),
            'joinTables' => array(
                'TABLE_COUPON_REDEEM_TRACK' => array(
                    'table' => TABLE_COUPON_REDEEM_TRACK,
                    'alias' => 'crt',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'coupon_id',
                    'fkeyFieldRight' => 'coupon_id',
                    'selectColumns' => array('redeem_date')
                ),
                'TABLE_COUPON_EMAIL_TRACK' => array(
                    'table' => TABLE_COUPON_EMAIL_TRACK,
                    'fkeyTable' => 'TABLE_COUPONS',
                    'alias' => 'cet',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'coupon_id',
                    'fkeyFieldRight' => 'coupon_id',
                    'selectColumns' => array('sent_firstname', 'sent_lastname', 'date_sent', 'emailed_to')
                ),
            ),
            'whereClauses' => array(
                array(
                    'type' => 'AND',
                    'table' => TABLE_COUPONS,
                    'field' => 'coupon_type',
                    'value' => "'G'"
                ),
            ),
            'orderBys' => array(
                array('field' => 'date_sent DESC')),
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
                    'field' => 'senders_name',
                    'handler' => $sendersName
                ),
            ),
        );

        $this->outputLayout = array(


            'listMap' => array(
                'senders_name',
                'coupon_amount',
                'coupon_code',
                'date_sent',
                'redeem_date',
                'emailed_to'
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
                'senders_name' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_SENDERS_NAME,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
                'sent_firstname' => array(
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
                'sent_lastname' => array(
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
                'coupon_amount' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_VOUCHER_VALUE,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
                'coupon_code' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_VOUCHER_CODE,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
                'date_sent' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_DATE_SENT,
                            'align' => 'right',
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $dateShort
                    ),
                ),
                'redeem_date' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_HEADING_DATE_REDEEMED,
                            'align' => 'right',
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $redeemDateFormat
                    ),
                ),
                'emailed_to' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_INFO_EMAIL_ADDRESS,
                            'align' => 'right',
                            'size' => '30'
                        )
                    )
                ),
            ),
        );
    }
}
