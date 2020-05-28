<?php

/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 Apr 20 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$product_categories_string = '';
$product_categories = zen_generate_category_path($pInfo->products_id, 'product');
if (!isset($category_path)) {
    $category_path = '';
}
$preselect_master_category = true; // set to false to prevent accidental deletion
for ($i = 0, $n = count($product_categories); $i < $n; $i++) {
    $category_path = '';
    for ($j = 0, $k = count($product_categories[$i]); $j < $k; $j++) {
        $category_path .= $product_categories[$i][$j]['text'];
        if ($j + 1 < $k) {
            $category_path .= '&nbsp;&gt;&nbsp;';
        }
    }
    if (count($product_categories) >= 1 && (int)zen_get_parent_category_id($pInfo->products_id) === (int)$product_categories[$i][count($product_categories[$i]) - 1]['id']) {
        $product_categories_string .= '<div class="checkbox text-danger"><label><strong>' . zen_draw_checkbox_field('product_categories[]', $product_categories[$i][count($product_categories[$i]) - 1]['id'], $preselect_master_category) . $category_path . '</strong></label></div>';
        $product_master_category_string = $category_path;
    } else {
        $product_categories_string .= '<div class="checkbox"><label>' . zen_draw_checkbox_field('product_categories[]', $product_categories[$i][count($product_categories[$i]) - 1]['id'], true) . $category_path . '</label></div>';
    }
}

$heading = [];
$heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</h4>'];
$contents = ['form' => zen_draw_form('delete_products', FILENAME_CATEGORY_PRODUCT_LISTING, 'action=delete_product_confirm&product_type=' . $product_type . '&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'class="form-horizontal"') . zen_draw_hidden_field('products_id', $pInfo->products_id)];
$contents[] = ['text' => '<h3>ID#' . $pInfo->products_id . ': ' . $pInfo->products_name . '</h3>'];
$contents[] = ['text' => '<span class="text-danger"><strong>' . TEXT_MASTER_CATEGORIES_ID . ' ID#' . zen_get_parent_category_id($pInfo->products_id) . ' ' . $product_master_category_string . '</strong></span>'];
if (count($product_categories) > 1) {
    $contents[] = ['text' => sprintf(TEXT_DELETE_PRODUCT_INTRO, $pInfo->products_id, $pInfo->products_name, zen_get_parent_category_id($pInfo->products_id), $product_master_category_string)];
}
$contents[] = ['text' => $product_categories_string];
$contents[] = ['align' => 'center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
$contents[] = ['text' => zen_draw_separator('pixel_black.gif', '100%', '1')];
$contents[] = ['align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $pInfo->products_id . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . BUTTON_PRODUCTS_TO_CATEGORIES . '</a>'];
