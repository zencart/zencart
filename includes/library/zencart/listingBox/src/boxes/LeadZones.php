<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\ListingBox\boxes;

/**
 * Class LeadZones
 * @package ZenCart\ListingBox\boxes
 */
class LeadZones extends AbstractLeadListingBox
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_ZONES,
                'alias' => 'z',
                'fkeyFieldLeft' => 'zone_id',
            ),
            'joinTables' => array(
                'TABLE_COUNTRIES_NAME' => array(
                    'table' => TABLE_COUNTRIES_NAME,
                    'alias' => 'c',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'zone_country_id',
                    'fkeyFieldRight' => 'countries_id',
                    'addColumns' => true
                )
            ),
            'whereClauses' => array(
                array(
                    'table' => TABLE_COUNTRIES_NAME,
                    'field' => 'language_id',
                    'value' => $_SESSION ['languages_id'],
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
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_ZONES,
                    'pagingVarSrc' => 'post'
                )
            ),

        );

        $this->outputLayout = array(
            'allowDelete' => true,
            'relatedLinks' => array(
                array(
                    'text' => BOX_TAXES_COUNTRIES,
                    'href' => zen_href_link(FILENAME_COUNTRIES)
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
                )
            ),
            'listMap' => array(
                'zone_id',
                'countries_name',
                'zone_code',
                'zone_name'
            ),
            'editMap' => array(
                'countries_name',
                'zone_code',
                'zone_name',
                'zone_country_id'
            ),
            'fields' => array(
                'zone_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_ZONE,
                            'align' => 'left'
                        )
                    )
                ),
                'countries_name' => array(
                    'parentTable' => TABLE_COUNTRIES_NAME,
                    'bindVarsType' => 'string',
                    'autocomplete' => array(
                        'dataTable' => TABLE_COUNTRIES_NAME,
                        'dataSearchField' => 'countries_name',
                        'valueResponse' => 'countries_name',
                        'dataResponse' => 'countries_id',
                        'dataResponseField' => 'zone_country_id',
                        'placeholder' => TEXT_PLACEHOLDER_CHOOSE_COUNTRY
                    ),
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_COUNTRY_NAME,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'zone_country_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => '',
                            'type' => 'hidden',
                            'size' => '30'
                        )
                    )
                ),
                'zone_code' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_ZONE_CODE,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '20'
                        )
                    )
                ),
                'zone_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_ZONE_NAME,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '20'
                        )
                    )
                )
            ),
        );
    }
}
