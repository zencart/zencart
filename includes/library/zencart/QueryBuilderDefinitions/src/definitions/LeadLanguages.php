<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadLanguages
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadLanguages extends AbstractLeadDefinition
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $languageName = function ($item, $key, $pkey) {
            if ($item ['code'] == DEFAULT_LANGUAGE) {
                return $item ['name'] . ' <strong>' . TEXT_ITEM_DEFAULT . '</strong>';
            } else {
                return $item ['name'];
            }
        };

        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_LANGUAGES,
                'alias' => 'l',
                'fkeyFieldLeft' => 'languages_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_LANGUAGES,
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
                    'text' => BOX_LOCALIZATION_ORDERS_STATUS,
                    'href' => zen_href_link(FILENAME_ZONES)
                ),
            ),
            'listMap' => array(
                'name',
                'code'
            ),
            'editMap' => array(
                'name',
                'code',
                'image',
                'directory',
                'sort_order',
                'setAsDefault'
            ),
            'fields' => array(
                'languages_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => '',
                            'align' => 'left'
                        )
                    )
                ),
                'name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_LANGUAGE_NAME,
                            'type' => 'text',
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $languageName
                    )
                ),
                'code' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_LANGUAGE_CODE,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'image' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_LANGUAGE_IMAGE,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'directory' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_LANGUAGE_DIRECTORY,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'sort_order' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_LANGUAGE_SORT_ORDER,
                            'type' => 'text',
                            'size' => '30'
                        )
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
                    )
                )
            ),
        );
    }

}
