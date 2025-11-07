<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 16 Modified in v2.2.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (isset($_GET['pID'])) {
  $products_id = zen_db_prepare_input($_GET['pID']);
}

$redirect_page = (isset($_GET['page'])) ? '&page=' . $_GET['page'] : '';
$redirect_search = (isset($_POST['search'])) ? '&search=' . zen_preserve_search_quotes($_POST['search']) : '';

if (isset($_POST['edit']) && $_POST['edit'] === 'edit') {
    $action = 'new_product';
} elseif (($_POST['products_model'] ?? '') . implode('', $_POST['products_url'] ?? []) . implode('', $_POST['products_name'] ?? []) . implode('', $_POST['products_description'] ?? []) === '') {
    $messageStack->add_session(ERROR_NO_DATA_TO_SAVE, 'error');
    zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . $redirect_page . $redirect_search));
} else {
    $products_date_available = zen_db_prepare_input($_POST['products_date_available']);
    if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($products_date_available)) {
        $local_fmt = zen_datepicker_format_fordate();
        $dt = DateTime::createFromFormat($local_fmt, $products_date_available);
        $products_date_available = 'null';
        if (!empty($dt)) {
            $products_date_available = $dt->format('Y-m-d');
        }
    }
    $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

    if (!empty($products_id)) {
        $zco_notifier->notify('NOTIFY_MODULES_UPDATE_PRODUCT_START', ['action' => $action, 'products_id' => $products_id]);
    }

    // Data-cleaning to prevent data-type mismatch errors:
    $products_price_w = $_POST['products_price_w'] ?? '0';  //- Won't be present if wholesale pricing is disabled for the site!
    $products_price_w = ($products_price_w !== '') ? $products_price_w : '0';   //- Don't allow a blank entry
    $sql_data_array = [
        'products_quantity' => convertToFloat($_POST['products_quantity']),
        'products_type' => (int)$_POST['product_type'],
        'products_model' => zen_db_prepare_input($_POST['products_model']),
        'products_mpn' => zen_db_prepare_input($_POST['products_mpn'] ?? ''),
        'products_price' => convertToFloat($_POST['products_price']),
        'products_price_w' => zen_db_prepare_input($products_price_w),
        'products_date_available' => $products_date_available,

        'products_weight' => convertToFloat($_POST['products_weight']),
        'products_length' => convertToFloat($_POST['products_length']),
        'products_width' => convertToFloat($_POST['products_width']),
        'products_height' => convertToFloat($_POST['products_height']),
        'product_ships_in_own_box' => (int)($_POST['product_ships_in_own_box'] ?? 0),

        'products_status' => ($products_date_available === 'null') ? (int)$_POST['products_status'] : 0,
        'products_virtual' => (int)$_POST['products_virtual'],
        'products_tax_class_id' => (int)$_POST['products_tax_class_id'],
        'manufacturers_id' => (int)$_POST['manufacturers_id'],
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
    ];

    $db_filename = zen_limit_image_filename($_POST['products_image'], TABLE_PRODUCTS, 'products_image');
    $sql_data_array['products_image'] = zen_db_prepare_input($db_filename);
    $new_image = 'true';

    // when set to none remove from database
    // is out dated for browsers use radio only
    if (isset($_POST['image_delete']) && $_POST['image_delete'] === '1') {
        $sql_data_array['products_image'] = '';
        $new_image = 'false';
    }

    if ($action === 'insert_product') {
        $sql_data_array['products_date_added'] = 'now()';
        $sql_data_array['master_categories_id'] = (int)$current_category_id;

        zen_db_perform(TABLE_PRODUCTS, $sql_data_array);
        $products_id = zen_db_insert_id();

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter($products_id);

        $db->Execute(
            "INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . "
                (products_id, categories_id)
             VALUES
                (" . (int)$products_id . ", " . (int)$current_category_id . ")"
        );

        zen_record_admin_activity('New product ' . (int)$products_id . ' added via admin console.', 'info');

        ///////////////////////////////////////////////////////
        //// INSERT PRODUCT-TYPE-SPECIFIC *INSERTS* HERE //////
        ////    *END OF PRODUCT-TYPE-SPECIFIC INSERTS* ////////
        ///////////////////////////////////////////////////////
    } elseif ($action === 'update_product') {
        $sql_data_array['products_last_modified'] = 'now()';
        $sql_data_array['master_categories_id'] = (!empty($_POST['master_category']) && (int)$_POST['master_category'] > 0 ? (int)$_POST['master_category'] : (int)$_POST['master_categories_id']);

        zen_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', 'products_id = ' . (int)$products_id);

        zen_record_admin_activity('Updated product ' . (int)$products_id . ' via admin console.', 'info');

        // reset products_price_sorter for searches etc.
        zen_update_products_price_sorter((int)$products_id);

        ///////////////////////////////////////////////////////
        //// INSERT PRODUCT-TYPE-SPECIFIC *UPDATES* HERE //////

        $zco_notifier->notify('NOTIFY_ADMIN_UPDATE_PRODUCT_UPDATE', $products_id, $sql_data_array);

        ////    *END OF PRODUCT-TYPE-SPECIFIC UPDATES* ////////
        ///////////////////////////////////////////////////////
    }

    // Process additional images if any
    $additional_images = $_POST['additional_images'] ?? [];

    if (!empty($additional_images) && is_array($additional_images)) {
        // get sort order for existing additional images
        $next_sort_order = 0;
        $existing_sort = $db->Execute(
            "SELECT MAX(sort_order) AS max_sort_order
               FROM " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . "
              WHERE products_id = " . (int)$products_id
        );
        if ($existing_sort->RecordCount() > 0 && $existing_sort->fields['max_sort_order'] !== null) {
            $next_sort_order = (int)$existing_sort->fields['max_sort_order'] + 1;
        }

        // Insert each additional image
        foreach ($additional_images as $sort_order => $img) {
            if (!empty($img)) {
                $db->Execute(
                    "INSERT INTO " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " (products_id, additional_image, sort_order)
                 VALUES (" . (int)$products_id . ", '" . zen_db_input($img) . "', " . (int)$next_sort_order . ")"
                );
            }
            $next_sort_order++;
        }
    }
    // delete any additional images marked for removal
    if (isset($_POST['additional_image_delete']) && is_array($_POST['additional_image_delete'])) {
        foreach ($_POST['additional_image_delete'] as $img_id) {
            $img_id = (int)$img_id;
            if ($img_id > 0) {
                // get image filename before deleting record
                $img_to_delete = $db->Execute(
                    "SELECT additional_image FROM " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . "
                   WHERE id = " . $img_id . "
                     AND products_id = " . (int)$products_id
                );
                $img_name = $img_to_delete->fields['additional_image'];

                // check if not used by another product
                $img_to_delete = $db->Execute(
                    "SELECT id FROM " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . "
                   WHERE additional_image = (SELECT additional_image FROM " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . " WHERE id = " . $img_id . ")
                     AND products_id <> " . (int)$products_id
                );

                // delete file if not used by another product
                if ($img_to_delete->RecordCount() < 1) {
                    $img_file = DIR_FS_CATALOG_IMAGES . $img_name;
                    if (file_exists($img_file)) {
                        @unlink($img_file);
                    }
                } else {
                    // another product is using this image
                    $messageStack->add_session(TEXT_IMAGE_USED_BY_OTHER_PRODUCTS, 'warning');
                }

                // delete record for this product
                $db->Execute(
                    "DELETE FROM " . TABLE_PRODUCTS_ADDITIONAL_IMAGES . "
                   WHERE products_id = " . (int)$products_id . "
                     AND id = " . $img_id
                );
            }
        }
    }

    $languages = zen_get_languages();
    for ($i = 0, $n = count($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];

        $sql_data_array = [
          'products_name' => zen_db_prepare_input($_POST['products_name'][$language_id]),
          'products_description' => zen_db_prepare_input($_POST['products_description'][$language_id]),
          'products_url' => zen_db_prepare_input($_POST['products_url'][$language_id])
        ];

        // For database consistency, check whether a record exists for this language already; if not, we will create it even if we're in update mode
        $sql = "SELECT count(products_id) AS found FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE products_id = " . (int)$products_id . " AND language_id = " . (int)$language_id;
        $result = $db->Execute($sql, 1);
        $record_exists = !empty($result->fields['found']);

        if ($action === 'insert_product' || !$record_exists) {
            $insert_sql_data = [
                'products_id' => (int)$products_id,
                'language_id' => (int)$language_id,
            ];

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
        } elseif ($action === 'update_product') {
            zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', 'products_id = ' . (int)$products_id . ' AND language_id = ' . (int)$language_id);
        }
    }

    $zco_notifier->notify('NOTIFY_MODULES_UPDATE_PRODUCT_END', ['action' => $action, 'products_id' => $products_id]);

    zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . $redirect_page . $redirect_search));
}
