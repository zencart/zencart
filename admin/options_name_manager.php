<?php
/**steve for phpStorm inspections
 * @var messageStack $messageStack
 * @var zcObserverLogEventListener $zco_notifier
 */
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2020 April 15 Modified in v1.5.7 $
 */
require 'includes/application_top.php';
$languages = zen_get_languages();

//required for display of products price in Global Tools selection
require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

// check for damaged database, caused by users indiscriminately deleting table data
$ary = [];
$chk_option_values = $db->Execute("SELECT language_id
                                   FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   WHERE products_options_values_id = " . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID);
foreach ($chk_option_values as $item) {
    $ary[] = (int)$item['language_id'];
}
for ($i = 0, $n = count($languages); $i < $n; $i++) {
    if ((int)$languages[$i]['id'] > 0 && !in_array((int)$languages[$i]['id'], $ary, true)) {
        $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name)
                  VALUES (" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . ", " . (int)$languages[$i]['id'] . ", 'TEXT')");
    }
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$currentPage = (!empty($_GET['page']) ? (int)$_GET['page'] : 0);

//Global Tools
if (!empty($_POST['option_order_by'])) { // from Option Name sort order dropdown
    $option_order_by = $_POST['option_order_by'] === 'id' ? 'id' : 'name';
} else {
    $option_order_by = 'name';
}
if (!empty($_POST['options_id'])) { // selected Option Name from dropdowns (not used for ADD/DELETE ALL)
    $_SESSION['selectedOptionId'] = (int)$_POST['options_id'];
}
if (!empty($_POST['form_wrapper_id'])) { // id of surrounding div of the submitted form: on redirect, used to focus browser viewport on same id
    $form_id = zen_db_prepare_input($_POST['form_wrapper_id']);
}

if (zen_not_null($action)) {
    switch ($action) {
        case 'add_product_options': // insert a new Option Name
            $option_name_array = $_POST['option_name']; // array
            $products_options_sort_order = $_POST['products_options_sort_order']; // array
            $option_type = (int)($_POST['option_type']);
            $max_options_id_values = $db->Execute("SELECT COALESCE(MAX(products_options_id), 0) + 1 AS next_id FROM " . TABLE_PRODUCTS_OPTIONS);
            $new_products_options_id = (int)$max_options_id_values->fields['next_id'];

            for ($i = 0, $n = count($languages); $i < $n; $i++) {
                $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);

                $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, products_options_name, language_id, products_options_sort_order, products_options_type)
                      VALUES (" . $new_products_options_id . ",
		                      '" . zen_db_input($option_name) . "',
                              " . (int)$languages[$i]['id'] . ",
                              " . (int)$products_options_sort_order[$languages[$i]['id']] . ",
                              " . (int)zen_db_input($option_type) . ")");
            }

            switch ($option_type) {
                case PRODUCTS_OPTIONS_TYPE_TEXT:
                case PRODUCTS_OPTIONS_TYPE_FILE:
                    $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_values_id, products_options_id)
                        VALUES (" . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . ",
                                " . $new_products_options_id . ")");
                    break;
            }

// check and alert for duplicate names (is allowed)
            $duplicate_option = '';
            for ($i = 0, $n = count($languages); $i < $n; $i++) {
                $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);

                if (!empty($option_name)) {
                    $check = $db->Execute("SELECT products_options_name
                                 FROM " . TABLE_PRODUCTS_OPTIONS . "
                                 WHERE language_id = " . (int)$languages[$i]['id'] . "
                                 AND products_options_name = '" . zen_db_input($option_name) . "' LIMIT 2");
                    if ($check->RecordCount() > 1) {
                        $messageStack->add_session(sprintf(TEXT_WARNING_DUPLICATE_OPTION_NAME, $new_products_options_id, $option_name, zen_get_language_name($languages[$i]['id'])), 'caution');
                    }
                }
            }
            zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['action'])));
            break;

        case 'update_option_name':
            $option_name_array = $_POST['option_name'];
            $option_type = (int)$_POST['option_type'];
            $option_id = zen_db_prepare_input($_POST['option_id']);
            $products_options_sort_order_array = $_POST['products_options_sort_order'];

            $products_options_length_array = $_POST['products_options_length'];
            $products_options_comment_array = $_POST['products_options_comment'];
            $products_options_size_array = $_POST['products_options_size'];

            $products_options_images_per_row_array = $_POST['products_options_images_per_row'];
            $products_options_images_style_array = $_POST['products_options_images_style'];
            $products_options_rows_array = $_POST['products_options_rows'];

            for ($i = 0, $n = count($languages); $i < $n; $i++) {
                $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);
                $products_options_sort_order = (int)$products_options_sort_order_array[$languages[$i]['id']];


                $products_options_length = zen_db_prepare_input($products_options_length_array[$languages[$i]['id']]);
                $products_options_comment = zen_db_prepare_input($products_options_comment_array[$languages[$i]['id']]);
                $products_options_size = zen_db_prepare_input($products_options_size_array[$languages[$i]['id']]);

                $products_options_images_per_row = (int)$products_options_images_per_row_array[$languages[$i]['id']];
                $products_options_images_style = (int)$products_options_images_style_array[$languages[$i]['id']];
                $products_options_rows = (int)$products_options_rows_array[$languages[$i]['id']];

                $db->Execute("UPDATE " . TABLE_PRODUCTS_OPTIONS . "
                      SET products_options_name = '" . zen_db_input($option_name) . "',
                          products_options_type = '" . $option_type . "',
                          products_options_length = '" . zen_db_input($products_options_length) . "',
                          products_options_comment = '" . zen_db_input($products_options_comment) . "',
                          products_options_size = '" . zen_db_input($products_options_size) . "',
                          products_options_sort_order = " . $products_options_sort_order . ",
                          products_options_images_per_row = " . $products_options_images_per_row . ",
                          products_options_images_style = " . $products_options_images_style . ",
                          products_options_rows = " . $products_options_rows . "
                      WHERE products_options_id = " . (int)$option_id . "
                      AND language_id = " . (int)$languages[$i]['id']);
            }

            switch ($option_type) {
                case PRODUCTS_OPTIONS_TYPE_TEXT:
                case PRODUCTS_OPTIONS_TYPE_FILE:
// disabled because this could cause trouble if someone changed types unintentionally and deleted all their option values.  Shops with small numbers of values per option should consider uncommenting this.
//            zen_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " where products_options_id = '" . $_POST['option_id'] . "'");
// add in a record if none exists when option type is switched
                    $check_type = $db->Execute("SELECT COUNT(products_options_id) AS count
                                      FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                      WHERE products_options_id = " . (int)$_POST['option_id'] . "
                                      AND products_options_values_id = 0");
                    if ($check_type->fields['count'] === 0) {
                        $db->Execute("INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_values_to_products_options_id, products_options_id, products_options_values_id)
                          VALUES (NULL, " . (int)$_POST['option_id'] . ", " . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID . ")");
                    }
                    break;
                default:
// if switched from file or text remove 0
                    $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                        WHERE products_options_id = " . (int)$_POST['option_id'] . "
                        AND products_options_values_id = " . (int)PRODUCTS_OPTIONS_VALUES_TEXT_ID);
                    break;
            }

// alert if possible duplicate
            $duplicate_option = '';
            for ($i = 0, $n = count($languages); $i < $n; $i++) {
                $option_name = zen_db_prepare_input($option_name_array[$languages[$i]['id']]);

                $check = $db->Execute("SELECT products_options_name
                               FROM " . TABLE_PRODUCTS_OPTIONS . "
                               WHERE language_id = " . (int)$languages[$i]['id'] . "
                               AND products_options_name = '" . zen_db_input($option_name) . "'");

                if ($check->RecordCount() > 1) {//steve added
                    $messageStack->add_session(sprintf(TEXT_WARNING_DUPLICATE_OPTION_NAME, $option_id, $option_name, zen_get_language_name($languages[$i]['id'])), 'caution');
                }
            }
            zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['action'])));
            break;
        case 'delete_option':
            $option_id = zen_db_prepare_input($_GET['option_id']);

            $remove_option_values = $db->Execute("SELECT products_options_id, products_options_values_id
                                            FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                            WHERE products_options_id = " . (int)$option_id);

            foreach ($remove_option_values as $remove_option_value) {
                $zco_notifier->notify('OPTIONS_NAME_MANAGER_DELETE_OPTION', ['option_id' => $option_id, 'options_values_id' => (int)$remove_option_value['products_options_values_id']]);
                $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                      WHERE products_options_values_id = " . (int)$remove_option_value['products_options_values_id'] . "
                      AND products_options_values_id != 0");
            }

            $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS . "
                    WHERE products_options_id = " . (int)$option_id);

            $db->Execute("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                    WHERE products_options_id = " . (int)$option_id);

            zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['action'])));
            break;

