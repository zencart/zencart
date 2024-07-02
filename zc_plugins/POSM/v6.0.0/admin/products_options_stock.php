<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM v5.0.0
//
require 'includes/application_top.php';

// -----
// Define some pseudo-configuration items, allowing a store to provide default values for the "Include disabled"
// and "Include model-number" checkboxes.  These "might" be migrated to database configuration settings in the
// future.
//
// Each setting can be either 'true' or 'false'.
//
zen_define_default('POSM_INCLUDE_DISABLED_DEFAULT', 'false');
zen_define_default('POSM_INCLUDE_MODEL_DEFAULT', 'false');

// -----
// Ditto for the sort-by selection.  This value can be one of:
//
// 'default' ...... Sort by attributes' sort order.
// 'model-asc' .... Sort by the products' name/model A-Z
// 'model-desc' ... Sort by the products' name/model Z-A
//
zen_define_default('POSM_SORT_BY_DEFAULT', 'model-asc');

$languages = zen_get_languages();

// -----
// Load the functions needed by this tool.  For POSM versions prior to v4.4.0,
// these functions were included in this script!
//
require DIR_WS_FUNCTIONS . 'products_options_stock_admin_functions.php';

// -----
// Gather up the currently-defined names to be associated with product variants
// that are currently out-of-stock.
//
$pos_names_array = [];
$pos_names_date_array = [];
$pos_names_info = $db->Execute(
    "SELECT pos_name_id AS id, pos_name AS text
       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_NAMES . "
      WHERE language_id = " . (int)$_SESSION['languages_id'] . "
   ORDER BY pos_name_id"
);
foreach ($pos_names_info as $next_name) {
    $pos_names_array[] = $next_name;
    if ($posObserver->stringPos($next_name['text'], '[date]') !== false) {
        $pos_names_date_array[] = $next_name['id'];
    }
}
unset($pos_names_info);

// -----
// Starting with v4.1.0, the out-of-stock date column is not displayed if none of the out-of-stock
// labels require a date field, as an on-screen real-estate savings.  Similarly, if only one out-of-stock
// label is defined and that label doesn't require a [date], don't display that column.
//
$show_date_column = (count($pos_names_date_array) !== 0);
$show_oos_label_column = (count($pos_names_array) > 1 || $show_date_column === true);
$single_label_text = $pos_names_array[0]['text'];
$single_label_id = $pos_names_array[0]['id'];
if (((int)POSM_BIS_DATE_REMINDER === 0) || $show_date_column === false) {
    $reminder_date = '';
    $names_with_dates = '';
} else {
    $reminder_date = date('Y-m-d', strtotime('-' . (int)POSM_BIS_DATE_REMINDER . ' day'));
    $names_with_dates = implode(',', $pos_names_date_array);
}

// -----
// Determine the product (if any) that is currently selected.
//
$pID = isset($_POST['pID']) ? ((int)$_POST['pID']) : (isset($_GET['pID']) ? ((int)$_GET['pID']) : 0);
unset($_GET['pID']);

$quantity = $_POST['quantity'] ?? '0';
$pos_name = isset($_POST['pos_name']) ? ((int)$_POST['pos_name']) : $single_label_id;

$sort_by = $_GET['sort_by'] ?? $_SESSION['posm_sort_by'] ?? POSM_SORT_BY_DEFAULT;
$_SESSION['posm_sort_by'] = $sort_by;

