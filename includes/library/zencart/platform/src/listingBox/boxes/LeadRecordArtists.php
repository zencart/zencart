<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:New in v1.6.0  $
 */
namespace ZenCart\Platform\listingBox\boxes;

/**
 * Class LeadRecordArtists
 * @package ZenCart\Platform\listingBox\boxes
 */
class LeadRecordArtists extends AbstractLeadListingBox
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_RECORD_ARTISTS,
                'alias' => 'ra',
                'fkeyFieldLeft' => 'artists_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_RECORD_ARTISTS,
                    'pagingVarSrc' => 'post'
                )
            ),
            'language' => true,
            'languageInfoTable' => TABLE_RECORD_ARTISTS_INFO,

        );

        $this->outputLayout = array(
            'allowDelete' => true,
            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerMusicType.php',
            'relatedLinks' => array(
                array(
                    'text' => BOX_CATALOG_RECORD_COMPANY,
                    'href' => zen_href_link(FILENAME_RECORD_COMPANY)
                ),
                array(
                    'text' => BOX_CATALOG_MUSIC_GENRE,
                    'href' => zen_href_link(FILENAME_MUSIC_GENRE)
                ),
                array(
                    'text' => BOX_CATALOG_MEDIA_MANAGER,
                    'href' => zen_href_link(FILENAME_MEDIA_MANAGER)
                ),
                array(
                    'text' => BOX_CATALOG_MEDIA_TYPES,
                    'href' => zen_href_link(FILENAME_MEDIA_TYPES)
                ),
            ),
            'hasImageUpload' => true,
            'listMap' => array(
                'artists_id',
                'artists_name',
                'artists_url'
            ),
            'editMap' => array(
                'artists_name',
                'artists_url',
                'artists_image'
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
                'artists_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_RECORD_ARTIST_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'artists_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_RECORD_ARTIST_NAME,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'artists_image' => array(
                    'bindVarsType' => 'string',
                    'upload' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_RECORD_ARTIST_IMAGE,
                            'type' => 'file',
                            'uploadOptions' => array(
                                'imageDirectorySelector' => true,
                                'imageDirectoryServer' => false
                            ),
                            'size' => '30'
                        )
                    ),
                    'validations' => array(
                        'required' => false
                    )
                ),
                'artists_url' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_RECORD_ARTIST_URL,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                )
            ),
        );
    }
}
