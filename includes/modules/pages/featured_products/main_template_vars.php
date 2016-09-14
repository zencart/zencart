<?php
/**
 * Featured
 *
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
if (MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS > 0 )
{
    $qb = new ZenCart\QueryBuilder\QueryBuilder($db);
    $modelFactory = new ZenCart\Model\ModelFactory($db, $capsule);
    $box = new ZenCart\ListingQueryAndOutput\definitions\FeaturedProductsPage($zcRequest, $modelFactory);
    $paginator = new ZenCart\Paginator\Paginator($zcRequest);
    $builder = new ZenCart\QueryBuilder\PaginatorBuilder($zcRequest, $box->getListingQuery(), $paginator);
    $box->buildResults($qb, $db, new ZenCart\QueryBuilder\DerivedItemManager, $builder->getPaginator());
    $tplVars['listingBox'] = $box->getTplVars();
    require($template->get_template_dir('tpl_product_listing_standard.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_product_listing_standard.php');
}