$onload = '';
$action = (isset($_GET['action']) && $_GET['action'] === 'update') ? 'update' : '';
if ($action === 'update') {
    $error = false;
    $sub_action = $_POST['sub_action'] ?? '';
    switch ($sub_action) {
        case 'add':
        case 'replace':
        case 'insert':
            if (!isset($_POST['quantity']) || posm_is_numeric_string($_POST['quantity']) === false || $_POST['quantity'] < 0) {
                $messageStack->add(ERROR_INVALID_QUANTITY, 'error');
                $error = true;
                $onload = 'document.modify_form.quantity.focus();';
            } elseif (!is_array($_POST['option_values'])) {
                $messageStack->add(sprintf(ERROR_INVALID_FORM_VALUES, 1), 'error');
                $error = true;
            } else {
                $replace_quantities = ($sub_action === 'replace');
                $is_multiple_insert = false;
                foreach ($_POST['option_values'] as $option_id => $options_values_id) {
                    if ($options_values_id == 0) {
                        $is_multiple_insert = true;
                        break;
                    }
                }
                if ($is_multiple_insert === false) {
                    insert_stock_option($pID, $quantity, $pos_name, $_POST['option_values'], $replace_quantities);
                    $messageStack->add_session(SUCCESS_NEW_OPTION_CREATED, 'success');
                } else {
                    $options = [];
                    foreach ($_POST['option_values'] as $options_id => $options_value) {
                        if ($options_value != 0 ) {
                            $options[$options_id] = [$options_value];
                        } else {
                            $options_values_list = get_pos_options_values($_POST['pID'], $options_id);
                            $options_values = [];
                            foreach ($options_values_list as $next_value) {
                                $options_values[] = $next_value['id'];
                            }
                            $options[$options_id] = $options_values;
                        }
                    }
                    unset($options_values_list);

                    $all_options = build_all_options_array($options);
                    foreach ($all_options as $current_options_values) {
                        insert_stock_option($pID, $quantity, $pos_name, $current_options_values, $replace_quantities);
                    }
                    $messageStack->add_session(SUCCESS_NEW_OPTION_CREATED, 'success');
                }
            }
            break;

        case 'update':
            $pos_models = zen_db_prepare_input($_POST['pos_model']);

            // -----
            // Starting with v4.1.0, the Out-of-Stock Label and its associated date columns might not
            // be included.
            //
            $pos_names = zen_db_prepare_input($_POST['pos_names']);
            $pos_dates = zen_db_prepare_input($_POST['pos_date']);

            // -----
            // If the site's PHP configuration's 'max_input_size' or 'post_max_size' values are too
            // small, it's possible that the number of posted variables for the various arrays
            // are different (i.e. some values weren't gathered).  Check for the condition and
            // disallow any changes if the array sizes are different.
            //
            // The notifier has been updated (v3.2.0) to allow a watching observer to indicate
            // that there are missing values, too.  The second notifier was added in v4.1.0 to enable
            // an observer to indicate an error and any onload action.
            //
            // POSM 4.4.0+, adding a notifier prefixed with 'NOTIFY_' to meet the zc158 requirements
            // for auto-loaded observers.  The legacy notifications were removed in v5.0.0.
            //
            $qty_count = count($_POST['pos_quantity']);
            $missing_inputs_flag = false;
            $zco_notifier->notify('NOTIFY_POSM_UPDATE_PREPARE_INPUTS', $qty_count, $missing_inputs_flag, $error, $onload);

            $name_count = (is_array($pos_names)) ? count($pos_names) : $qty_count;
            $date_count = (is_array($pos_dates)) ? count($pos_dates) : $qty_count;
            if ($qty_count !== count($pos_models) || $qty_count !== $name_count || $qty_count !== $date_count) {
                $missing_inputs_flag = true;
            }
            if ($missing_inputs_flag === true) {
                $error = true;
                $post_max_size = @ini_get('post_max_size');
                $max_input_vars = @ini_get('max_input_vars');
                $messageStack->add(sprintf(ERROR_MISSING_INPUTS, (string)$post_max_size, (string)$max_input_vars), 'error');
            } elseif ($error === false) {
                $model_max_length = zen_field_length(TABLE_PRODUCTS_OPTIONS_STOCK, 'pos_model');
                foreach ($_POST['pos_quantity'] as $pos_id => $pos_quantity) {
                    $pos_id = (int)$pos_id;
                    if (posm_is_numeric_string($pos_quantity) === false) {
                        $messageStack->add(ERROR_INVALID_QUANTITY, 'error');
                        $error = true;
                        $onload = "document.modify_form['pos_quantity[$pos_id]'].focus();";
                        break;
                    }

                    if ($posObserver->stringLen($pos_models[$pos_id]) > $model_max_length) {
                        $messageStack->add(sprintf(ERROR_MODEL_TOO_LONG, $pos_models[$pos_id]), 'error');
                        $error = true;
                        $onload = "document.modify_form['pos_model[$pos_id]'].focus();";
                        break;
                    }

                    $model = zen_db_input($pos_models[$pos_id]);
                    if (POSM_DUPLICATE_MODELNUMS === 'Disallow' && posm_modelnum_is_duplicate($pos_id, $model)) {
                        $messageStack->add(sprintf(ERROR_DUPLICATE_MODEL_FOUND, $model));
                        $error = true;
                        $onload = "document.modify_form['pos_model[$pos_id]'].focus();";
                        break;
                    }

                    $name_id = (int)(is_array($pos_names)) ? $pos_names[$pos_id] : $pos_names;
                    if ($posObserver->stringPos(get_pos_oos_name($name_id, $_SESSION['languages_id']), '[date]') === false) {
                        $date = '0001-01-01';
                    } else {
                        $year = $posObserver->subString($pos_dates[$pos_id], 0, 4);
                        $month = $posObserver->subString($pos_dates[$pos_id], 5, 2);
                        $day = $posObserver->subString($pos_dates[$pos_id], 8, 2);
                        if ($pos_dates[$pos_id] === '0001-01-01' || checkdate((int)$month, (int)$day, (int)$year) === false) {
                            $messageStack->add(ERROR_INVALID_DATE, 'error');
                            $error = true;
                            $onload = "document.modify_form['pos_date[$pos_id]'].focus();";
                            break;
                        }
                        $date = "$year-$month-$day";
                    }

                    $update_posm_record_sql = [
                        'products_quantity' => $pos_quantity,
                        'pos_name_id' => $name_id,
                        'pos_date' => $date,
                        'pos_model' => $model,
                        'last_modified' => 'now()'
                    ];

                    // -----
                    // POSM 4.4.0+, adding a notifier prefixed with 'NOTIFY_' to meet the zc158 requirements
                    // for auto-loaded observers.  The legacy notifications were removed in v5.0.0
                    //
                    $zco_notifier->notify(
                        'NOTIFY_POSM_UPDATE_DATABASE_RECORD',
                        [
                            'pos_id' => $pos_id,
                            'pID' => $pID,
                        ],
                        $update_posm_record_sql,
                        $error,
                        $onload
                    );

                    // -----
                    // If an observer has indicated an issue with one of its data elements, break out of the foreach
                    // loop so that the unwanted data isn't recorded in the database.  The observer is "presumed" to
                    // have set a message in the messageStack indicating the issue and set the $onload variable to position
                    // the cursor on the offending data.
                    //
                    if ($error === true) {
                        break;
                    }
                    zen_db_perform(TABLE_PRODUCTS_OPTIONS_STOCK, $update_posm_record_sql, 'update', "pos_id = $pos_id LIMIT 1");
                }
            }

            if ($error === false) {
                $messageStack->add_session(SUCCESS_QUANTITY_UPDATED, 'success');
            }
            break;

        case 'remove':
            if (isset($_POST['pos_remove']) && is_array($_POST['pos_remove'])) {
                foreach ($_POST['pos_remove'] as $pos_id => $selected) {
                    $pos_id = (int)$pos_id;
                    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . " WHERE pos_id = $pos_id");
                    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " WHERE pos_id = $pos_id");
                }

                // -----
                // POSM 4.4.0+, adding a notifier prefixed with 'NOTIFY_' to meet the zc158 requirements
                // for auto-loaded observers.  The legacy notifications were removed in v5.0.0
                //
                $zco_notifier->notify('NOTIFY_POSM_REMOVE_OPTIONS', $pID);

                $messageStack->add_session(sprintf(SUCCESS_OPTION_RECORDS_REMOVED, count($_POST['pos_remove'])), 'success');
            }
            break;

        case 'insert_options':
            $pos_records = $db->Execute(
                "SELECT pos_id
                   FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                  WHERE products_id = $pID"
            );
            $posa_sql = ['products_id' => $pID];
            foreach ($pos_records as $next_record) {
                $posa_sql['pos_id'] = $next_record['pos_id'];
                foreach ($_POST['option_values'] as $options_id => $options_values_id) {
                    $posa_sql['options_id'] = $options_id;
                    $posa_sql['options_values_id'] = $options_values_id;
                    zen_db_perform(TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES, $posa_sql);
                }

                $pos_attributes = $db->Execute(
                    "SELECT options_id, options_values_id
                       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
                      WHERE pos_id = " . $next_record['pos_id']
                );
                $attributes = [];
                foreach ($pos_attributes as $next_attr) {
                    $attributes[$next_attr['options_id']] = $next_attr['options_values_id'];
                }
                $attributes = array_merge($attributes, $_POST['option_values']);

                $db->Execute(
                    "UPDATE " . TABLE_PRODUCTS_OPTIONS_STOCK . "
                        SET pos_hash = '" . generate_pos_option_hash($pID, $attributes) . "',
                            last_modified = now()
                      WHERE pos_id = " . $next_record['pos_id'] . "
                      LIMIT 1"
                );
            }
            $messageStack->add_session(SUCCESS_OPTIONS_ADDED, 'success');
            break;

        default:
            break;
    }

    // -----
    // Update the product's base/aggregate quantity to be the sum of all current variants' quantities.
    // This action is performed regardless of whether/not an error has been detected, since one or
    // more variant-quantities might have been updated before an error was detected.
    //
    posm_update_base_product_quantity($pID);

    // -----
    // If no error in the update/insert/remove processing, redirect back to refresh the page.
    //
    if ($error === false) {
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK, zen_get_all_get_params(['pID', 'action']) . "pID=$pID"));
    }
}

if (isset($_GET['category_id'])) {
    $current_category_id = (int)$_GET['category_id'];
} elseif (isset($_SESSION['posm_category_id'])) {
    $current_category_id = (int)$_SESSION['posm_category_id'];
} else {
    $current_category_id = -1;  //-"Please select" used, if not previously set.
}
$_SESSION['posm_category_id'] = $current_category_id;

// -----
// Determine whether/not the store has overridden the display of the "Include disabled" and/or
// "Include model-number" checkboxes (see top of this script for details).
//
if (isset($_GET['disabled'])) {
    $include_disabled = ($_GET['disabled'] === 'true');
} elseif (isset($_SESSION['posm_include_disabled'])) {
    $include_disabled = $_SESSION['posm_include_disabled'];
} else {
    $include_disabled = (POSM_INCLUDE_DISABLED_DEFAULT === 'true');
}
$_SESSION['posm_include_disabled'] = $include_disabled;
$include_disabled_sql = ($include_disabled) ? '' : ' WHERE p.products_status = 1';

if (isset($_GET['use_model'])) {
    $include_model = ($_GET['use_model'] === 'true');
} elseif (isset($_SESSION['posm_include_model'])) {
    $include_model = $_SESSION['posm_include_model'];
} else {
    $include_model = (POSM_INCLUDE_MODEL_DEFAULT === 'true');
}
$_SESSION['posm_include_model'] = $include_model;