/////////////////////////////////////
// Global Tools
// Add and Delete
        case 'update_options_values': // same form action for all three ADD and DELETE tools

            $update_to = (int)$_GET['update_to']; // 0 - all, 1 - 1 product, 2 - all products in a category
            $product_to_update = (!empty($_POST['product_to_update']) ? (int)$_POST['product_to_update'] : -1); // a product is selected, no set if only Option Name changed
            $category_to_update = (!empty($_POST['category_to_update']) ? (int)$_POST['category_to_update'] : -1); // a category is selected, no set if only Option Name changed
            if (
                (int)$_POST['options_id'] === -1 // Option Name select has been changed to no selection
                || ($update_to === 1 && $product_to_update === -1) // Option Name selected, but no product has been selected
                || ($update_to === 2 && $category_to_update === -1) // Option Name selected, no category has been selected
            ) { // reload page with Option Name selection as selected in dropdowns, and results filtered (not used for ADD/DELETE ALL)
                zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['action', 'update_action', 'update_to'])) . '#' . $form_id);
            }
            $update_action = (int)$_GET['update_action'];
            $optionName = zen_get_option_name_language((int)$_POST['options_id'], $_SESSION['languages_id']);//for messageStack

            switch ($update_to) {
                case (0): // update ALL products with this Option Name
                    // get all matching products
                    $all_update_products = $db->Execute("SELECT DISTINCT products_id
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                               WHERE options_id = " . (int)$_POST['options_id']);
                    break;

                case (1): // update ONE product with this Option Name
                    // get one matching product
                    $product_to_update = (int)$_POST['product_to_update'];
                    $all_update_products = $db->Execute("SELECT DISTINCT products_id
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                               WHERE options_id = " . (int)$_POST['options_id'] . "
                                               AND products_id = " . $product_to_update);
                    break;

                case (2):// update ALL products with this Option Name in a specific category
                    // get all matching products in a category
                    $category_to_update = (int)$_POST['category_to_update'];
                    $all_update_products = $db->Execute("SELECT DISTINCT pa.products_id
                                               FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                               LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc ON pa.products_id = ptc.products_id
                                               WHERE ptc.categories_id = " . $category_to_update . "
                                               AND pa.options_id = " . (int)$_POST['options_id'] . "
                                               AND pa.products_id = ptc.products_id");
                    break;
            }

            if ($all_update_products->RecordCount() < 1) { // no matching products found
                $messageStack->add_session(sprintf(ERROR_PRODUCTS_OPTIONS_PRODUCTS, zen_get_option_name_language((int)$_POST['options_id'], $_SESSION['languages_id'])), 'caution');
            } else {
                // get Option Values for this Option Name
                $all_options_values = $db->Execute("SELECT products_options_id, products_options_values_id
                                                FROM " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . "
                                                WHERE products_options_id = " . (int)$_POST['options_id']);

                $count = 0; // record total of products modified for messageStack
                if ($update_action === 0) { // $update_action == 0 ADD Option Values
                    foreach ($all_update_products as $all_update_product) {
                        $updated = 'false';
                        foreach ($all_options_values as $all_options_value) {
                            $check_all_options_values = $db->Execute("SELECT products_attributes_id
                                                        FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                                        WHERE products_id = " . (int)$all_update_product['products_id'] . "
                                                        AND options_id = " . (int)$all_options_value['products_options_id'] . "
                                                        AND options_values_id = " . (int)$all_options_value['products_options_values_id']);

                            if ($check_all_options_values->RecordCount() < 1) { // this Option Value is missing from this product
                                $updated = 'true';
                                $db->Execute("INSERT INTO " . TABLE_PRODUCTS_ATTRIBUTES . " (products_id, options_id, options_values_id)
                              VALUES (" . (int)$all_update_product['products_id'] . ", " . (int)$all_options_value['products_options_id'] . ", " . (int)$all_options_value['products_options_values_id'] . ")");

                                //for user confirmation message
                                $product_name = zen_get_products_model((int)$all_update_product['products_id']) . ' - ' . zen_get_products_name((int)$all_update_product['products_id']);
                                $value_name = $db->Execute("SELECT products_options_values_name
                                                  FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                                  WHERE products_options_values_id = " . (int)$all_options_value['products_options_values_id'] . "
                                                  AND language_id = " . (int)$_SESSION['languages_id'] . " LIMIT 1");
                                $messageStack->add_session(sprintf(SUCCESS_PRODUCT_OPTION_VALUE, $optionName, $value_name->fields['products_options_values_name'], $product_name), 'success');
                            }
                        }
                        if ($updated === 'true') { // Option Values have been added to the product, update the Sort Order
                            zen_update_attributes_products_option_values_sort_order($all_update_product['products_id']);
                            $messageStack->add_session(sprintf(SUCCESS_PRODUCT_OPTIONS_VALUES_SORT_ORDER, $optionName, $product_name), 'success');
                            $count++;
                        }
                    }
                    if ($updated === 'true') {
                        $messageStack->add_session(sprintf(SUCCESS_PRODUCTS_OPTIONS_VALUES, $optionName, $count), 'success');
                    } else {
                        $messageStack->add_session(sprintf(ERROR_PRODUCTS_OPTIONS_VALUES, zen_get_option_name_language((int)$_POST['options_id'], $_SESSION['languages_id'])), 'caution');
                    }
                } else { // $update_action !=0 Delete Option Values
                    foreach ($all_update_products as $all_update_product) {
                        $updated = 'false';
                        foreach ($all_options_values as $all_options_value) {
                            $check_all_options_values = $db->Execute("SELECT products_attributes_id
                                                        FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                                                        WHERE products_id = " . (int)$all_update_product['products_id'] . "
                                                        AND options_id = " . (int)$all_options_value['products_options_id'] . "
                                                        AND options_values_id= " . (int)$all_options_value['products_options_values_id']);
                            if ($check_all_options_values->RecordCount() >= 1) {
                                $updated = 'true';
                                $db->Execute("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
                              WHERE products_id = " . (int)$all_update_product['products_id'] . "
                              AND options_id = " . (int)$_POST['options_id']);
                                $zco_notifier->notify('OPTIONS_NAME_MANAGER_UPDATE_OPTIONS_VALUES_DELETE', [
                                        'products_id' => $all_update_product['products_id'],
                                        'options_id' => $all_options_value['products_options_id'],
                                        'options_values_id' => $all_options_value['products_options_values_id']
                                    ]
                                );
                            }
                        }
                        if ($updated === 'true') {
                            $product_name = zen_get_products_model((int)$all_update_product['products_id']) . ' - ' . zen_get_products_name((int)$all_update_product['products_id']);
                            $messageStack->add_session(sprintf(SUCCESS_PRODUCT_OPTION_VALUES_DELETED, $optionName, $product_name), 'success');
                            $count++;
                        }
                    }
                    if ($updated === 'true') {
                        $messageStack->add_session(sprintf(SUCCESS_PRODUCTS_OPTIONS_VALUES_DELETED, $optionName, $count), 'success');
                    }
                } // update_action
            } // no products found
            zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['action', 'update_action', 'update_to'])) . '#' . $form_id);
            break;
////////////////////////////////////
// copy features
        case 'copy_options_values':
            $options_id_from = (int)$_POST['options_id_from'];
            $options_id_to = (int)$_POST['options_id_to'];

            if ($options_id_from === $options_id_to) {
                // cannot copy to self
                $messageStack->add_session(sprintf(ERROR_OPTION_VALUES_COPIED, zen_options_name($options_id_from), zen_options_name($options_id_to), 'error'));
            } else {
                $max_options_id_values = $db->Execute("SELECT MAX(products_options_values_id) + 1 AS next_id
                                                       FROM " . TABLE_PRODUCTS_OPTIONS_VALUES);
                $next_id = (int)$max_options_id_values->fields['next_id'];

                $copy_from_values = $db->Execute("SELECT pov.*
                                          FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov
                                          LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo ON pov.products_options_values_id = povtpo.products_options_values_id
                                          WHERE povtpo.products_options_id = " . $options_id_from . "
                                          ORDER BY povtpo.products_options_values_id");
                if ($copy_from_values->RecordCount() > 0) {
                    // successful copy
                    //todo remove $next_id = ($start_id->fields['products_options_values_id'] + 1);
                    while (!$copy_from_values->EOF) {
                        $current_id = (int)$copy_from_values->fields['products_options_values_id'];
                        $sql = "INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order)
                    VALUES (" . $next_id . ", " . (int)$copy_from_values->fields['language_id'] . ", '" . $copy_from_values->fields['products_options_values_name'] . "', " . (int)$copy_from_values->fields['products_options_values_sort_order'] . ")";
                        $db->Execute($sql);
                        $copy_from_values->MoveNext();
                        if ((int)$copy_from_values->fields['products_options_values_id'] !== $current_id || $copy_from_values->EOF) {//todo check these ints
                            $sql = "INSERT INTO " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " (products_options_id, products_options_values_id)
                      VALUES (" . $options_id_to . ", " . $next_id . ")";
                            $db->Execute($sql);
                            $next_id++;
                        }
                    }
                    $messageStack->add(sprintf(SUCCESS_OPTION_VALUES_COPIED, zen_options_name($options_id_from), zen_options_name($options_id_to)), 'success');
                } else {
                    // warning nothing to copy
                    $messageStack->add(sprintf(ERROR_OPTION_VALUES_NONE, zen_options_name($options_id_from), 'error'));
                }
            }
            zen_redirect(zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['action', 'update_action', 'update_to'])));
            break;
////////////////////////////////////
    }
}

$products_options_types_list = [];
$products_options_type_array = $db->Execute("SELECT products_options_types_id, products_options_types_name
                                             FROM " . TABLE_PRODUCTS_OPTIONS_TYPES . "
                                             ORDER BY products_options_types_id");
foreach ($products_options_type_array as $products_options_type) {
    $products_options_types_list[$products_options_type['products_options_types_id']] = $products_options_type['products_options_types_name'];
}

$optionTypeValuesArray = [];
foreach ($products_options_types_list as $id => $text) {
    $optionTypeValuesArray[] = compact('id', 'text');
}

/**
 * @param $opt_type
 * @return mixed
 */
function translate_type_to_name($opt_type)
{
    global $products_options_types_list;
    return $products_options_types_list[$opt_type];
}

?>
    <!doctype html>
    <html <?php echo HTML_PARAMS; ?>>
    <head>
        <meta charset="<?php echo CHARSET; ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo TITLE; ?></title>
        <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
        <?php //zen4all removed this css and following script ?>
        <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
        <script src="includes/menu.js"></script>
        <script>
            function init() {
                cssjsmenu('navbar');
                if (document.getElementById) {
                    var kill = document.getElementById('hoverJS');
                    kill.disabled = true;
                }
            }
        </script>
    </head>
    <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
        <!-- header_eof //-->
    <div class="container-fluid">
        <!-- body //-->
        <!-- body_text //-->
        <h1><?php echo HEADING_TITLE; ?></h1>
        <div class="row text-right">
            <a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER) ?>" class="btn btn-default" role="button"><?php echo TEXT_ATTRIBUTES_CONTROLLER; ?></a>&nbsp;
            <a href="<?php echo zen_href_link(FILENAME_OPTIONS_VALUES_MANAGER) ?>" class="btn btn-default" role="button"><?php echo IMAGE_OPTION_VALUES; ?></a>
        </div>
        <?php echo zen_draw_separator('pixel_black.gif', '100%', '2');

        // bof: Delete Option Name
        if ($action === 'delete_product_option') {
            $options = $db->Execute("SELECT products_options_id, products_options_name
                                 FROM " . TABLE_PRODUCTS_OPTIONS . "
                                 WHERE products_options_id = " . (int)$_GET['option_id'] . "
                                 AND language_id = " . (int)$_SESSION['languages_id']); ?>

            <div class="row"><h2><?php echo TEXT_OPTION_NAME . ': "' . $options->fields['products_options_name'] . '"'; ?></h2></div>

            <?php
            $products = $db->Execute("SELECT p.products_id, pd.products_name, pov.products_options_values_name
                                      FROM " . TABLE_PRODUCTS . " p,
                                           " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov,
                                           " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                           " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                      WHERE pd.products_id = p.products_id
                                      AND pov.language_id = " . (int)$_SESSION['languages_id'] . "
                                      AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                      AND pa.products_id = p.products_id
                                      AND pa.options_id = " . (int)$_GET['option_id'] . "
                                      AND pov.products_options_values_id = pa.options_values_id
                                      ORDER BY pd.products_name");

            if ($products->RecordCount() > 0) { // there are products using Values associated with this Name: do not permit deletion of Option Name ?>
                <div class="row">
                    <div class="col-sm-10 errorText"><h3><?php echo TEXT_WARNING_OF_DELETE; ?></h3></div>
                    <div class="col-sm-2"><?php //todo zen4all current page; ?>
                        <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . 'option_order_by=' . $option_order_by); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                    </div>
                </div>
                <table class="table table-striped">
                    <tr class="dataTableHeadingRow">
                        <th class="dataTableHeadingContent text-center"><?php echo TEXT_OPTION_ID; ?></th>
                        <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT; ?></th>
                        <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_VALUE; ?></th>
                        <th class="dataTableHeadingContent">&nbsp;</th>
                    </tr>
                    <?php
                    foreach ($products as $product) { ?>
                        <tr>
                            <td class="text-center"><?php echo $product['products_id']; ?></td>
                            <td><?php echo $product['products_name']; ?></td>
                            <td><?php echo $product['products_options_values_name']; ?></td>
                            <td><a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $product['products_id']) ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT_ATTRIBUTES; ?></a></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="4"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></td>
                    </tr>
                </table>
            <?php } else { ?>
                <div class="row">
                    <div class="col-sm-9"><?php echo TEXT_OK_TO_DELETE; ?></div>
                    <div class="col-sm-3 text-right">
                        <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=delete_option&option_id=' . $_GET['option_id'] . '&' . ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . 'option_order_by=' . $option_order_by); ?>" class="btn btn-danger" role="button"><?php echo IMAGE_DELETE; ?></a>
                        <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . 'option_order_by=' . $option_order_by); ?>" class="btn btn-default"
                           role="button"><?php echo TEXT_CANCEL; ?></a>
                    </div>
                </div>
            <?php }
            // eof: Delete Option Name

        } else {
            //todo steve, hide sort select when no options
            $options_query_raw = "SELECT *
                                FROM " . TABLE_PRODUCTS_OPTIONS . "
                                WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                ORDER BY " . ($option_order_by === 'id' ? 'products_options_id' : 'products_options_name');
            //todo zen4all current page
            $options_split = new splitPageResults($currentPage, MAX_ROW_LISTS_OPTIONS, $options_query_raw, $options_query_numrows);
            $options_values = $db->Execute($options_query_raw);
            if ($options_values->RecordCount() > 1) {
                echo zen_draw_form('option_order_by_form', FILENAME_OPTIONS_NAME_MANAGER, '', 'post', 'class="form-horizontal"'); ?>
                <div>
                    <label for="option_order_by"><?php echo TEXT_ORDER_BY; ?></label>
                    <select name="option_order_by" onchange="this.form.submit();" id="option_order_by">
                        <option value="id"<?php echo $option_order_by === 'id' ? ' selected' : ''; ?>><?php echo TEXT_OPTION_ID; ?></option>
                        <option value="name"<?php echo $option_order_by === 'name' ? ' selected' : ''; ?>><?php echo TEXT_OPTION_NAME; ?></option>
                    </select>
                </div>
                <?php echo '</form>'; ?>
                <div class="row">
                    <?php echo zen_draw_separator('pixel_trans.gif')
                    //todo zen4all current page
                    ?>
                    <div class="col-sm-6"><?php echo $options_split->display_count($options_query_numrows, MAX_ROW_LISTS_OPTIONS, $currentPage, TEXT_DISPLAY_NUMBER_OF_OPTIONS); ?></div>
                    <?php $exclude_array = ['page']; //todo zen4al added
                    ?>
                    <div class="col-sm-6 text-right"><?php echo $options_split->display_links($options_query_numrows, MAX_ROW_LISTS_OPTIONS, MAX_DISPLAY_PAGE_LINKS, $currentPage, zen_get_all_get_params($exclude_array)); ?></div>
                </div>
            <?php } ?>
            <table class="table table-striped">
                <thead>
                <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent text-center"><?php echo TEXT_OPTION_ID; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_NAME; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_OPT_TYPE; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo TEXT_SORT_ORDER; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_OPTION_VALUE_SIZE; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_OPTION_VALUE_MAX; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                // $next_id = 1; //todo remove??
                foreach ($options_values as $options_value) { //list the Option Names

                    // Edit an Option Name
                    if (($action === 'update_option') && ((int)$_GET['option_id'] === (int)$options_value['products_options_id'])) { ?>
                        <tr>
                            <td colspan="7"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></td>
                        </tr>
                        <tr>
                            <td colspan="7">
                                <?php echo zen_draw_form('option', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_option_name' . '&' . ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . 'option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"');
                                $productsOptionsImagesStyleArray = [
                                    ['id' => '0', 'text' => TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_0],
                                    ['id' => '1', 'text' => TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_1],
                                    ['id' => '2', 'text' => TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_2],
                                    ['id' => '3', 'text' => TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_3],
                                    ['id' => '4', 'text' => TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_4],
                                    ['id' => '5', 'text' => TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE_5]
                                ];
                                ?>
                                <table class="table">
                                    <tr>
                                        <?php
                                        //todo zen4all current page
                                        $option_name_input = '';
                                        $sort_order_input = '';
                                        $inputs2 = '';
                                        for ($i = 0, $n = count($languages); $i < $n; $i++) {
                                            $option_name = $db->Execute("SELECT products_options_name, products_options_sort_order, products_options_size, products_options_length, products_options_comment, products_options_images_per_row, products_options_images_style, products_options_rows
                                                   FROM " . TABLE_PRODUCTS_OPTIONS . "
                                                   WHERE products_options_id = " . (int)$options_value['products_options_id'] . "
                                                   AND language_id = " . (int)$languages[$i]['id']);
//todo zen4all added int to option id on following lines
                                            $option_name_input .= zen_draw_label(TABLE_HEADING_OPT_NAME . ($n > 1 ? ' (' . $languages[$i]['code'] . ')' : ''), 'option_name[' . $languages[$i]['id'] . ']', 'class="control-label"') . ': ';
                                            $option_name_input .= zen_draw_input_field('option_name[' . $languages[$i]['id'] . ']', zen_output_string($option_name->fields['products_options_name']), zen_set_field_length(TABLE_PRODUCTS_OPTIONS, 'products_options_name', 40) . ' class="form-control" id="option_name[' . $languages[$i]['id'] . ']" required');
                                            ($i + 1 < $n ? $option_name_input .= '<br>' : '');

                                            $sort_order_input .= zen_draw_label(TEXT_SORT_ORDER . ':', 'products_options_sort_order[' . $languages[$i]['id'] . ']', 'class="control-label"');
                                            $sort_order_input .= zen_draw_input_field('products_options_sort_order[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_sort_order'], 'size="3" class="form-control text-center" id="products_options_sort_order[' . $languages[$i]['id'] . ']"');
                                            ($i + 1 < $n ? $sort_order_input .= '<br>' : '');

                                            $inputs2 .= ($n > 1 ? '<h4>' . zen_get_language_icon($languages[$i]['id']) . '</h4>' : '');
                                            $inputs2 .= '<div class="row">';
                                            $inputs2 .= '<div class="col-sm-12">';
                                            $inputs2 .= zen_draw_label(TEXT_OPTION_VALUE_COMMENTS . ':', 'products_options_comment[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_comment[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_comment'], 'class="form-control" style="width:100%" id="products_options_comment[' . $languages[$i]['id'] . ']"');
                                            $inputs2 .= '</div>';
                                            $inputs2 .= '</div>';
                                            $inputs2 .= '<br><div class="row"><div class="col-sm-12">' . TEXT_OPTION_ATTRIBUTE_MAX_LENGTH . '</div></div>';
                                            $inputs2 .= '<div class="row">';
                                            $inputs2 .= '<div class="col-sm-4">';
                                            $inputs2 .= zen_draw_label(TEXT_OPTION_VALUE_ROWS . ':', 'products_options_rows[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_rows[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_rows'], 'class="form-control" id="products_options_rows[' . $languages[$i]['id'] . ']"', '', 'number');
                                            $inputs2 .= '</div>';
                                            $inputs2 .= '<div class="col-sm-4">';
                                            $inputs2 .= zen_draw_label(TEXT_OPTION_VALUE_SIZE . ':', 'products_options_size[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_size[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_size'], 'class="form-control" id="products_options_size[' . $languages[$i]['id'] . ']"', '', 'number');
                                            $inputs2 .= '</div>';
                                            $inputs2 .= '<div class="col-sm-4">';
                                            $inputs2 .= zen_draw_label(TEXT_OPTION_VALUE_MAX . ':', 'products_options_length[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_length[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_length'], 'class="form-control" id="products_options_length[' . $languages[$i]['id'] . ']"', '', 'number');
                                            $inputs2 .= '</div>';
                                            $inputs2 .= '</div>';
                                            $inputs2 .= '<div class="row">';
                                            $inputs2 .= '<div class="col-sm-3">';
                                            $inputs2 .= zen_draw_label(TEXT_OPTION_ATTRIBUTE_IMAGES_PER_ROW . ':', 'products_options_images_per_row[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_input_field('products_options_images_per_row[' . $languages[$i]['id'] . ']', $option_name->fields['products_options_images_per_row'], 'class="form-control" id="products_options_images_per_row[' . $languages[$i]['id'] . ']"', '', 'number');
                                            $inputs2 .= '</div>';
                                            $inputs2 .= '<div class="col-sm-9">';
                                            $inputs2 .= zen_draw_label(TEXT_OPTION_ATTRIBUTE_IMAGES_STYLE . ' - <a href="' . DIR_WS_IMAGES . 'option_name_manager-attribute_layouts.gif" target="_blank">' . TEXT_OPTION_ATTRIBUTE_LAYOUTS_EXAMPLE . '</a>:', 'products_options_images_style[' . $languages[$i]['id'] . ']', 'class="control-label"') . zen_draw_pull_down_menu('products_options_images_style[' . $languages[$i]['id'] . ']', $productsOptionsImagesStyleArray,
                                                    $option_name->fields['products_options_images_style'], 'class="form-control" style="width:100%" id="products_options_images_style[' . $languages[$i]['id'] . ']"');
                                            $inputs2 .= '</div>';
                                            $inputs2 .= '</div>';
                                        }
                                        ?>
                                        <td class="text-center">
                                            <?php
                                            echo $options_value['products_options_id'];
                                            echo zen_draw_hidden_field('option_id', $options_value['products_options_id']); ?>
                                        </td>
                                        <td><?php echo $option_name_input; ?></td>
                                        <td><?php echo zen_draw_label(TABLE_HEADING_OPT_TYPE . ':', 'edit_options_type', 'class="control-label"') . zen_draw_pull_down_menu('option_type', $optionTypeValuesArray, $options_value['products_options_type'], 'class="form-control" id="edit_options_type"'); ?></td>
                                        <td><?php echo $sort_order_input; ?></td>
                                        <td class="text-center">
                                            <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
                                            <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . 'option_order_by=' . $option_order_by); ?>" class="btn btn-default" role="button"><?php echo TEXT_CANCEL; ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5">
                                            <?php echo $inputs2; ?>
                                        </td>
                                    </tr>
                                    <?php //todo steve removed ?>
                                </table>
                                <?php echo '</form>' . "\n"; ?>
                            </td>
                        </tr>
                        <?php
                        // eof: edit Option Name

                    } else { ?>
                        <tr>
                            <td class="text-center"><?php echo $options_value["products_options_id"]; ?></td>
                            <td><?php echo $options_value["products_options_name"]; ?></td>
                            <td><?php echo translate_type_to_name($options_value["products_options_type"]); ?></td>
                            <td class="text-center"><?php echo $options_value["products_options_sort_order"]; ?></td>
                            <td class="text-center"><?php echo $options_value["products_options_size"]; ?></td>
                            <td class="text-center"><?php echo $options_value["products_options_length"]; ?></td>
                            <?php
                            // hide buttons when editing
                            if ($action === 'update_option') {
                                ?>
                                <td>&nbsp;</td>
                                <?php
                            } else {
                                ?>
                                <td class="text-center"> <?php //todo current page ?>
                                    <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=update_option&option_id=' . $options_value['products_options_id'] . '&option_order_by=' . $option_order_by . '&' . ($currentPage !== 0 ? 'page=' . $currentPage : '')); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT; ?></a>
                                    <a href="<?php echo zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, 'action=delete_product_option&option_id=' . $options_value['products_options_id'] . '&' . ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . 'option_order_by=' . $option_order_by, 'NONSSL'); ?>" class="btn btn-default" role="button"><?php echo IMAGE_DELETE; ?></a>
                                </td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                    }
                }

                // Insert a new Option Name
                if ($action !== 'update_option') { // show row for Inserting a new Option Name. Displayed by default/when no action set/on initial page load ?>
                    <tr>
                        <td colspan="7">
                            <button class="btn btn-primary toggleDisplay" role="button" title="<?php echo TEXT_CLICK_TO_SHOW_HIDE; ?>"><?php echo TEXT_INSERT_NEW_OPTION_NAME; ?></button>
                            <div id="insertOption" style="display: block">
                                <?php
                                $inputs = '';
                                $inputs2 = '';
                                for ($i = 0, $n = count($languages); $i < $n; $i++) {
//todo zen4all added divs
                                    $inputs .= zen_draw_label(($n > 1 ? zen_get_language_icon($languages[$i]['id']) . ' ' : ' ') . TABLE_HEADING_OPT_NAME . ':', 'option_name[' . $languages[$i]['id'] . ']', 'class="control-label"');
                                    $inputs .= zen_draw_input_field('option_name[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_PRODUCTS_OPTIONS, 'products_options_name', 20) . ' class="form-control" id="option_name[' . $languages[$i]['id'] . ']" placeholder="' . $languages[$i]['directory'] . '" required');
                                    ($i + 1 < $n ? $inputs .= '<br>' : '');
                                    $inputs2 .= zen_draw_label(TEXT_SORT_ORDER . ':', 'products_options_sort_order[' . $languages[$i]['id'] . ']');
                                    $inputs2 .= zen_draw_input_field('products_options_sort_order[' . $languages[$i]['id'] . ']', '0', 'size="3" class="form-control" id="products_options_sort_order[' . $languages[$i]['id'] . ']"');
                                    ($i + 1 < $n ? $inputs2 .= '<br>' : '');
                                }
                                echo zen_draw_form('options', FILENAME_OPTIONS_NAME_MANAGER, 'action=add_product_options' . '&' . ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '') . 'option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"');
                                ?>
                                <table class="table">
                                    <tr>
                                        <td><?php echo $inputs; ?></td>
                                        <td><?php echo $inputs2; ?></td>
                                        <td><?php echo zen_draw_label(TABLE_HEADING_OPT_TYPE . ':', 'option_type', 'class="control-label"') . zen_draw_pull_down_menu('option_type', $optionTypeValuesArray, 0, 'class="form-control" id="option_type"'); ?></td>
                                        <td class="dataTableHeadingContent text-center" style="vertical-align: bottom">
                                            <button type="submit" class="btn btn-primary"><?php echo IMAGE_INSERT; ?></button>
                                        </td>
                                    </tr>
                                </table>
                                <?php echo '</form>'; ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="7"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></td>
                </tr>
                </tbody>
            </table>
        <?php }
        //eof Options Names
        ////////////////////////////////////////////////////////////////////////////

        // bof Global Tools
        if (!empty($_GET['products_order_by']) && $_GET['products_order_by'] === 'model') {
            $products_order_by = 'model';
        } else {
            $products_order_by = 'name';
        }
        if (!empty($_GET['category_path']) && $_GET['category_path'] === '1') {
            $category_path = true;
        } else {
            $category_path = false;
        }
        $selectedOptionId = !empty($_SESSION['selectedOptionId']) ? (int)$_SESSION['selectedOptionId'] : -1; // set after first select of Option Name dropdopwn

        $options_values = $db->Execute("SELECT products_options_id, products_options_name
                                        FROM " . TABLE_PRODUCTS_OPTIONS . "
                                        WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                                        AND products_options_name != ''
                                        AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_TEXT . "
                                        AND products_options_type != " . (int)PRODUCTS_OPTIONS_TYPE_FILE . "
                                        ORDER BY products_options_name");
        $optionsValuesArray = []; // array for dropdown list of Option Names
        $optionsValuesArray[] = [
            'id' => '-1',
            'text' => TEXT_SELECT_OPTION
        ];
        foreach ($options_values as $options_value) {
            $optionsValuesArray[] = [
                'id' => $options_value['products_options_id'],
                'text' => $options_value['products_options_name']
            ];
        }
        ?>
        <div>
            <div class="pageHeading"><?php echo TEXT_GLOBAL_TOOLS; ?></div>
            <div>
                <h4><span class="alert"><?php echo TEXT_WARNING_BACKUP; ?></span></h4>
                <h5><?php echo TEXT_SELECT_OPTION_TYPES_ALLOWED; ?></h5>

                <!-- bof Global Tools ADD Option Values to products -->
                <div style="border: 1px solid #999;padding:5px">
                    <div><p><?php echo TEXT_INFO_OPTION_VALUES_ADD; ?></p></div>

                    <!-- bof: add ALL additional Option Values of this Option Name, to ALL products that already have at least one Option Value of this Option Name -->
                    <div>
                        <div>
                            <h4><?php echo TEXT_OPTION_VALUE_ADD_ALL; ?></h4>
                            <h5><?php echo TEXT_INFO_OPTION_VALUE_ADD_ALL; ?></h5>
                        </div>
                        <?php echo zen_draw_form('add_values_all_form', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=0&update_action=0' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
                        <div class="row">
                            <div class="col-sm-3">
                                <?php echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"'); ?>
                                <?php echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control" id="options_id"'); ?></div>
                            <div class="col-sm-7">&nbsp;</div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-warning"><?php echo IMAGE_UPDATE; ?></button>
                            </div>
                        </div>
                        <?php echo '</form>'; ?>
                    </div>
                    <!-- eof: add ALL additional Option Values of this Option Name, to ALL products that already have at least one Option Value of this Option Name -->

                    <div class="row text-center"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>

                    <!-- bof: add ALL additional Option Values of this Option Name, to ONE product that already has at least one Option Value of this Option Name -->
                    <div id="addOptionValuesOneWrapper">
                        <div>
                            <h4><?php echo TEXT_OPTION_VALUE_ADD_PRODUCT; ?></h4>
                            <h5><?php echo TEXT_INFO_OPTION_VALUE_ADD_PRODUCT; ?></h5>
                        </div>
                        <?php echo zen_draw_form('add_values_one_form', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=1&update_action=0' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"');
                        echo zen_draw_hidden_field('form_wrapper_id', 'addOptionValuesOneWrapper'); ?>
                        <div>
                            <div class="col-sm-3">
                                <?php echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"'); ?>
                                <?php echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, $selectedOptionId, 'class="form-control" id="addOptionValuesOne" onchange="this.form.submit();"'); ?></div>
                            <div class="col-sm-7">
                                <?php
                                echo zen_draw_label(TEXT_SELECT_PRODUCT, 'product_to_update_add', 'class="control-label"') . '<br>';
                                echo zen_draw_products_pull_down_attributes('product_to_update', 'size="5" class="form-control" id="product_to_update_add"', '', $products_order_by, $selectedOptionId);
                                if ($selectedOptionId !== -1) {
                                    $products_sort_link = ($products_order_by === 'name' ?
                                        '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params() . '&products_order_by=model#addOptionValuesOneWrapper') . '">' . TEXT_ORDER_BY . ' ' . TABLE_HEADING_MODEL . '</a>' :
                                        '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params() . '&products_order_by=name#addOptionValuesOneWrapper') . '">' . TEXT_ORDER_BY . ' ' . TEXT_NAME . '</a>');
                                    echo '<br>' . $products_sort_link;
                                } ?>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-warning"><?php echo IMAGE_UPDATE; ?></button>
                            </div>
                        </div>
                        <?php echo '</form>'; ?>
                    </div>
                    <!-- eof: add ALL additional Option Values of this Option Name, to ONE product that already has at least one Option Value of this Option Name -->

                    <div class="row text-center"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>

                    <!-- bof: add ALL additional Option Values of this Option Name, to ALL products in ONE category that already have at least one Option Value of this Option Name -->
                    <div id="addOptionValuesCategoryWrapper">
                        <div>
                            <h4><?php echo TEXT_OPTION_VALUE_ADD_CATEGORY; ?></h4>
                            <h5><?php echo TEXT_INFO_OPTION_VALUE_ADD_CATEGORY; ?></h5>
                        </div>
                        <?php echo zen_draw_form('add_values_all_category_form', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=2&update_action=0' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"');
                        echo zen_draw_hidden_field('form_wrapper_id', 'addOptionValuesCategoryWrapper'); ?>
                        <div class="row">
                            <div class="col-sm-3">
                                <?php
                                echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"');
                                echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, $selectedOptionId, 'class="form-control optionNameFilter" id="addOptionValuesCategory" onchange="this.form.submit();"'); ?></div>
                            <div class="col-sm-7">
                                <?php
                                echo zen_draw_label(TEXT_SELECT_CATEGORY, 'category_to_update_add', 'class="control-label"') . '<br>';
                                echo zen_draw_products_pull_down_categories_attributes('category_to_update', 'size="5" class="form-control" id="category_to_update_add"', '', $category_path, $selectedOptionId);
                                if ($selectedOptionId !== -1) {
                                    $show_category_path_link = ($category_path ?
                                        '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['category_path']) . '&category_path=0#addOptionValuesCategoryWrapper') . '">' . TEXT_SHOW_CATEGORY_NAME . '</a>' :
                                        '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['category_path']) . '&category_path=1#addOptionValuesCategoryWrapper') . '">' . TEXT_SHOW_CATEGORY_PATH . '</a>');
                                    echo '<br>' . $show_category_path_link;
                                } ?>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-warning"><?php echo IMAGE_UPDATE; ?></button>
                            </div>
                        </div>
                        <?php echo '</form>'; ?>
                    </div>
                    <!-- eof: add ALL additional Option Values of this Option Name, to ALL products in ONE category that already have at least one Option Value of this Option Name -->
                </div>
                <!-- eof Global Tools ADD Option Values to products-->

                <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>

                <!-- bof Global Tools DELETE Option Values from products -->
                <div style="border: 1px solid #999;padding:5px">
                    <div><p><?php echo TEXT_COMMENT_OPTION_VALUE_DELETE_ALL; ?></p></div>

                    <!-- bof: delete ALL Option Values of this Option Name, from ALL products that have at least one Option Value of this Option Name -->
                    <div>
                        <div>
                            <h4><?php echo TEXT_OPTION_VALUE_DELETE_ALL; ?></h4>
                            <h5><?php echo TEXT_INFO_OPTION_VALUE_DELETE_ALL; ?></h5>
                        </div>
                        <?php echo zen_draw_form('delete_values_all_form', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=0&update_action=1' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"'); ?>
                        <div class="row">
                            <div class="col-sm-3">
                                <?php
                                echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"');
                                echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, '', 'class="form-control"'); ?>
                            </div>
                            <div class="col-sm-7">&nbsp;</div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo IMAGE_DELETE; ?></button>
                            </div>
                        </div>
                        <?php echo '</form>'; ?>
                    </div>
                    <!-- eof: delete ALL Option Values of this Option Name from ALL products -->

                    <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>

                    <!-- bof: delete ALL Option Values of this Option Name from ONE product that has at least one Option Value of this Option Name -->
                    <div id="deleteOptionValuesOneWrapper">
                        <div>
                            <h4><?php echo TEXT_OPTION_VALUE_DELETE_PRODUCT; ?></h4>
                            <h5><?php echo TEXT_INFO_OPTION_VALUE_DELETE_PRODUCT; ?></h5>
                        </div>
                        <?php echo zen_draw_form('delete_values_one_form', FILENAME_OPTIONS_NAME_MANAGER, 'action=update_options_values&update_to=1&update_action=1' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"');
                        echo zen_draw_hidden_field('form_wrapper_id', 'deleteOptionValuesOneWrapper'); ?>
                        <div class="row">
                            <div class="col-sm-3">
                                <?php
                                echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"');
                                echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, $selectedOptionId, 'class="form-control optionNameFilter" id="deleteOptionValuesOne" onchange="this.form.submit(this.id);"'); ?>
                            </div>
                            <div class="col-sm-7">
                                <?php
                                echo zen_draw_label(TEXT_SELECT_PRODUCT, 'product_to_update_delete', 'class="control-label"') . '<br>';
                                echo zen_draw_products_pull_down_attributes('product_to_update', 'size="5" class="form-control" id="product_to_update_delete"', '', $products_order_by, $selectedOptionId);
                                if ($selectedOptionId !== -1) {
                                    $products_sort_link = ($products_order_by === 'name' ?
                                        '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params() . '&products_order_by=model#deleteOptionValuesOneWrapper') . '">' . TEXT_ORDER_BY . ' ' . TABLE_HEADING_MODEL . '</a>' :
                                        '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params() . '&products_order_by=name#deleteOptionValuesOneWrapper') . '">' . TEXT_ORDER_BY . ' ' . TEXT_NAME . '</a>');
                                    echo '<br>' . $products_sort_link;
                                } ?>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo IMAGE_DELETE; ?></button>
                            </div>
                        </div>
                        <?php echo '</form>'; ?>
                    </div>
                    <!-- eof: delete ALL Option Values of this Option Name from ONE product -->

                    <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>

                    <!-- bof: delete ALL Option Values of this Option Name, from ALL products in ONE category that has at least one Option Value of this Option Name -->
                    <div id="deleteOptionValuesCategoryWrapper">
                        <div>
                            <h4><?php echo TEXT_OPTION_VALUE_DELETE_CATEGORY; ?></h4>
                            <h5><?php echo TEXT_INFO_OPTION_VALUE_DELETE_CATEGORY; ?></h5>
                        </div>
                        <?php echo zen_draw_form('delete_values_category_form', FILENAME_OPTIONS_NAME_MANAGER,
                            'action=update_options_values&update_to=2&update_action=1' . '&option_order_by=' . $option_order_by, 'post', 'class="form-horizontal"');
                        echo zen_draw_hidden_field('form_wrapper_id', 'deleteOptionValuesCategoryWrapper'); ?>
                        <div class="row">
                            <div class="col-sm-3">
                                <?php
                                echo zen_draw_label(TEXT_SELECT_OPTION, 'options_id', 'class="control-label"');
                                echo zen_draw_pull_down_menu('options_id', $optionsValuesArray, $selectedOptionId, 'class="form-control optionNameFilter" id="deleteOptionValuesCategory" onchange="this.form.submit();"'); ?>
                            </div>
                            <div class="col-sm-7">
                                <?php
                                echo zen_draw_label(TEXT_SELECT_CATEGORY, 'category_to_update_delete', 'class="control-label"') . '<br>';
                                echo zen_draw_products_pull_down_categories_attributes('category_to_update', 'size="5" class="form-control" id="category_to_update_delete"', '', $category_path, $selectedOptionId);
                                if ($selectedOptionId !== -1) {
                                    $show_category_path_link = ($category_path ?
                                        '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['category_path']) . '&category_path=0#deleteOptionValuesCategoryWrapper') . '">' . TEXT_SHOW_CATEGORY_NAME . '</a>' :
                                        '<a href="' . zen_href_link(FILENAME_OPTIONS_NAME_MANAGER, zen_get_all_get_params(['category_path']) . '&category_path=1#deleteOptionValuesCategoryWrapper') . '">' . TEXT_SHOW_CATEGORY_PATH . '</a>');
                                    echo '<br>' . $show_category_path_link;
                                } ?>
                            </div>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> <?php echo IMAGE_DELETE; ?></button>
                            </div>
                        </div>
                        <?php echo '</form>'; ?>
                    </div>
                    <!-- eof: delete ALL Option Values of this Option Name from ALL products in ONE category -->

                </div>
                <!-- eof Global Tools DELETE Option Values from products -->

                <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>

                <!-- bof Global Tools COPY Option Values to another Option Name -->
                <div style="border: 1px solid #999;padding:5px">
                    <div>
                        <h4><?php echo TEXT_OPTION_VALUE_COPY_ALL; ?></h4>
                        <h5><?php echo TEXT_INFO_OPTION_VALUE_COPY_ALL; ?></h5>
                    </div>
                    <?php echo zen_draw_form('copy_values_form', FILENAME_OPTIONS_NAME_MANAGER, 'action=copy_options_values' . '&option_order_by=' . $option_order_by, 'post',
                        'class="form-horizontal"'); ?>
                    <div class="row">
                        <div class="col-sm-3">
                            <?php echo zen_draw_label(TEXT_SELECT_OPTION_FROM, 'options_id_from_copy', 'class="control-label"'); ?>
                            <?php echo zen_draw_pull_down_menu('options_id_from', $optionsValuesArray, '', 'class="form-control" id="options_id_from_copy"'); ?>
                        </div>
                        <div class="col-sm-7">
                            <?php echo zen_draw_label(TEXT_SELECT_OPTION_TO, 'options_id_to_copy', 'class="control-label"'); ?>
                            <?php echo zen_draw_pull_down_menu('options_id_to', $optionsValuesArray, '', 'class="form-control" id="options_id_to_copy"'); ?>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-copy"></i> <?php echo IMAGE_COPY; ?></button>
                        </div>
                    </div>
                    <?php echo '</form>'; ?>
                </div>
                <!-- eof Global Tools COPY Option Values to another Option Name -->
            </div>
        </div>

    </div>
    <!-- body_text_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <script>
        $(function(){
            $("div#insertOption").toggle(); // hide "Add new Option name" div on initial page load
            $(".toggleDisplay").click(function(){ //toggle "Add new Option name" div on button click
                $("div#insertOption").toggle('fast');
            });
        });
    </script>
    </body>
    </html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>