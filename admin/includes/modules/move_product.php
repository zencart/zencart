<?php

/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 Mar 15 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$product_categories = zen_generate_category_path($pInfo->products_id, 'product');
if (!isset($category_path)) $category_path = '';
  for ($i = 0, $n = count($product_categories); $i < $n; $i++) { //make a text path category > subcategory...for each category in which the product resides
    $category_path = '';
    for ($j = 0, $k = count($product_categories[$i]); $j < $k; $j++) {
        $category_path .= $product_categories[$i][$j]['text'];
        if ($j+1 < $k)  $category_path .= '&nbsp;&gt;&nbsp;';
    }
    if (count($product_categories) > 0 && zen_get_parent_category_id($pInfo->products_id) == $product_categories[$i][count($product_categories[$i]) - 1]['id']) {
        $product_master_category_string = $category_path;
    }
    if (count($product_categories) > 0  && $current_category_id == $product_categories[$i][count($product_categories[$i]) - 1]['id']) {
        $product_current_category_string = $category_path;
    }
}

$heading = [];
$heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_MOVE_PRODUCT . '</h4>'];
$contents = ['form' => zen_draw_form('products', FILENAME_CATEGORY_PRODUCT_LISTING, 'action=move_product_confirm&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'class="form-horizontal"') . zen_draw_hidden_field('products_id', $pInfo->products_id)];
$contents[] = ['text' => '<h3>' . 'ID#' . $pInfo->products_id . ': ' . $pInfo->products_name . '</h3>'];
$contents[] = ['text' => TEXT_MOVE_PRODUCTS_INTRO];
$contents[] = ['text' => zen_draw_label(sprintf(TEXT_MOVE_PRODUCT, $pInfo->products_id, $pInfo->products_name, $current_category_id, $product_current_category_string), 'move_to_category_id', 'style="font-weight:normal;font-size:larger;"') . zen_draw_pull_down_menu('move_to_category_id', zen_get_category_tree(), $current_category_id, 'id="move_to_category_id" class="form-control"')];
$contents[] = ['align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_MOVE . '</button> <a href="' . zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
$contents[] = ['text' => TEXT_INFO_CURRENT_CATEGORIES];
$contents[] = ['text' => '<span class="text-danger"><strong>' . TEXT_MASTER_CATEGORIES_ID . ' <br>ID#' . zen_get_parent_category_id($pInfo->products_id) . ' ' . $product_master_category_string . '</strong></span>'];
if (count($product_categories) > 1) {
   $contents[] = ['text' => '<strong>' . zen_output_generated_category_path($pInfo->products_id, 'product') . '</strong>'];
}
$contents[] = ['text' => '<hr>'];
$contents[] = ['align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $pInfo->products_id . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . BUTTON_PRODUCTS_TO_CATEGORIES . '</a>'];