// ----
// Determine the name selection and products' sort-order, based on whether/not the model
// number is to be included.
//
if ($include_model) {
    $select_name = "CONCAT(p.products_model,' - ', pd.products_name)";
    $order_by = 'p.products_model ASC, pd.products_name ASC, p.products_id ASC';
} else {
    $select_name = 'pd.products_name';
    $order_by = 'pd.products_name ASC, p.products_id ASC';
}
if ($current_category_id === 0) {
    $products_list = $db->Execute(
        "SELECT $select_name as `text`, p.products_model, pd.products_id as `id`, p.products_quantity, p.products_status
           FROM " . TABLE_PRODUCTS . " p
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    ON pd.products_id = p.products_id
                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
          $include_disabled_sql
       ORDER BY $order_by"
    );
} elseif ($current_category_id !== -1) {
    $products_list = $db->Execute(
        "SELECT $select_name as `text`, p.products_model, pd.products_id as `id`, p.products_quantity, p.products_status
           FROM " . TABLE_PRODUCTS . " p
                INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
                    ON ptc.categories_id = $current_category_id
                   AND ptc.products_id = p.products_id
                INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    ON pd.products_id = ptc.products_id
                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
          $include_disabled_sql
       ORDER BY $order_by"
    );
}

// -----
// Build up the list of products to be displayed, so long as the admin has currently chosen
// the category to be displayed.  That product list is restricted to products with
// POSM-manageable attributes (as possibly restricted via POSM's configuration settings).
//
$products_select = [];
if ($current_category_id !== -1) {
    foreach ($products_list as $next_product) {
        if (product_has_pos_attributes($next_product['id'])) {
            if ($pID === 0) {
                $pID = (int)$next_product['id'];
            }
            if (is_pos_product($next_product['id'])) {
                $next_product['text'] .= (' ' . TEXT_POS_IDENTIFIER);
            }
            if ((int)$next_product['products_status'] === 0) {
                $next_product['text'] .= TEXT_PRODUCT_DISABLED_IDENTIFIER;
            }
            $next_product['option_parms'] = (posm_product_has_oos_options($next_product['id'], $reminder_date, $names_with_dates)) ? 'class="out-of-stock"' : '';
            $products_select[] = $next_product;
        }
    }
    unset($products_list);
}

// -----
// Check for invalid values in the POSM_STOCK_REORDER_LEVEL setting (it should contain only digits 0-9) and reset it to 0 if found invalid.
//
$posm_stock_reorder_level = preg_replace("/[^0-9]/", '', POSM_STOCK_REORDER_LEVEL);
if ($posm_stock_reorder_level !== POSM_STOCK_REORDER_LEVEL) {
    $db->Execute(
        "UPDATE " . TABLE_CONFIGURATION . "
            SET configuration_value = '0'
          WHERE configuration_key = 'POSM_STOCK_REORDER_LEVEL'
          LIMIT 1"
    );
    $messageStack->add(sprintf(CAUTION_POSM_REORDER_LEVEL, POSM_STOCK_REORDER_LEVEL), 'caution');
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
<?php
$model_num_width = (POSM_ADMIN_MODEL_WIDTH === '') ? '' : ('width: ' . POSM_ADMIN_MODEL_WIDTH . ';');
?>
    <style>
input[type="text"].model-num {
    font-family: "Courier New", Courier, monospace;
    font-size: 12px;
    display: inline-block;
    <?= $model_num_width ?>
}
input[type="text"].date {
    width: 8em;
}
input[type="text"].quantity {
    width: 4em;
    display: inline-block;
}
hr {
    margin: 1em auto;
    border-top: 1px solid #ddd;
}
#no-products {
    font-size: larger;
    font-weight: bold;
    text-align: center;
}
.hoverRow:hover {
    background-color: #dcdcdc;
}
.removed, .removed td {
    text-decoration: line-through;
    color: #6666ff!important;
}
.centered {
    text-align: center;
}
table > tbody > tr.dataTableHeadingRow > td {
    background-color: <?= POSM_DIVIDER_COLOR ?>;
}
.disabled, .out-of-stock, .dup-model {
    color: red;
}
.version, .smaller {
    font-size: smaller;
}
.duplicate, input.lowstock, span.lowstock {
    border: 1px solid red;
}
.no-date, .d-none {
    display: none;
}
.input-xs {
    height: 22px;
    padding: 2px 5px;
    font-size: 12px;
    line-height: 1.5; /* If Placeholder of the input is moved up, rem/modify this. */
    border-radius: 3px;
}
.d-inline-block {
    display: inline-block;
}
    </style>
<?php
// -----
// The updated notification enables observers to make changes to the onload
// action for the page's view as well as providing additional CSS and JS to
// be applied for the page.
//
// Each of the CSS/JS additions are specified as strings; the content of the
// returned string is echoed here in a <style> or <script> tag, respectively.
//
// Notes:
// 1) An alternative to the use of this notification is to include add-on specific
//    CSS/JS content via separate files present in the admin's /includes/css and
//    /includes/javascript sub-directories, respectively.  Files' names to be loaded
//    for this page should start with 'products_options_stock_' for the
//    files to be auto-loaded.
// 2) Since this javascript is loaded *prior to* the load of the jQuery base, any JS content
//    must be PURE javascript.  Any jQuery content required by the POSM add-on
//    for this page should be placed in the /includes/javascript sub-directory, as
//    identified above.
//
$css_content = '';
$js_content = '';
$zco_notifier->notify('NOTIFY_POSM_INSERT_HEAD', '', $onload, $css_content, $js_content);
if ($css_content !== '') {
?>
    <style><?= $css_content ?></style>
<?php
}
if ($js_content !== '') {
?>
    <script><?= $js_content ?></script>
<?php
}
?>
</head>
<body <?= $onload ?>>

<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<!-- body //-->
<?php
$options = get_pos_options($pID);
$product_options = [];
$product_options_sort = [];
foreach ($options as $next_option) {
    $options_id = $next_option['options_id'];
    $product_options[$options_id] = $next_option['options_name'];
    $product_options_sort[$options_id] = [
        'sort_order' => $next_option['sort_order'] . $next_option['options_name'],
        'values' => []
    ];

    $options_values = get_pos_options_values($pID, $options_id);
    foreach ($options_values as $next_value) {
        $product_options_sort[$options_id]['values'][$next_value['id']] = $next_value['sort_order'] . $next_value['text'];
    }
}
unset($options, $options_values);

$options_removed_array = [];
$options_added_array = [];
$pos_product_options = [];
$pos_options = $db->Execute(
    "SELECT *
       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
      WHERE products_id = $pID"
);
foreach ($pos_options as $next_option) {
    $pos_id = $next_option['pos_id'];
    $pos_attributes = $db->Execute(
        "SELECT DISTINCT posa.options_id, posa.options_values_id
           FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . " posa
                INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                    ON pa.products_id = posa.products_id
                   AND pa.options_id = posa.options_id
                   AND pa.options_values_id = posa.options_values_id
                INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " po
                    ON po.products_options_id = posa.options_id
                   AND po.language_id = " . (int)$_SESSION['languages_id'] . "
                INNER JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                    ON pov.products_options_values_id = posa.options_values_id
                   AND pov.language_id = " . (int)$_SESSION['languages_id'] . "
          WHERE posa.pos_id = $pos_id"
    );

    $valid = true;
    if ($pos_attributes->EOF || $pos_attributes->RecordCount() != count($product_options_sort)) {
        $valid = false;
        $pos_attributes = $db->Execute(
            "SELECT options_id, options_values_id
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK_ATTRIBUTES . "
              WHERE pos_id = $pos_id"
        );
    }

    $option_array = [];
    foreach ($pos_attributes as $next_attr) {
        $option_array[$next_attr['options_id']] = $next_attr['options_values_id'];

        // -----
        // If the current option-combination's option_id is not present within the product's definition, then that option
        // has been removed from the product since the options' stock was last edited.
        //
        if (!isset($product_options[$next_attr['options_id']])) {
            $options_removed_array[] = $next_attr;
        }
    }

    // -----
    // Check that the number of options present in the current POS record jives with the number of options defined
    // for the product.  If the number of currently-defined options for the product doesn't match the number in the
    // POS record less the options removed from the product, then there was one or more option added to the product.
    //
    if ((count($option_array) - count($options_removed_array)) !== count($product_options)) {
        reset($product_options);
        foreach ($product_options as $option_id => $option_name) {
            if (!isset($option_array[$option_id])) {
                $options_added_array[$option_id] = $option_name;
            }
        }
    }

    // -----
    // Set the 'pos_model' to an empty string, just in case the value is NULL (the
    // database default) to prevent unwanted PHP deprecations/errors from being logged
    // when it's output as a protected string.
    //
    $next_option['pos_model'] = $next_option['pos_model'] ?? '';
    $pos_product_options[$pos_id] = [
        'valid' => $valid,
        'label_id' => $next_option['pos_name_id'],
        'quantity' => $next_option['products_quantity'],
        'model' => zen_output_string_protected($next_option['pos_model']),
        'date' => $next_option['pos_date'],
        'options' => $option_array,
    ];

    // -----
    // POSM 4.4.0+, adding a notifier prefixed with 'NOTIFY_' to meet the zc158 requirements
    // for auto-loaded observers.  The legacy notifications were removed in v5.0.0.
    //
    $zco_notifier->notify('NOTIFY_POSM_ADD_PRODUCT_OPTION', $next_option, $pos_product_options);
}
unset($pos_options, $option_array, $pos_attributes);

