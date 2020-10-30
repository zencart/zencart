<?php

/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 01 Modified in v1.5.7a $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (isset($_GET['pID'])) {
  $products_id = zen_db_prepare_input($_GET['pID']);
}
if (isset($_POST['edit']) && $_POST['edit'] == 'edit') {
  $action = 'new_product';
} elseif ((isset($_POST['products_model']) ? $_POST['products_model'] : '') . (isset($_POST['products_url']) ? implode('', $_POST['products_url']) : '') . (isset($_POST['products_name']) ? implode('', $_POST['products_name']) : '') . (isset($_POST['products_description']) ? implode('', $_POST['products_description']) : '') != '') {
  $products_date_available = zen_db_prepare_input($_POST['products_date_available']);
  if (DATE_FORMAT_DATE_PICKER != 'yy-mm-dd' && !empty($products_date_available)) {
    $local_fmt = zen_datepicker_format_fordate(); 
    $dt = DateTime::createFromFormat($local_fmt, $products_date_available);
      $products_date_available = 'null';
      if (!empty($dt)) {
        $products_date_available = $dt->format('Y-m-d'); 
      }
  }
  $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

  // Data-cleaning to prevent data-type mismatch errors:
  $sql_data_array = array(
    'products_quantity' => convertToFloat($_POST['products_quantity']),
    'products_type' => (int)$_POST['product_type'],
    'products_model' => zen_db_prepare_input($_POST['products_model']),
    'products_price' => convertToFloat($_POST['products_price']),
    'products_date_available' => $products_date_available,
    'products_weight' => convertToFloat($_POST['products_weight']),
    'products_status' => (int)$_POST['products_status'],
    'products_virtual' => (int)$_POST['products_virtual'],
    'products_tax_class_id' => (int)$_POST['products_tax_class_id'],
//    'manufacturers_id' => (int)$_POST['manufacturers_id'],
    'products_quantity_order_min' => convertToFloat($_POST['products_quantity_order_min']) == 0 ? 1 : convertToFloat($_POST['products_quantity_order_min']),
    'products_quantity_order_units' => convertToFloat($_POST['products_quantity_order_units']) == 0 ? 1 : convertToFloat($_POST['products_quantity_order_units']),
    'products_priced_by_attribute' => (int)$_POST['products_priced_by_attribute'],
    'product_is_free' => (int)$_POST['product_is_free'],
    'product_is_call' => (int)$_POST['product_is_call'],
    'products_quantity_mixed' => (int)$_POST['products_quantity_mixed'],
    'product_is_always_free_shipping' => (int)$_POST['product_is_always_free_shipping'],
    'products_qty_box_status' => (int)$_POST['products_qty_box_status'],
    'products_quantity_order_max' => convertToFloat($_POST['products_quantity_order_max']),
    'products_sort_order' => (int)$_POST['products_sort_order'],
    'products_discount_type' => (int)$_POST['products_discount_type'],
    'products_discount_type_from' => (int)$_POST['products_discount_type_from'],
    'products_price_sorter' => convertToFloat($_POST['products_price_sorter']),
  );

  $db_filename = zen_limit_image_filename($_POST['products_image'], TABLE_PRODUCTS, 'products_image');
  $sql_data_array['products_image'] = zen_db_prepare_input($db_filename);
  $new_image = 'true';

  // when set to none remove from database
  // is out dated for browsers use radio only
  if (isset($_POST['image_delete']) && $_POST['image_delete'] == '1') {
    $sql_data_array['products_image'] = '';
    $new_image = 'false';
  }

  if ($action == 'insert_product') {
    $sql_data_array['products_date_added'] = 'now()';
    $sql_data_array['master_categories_id'] = (int)$current_category_id;

    zen_db_perform(TABLE_PRODUCTS, $sql_data_array);
    $products_id = zen_db_insert_id();

    // reset products_price_sorter for searches etc.
    zen_update_products_price_sorter($products_id);

    $db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
                  VALUES (" . (int)$products_id . ", " . (int)$current_category_id . ")");

    zen_record_admin_activity('New product ' . (int)$products_id . ' added via admin console.', 'info');

    ///////////////////////////////////////////////////////
    //// INSERT PRODUCT-TYPE-SPECIFIC *INSERTS* HERE //////

    $sql_data_array = array(
      'products_id' => (int)$products_id,
      'artists_id' => (int)$_POST['artists_id'],
      'record_company_id' => (int)$_POST['record_company_id'],
      'music_genre_id' => (int)$_POST['music_genre_id'],
    );

    zen_db_perform(TABLE_PRODUCT_MUSIC_EXTRA, $sql_data_array);

    ////    *END OF PRODUCT-TYPE-SPECIFIC INSERTS* ////////
    ///////////////////////////////////////////////////////
  } elseif ($action == 'update_product') {
    $sql_data_array['products_last_modified'] = 'now()';
    $sql_data_array['master_categories_id'] = (!empty($_POST['master_category']) && (int)$_POST['master_category'] > 0 ? (int)$_POST['master_category'] : (int)$_POST['master_categories_id']);

    zen_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = " . (int)$products_id);

    zen_record_admin_activity('Updated product ' . (int)$products_id . ' via admin console.', 'info');

    // reset products_price_sorter for searches etc.
    zen_update_products_price_sorter((int)$products_id);

    ///////////////////////////////////////////////////////
    //// INSERT PRODUCT-TYPE-SPECIFIC *UPDATES* HERE //////

    $sql_data_array = array(
      'artists_id' => (int)$_POST['artists_id'],
      'record_company_id' => (int)$_POST['record_company_id'],
      'music_genre_id' => (int)$_POST['music_genre_id'],
    );

    zen_db_perform(TABLE_PRODUCT_MUSIC_EXTRA, $sql_data_array, 'update', "products_id = " . (int)$products_id);

    ////    *END OF PRODUCT-TYPE-SPECIFIC UPDATES* ////////
    ///////////////////////////////////////////////////////
  }

  $languages = zen_get_languages();
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $language_id = $languages[$i]['id'];

    $sql_data_array = array(
      'products_name' => zen_db_prepare_input($_POST['products_name'][$language_id]),
      'products_description' => zen_db_prepare_input($_POST['products_description'][$language_id]),
      'products_url' => zen_db_prepare_input($_POST['products_url'][$language_id]));

    if ($action == 'insert_product') {
      $insert_sql_data = array(
        'products_id' => (int)$products_id,
        'language_id' => (int)$language_id);

      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

      zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
    } elseif ($action == 'update_product') {
      zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = " . (int)$products_id . " and language_id = " . (int)$language_id);
    }
  }
  $zco_notifier->notify('NOTIFY_PRODUCT_MUSIC_UPDATE_PRODUCT_END', array('action' => $action, 'products_id' => $products_id));

  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_POST['search']) ? '&search=' . $_POST['search'] : '')));
} else {
  $messageStack->add_session(ERROR_NO_DATA_TO_SAVE, 'error');
  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_POST['search']) ? '&search=' . $_POST['search'] : '')));
}

/**
 * NOTE: THIS IS HERE FOR BACKWARD COMPATIBILITY. The function is properly declared in the functions files instead.
 * Convert value to a float -- mainly used for sanitizing and returning non-empty strings or nulls
 * @param int|float|string $input
 * @return float|int
 */
if (!function_exists('convertToFloat')) {

  function convertToFloat($input = 0) {
    if ($input === null) {
      return 0;
    }
    $val = preg_replace('/[^0-9,\.\-]/', '', $input);
    // do a non-strict compare here:
    if ($val == 0) {
      return 0;
    }

    return (float)$val;
  }

}
