<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2015-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM 5.0.0
//
require 'includes/application_top.php';
$languages = zen_get_languages();

// -----
// Load the class-helper for the tool's processing.  For POSM versions prior to v4.1.0,
// this class was in-line here.
//
require DIR_WS_CLASSES . 'PosmViewAll.php';

// -----
// Load the functions needed by this tool, specifically the quantity-checking
// one!
//
require DIR_WS_FUNCTIONS . 'products_options_stock_admin_functions.php';

$onload = '';
if (isset($_GET['action'], $_POST['quantity'], $_POST['update_x']) && $_GET['action'] === 'update' && is_array($_POST['quantity'])) {
    $quantity_inputs = zen_db_prepare_input($_POST['quantity']);
    $model_inputs = zen_db_prepare_input($_POST['model'] ?? []);

    // -----
    // Let an observer 'know' that we're starting the page's initialization.
    //
    $zco_notifier->notify('NOTIFY_POSM_VIEW_ALL_UPDATE_INIT');
    $error = false;

    $model_max_length = zen_field_length(TABLE_PRODUCTS_OPTIONS_STOCK, 'pos_model');
    $pos_id_array = [];
    foreach ($quantity_inputs as $pos_id => $new_quantity) {
        $pos_id = (int)$pos_id;

        if (posm_is_numeric_string($new_quantity) === false) {
            $messageStack->add(ERROR_INVALID_QUANTITY, 'error');
            $error = true;
            $onload = " document.modify_form['quantity[$pos_id]'].focus();";
        }

        $posm_sql_data = [
            'products_quantity' => $new_quantity,
            'last_modified' => 'now()'
        ];
        $where_str = "pos_id = $pos_id AND products_quantity != $new_quantity";
        if (isset($model_inputs[$pos_id])) {
            $new_model = $model_inputs[$pos_id];

            if ($posObserver->stringLen($model_inputs[$pos_id]) > $model_max_length) {
                $messageStack->add(sprintf(ERROR_MODEL_TOO_LONG, $model_inputs[$pos_id]), 'error');
                $error = true;
                $onload = "document.modify_form['model[$pos_id]'].focus();";
                break;
            }

            if (POSM_DUPLICATE_MODELNUMS === 'Disallow' && posm_modelnum_is_duplicate($pos_id, $new_model)) {
                $messageStack->add(sprintf(ERROR_DUPLICATE_MODEL_FOUND, $new_model));
                $error = true;
                $onload = " document.modify_form['model[$pos_id]'].focus();";
                break;
            }

            $posm_sql_data['pos_model'] = $new_model;
            $where_str = "pos_id = $pos_id AND (products_quantity != $new_quantity OR BINARY pos_model != '" . zen_db_input($new_model) . "')";
        }

        // -----
        // Note: If an observer sets the $error to (bool)true, it is the observer's responsibility
        // to have previously added a message to the stack for display to the admin.
        //
        $zco_notifier->notify('NOTIFY_POSM_VIEW_ALL_UPDATE', $pos_id, $posm_sql_data, $where_str, $error, $onload);

        if ($error === false) {
            $pos_id_array[] = $pos_id;
            $where_str .= ' LIMIT 1';
            zen_db_perform(TABLE_PRODUCTS_OPTIONS_STOCK, $posm_sql_data, 'update', $where_str);
        }
    }

    // -----
    // For any of the POSM-managed products updated (the loop above stops on the first error),
    // make sure that each base-product's quantity is updated to be the current sum of its
    // variants' quantities.
    //
    if (!empty($pos_id_array)) {
        $product_ids = $db->Execute(
            "SELECT DISTINCT products_id
               FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . "
               WHERE pos_id IN (" . implode(',', $pos_id_array) . ")"
        );
        foreach ($product_ids as $product) {
            posm_update_base_product_quantity($product['products_id']);

            // -----
            // This notification, introduced in POSM v4.4.0, enables a POSM extension to also
            // provide some 'after-variant-handling' for the products that have been
            // updated.
            //
            $zco_notifier->notify('NOTIFY_POSM_VIEW_ALL_UPDATE_PRODUCT', $product['products_id']);
        }
    }

    // -----
    // If no error was detected, indicate that all's good to the admin and refresh
    // the page.
    //
    if ($error === false) {
        $messageStack->add_session(POSM_VIEW_ALL_UPDATED, 'success');
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK_VIEW_ALL, zen_get_all_get_params(['action'])));
    }
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
input[type="text"] {
    width: 4em; text-align: right;
}
input[type="text"].model-num {
    font-family: "Courier New", Courier, monospace;
    font-size: 12px;
    <?= $model_num_width ?>
}
input[type="text"].date {
    width: auto;
}
table > thead > tr.dataTableHeadingRow > th, table > tbody > tr.dataTableHeadingRow > td {
    background-color: <?= POSM_DIVIDER_COLOR ?>;
}
.centered {
    text-align: center;
}
.lowstock, .lowstock td {
    color: #ff3333;
}
.hoverRow:hover {
    background-color: #ebebeb;
}
.table-condensed > tbody > tr.hoverRow > td {
    padding: 2px;
}
.removed, .removed td {
    text-decoration: line-through;
    color: #6666ff!important;
}
.smaller, .version {
    font-size: smaller;
}
.d-none {
    display: none;
}
.disabled {
    color: red;
}
.option-name {
    font-weight: bold;
}
.value-name {
    font-style: italic;
}
.model input {
    width: 20em;
}
.duplicate, .out-of-stock {
    border: 1px solid red;
}
.table-borderless > tbody > tr.hoverRow > td {
    border-top: none;
}
table#main-form > tbody > tr:last-child > td {
    border-bottom: 1px solid #ddd;
}
    </style>
