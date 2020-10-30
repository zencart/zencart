<?php
/**
 * index header_php.php
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2020 Aug 06 Modified in v1.5.7a $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_INDEX');

// the following cPath references come from application_top/initSystem
// -----
// If $cPath exists, implying that the $current_category_id has been set, determine whether
// that category exists and is enabled.  If so, determine whether the valid category has at
// least one product or one sub-category.
//
$category_depth = 'top';
$current_category_not_found = false;
$current_category_is_disabled = false;
$current_category_has_products = false;
$current_category_has_subcats = false;
if (isset($cPath) && zen_not_null($cPath)) {
    $category_status_query = 
        "SELECT categories_status
           FROM " . TABLE_CATEGORIES . "
          WHERE categories_id = :currentCategoryId
          LIMIT 1";
    $category_status_query = $db->bindVars($category_status_query, ':currentCategoryId', $current_category_id, 'integer');
    $category_status = $db->Execute($category_status_query);
    if ($category_status->EOF) {
        $current_category_not_found = true;
    } elseif ($category_status->fields['categories_status'] == '0') {
        $current_category_is_disabled = true;
    }
    $category_products_query = 
        "SELECT products_id
           FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
          WHERE categories_id = :currentCategoryId
          LIMIT 1";
    $category_products_query = $db->bindVars($category_products_query, ':currentCategoryId', $current_category_id, 'integer');
    $category_products = $db->Execute($category_products_query);
    if (!$category_products->EOF) {
        $current_category_has_products = true;
    } else {
        $category_parent_query =
            "SELECT parent_id
               FROM " . TABLE_CATEGORIES . "
              WHERE parent_id = :currentCategoryId
              LIMIT 1";
        $category_parent_query = $db->bindVars($category_parent_query, ':currentCategoryId', $current_category_id, 'integer');
        $category_parent = $db->Execute($category_parent_query);
        $current_category_has_subcats = !$category_parent->EOF;
    }
    
    // -----
    // Give an observer the chance to override the default handling for the category.
    //
    $category_redirect_handled = false;
    $zco_notifier->notify(
        'NOTIFY_INDEX_CATEGORY_STATUS_CHECK', 
        array('cPath' => $cPath, 'current_category_id' => $current_category_id),
        $category_redirect_handled,
        $current_category_not_found,
        $current_category_is_disabled,
        $current_category_has_products,
        $current_category_has_subcats,
        $category_depth
    );
    
    // -----
    // If an observer hasn't overridden the default handling for the category's display:
    //
    // 1. Make sure that the current category-id is found for the store.  If not:
    //    - Remove the 'cPath' parameter for follow-on processing by other modules.
    //    - Reset the breadcrumbs.
    //    - Set the flag to cause noindex/nofollow to be included for the page
    //    - Issue a 404 (Not found)
    // 2. Otherwise, make sure that the current category-id is enabled.  If not:
    //    - Set the category_depth to indicate that a products' listing is to be displayed; it'll indicate no products found.
    //    - Set the flag to cause noindex/nofollow to be included for the page
    //    - Issue a 410 (Gone).
    //
    //    Note: Stores that wish to to operate as in Zen Cart versions prior to v157a, where a disabled 
    //    category is still displayed can comment the above section 'out'.
    //
    // 3. Otherwise, the category is present and not disabled. Determine the category 'depth' to be displayed.
    //    a. If the current category contains at least one product, display a products' listing.
    //    b. Otherwise, check to see if the current category has sub-categories.  If so,
    //       display a categories' listing; otherwise, display a products' listing, enabling the
    //      'No products in category' message to be displayed.
    //
    //      Note: While this final check appears to be redundant, it's maintaining backward compatibility
    //      of display for stores that have an 'invalid' mix of products and categories within a
    //      category.
    //
    if (!$category_redirect_handled) {
        if ($current_category_not_found) {
            unset($_GET['cPath']);
            $breadcrumb->reset();
            $robotsNoIndex = true;
            header('HTTP/1.1 404 Not Found');
//-bof-Comment the following four (4) lines out to display disabled categories
        } elseif ($current_category_is_disabled) {
            $category_depth = 'products';
            $robotsNoIndex = true;
            header('HTTP/1.1 410 Gone');
//-eof-Comment the above four (4) lines out to display disabled categories
        } elseif ($current_category_has_products) {
            $category_depth = 'products';
        } else {
            $category_depth = ($current_category_has_subcats) ? 'nested' : 'products';
        }
    }
}

// include template specific file name defines
$define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_MAIN_PAGE, 'false');
require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

// set the product filters according to selected product type
$typefilter = 'default';
if (isset($_GET['typefilter'])) {
    $typefilter = $_GET['typefilter'];
}
require zen_get_index_filters_directory($typefilter . '_filter.php');

// query the database based on the selected filters
$listing = $db->Execute($listing_sql);

// UNCOMMENT THE FOLLOWING LINE if you want to skip Search Engine indexing if the category has no products:
//if ($category_depth == 'products' && $listing->RecordCount() == 0) $robotsNoIndex = true;

// if only one product in this category, go directly to the product page, instead of displaying a link to just one item:
// if filter_id exists the 1 product redirect is ignored
if (SKIP_SINGLE_PRODUCT_CATEGORIES == 'True' && !isset($_GET['filter_id']) && !isset($_GET['alpha_filter_id'])) {
    if ($listing->RecordCount() == 1) {
        zen_redirect(zen_href_link(zen_get_info_page($listing->fields['products_id']), ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $listing->fields['products_id']));
    }
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_INDEX');
