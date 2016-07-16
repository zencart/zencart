<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:New in v1.6.0  $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadRecordArtists
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadRecordArtists extends AbstractLeadDefinition
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
            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerPieceType.php',
            'relatedLinks' => array(
                array(
                    'text' => BOX_CATALOG_AGENCY,
                    'href' => zen_href_link(FILENAME_AGENCY)
                ),
                array(
                    'text' => BOX_CATALOG_PIECE_GENRE,
                    'href' => zen_href_link(FILENAME_PIECE_GENRE)
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
            'hasMediaUpload' => true,
            'listMap' => array(
                'artists_id',
                'artists_name',
                'artists_url',
                'linked_products',
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
                                'mediaDirectorySelector' => true,
                                'mediaDirectoryServer' => false
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
     * @param $artistsId
     * @return mixed
     */
    protected function getLinkedProducts($artistsId)
    {
        $sql = "SELECT count(*) as count FROM " . TABLE_PRODUCT_PIECE_EXTRA . " WHERE artists_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $artistsId, 'integer');
        $result = $this->dbConn->Execute($sql);

        return $result->fields['count'];
    }
}
