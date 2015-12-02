<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: products_previous_next.php 18695 2011-05-04 05:24:19Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/////
// BOF PREVIOUS NEXT

  if (!isset($current_category_id)) $current_category_id = 0;

  if (!isset($prev_next_list) || $prev_next_list == '') {
// calculate the previous and next

    $result = $db->Execute("select products_type from " . TABLE_PRODUCTS . " where products_id='" . (int)$products_filter . "'");
    $check_type = ($result->EOF) ? 0 : $result->fields['products_type'];
    define('PRODUCT_INFO_PREVIOUS_NEXT_SORT', zen_get_configuration_key_value_layout('PRODUCT_INFO_PREVIOUS_NEXT_SORT', $check_type));

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
        $prev_next_order= " order by p.products_price, pd.products_name";
        break;
      case (4):
        $prev_next_order= " order by p.products_price, p.products_model";
        break;
      case (5):
        $prev_next_order= " order by pd.products_name, p.products_model";
        break;
      default:
        $prev_next_order= " order by pd.products_name";
        break;
      }


// set current category
    $current_category_id = (isset($_GET['current_category_id']) ? (int)$_GET['current_category_id'] : $current_category_id);

    if (!$current_category_id) {
      $sql = "SELECT categories_id
              from   " . TABLE_PRODUCTS_TO_CATEGORIES . "
              where  products_id ='" .  (int)$products_filter . "'";

      $cPath_row = $db->Execute($sql);
      $current_category_id = $cPath_row->fields['categories_id'];
    }

    $sql = "select p.products_id, pd.products_name
            from   " . TABLE_PRODUCTS . " p, "
                     . TABLE_PRODUCTS_DESCRIPTION . " pd, "
                     . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
            where  p.products_id = pd.products_id and pd.language_id= '" . (int)$_SESSION['languages_id'] . "' and p.products_id = ptc.products_id and ptc.categories_id = '" . (int)$current_category_id . "'" .
            $prev_next_order
            ;

    $products_ids = $db->Execute($sql);
  }

// reset if not already set for display
  ($_GET['products_filter'] == '' ? (int)$_GET['products_filter'] = $products_filter : '');
  ($_GET['current_category_id'] == '' ? (int)$_GET['current_category_id'] = $current_category_id : '');

  $id_array = array();
  while (!$products_ids->EOF) {
    $id_array[] = $products_ids->fields['products_id'];
    $products_ids->MoveNext();
  }

  $counter = 0;
// if invalid product id skip
  if (sizeof($id_array)) {
    reset ($id_array);
    while (list($key, $value) = each ($id_array)) {
      if ($value == $products_filter) {
        $position = $counter;
        if ($key == 0) {
          $previous = -1; // it was the first to be found
        } else {
          $previous = $id_array[$key - 1];
        }
        if ($id_array[$key + 1]) {
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
            where  categories_id = '" . (int)$current_category_id . "' AND language_id = '" . (int)$_SESSION['languages_id'] . "'";

    $category_name_row = $db->Execute($sql);
  } // if id_array

  switch(true) {
  case (strstr($PHP_SELF, FILENAME_ATTRIBUTES_CONTROLLER) || $zcRequest->readGet('cmd') == FILENAME_ATTRIBUTES_CONTROLLER):
    $curr_page = FILENAME_ATTRIBUTES_CONTROLLER;
    break;
  case (strstr($PHP_SELF, FILENAME_PRODUCTS_TO_CATEGORIES) || $zcRequest->readGet('cmd') == FILENAME_PRODUCTS_TO_CATEGORIES):
    $curr_page = FILENAME_PRODUCTS_TO_CATEGORIES;
    break;
  default:
    $curr_page = FILENAME_PRODUCTS_PRICE_MANAGER;
    break;
  }
// to display use products_previous_next_display.php
