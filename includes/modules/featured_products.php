<?php

/**
 * featured_products module - prepares content for display
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
$display_limit = '';

if ((($manufacturers_id > 0 && empty($_GET['filter_id'])) || !empty($_GET['music_genre_id']) || !empty($_GET['record_company_id'])) || empty($new_products_category_id)) {
    $sql = "SELECT p.products_id, p.products_image, pd.products_name, p.master_categories_id
            FROM " . TABLE_PRODUCTS . " p
            LEFT JOIN " . TABLE_FEATURED . " f ON p.products_id = f.products_id
            LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id
            AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
            WHERE p.products_status = 1
            AND f.status = 1";
} else {
    // get all products and cPaths in this subcat tree
    $productsInCategory = zen_get_categories_products_list((($manufacturers_id > 0 && !empty($_GET['filter_id'])) ? zen_get_generated_category_path_rev($_GET['filter_id']) : $cPath), false, true, 0, $display_limit);

    if (is_array($productsInCategory) && count($productsInCategory) > 0) {
        // build products-list string to insert into SQL query
        foreach ($productsInCategory as $key => $value) {
            $list_of_products .= $key . ', ';
        }
        $list_of_products = substr($list_of_products, 0, -2); // remove trailing comma
        $sql = "SELECT p.products_id, p.products_image, pd.products_name, p.master_categories_id
                FROM " . TABLE_PRODUCTS . " p
                LEFT JOIN " . TABLE_FEATURED . " f ON p.products_id = f.products_id
                LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id
                  AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                WHERE p.products_status = 1
                AND f.status = 1
                AND p.products_id IN (" . $list_of_products . ")";
    }
}
if ($sql !== '') {
    $featured_products = $db->ExecuteRandomMulti($sql, MAX_DISPLAY_SEARCH_RESULTS_FEATURED);
}

$row = 0;
$col = 0;
$list_box_contents = [];
$title = '';

$num_products_count = ($sql === '') ? 0 : $featured_products->RecordCount();

// show only when 1 or more
if ($num_products_count > 0) {
    if ($num_products_count < SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS || SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS == 0) {
        $col_width = floor(100 / $num_products_count);
    } else {
        $col_width = floor(100 / SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS);
    }
    while (!$featured_products->EOF) {
        $product_info = new Product((int)$featured_products->fields['products_id']);
        $data = $product_info->getDataForLanguage();

        $products_price = zen_get_products_display_price($data['products_id']);
        if (!isset($productsInCategory[$data['products_id']])) {
            $productsInCategory[$data['products_id']] = zen_get_generated_category_path_rev($data['master_categories_id']);
        }

        $zco_notifier->notify('NOTIFY_MODULES_FEATURED_PRODUCTS_B4_LIST_BOX', [], $data, $products_price);

        $list_box_contents[$row][$col] = [
            'params' => 'class="centerBoxContentsFeatured centeredContent back"' . ' ' . 'style="width:' . $col_width . '%;"',
            'text' => (($data['products_image'] === '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == 0) ? ''
                    : '<a href="'
                        . zen_href_link(zen_get_info_page($data['products_id']), 'cPath=' . $productsInCategory[$data['products_id']] . '&products_id=' . $data['products_id']) . '">'
                        . zen_image(DIR_WS_IMAGES . $data['products_image'], $data['products_name'], IMAGE_FEATURED_PRODUCTS_LISTING_WIDTH, IMAGE_FEATURED_PRODUCTS_LISTING_HEIGHT)
                    . '</a><br>')
                . '<a href="' . zen_href_link(zen_get_info_page($data['products_id']), 'cPath=' . $productsInCategory[$data['products_id']] . '&products_id=' . $data['products_id']) . '">' . $data['products_name']
                . '</a><br>' . $products_price,
        ];

        $col++;
        if ($col > (SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS - 1)) {
            $col = 0;
            $row++;
        }
        $featured_products->MoveNextRandom();
    }

    if ($featured_products->RecordCount() > 0) {
        if (!empty($current_category_id)) {
            $category_title = zen_get_category_name((int)$current_category_id);
            $title = '<h2 class="centerBoxHeading">' . TABLE_HEADING_FEATURED_PRODUCTS . ($category_title !== '' ? ' - ' . $category_title : '') . '</h2>';
        } else {
            $title = '<h2 class="centerBoxHeading">' . TABLE_HEADING_FEATURED_PRODUCTS . '</h2>';
        }
        $zc_show_featured = true;
    }
}

