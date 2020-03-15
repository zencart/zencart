<?php

/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All Wed Jan 17 12:01:19 2018 +0100 New in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$product_categories = zen_generate_category_path($pInfo->products_id, 'product');
if (!isset($category_path)) $category_path = '';
  for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
    $category_path = '';
    for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
        $category_path .= $product_categories[$i][$j]['text'];
        if ($j+1 < $k)  $category_path .= '&nbsp;&gt;&nbsp;';
    }
    if (sizeof($product_categories) > 0 && zen_get_parent_category_id($pInfo->products_id) == $product_categories[$i][sizeof($product_categories[$i]) - 1]['id']) {
        $product_master_category_string = $category_path;
    }
    if (sizeof($product_categories) > 0 && $current_category_id == $product_categories[$i][sizeof($product_categories[$i]) - 1]['id']) {
        $product_current_category_string = $category_path;
    }
}

$heading = array();
$heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_MOVE_PRODUCT . '</h4>');
$contents = array('form' => zen_draw_form('products', FILENAME_CATEGORY_PRODUCT_LISTING, 'action=move_product_confirm&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'class="form-horizontal"') . zen_draw_hidden_field('products_id', $pInfo->products_id));
$contents[] = array('text' => '<h3>' . 'ID#' . $pInfo->products_id . ': ' . $pInfo->products_name . '</h3>');
$contents[] = array('text' => TEXT_MOVE_PRODUCTS_INTRO);
$contents[] = array('text' => zen_draw_label(sprintf(TEXT_MOVE_PRODUCT, $pInfo->products_id, $pInfo->products_name, $product_current_category_string), 'move_to_category_id', 'style="font-weight:normal;font-size:larger;"') . zen_draw_pull_down_menu('move_to_category_id', zen_get_category_tree(), $current_category_id, 'id="move_to_category_id" class="form-control"'));
$contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_MOVE . '</button> <a href="' . zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
$contents[] = array('text' => TEXT_INFO_CURRENT_CATEGORIES);
$contents[] = array('text' => '<span class="text-danger"><strong>' . TEXT_MASTER_CATEGORIES_ID . ' ID#' . zen_get_parent_category_id($pInfo->products_id) . ' ' . $product_master_category_string . '</strong></span>');
$contents[] = array('text' => '<strong>' . zen_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
$contents[] = array('text' => zen_draw_separator('pixel_black.gif', '100%', '1'));
$contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $pInfo->products_id . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . BUTTON_PRODUCTS_TO_CATEGORIES . '</a>');

