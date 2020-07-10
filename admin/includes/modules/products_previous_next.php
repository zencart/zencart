<?php

/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Oct 01 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/////
// BOF PREVIOUS NEXT

if (!isset($current_category_id)) {
  $current_category_id = 0;
}

if (!isset($prev_next_list) || $prev_next_list == '') {
// calculate the previous and next

  $result = $db->Execute("SELECT products_type
                          FROM " . TABLE_PRODUCTS . "
                          WHERE products_id = " . (int)$products_filter);
  $check_type = ($result->EOF) ? 0 : $result->fields['products_type'];
  if (!defined('PRODUCT_INFO_PREVIOUS_NEXT_SORT')) define('PRODUCT_INFO_PREVIOUS_NEXT_SORT', zen_get_configuration_key_value_layout('PRODUCT_INFO_PREVIOUS_NEXT_SORT', $check_type));

  // sort order
  switch (PRODUCT_INFO_PREVIOUS_NEXT_SORT) {
    case (0):
      $prev_next_order = ' ORDER BY p.products_id';
      break;
    case (1):
      $prev_next_order = " ORDER BY pd.products_name";
      break;
    case (2):
      $prev_next_order = " ORDER BY p.products_model";
      break;
    case (3):
      $prev_next_order = " ORDER BY p.products_price, pd.products_name";
      break;
    case (4):
      $prev_next_order = " ORDER BY p.products_price, p.products_model";
      break;
    case (5):
      $prev_next_order = " ORDER BY pd.products_name, p.products_model";
      break;
    case (6):
      $prev_next_order = " ORDER BY p.products_sort_order";
      break;
    default:
      $prev_next_order = " ORDER BY pd.products_name";
      break;
  }


// set current category
  $current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : $current_category_id);

  if (empty($current_category_id)) {
    $sql = "SELECT categories_id
            FROM   " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE products_id = " . (int)$products_filter;

    $cPath_row = $db->Execute($sql);

    $current_category_id = 0;

    if (!$cPath_row->EOF) {
      $current_category_id = $cPath_row->fields['categories_id'];
    }
  }

  $sql = "SELECT p.products_id, pd.products_name
          FROM   " . TABLE_PRODUCTS . " p,
                 " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                 " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
          WHERE  p.products_id = pd.products_id
          AND pd.language_id= " . (int)$_SESSION['languages_id'] . "
          AND p.products_id = ptc.products_id
          AND ptc.categories_id = " . (int)$current_category_id .
      $prev_next_order
  ;

  $products_ids = $db->Execute($sql);
}

// reset if not already set for display
(isset($_GET['products_filter']) && $_GET['products_filter'] == '' ? (int)$_GET['products_filter'] = $products_filter : '');
(isset($_GET['current_category_id']) && $_GET['current_category_id'] == '' ? (int)$_GET['current_category_id'] = $current_category_id : '');

$id_array = [];
foreach ($products_ids as $products_id) {
  $id_array[] = $products_id['products_id'];
}

$counter = 0;
// if invalid product id skip
$id_array_size = count($id_array);
if ($id_array_size) {
  foreach ($id_array as $key => $value) {
    if ($value == $products_filter) {
      $position = $counter;
      if ($key == 0) {
        $previous = -1; // it was the first to be found
      } else {
        $previous = $id_array[$key - 1];
      }
      if ((($key + 1) < $id_array_size) && $id_array[$key + 1]) {
        $next_item = $id_array[$key + 1];
      } else {
        $next_item = $id_array[0];
      }
    }
    $last = $value;
    $counter++;
  }

  if ($previous == -1) {
    $previous = $last;
  }

  $sql = "SELECT categories_name
          FROM " . TABLE_CATEGORIES_DESCRIPTION . "
          WHERE categories_id = " . (int)$current_category_id . "
          AND language_id = " . (int)$_SESSION['languages_id'];

  $category_name_row = $db->Execute($sql);
} // if id_array

/*
  if (strstr($PHP_SELF, FILENAME_PRODUCTS_PRICE_MANAGER)) {
  $curr_page = FILENAME_PRODUCTS_PRICE_MANAGER;
  } else {
  $curr_page = FILENAME_ATTRIBUTES_CONTROLLER;
  }
 */

switch (true) {
  case (strstr($PHP_SELF, FILENAME_ATTRIBUTES_CONTROLLER)):
    $curr_page = FILENAME_ATTRIBUTES_CONTROLLER;
    break;
  case (strstr($PHP_SELF, FILENAME_PRODUCTS_TO_CATEGORIES)):
    $curr_page = FILENAME_PRODUCTS_TO_CATEGORIES;
    break;
  default:
    $curr_page = FILENAME_PRODUCTS_PRICE_MANAGER;
    break;
}
// to display use products_previous_next_display.php
