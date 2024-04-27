<?php
/**
 * products_new header_php.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 31 Modified in v2.0.0-beta1 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_PRODUCTS_NEW');

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
foreach ($define_list as $key => $value) {
    if ((int)$value > 0) {
        $column_list[] = $key;
    }
}

// display limits
$display_limit = zen_get_new_date_range();
//MAX_DISPLAY_PRODUCTS_NEW

$select_column_list = " pd.products_name, p.products_image, p.products_date_added, m.manufacturers_name, p.products_model, p.products_quantity, p.products_weight,";
$sql_joins = '';
$and = $display_limit . ' '; // has a trailing space, not an empty string, to prevent clash with fallback category filter

// display sort order dropdown
$disp_order_default = PRODUCT_NEW_LIST_SORT_DEFAULT;

// set the product filters according to selected product type
$typefilter = $_GET['typefilter'] ?? 'default';
require(zen_get_index_filters_directory($typefilter . '_filter.php'));


// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_PRODUCTS_NEW', null);
