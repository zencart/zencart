<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM v4.5.1
//
// NOTE: These functions, for POSM versions prior to v4.4.0, were previously part of the
// main tool (/admin/products_options_stock.php).
//

// -----
// Retrieve a collection of stock-managed values for the given product/option pair.
//
// Using LPAD on the sort-order since the sort-order+values-name could be used as the
// sort; ensuring that 10 > 1.
//
/**
 * @param $pID
 * @param $options_id
 *
 * @return queryFactoryResult
 */
function get_pos_options_values($pID, $options_id)
{
    global $db;

    $pID = (int)$pID;
    $options_id = (int)$options_id;
    return $db->Execute(
        "SELECT pov.products_options_values_name as `text`, pov.products_options_values_id as `id`, LPAD(pa.products_options_sort_order, 11, '0') as sort_order
           FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                    ON pa.options_values_id = pov.products_options_values_id
                   AND pa.products_id = $pID
                   AND pa.options_id = $options_id
                   AND pa.attributes_display_only = 0
          WHERE pov.language_id = " . $_SESSION['languages_id'] . "
       ORDER BY sort_order ASC, pov.products_options_values_name ASC"
    );
}

// -----
// Retrieve a collection of stock-managed options for the given product.
//
// Using LPAD on the sort-order since the sort-order+options-name could be used as the
// sort; ensuring that 10 > 1.
//
/**
 * @param $pID
 *
 * @return queryFactoryResult
 */
function get_pos_options($pID)
{
    global $db;

    $pID = (int)$pID;
    $and_clause = '';
    if (POSM_OPTIONAL_OPTION_TYPES_LIST !== '') {
        $and_clause .= " AND po.products_options_type NOT IN (" . POSM_OPTIONAL_OPTION_TYPES_LIST . ")";
    }
    if (POSM_OPTIONAL_OPTION_NAMES_LIST !== '') {
        $and_clause .= " AND po.products_options_id NOT IN (" . POSM_OPTIONAL_OPTION_NAMES_LIST . ")";
    }
    return $db->Execute(
        "SELECT DISTINCT pa.options_id, po.products_options_name as options_name, LPAD(po.products_options_sort_order, 11, '0') as sort_order
           FROM " . TABLE_PRODUCTS_OPTIONS . " po
                INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                    ON pa.options_id = po.products_options_id
                   AND pa.products_id = $pID
          WHERE po.products_options_type  IN (" . POSM_OPTIONS_TYPES_TO_MANAGE . ")$and_clause
            AND po.language_id = " . (int)$_SESSION['languages_id'] . "
       ORDER BY sort_order ASC, po.products_options_name ASC"
    );
}

/**
 * @param $products_id
 * @param $options_id
 * @param $varname
 * @param $default
 * @param $include_all
 *
 * @return string
 */
function draw_option_pulldown($products_id, $options_id, $varname, $default = '', $include_all = true)
{
    $products_id = (int)$products_id;
    $options_id = (int)$options_id;
    $options_values = get_pos_options_values($products_id, $options_id);
    $options = [];
    foreach ($options_values as $next_value) {
        $options[] = $next_value;
    }

    if (count($options) === 0) {
        $return_val = '';
    } else {
        if ($include_all === true) {
            array_unshift($options, ['id' => 0, 'text' => TEXT_ALL]);
        }
        $return_val = zen_draw_pull_down_menu($varname, $options, $default, 'class="oSelect form-control input-sm" data-ocount="' . (count($options) - 1) . '"');
    }
    return $return_val;
}

/**
 * @param $options
 *
 * @return array|array[]
 */
function build_all_options_array($options)
{
    end($options);  // Position at end of array to pull the to-be-removed element's data
    $options_id = key($options);
    $options_values = current($options);

    if (array_pop($options) === null) {
        $return_array = [[]];
    } else {
        $current_option_array = build_all_options_array($options);

        $return_array = [];
        foreach ($options_values as $current_value) {
            $current_option_value = [];
            $current_option_value[$options_id] = $current_value;
            foreach ($current_option_array as $option_array_entry) {
                $return_array[] = $option_array_entry + $current_option_value;
            }
        }
    }
    return $return_array;
}

/**
 * @param $pID
 * @param $quantity
 * @param $pos_name_id
 * @param $options_values_array
 * @param $replace_quantity
 *
 * @return void
 */
