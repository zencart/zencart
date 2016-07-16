<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadPieceGenre
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadPieceGenre extends AbstractLeadDefinition
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
                'table' => TABLE_PIECE_GENRE,
                'alias' => 'mg',
                'fkeyFieldLeft' => 'piece_genre_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_PIECE_GENRES,
                    'pagingVarSrc' => 'post'
                )
            ),
        );

        $this->outputLayout = array(
            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerPieceGenre.php',
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
                'piece_genre_id',
                'piece_genre_name',
                'linked_products',
            ),
            'editMap' => array(
                'piece_genre_name',
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
                'piece_genre_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_PIECE_GENRE_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'piece_genre_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_PIECE_GENRE_NAME,
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
     * @param $pieceGenreId
     * @return mixed
     */
    protected function getLinkedProducts($pieceGenreId)
    {
        $sql = "SELECT count(*) as count FROM " . TABLE_PRODUCT_PIECE_EXTRA . " WHERE piece_genre_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $pieceGenreId, 'integer');
        $result = $this->dbConn->Execute($sql);

        return $result->fields['count'];
    }
}
