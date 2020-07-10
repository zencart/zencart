<?php
/**
 *  product_prev_next.php
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 09 Modified in v1.5.7 $
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


  $sql = "SELECT p.products_id, p.products_model, p.products_price_sorter, pd.products_name, p.products_sort_order
          FROM   " . TABLE_PRODUCTS . " p, "
  . TABLE_PRODUCTS_DESCRIPTION . " pd, "
  . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
          WHERE  p.products_status = '1' AND p.products_id = pd.products_id AND pd.language_id= '" . (int)$_SESSION['languages_id'] . "' AND p.products_id = ptc.products_id AND ptc.categories_id = '" . (int)$current_category_id . "'" .
  $prev_next_order;

  $products_ids = $db->Execute($sql);
  $products_found_count = $products_ids->RecordCount();

  // if invalid product id skip
  if ($products_found_count > 0) {

    foreach ($products_ids as $products_id) {
      $id_array[] = $products_id['products_id'];
    }

    $position = $counter = 0;
    $previous = -1; // identify as needing to go to the end of the list.
    $next_item = $id_array[0]; // set the next as the first initially.
    foreach ($id_array as $key => $value) {
      if ($value == (int)$_GET['products_id']) {
        $position = $counter;
        if ($key != 0) {
          $previous = $id_array[$key - 1];
        }
        if (isset($id_array[$key + 1]) && $id_array[$key + 1]) {
          $next_item = $id_array[$key + 1];
        }
      }
      $last = $value;
      $counter++;
    }

    if ($previous == -1) $previous = $last;

    $sql = "SELECT categories_name
            FROM   " . TABLE_CATEGORIES_DESCRIPTION . "
            WHERE  categories_id = " . (int)$current_category_id . " AND language_id = '" . (int)$_SESSION['languages_id'] . "'";

    $category_name_row = $db->Execute($sql);
  } // if is_array

  // previous_next button and product image settings
  // include products_image status 0 = off 1= on
  // 0 = button only 1= button and product image 2= product image only
  $previous_button = '';
  $next_item_button = '';
  if (SHOW_PREVIOUS_NEXT_STATUS == 0 || SHOW_PREVIOUS_NEXT_IMAGES != 2) {
    $previous_button = zen_image_button(BUTTON_IMAGE_PREVIOUS, BUTTON_PREVIOUS_ALT);
    $next_item_button = zen_image_button(BUTTON_IMAGE_NEXT, BUTTON_NEXT_ALT);
  }
  $previous_image = '';
  $next_item_image = '';
  // identify what constitutes equality and then not that.
  $prev_not_equal_next = !((empty($previous) && empty($next_item)) || (!empty($previous) && !empty($next_item) && $previous == $next_item));
  if (SHOW_PREVIOUS_NEXT_STATUS != 0 && SHOW_PREVIOUS_NEXT_IMAGES >= 1 && $prev_not_equal_next) {
    $previous_image = (!empty($previous) ? zen_get_products_image($previous, PREVIOUS_NEXT_IMAGE_WIDTH, PREVIOUS_NEXT_IMAGE_HEIGHT) : '');
    $next_item_image = (!empty($next_item) ? zen_get_products_image($next_item, PREVIOUS_NEXT_IMAGE_WIDTH, PREVIOUS_NEXT_IMAGE_HEIGHT) : '');
  }
}
// eof: previous next
