<?php

/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Mon Nov 12 20:38:09 2018 -0500 New in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
    $products_id = (int)$_POST['products_id'];
    $categories_id = (int)$_POST['categories_id'];

    if ($_POST['copy_as'] == 'link') {
        if ($categories_id != $current_category_id) {
            $check = $db->Execute("SELECT COUNT(*) AS total
                                   FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                   WHERE products_id = " . $products_id . "
                                   AND categories_id = " . $categories_id);
            if ($check->fields['total'] < '1') {
                $db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
                              VALUES (" . $products_id . ", " . $categories_id . ")");

                zen_record_admin_activity('Product ' . $products_id . ' copied as link to category ' . $categories_id . ' via admin console.', 'info');
            }
        } else {
            $messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
        }
    } elseif ($_POST['copy_as'] == 'duplicate') {

        $product = $db->Execute("SELECT products_type, products_quantity, products_model, products_image,
                                    products_price, products_virtual, products_date_available, products_weight,
                                    products_tax_class_id, manufacturers_id,
                                    products_quantity_order_min, products_quantity_order_units, products_priced_by_attribute,
                                    product_is_free, product_is_call, products_quantity_mixed,
                                    product_is_always_free_shipping, products_qty_box_status, products_quantity_order_max, products_sort_order,
                                    products_price_sorter, master_categories_id
                             FROM " . TABLE_PRODUCTS . "
                             WHERE products_id = " . $products_id);

        // fix Product copy from if Unit is 0
        if ($product->fields['products_quantity_order_units'] == 0) {
            $sql = "UPDATE " . TABLE_PRODUCTS . "
                    SET products_quantity_order_units = 1
                    WHERE products_id = " . $products_id;
            $results = $db->Execute($sql);
        }
        // fix Product copy from if Minimum is 0
        if ($product->fields['products_quantity_order_min'] == 0) {
            $sql = "UPDATE " . TABLE_PRODUCTS . "
                    SET products_quantity_order_min = 1
                    WHERE products_id = " . $products_id;
            $results = $db->Execute($sql);
        }

        $tmp_value = zen_db_input($product->fields['products_quantity']);
        $products_quantity = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value == 0) ? 0 : $tmp_value;
        $tmp_value = zen_db_input($product->fields['products_price']);
        $products_price = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value == 0) ? 0 : $tmp_value;
        $tmp_value = zen_db_input($product->fields['products_weight']);
        $products_weight = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value == 0) ? 0 : $tmp_value;

        $db->Execute("INSERT INTO " . TABLE_PRODUCTS . " (products_type, products_quantity, products_model, products_image,
                                                      products_price, products_virtual, products_date_added, products_date_available,
                                                      products_weight, products_status, products_tax_class_id,
                                                      manufacturers_id, products_quantity_order_min, products_quantity_order_units,
                                                      products_priced_by_attribute, product_is_free, product_is_call, products_quantity_mixed,
                                                      product_is_always_free_shipping, products_qty_box_status, products_quantity_order_max,
                                                      products_sort_order, products_price_sorter, master_categories_id)
                  VALUES ('" . zen_db_input($product->fields['products_type']) . "',
                          '" . $products_quantity . "',
                          '" . zen_db_input($product->fields['products_model']) . "',
                          '" . zen_db_input($product->fields['products_image']) . "',
                          '" . $products_price . "',
                          '" . zen_db_input($product->fields['products_virtual']) . "',
                          now(),
                          " . (zen_not_null(zen_db_input($product->fields['products_date_available'])) ? "'" . zen_db_input($product->fields['products_date_available']) . "'" : 'null') . ",
                          '" . $products_weight . "', '0',
                          '" . (int)$product->fields['products_tax_class_id'] . "',
                          '" . (int)$product->fields['manufacturers_id'] . "',
                          '" . zen_db_input(($product->fields['products_quantity_order_min'] == 0 ? 1 : $product->fields['products_quantity_order_min'])) . "',
                          '" . zen_db_input(($product->fields['products_quantity_order_units'] == 0 ? 1 : $product->fields['products_quantity_order_units'])) . "',
                          '" . zen_db_input($product->fields['products_priced_by_attribute']) . "',
                          '" . (int)$product->fields['product_is_free'] . "',
                          '" . (int)$product->fields['product_is_call'] . "',
                          '" . (int)$product->fields['products_quantity_mixed'] . "',
                          '" . zen_db_input($product->fields['product_is_always_free_shipping']) . "',
                          '" . zen_db_input($product->fields['products_qty_box_status']) . "',
                          '" . zen_db_input($product->fields['products_quantity_order_max']) . "',
                          '" . zen_db_input($product->fields['products_sort_order']) . "',
                          '" . zen_db_input($product->fields['products_price_sorter']) . "',
                          '" . zen_db_input($categories_id) . "')");

        $dup_products_id = (int)$db->Insert_ID();

