<?php

/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 11 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$copy_attributes_delete_first = '0';
$copy_attributes_duplicates_skipped = '0';
$copy_attributes_duplicates_overwrite = '0';
$copy_attributes_include_downloads = '1';
$copy_attributes_include_filename = '1';

$heading = array();
$heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_COPY_TO . '</h4>');
if (empty($pInfo->products_id)) {
  if (is_object($pInfo)) {
    $pInfo->products_id = $pID;
  } else {
    $pInfo = new objectInfo(array('products_id' => $pID));
  }
}

$contents = array('form' => zen_draw_form('copy_product', FILENAME_CATEGORY_PRODUCT_LISTING, 'action=copy_product_confirm&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'class="form-horizontal"') . zen_draw_hidden_field('products_id', $pInfo->products_id));
$contents[] = array('text' => TEXT_INFO_CURRENT_PRODUCT . '<br><strong>' . $pInfo->products_model . ' - ' . $pInfo->products_name . ' [ID#' . $pInfo->products_id . ']</strong>');
$contents[] = array('text' => TEXT_INFO_CURRENT_CATEGORIES . '<br><strong>' . zen_output_generated_category_path($pInfo->products_id, 'product') . '</strong>');
$contents[] = array('text' => zen_draw_label(TEXT_CATEGORIES, 'categories_id', 'class="control-label"') . zen_draw_pull_down_menu('categories_id', zen_get_category_tree(), $current_category_id, 'class="form-control" id="categories_id"'));

$contents[] = array(
    'text' => '<h5>' . TEXT_HOW_TO_COPY . '</h5>' .
        '<div class="radio h6"><label>' . zen_draw_radio_field('copy_as', 'link', true) . TEXT_COPY_AS_LINK . '</label></div>' .
        zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '', '1', 'style="width:100%"') .
        '<div class="radio h6"><label>' . zen_draw_radio_field('copy_as', 'duplicate') . TEXT_COPY_AS_DUPLICATE . '</label></div>'
);
$contents[] = array('text' => '<div class="checkbox"><label>'.zen_draw_checkbox_field('copy_media',true, true) . TEXT_COPY_MEDIA_MANAGER . '</label></div>');

// only ask about attributes if defined
if (zen_has_product_attributes($pInfo->products_id, 'false')) {
    $contents[] = array(
        'text' => '<h6>' . TEXT_COPY_ATTRIBUTES . '</h6>' .
            '<div class="radio"><label>' . zen_draw_radio_field('copy_attributes', 'copy_attributes_yes', true) . TEXT_YES . '</label></div>' .
            '<div class="radio"><label>' . zen_draw_radio_field('copy_attributes', 'copy_attributes_no') . TEXT_NO . '</label></div>'
    );

    $zco_notifier->notify('NOTIFY_ADMIN_PRODUCT_COPY_TO_ATTRIBUTES', $pInfo, $contents);
}
//are any metatags defined
$metatags_defined = false;
for ($i = 0, $n = count($languages); $i < $n; $i++) {
    if (zen_get_metatags_description($pInfo->products_id,
            $languages[$i]['id']) . zen_get_metatags_keywords($pInfo->products_id,
            $languages[$i]['id']) . zen_get_metatags_title($pInfo->products_id, $languages[$i]['id']) != '') {
        $metatags_defined = true;
    }
}
//only ask about metatags if defined
if ($metatags_defined) {
    $contents[] = array(
        'text' => '<h6>' . TEXT_COPY_METATAGS . '</h6>' .
            '<div class="radio"><label>' . zen_draw_radio_field('copy_metatags', 'copy_metatags_yes', true) . TEXT_YES . '</label></div>' .
            '<div class="radio"><label>' . zen_draw_radio_field('copy_metatags', 'copy_metatags_no') . TEXT_NO . '</label></div>'
    );
}
//only ask about linked categories if defined
if (zen_get_product_is_linked($pInfo->products_id) == 'true') {
    $contents[] = array(
        'text' => '<h6>' . TEXT_COPY_LINKED_CATEGORIES . '</h6>' .
            '<div class="radio"><label>' . zen_draw_radio_field('copy_linked_categories', 'copy_linked_categories_yes', true) . TEXT_YES . '</label></div>' .
            '<div class="radio"><label>' . zen_draw_radio_field('copy_linked_categories', 'copy_linked_categories_no') . TEXT_NO . '</label></div>'
    );
}
// only ask if product has qty discounts defined
if (zen_has_product_discounts($pInfo->products_id) == 'true') {
    //$contents[] = array('text' => TEXT_COPY_DISCOUNTS_ONLY);
    $contents[] = array(
        'text' => '<h6>' . TEXT_COPY_DISCOUNTS . '</h6>' .
            '<div class="radio"><label>' . zen_draw_radio_field('copy_discounts', 'copy_discounts_yes', true) . TEXT_YES . '</label></div>' .
            '<div class="radio"><label>' . zen_draw_radio_field('copy_discounts', 'copy_discounts_no') . TEXT_NO . '</label></div>'
    );
}
$contents[] = array('text' => '<label>' . zen_draw_checkbox_field('edit_duplicate', '1', true) . TEXT_COPY_EDIT_DUPLICATE . '</label>');
$contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '', '3', 'style="width:100%"'));
$contents[] = array('align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_COPY . '</button> 
<a href="' . zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
);

$contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '', '1', 'style="width:100%"'));
$contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $pInfo->products_id . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . BUTTON_PRODUCTS_TO_CATEGORIES . '</a>');
