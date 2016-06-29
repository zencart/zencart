<?php
/**
 * Class Index
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class ReportStatsProductsPurchased
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class ReportStatsProductsPurchased extends AbstractLeadDefinition
{
    /**
     *
     */
    public function initQueryAndLayout()
    {

        $this->listingQuery['main'] = array(
            'mainTable' => array(
                'table' => TABLE_PRODUCTS,
                'alias' => 'p',
                'fkeyFieldLeft' => 'products_id',
            ),
            'orderBys' => array(
                array('field' => 'products_ordered DESC')),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_PRODUCTS,
                    'pagingVarSrc' => 'post'
                )
            ),
            'language' => true,
            'languageKeyField' => 'language_id',
            'languageInfoTable' => TABLE_PRODUCTS_DESCRIPTION,

        );

        $this->outputLayout['main'] = array(


            'listMap' => array(
                'products_id',
                'products_name',
                'products_ordered',
            ),
            'fields' => array(
                'products_id' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_NUMBER,
                            'align' => 'left'
                        )
                    )
                ),
                'products_name' => array(
                    'bindVarsType' => 'string',
                    'language' => true,
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_PRODUCTS,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'products_ordered' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_PURCHASED,
                            'align' => 'left',
                        )
                    )
                ),
            ),
        );
    }
}