function insert_stock_option($pID, $quantity, $pos_name_id, $options_values_array, $replace_quantity)
{
    global $db, $messageStack, $zco_notifier;

    $pID = (int)$pID;
    $quantity = (float)$quantity;
    $pos_name_id = (int)$pos_name_id;
    $hash = generate_pos_option_hash($pID, $options_values_array);
    $check = $db->Execute(
        "SELECT pos_id, products_quantity
           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
          WHERE products_id = $pID
            AND pos_hash = '$hash'
          LIMIT 1"
    );
    if (!$check->EOF) {
        if (!$replace_quantity) {
            $quantity = $check->fields['products_quantity'] + $quantity;
        }
        if ($quantity < 0) {
            $quantity = 0;
        }
        $db->Execute(
            "UPDATE " . TABLE_PRODUCTS_OPTIONS_STOCK . "
               SET products_quantity = $quantity, last_modified = now()
             WHERE pos_id = " . $check->fields['pos_id']
        );

    } else {
        if ($quantity < 0) {
            $quantity = 0;
        }
        $db->Execute(
            "INSERT INTO " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                (products_id, products_quantity, pos_name_id, pos_hash, last_modified)
             VALUES
                ($pID, $quantity, $pos_name_id, '$hash', now())"
        );
        $pos_id = $db->insert_ID();
        foreach ($options_values_array as $options_id => $options_values_id) {
            $db->Execute(
                "INSERT INTO " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                    (pos_id, products_id, options_id, options_values_id)
                 VALUES
                    ($pos_id, $pID, $options_id, $options_values_id)"
            );
        }

        // -----
        // POSM v4.2.2+: Let an observer 'know' that a managed option has just been added.
        //
        // POSM 4.4.0+, adding a notifier prefixed with 'NOTIFY_' to meet the zc158 requirements
        // for auto-loaded observers.  Note that the legacy notification will be removed in a future
        // version of POSM.
        //
        $zco_notifier->notify(
            'POSM_MAIN_OPTION_INSERTED',
            [
                'pID' => $pID,
                'options_values_array' => $options_values_array,
                'quantity' => $quantity,
                'pos_id' => $pos_id
            ]
        );
        $zco_notifier->notify(
            'NOTIFY_POSM_MAIN_OPTION_INSERTED',
            [
                'pID' => $pID,
                'options_values_array' => $options_values_array,
                'quantity' => $quantity,
                'pos_id' => $pos_id
            ]
        );
    }
}

/**
 * @param $pID
 * @param $reminder_date
 * @param $names_with_dates
 *
 * @return bool
 */
function posm_product_has_oos_options($pID, $reminder_date, $names_with_dates)
{
    global $db;

    if ($reminder_date === '') {
        $additional_clause = '';
    } else {
        $additional_clause = " OR (pos_name_id IN ($names_with_dates) AND pos_date < '$reminder_date')";
    }
    $check = $db->Execute(
        "SELECT products_quantity
           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
          WHERE products_id = " . (int)$pID . "
            AND ( products_quantity <= " . (int)POSM_STOCK_REORDER_LEVEL . "$additional_clause )
         LIMIT 1"
    );
    return !$check->EOF;
}

/**
 * @param $name
 * @param $values
 * @param $default
 * @param $parameters
 * @param $required
 *
 * @return string
 */
function posm_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false)
{
    $field = '<select rel="dropdown" name="' . zen_output_string($name) . '"';
    if ($parameters !== '') {
        $field .= ' ' . $parameters;
    }
    $field .= '>' . "\n";

    if (empty($default) && isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
        $default = stripslashes($GLOBALS[$name]);
    }

    foreach ($values as $current_value) {
        $field .= '<option value="' . zen_output_string($current_value['id']) . '"';
        if (!empty($current_value['option_parms'])) {
            $field .= ' ' . $current_value['option_parms'];
        }
        if ((int)$default === (int)$current_value['id']) {
            $field .= ' selected="selected"';
        }
        $field .= '>' . htmlspecialchars($current_value['text'], ENT_COMPAT, CHARSET, false) . '</option>' . "\n";
    }
    $field .= '</select>' . "\n";

    if ($required === true) {
        $field .= TEXT_FIELD_REQUIRED;
    }
    return $field;
}

/**
 * Indicates, via true/false return, whether the input string is a numeric
 * value for a quantity or price value.
 *
 * Added for POSM 4.4.0
 *
 * @param $value
 *
 * @return bool
 */
function posm_is_numeric_string(string $value): bool
{
    return (preg_match('/^\d+\.?\d*$/', $value) === 1);
}
