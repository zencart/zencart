<?php
/**
 * Class Index
 *
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:$
 */
namespace ZenCart\ListingBox\boxes;

/**
 * Class LeadCountries
 * @package ZenCart\ListingBox\boxes
 */
class ReportStatsProductsViewed extends AbstractLeadListingBox
{
    /**
     *
     */
    public function initQueryAndLayout()
    {

        $languageName = function($resultItem)
        {
           return \zen_get_language_name($resultItem['language_id']);
        };

        $this->listingQuery = array(
            'mainTable' => array(
                'table' => TABLE_PRODUCTS_DESCRIPTION,
                'alias' => 'p',
                'fkeyFieldLeft' => 'products_id',
            ),
            'orderBys' => array(array('field' => 'products_viewed DESC')),
            'isPaginated' => true,
            'pagination' => array(
                'scrollerParams' => array(
                    'navLinkText' => TEXT_DISPLAY_NUMBER_OF_PRODUCTS,
                    'pagingVarSrc' => 'post'
                )
            ),
            'derivedItems' => array(
                array(
                    'context' => 'list',
                    'field' => 'language_id',
                    'handler' => $languageName
                ),
            ),
        );

        $this->outputLayout = array(


            'listMap' => array(
                'products_id',
                'products_name',
                'language_id',
                'products_viewed',
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
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_PRODUCTS,
                            'align' => 'right',
                            'type' => 'text',
                            'size' => '30'
                        )
                    )
                ),
                'products_viewed' => array(
                    'bindVarsType' => 'integer',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_VIEWED,
                            'align' => 'left',
                        )
                    )
                ),
                'language_id' => array(
                    'bindVarsType' => 'string',
                    'layout' => array(
                        'common' => array(
                            'title' => TABLE_HEADING_LANGUAGE,
                            'align' => 'right',
                            'type' => 'select',
                            'size' => '5'
                        ),
                        'list' => array(
                            'options' => \zen_get_languages_list()
                        ),
                    ),
                ),
            ),
        );
    }
}