<?php
// -----
// This notification enables observers to make changes to the onload
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
//    for this page should start with 'products_options_stock_view_all_' for the
//    files to be auto-loaded.
// 2) Since this javascript is loaded *prior to* the load of the jQuery base, any JS content
//    must be PURE javascript.  Any jQuery content required by the POSM add-on
//    for this page should be placed in the /includes/javascript sub-directory, as
//    identified above.
//
$css_content = '';
$js_content = '';
$zco_notifier->notify('NOTIFY_POSM_VIEW_ALL_INSERT_HEAD', '', $onload, $css_content, $js_content);
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
$base_static_field_count = 4;
$static_field_count = $base_static_field_count;
$instructions2 = TEXT_POS_INSTRUCTIONS2;
$zco_notifier->notify('NOTIFY_POSM_VIEW_ALL_START_BODY', '', $static_field_count, $instructions2);
define('STATIC_FIELD_COUNT', $static_field_count);

$view_all = (isset($_GET['view_all']));
$view_all_checkbox = zen_draw_checkbox_field('view_all', '', $view_all, '');

$sort_by = $_GET['sort_by'] ?? 'default';
$sort_array = [
    ['id' => 'default', 'text' => POSM_TEXT_SORT_BY_DEFINITION],
    ['id' => 'model-asc', 'text' => POSM_TEXT_SORT_BY_MODEL_ASC],
    ['id' => 'model-desc', 'text' => POSM_TEXT_SORT_BY_MODEL_DESC],
];
$sort_dropdown = zen_draw_pull_down_menu('sort_by', $sort_array, $sort_by, 'id="sort-by" class="form-control"');
?>
<div class="container-fluid">
    <h1><?= HEADING_TITLE ?></h1>
    <p><?= TEXT_POS_INSTRUCTIONS . $instructions2 ?></p>
    <hr>

    <?= zen_draw_form('all-variants', FILENAME_PRODUCTS_OPTIONS_STOCK_VIEW_ALL, zen_get_all_get_params(['view_all', 'action', 'sort_by']), 'get', 'class="form-inline"') ?>
        <div class="form-group">
            <?= zen_draw_label(POSM_TEXT_SORT_BY, 'sort-by', 'class="control-label"') ?>
            <?= $sort_dropdown ?>
        </div>
        <div class="checkbox">
            <label class="control-label">
                <?= $view_all_checkbox . ' ' . TEXT_CHECK_TO_VIEW_ALL ?>
            </label>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?= BUTTON_GO ?></button>
        </div>
    <?= '</form>' ?>
    <hr>

    <?= zen_draw_form('modify_form', FILENAME_PRODUCTS_OPTIONS_STOCK_VIEW_ALL, zen_get_all_get_params(['action']) . 'action=update', 'post') ?>
        <table class="table table-condensed table-borderless" id="main-form">
            <thead>
                <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent"><?= POSM_TEXT_PRODUCT_NAME ?></th>
                    <th class="dataTableHeadingContent"><?= POSM_TEXT_OPTIONS_LIST ?></th>
                    <th class="dataTableHeadingContent text-center"><?= POSM_TEXT_VARIANT_MODEL ?></th>
