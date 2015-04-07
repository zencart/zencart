<?php
/**
 * Specials
 *
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */
if (MAX_DISPLAY_PRODUCTS_NEW > 0 )
{
    $qb = new ZenCart\Platform\QueryBuilder($db);
    $box = new ZenCart\Platform\listingBox\boxes\AllProductsPage($zcRequest);
    $paginator = new ZenCart\Platform\Paginator\Paginator($zcRequest);
    $builder = new ZenCart\Platform\listingBox\PaginatorBuilder($zcRequest, $box->getListingQuery(), $paginator);
    $box->buildResults($qb, $db, new ZenCart\Platform\listingBox\DerivedItemManager, $builder->getPaginator());
    $tplVars['listingBox'] = $box->getTplVars();
    require($template->get_template_dir('tpl_product_listing_standard.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_product_listing_standard.php');
}
