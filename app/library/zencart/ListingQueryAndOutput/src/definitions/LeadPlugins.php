<?php
/**
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\ListingQueryAndOutput\definitions;

class LeadPlugins extends AbstractLeadDefinition
{
    /**
     *
     */
    public function initQueryAndOutput()
    {

        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_PLUGINS,
                'fkeyFieldLeft' => 'id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_PLUGINS,
                    'pagingVarSrc' => 'post'
                )
            ),

        );

        $this->outputLayout = array(
            'allowEdit' => false,
            'allowAdd' => false,
            'listMap' => array(
                'plugin_name',
                'plugin_status'
            ),
            'fields' => array(
                'plugin_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_PLUGIN_NAME,
                            'align' => 'left',
                            'type' => 'text',
                        )
                    )
                ),

                'plugin_status' => array(
                    'bindVarsType' => 'integer',
                    'title' => TEXT_ENTRY_PLUGIN_STATUS,
                    'align' => 'right',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_PLUGIN_STATUS,
                            'align' => 'right',
                            'type' => 'select',
                            'size' => '5',
                            'options' => array(
                                array(
                                    'id' => '',
                                    'text' => TEXT_ALL
                                ),
                                array(
                                    'id' => '1',
                                    'text' => TEXT_INSTALLED
                                ),
                                array(
                                    'id' => '0',
                                    'text' => TEXT_NOT_INSTALLED
                                )
                            )
                        ),
                    ),
                    'fieldFormatter' => array(
                        'callable' => 'statusIconUpdater'
                    )
                )


            ),
        );
    }
}
