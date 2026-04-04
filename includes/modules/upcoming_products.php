<?php

/**
 * upcoming_products module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 24 Modified in v2.1.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// initialize vars
$categories_products_id_list = [];
$list_of_products = '';
$sql = '';

$display_limit = zen_get_upcoming_date_range();

$limit_clause = "  ORDER BY " . (EXPECTED_PRODUCTS_FIELD == 'date_expected' ? 'date_expected' : 'products_name') . " " . (EXPECTED_PRODUCTS_SORT == 'asc' ? 'ASC' : 'DESC') . "
                   LIMIT " . (int)MAX_DISPLAY_UPCOMING_PRODUCTS;

if ((($manufacturers_id > 0 && empty($_GET['filter_id'])) || !empty($_GET['music_genre_id']) || !empty($_GET['record_company_id'])) || empty($new_products_category_id)) {
    $sql = "SELECT p.products_id, pd.products_name, products_date_available AS date_expected, p.master_categories_id
            FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
            WHERE p.products_id = pd.products_id
            AND p.products_status = 1
            AND pd.language_id = " . (int)$_SESSION['languages_id'] .
            $display_limit .
            $limit_clause;
} else {
    // get all products and cPaths in this subcat tree
    $productsInCategory = zen_get_categories_products_list((($manufacturers_id > 0 && !empty($_GET['filter_id'])) ? zen_get_generated_category_path_rev($_GET['filter_id']) : $cPath), false, true, 0, $display_limit);

    if (is_array($productsInCategory) && count($productsInCategory) > 0) {
        // build products-list string to insert into SQL query
        foreach ($productsInCategory as $key => $value) {
            $list_of_products .= $key . ', ';
        }
        $list_of_products = substr($list_of_products, 0, -2); // remove trailing comma

        $sql = "SELECT p.products_id, pd.products_name, products_date_available AS date_expected, p.master_categories_id
                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_id = pd.products_id
                AND p.products_id IN (" . $list_of_products . ")
                AND p.products_status = 1
                AND pd.language_id = " . (int)$_SESSION['languages_id'] .
                $display_limit .
                $limit_clause;
    }
}

if ($sql !== '') {
    $expected = $db->Execute($sql);
}
if ($sql !== '' && $expected->RecordCount() > 0) {
    foreach ($expected as $expect) {
        if (!isset($productsInCategory[$expect['products_id']])) {
            $productsInCategory[$expect['products_id']] = zen_get_generated_category_path_rev($expect['master_categories_id']);
        }
        $expectedItems[] = array_merge($expect, (new Product((int)$expect['products_id']))->getDataForLanguage());
    }
    require $template->get_template_dir('tpl_modules_upcoming_products.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/' . 'tpl_modules_upcoming_products.php';
}
