<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadRecordCompany
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadRecordCompany extends AbstractLeadDefinition
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
            'deleteItemHandlerTemplate' => 'tplItemRowDeleteHandlerPieceType.php',
            'allowDelete' => true,
            'relatedLinks' => array(
                array(
                    'text' => BOX_CATALOG_RECORD_ARTISTS,
                    'href' => zen_href_link(FILENAME_RECORD_ARTISTS)
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
                )
            ),
            'hasMediaUpload' => true,
            'listMap' => array(
                'record_company_id',
                'record_company_name',
                'record_company_url',
                'linked_products',
            ),
            'editMap' => array(
                'record_company_name',
                'record_company_url',
                'record_company_image'
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
     * @param $recordCompanyId
     * @return mixed
     */
    protected function getLinkedProducts($recordCompanyId)
    {
        $sql = "SELECT count(*) as count FROM " . TABLE_PRODUCT_PIECE_EXTRA . " WHERE record_company_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $recordCompanyId, 'integer');
        $result = $this->dbConn->Execute($sql);

        return $result->fields['count'];
    }

}