// -----
// Now, sort the product's options as identified by the page dropdown.
//
if ($posObserver->stringPos($sort_by, 'model-') === 0) {
    if ($sort_by === 'model-asc') {
        uasort($pos_product_options, static function($a, $b)
        {
            $result = strcasecmp($a['model'], $b['model']);
            return ($result < 0) ? -1 : (($result > 0) ? 1 : 0);
        });
    } else {
        uasort($pos_product_options, static function($a, $b)
        {
            $result = strcasecmp($b['model'], $a['model']);
            return ($result < 0) ? -1 : (($result > 0) ? 1 : 0);
        });
    }
} else {
    uasort($pos_product_options, static function($a, $b)
    {
        global $product_options_sort;

        $result = 0;
        foreach ($a['options'] as $options_id => $options_values_id) {
            if (!isset($b['options'][$options_id])) {
                $result = 1;
                break;
            }

            if ($options_values_id === $b['options'][$options_id]) {
                continue;
            }

            $result = ($product_options_sort[$options_id]['values'][$options_values_id] < $product_options_sort[$options_id]['values'][$b['options'][$options_id]]) ? -1 : 1;
            break;
        }
        return $result;
    });
}

$static_field_count = ($show_date_column === true) ? 5 : 4;
if ($show_oos_label_column === false) {
    $static_field_count--;
}

$zco_notifier->notify('NOTIFY_POSM_START_HTML_OUTPUT', [], $static_field_count);
define('STATIC_FIELD_COUNT', $static_field_count);

// -----
// Show a link to the "View All" tool, so long as there's at least one managed
// variant.
//
$check = $db->Execute(
    "SELECT pos_id
       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
      LIMIT 1"
);
$view_all_link = '';
if (!$check->EOF) {
    $view_all_link =
        '<a class="btn btn-sm btn-info" title="' . BUTTON_VIEW_ALL_ALT . '" href="' . zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_VIEW_ALL) . '">' .
            BUTTON_VIEW_ALL .
        '</a>&nbsp;';
}
unset($check);

$product_type = zen_get_products_type($pID);
$type_handler = $zc_products->get_admin_handler($product_type);
?>
<div class="container-fluid">
    <h1><?= HEADING_TITLE ?></h1>
    <div class="text-right">
        <?= $view_all_link ?>
        <a class="btn btn-info btn-sm" title="<?= BUTTON_DEFINE_LABELS_ALT ?>" href="<?= zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_NAMES) ?>">
            <?= BUTTON_DEFINE_LABELS ?>
        </a>
    </div>
    <p><?= TEXT_POS_INSTRUCTIONS ?></p>
    <hr>
<?php
$cat_form_parameters = zen_get_all_get_params(['category_id', 'disabled', 'disabled_check', 'use_model', 'use_model_check', 'sort_by']);
?>
    <?= zen_draw_form('cat-form', FILENAME_PRODUCTS_OPTIONS_STOCK, $cat_form_parameters, 'get', 'id="cat-form" class="form-inline"') ?>
        <?= zen_draw_hidden_field('disabled', ($include_disabled ? 'true' : ''), 'id="posm-disabled"') ?>
        <?= zen_draw_hidden_field('use_model', ($include_model ? 'true' : ''), 'id="posm-use-model"') ?>
        <?= zen_draw_hidden_field('pID', $pID, 'id="pid-sort"') ?>

        <div class="form-group">
            <?= zen_draw_label(TEXT_CHOOSE_CATEGORY, 'category_id', 'class="control-label"') ?>
<?php
// -----
// Create the category-selection drop-down.  Note that when a different category is selected, the currently-active
// product (represented by $pID) is reset by the tool's jQuery component.
//
$category_select = zen_get_category_tree('', '', '0', '', '', true);
array_unshift($category_select, ['id' => 0, 'text' => TEXT_ALL_CATEGORIES]);
array_unshift($category_select, ['id' => -1, 'text' => TEXT_PLEASE_SELECT]);
?>
            <?= zen_draw_pull_down_menu('category_id', $category_select, $current_category_id, 'class="form-control" id="category_id"') ?>
        </div>
        <div class="checkbox">
            <label>
                <?= zen_draw_checkbox_field('disabled_check', 'yes', $include_disabled, '', 'id="disabled-check"') ?>
                <?= ' ' . TEXT_INCLUDE_DISABLED ?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= zen_draw_checkbox_field('use_model_check', 'yes', $include_model, '', 'id="use-model-check"') ?>
                <?= ' ' . TEXT_INCLUDE_MODEL ?>
            </label>
        </div>

        <div class="form-group">
            <?= zen_draw_label(POSM_TEXT_SORT_BY, 'sort_by', 'class="control-label"') ?>
<?php
$sort_array = [
    [
        'id' => 'default',
        'text' => POSM_TEXT_SORT_BY_DEFINITION
    ],
    [
        'id' => 'model-asc',
        'text' => POSM_TEXT_SORT_BY_MODEL_ASC
    ],
    [
        'id' => 'model-desc',
        'text' => POSM_TEXT_SORT_BY_MODEL_DESC
    ],
];
?>
            <?= zen_draw_pull_down_menu('sort_by', $sort_array, $sort_by, 'class="form-control" id="sort_by"') ?>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?= BUTTON_GO ?></button>
        </div>

    <?= '</form>' ?>
<?php
if (count($products_select) !== 0) {
    $prod_form_parameters = zen_get_all_get_params(['pID', 'disabled', 'disabled_check', 'use_model', 'use_model_check']);
?>
    <hr>
    <?= zen_draw_form('prod-form', FILENAME_PRODUCTS_OPTIONS_STOCK, $prod_form_parameters, 'get', 'id="prod-form" class="form-inline"') ?>

        <div class="form-group">
            <?= zen_draw_label(TEXT_CHOOSE_PRODUCT, 'pID', 'class="control-label"') ?>
            <?= zen_draw_pull_down_menu('pID', $products_select, $pID, 'class="form-control" id="pID"') ?>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?= BUTTON_GO ?></button>
        </div>
    <?= '</form>' ?>
    <a href="<?= zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, "products_filter=$pID&current_category_id=$current_category_id", 'NONSSL') ?>" class="btn btn-sm btn-info">
        <?= IMAGE_EDIT_ATTRIBUTES ?>
    </a>
    <a href="<?= zen_href_link($type_handler, 'cPath=' . zen_get_product_path($pID) . "&product_type=$product_type&pID=$pID&action=new_product") ?>" class="btn btn-sm btn-info">
        <?= IMAGE_EDIT_PRODUCT ?>
    </a>
<?php
}

