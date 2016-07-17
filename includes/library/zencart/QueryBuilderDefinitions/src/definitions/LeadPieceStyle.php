<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadPieceStyle
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadPieceStyle extends AbstractLeadDefinition
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
                'table' => TABLE_PIECE_STYLE,
                'alias' => 'mg',
                'fkeyFieldLeft' => 'piece_style_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_PIECE_STYLES,
                    'pagingVarSrc' => 'post'
                )
            ),
        );

        $this->outputLayout = array(
            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerPieceStyle.php',
            'allowDelete' => true,
            'relatedLinks' => array(
                array(
                    'text' => BOX_CATALOG_ARTISTS,
                    'href' => zen_href_link(FILENAME_ARTISTS)
                ),
                array(
                    'text' => BOX_CATALOG_AGENCY,
                    'href' => zen_href_link(FILENAME_AGENCY)
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
                'piece_style_id',
                'piece_style_name',
                'linked_products',
            ),
            'editMap' => array(
                'piece_style_name',
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
                'piece_style_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_PIECE_STYLE_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'piece_style_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_PIECE_STYLE_NAME,
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
     * @param $pieceStyleId
     * @return mixed
     */
    protected function getLinkedProducts($pieceStyleId)
    {
        $sql = "SELECT count(*) as count FROM " . TABLE_PRODUCT_PIECE_EXTRA . " WHERE piece_style_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $pieceStyleId, 'integer');
        $result = $this->dbConn->Execute($sql);

        return $result->fields['count'];
    }
}
