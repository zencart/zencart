<?php
/**
 * index category_row.php
 *
 * Prepares the content for displaying a category's sub-category listing in grid format.
 * Once the data is prepared, it calls the standard tpl_list_box_content template for display.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Feb 24 Modified in v2.0.0-beta1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$num_categories = $categories->RecordCount();
if (empty($num_categories)) {
    return;
}

$rows = 0;
$columns = 0;
$list_box_contents = [];
$title = '';

$columns_per_row = defined('MAX_DISPLAY_CATEGORIES_PER_ROW') ? (int)MAX_DISPLAY_CATEGORIES_PER_ROW : 0;
if (empty($category_row_layout_style) || !in_array($category_row_layout_style, ['columns', 'fluid'])) {
    $category_row_layout_style = $columns_per_row > 0 ? 'columns' : 'fluid';
}

// if in fixed-columns mode, calculate column width
if ($category_row_layout_style === 'columns') {
    $calc_value = $columns_per_row;
    if ($num_categories < $columns_per_row || $columns_per_row === 0) {
        $calc_value = $num_categories;
    }
    $col_width = floor(100 / $calc_value) - 0.5;
}

foreach ($categories as $next_category) {
    $zco_notifier->notify('NOTIFY_CATEGORY_ROW_IMAGE', $next_category['categories_id'], $next_category['categories_image']);
    if (empty($next_category['categories_image'])) {
        $next_category['categories_image'] = 'pixel_trans.gif';
    }
    $cPath_new = zen_get_path($next_category['categories_id']);

    // strip out 0_ from top level cats
    $cPath_new = str_replace('=' . (int)TOPMOST_CATEGORY_PARENT_ID . '_', '=', $cPath_new);

    //    $categories->fields['products_name'] = zen_get_products_name($categories->fields['products_id']);



    // Set css classes for "row" wrapper, to allow for fluid grouping of cells based on viewport
    // these defaults are inspired by Bootstrap4, but can be customized to suit your own framework
    if ($category_row_layout_style === 'fluid') {
        $grid_cards_classes = $grid_category_cards_classes ?? 'row row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-3';
        if (!isset($grid_category_classes_matrix)) {
            // this array is intentionally in reverse order, with largest index first
            $grid_category_classes_matrix = [
                '12' => 'row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6',
                '10' => 'row row-cols-1 row-cols-md-2 row-cols-lg-4 row-cols-xl-5',
                '9' => 'row row-cols-1 row-cols-md-3 row-cols-lg-4 row-cols-xl-5',
                '8' => 'row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4',
                '6' => 'row row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-3',
            ];
        }

        // determine classes to use based on number of grid-columns used by "center" column
        if (isset($center_column_width)) {
            foreach ($grid_category_classes_matrix as $width => $classes) {
                if ($center_column_width >= $width) {
                    $grid_cards_classes = $classes;
                    break;
                }
            }
        }
        $list_box_contents[$rows]['params'] = 'class="' . $grid_cards_classes . ' text-center"';
    }

    $style = '';
    if ($category_row_layout_style === 'columns') {
        $style = ' style="width:' . $col_width . '%;"';
    }
    $grid_category_card_params = $grid_category_card_params ?? 'categoryListBoxContents centeredContent back gridlayout';
    $grid_category_wrap_classes = $grid_category_wrap_classes ?? '';
    $list_box_contents[$rows][] = [
        'params' => 'class="' . $grid_category_card_params . '"' . $style,
        'text' =>
            '<a href="' . zen_href_link(FILENAME_DEFAULT, $cPath_new) . '">' .
            zen_image(DIR_WS_IMAGES . $next_category['categories_image'], $next_category['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT, 'loading="lazy"') .
            '<br>' .
            $next_category['categories_name'] .
            '</a>',
        'wrap_with_classes' => $grid_category_wrap_classes,
        'card_type' => $category_row_layout_style,
    ];

    if ($category_row_layout_style === 'columns') {
        $columns++;
        if ($columns >= $columns_per_row) {
            $columns = 0;
            $rows++;
        }
    }
}
