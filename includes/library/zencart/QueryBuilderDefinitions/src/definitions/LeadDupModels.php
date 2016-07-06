<?php
/**
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * Some portions @copyright Copyright 2011-2016 That Software Guy
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class LeadDupModels
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class LeadDupModels extends AbstractLeadDefinition
{

    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_PRODUCTS,
                'alias' => 'p',
                'fkeyFieldLeft' => 'products_id',
            ),
            'joinTables' => array(
                'TABLE_PRODUCTS_NAME' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => true
                )
            ),
            'whereClauses' => array(
                array(
                    'type' => 'AND',
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'field' => 'language_id',
                    'value' => ':language_id:'
                ),
                array(
                    'custom' => " AND products_model IN (SELECT products_model FROM " . TABLE_PRODUCTS . " GROUP BY products_model HAVING (count( products_model ) >1)) ORDER BY products_model "
                )
            ),
            'bindVars' => array(
                array(
                    ':language_id:',
                    $_SESSION ['languages_id'],
                    'integer'
                )
            ),
            'singleTable' => true,
            'isPaginated' => true,
            'language' => true,
            'languageKeyField' => 'language_id',
            'languageInfoTable' => TABLE_PRODUCTS_DESCRIPTION,
        );
        $this->outputLayout = array(
            'allowDelete' => false,
            'allowAdd' => false,
            'relatedLinks' => array(
                array(
                    'text' => BOX_CATALOG_CATEGORIES_PRODUCTS,
                    'href' => zen_href_link(FILENAME_CATEGORIES)
                )
            ),
            'listMap' => array(
                'products_id',
                'products_name',
                'products_model'
            ),
            'editMap' => array(
                'products_name',
                'products_model'
            ),
            'fields' => array(
                'products_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => HEADING_PRODUCTS_ID, 
                            'align' => 'left'
                        )
                    )
                ),
                'products_name' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => HEADING_PRODUCTS_NAME, 
                            'align' => 'left',
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'products_model' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => HEADING_PRODUCTS_MODEL, 
                            'align' => 'left',
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                )
            )
        );
    }

}