<?php
// -----
// This notification provides the observer with access to an array of content which is
// rendered via this script.  That array of content, if updated, is expected to contain
// an array of associative arrays ... one array element for each added column of data:
//
// $additional_content = [
//   [
//      'text' => 'Column Title',           //- The title to use for the column (required)
//      'align' => 'left|center|right',     //- The text direction for the column's heading (optional)
//   ],
//   ...
// ];
//
// Note: Observers are expected to add one column for each field that they've 'registered' via the
// NOTIFY_POSM_VIEW_ALL_START_BODY notification!
//
$additional_content = [];
$zco_notifier->notify('NOTIFY_POSM_VIEW_ALL_TABLE_HEADING', '', $additional_content);
if (count($additional_content) !== (int)STATIC_FIELD_COUNT - $base_static_field_count) {
    trigger_error('Incorrect table-heading fields supplied by observers, current: ' . count($additional_contrnt) . ', expected: ' . (STATIC_FIELD_COUNT - $base_static_field_count), E_USER_NOTICE);
}
foreach ($additional_content as $content) {
    $additional_class = (isset($content['align'])) ? ' text-' . $content['align'] : '';
?>
                    <th class="dataTableHeadingContent<?= $additional_class ?>"><?= $content['text'] ?></th>
<?php
}
?>
                    <th class="dataTableHeadingContent text-center"><?= TEXT_POS_STOCK_QUANTITY ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="<?= STATIC_FIELD_COUNT - 1 ?>">&nbsp;</td>
                    <td class="dataTableContent text-center">
                        <button name="update_x" class="btn btn-sm btn-primary" type="submit" title="<?= TEXT_UPDATE_ALT ?>">
                            <?= IMAGE_UPDATE ?>
                        </button>
                    </td>
                </tr>
<?php
$page_num = (int)($_GET['page'] ?? 1);
if ($page_num <= 0) {
    $page_num = 1;
}
$where_clause = ($view_all === true) ? '' : " WHERE pos.products_quantity <= $posm_stock_reorder_level";
$view_all_sql =
    "SELECT DISTINCT pos.products_id, pd.products_name, p.master_categories_id
       FROM " . TABLE_PRODUCTS_OPTIONS_STOCK . " pos
            INNER JOIN " . TABLE_PRODUCTS . " p
                ON p.products_id = pos.products_id
            INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                ON pd.products_id = pos.products_id
               AND pd.language_id = " . $_SESSION['languages_id'] . $where_clause . "
   ORDER BY pd.products_name ASC";

