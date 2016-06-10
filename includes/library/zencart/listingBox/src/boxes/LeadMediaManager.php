<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\ListingBox\boxes;

/**
 * Class LeadMusicGenre
 * @package ZenCart\ListingBox\boxes
 */
class LeadMediaManager extends AbstractLeadListingBox
{

    /**
     *
     */
    public function initQueryAndLayout()
    {

        $linkedProducts = function ($item, $key, $pkey) {
            $count = $this->getLinkedProducts($item[$pkey]);
            return $count;
        };

        $linkedClips = function ($item, $key, $pkey) {
            $count = $this->getLinkedClips($item[$pkey]);
            return $count;
        };

        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_MEDIA_MANAGER,
                'alias' => 'mg',
                'fkeyFieldLeft' => 'media_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_MEDIA_COLLECTIONS,
                    'pagingVarSrc' => 'post'
                )
            ),
        );

        $this->outputLayout = array(
//            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerMusicGenre.php',
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
                    'text' => BOX_CATALOG_MUSIC_GENRE,
                    'href' => zen_href_link(FILENAME_MUSIC_GENRE)
                ),
                array(
                    'text' => BOX_CATALOG_MEDIA_TYPES,
                    'href' => zen_href_link(FILENAME_MEDIA_TYPES)
                )
            ),
            'listMap' => array(
                'media_name',
                'linked_clips',
                'linked_products'
            ),
            'editMap' => array(
                'media_name',
            ),
            'fields' => array(
                'media_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_MEDIA,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'media_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_MEDIA,
                            'size' => '30'
                        )
                    )
                ),
                'linked_clips' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_LINKED_CLIPS,
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $linkedClips
                    )
                ),
                'linked_products' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_LINKED_PRODUCTS,
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $linkedProducts
                    )
                ),
            ),
            'extraRowActions' => array(
                array(
                    'key' => 'assign_to_product',
                    'link' => array(
                        'cmd' => FILENAME_MEDIA_MANAGER_PRODUCTS,
                        'params' => array(
                            array(
                                'type' => 'item',
                                'name' => 'media_id',
                                'value' => 'media_id'
                            )
                        )
                    ),
                    'linkText' => TEXT_HEADING_ASSIGN_PRODUCTS
                ),
                array(
                    'key' => 'assign_to_clip',
                    'link' => array(
                        'cmd' => FILENAME_MEDIA_MANAGER_CLIPS,
                        'params' => array(
                            array(
                                'type' => 'item',
                                'name' => 'media_id',
                                'value' => 'media_id'
                            )
                        )
                    ),
                    'linkText' => TEXT_HEADING_ASSIGN_CLIPS
                ),
            ),
        );
    }


    protected function getLinkedProducts($mediaId)
    {
        $sql = "SELECT count(*) AS count FROM " . TABLE_MEDIA_TO_PRODUCTS . " WHERE media_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $mediaId, 'integer');
        $result = $this->dbConn->Execute($sql);
        return $result->fields['count'];
    }
    protected function getLinkedClips($mediaId)
    {
        $sql = "SELECT count(*) AS count FROM " . TABLE_MEDIA_CLIPS . " WHERE media_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $mediaId, 'integer');
        $result = $this->dbConn->Execute($sql);
        return $result->fields['count'];
    }
}
