<?php

/**
 * featured_categories module - prepares content for display
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 24 Modified in v2.1.0-alpha1 $
 * based on featured_products
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// initialize vars
$categories_categories_id_list = [];
$sql = '';
$display_limit = '';

    $sql = "SELECT p.categories_id, p.categories_image, pd.categories_name
            FROM " . TABLE_CATEGORIES . " p
            LEFT JOIN " . TABLE_FEATURED_CATEGORIES . " f ON p.categories_id = f.categories_id
            LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " pd ON p.categories_id = pd.categories_id
            AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
            WHERE p.categories_status = 1
            AND f.status = 1";
    $featured_categories = $db->ExecuteRandomMulti($sql, MAX_DISPLAY_SEARCH_RESULTS_FEATURED);

$row = 0;
$col = 0;
$list_box_contents = [];
$title = '';

$num_categories_count = $featured_categories->RecordCount();

// show only when 1 or more
if ($num_categories_count > 0) {
    if ($num_categories_count < SHOW_PRODUCT_INFO_COLUMNS_FEATURED_CATEGORIES || SHOW_PRODUCT_INFO_COLUMNS_FEATURED_CATEGORIES == 0) {
        $col_width = floor(100 / $num_categories_count);
    } else {
        $col_width = floor(100 / SHOW_PRODUCT_INFO_COLUMNS_FEATURED_CATEGORIES);
    }
    while (!$featured_categories->EOF) {
        $category_info = new Category((int)$featured_categories->fields['categories_id']);
        $data = $category_info->getDataForLanguage();

        $list_box_contents[$row][$col] = [
            'params' => 'class="centerBoxContentsFeaturedCategories centeredContent back"' . ' ' . 'style="width:' . $col_width . '%;"',
            'text' => (($data['categories_image'] === '' && CATEGORY_IMAGE_NO_IMAGE_STATUS == 0) ? ''
                    : '<a href="' . zen_href_link(FILENAME_DEFAULT, 'cPath='.  zen_get_generated_category_path_rev($data['categories_id'])). '">'
                        . zen_image(DIR_WS_IMAGES . $data['categories_image'], $data['categories_name'], IMAGE_FEATURED_CATEGORY_LISTING_WIDTH, IMAGE_FEATURED_CATEGORY_LISTING_HEIGHT)
                    . '</a><br>')
                . '<a href="' . zen_href_link(FILENAME_DEFAULT, 'cPath='.  zen_get_generated_category_path_rev($data['categories_id'])). '">' . $data['categories_name']
                . '</a><br>',
        ];

        $col++;
        if ($col > (SHOW_PRODUCT_INFO_COLUMNS_FEATURED_CATEGORIES - 1)) {
            $col = 0;
            $row++;
        }
        $featured_categories->MoveNextRandom();
    }

    if ($featured_categories->RecordCount() > 0) {
        if (!empty($current_category_id)) {
            $category_title = zen_get_category_name((int)$current_category_id);
            $title = '<h2 class="centerBoxHeading">' . TABLE_HEADING_FEATURED_CATEGORIES . ($category_title !== '' ? ' - ' . $category_title : '') . '</h2>';
        } else {
            $title = '<h2 class="centerBoxHeading">' . TABLE_HEADING_FEATURED_CATEGORIES . '</h2>';
        }
        $zc_show_featured = true;
    }
}

