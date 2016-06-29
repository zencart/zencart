<?php
/**
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadCurrencies
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadCurrencies extends AbstractLeadDefinition
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $currencyName = function ($item, $key, $pkey) {
            if ($item ['code'] == DEFAULT_CURRENCY) {
                return $item ['title'] . ' <strong>' . TEXT_ITEM_DEFAULT . '</strong>';
            } else {
                return $item ['title'];
            }
        };
        $lastUpdated = function ($item, $key, $pkey) {
            return zen_datetime_short($item ['last_updated']);
        };

        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_CURRENCIES,
                'alias' => 'c',
                'fkeyFieldLeft' => 'currencies_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_CURRENCIES,
                    'pagingVarSrc' => 'post'
                )
            ),

        );

        $this->outputLayout = array(
            'allowDelete' => true,
            'relatedLinks' => array(
                array(
                    'text' => TEXT_UPDATE_CURRENCIES,
                    'href' => zen_href_link(FILENAME_CURRENCIES, 'action=updateCurrencies')
                ),
                array(
                    'text' => TEXT_ISO_LIST,
                    'href' => ISO_CURRENCY_CODES_LINK,
                    'target' => '_blank'
                ),
                array(
                    'text' => BOX_LOCALIZATION_LANGUAGES,
                    'href' => zen_href_link(FILENAME_LANGUAGES),
                ),
            ),
            'listMap' => array(
                'title',
                'code',
                'value',
                'last_updated'  // zen_datetime_short()   TEXT_INFO_CURRENCY_LAST_UPDATED
            ),
            'editMap' => array(
                'title',
                'code',
                'value',
                'symbol_left',
                'symbol_right',
                'decimal_point',
                'thousands_point',
                'decimal_places',
                'setAsDefault'
            ),
            'fields' => array(
                'currencies_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => '',
                            'align' => 'left'
                        )
                    )
                ),
                'title' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_CURRENCY_NAME,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $currencyName
                    )
                ),
                'code' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_CURRENCY_CODE,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    )
                ),
                'symbol_left' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_CURRENCY_SYMBOL_LEFT,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    )
                ),
                'symbol_right' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_CURRENCY_SYMBOL_RIGHT,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    ),
                    'validations' => array(
                        'required' => false
                    )
                ),
                'decimal_point' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_CURRENCY_DECIMAL_POINT,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    )
                ),
                'thousands_point' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_CURRENCY_THOUSANDS_POINT,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    )
                ),
                'decimal_places' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_CURRENCY_DECIMAL_PLACES,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    )
                ),
                'value' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_CURRENCY_VALUE,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    )
                ),
                'last_updated' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_LAST_UPDATED,
                            'align' => 'right',
                        )
                    ),
                    'fieldFormatter' => array(
                    'callable' => $lastUpdated
                    )
                ),
                'setAsDefault' => array(
                    'fieldType' => 'display',
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_SET_AS_DEFAULT,
                            'align' => 'right',
                            'type' => 'checkbox'
                        )
                    )
                )
            ),
            'formatter' => array('class' => 'AdminLead')
        );
    }

}
