<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id:New in v1.6.0  $
 */
namespace ZenCart\ListingBox\boxes;

/**
 * Class LeadOrdersStatus
 * @package ZenCart\Platform\listingBox\boxes
 */
class LeadOrdersStatus extends AbstractLeadListingBox
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $orderStatusName = function ($item, $key, $pkey) {
            if ($item ['orders_status_id'] == DEFAULT_ORDERS_STATUS_ID) {
                return $item ['orders_status_name'] . ' <strong>' . TEXT_ITEM_DEFAULT . '</strong>';
            } else {
                return $item ['orders_status_name'];
            }
        };


        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_ORDERS_STATUS,
                'alias' => 'os',
                'fkeyFieldLeft' => 'orders_status_id',
            ),
            'language' => true,
            'singleTable' => true,
            'languageInfoTable' => TABLE_ORDERS_STATUS,
            'languageKeyField' => 'language_id',
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_ORDERS_STATUS,
                    'pagingVarSrc' => 'post'
                )
            ),

        );

        $this->outputLayout = array(
            'allowDelete' => true,
            'relatedLinks' => array(
                array(
                    'text' => BOX_LOCALIZATION_CURRENCIES,
                    'href' => zen_href_link(FILENAME_COUNTRIES)
                ),
                array(
                    'text' => BOX_LOCALIZATION_LANGUAGES,
                    'href' => zen_href_link(FILENAME_GEO_ZONES)
                ),
            ),
            'listMap' => array(
                'orders_status_id',
                'orders_status_name'
            ),
            'editMap' => array(
                'orders_status_name',
                'setAsDefault'
            ),
            'fields' => array(
                'orders_status_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_ORDERS_STATUS_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'orders_status_name' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_ORDERS_STATUS_NAME,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $orderStatusName
                    )
                ),
                'setAsDefault' => array(
                    'fieldType' => 'display',
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_SET_DEFAULT,
                            'align' => 'right',
                            'type' => 'checkbox'
                        )
                    ),
                )
            ),
        );
    }
}