// -----
// If there are no products in the currently-selected category, let the admin know.
//
if (count($products_select) === 0) {
    $product_model = '';
?>
    <hr>
    <p id="no-products"><?= TEXT_NO_PRODUCTS_IN_CATEGORY ?></p>
<?php
// -----
// Otherwise,
} else {
    // -----
    // If a single Out-of-stock label is defined, display it here.  This allows us to remove
    // the associated column for each product, reducing on-screen real-estate and the number
    // of variables to post.
    //
    if ($show_oos_label_column === false) {
?>
    <hr>
    <p><?= sprintf(TEXT_SINGLE_LABEL_NAME, $single_label_text) ?></p>
<?php
    }

    $options_added = (count($options_added_array) !== 0);
?>
    <p><?= ($options_added === true) ? TEXT_POS_OPTIONS_ADDED : TEXT_POS_INSERT ?></p>
<?php
    // -----
    // The total number of columns displayed is the sum of those associated with the current
    // product's options and those that are static.
    //
    $option_cols = count($product_options);
    $total_cols = $option_cols + STATIC_FIELD_COUNT;
?>
    <?= zen_draw_form('modify_form', FILENAME_PRODUCTS_OPTIONS_STOCK, zen_get_all_get_params(['action', 'disabled', 'disabled_check', 'use_model', 'use_model_check']) . 'action=update', 'post') ?>
        <?= zen_draw_hidden_field('pID', $pID) ?>
        <table class="table table-condensed">
            <tr class="dataTableHeadingRow">
<?php
    foreach ($product_options as $option_id => $option_name) {
?>
                <td class="dataTableHeadingContent"><?= $option_name ?></td>
<?php
    }
?>
                <td class="dataTableHeadingContent">&nbsp;</td>
<?php
    if ($show_oos_label_column === true) {
?>
                <td class="dataTableHeadingContent text-center"><?= TEXT_OOS_LABEL ?></td>
<?php
    }

    if ($show_date_column === true) {
?>
                <td class="dataTableHeadingContent text-center">&nbsp;</td>
<?php
    }

    // -----
    // This notification provides the observer with access to an array of content which is
    // rendered via this script.  That array of content, if updated, is expected to contain
    // an array of associative arrays ... one array element for each added column (or colspan) of data:
    //
    // $additional_content = [
    //   [
    //      'text' => 'Column Title',                   //- The title to use for the column (required)
    //      'align' => 'left|center|right',             //- The text direction for the column's heading (optional)
    //      'params' => 'additional HTML parameters',   //- Additional, optional, parameters for the header, e.g. 'colspan="7"'
    //   ],
    //   ...
    // ];
    //
    $additional_content = [];
    $zco_notifier->notify('NOTIFY_POSM_UPPER_HEADING_INSERT', '', $additional_content);
    foreach ($additional_content as $content) {
        $additional_class = (isset($content['align'])) ? ' text-' . $content['align'] : '';
        $additional_params = (isset($content['params'])) ? ' ' . $content['params'] : '';
?>
                <td class="dataTableHeadingContent<?= $additional_class ?>"<?= $additional_params ?>><?= $content['text'] ?></td>
<?php
    }
?>
                <td class="dataTableHeadingContent text-center"><?= TEXT_POS_STOCK_QUANTITY ?></td>
                <td class="dataTableHeadingContent text-center">&nbsp;</td>
            </tr>

            <tr>
<?php
    foreach ($product_options as $option_id => $option_name) {
        if ($options_added) {
            $option_output = (isset($options_added_array[$option_id])) ? draw_option_pulldown($pID, $option_id, "option_values[$option_id]", '', false) : '&mdash;';
        } else {
            $option_output = draw_option_pulldown($pID, $option_id, "option_values[$option_id]");
        }
?>
                <td class="dataTableContent"><?= $option_output ?></td>
<?php
    }
?>
                <td class="dataTableContent">&nbsp;</td>
<?php
    if ($show_oos_label_column === true) {
?>
                <td class="dataTableContent text-center"><?= (count($pos_names_array) === 0) ? TEXT_NONE_DEFINED : zen_draw_pull_down_menu('pos_name', $pos_names_array, $pos_name, 'class="form-control input-sm"') ?></td>
<?php
    }

    if ($show_date_column === true) {
?>
                <td class="dataTableContent">&nbsp;</td>
<?php
    }

    // -----
    // This notification provides the observer with access to an array of content which is
    // rendered via this script.  That array of content, if updated, is expected to contain
    // an array of associative arrays ... one array element for each added column/colspan of data:
    //
    // $additional_content = [
    //   [
    //      'text' => 'Column Title',                   //- The title to use for the column (required)
    //      'align' => 'left|center|right',             //- The text direction for the column's heading (optional)
    //      'params' => 'additional HTML parameters',   //- Additional, optional, parameters for the header, e.g. 'colspan="7"'
    //   ],
    //   ...
    // ];
    //
    $additional_content = [];
    $zco_notifier->notify('NOTIFY_POSM_UPPER_CONTENT_INSERT', '', $additional_content);
    foreach ($additional_content as $content) {
        $additional_class = (isset($content['align'])) ? ' text-' . $content['align'] : '';
        $additional_params = (isset($content['params'])) ? ' ' . $content['params'] : '';
?>
                <td class="dataTableHeadingContent<?= $additional_class ?>"<?= $additional_params ?>><?= $content['text'] ?></td>
<?php
    }

    // -----
    // Set the current product's model ... even if it's not displayed, it's used
    // in the tool's jQuery component, so don't want PHP to generate warnings.
    //
    $product_model = zen_get_products_model($pID);

    // -----
    // If a new option has been added to the product currently being processed, the quantity input is
    // not rendered so that the admin can, first, choose the option-values to be added to any
    // pre-existing managed options.
    //
    $q = $db->Execute(
        "SELECT products_quantity
           FROM " . TABLE_PRODUCTS . "
          WHERE products_id = $pID
          LIMIT 1"
    );
    $product_quantity = (!$q->EOF) ? $q->fields['products_quantity'] : 0;

    // -----
    // Determine the button(s) to be displayed in the upper portion of the form.
    //
    // 1. If the currently-selected product is not currently managed by POSM or if an
    //    additional option has been added to the product after the product's POSM configuration
    //    has been set, that's an "Insert" button.
    // 2. Otherwise, the currently-selected is POSM-managed.  The "Add Qty." and
    //    "Replace Qty." buttons will be shown to enable a quick quantity-update/replacement
    //    for the currently-managed -- or added -- variants.
    //
    if ($options_added === true) {
        $upper_buttons = [
            [
                'value' => 'insert_options',
                'text' => IMAGE_INSERT,
            ],
        ];
        $quantity_field = '&mdash;';
    } else {
        $quantity_field = zen_draw_input_field('quantity', $quantity, 'id="insert_quantity" class="quantity form-control input-xs"');
        if (count($pos_product_options) === 0) {
            $upper_buttons = [
                [
                    'value' => 'insert',
                    'text' => IMAGE_INSERT,
                ],
            ];
        } else {
            $upper_buttons = [
                [
                    'value' => 'add',
                    'text' => TEXT_ADD_TO_QUANTITY,
                ],
                [
                    'value' => 'replace',
                    'text' => TEXT_REPLACE_QUANTITY,
                    'class' => 'btn-warning',
                ],
            ];
        }
    }
?>
                <td class="dataTableContent text-center"><?= $quantity_field ?></td>
                <td class="dataTableContent text-center">
<?php
    foreach ($upper_buttons as $next_button) {
        $button_class = (isset($next_button['class'])) ? $next_button['class'] : 'btn-primary';
?>
                    <button type="submit" name="sub_action" value="<?= $next_button['value'] ?>" class="btn btn-default btn-sm <?= $button_class ?>" id="btn-<?= $next_button['value'] ?>">
                        <?= $next_button['text'] ?>
                    </button>
<?php
    }
?>
                </td>
            </tr>
<?php
    if (count($pos_product_options) !== 0) {
?>
            <tr>
                <td colspan="<?= $total_cols ?>"><?= TEXT_POS_INSTRUCTIONS2 ?></td>
            </tr>
<?php
        // -----
        // This notification enables an observer to identify one or more rows of content to be output
        // under the control of this main tool.
        //
        $additional_instructions = [];
        $zco_notifier->notify('NOTIFY_POSM_SET_INSTRUCTIONS', $pID, $additional_instructions);
        foreach ($additional_instructions as $next_instruction) {
            $parameters = (!empty($next_instruction['params'])) ? ' ' . $next_instruction['params'] : '';
?>
            <tr>
                <td colspan="<?= $total_cols ?>"<?= $parameters ?>><?= $next_instruction['text'] ?></td>
            </tr>
<?php
        }
?>
            <tr class="dataTableHeadingRow">
<?php
        foreach ($product_options as $option_id => $option_name) {
?>
                <td class="dataTableHeadingContent"><?= $option_name ?></td>
<?php
        }
?>
                <td class="dataTableHeadingContent text-center"><?= TEXT_OPTION_MODEL ?></td>
<?php
        if ($show_oos_label_column === true) {
?>
                <td class="dataTableHeadingContent text-center"><?= TEXT_OOS_LABEL ?></td>
<?php
        }

        if ($show_date_column === true) {
?>
                <td class="dataTableHeadingContent text-center"><?= TEXT_OOS_DATE ?></td>
<?php
        }

        // -----
        // This is the now-current version of the notification; enabling additional headings/columns to be added to the left
        // of the 'Qty./Update' column.
        //
        // $extra_headings = [
        //   [
        //      'text' => 'Column Data',            //- The data to be included for the column (required)
        //      'align' => 'left|center|right',     //- The text direction for the column's data (optional)
        //      'params' => 'Column Parameters',    //- Any additional HTML non-class parameters to apply to the data (optional)
        //      'class' => '',                      //- Any additional HTML class-name to apply to the data (optional)
        //   ],
        //   ...
        // ];
        //
        // NOTE: Columns can be added to the right of the 'Qty./Update' column via the NOTIFY_POSM_LOWER_HEADING_INSERT_AFTER_QTY
        // notification.  The sum of the number of columns added for these two notifications is expected to total to the number
        // of additional columns the observer has indicated as a result of the NOTIFY_POSM_START_HTML_OUTPUT notification.
        //
        $extra_headings = [];
        $zco_notifier->notify('NOTIFY_POSM_LOWER_HEADING_INSERT_B4_QTY', '', $extra_headings);
        foreach ($extra_headings as $current_heading) {
            $class = '';
            if (isset($current_heading['align']) && ($current_heading['align'] === 'right' || $current_heading['align'] === 'center')) {
                $class = ' text-' . $current_heading['align'];
            }
            if (isset($current_heading['class'])) {
                $class .= ' ' . $current_heading['class'];
            }
            $parameters = (isset($current_heading['params'])) ? ' ' . $current_heading['params'] : '';
?>
                <td class="dataTableHeadingContent<?= $class ?>"<?= $parameters ?>><?= $current_heading['text'] ?></td>
<?php
        }
?>
                <td class="dataTableHeadingContent text-center">
                    <?= TEXT_POS_STOCK_QUANTITY ?><br>
                    <span class="smaller"><?= sprintf(TEXT_CURRENT_TOTAL, $product_quantity) ?></span>
                </td>
<?php
        // -----
        // This is the now-current version of the notification; enabling additional headings/columns to be added to the right
        // of the 'Qty./Update' column.
        //
        // $extra_headings = [
        //   [
        //      'text' => 'Column Data',            //- The data to be included for the column (required)
        //      'align' => 'left|center|right',     //- The text direction for the column's data (optional)
        //      'params' => 'Column Parameters',    //- Any additional HTML non-class parameters to apply to the data (optional)
        //      'class' => '',                      //- Any additional HTML class-name to apply to the data (optional)
        //   ],
        //   ...
        // ];
        //
        // NOTE: Columns can be added to the left of the 'Qty./Update' column via the NOTIFY_POSM_LOWER_HEADING_INSERT_B4_QTY
        // notification.  The sum of the number of columns added for these two notifications is expected to total to the number
        // of additional columns the observer has indicated as a result of the NOTIFY_POSM_START_HTML_OUTPUT notification.
        //
        $extra_headings = [];
        $zco_notifier->notify('NOTIFY_POSM_LOWER_HEADING_INSERT_AFTER_QTY', '', $extra_headings);

        $columns_to_right = count($extra_headings);
        foreach ($extra_headings as $current_heading) {
            $class = '';
            if (isset($current_heading['align']) && ($current_heading['align'] === 'right' || $current_heading['align'] === 'center')) {
                $class = ' text-' . $current_heading['align'];
            }
            if (isset($current_heading['class'])) {
                $class .= ' ' . $current_heading['class'];
            }
            $parameters = (isset($current_heading['params'])) ? ' ' . $current_heading['params'] : '';
?>
                <td class="dataTableHeadingContent<?= $class ?>"<?= $parameters ?>><?= $current_heading['text'] ?></td>
<?php
        }
?>
                <td class="dataTableHeadingContent text-center">
                    <?= TEXT_POS_REMOVE ?><br>
                    <span class="smaller"><?= TABLE_HEADING_CHECK_UNCHECK ?></span>
                    <?= zen_draw_checkbox_field('check-uncheck', '', false, '', 'id="check-uncheck"') ?>
                </td>
            </tr>

            <tr>
                <td colspan="<?= $option_cols ?>">&nbsp;</td>
                <td class="dataTableContent text-center">
                    <span id="base-model"><?= $product_model ?></span><br>
<?php
        // -----
        // Don't render the model-prefill checkbox if the model number is an empty string.
        //
        if ($product_model !== '') {
?>
                    <span id="set-model-span">
                        <span class="smaller" title="<?= TEXT_MODEL_DEFAULT_TITLE ?>">
                            <?= TEXT_MODEL_DEFAULT ?>&nbsp;
                        </span>
                        <?= zen_draw_checkbox_field('set_default', '', false, '', 'id="set-model-default"') ?>
                    </span>
<?php
        }
?>
                </td>
<?php
        // -----
        // If the current STATIC_FIELD_COUNT indicates more columns than those always displayed (the
        // model-number, 'Update' and 'Remove' buttons), add a blank column-span to account for any
        // un-rendered columns prior to the 'Update/Qty.' column.
        //
        if (STATIC_FIELD_COUNT > 3) {
?>
                <td colspan="<?= STATIC_FIELD_COUNT - 3 - $columns_to_right ?>">&nbsp;</td>
<?php
        }

        // -----
        // Enable an observer to add HTML parameters to the 'Update' button.
        //
        $posm_update_button_parms = '';
        $zco_notifier->notify('NOTIFY_POSM_SET_UPDATE_BUTTON_PARAMETERS', '', $posm_update_button_parms);
        if ($posm_update_button_parms !== '' && strpos($posm_update_button_parms, ' ') !== 0) {
            $posm_update_button_parms = ' ' . $posm_update_button_parms;
        }
?>
                <td class="dataTableContent text-center">
                    <button class="btn btn-primary btn-sm posm-update" type="submit" title="<?= TEXT_UPDATE_ALT ?>" name="sub_action" value="update"<?= $posm_update_button_parms ?>>
                        <?= BUTTON_UPDATE ?>
                    </button>
                </td>
<?php
        if ($columns_to_right !== 0) {
?>
                <td colspan="<?= $columns_to_right ?>">&nbsp;</td>
<?php
        }
?>
                <td class="dataTableContent text-center">
                    <button class="btn btn-danger btn-sm posm-remove" type="submit" title="<?= TEXT_REMOVE_ALT ?>" name="sub_action" value="remove">
                        <?= BUTTON_REMOVE ?>
                    </button>
                </td>
            </tr>
<?php
    }

    // -----
    // Set the model-field's width if-and-only-if the POSM_ADMIN_MODEL_WIDTH setting is empty; otherwise, the width
    // is set via CSS (see above).
    //
    $model_field_size = ($model_num_width === '') ? zen_set_field_length(TABLE_PRODUCTS_OPTIONS_STOCK, 'pos_model') : '';
    $date_field_size = 'size="11" maxlength="10"';
    $hidden_fields = (!$show_date_column) ? zen_draw_hidden_field('pos_date', '') : '';
    if ($show_oos_label_column === false) {
        $hidden_fields .= zen_draw_hidden_field('pos_names', $single_label_id);
    }

    foreach ($pos_product_options as $pos_id => $info_array) {
        if ($action === 'update') {
            $pos_model = $_POST['pos_model'][$pos_id];
            $pos_name_id = (is_array($_POST['pos_names'])) ? $_POST['pos_names'][$pos_id] : $single_label_id;
            $pos_date = (is_array($_POST['pos_date'])) ? $_POST['pos_date'][$pos_id] : '';
            $pos_quantity = $_POST['pos_quantity'][$pos_id];
        } else {
            $pos_model = $info_array['model'];
            $pos_name_id = $info_array['label_id'];
            $pos_date = $info_array['date'];
            $pos_quantity = $info_array['quantity'];
        }
        $extra_model_class = (POSM_DUPLICATE_MODELNUMS !== 'Allow' && posm_modelnum_is_duplicate($pos_id, $pos_model)) ? ' duplicate' : '';

        $oos_date_is_expired = false;
        if ($posObserver->stringPos(get_pos_oos_name($pos_name_id, $_SESSION['languages_id']), '[date]') !== false) {
            $date_class = 'date d-inline-block';
            $oos_date_is_expired = ($reminder_date > $pos_date);
        } else {
            $date_class = 'date d-none';
        }

        if ($info_array['valid']) {
            $additional_class = ($oos_date_is_expired === true) ? ' bg-warning' : '';
            $quantity_parms = 'class="form-control input-xs quantity' . (($info_array['quantity'] <= $posm_stock_reorder_level) ? ' lowstock' : '') . '"';
        } else {
            $additional_class = ' removed';
            $quantity_parms = ' class="form-control input-xs quantity" readonly';
        }
?>
            <tr class="hoverRow<?= $additional_class ?>">
<?php
        foreach ($product_options as $option_id => $option_name) {
            $option_value_name = zen_values_name($info_array['options'][$option_id]);
            if ($option_value_name === '') {
                $option_value_name = '<b>* UNKNOWN VALUE *</b>';
            }
?>
                <td class="dataTableContent"><?= $option_value_name ?></td>
<?php
        }
?>
                <td class="dataTableContent text-center">
                    <?= zen_draw_input_field("pos_model[$pos_id]", $pos_model, 'class="form-control input-xs model-num' . $extra_model_class . '" ' . $model_field_size) ?>
                </td>
<?php
        if ($show_oos_label_column === true) {
?>
                <td class="dataTableContent text-center">
                    <?= (count($pos_names_array) === 0) ? TEXT_NONE_DEFINED : zen_draw_pull_down_menu("pos_names[$pos_id]", $pos_names_array, $pos_name_id, 'class="pos-name form-control input-xs" data-posid="' . $pos_id . '"') ?>
                </td>
<?php
        }

        if ($show_date_column === true) {
?>
                <td class="dataTableContent text-center">
                    <?= zen_draw_input_field("pos_date[$pos_id]", $pos_date, 'class="form-control input-xs ' . $date_class . '" ' . $date_field_size) ?>
                </td>
<?php
        }

        // -----
        // This is the now-current version of the notification; enabling additional content-columns to be added to the left
        // of the 'Qty./Update' column.
        //
        // $lower_content = [
        //   [
        //      'text' => 'Column Data',            //- The data to be included for the column (required)
        //      'align' => 'left|center|right',     //- The text direction for the column's data (optional)
        //      'params' => 'Column Parameters',    //- Any additional HTML non-class parameters to apply to the data (optional)
        //      'class' => '',                      //- Any additional HTML class-name to apply to the data (optional)
        //   ],
        //   ...
        // ];
        //
        // NOTE: Columns can be added to the right of the 'Qty./Update' column via the NOTIFY_POSM_LOWER_CONTENT_INSERT_AFTER_QTY
        // notification.  The sum of the number of columns added for these two notifications is expected to total to the number
        // of additional columns the observer has indicated as a result of the NOTIFY_POSM_START_HTML_OUTPUT notification.
        //
        $lower_content = [];
        $zco_notifier->notify(
            'NOTIFY_POSM_LOWER_CONTENT_INSERT_B4_QTY',
            [
                'pos_id' => $pos_id,
                'info_array' => $info_array,
                'action' => $action,
            ],
            $lower_content
        );
        foreach ($lower_content as $current_content) {
            $class = '';
            if (isset($current_content['align']) && ($current_content['align'] === 'right' || $current_content['align'] === 'center')) {
                $class = ' text-' . $current_content['align'];
            }
            if (isset($current_content['class'])) {
                $class .= ' ' . $current_content['class'];
            }
            $parameters = (isset($current_content['params'])) ? ' ' . $current_content['params'] : '';
?>
                <td class="dataTableHeadingContent<?= $class ?>"<?= $parameters ?>><?= $current_content['text'] ?></td>
<?php
        }
?>
                <td class="dataTableContent text-center">
                    <?= zen_draw_input_field("pos_quantity[$pos_id]", $pos_quantity, $quantity_parms) ?>
                </td>
<?php
        // -----
        // This is the now-current version of the notification; enabling additional content-columns to be added to the right
        // of the 'Qty./Update' column.
        //
        // $lower_content = [
        //   [
        //      'text' => 'Column Data',            //- The data to be included for the column (required)
        //      'align' => 'left|center|right',     //- The text direction for the column's data (optional)
        //      'params' => 'Column Parameters',    //- Any additional HTML non-class parameters to apply to the data (optional)
        //      'class' => '',                      //- Any additional HTML class-name to apply to the data (optional)
        //   ],
        //   ...
        // ];
        //
        // NOTE: Columns can be added to the left of the 'Qty./Update' column via the NOTIFY_POSM_LOWER_CONTENT_INSERT_B4_QTY
        // notification.  The sum of the number of columns added for these two notifications is expected to total to the number
        // of additional columns the observer has indicated as a result of the NOTIFY_POSM_START_HTML_OUTPUT notification.
        //
        $lower_content = [];
        $zco_notifier->notify(
            'NOTIFY_POSM_LOWER_CONTENT_INSERT_AFTER_QTY',
            [
                'pos_id' => $pos_id,
                'info_array' => $info_array,
                'action' => $action,
            ],
            $lower_content
        );

        foreach ($lower_content as $current_content) {
            $class = '';
            if (isset($current_content['align']) && ($current_content['align'] === 'right' || $current_content['align'] === 'center')) {
                $class = ' text-' . $current_content['align'];
            }
            if (isset($current_content['class'])) {
                $class .= ' ' . $current_content['class'];
            }
            $parameters = (isset($current_content['params'])) ? ' ' . $current_content['params'] : '';
?>
                <td class="dataTableHeadingContent<?= $class ?>"<?= $parameters ?>><?= $current_content['text'] ?></td>
<?php
        }
?>
                <td class="dataTableContent text-center">
                    <?= zen_draw_checkbox_field("pos_remove[$pos_id]", false, false, '', 'class="cBox"') ?>
                </td>
            </tr>
<?php
    }

    if (count($pos_product_options) > 0) {
?>
            <tr>
                <td colspan="<?= $option_cols ?>">&nbsp;</td>
                <td class="dataTableContent text-center"><?= $product_model ?></td>
<?php
        // -----
        // If the current STATIC_FIELD_COUNT indicates more columns than those always displayed (the
        // model-number, 'Update' and 'Remove' buttons), add a blank column-span to account for any
        // un-rendered columns prior to the 'Update/Qty.' column.
        //
        if (STATIC_FIELD_COUNT > 3) {
?>
                <td colspan="<?= STATIC_FIELD_COUNT - 3 - $columns_to_right ?>">&nbsp;</td>
<?php
        }
?>
                <td class="dataTableContent text-center">
                    <button class="btn btn-primary btn-sm posm-update" type="submit" title="<?= TEXT_UPDATE_ALT ?>" name="sub_action" value="update"<?= $posm_update_button_parms ?>>
                        <?= BUTTON_UPDATE ?>
                    </button>
                </td>
<?php
        if ($columns_to_right !== 0) {
?>
                <td colspan="<?= $columns_to_right ?>">&nbsp;</td>
<?php
        }
?>
                <td class="dataTableContent text-center">
                    <button class="btn btn-danger btn-sm posm-remove" type="submit" title="<?= TEXT_REMOVE_ALT ?>" name="sub_action" value="remove">
                        <?= BUTTON_REMOVE ?>
                    </button>
                </td>
            </tr>
<?php
    }
?>
        </table>
        <?= $hidden_fields ?>
    <?= '</form>' ?>
<?php
}   //-At least one product exists in the currently-selected category
?>
<!-- body_text_eof //-->
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
<script>
$(function() {
    // -----
    // When the selected category-id is changed, the current product's id is reset.
    //
    $('select[name="category_id"]').on('change', function() {
        $('#pid-sort').remove();
    });
<?php
if (POSM_DUPLICATE_MODELNUMS !== 'Allow') {
?>
    $('input[type="text"].model-num').on('change', function() {
        let modelField = $(this).attr('name');
        let posID = $(this).attr('name').match(/\d+/g);
        let modelNum = $(this).val();
        $(this).removeClass('duplicate');
        zcJS.ajax({
            url: "ajax.php?act=ajaxProductsOptionsStockAdmin&method=isModelDuplicate",
            data: {
                model_num: modelNum,
                pos_id: posID
            },
            cache: false,
            headers: { "cache-control": "no-cache" },
            error: function (jqXHR, textStatus, errorThrown) {
                if (textStatus === 'timeout') {
                    alert(ajaxTimeoutErrorMessage);
                }
            },
        }).done(function(response) {
            if (response.isOk === false) {
                $('input[type="text"][name="'+modelField+'"].model-num').addClass('duplicate');
<?php
    if (POSM_DUPLICATE_MODELNUMS === 'Disallow') {
?>
                alert(<?= JSCRIPT_ERROR_DUPLICATE_MODEL ?>);
                document.modify_form['pos_model['+posID+']'].focus();
<?php
    }
?>
            }
        });
    });
<?php
}
?>
    // -----
    // When the "Include disabled?" checkbox is changed, copy its value to a hidden input.
    //
    $('#disabled-check').on('change', function() {
        $('#posm-disabled').val($('#disabled-check').is(':checked'));
    });

    // -----
    // When the "Include products' model?" checkbox is changed, copy its value to a hidden input.
    //
    $('#use-model-check').on('change', function() {
        $('#posm-use-model').val($('#use-model-check').is(':checked'));
    });

    // -----
    // When the "Check/Uncheck All" checkbox is changed, reflect its state into all of
    // the "Remove" checkboxes.
    //
    $('#check-uncheck').on('change', function() {
        $('.cBox').prop('checked', this.checked);
    });

    // -----
    // When one of the "Remove" buttons is clicked, check to make sure that at least one
    // option-combination was chosen for removal and, if so, present a message to the admin
    // to confirm the action.
    //
    // Note: The 'selected' variable is used within the confirmation message!
    //
    $('button.posm-remove').on('click', function() {
        let submitOK = false;
        let selected = $('.cBox:checked').length;
        if (selected === 0) {
            alert('<?= WARNING_NO_FILES_SELECTED ?>');
        } else {
            submitOK = confirm('<?= JS_MESSAGE_DELETE_SELECTED_CONFIRM ?>');
        }
        return submitOK;
    });

    // -----
    // When an "Insert", "Add Qty" or "Replace Qty" button (in the upper form) is clicked, determine
    // whether the quantity is to be added or replaced and set the message
    // sub-text accordingly.
    //
    // The 'items' variable sums up the number of items to be potentially added for the action.
    //
    // Note: Both the 'add_replace', 'items' and 'quantity' variables are used/embedded in the confirmation message language definitions.
    //
    $('#btn-insert, #btn-add, #btn-replace').on('click', function() {
        let items = 1;
        $('.oSelect').each(function() {
            if ($(this).children('option:selected').val() === 0) {
                items *= $(this).data('ocount');
            }
        });

        if (this.id === 'btn-insert') {
            let confirm_message = '<?= JS_MESSAGE_INSERT_NEW_CONFIRM ?>';
        } else {
            if (this.id === 'btn-add') {
                let quantity = $('#insert_quantity').val();
                let add_replace = '<?= JS_MESSAGE_UPDATED ?>';
            } else {
                let add_replace = '<?= JS_MESSAGE_REPLACED ?>';
            }
            let confirm_message = '<?= JS_MESSAGE_INSERT_MULTIPLE_CONFIRM ?>';
        }

        let submitOK = true;
        if (items !== 1) {
            submitOK = confirm(confirm_message);
        }
        return submitOK;
    });

    // -----
    // When an option's out-of-stock label is changed, and the new label contains a
    // '[date]', display the associated option's date field for entry; hide that date
    // entry if no date is required.
    //
    $('.pos-name').on('change', function() {
        let posid = $(this).data('posid');
        if ($(this).find('option:selected').text().indexOf('[date]') === -1) {
            $('input[name="pos_date['+posid+']"]').css('display', 'none');
        } else {
            $('input[name="pos_date['+posid+']"]').css('display', 'inline-block');
        }
    });

    // -----
    // If an admin clicks the checkbox, requesting that the base product's model
    // number be filled for any currently-empty values, display a confirmation (since
    // the screen update can't be undone) and, if confirmed, set that base
    // model number to the currently empty models.
    //
    // Once performed, hide the checkbox.
    //
    $('#set-model-default').on('change', function() {
        if (!$(this).val()) {
            return;
        }
        let emptyModels = 0;
        $('.model-num').each(function() {
            if (this.value === '') {
                emptyModels++;
            }
        });
        if (confirm('<?= sprintf(JS_MESSAGE_CONFIRM_MODEL_DEFAULT, $product_model) ?>')) {
            let baseModel = $('#base-model').html();
            $('.model-num').each(function() {
                if (this.value === '') {
                    this.value = baseModel;
                }
            });
            $('#set-model-span').hide();
        }
    });
});
</script>
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
