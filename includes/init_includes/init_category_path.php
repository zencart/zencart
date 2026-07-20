<?php
/**
 * pre-calculate the category path
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Nov 03 Modified in v1.5.8a $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$show_welcome = false;
if (!zen_page_uses_catalog_breadcrumb_lookups($current_page)) {
    // Only pages recognized as legitimately using catalog-filter parameters
    // derive $cPath from cPath/products_id GET params.
    // Other pages related to accounts and checkout ignore them entirely,
    // including a directly-supplied cPath, to avoid wasted category-lookup queries
    // when bots probe arbitrary pages with spoofed query strings.
    $cPath = '';
} elseif (isset($_GET['cPath'])) {
    $cPath = $_GET['cPath'];
} elseif (isset($_GET['products_id']) && !zen_check_url_get_terms()) {
    $cPath = zen_get_product_path($_GET['products_id']);
} else {
    if ($current_page == 'index' && SHOW_CATEGORIES_ALWAYS == '1' && !zen_check_url_get_terms()) {
        $show_welcome = true;
        $cPath = (defined('CATEGORIES_START_MAIN') ? CATEGORIES_START_MAIN : '');
    } else {
        $show_welcome = false;
        $cPath = '';
    }
}
if (zen_not_null($cPath)) {
    $cPath_array = zen_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(count($cPath_array) - 1)];
} else {
    $current_category_id = TOPMOST_CATEGORY_PARENT_ID;
    $cPath_array = [];
}

// determine whether the current page is the home page or a product listing
//$this_is_home_page = ($current_page=='index' && ((int)$cPath == 0 || $show_welcome == true));
$this_is_home_page = ($current_page == 'index'
    && (!isset($_GET['cPath']) || $_GET['cPath'] == '')
    && (!isset($_GET['manufacturers_id']) || $_GET['manufacturers_id'] == '')
    && (!isset($_GET['typefilter']) || $_GET['typefilter'] == '')
);
