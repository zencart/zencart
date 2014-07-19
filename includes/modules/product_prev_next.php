<?php
/**
 *  product_prev_next.php
 *
 * @package productTypes
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: product_prev_next.php 6912 2007-09-02 02:23:45Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// bof: previous next
if (PRODUCT_INFO_PREVIOUS_NEXT != 0) {

  // sort order
  switch(PRODUCT_INFO_PREVIOUS_NEXT_SORT) {
    case (0):
    $prev_next_order= ' order by LPAD(p.products_id,11,"0")';
    break;
    case (1):
    $prev_next_order= " order by pd.products_name";
    break;
    case (2):
    $prev_next_order= " order by p.products_model";
    break;
    case (3):
    $prev_next_order= " order by p.products_price_sorter, pd.products_name";
    break;
    case (4):
    $prev_next_order= " order by p.products_price_sorter, p.products_model";
    break;
    case (5):
    $prev_next_order= " order by pd.products_name, p.products_model";
    break;
    case (6):
    $prev_next_order= ' order by LPAD(p.products_sort_order,11,"0"), pd.products_name';
    break;
    default:
    $prev_next_order= " order by pd.products_name";
    break;
  }

/*
  if (!$current_category_id || SHOW_CATEGORIES_ALWAYS == 1) {
    $sql = "SELECT categories_id
            from   " . TABLE_PRODUCTS_TO_CATEGORIES . "
            where  products_id ='" .  (int)$_GET['products_id']
    . "'";
    $cPath_row = $db->Execute($sql);
    $current_category_id = $cPath_row->fields['categories_id'];
    $cPath = $current_category_id;
  }
*/


//  if (!$current_category_id || !$cPath) {
  if ($cPath < 1) {
    $cPath = zen_get_product_path((int)$_GET['products_id']);
//    $_GET['$cPath'] = $cPath;
    $cPath_array = zen_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];

//    $current_category_id = $cPath;
  }


  $sql = "select p.products_id, p.products_model, p.products_price_sorter, pd.products_name, p.products_sort_order
          from   " . TABLE_PRODUCTS . " p, "
  . TABLE_PRODUCTS_DESCRIPTION . " pd, "
  . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
          where  p.products_status = '1' and p.products_id = pd.products_id and pd.language_id= '" . (int)$_SESSION['languages_id'] . "' and p.products_id = ptc.products_id and ptc.categories_id = '" . (int)$current_category_id . "'" .
  $prev_next_order;

  $products_ids = $db->Execute($sql);
  $products_found_count = $products_ids->RecordCount();

  while (!$products_ids->EOF) {
    $id_array[] = $products_ids->fields['products_id'];
    $products_ids->MoveNext();
  }

  // if invalid product id skip
  if (is_array($id_array)) {
    reset ($id_array);
    $counter = 0;
    foreach ($id_array as $key => $value) {
      if ($value == (int)$_GET['products_id']) {
        $position = $counter;
        if ($key == 0) {
          $previous = -1; // it was the first to be found
        } else {
          $previous = $id_array[$key - 1];
        }
        if (isset($id_array[$key + 1]) && $id_array[$key + 1]) {
          $next_item = $id_array[$key + 1];
        } else {
          $next_item = $id_array[0];
        }
      }
      $last = $value;
      $counter++;
    }

    if ($previous == -1) $previous = $last;

    $sql = "select categories_name
            from   " . TABLE_CATEGORIES_DESCRIPTION . "
            where  categories_id = " . (int)$current_category_id . " AND language_id = '" . (int)$_SESSION['languages_id'] . "'";

    $category_name_row = $db->Execute($sql);
  } // if is_array

  // previous_next button and product image settings
  // include products_image status 0 = off 1= on
  // 0 = button only 1= button and product image 2= product image only
  $previous_button = zen_image_button(BUTTON_IMAGE_PREVIOUS, BUTTON_PREVIOUS_ALT);
  $next_item_button = zen_image_button(BUTTON_IMAGE_NEXT, BUTTON_NEXT_ALT);
  $previous_image = zen_get_products_image($previous, PREVIOUS_NEXT_IMAGE_WIDTH, PREVIOUS_NEXT_IMAGE_HEIGHT);
  $next_item_image = zen_get_products_image($next_item, PREVIOUS_NEXT_IMAGE_WIDTH, PREVIOUS_NEXT_IMAGE_HEIGHT);
  if (SHOW_PREVIOUS_NEXT_STATUS == 0) {
    $previous_image = '';
    $next_item_image = '';
  } else {
    if (SHOW_PREVIOUS_NEXT_IMAGES >= 1) {
      if (SHOW_PREVIOUS_NEXT_IMAGES == 2) {
        $previous_button = '';
        $next_item_button = '';
      }
      if ($previous == $next_item) {
        $previous_image = '';
        $next_item_image = '';
      }
    } else {
      $previous_image = '';
      $next_item_image = '';
    }
  }
}
// eof: previous next
?>