<?php
/**
 * functions for managing removal of products/categories
 *
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
  */

  function zen_remove_category($category_id) {
    if ((int)$category_id == 0) return;
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_CATEGORY', array(), $category_id);

    // delete from salemaker - sale_categories_selected
    $chk_sale_categories_selected = $db->Execute("select * from " . TABLE_SALEMAKER_SALES . "
    WHERE
    sale_categories_selected = '" . (int)$category_id . "'
    OR sale_categories_selected LIKE '%," . (int)$category_id . ",%'
    OR sale_categories_selected LIKE '%," . (int)$category_id . "'
    OR sale_categories_selected LIKE '" . (int)$category_id . ",%'");

    // delete from salemaker - sale_categories_all
    $chk_sale_categories_all = $db->Execute("select * from " . TABLE_SALEMAKER_SALES . "
    WHERE
    sale_categories_all = '" . (int)$category_id . "'
    OR sale_categories_all LIKE '%," . (int)$category_id . ",%'
    OR sale_categories_all LIKE '%," . (int)$category_id . "'
    OR sale_categories_all LIKE '" . (int)$category_id . ",%'");

//echo 'WORKING ON: ' . (int)$category_id . ' chk_sale_categories_selected: ' . $chk_sale_categories_selected->RecordCount() . ' chk_sale_categories_all: ' . $chk_sale_categories_all->RecordCount() . '<br>';
while (!$chk_sale_categories_selected->EOF) {
  $skip_cats = false; // used when deleting
  $skip_sale_id = 0;
//echo '<br>FIRST LOOP: sale_id ' . $chk_sale_categories_selected->fields['sale_id'] . ' sale_categories_selected: ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
  // 9 or ,9 or 9,
  // delete record if sale_categories_selected = 9 and  sale_categories_all = ,9,
  if ($chk_sale_categories_selected->fields['sale_categories_selected'] == (int)$category_id and $chk_sale_categories_selected->fields['sale_categories_all'] == ',' . (int)$category_id . ',') { // delete record
//echo 'A: I should delete this record sale_id: ' . $chk_sale_categories_selected->fields['sale_id'] . '<br><br>';
    $skip_cats = true;
    $skip_sale_id = $chk_sale_categories_selected->fields['sale_id'];
    $salemakerdelete = "DELETE from " . TABLE_SALEMAKER_SALES . " WHERE sale_id='"  . $skip_sale_id . "'";
  }

  // if in the front - remove 9,
  //  if ($chk_sale_categories_selected->fields['sale_categories_selected'] == (int)$category_id . ',') { // front
  if (!$skip_cats && (preg_match('/^' . (int)$category_id . ',/', $chk_sale_categories_selected->fields['sale_categories_selected'])) ) { // front
//echo 'B: I need to remove - ' . (int)$category_id . ', - from the front of ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
    $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], strlen((int)$category_id . ','));
//echo 'B: new_sale_categories_selected: ' . $new_sale_categories_selected . '<br><br>';
  }

  // if in the middle or end - remove ,9,
  if (!$skip_cats && (strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',')) ) { // middle or end
//echo 'C: I need to remove - ,' . (int)$category_id . ', - from the middle or end ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
    $start_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',') + strlen(',' . (int)$category_id . ',');
    $end_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id . ',', $start_cat+strlen(',' . (int)$category_id . ','));
    $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1)) . substr($chk_sale_categories_selected->fields['sale_categories_selected'], $start_cat);
//echo 'C: new_sale_categories_selected: ' . $new_sale_categories_selected. '<br><br>';
    $skip_cat_last = true;
  }


// not needed in loop 1 if middle does end
  // if on the end - remove ,9 skip if middle cleaned it
  if (!$skip_cats && !$skip_cat_last && (strripos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id)) ) { // end
    $start_cat = (int)strpos($chk_sale_categories_selected->fields['sale_categories_selected'], ',' . (int)$category_id) + strlen(',' . (int)$category_id);
//echo 'D: I need to remove - ,' . (int)$category_id . ' - from the end ' . $chk_sale_categories_selected->fields['sale_categories_selected'] . '<br>';
    $new_sale_categories_selected = substr($chk_sale_categories_selected->fields['sale_categories_selected'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1));
//echo 'D: new_sale_categories_selected: ' . $new_sale_categories_selected. '<br><br>';
  }

  if (!$skip_cats) {
    $salemakerupdate =
    "UPDATE " . TABLE_SALEMAKER_SALES . "
    SET sale_categories_selected='" . $new_sale_categories_selected . "'
    WHERE sale_id = '" . $chk_sale_categories_selected->fields['sale_id'] . "'";
//echo 'Update new_sale_categories_selected: ' . $salemakerupdate . '<br>';
    $db->Execute($salemakerupdate);
  } else {
//echo 'Record was deleted sale_id ' . $skip_sale_id . '<br>' . $salemakerdelete;
    $db->Execute($salemakerdelete);
  }

  $chk_sale_categories_selected->MoveNext();
}

