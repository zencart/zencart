<?php
/**
 * index header_php.php
 *
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
// This should be first line of the script:
$zco_notifier->notify ( 'NOTIFY_HEADER_START_INDEX' );

// the following cPath references come from application_top/initSystem
$category_depth = 'top';
$categoryError = false;
if (isset ( $cPath ) && zen_not_null ( $cPath ))
{
  $categories_check_query = "SELECT count(*) AS total
                                FROM   " . TABLE_CATEGORIES . "
                                WHERE   categories_id = :categoriesID";

  $categories_check_query = $db->bindVars ( $categories_check_query, ':categoriesID', $current_category_id, 'integer' );
  $categories_check = $db->Execute ( $categories_check_query );
  if ($categories_check->fields['total'] == 0) {
    $categoryError = true;
  }

  $categories_products_query = "SELECT count(*) AS total
                                FROM   " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                WHERE   categories_id = :categoriesID";

  $categories_products_query = $db->bindVars ( $categories_products_query, ':categoriesID', $current_category_id, 'integer' );
  $categories_products = $db->Execute ( $categories_products_query );

  if ($categories_products->fields['total'] > 0)
  {
    $category_depth = 'products'; // display products
  } else
  {
    $category_parent_query = "SELECT count(*) AS total
                              FROM   " . TABLE_CATEGORIES . "
                              WHERE  parent_id = :categoriesID";

    $category_parent_query = $db->bindVars ( $category_parent_query, ':categoriesID', $current_category_id, 'integer' );
    $category_parent = $db->Execute ( $category_parent_query );

    if ($category_parent->fields['total'] > 0)
    {
      $category_depth = 'nested'; // navigate through the categories
    } else
    {
      $category_depth = 'products'; // category has no products, but display the 'no products' message
      $categoryError = true;
    }
  }
}
$define_page = zen_get_file_directory ( DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_MAIN_PAGE, 'false' );
require (DIR_WS_MODULES . zen_get_module_directory ( 'require_languages.php' ));

if (true)
{
  $modelFactory = new ZenCart\Model\ModelFactory($db, $capsule);
  $qb = new ZenCart\QueryBuilder\QueryBuilder($db);
  $box = new ZenCart\ListingQueryAndOutput\definitions\ProductsPage($zcRequest, $modelFactory);
  $paginator = new ZenCart\Paginator\Paginator($zcRequest);
  $builder = new ZenCart\QueryBuilder\PaginatorBuilder($zcRequest,$box->getListingQuery(), $paginator);
  $box->buildResults($qb, $db, new ZenCart\QueryBuilder\DerivedItemManager, $builder->getPaginator());
  $tplVars['listingBox'] = $box->getTplVars();

  if ($category_depth == 'products' && $box->getTotalItemCount () == 0) $robotsNoIndex = true;
  if (SKIP_SINGLE_PRODUCT_CATEGORIES == 'True' and (! isset ( $_GET['filter_id'] ) and ! isset ( $_GET['alpha_filter'] ))) {
    // If there is only one item in the list, and the total items available including pagination is still only 1, redirect directly to the product page

    if ($category_depth == 'products' && $box->getFormattedItemsCount () == 1 && $box->getTotalItemCount() < 2)  {
        $productsId = $tplVars['listingBox']['paginator']['resultList'][0]['products_id'];
        $productsCpath = $zcRequest->readGet('cPath', '');
        zen_redirect ( zen_href_link ( zen_get_info_page ( $productsId ), ($cPath ? 'cPath=' . $productsCpath . '&' : '') . 'products_id=' . $productsId ) );
    }
  }
}
// This should be last line of the script:
$zco_notifier->notify ( 'NOTIFY_HEADER_END_INDEX' );