// Music Media Copy
        if (isset($_POST['copy_media']) && ($_POST['copy_media'] == '1' || $_POST['copy_media'] == 'on')) {
            $product_media = $db->Execute("SELECT media_id
                                             FROM " . TABLE_MEDIA_TO_PRODUCTS . "
                                             WHERE product_id = " . (int)$products_id);
            foreach ($product_media as $item) {
                $db->Execute("INSERT INTO " . TABLE_MEDIA_TO_PRODUCTS . " (media_id, product_id)
                              VALUES ('" . $item['media_id'] . "',
                                      '" . $dup_products_id . "')");
                $product_media->MoveNext();
            }
        }

        $music_extra = $db->Execute("SELECT artists_id, record_company_id, music_genre_id
                                         FROM " . TABLE_PRODUCT_MUSIC_EXTRA . "
                                         WHERE products_id = " . (int)$products_id);

        $db->Execute("INSERT INTO " . TABLE_PRODUCT_MUSIC_EXTRA . " (products_id, artists_id, record_company_id, music_genre_id)
                          VALUES ('" . (int)$dup_products_id . "',
                                  '" . zen_db_input($music_extra->fields['artists_id']) . "',
                                  '" . zen_db_input($music_extra->fields['record_company_id']) . "',
                                  '" . zen_db_input($music_extra->fields['music_genre_id']) . "')");


        $descriptions = $db->Execute("SELECT language_id, products_name, products_description, products_url
                                      FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                                      WHERE products_id = " . $products_id);
        foreach ($descriptions as $description) {
            $db->Execute("INSERT INTO " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url, products_viewed)
                    VALUES ('" . $dup_products_id . "',
                            '" . (int)$description['language_id'] . "',
                            '" . zen_db_input($description['products_name']) . " " . TEXT_DUPLICATE_IDENTIFIER . "',
                            '" . zen_db_input($description['products_description']) . "',
                            '" . zen_db_input($description['products_url']) . "',
                            0)");
        }

        $db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
                      VALUES (" . $dup_products_id . ", " . $categories_id . ")");

// FIX HERE
/////////////////////////////////////////////////////////////////////////////////////////////

// copy attributes to Duplicate
        if (!empty($_POST['copy_attributes']) && $_POST['copy_attributes'] == 'copy_attributes_yes') {

            if (DOWNLOAD_ENABLED == 'true') {
                $copy_attributes_include_downloads = '1';
                $copy_attributes_include_filename = '1';
            } else {
                $copy_attributes_include_downloads = '0';
                $copy_attributes_include_filename = '0';
            }

            $copy_result = zen_copy_products_attributes($products_id, $dup_products_id);
            if ($copy_result) {
                $messageStack->add_session(sprintf(TEXT_COPY_AS_DUPLICATE_ATTRIBUTES, $products_id, $dup_products_id), 'success');
            }
        }

// copy meta tags to Duplicate
        if (!empty($_POST['copy_metatags']) && $_POST['copy_metatags'] == 'copy_metatags_yes') {
            $metatags_status = $db->Execute("SELECT metatags_title_status, metatags_products_name_status, metatags_model_status, metatags_price_status, metatags_title_tagline_status
                                             FROM " . TABLE_PRODUCTS . "
                                             WHERE products_id = '" . $products_id . "'");

            $db->Execute("UPDATE " . TABLE_PRODUCTS . " SET
                metatags_title_status = '" . zen_db_input($metatags_status->fields['metatags_title_status']). "',
                metatags_products_name_status = '" . zen_db_input($metatags_status->fields['metatags_products_name_status']). "',
                metatags_model_status = '" . zen_db_input($metatags_status->fields['metatags_model_status']). "',
                metatags_price_status= '" . zen_db_input($metatags_status->fields['metatags_price_status']). "',
                metatags_title_tagline_status = '" . zen_db_input($metatags_status->fields['metatags_title_tagline_status']). "'
                WHERE products_id = " . $dup_products_id);

            $metatags_descriptions = $db->Execute("SELECT language_id, metatags_title, metatags_keywords, metatags_description
                                                   FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                                                   WHERE products_id = " . $products_id);

            while (!$metatags_descriptions->EOF) {//one row per language
                $db->Execute("INSERT INTO " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " (products_id, language_id, metatags_title, metatags_keywords, metatags_description)
                        VALUES (
                        '" . $dup_products_id . "',
                        '" . (int)$metatags_descriptions->fields['language_id'] . "',
                        '" . zen_db_input($metatags_descriptions->fields['metatags_title']) . "',
                        '" . zen_db_input($metatags_descriptions->fields['metatags_keywords']) . "',
                        '" . zen_db_input($metatags_descriptions->fields['metatags_description']). "')");

                $messageStack->add_session(sprintf(TEXT_COPY_AS_DUPLICATE_METATAGS, (int)$metatags_descriptions->fields['language_id'], $products_id, $dup_products_id), 'success');

                $metatags_descriptions->MoveNext();
            }
        }

// copy linked categories to Duplicate
        if (!empty($_POST['copy_linked_categories']) && $_POST['copy_linked_categories'] == 'copy_linked_categories_yes') {
            $categories_from = $db->Execute("SELECT categories_id
                                             FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                             WHERE products_id = " . $products_id);

            foreach ($categories_from as $row) {
                //"insert ignore" as the new product already has one entry for the master category
                $db->Execute("INSERT IGNORE INTO " . TABLE_PRODUCTS_TO_CATEGORIES . "
                              (products_id, categories_id)
                              VALUES (" . $dup_products_id . ", " . (int)$row['categories_id'] . ")");
                $messageStack->add_session(sprintf(TEXT_COPY_AS_DUPLICATE_CATEGORIES, (int)$row['categories_id'], $products_id, $dup_products_id), 'success');
            }
        }

// copy product discounts to Duplicate
        if (!empty($_POST['copy_discounts']) && $_POST['copy_discounts'] == 'copy_discounts_yes') {
            zen_copy_discounts_to_product($products_id, $dup_products_id);
            $messageStack->add_session(sprintf(TEXT_COPY_AS_DUPLICATE_DISCOUNTS, $products_id, $dup_products_id), 'success');
        }

        zen_record_admin_activity('Product ' . $products_id . ' duplicated as product ' . $dup_products_id . ' via admin console.', 'info');

        $zco_notifier->notify('NOTIFY_PRODUCT_MUSIC_COPY_TO_CONFIRM_DUPLICATE', array('products_id' => $products_id, 'dup_products_id' => $dup_products_id));

        $products_id = $dup_products_id;//reset for further use in price update and final redirect to new linked product or new duplicated product
    }// EOF duplication

    // reset products_price_sorter for searches etc.
    zen_update_products_price_sorter($products_id);
}
zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $categories_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
