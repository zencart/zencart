<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadAgency
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadAgency extends AbstractLeadDefinition
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
                'table' => TABLE_AGENCY,
                'alias' => 'rc',
                'fkeyFieldLeft' => 'agency_id',
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_AGENCIES,
                    'pagingVarSrc' => 'post'
                )
            ),
            'language' => true,
            'languageInfoTable' => TABLE_AGENCY_INFO,

        );

        $this->outputLayout = array(
            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerPieceType.php',
            'allowDelete' => true,
            'relatedLinks' => array(
                array(
                    'text' => BOX_CATALOG_ARTISTS,
                    'href' => zen_href_link(FILENAME_ARTISTS)
                ),
                array(
                    'text' => BOX_CATALOG_PIECE_STYLE,
                    'href' => zen_href_link(FILENAME_PIECE_STYLE)
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
            'hasMediaUpload' => true,
            'listMap' => array(
                'agency_id',
                'agency_name',
                'agency_url',
                'linked_products',
            ),
            'editMap' => array(
                'agency_name',
                'agency_url',
                'agency_image'
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
                'agency_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'list' => array(
                            'title' => TEXT_ENTRY_AGENCY_ID,
                            'align' => 'left'
                        )
                    )
                ),
                'agency_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_AGENCY_NAME,
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'agency_image' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_AGENCY_IMAGE,
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
                'agency_url' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TEXT_ENTRY_AGENCY_URL,
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
     * @param $agencyId
     * @return mixed
     */
    protected function getLinkedProducts($agencyId)
    {
        $sql = "SELECT count(*) as count FROM " . TABLE_PRODUCT_PIECE_EXTRA . " WHERE agency_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $agencyId, 'integer');
        $result = $this->dbConn->Execute($sql);

        return $result->fields['count'];
    }

}
