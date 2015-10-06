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
class LeadMusicGenre extends AbstractLeadListingBox
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

        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_MUSIC_GENRE,
                'alias' => 'mg',
                'fkeyFieldLeft' => 'music_genre_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_MUSIC_GENRES,
                    'pagingVarSrc' => 'post'
                )
            ),
        );

        $this->outputLayout = array(
            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerMusicGenre.php',
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
                    'text' => BOX_CATALOG_MEDIA_TYPES,
                    'href' => zen_href_link(FILENAME_MEDIA_TYPES)
                )
            ),
            'listMap' => array(
                'music_genre_id',
                'music_genre_name',
                'linked_products',
            ),
            'editMap' => array(
                'music_genre_name',
            ),
            'fields' => array(
                'music_genre_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_MUSIC_GENRE_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'music_genre_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_MUSIC_GENRE_NAME,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'linked_products' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_LINKED_PRODUCTS,
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                      'callable' => $linkedProducts
                    )
                ),
            ),
        );
    }

    /**
     * @param $musicGenreId
     * @return mixed
     */
    protected function getLinkedProducts($musicGenreId)
    {
        $sql = "SELECT count(*) as count FROM " . TABLE_PRODUCT_MUSIC_EXTRA . " WHERE music_genre_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $musicGenreId, 'integer');
        $result = $this->dbConn->Execute($sql);

        return $result->fields['count'];
    }
}
