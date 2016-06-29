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
 * Class LeadCountries
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadCountries extends AbstractLeadDefinition
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_COUNTRIES,
                'alias' => 'c',
                'fkeyFieldLeft' => 'countries_id',
            ),
            'joinTables' => array(
                'TABLE_COUNTRIES_NAME' => array(
                    'table' => TABLE_COUNTRIES_NAME,
                    'alias' => 'cn',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'countries_id',
                    'addColumns' => true
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_COUNTRIES_NAME,
                    'field' => 'language_id',
                    'value' => (int)$this->request->getSession()->get('languages_id'),
                    'type' => 'AND'
                )
            ),
            'language' => true,
            'singleTable' => true,
            'languageInfoTable' => TABLE_COUNTRIES_NAME,
            'languageKeyField' => 'language_id',
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_COUNTRIES,
                    'pagingVarSrc' => 'post'
                )
            ),

        );

        $this->outputLayout = array(

            'relatedLinks' => array(
                array(
                    'text' => BOX_TAXES_ZONES,
                    'href' => zen_href_link(FILENAME_ZONES)
                ),
                array(
                    'text' => BOX_TAXES_GEO_ZONES,
                    'href' => zen_href_link(FILENAME_GEO_ZONES)
                ),
                array(
                    'text' => BOX_TAXES_TAX_CLASSES,
                    'href' => zen_href_link(FILENAME_TAX_CLASSES)
                ),
                array(
                    'text' => BOX_TAXES_TAX_RATES,
                    'href' => zen_href_link(FILENAME_TAX_RATES)
                ),
                array(
                    'text' => TEXT_ISO_LIST,
                    'href' => ISO_COUNTRY_CODES_LINK,
                    'target' => '_blank'
                )
            ),
            'listMap' => array(
                'countries_id',
                'countries_name',
                'countries_iso_code_2',
                'countries_iso_code_3',
                'address_format_id',
                'status'
            ),
            'editMap' => array(
                'countries_name',
                'countries_iso_code_2',
                'countries_iso_code_3',
                'address_format_id',
                'status'
            ),
            'multiEditMap' => array(
                'address_format_id',
                'status'
            ),
            'multiEditDisplayField' => 'countries_name',
            'fields' => array(
                'countries_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'countries_name' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_COUNTRY_NAME,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'countries_iso_code_2' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_COUNTRY_ISO2,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    )
                ),
                'countries_iso_code_3' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_COUNTRY_ISO3,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    )
                ),
                'address_format_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_FORMAT_ID,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '5'
                        )
                    ),
                ),
                'status' => array(
                    'bindVarsType' => 'integer',
                    'title' => TEXT_ENTRY_COUNTRY_STATUS,
                    'align' => 'right',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_COUNTRY_STATUS,
                            'align' => 'right',
                            'type' => 'select',
                            'size' => '5',
                            'options' => array(
                                array(
                                    'id' => '1',
                                    'text' => TEXT_ENABLED
                                ),
                                array(
                                    'id' => '0',
                                    'text' => TEXT_DISABLED
                                )
                            )
                        ),
                        'list' => array(
                            'options' => array(
                                array(
                                    'id' => '',
                                    'text' => TEXT_ALL
                                ),
                                array(
                                    'id' => '1',
                                    'text' => TEXT_ENABLED
                                ),
                                array(
                                    'id' => '0',
                                    'text' => TEXT_DISABLED
                                )
                            )
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => 'statusIconUpdater'
                    )
                )
            ),
            'formatter' => array('class' => 'AdminLead')
        );
    }
}
