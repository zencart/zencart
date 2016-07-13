<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadTaxClasses
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadTaxClasses extends AbstractLeadDefinition
{

    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_TAX_CLASS,
                'alias' => 'tc',
                'fkeyFieldLeft' => 'tax_class_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES,
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
                    'text' => BOX_TAXES_TAX_RATES,
                    'href' => zen_href_link(FILENAME_TAX_RATES)
                )
            ),
            'listMap' => array(
                'tax_class_id',
                'tax_class_title',
                'tax_class_description',
            ),
            'editMap' => array(
                'tax_class_title',
                'tax_class_description'
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
                'tax_class_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_TAX_CLASS_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'tax_class_title' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_INFO_CLASS_TITLE,
                            'type' => 'text',
                            'size' => '30'
                        ),
                        'list' => array(
                            'title' => TEXT_ENTRY_TAX_CLASSES,
                            'align' => 'right'
                        )
                    )
                ),
                'tax_class_description' => array(
                    'bindVarsType' => 'string',
                    'align' => 'right',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_INFO_CLASS_DESCRIPTION,
                            'type' => 'text',
                            'size' => '40'
                        ),
                        'edit' => array(
                            'title' => TEXT_INFO_CLASS_DESCRIPTION,
                            'type' => 'text',
                            'size' => '155'
                        ),
                        'add' => array(
                            'title' => TEXT_INFO_CLASS_DESCRIPTION,
                            'type' => 'text',
                            'size' => '155'
                        )
                    )
                ),
            ),
        );
    }

}
