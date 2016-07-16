<?php
/**
 * @package classes
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadMediaManagerProducts
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadMediaManagerProducts extends AbstractLeadDefinition
{

    /**
     *
     */
    public function initQueryAndLayout()
    {

        $productName = function ($item, $key, $pkey) {
            return zen_get_products_name($item['product_id']);
        };


        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_MEDIA_TO_PRODUCTS,
                'alias' => 'mtp',
                'fkeyFieldLeft' => 'association_id',
            ),
            'joinTables' => array(
                'TABLE_MEDIA_MANAGER' => array(
                    'table' => TABLE_MEDIA_MANAGER,
                    'alias' => 'mm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'media_id',
                    'fkeyFieldRight' => 'media_id',
                    'selectColumns' => array('media_name')
                ),
            ),
            'whereClauses' => array(
                array(
                    'type' => 'AND',
                    'table' => TABLE_MEDIA_MANAGER,
                    'field' => 'media_id',
                    'value' => ':media_id:'
                )
            ),
            'bindVars' => array(
                array(
                    ':media_id:',
                    $this->request->readGet('media_id'),
                    'integer'
                )
            ),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_LINKED_PRODUCTS,
                    'pagingVarSrc' => 'post'
                )
            ),
        );

        $this->outputLayout = array(
            'pageTitle' => $this->getTitle(),
            'allowDelete' => true,
            'extraDeleteParameters' => '&media_id=' . $this->request->readGet('media_id'),
            'allowEdit' => false,
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
                    'text' => BOX_CATALOG_PIECE_GENRE,
                    'href' => zen_href_link(FILENAME_PIECE_GENRE)
                ),
                array(
                    'text' => BOX_CATALOG_MEDIA_TYPES,
                    'href' => zen_href_link(FILENAME_MEDIA_TYPES)
                )
            ),
            'actionLinksList' => array(
                'listView' => array(
                    'linkGetAllGetParams' => true,
                    'linkGetAllGetParamsIgnore' => array(
                        'action',
//                        'media_id'
                    )
                ),
                'addView' => array(
                    'linkGetAllGetParams' => true,
                    'linkGetAllGetParamsIgnore' => array(
                        'action',
//                        'media_id'
                    )
                ),
                'parentView' => array(
                    'linkTitle' => TEXT_PARENT_COLLECTION,
                    'linkCmd' => FILENAME_MEDIA_MANAGER,
                    'linkGetAllGetParams' => true,
                    'linkGetAllGetParamsIgnore' => array(
                        'action',
//                        'media_id'
                    )
                )
            ),
            'listMap' => array(
                'product_name',
            ),
            'editMap' => array(
                'product_id',
            ),
            'fields' => array(
                'association_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => '',
                            'align' => 'left'
                        )
                    )
                ),
                'media_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_MEDIA,
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
                'product_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_SELECT_PRODUCT,
                            'size' => '30',
                            'type' => 'productSelect'
                        )
                    )
                ),
                'product_name' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_PRODUCT_NAME,
                            'size' => '30'
                        )
                    ),
                    'fieldFormatter' => array(
                        'callable' => $productName
                    )
                ),
            ),
//            'extraRowActions' => array(),
        );
    }

    protected function getTitle()
    {
        $sql = "SELECT media_name FROM " . TABLE_MEDIA_MANAGER . " WHERE media_id = :id:";
        $sql = $this->dbConn->bindvars($sql, ':id:', $this->request->readGet('media_id'), 'integer');
        $result = $this->dbConn->execute($sql);
        return $result->fields['media_name'];
    }

}
