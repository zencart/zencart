<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\ListingBox\boxes;

/**
 * Class LeadMediaTypes
 * @package ZenCart\ListingBox\boxes
 */
class LeadMediaTypes extends AbstractLeadListingBox
{

    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_MEDIA_TYPES,
                'alias' => 'mt',
                'fkeyFieldLeft' => 'type_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_MEDIA_TYPES,
                    'pagingVarSrc' => 'post'
                )
            ),
        );

        $this->outputLayout = array(
            'allowDelete' => true,
            'relatedLinks' => array(
                array(
                    'text' => BOX_CATALOG_RECORD_ARTISTS,
                    'href' => zen_href_link(FILENAME_RECORD_ARTISTS)
                ),
                array(
                    'text' => BOX_CATALOG_RECORD_COMPANY,
                    'href' => zen_href_link(FILENAME_RECORD_COMPANY)
                ),
                array(
                    'text' => BOX_CATALOG_MEDIA_MANAGER,
                    'href' => zen_href_link(FILENAME_MEDIA_MANAGER)
                ),
                array(
                    'text' => BOX_CATALOG_MUSIC_GENRE,
                    'href' => zen_href_link(FILENAME_MUSIC_GENRE)
                )
            ),
            'listMap' => array(
                'type_id',
                'type_name',
                'type_ext',
            ),
            'editMap' => array(
                'type_name',
                'type_ext',
            ),
            'fields' => array(
                'type_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_MEDIA_TYPE_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'type_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_TYPE_NAME,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'type_ext' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_TYPE_EXT,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
            ),
        );
    }

}