while (!$chk_sale_categories_all->EOF) {
//echo '<br><br>SECOND LOOP: sale_id ' . $chk_sale_categories_all->fields['sale_id'] . ' sale_categories_all: ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br><br>';
  // remove ,9 if on front as ,9, - remove ,9 if in the middle as ,9, - remove ,9 if on the end as ,9,
  // beware of ,79, or ,98, or ,99, when cleaning 9
  // if ($chk_sale_categories_all->fields['sale_categories_all'] == ',9') { // front
  // if (something for the middle) { // middle
  // if (right($chk_sale_categories_all->fields['sale_categories_all']) == ',9,') { // end

  $skip_cats = false;
  if ($skip_sale_id == $chk_sale_categories_all->fields['sale_id']) { // was deleted
//echo 'A: I should delete this record sale_id: ' . $chk_sale_categories_all->fields['sale_id'] . ' but already done' . '<br><br>';
    $skip_cats = true;
  }

  // if in the front - remove 9,
  //  if ($chk_sale_categories_all->fields['sale_categories_all'] == (int)$category_id . ',') { // front
  if (!$skip_cats && (preg_match('/^' . ',' . (int)$category_id . ',/', $chk_sale_categories_all->fields['sale_categories_all'])) ) { // front
//echo 'B: I need to remove - ' . (int)$category_id . ', - from the front of ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
    $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], strlen(',' . (int)$category_id));
//echo 'B: new_sale_categories_all: ' . $new_sale_categories_all . '<br><br>';
  }

  // if in the middle or end - remove ,9,
  if (!$skip_cats && (strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',')) ) { // middle
//echo 'C: I need to remove - ,' . (int)$category_id . ', - from the middle or end ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
    $start_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',') + strlen(',' . (int)$category_id . ',');
    $end_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',', $start_cat+strlen(',' . (int)$category_id . ','));
    $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1)) . substr($chk_sale_categories_all->fields['sale_categories_all'], $start_cat);
//echo 'C: new_sale_categories_all: ' . $new_sale_categories_all. '<br><br>';
  }

/*
// not needed in loop 2
  // if on the end - remove ,9,
  if (!$skip_cats && (strripos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id . ',')) ) { // end
    $start_cat = (int)strpos($chk_sale_categories_all->fields['sale_categories_all'], ',' . (int)$category_id) + strlen(',' . (int)$category_id . ',');
    echo 'D: I need to remove from the end - ,' . (int)$category_id . ', - from the end ' . $chk_sale_categories_all->fields['sale_categories_all'] . '<br>';
    $new_sale_categories_all = substr($chk_sale_categories_all->fields['sale_categories_all'], 0, $start_cat - (strlen(',' . (int)$category_id . ',') - 1));
    echo 'D: new_sale_categories_all: ' . $new_sale_categories_all. '<br><br>';
  }
*/
      $salemakerupdate = "UPDATE " . TABLE_SALEMAKER_SALES . " SET sale_categories_all='" . $new_sale_categories_all . "' WHERE sale_id = '" . $chk_sale_categories_all->fields['sale_id'] . "'";

//echo 'Update sale_categories_all: ' . $salemakerupdate . '<br>';

      $db->Execute($salemakerupdate);

      $chk_sale_categories_all->MoveNext();
}