$view_all_split = new splitPageResults($page_num, POSM_MAX_PRODUCTS_VIEW_ALL, $view_all_sql, $view_all_query_numrows);
$products_list = $db->Execute($view_all_sql);
if ($products_list->EOF) {
?>
                <tr>
                    <td colspan="<?= STATIC_FIELD_COUNT ?>" class="text-center"><?= POSM_VIEW_ALL_NO_PRODUCTS_TO_LIST ?></td>
                </tr>
            </tbody>
        </table>
    <?= '</form>' ?>
<?php
} else {
    $posm_view_all = new PosmViewAll($posm_stock_reorder_level, $sort_by);
    $model_field_size = (POSM_ADMIN_MODEL_WIDTH === '') ? ' ' . zen_set_field_length(TABLE_PRODUCTS_OPTIONS_STOCK, 'pos_model') : '';
    foreach ($products_list as $next_product) {
        $products_id = $next_product['products_id'];
        $products_name_extra_info = '';
        $products_name_additional_columns = '<td colspan="' . (STATIC_FIELD_COUNT - 1) . '">&nbsp;</td>';

        // -----
        // This notification provides the observer with access to an array of content which is
        // rendered via this script.  That array of content, if updated, is expected to contain
        // an array of associative arrays which define the content (either a column of data or a colspan) to
        // fill-out the row based on the number of fields that they've 'registered' via the
        // NOTIFY_POSM_VIEW_ALL_START_BODY notification!
        //
        // $additional_content = [
        //   [
        //      'text' => 'Column Title',           //- The title to use for the column (required)
        //      'align' => 'left|center|right',     //- The text direction for the column's heading (optional)
        //      'params' => 'HTML parameters list', //- The optional additional parameters, e.g. 'colspan="5"' if the observer
        //                                          //-  isn't adding content to more than one of its columns for this row element.
        //   ],
        //   ...
        // ];
        //
        // The $products_name_extra_info value returned is prepended to the link to the product's name/definition.
        //
        $additional_columns_copy = $products_name_additional_columns;
        $additional_content = [];
        $zco_notifier->notify(
            'NOTIFY_POSM_VIEW_ALL_PRODUCTS_NAME',
            $products_id,
            $products_name_extra_info,
            $additional_content
        );

        // -----
        // A mixture of observers monitoring the legacy notification and the now-current one will result in
        // a wonky display on the product's name row.  If both notifications come back with additional
        // content, log a PHP Notice and ignore all the inputs; the product's name row will be shown full-table width.
        //
        $additional_content_count = count($additional_content);
        $name_column_parameters = ' colspan="' . STATIC_FIELD_COUNT . '"';
        if ($additional_columns_copy !== $products_name_additional_columns && $additional_content_count !== 0) {
            trigger_error('Multiple observers provided additional product-name content: ' . $products_name_additional_columns . ', ' . json_encode($additional_content), E_USER_NOTICE);
            $products_name_additional_columns = '';
        // -----
        // Otherwise, if neither notification resulted in additional columns being added, the product's name row will be shown
        // full-table width.
        //
        } elseif ($additional_columns_copy === $products_name_additional_columns && $additional_content_count === 0) {
            $products_name_additional_columns = '';
        // -----
        // Otherwise, one or the other notification resulted in additional columns being added.  If it was
        // an observer responding to the legacy notification, reset the parameters to be applied to the
        // product's name-column.
        //
        } elseif ($additional_columns_copy !== $products_name_additional_columns) {
            $name_column_parameters = '';
        // -----
        // Finally (!), an observer responded with additional columns to be added based on the $additional_content
        // array.
        //
        } else {
            $name_column_parameters = ' colspan="' . ($base_static_field_count - 1) . '"';
            $products_name_additional_columns = '';
        }
?>
                <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"<?= $name_column_parameters ?>>
                        <?= $products_name_extra_info ?>
                        <a href="<?= zen_href_link(FILENAME_PRODUCTS_OPTIONS_STOCK, "pID=$products_id&category_id=" . $next_product['master_categories_id']) ?>">
                            <?= $next_product['products_name'] ?>
                        </a>
                    </td>
                    <?= $products_name_additional_columns ?>
<?php
        // -----
        // If additional content was provided during the now-current notification's processing, add those columns
        // to the header.
        //
        if ($additional_content_count !== 0) {
            foreach ($additional_content as $next_column) {
                $column_align_class = '';
                if (isset($next_column['align']) && ($next_column['align'] === 'right' || $next_column['align'] === 'center')) {
                    $column_align_class = ' text-' . $next_column['align'];
                }
                $additional_parameters = (isset($next_column['params'])) ? ' ' . $next_column['params'] : '';
?>
                    <td class="dataTableHeadingContent<?= $column_align_class ?>"<?= $additional_parameters ?>>
                        <?= $next_column['text'] ?>
                    </td>
<?php
            }

            // -----
            // Now output a place-holder heading for the quantity column.
            //
?>
                    <td class="dataTableHeadingContent">&nbsp;</td>
<?php
        }
?>
                </tr>
<?php
        $product_options = $posm_view_all->outputProduct($products_id, $view_all);
        foreach ($product_options as $current_option) {
            $pos_id = $current_option['fields']['pos_id'];

            $pos_model = isset($_POST['model'][$pos_id]) ? zen_output_string_protected($_POST['model'][$pos_id]) : $current_option['fields']['pos_model'];
            $extra_model_class = (POSM_DUPLICATE_MODELNUMS !== 'Allow' && posm_modelnum_is_duplicate($pos_id, $pos_model)) ? ' duplicate' : '';
            if (POSM_VIEW_ALL_MODEL_UPDATE === 'true') {
                $pos_model = zen_draw_input_field("model[$pos_id]", $pos_model, 'class="model-num' . $extra_model_class . '"' . $model_field_size);
            }
?>
                <tr class="hoverRow">
                    <td>&nbsp;</td>
                    <td class="dataTableContent"><?= $current_option['option_name'] ?></td>
                    <td class="dataTableContent text-center model"><?= $pos_model ?></td>
<?php
            // -----
            // This notification provides the observer with access to an array of content which is
            // rendered via this script.  That array of content, if updated, is expected to contain
            // an array of associative arrays ... one array element for each added column of data:
            //
            // $additional_content = [
            //   [
            //      'text' => 'Column Data',            //- The data to be included for the column (required)
            //      'align' => 'left|center|right',     //- The text direction for the column's data (optional)
            //      'params' => 'Column Parameters',    //- Any additional HTML non-class parameters to apply to the data (optional)
            //      'class' => '',                      //- Any additional HTML class-name to apply to the data (optional)
            //   ],
            //   ...
            // ];
            //
            // NOTE: The observer is expected to include the same number of columns of data as those supplied
            // for the NOTIFY_POSM_VIEW_ALL_TABLE_HEADING notification, above.
            //
            $additional_content = [];
            $zco_notifier->notify('NOTIFY_POSM_VIEW_ALL_INSERT_DATA', $current_option['fields'], $additional_content);
            foreach ($additional_content as $content) {
                $additional_class = (isset($content['align'])) ? ' text-' . $content['align'] : '';
                if (isset($content['class'])) {
                    $additional_class .= ' ' . $content['class'];
                }
                $parameters = (isset($content['params'])) ? ' ' . $content['params'] : '';
?>
                    <td class="dataTableContent<?= $additional_class ?>"<?= $parameters ?>>
                        <?= $content['text'] ?>
                    </td>
<?php
            }
            $quantity = $_POST['quantity'][$pos_id] ?? $current_option['fields']['products_quantity'];
            $out_of_stock_class = ($quantity <= $posm_stock_reorder_level) ? 'class="out-of-stock"' : '';
?>
                    <td class="dataTableContent text-center">
                        <?= zen_draw_input_field("quantity[$pos_id]", $quantity, $out_of_stock_class) ?>
                    </td>
                </tr>
<?php
        }
    }  // END loop displaying information for all products
?>
                <tr>
                    <td colspan="<?= STATIC_FIELD_COUNT - 1 ?>">&nbsp;</td>
                    <td class="dataTableContent text-center">
                        <button name="update_x" class="btn btn-sm btn-primary" type="submit" title="<?= TEXT_UPDATE_ALT ?>">
                            <?= IMAGE_UPDATE ?>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    <?= '</form>' ?>

    <div class="row">
        <div class="col-md-6 smallText">
            <?= $view_all_split->display_count($view_all_query_numrows, POSM_MAX_PRODUCTS_VIEW_ALL, $page_num, POSM_TEXT_DISPLAY_NUMBER_OF_PRODUCTS) ?>
        </div>
        <div class="col-md-6 smallText text-right">
            <?= $view_all_split->display_links($view_all_query_numrows, POSM_MAX_PRODUCTS_VIEW_ALL, MAX_DISPLAY_PAGE_LINKS, $page_num, zen_get_all_get_params(['page'])) ?>
        </div>
    </div>
<?php
}  // One or more products was found
?>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
<?php
if (POSM_DUPLICATE_MODELNUMS !== 'Allow') {
?>
<script>
$(function() {
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
                document.modify_form['model['+posID+']'].focus();
<?php
    }
?>
            }
        });
    });
});
</script>
<?php
}
?>
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
