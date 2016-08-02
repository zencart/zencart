<?php
/**
 * Class SearchResults
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: currencies.php 15880 2010-04-11 16:24:30Z wilt $
 */
namespace ZenCart\QueryBuilderDefinitions\definitions;

/**
 * Class SearchResults
 * @package ZenCart\QueryBuilderDefinitions\definitions
 */
class SearchResults extends AbstractDefinition
{
    /**
     *
     */
    public function initQueryAndLayout()
    {
        $this->listingQuery = array(
            'isRandom' => false,
            'isDistinct' => true,
            'isPaginated' => true,
            'pagination' => array('adapterParams' => array('itemsPerPage' => MAX_DISPLAY_PRODUCTS_LISTING)),
            'filters' => array(
                array(
                    'name' => 'AlphaFilter',
                    'parameters' => array()
                ),
                array(
                    'name' => 'SearchResults',
                    'parameters' => array('currencies'=>$GLOBALS['currencies'])
                ),
                array(
                    'name' => 'TypeFilter',
                    'parameters' => array()
                ),
            ),
            'derivedItems' => array(
                array(
                    'field' => 'productCpath',
                    'handler' => 'productCpathBuilder'
                ),
                array( // must happen after productCpathBuilder
                    'field' => 'link',
                    'handler' => 'productLinkBuilder'
                ),
                array( // must happen after productLinkBuilder
                    'field' => 'displayPrice',
                    'handler' => 'displayPriceBuilder'
                ),
                array( // must happen after displayPriceBuilder
                    'field' => 'displayFreeTag',
                    'handler' => 'displayFreeTagBuilder'
                ),
                array( // must happen after displayPriceBuilder
                    'field' => 'priceBlock',
                    'handler' => 'priceBlockBuilder'
                ),
            ),
            'joinTables' => array(
                'TABLE_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_PRODUCTS_DESCRIPTION,
                    'alias' => 'pd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => true
                ),
                'TABLE_MANUFACTURERS' => array(
                    'table' => TABLE_MANUFACTURERS,
                    'alias' => 'm',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'manufacturers_id',
                    'addColumns' => true
                ),
                'TABLE_PRODUCTS_TO_CATEGORIES' => array(
                    'table' => TABLE_PRODUCTS_TO_CATEGORIES,
                    'alias' => 'p2c',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => false
                ),
                'TABLE_META_TAGS_PRODUCTS_DESCRIPTION' => array(
                    'table' => TABLE_META_TAGS_PRODUCTS_DESCRIPTION,
                    'alias' => 'mtpd',
                    'type' => 'left',
                    'fkeyFieldLeft' => 'products_id',
                    'addColumns' => false
                )
            )
        );
        $this->outputLayout = array(
            'formatter' => array('class' => 'TabularProduct',
                                 'template' => 'tpl_listingbox_tabular.php',
            )
        );
    }
}
