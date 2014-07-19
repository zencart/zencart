<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Jun 30 2014 Modified in v1.5.4 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
        if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
          $products_id = zen_db_prepare_input($_POST['products_id']);
          $categories_id = zen_db_prepare_input($_POST['categories_id']);

// Copy attributes to duplicate product
          $products_id_from=$products_id;

          if ($_POST['copy_as'] == 'link') {
            if ($categories_id != $current_category_id) {
              $check = $db->Execute("select count(*) as total
                                     from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                     where products_id = '" . (int)$products_id . "'
                                     and categories_id = '" . (int)$categories_id . "'");
              if ($check->fields['total'] < '1') {
                $db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                          (products_id, categories_id)
                              values ('" . (int)$products_id . "', '" . (int)$categories_id . "')");

                zen_record_admin_activity('Product ' . (int)$products_id . ' copied as link to category ' . (int)$categories_id . ' via admin console.', 'info');
              }
            } else {
              $messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
            }
          } elseif ($_POST['copy_as'] == 'duplicate') {
            $old_products_id = (int)$products_id;
            $product = $db->Execute("select products_type, products_quantity, products_model, products_image,
                                            products_price, products_virtual, products_date_available, products_weight,
                                            products_tax_class_id, manufacturers_id,
                                            products_quantity_order_min, products_quantity_order_units, products_priced_by_attribute,
                                            product_is_free, product_is_call, products_quantity_mixed,
                                            product_is_always_free_shipping, products_qty_box_status, products_quantity_order_max, products_sort_order,
                                            products_price_sorter, master_categories_id
                                     from " . TABLE_PRODUCTS . "
                                     where products_id = '" . (int)$products_id . "'");

            $tmp_value = zen_db_input($product->fields['products_quantity']);
            $products_quantity = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
            $tmp_value = zen_db_input($product->fields['products_price']);
            $products_price = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;
            $tmp_value = zen_db_input($product->fields['products_weight']);
            $products_weight = (!zen_not_null($tmp_value) || $tmp_value=='' || $tmp_value == 0) ? 0 : $tmp_value;

            $db->Execute("insert into " . TABLE_PRODUCTS . "
                                      (products_type, products_quantity, products_model, products_image,
                                       products_price, products_virtual, products_date_added, products_date_available,
                                       products_weight, products_status, products_tax_class_id,
                                       manufacturers_id,
                                       products_quantity_order_min, products_quantity_order_units, products_priced_by_attribute,
                                       product_is_free, product_is_call, products_quantity_mixed,
                                       product_is_always_free_shipping, products_qty_box_status, products_quantity_order_max, products_sort_order,
                                       products_price_sorter, master_categories_id
                                       )
                          values ('" . zen_db_input($product->fields['products_type']) . "',
                                  '" . $products_quantity . "',
                                  '" . zen_db_input($product->fields['products_model']) . "',
                                  '" . zen_db_input($product->fields['products_image']) . "',
                                  '" . $products_price . "',
                                  '" . zen_db_input($product->fields['products_virtual']) . "',
                                  now(),
                                  '" . (zen_not_null(zen_db_input($product->fields['products_date_available'])) ? zen_db_input($product->fields['products_date_available']) : '0001-01-01 00:00:00') . "',
                                  '" . $products_weight . "', '0',
                                  '" . (int)$product->fields['products_tax_class_id'] . "',
                                  '" . (int)$product->fields['manufacturers_id'] . "',
                                  '" . zen_db_input($product->fields['products_quantity_order_min']) . "',
                                  '" . zen_db_input($product->fields['products_quantity_order_units']) . "',
                                  '" . zen_db_input($product->fields['products_priced_by_attribute']) . "',
                                  '" . (int)$product->fields['product_is_free'] . "',
                                  '" . (int)$product->fields['product_is_call'] . "',
                                  '" . (int)$product->fields['products_quantity_mixed'] . "',
                                  '" . zen_db_input($product->fields['product_is_always_free_shipping']) . "',
                                  '" . zen_db_input($product->fields['products_qty_box_status']) . "',
                                  '" . zen_db_input($product->fields['products_quantity_order_max']) . "',
                                  '" . zen_db_input($product->fields['products_sort_order']) . "',
                                  '" . zen_db_input($product->fields['products_price_sorter']) . "',
                                  '" . zen_db_input($categories_id) .
                                  "')");

            $dup_products_id = $db->Insert_ID();

            $description = $db->Execute("select language_id, products_name, products_description,
                                                             products_url
                                         from " . TABLE_PRODUCTS_DESCRIPTION . "
                                         where products_id = '" . (int)$products_id . "'");
            while (!$description->EOF) {
              $db->Execute("insert into " . TABLE_PRODUCTS_DESCRIPTION . "
                                        (products_id, language_id, products_name, products_description,
                                         products_url, products_viewed)
                            values ('" . (int)$dup_products_id . "',
                                    '" . (int)$description->fields['language_id'] . "',
                                    '" . zen_db_input($description->fields['products_name']) . "',
                                    '" . zen_db_input($description->fields['products_description']) . "',
                                    '" . zen_db_input($description->fields['products_url']) . "', '0')");
              $description->MoveNext();
            }

            $db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . "
                          (products_id, categories_id)
                          values ('" . (int)$dup_products_id . "', '" . (int)$categories_id . "')");
            $products_id = $dup_products_id;
            $description->MoveNext();
// FIX HERE
/////////////////////////////////////////////////////////////////////////////////////////////
// Copy attributes to duplicate product
// moved above            $products_id_from=zen_db_input($products_id);
            $products_id_to= $dup_products_id;
            $products_id = $dup_products_id;

if ( $_POST['copy_attributes']=='copy_attributes_yes' and $_POST['copy_as'] == 'duplicate' ) {
  // $products_id_to= $copy_to_products_id;
  // $products_id_from = $pID;
//            $copy_attributes_delete_first='1';
//            $copy_attributes_duplicates_skipped='1';
//            $copy_attributes_duplicates_overwrite='0';

            if (DOWNLOAD_ENABLED == 'true') {
              $copy_attributes_include_downloads='1';
              $copy_attributes_include_filename='1';
            } else {
              $copy_attributes_include_downloads='0';
              $copy_attributes_include_filename='0';
            }

            zen_copy_products_attributes($products_id_from, $products_id_to);
}
// EOF: Attributes Copy on non-linked
/////////////////////////////////////////////////////////////////////

            // copy product discounts to duplicate
            if ($_POST['copy_discounts'] == 'copy_discounts_yes') {
              zen_copy_discounts_to_product($old_products_id, (int)$dup_products_id);
            }

            zen_record_admin_activity('Product ' . (int)$old_products_id . ' duplicated as product ' . (int)$dup_products_id . ' via admin console.', 'info');
          }

          // reset products_price_sorter for searches etc.
          zen_update_products_price_sorter($products_id);

        }
        zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $categories_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
