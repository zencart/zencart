<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: v4.5.1
//
function product_has_pos_attributes($products_id): bool
{
    global $db, $posObserver;

    // -----
    // Quick return if POSM isn't currently enabled.
    //
    if (!$posObserver->enabled) {
        return false;
    }

    $and_clause = '';
    $products_id = (int)$products_id;
    if (POSM_OPTIONAL_OPTION_TYPES_LIST !== '') {
        $and_clause .= " AND po.products_options_type NOT IN (" . POSM_OPTIONAL_OPTION_TYPES_LIST . ")";
    }
    if (POSM_OPTIONAL_OPTION_NAMES_LIST !== '') {
        $and_clause .= " AND po.products_options_id NOT IN (" . POSM_OPTIONAL_OPTION_NAMES_LIST . ")";
    }
    $check = $db->Execute(
        "SELECT pa.products_attributes_id
           FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " po
                    ON po.products_options_id = pa.options_id
                   AND po.products_options_type IN (" . POSM_OPTIONS_TYPES_TO_MANAGE . ")$and_clause
          WHERE pa.products_id = $products_id
          LIMIT 1"
    );
    return !$check->EOF;
}

function is_pos_product($products_id): bool
{
    global $db, $posObserver;

    // -----
    // Quick return if POSM isn't currently enabled.
    //
    if (!$posObserver->enabled) {
        return false;
    }

    $products_id = (int)$products_id;
    $check = $db->Execute(
        "SELECT pos_id
           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
          WHERE products_id = $products_id
          LIMIT 1"
    );
    return !$check->EOF;
}

function generate_pos_option_hash($pID, $options_array): string
{
    global $db, $posObserver;

    // -----
    // Quick return if POSM isn't currently enabled.
    //
    if (!$posObserver->enabled) {
        return '';
    }

    $optional_options_ids = [];
    if (POSM_OPTIONAL_OPTION_NAMES_LIST !== '') {
        $optional_options_ids = explode(',', POSM_OPTIONAL_OPTION_NAMES_LIST);
    }
    $and_clause = (POSM_OPTIONAL_OPTION_TYPES_LIST !== '') ? (" AND products_options_type NOT IN (" . POSM_OPTIONAL_OPTION_TYPES_LIST . ")") : '';

    ksort($options_array);
    $hash_in = (int)$pID;
    foreach ($options_array as $key => $value) {
        $key = (string)$key;
        if (strpos($key, '_chk') !== false) {
            $checkbox_options = explode('_chk', $key);
            $key = $checkbox_options[0];
            $value = $checkbox_options[1];
        } elseif (strpos($key, TEXT_PREFIX) !== false) {
            if ($value == '') {
                continue;
            }
            $key = str_replace(TEXT_PREFIX, '', $key);
        }
        if (!in_array($key, $optional_options_ids)) {
            $check = $db->Execute(
                "SELECT products_options_id
                   FROM " . TABLE_PRODUCTS_OPTIONS . "
                  WHERE products_options_id = $key $and_clause
                  LIMIT 1"
            );
            if (!$check->EOF) {
                $hash_in .= ($key . $value);
            }
        }
    }
    $hash_out = hash('md5', $hash_in);
    $posObserver->debug_message(
        "products_options_stock_functions: $hash_out = generate_pos_option_hash($pID, options_array\n" .
        json_encode($options_array, JSON_PRETTY_PRINT) . "\n" .
        json_encode($optional_options_ids, JSON_PRETTY_PRINT)
    );
    return $hash_out;
}

function get_pos_oos_name($pos_name_id, $language_id)
{
    global $db, $posObserver;

    // -----
    // Quick return if POSM isn't currently enabled.
    //
    if (!$posObserver->enabled) {
        return false;
    }

    $pos_name_id = (int)$pos_name_id;
    $language_id = (int)$language_id;
    $name_info = $db->Execute(
        "SELECT pos_name
           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . "
          WHERE pos_name_id = $pos_name_id
            AND language_id = $language_id
            LIMIT 1"
    );
    return ($name_info->EOF) ? false : $name_info->fields['pos_name'];
}
