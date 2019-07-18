<?php

/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jun 25 Modified in v1.5.6c $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$copy_attributes_delete_first = '0';
$copy_attributes_duplicates_skipped = '0';
$copy_attributes_duplicates_overwrite = '0';
$copy_attributes_include_downloads = '1';
$copy_attributes_include_filename = '1';

$heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_COPY_TO . '</h4>');
if (empty($pInfo->products_id)) {
  if (!is_object($pInfo)) {
    $pInfo = new objectInfo(array('products_id' => $pID)); 
  } else {
    $pInfo->products_id = $pID;
  }
}

$contents = array('form' => zen_draw_form('copy_product', FILENAME_CATEGORY_PRODUCT_LISTING, 'action=copy_product_confirm&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'class="form-horizontal"') . zen_draw_hidden_field('products_id', $pInfo->products_id));
$contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
$contents[] = array('text' => TEXT_INFO_CURRENT_PRODUCT . '<br><strong>' . $pInfo->products_name . ' ID#' . $pInfo->products_id . '</strong>');
$contents[] = array('text' => TEXT_INFO_CURRENT_CATEGORIES . '<br /><strong>' . zen_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
$contents[] = array('text' => zen_draw_label(TEXT_CATEGORIES, 'categories_id', 'class="control-label"') . zen_draw_pull_down_menu('categories_id', zen_get_category_tree(), $current_category_id, 'class="form-control"'));
$contents[] = array('text' => zen_draw_label(TEXT_HOW_TO_COPY, 'copy_as', 'class="control-label"') . '<div class="radio"><label>' . zen_draw_radio_field('copy_as', 'link', true) . TEXT_COPY_AS_LINK . '</label></div><div class="radio"><label>' . zen_draw_radio_field('copy_as', 'duplicate') . TEXT_COPY_AS_DUPLICATE . '</label></div>');
$contents[] = array('text' => '<div class="checkbox"><label>'.zen_draw_checkbox_field('copy_media',true, true) . TEXT_COPY_MEDIA_MANAGER . '</label></div>');
$contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));

// only ask about attributes if they exist
if (zen_has_product_attributes($pInfo->products_id, 'false')) {
  $contents[] = array('text' => TEXT_COPY_ATTRIBUTES_ONLY);
  $contents[] = array('text' => zen_draw_label(TEXT_COPY_ATTRIBUTES, 'copy_attributes', 'class="control-label"') . '<div class="radio"><label>' . zen_draw_radio_field('copy_attributes', 'copy_attributes_yes', true) . TEXT_COPY_ATTRIBUTES_YES . '</label></div><div class="radio"><label>' . zen_draw_radio_field('copy_attributes', 'copy_attributes_no') . TEXT_COPY_ATTRIBUTES_NO . '</label></div>');
  // future          $contents[] = array('align' => 'center', 'text' => '<br />' . ATTRIBUTES_NAMES_HELPER . '<br />' . zen_draw_separator('pixel_trans.gif', '1', '10'));
  $contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));
}

// only ask if product has qty discounts
if (zen_has_product_discounts($pInfo->products_id) == 'true') {
  $contents[] = array('text' => TEXT_COPY_DISCOUNTS_ONLY);
  $contents[] = array('text' => zen_draw_label(TEXT_COPY_DISCOUNTS, 'copy_discounts', 'class="control-label"') . '<div class="radio"><label>' . zen_draw_radio_field('copy_discounts', 'copy_discounts_yes', true) . TEXT_COPY_DISCOUNTS_YES . '</label></div><div class="radio"><label>' . zen_draw_radio_field('copy_discounts', 'copy_discounts_no') . TEXT_COPY_DISCOUNTS_NO . '</label></div>');
  $contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));
}

$contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_COPY . '</button> <a href="' . zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');

$contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'));
$contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $pInfo->products_id . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . BUTTON_PRODUCTS_TO_CATEGORIES . '</a>');
