<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadTaxRates
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadTaxRates extends AbstractLeadDefinition
{

    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_TAX_RATES,
                'alias' => 'tr',
                'fkeyFieldLeft' => 'tax_rates_id',
            ),
            'joinTables' => array(
                'TABLE_GEO_ZONES' => array(
                    'table' => TABLE_GEO_ZONES,
                    'alias' => 'tz',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'tax_zone_id',
                    'fkeyFieldRight' => 'geo_zone_id',
                    'addColumns' => true
                ),
                'TABLE_COUNTRIES' => array(
                    'table' => TABLE_TAX_CLASS,
                    'alias' => 'tc',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'tax_class_id',
                    'fkeyFieldRight' => 'tax_class_id',
                    'addColumns' => true
                )
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_TAX_RATES,
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
                )
            ),
            'listMap' => array(
                'tax_priority',
                'tax_class_title',
                'geo_zone_name',
                'tax_rate',
                'tax_description'
            ),
            'editMap' => array(
                'tax_priority',
                'tax_class_title',
                'geo_zone_name',
                'tax_rate',
                'tax_description',
                'tax_class_id',
                'tax_zone_id'
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
                'tax_rates_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => '',
                            'align' => 'left'
                        )
                    )
                ),
                'tax_priority' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_TAX_RATE_PRIORITY,
                            'type' => 'text',
                            'size' => '5'
                        ),
                        'list' => array(
                            'title' => TEXT_ENTRY_TAX_RATE_PRIORITY,
                            'align' => 'right'
                        )
                    ),
                    'validations' => array(
                        'pattern' => 'integer'
                    )
                ),
                'tax_rate' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_TAX_RATE,
                            'type' => 'text',
                            'size' => '15'
                        ),
                        'list' => array(
                            'title' => TEXT_ENTRY_TAX_RATE,
                            'align' => 'right'
                        )
                    ),
                    'validations' => array(
                        'pattern' => 'number'
                    )
                ),
                'tax_description' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_TAX_DESCRIPTION,
                            'type' => 'text',
                            'size' => '25'
                        ),
                        'list' => array(
                            'title' => TEXT_ENTRY_TAX_DESCRIPTION,
                            'align' => 'right'
                        )
                    )
                ),
                'tax_class_title' => array(
                    'parentTable' => TABLE_TAX_CLASS,
                    'bindVarsType' => 'string',
                    'fillByLookup' => array(
                        'dataTable' => TABLE_TAX_CLASS,
                        'dataSearchField' => 'tax_class_title',
                        'valueResponse' => 'tax_class_title',
                        'dataResponse' => 'tax_class_id',
                        'dataResponseField' => 'tax_class_id'
                    ),
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_TAX_CLASS_TITLE,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '30'
                        ),
                        'edit' => array(
                            'title' => TEXT_ENTRY_TAX_CLASS_TITLE,
                            'align' => 'right',
                            'type' => 'textSelect2',
                            'size' => '30'
                        ),
                    )
                ),
                'tax_class_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => '',
                            'type' => 'hidden',
                            'size' => '30'
                        )
                    )
                ),
                'geo_zone_name' => array(
                    'parentTable' => TABLE_GEO_ZONES,
                    'bindVarsType' => 'string',
                    'fillByLookup' => array(
                        'dataTable' => TABLE_GEO_ZONES,
                        'dataSearchField' => 'geo_zone_name',
                        'valueResponse' => 'geo_zone_name',
                        'dataResponse' => 'geo_zone_id',
                        'dataResponseField' => 'tax_zone_id'
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
                    )
                ),
                'tax_zone_id' => array(
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
