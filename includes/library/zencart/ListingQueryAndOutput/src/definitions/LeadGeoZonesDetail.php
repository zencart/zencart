<?php
/**
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id:New in v1.6.0  $
 */
namespace ZenCart\ListingQueryAndOutput\definitions;

/**
 * Class LeadGeoZonesDetail
 * @package ZenCart\ListingQueryAndOutput\definitions
 */
class LeadGeoZonesDetail extends AbstractLeadDefinition
{
    /**
     *
     */
    public function initQueryAndOutput()
    {
        $countryName = function ($item, $key, $pkey) {
            if ($item ['zone_country_id'] == 0) {
//            echo 'zone_country_id = ' . $item ['zone_country_id'];
                return TEXT_ALL_COUNTRIES;
            } else {
                return $item ['countries_name'];
            }
        };

        $zoneName = function ($item, $key, $pkey) {
            if ($item ['zone_id'] == 0) {
                return $item ['zone_name'] . TEXT_ALL_ZONES;
            } else {
                return $item ['zone_name'];
            }
        };

        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_ZONES_TO_GEO_ZONES,
                'alias' => 'zgz',
                'fkeyFieldLeft' => 'association_id',
            ),
            'whereClauses' => array(
                array(
                    'type' => 'AND',
                    'table' => TABLE_ZONES_TO_GEO_ZONES,
                    'field' => 'geo_zone_id',
                    'value' => ':geo_zone_id:'
                ),
                array(
                    'type' => 'AND',
                    'table' => TABLE_COUNTRIES_NAME,
                    'field' => 'language_id',
                    'value' => ':language_id:'
                )
            ),
            'bindVars' => array(
                array(
                    ':geo_zone_id:',
                    $this->request->readGet('geo_zone_id'),
                    'integer'
                ),
                array(
                    ':language_id:',
                    $_SESSION ['languages_id'],
                    'integer'
                )
            ),
            'joinTables' => array(
                'TABLE_COUNTRIES_NAME' => array(
                    'table' => TABLE_COUNTRIES_NAME,
                    'alias' => 'c',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'zone_country_id',
                    'fkeyFieldRight' => 'countries_id',
                    'selectColumns' => array('countries_name')
                ),
                'TABLE_ZONES' => array(
                    'table' => TABLE_ZONES,
                    'alias' => 'tz',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'zone_id',
                    'fkeyFieldRight' => 'zone_id',
                    'selectColumns' => array('zone_name', 'zone_code')
                )
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_GEO_ZONES,
                    'pagingVarSrc' => 'post'
                )
            ),

        );


        $this->outputLayout = array(
            'allowDelete' => true,
            'extraDeleteParameters' => '&geo_zone_id=' . $this->request->readGet('geo_zone_id'),
            'relatedLinks' => array(
                array(
                    'text' => BOX_TAXES_COUNTRIES,
                    'href' => zen_href_link(FILENAME_COUNTRIES)
                ),
                array(
                    'text' => BOX_TAXES_ZONES,
                    'href' => zen_href_link(FILENAME_ZONES)
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
            'actionLinksList' => array(
                'listView' => array(
                    'linkGetAllGetParams' => true,
                    'linkGetAllGetParamsIgnore' => array(
                        'action',
                        'association_id'
                    )
                ),
                'addView' => array(
                    'linkGetAllGetParams' => true,
                    'linkGetAllGetParamsIgnore' => array(
                        'action',
                        'association_id'
                    )
                ),
                'parentView' => array(
                    'linkTitle' => 'Parent Zone',
                    'linkCmd' => FILENAME_GEO_ZONES,
                    'linkGetAllGetParams' => true,
                    'linkGetAllGetParamsIgnore' => array(
                        'action',
                        'association_id'
                    )
                )
            ),
            'listMap' => array(
                'countries_name',
                'zone_name'
            ),
            'editMap' => array(
                'countries_name',
                'zone_name',
                'zone_country_id',
                'zone_id',
                'geo_zone_id'
            ),
            'autoMap' => array(
                'add' => array(
                    array(
                        'field' => 'date_added',
                        'value' => 'now()',
                        'bindVarsType' => 'passthru'
                    )
                ),
                'edit' => array(
                    array(
                        'field' => 'last_modified',
                        'value' => 'now()',
                        'bindVarsType' => 'passthru'
                    )
                )
            ),
            'fields' => array(
                'association_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => '',
                            'align' => 'left'
                        )
                    )
                ),
                'countries_name' => array(
                    'parentTable' => TABLE_COUNTRIES_NAME,
                    'bindVarsType' => 'string',
                    'fillByLookup' => array(
                        'dataTable' => TABLE_COUNTRIES_NAME,
                        'dataSearchField' => 'countries_name',
                        'valueResponse' => 'countries_name',
                        'dataResponse' => 'countries_id',
                        'dataResponseField' => 'zone_country_id',
                        'addAllResponse' => true,
                        'addAllResponseText' => TEXT_ALL_COUNTRIES,
                        'addAllResponseValue' => 0
                    ),
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_COUNTRY_NAME,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '30'
                        ),
                        'edit' => array(
                            'title' => TEXT_ENTRY_COUNTRY_NAME,
                            'align' => 'right',
                            'type' => 'textSelect2',
                            'size' => '30'
                        ),
                        'add' => array(
                            'title' => TEXT_ENTRY_COUNTRY_NAME,
                            'align' => 'right',
                            'type' => 'textSelect2',
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $countryName
                    )
                ),
                'zone_name' => array(
                    'parentTable' => TABLE_ZONES,
                    'bindVarsType' => 'string',
                    'fillByLookup' => array(
                        'custom' => 'select2DriverGeoZonesDetail.php',
                        'dataTable' => TABLE_ZONES,
                        'dataSearchField' => 'zone_name',
                        'valueResponse' => 'zone_name',
                        'dataResponse' => 'zone_id',
                        'dataResponseField' => 'zone_id',
                        'addAllResponse' => true,
                        'addAllResponseText' => TEXT_ALL_ZONES,
                        'addAllResponseValue' => 0,
                        'extraWhere' => 'zone_country_id'
                    ),
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_ZONE,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '30'
                        ),
                        'edit' => array(
                            'title' => TEXT_ENTRY_ZONE,
                            'align' => 'right',
                            'type' => 'textSelect2',
                            'size' => '30'
                        ),
                        'add' => array(
                            'title' => TEXT_ENTRY_ZONE,
                            'align' => 'right',
                            'type' => 'textSelect2',
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $zoneName
                    )
                ),
                'zone_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => '',
                            'type' => 'hidden',
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
                'geo_zone_id' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => '',
                            'align' => 'right',
                            'type' => 'hidden',
                            'size' => '20'
                        )
                    )
                ),
                'language_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => '',
                            'type' => 'hidden',
                            'size' => '30'
                        )
                    )
                )
            ),
        );
    }

}
