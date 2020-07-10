<?php
/**
 * index header_php.php
 *
 * @package page
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Thu Oct 4 13:29:04 2018 -0400 Modified in v1.5.6 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_INDEX');

// the following cPath references come from application_top/initSystem
$category_depth = 'top';
if (isset($cPath) && zen_not_null($cPath)) {
  $categories_products_query = "SELECT count(*) AS total
                                FROM   " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                WHERE   categories_id = :categoriesID";

  $categories_products_query = $db->bindVars($categories_products_query, ':categoriesID', $current_category_id, 'integer');
  $categories_products = $db->Execute($categories_products_query);

  if ($categories_products->fields['total'] > 0) {
    $category_depth = 'products'; // display products
  } else {
    $category_parent_query = "SELECT count(*) AS total
                              FROM   " . TABLE_CATEGORIES . "
                              WHERE  parent_id = :categoriesID";

    $category_parent_query = $db->bindVars($category_parent_query, ':categoriesID', $current_category_id, 'integer');
    $category_parent = $db->Execute($category_parent_query);

    if ($category_parent->fields['total'] > 0) {
      $category_depth = 'nested'; // navigate through the categories
    } else {
      $category_depth = 'products'; // category has no products, but display the 'no products' message
    }
  }
}
// include template specific file name defines
$define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_MAIN_PAGE, 'false');
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

// set the product filters according to selected product type
$typefilter = 'default';
if (isset($_GET['typefilter'])) $typefilter = $_GET['typefilter'];
require(zen_get_index_filters_directory($typefilter . '_filter.php'));

// query the database based on the selected filters
$listing = $db->Execute($listing_sql);

// UNCOMMENT THE FOLLOWING LINE if you want to skip Search Engine indexing if the category has no products:
//if ($category_depth == 'products' && $listing->RecordCount() == 0) $robotsNoIndex = true;

// if only one product in this category, go directly to the product page, instead of displaying a link to just one item:
// if filter_id exists the 1 product redirect is ignored
if (SKIP_SINGLE_PRODUCT_CATEGORIES=='True' and (!isset($_GET['filter_id']) and !isset($_GET['alpha_filter_id']))) {
  if ($listing->RecordCount() == 1) {
    zen_redirect(zen_href_link(zen_get_info_page($listing->fields['products_id']), ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $listing->fields['products_id']));
  }
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_INDEX');