//die('DONE TESTING');

    $category_image = $db->Execute("select categories_image
                                    from " . TABLE_CATEGORIES . "
                                    where categories_id = '" . (int)$category_id . "'");

    $duplicate_image = $db->Execute("select count(*) as total
                                     from " . TABLE_CATEGORIES . "
                                     where categories_image = '" .
                                           zen_db_input($category_image->fields['categories_image']) . "'");
    if ($duplicate_image->fields['total'] < 2) {
      if (file_exists(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $category_image->fields['categories_image']);
      }
    }

    $db->Execute("delete from " . TABLE_CATEGORIES . "
                  where categories_id = '" . (int)$category_id . "'");

    $db->Execute("delete from " . TABLE_CATEGORIES_DESCRIPTION . "
                  where categories_id = '" . (int)$category_id . "'");

    $db->Execute("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                  where categories_id = '" . (int)$category_id . "'");

    $db->Execute("delete from " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
                  where categories_id = '" . (int)$category_id . "'");

    $db->Execute("delete from " . TABLE_COUPON_RESTRICT . "
                  where category_id = '" . (int)$category_id . "'");

    zen_record_admin_activity('Deleted category ' . (int)$category_id . ' from database via admin console.', 'warning');
  }

  function zen_remove_product($product_id, $ptc = 'true') {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_PRODUCT', array(), $product_id, $ptc);

    $product_image = $db->Execute("select products_image
                                   from " . TABLE_PRODUCTS . "
                                   where products_id = '" . (int)$product_id . "'");

    $duplicate_image = $db->Execute("select count(*) as total
                                     from " . TABLE_PRODUCTS . "
                                     where products_image = '" . zen_db_input($product_image->fields['products_image']) . "'");

    if ($duplicate_image->fields['total'] < 2 and $product_image->fields['products_image'] != '' && PRODUCTS_IMAGE_NO_IMAGE != substr($product_image->fields['products_image'], strrpos($product_image->fields['products_image'], '/')+1)) {
      $products_image = $product_image->fields['products_image'];
      $products_image_extension = substr($products_image, strrpos($products_image, '.'));
      $products_image_base = preg_replace('/' . $products_image_extension . '/', '', $products_image);

      $filename_medium = 'medium/' . $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extension;
      $filename_large = 'large/' . $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extension;

      if (file_exists(DIR_FS_CATALOG_IMAGES . $product_image->fields['products_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $product_image->fields['products_image']);
      }
      if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_medium)) {
        @unlink(DIR_FS_CATALOG_IMAGES . $filename_medium);
      }
      if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_large)) {
        @unlink(DIR_FS_CATALOG_IMAGES . $filename_large);
      }
    }

    $db->Execute("delete from " . TABLE_SPECIALS . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_PRODUCTS . "
                  where products_id = '" . (int)$product_id . "'");

//    if ($ptc == 'true') {
      $db->Execute("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                    where products_id = '" . (int)$product_id . "'");
//    }

    $db->Execute("delete from " . TABLE_PRODUCTS_DESCRIPTION . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                  where products_id = '" . (int)$product_id . "'");

    zen_products_attributes_download_delete($product_id);

    $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                  where products_id = '" . (int)$product_id . "'");


    $product_reviews = $db->Execute("select reviews_id
                                     from " . TABLE_REVIEWS . "
                                     where products_id = '" . (int)$product_id . "'");

    while (!$product_reviews->EOF) {
      $db->Execute("delete from " . TABLE_REVIEWS_DESCRIPTION . "
                    where reviews_id = '" . (int)$product_reviews->fields['reviews_id'] . "'");
      $product_reviews->MoveNext();
    }
    $db->Execute("delete from " . TABLE_REVIEWS . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_FEATURED . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                  where products_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_COUPON_RESTRICT . "
                  where product_id = '" . (int)$product_id . "'");

    $db->Execute("delete from " . TABLE_PRODUCTS_NOTIFICATIONS . "
                  where products_id = '" . (int)$product_id . "'");

    zen_record_admin_activity('Deleted product ' . (int)$product_id . ' from database via admin console.', 'warning');
  }

  function zen_products_attributes_download_delete($product_id) {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_PRODUCTS_ATTRIBUTES_DOWNLOAD_DELETE', array(), $product_id);

  // remove downloads if they exist
    $remove_downloads= $db->Execute("select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id= '" . (int)$product_id . "'");
    while (!$remove_downloads->EOF) {
      $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " where products_attributes_id= '" . $remove_downloads->fields['products_attributes_id'] . "'");
      $remove_downloads->MoveNext();
    }
  }

  function zen_remove_order($order_id, $restock = false) {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_REMOVE_ORDER', array(), $order_id, $restock);
    if ($restock == 'on') {
      $order = $db->Execute("select products_id, products_quantity
                             from " . TABLE_ORDERS_PRODUCTS . "
                             where orders_id = '" . (int)$order_id . "'");

      while (!$order->EOF) {
        $db->Execute("update " . TABLE_PRODUCTS . "
                      set products_quantity = products_quantity + " . $order->fields['products_quantity'] . ", products_ordered = products_ordered - " . $order->fields['products_quantity'] . " where products_id = '" . (int)$order->fields['products_id'] . "'");
        $order->MoveNext();
      }
    }

    $db->Execute("delete from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_ORDERS_STATUS_HISTORY . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_ORDERS_TOTAL . "
                  where orders_id = '" . (int)$order_id . "'");

    $db->Execute("delete from " . TABLE_COUPON_GV_QUEUE . "
                  where order_id = '" . (int)$order_id . "' and release_flag = 'N'");

    zen_record_admin_activity('Deleted order ' . (int)$order_id . ' from database via admin console.', 'warning');
  }


////
// Sets the status of a product
  function zen_set_product_status($products_id, $status) {
    global $db;
    if ($status == '1') {
      return $db->Execute("update " . TABLE_PRODUCTS . "
                           set products_status = 1, products_last_modified = now()
                           where products_id = '" . (int)$products_id . "'");

    } elseif ($status == '0') {
      return $db->Execute("update " . TABLE_PRODUCTS . "
                           set products_status = 0, products_last_modified = now()
                           where products_id = '" . (int)$products_id . "'");

    } else {
      return -1;
    }
  }

/**
 * Sets the status of a product review
 */
  function zen_set_reviews_status($review_id, $status) {
    global $db;
    if ($status == '1') {
      return $db->Execute("update " . TABLE_REVIEWS . "
                           set status = 1
                           where reviews_id = '" . (int)$review_id . "'");

    } elseif ($status == '0') {
      return $db->Execute("update " . TABLE_REVIEWS . "
                           set status = 0
                           where reviews_id = '" . (int)$review_id . "'");

    } else {
      return -1;
    }
  }





function zen_copy_products_attributes($products_id_from, $products_id_to) {
  global $db;
  global $messageStack;
  global $copy_attributes_delete_first, $copy_attributes_duplicates_skipped, $copy_attributes_duplicates_overwrite, $copy_attributes_include_downloads, $copy_attributes_include_filename;

// Check for errors in copy request
  if ( (!zen_has_product_attributes($products_id_from, 'false') or !zen_products_id_valid($products_id_to)) or $products_id_to == $products_id_from ) {
    if ($products_id_to == $products_id_from) {
      // same products_id
      $messageStack->add_session('<b>WARNING: Cannot copy from Product ID #' . $products_id_from . ' to Product ID # ' . $products_id_to . ' ... No copy was made' . '</b>', 'caution');
    } else {
      if (!zen_has_product_attributes($products_id_from, 'false')) {
        // no attributes found to copy
        $messageStack->add_session('<b>WARNING: No Attributes to copy from Product ID #' . $products_id_from . ' for: ' . zen_get_products_name($products_id_from) . ' ... No copy was made' . '</b>', 'caution');
      } else {
        // invalid products_id
        $messageStack->add_session('<b>WARNING: There is no Product ID #' . $products_id_to . ' ... No copy was made' . '</b>', 'caution');
      }
    }
  } else {
// FIX HERE - remove once working

// check if product already has attributes
    $check_attributes = zen_has_product_attributes($products_id_to, 'false');

    if ($copy_attributes_delete_first=='1' and $check_attributes == true) {
// die('DELETE FIRST - Copying from ' . $products_id_from . ' to ' . $products_id_to . ' Do I delete first? ' . $copy_attributes_delete_first);
      // delete all attributes first from products_id_to
      zen_products_attributes_download_delete($products_id_to);
      $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$products_id_to . "'");
    }

// get attributes to copy from
    $products_copy_from= $db->Execute("select * from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . (int)$products_id_from . "'" . " order by products_attributes_id");

    while ( !$products_copy_from->EOF ) {
// This must match the structure of your products_attributes table

      $update_attribute = false;
      $add_attribute = true;
      $check_duplicate = $db->Execute("select * from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id='" . (int)$products_id_to . "'" . " and options_id= '" . (int)$products_copy_from->fields['options_id'] . "' and options_values_id='" . (int)$products_copy_from->fields['options_values_id'] .  "'");
      if ($check_attributes == true) {
        if ($check_duplicate->RecordCount() == 0) {
          $update_attribute = false;
          $add_attribute = true;
        } else {
          if ($check_duplicate->RecordCount() == 0) {
            $update_attribute = false;
            $add_attribute = true;
          } else {
            $update_attribute = true;
            $add_attribute = false;
          }
        }
      } else {
        $update_attribute = false;
        $add_attribute = true;
      }

// die('UPDATE/IGNORE - Checking Copying from ' . $products_id_from . ' to ' . $products_id_to . ' Do I delete first? ' . ($copy_attributes_delete_first == '1' ? TEXT_YES : TEXT_NO) . ' Do I add? ' . ($add_attribute == true ? TEXT_YES : TEXT_NO) . ' Do I Update? ' . ($update_attribute == true ? TEXT_YES : TEXT_NO) . ' Do I skip it? ' . ($copy_attributes_duplicates_skipped=='1' ? TEXT_YES : TEXT_NO) . ' Found attributes in From: ' . $check_duplicate->RecordCount());

      if ($copy_attributes_duplicates_skipped == '1' and $check_duplicate->RecordCount() != 0) {
        // skip it
          $messageStack->add_session(TEXT_ATTRIBUTE_COPY_SKIPPING . $products_copy_from->fields['products_attributes_id'] . ' for Products ID#' . $products_id_to, 'caution');
      } else {
        if ($add_attribute == true) {
          // New attribute - insert it
          $db->Execute("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id, options_values_price, price_prefix, products_options_sort_order, product_attribute_is_free, products_attributes_weight, products_attributes_weight_prefix, attributes_display_only, attributes_default, attributes_discounted, attributes_image, attributes_price_base_included, attributes_price_onetime, attributes_price_factor, attributes_price_factor_offset, attributes_price_factor_onetime, attributes_price_factor_onetime_offset, attributes_qty_prices, attributes_qty_prices_onetime, attributes_price_words, attributes_price_words_free, attributes_price_letters, attributes_price_letters_free, attributes_required)
                        values ('" . (int)$products_id_to . "',
          '" . $products_copy_from->fields['options_id'] . "',
          '" . $products_copy_from->fields['options_values_id'] . "',
          '" . $products_copy_from->fields['options_values_price'] . "',
          '" . $products_copy_from->fields['price_prefix'] . "',
          '" . $products_copy_from->fields['products_options_sort_order'] . "',
          '" . $products_copy_from->fields['product_attribute_is_free'] . "',
          '" . $products_copy_from->fields['products_attributes_weight'] . "',
          '" . $products_copy_from->fields['products_attributes_weight_prefix'] . "',
          '" . $products_copy_from->fields['attributes_display_only'] . "',
          '" . $products_copy_from->fields['attributes_default'] . "',
          '" . $products_copy_from->fields['attributes_discounted'] . "',
          '" . $products_copy_from->fields['attributes_image'] . "',
          '" . $products_copy_from->fields['attributes_price_base_included'] . "',
          '" . $products_copy_from->fields['attributes_price_onetime'] . "',
          '" . $products_copy_from->fields['attributes_price_factor'] . "',
          '" . $products_copy_from->fields['attributes_price_factor_offset'] . "',
          '" . $products_copy_from->fields['attributes_price_factor_onetime'] . "',
          '" . $products_copy_from->fields['attributes_price_factor_onetime_offset'] . "',
          '" . $products_copy_from->fields['attributes_qty_prices'] . "',
          '" . $products_copy_from->fields['attributes_qty_prices_onetime'] . "',
          '" . $products_copy_from->fields['attributes_price_words'] . "',
          '" . $products_copy_from->fields['attributes_price_words_free'] . "',
          '" . $products_copy_from->fields['attributes_price_letters'] . "',
          '" . $products_copy_from->fields['attributes_price_letters_free'] . "',
          '" . $products_copy_from->fields['attributes_required'] . "')");
          $messageStack->add_session(TEXT_ATTRIBUTE_COPY_INSERTING . $products_copy_from->fields['products_attributes_id'] . ' for Products ID#' . $products_id_to, 'caution');
        }
        if ($update_attribute == true) {
          // Update attribute - Just attribute settings not ids
          $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set
          options_values_price='" . $products_copy_from->fields['options_values_price'] . "',
          price_prefix='" . $products_copy_from->fields['price_prefix'] . "',
          products_options_sort_order='" . $products_copy_from->fields['products_options_sort_order'] . "',
          product_attribute_is_free='" . $products_copy_from->fields['product_attribute_is_free'] . "',
          products_attributes_weight='" . $products_copy_from->fields['products_attributes_weight'] . "',
          products_attributes_weight_prefix='" . $products_copy_from->fields['products_attributes_weight_prefix'] . "',
          attributes_display_only='" . $products_copy_from->fields['attributes_display_only'] . "',
          attributes_default='" . $products_copy_from->fields['attributes_default'] . "',
          attributes_discounted='" . $products_copy_from->fields['attributes_discounted'] . "',
          attributes_image='" . $products_copy_from->fields['attributes_image'] . "',
          attributes_price_base_included='" . $products_copy_from->fields['attributes_price_base_included'] . "',
          attributes_price_onetime='" . $products_copy_from->fields['attributes_price_onetime'] . "',
          attributes_price_factor='" . $products_copy_from->fields['attributes_price_factor'] . "',
          attributes_price_factor_offset='" . $products_copy_from->fields['attributes_price_factor_offset'] . "',
          attributes_price_factor_onetime='" . $products_copy_from->fields['attributes_price_factor_onetime'] . "',
          attributes_price_factor_onetime_offset='" . $products_copy_from->fields['attributes_price_factor_onetime_offset'] . "',
          attributes_qty_prices='" . $products_copy_from->fields['attributes_qty_prices'] . "',
          attributes_qty_prices_onetime='" . $products_copy_from->fields['attributes_qty_prices_onetime'] . "',
          attributes_price_words='" . $products_copy_from->fields['attributes_price_words'] . "',
          attributes_price_words_free='" . $products_copy_from->fields['attributes_price_words_free'] . "',
          attributes_price_letters='" . $products_copy_from->fields['attributes_price_letters'] . "',
          attributes_price_letters_free='" . $products_copy_from->fields['attributes_price_letters_free'] . "',
          attributes_required='" . $products_copy_from->fields['attributes_required'] . "'"
           . " where products_id='" . (int)$products_id_to . "'" . " and options_id= '" . $products_copy_from->fields['options_id'] . "' and options_values_id='" . $products_copy_from->fields['options_values_id'] . "'");
//           . " where products_id='" . $products_id_to . "'" . " and options_id= '" . $products_copy_from->fields['options_id'] . "' and options_values_id='" . $products_copy_from->fields['options_values_id'] . "' and attributes_image='" . $products_copy_from->fields['attributes_image'] . "' and attributes_price_base_included='" . $products_copy_from->fields['attributes_price_base_included'] .  "'");
          $messageStack->add_session(TEXT_ATTRIBUTE_COPY_UPDATING . $products_copy_from->fields['products_attributes_id'] . ' for Products ID#' . $products_id_to, 'caution');
        }
      }

      $products_copy_from->MoveNext();
    } // end of products attributes while loop

     // reset products_price_sorter for searches etc.
     zen_update_products_price_sorter($products_id_to);
  } // end of no attributes or other errors
} // eof: zen_copy_products_attributes


/**
 * Delete all product attributes
 */
  function zen_delete_products_attributes($delete_product_id) {
    global $db, $zco_notifier;
    $zco_notifier->notify('NOTIFIER_ADMIN_ZEN_DELETE_PRODUCTS_ATTRIBUTES', array(), $delete_product_id);

    // first delete associated downloads
    $products_delete_from = $db->Execute("select pa.products_id, pad.products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad  where pa.products_id='" . (int)$delete_product_id . "' and pad.products_attributes_id= pa.products_attributes_id");
    while (!$products_delete_from->EOF) {
      $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " where products_attributes_id = '" . $products_delete_from->fields['products_attributes_id'] . "'");
      $products_delete_from->MoveNext();
    }

    $db->Execute("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$delete_product_id . "'");
}

/**
 * Set Product Attributes Sort Order to Products Option Value Sort Order
 */
  function zen_update_attributes_products_option_values_sort_order($products_id) {
    global $db;
    $attributes_sort_order = $db->Execute("select distinct pa.products_attributes_id, pa.options_id, pa.options_values_id, pa.products_options_sort_order, pov.products_options_values_sort_order from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$products_id . "' and pa.options_values_id = pov.products_options_values_id");
    while (!$attributes_sort_order->EOF) {
      $db->Execute("update " . TABLE_PRODUCTS_ATTRIBUTES . " set products_options_sort_order = '" . $attributes_sort_order->fields['products_options_values_sort_order'] . "' where products_id = '" . (int)$products_id . "' and products_attributes_id = '" . $attributes_sort_order->fields['products_attributes_id'] . "'");
      $attributes_sort_order->MoveNext();
    }
  }

/**
 * copy quantity-discounts from one product to another
 */
  function zen_copy_discounts_to_product($copy_from, $copy_to) {
    global $db;

    $check_discount_type_query = "select products_discount_type, products_discount_type_from, products_mixed_discount_quantity from " . TABLE_PRODUCTS . " where products_id='" . (int)$copy_from . "'";
    $check_discount_type = $db->Execute($check_discount_type_query);
    if ($check_discount_type->EOF) return FALSE;

    $db->Execute("update " . TABLE_PRODUCTS . " set products_discount_type='" . $check_discount_type->fields['products_discount_type'] . "', products_discount_type_from='" . $check_discount_type->fields['products_discount_type_from'] . "', products_mixed_discount_quantity='" . $check_discount_type->fields['products_mixed_discount_quantity'] . "' where products_id='" . (int)$copy_to . "'");

    $check_discount_query = "select * from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where products_id='" . (int)$copy_from . "' order by discount_id";
    $check_discount = $db->Execute($check_discount_query);
    $cnt_discount=1;
    while (!$check_discount->EOF) {
      $db->Execute("insert into " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
                  (discount_id, products_id, discount_qty, discount_price )
                  values ('" . (int)$cnt_discount . "', '" . (int)$copy_to . "', '" . $check_discount->fields['discount_qty'] . "', '" . $check_discount->fields['discount_price'] . "')");
      $cnt_discount++;
      $check_discount->MoveNext();
    }
  }


/**
 * Recursive algorithm to restrict all sub_categories of a specified category to a specified product_type
 */
  function zen_restrict_sub_categories($zf_cat_id, $zf_type) {
    global $db;
    $zp_sql = "select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$zf_cat_id . "'";
    $zq_sub_cats = $db->Execute($zp_sql);
    while (!$zq_sub_cats->EOF) {
      $zp_sql = "select * from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                         where category_id = '" . (int)$zq_sub_cats->fields['categories_id'] . "'
                         and product_type_id = '" . (int)$zf_type . "'";

      $zq_type_to_cat = $db->Execute($zp_sql);

      if ($zq_type_to_cat->RecordCount() < 1) {
        $za_insert_sql_data = array('category_id' => (int)$zq_sub_cats->fields['categories_id'],
                                    'product_type_id' => (int)$zf_type);
        zen_db_perform(TABLE_PRODUCT_TYPES_TO_CATEGORY, $za_insert_sql_data);
      }
      zen_restrict_sub_categories($zq_sub_cats->fields['categories_id'], $zf_type);
      $zq_sub_cats->MoveNext();
    }
  }


/**
 * Recursive algorithm to UNDO restriction from all sub_categories of a specified category for a specified product_type
 */
  function zen_remove_restrict_sub_categories($zf_cat_id, $zf_type) {
    global $db;
    $zp_sql = "select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$zf_cat_id . "'";
    $zq_sub_cats = $db->Execute($zp_sql);
    while (!$zq_sub_cats->EOF) {
        $sql = "delete from " .  TABLE_PRODUCT_TYPES_TO_CATEGORY . "
                where category_id = '" . (int)$zq_sub_cats->fields['categories_id'] . "'
                and product_type_id = '" . (int)$zf_type . "'";

        $db->Execute($sql);
      zen_remove_restrict_sub_categories($zq_sub_cats->fields['categories_id'], $zf_type);
      $zq_sub_cats->MoveNext();
    }
  }
  
