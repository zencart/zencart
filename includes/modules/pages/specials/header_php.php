<?php

/**
 * Specials
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 31 Modified in v2.0.0-beta1 $
 */
$zco_notifier->notify('NOTIFY_HEADER_START_SPECIALS');

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

// load extra language strings used by product_listing module
$languageLoader->setCurrentPage('index');
$languageLoader->loadLanguageForView();

$breadcrumb->add(NAVBAR_TITLE);

// create column list for product listing
$define_list = [
    'PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
    'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
    'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
    'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
    'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
    'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
    'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
//    'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW,
];
asort($define_list);
$column_list = [];
foreach ($define_list as $key => $value)
{
    if ((int)$value > 0) {
        $column_list[] = $key;
    }
}
$select_column_list = "pd.products_name, p.products_image, p.products_date_added, m.manufacturers_name, p.products_model, p.products_quantity, p.products_weight,";
$sql_joins = '';
$and = " AND s.status = 1 ";


// OPTIONALLY INCLUDE SALE ITEMS IN SPECIALS LISTING
if (defined('INCLUDE_SALEMAKER_IN_SPECIALS') && INCLUDE_SALEMAKER_IN_SPECIALS === 'True') {
    $sale_categories = $db->Execute("SELECT sale_categories_all FROM " . TABLE_SALEMAKER_SALES . " WHERE sale_status = 1");
    if (!$sale_categories->EOF) {
        $sale_categories_all = '';
        foreach ($sale_categories as $row) {
            $sale_categories_all .= ',' . trim($row['sale_categories_all'], ','); // remove trailing comma
        }
        $sale_categories_all = trim($sale_categories_all, ','); // remove preceeding comma

        // this now overrides the AND clause
        $and = "AND ( (s.status = 1 AND p.products_id = s.products_id) OR (p.master_categories_id IN ($sale_categories_all)) )";
    }
}

// filter by category
if (!empty($_GET['sale_category'])) {
    $_GET['sale_category'] = (int)$_GET['sale_category'];
    // find subcategories
    $subcategories_array = [];
    zen_get_subcategories($subcategories_array, $_GET['sale_category']);
    $subcategories_array[] = $_GET['sale_category'];
    $subcategories_string = trim(implode(',', $subcategories_array), ',');
    // append to $and
    $and .= " AND p.master_categories_id IN (" . $subcategories_string . ") ";
}

// display sort order dropdown
$disp_order_default = 8;
$default_sort_order = ' ORDER BY s.specials_date_added DESC ';

// set the product filters according to selected product type
$typefilter = $_GET['typefilter'] ?? 'default';
require(zen_get_index_filters_directory($typefilter . '_filter.php'));


// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_SPECIALS', null);
