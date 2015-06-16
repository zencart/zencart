<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\Platform\listingBox\boxes;

/**
 * Class LeadRecordCompany
 * @package ZenCart\Platform\listingBox\boxes
 */
class LeadRecordCompany extends AbstractLeadListingBox
{

    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_RECORD_COMPANY,
                'alias' => 'rc',
                'fkeyFieldLeft' => 'record_company_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_RECORD_COMPANIES,
                    'pagingVarSrc' => 'post'
                )
            ),
            'language' => true,
            'languageInfoTable' => TABLE_RECORD_COMPANY_INFO,

        );

        $this->outputLayout = array(
            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerMusicType.php',
            'allowDelete' => true,
            'relatedLinks' => array(
                array(
                    'text' => BOX_CATALOG_RECORD_ARTISTS,
                    'href' => zen_href_link(FILENAME_RECORD_ARTISTS)
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
                )
            ),
            'hasImageUpload' => true,
            'listMap' => array(
                'record_company_id',
                'record_company_name',
                'record_company_url'
            ),
            'editMap' => array(
                'record_company_name',
                'record_company_url',
                'record_company_image'
            ),
            'fields' => array(
                'record_company_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_RECORD_COMPANY_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'record_company_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_RECORD_COMPANY_NAME,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'record_company_image' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_RECORD_COMPANY_IMAGE,
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
                'record_company_url' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_RECORD_COMPANY_URL,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                )
            ),
        );
    }

}
