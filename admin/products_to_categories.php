<?php

/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */

require('includes/application_top.php');

// products_filter: the selected product
$products_filter = (int)($_POST['products_filter'] ?? $_GET['products_filter'] ?? 0);
$_GET['products_filter'] = $products_filter;

// current_category: the category containing the selected product
$current_category_id = (int)($_POST['current_category_id'] ?? $_GET['current_category_id'] ?? 0);
$_GET['current_category_id'] = $current_category_id; // for any redirects

// enable_copy_links_dropdown: checkbox to allow the copy categories to another product dropdown. This is a dropdown of all products so is disabled by default.
if (isset($_POST['enable_copy_links_dropdown'])) {// only set if checked
    $enable_copy_links_dropdown = ($_POST['enable_copy_links_dropdown'] === 'true');
} elseif (isset($_SESSION['enable_copy_links_dropdown'])) {
    $enable_copy_links_dropdown = $_SESSION['enable_copy_links_dropdown'];
} else {
    $enable_copy_links_dropdown = false;
}
$_SESSION['enable_copy_links_dropdown'] = $enable_copy_links_dropdown;

// Verify that at least one product exists
$result = $db->Execute("SELECT *
                              FROM " . TABLE_PRODUCTS . "
                              LIMIT 1");
if ($result->RecordCount() < 1) {
    $messageStack->add_session(ERROR_DEFINE_PRODUCTS, 'caution');
    zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING));
}

// Verify that product has a master_categories_id
if ($products_filter > 0) {
    $source_product_details = zen_get_products_model($products_filter)
        . ' - "'
        . zen_get_products_name($products_filter, (int)$_SESSION['languages_id'])
        . '" (#' . $products_filter . ')'; // format used for various messageStack

    if (zen_get_products_category_id($products_filter) < 1) {
        $messageStack->add(ERROR_DEFINE_PRODUCTS_MASTER_CATEGORIES_ID, 'error');
    }
}

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$languages = zen_get_languages();

$action = ($_GET['action'] ?? '');

if ($action === 'new_cat') {//this form action is from products_previous_next_display.php when a new category is selected
    $products_filter = zen_get_linked_products_for_category($current_category_id, true);
    zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
}

// set categories and products if not set
if ($products_filter === '' && !empty($current_category_id)) { // when prev-next has been changed to a category without products/with subcategories
    $new_product_query = $db->Execute("SELECT ptc.products_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc WHERE ptc.categories_id = " . $current_category_id . " LIMIT 1");
    $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : ''; // Empty if category has no products/has subcategories
    if ($products_filter !== '') {
        $messageStack->add_session(WARNING_PRODUCTS_LINK_TO_CATEGORY_REMOVED, 'caution');
        zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
    }

} elseif ($products_filter === '' && empty($current_category_id)) {// on first entry into page from Admin menu
    $reset_categories_id = zen_get_category_tree('', '', TOPMOST_CATEGORY_PARENT_ID, '', '', true);
    $current_category_id = (int)$reset_categories_id[0]['id'];
    $new_product_query = $db->Execute("SELECT ptc.products_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc WHERE ptc.categories_id = " . $current_category_id . " LIMIT 1");
    $products_filter = (!$new_product_query->EOF) ? $new_product_query->fields['products_id'] : '';// Empty if category has no products/has subcategories
    $_GET['products_filter'] = $products_filter;
}

require(DIR_WS_MODULES . FILENAME_PREV_NEXT);

// target_category_id: the root/base target category whose subcategories will be displayed for linking into
$target_category_id = (int)($_POST['target_category_id'] ?? $_GET['target_category_id'] ?? P2C_TARGET_CATEGORY_DEFAULT);
$_GET['target_category_id'] = $target_category_id;

if (!empty($action)) {
    switch ($action) {

        // Global Tools: Copy Linked categories from this product to another
        case 'copy_linked_categories_to_another_product':
            $copy_categories_type = !empty($_POST['type']) && $_POST['type'] !== 'replace' ? 'add' : 'replace';
            $target_product_id = (int)$_POST['target_product_id'];

            if ($target_product_id === '') {
                $messageStack->add(WARNING_COPY_LINKED_CATEGORIES_NO_TARGET, 'error');
            } else {
                $target_product_details = zen_get_products_model($target_product_id) . ' - "' . zen_get_products_name($target_product_id, (int)$_SESSION['languages_id']) . '" (#' . $target_product_id . ')'; // Used in messageStack

                $source_product_master_categories_id = (int)zen_get_products_category_id($products_filter);
                $target_product_master_categories_id = (int)zen_get_products_category_id($target_product_id);

                if ($source_product_master_categories_id === 0 || $target_product_master_categories_id === 0) { // source/target is missing a master category
                    if ($source_product_master_categories_id === 0) {
                        $messageStack->add(sprintf(ERROR_MASTER_CATEGORY_MISSING, $source_product_details));
                    }
                    if ($target_product_master_categories_id === 0) {
                        $messageStack->add(sprintf(ERROR_MASTER_CATEGORY_MISSING, $target_product_details));
                    }
                    break;
                }

                $product_categories = [];
                foreach (zen_get_linked_categories_for_product($products_filter, [$source_product_master_categories_id, $target_product_master_categories_id]) as $row) {
                    $product_categories[] = (int)$row;
                }

                $target_categories = [];
                foreach (zen_get_linked_categories_for_product($target_product_id, [$source_product_master_categories_id, $target_product_master_categories_id]) as $row) {
                    $target_categories[] = (int)$row;
                }

                $target_categories_update = [];
                switch ($copy_categories_type) {
                    case 'add':
                        foreach ($product_categories as $id) {
                            if (!in_array($id, $target_categories, true)) { // Include only NEW linked categories from source product
                                $target_categories_update[] = $id;
                            }
                        }
                        break;

                    case 'replace':
                        zen_unlink_product_from_all_linked_categories($target_product_id, $target_product_master_categories_id);
                        $target_categories_update = $product_categories;
                        break;
                }

                if (count($target_categories_update) < 1) {// No new categories to add
                    $messageStack->add(sprintf(WARNING_COPY_LINKED_CATEGORIES_NO_ADDITIONAL, $source_product_details, $target_product_details), 'warning');
                    break;
                }

                foreach ($target_categories_update as $target_category) {
                    zen_link_product_to_category($target_product_id, $target_category);
                }

                $messageStack->add_session(sprintf(($copy_categories_type === 'add' ? SUCCESS_LINKED_CATEGORIES_COPIED_TO_TARGET_PRODUCT_ADD : SUCCESS_LINKED_CATEGORIES_COPIED_TO_TARGET_PRODUCT_REPLACE),
                    count($target_categories_update), $source_product_details, $target_product_details), 'success');

                $exclude_array = ['action', 'products_filter', 'current_category_id'];
                zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, zen_get_all_get_params($exclude_array) . '&products_filter=' . $target_product_id . '&current_category_id=' . $target_product_master_categories_id));
            }
            break;

        // Global Tools: Copy products in Source category as linked products in Target category
        case 'copy_products_as_linked':
            $category_id_source = (int)$_POST['category_id_source'];
            $category_id_target = (int)$_POST['category_id_target'];

            if (!zen_validate_categories($category_id_source, $category_id_target)) {
                zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
            }

            // if either category was invalid nothing processes below

            // get products from source category
            $products_to_categories_links_source = $db->Execute("SELECT products_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE categories_id = " . $category_id_source);
            $add_links_array = [];
            foreach ($products_to_categories_links_source as $item) {
                $add_links_array[] = ['products_id' => $item['products_id']];
            }

            // get products from target category
            $products_to_categories_links_target = $db->Execute("SELECT products_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE categories_id = " . $category_id_target);
            $current_target_links_array = [];
            foreach ($products_to_categories_links_target as $item) {
                $current_target_links_array[] = ['products_id' => $item['products_id']];
            }

            // check for elements in $current_target_links_array that are already in $add_links_array
            $make_links_result = [];
            for ($i = 0, $n = count($add_links_array); $i < $n; $i++) {
                $good = 'true';
                for ($j = 0, $nn = count($current_target_links_array); $j < $nn; $j++) {
                    if ((int)$add_links_array[$i]['products_id'] === (int)$current_target_links_array[$j]['products_id']) {
                        $good = 'false';
                        break;
                    }
                }
                // build array of new (unlinked) products to copy
                if ($good === 'true') {
                    $make_links_result[] = ['products_id' => $add_links_array[$i]['products_id']];
                }
            }
            if (count($make_links_result) === 0) {//nothing new to copy
                $messageStack->add_session(sprintf(WARNING_COPY_FROM_IN_TO_LINKED, $category_id_source, $category_id_target), 'caution');
            } else {//do the copy
                $products_copied_message = '';
                for ($i = 0, $n = count($make_links_result); $i < $n; $i++) {
                    $new_product = $make_links_result[$i]['products_id'];
                    zen_link_product_to_category($new_product, $category_id_target);
                    $product_copied_format = zen_get_products_model($make_links_result[$i]['products_id']) . ' - "' . zen_get_products_name($make_links_result[$i]['products_id'], (int)$_SESSION['languages_id']) . '" (#' . $make_links_result[$i]['products_id'] . ')';
                    $products_copied_message .= sprintf(SUCCESS_PRODUCT_COPIED, $product_copied_format, $category_id_target);
                }
                $products_copied_message .= sprintf(SUCCESS_COPY_LINKED, $i, $category_id_source, $category_id_target);
                $messageStack->add_session($products_copied_message, 'success');
            }
            zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
            break;

        // Global Tools: Remove products from Target category that are linked from a Reference category
        case 'remove_linked_products':

            $category_id_reference = (int)$_POST['category_id_reference'];
            $category_id_target = (int)$_POST['category_id_target_remove'];

            if (!zen_validate_categories($category_id_reference, $category_id_target)) {
                zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
            }
            // if either category was invalid nothing processes below

            // get products to be removed as added linked from
            $products_to_categories_reference_linked = $db->Execute("SELECT ptoc.products_id, p.master_categories_id
                                                          FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptoc
                                                          LEFT JOIN " . TABLE_PRODUCTS . " p ON ptoc.products_id = p.products_id
                                                          WHERE ptoc.categories_id = " . $category_id_reference);
            $reference_links_array = [];
            $master_categories_id_stop = [];
            foreach ($products_to_categories_reference_linked as $item) {
                if ((int)$item['master_categories_id'] === $category_id_target) { // if a product to be removed has the same master category id as the target category: do NOT remove
                    $master_categories_id_stop[] = [
                        'products_id' => $item['products_id'],
                        'master_categories_id' => $item['master_categories_id']
                    ];
                }
                $reference_links_array[] = [
                    'products_id' => $item['products_id'],
                    'master_categories_id' => $item['master_categories_id']
                ];
            }

            $stop_warning_ = '';
            if (count($master_categories_id_stop) > 0) {//a product set to be unlinked is in its master category. Create message and abort unlinking.
                for ($i = 0, $n = count($master_categories_id_stop); $i < $n; $i++) {
                    $stop_warning .= sprintf(WARNING_PRODUCT_MASTER_CATEGORY_IN_TARGET, $master_categories_id_stop[$i]['products_id'], zen_get_products_name($master_categories_id_stop[$i]['products_id'], (int)$_SESSION['languages_id']), zen_get_products_model($master_categories_id_stop[$i]['products_id']), $category_id_target);
                }
                $stop_warning .= sprintf(WARNING_REMOVE_LINKED_PRODUCTS_MASTER_CATEGORIES_ID_CONFLICT, $category_id_reference, $category_id_target);
                $messageStack->add_session($stop_warning, 'warning');
                zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $master_categories_id_stop[0]['products_id'] . '&current_category_id=' . $current_category_id));
            }

            // get products in target category
            $products_to_categories_target_linked = $db->Execute("SELECT products_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE categories_id = " . $category_id_target);
            $target_links_array = [];
            foreach ($products_to_categories_target_linked as $item) {
                $target_links_array[] = ['products_id' => $item['products_id']];
            }

            // remove elements from $target_links_array that are in $reference_links_array
            $products_to_remove = [];
            for ($i = 0, $n = count($reference_links_array); $i < $n; $i++) {
                $good = 'false';
                for ($j = 0, $nn = count($target_links_array); $j < $nn; $j++) {
                    if ((int)$reference_links_array[$i]['products_id'] === (int)$target_links_array[$j]['products_id']) {
                        $good = 'true';
                        break;
                    }
                }
                // build array of products to remove
                if ($good === 'true') {
                    $products_to_remove[] = ['products_id' => $reference_links_array[$i]['products_id']];
                }
            }
            // check that there are some products to remove
            if (count($products_to_remove) === 0) {
                $messageStack->add_session(sprintf(WARNING_REMOVE_FROM_IN_TO_LINKED, $category_id_target, $category_id_reference), 'warning');
            } else {
                $products_removed_message = '';
                for ($i = 0, $n = count($products_to_remove); $i < $n; $i++) {
                    zen_unlink_product_from_category($products_to_remove[$i]['products_id'], $category_id_target);
                    $products_removed_format = zen_get_products_model($products_to_remove[$i]['products_id']) . ' - "' . zen_get_products_name($products_to_remove[$i]['products_id'], (int)$_SESSION['languages_id']) . '" (#' . $products_to_remove[$i]['products_id'] . ')';
                    $products_removed_message .= sprintf(SUCCESS_REMOVED_PRODUCT, $products_removed_format, $category_id_target);
                }
                $products_removed_message .= sprintf(SUCCESS_REMOVE_LINKED_PRODUCTS, $i);
                $messageStack->add_session($products_removed_message, 'success');
            }

            zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
            break;

        // Global Tools: Reset the master_categories_id for all products in the selected category
        case 'reset_products_category_as_master':

            $category_id_as_master = (int)$_POST['category_id_as_master'];

            if (!zen_validate_categories($category_id_as_master, TOPMOST_CATEGORY_PARENT_ID, true)) {
                zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
            }
            // if either category was invalid nothing processes below
            zen_reset_products_category_as_master($category_id_as_master);

            $messageStack->add_session(sprintf(SUCCESS_RESET_PRODUCTS_MASTER_CATEGORY, $category_id_as_master), 'success');
            zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
            break;

        // Change the master category id for the currently selected product
        case 'set_master_categories_id':
            zen_set_product_master_categories_id($products_filter, (int)$_GET['master_category']);

            zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id));
            break;

        // Choose a product to display
        case 'set_products_filter':
            zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter .
                '&current_category_id=' . $current_category_id .
                '&target_category_id=' . $target_category_id));
            break;

        // Product to multiple category links: Set the root category from which to display the subcategories for selection
        case 'set_target_category':
            $target_category_id = (int)$_POST['target_category_id'];
            zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&target_category_id=' . $target_category_id));
            break;

        // Product to multiple category links: Set the root category from which to display the subcategories for selection
        case 'set_default_target_category':
            $default_target_category_id = (int)$_POST['default_target_category_id'];
            $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_value = " . $default_target_category_id . "
                    WHERE configuration_key = 'P2C_TARGET_CATEGORY_DEFAULT'");
            zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&target_category_id=' . $default_target_category_id));
            break;

        // Product to multiple category links: Update the product to multiple-categories links
        case 'update_product':
            if (!isset($_POST['categories_add'])) {//no linked categories are selected
                $_POST['categories_add'] = [];
            }
            if (!empty($_POST['current_master_categories_id'])) {
                $current_master_categories_id = (int)$_POST['current_master_categories_id'];
            } else {
                $current_master_categories_id = (int)zen_get_products_category_id($products_filter);
            }
            $new_categories_sort_array = [];

            // Add the selected linked subcategories to the master category
            for ($i = 0, $n = count($_POST['categories_add']); $i < $n; $i++) {
                $new_categories_sort_array[] = (int)$_POST['categories_add'][$i];
            }

            // Build the list of categories within the target category
            $categories_info = [];
            zen_get_categories_info($target_category_id);//$target_category_id is the chosen root category that contains the subcategories to link to. This function populates array $categories_info
            $num_target_categories = count($categories_info);

            // Make the list of all the possible target subcategories' IDs. At the same time, check if product master category and currently-selected category are in the list of target subcategories
            $target_categories_ids = [];
            $master_category_in_target_categories_list = false;
            $current_category_in_target_categories_list = false;
            $current_category_name = '';
            $master_category_name = $current_category_name;

            for ($tc_i = 0; $tc_i < $num_target_categories; $tc_i++) {
                if ((int)$categories_info[$tc_i]['categories_id'] === $current_master_categories_id) {
                    $master_category_name = $categories_info[$tc_i]['categories_name'];//if the master category id is in the target list, skip it
                } else {
                    $target_categories_ids[] = $categories_info[$tc_i]['categories_id'];//load the categories to unlink
                }

                if ((int)$categories_info[$tc_i]['categories_id'] === $current_category_id) {
                    $current_category_name = $categories_info[$tc_i]['categories_name'];
                }
            }

            // 1- Unlink the product from all of the target subcategories. Subsequently below, it will then be (re-)linked into the selected target categories
            $target_categories_ids_string = implode(',', $target_categories_ids);
// TODO better to compare and unlink only those necessary??
            $db->Execute("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = " . $products_filter . " AND categories_id IN (" . $target_categories_ids_string . ")");

            $verify_current_category_id = ($current_category_id === $current_master_categories_id); // display product in same category after linking?

            for ($i = 0, $n = count($new_categories_sort_array); $i < $n; $i++) {//contains the selected linked categories
                // is current master_categories_id in the list?
                if ($new_categories_sort_array[$i] <= 0) {
                    $messageStack->add_session(sprintf(ERROR_CATEGORY_ID_INVALID, $new_categories_sort_array[$i]));
                } else {
                    if ($current_category_id === (int)$new_categories_sort_array[$i]) { // is the product still linked to the displayed category?
                        $verify_current_category_id = true;
                    }

                    $db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) VALUES (" . $products_filter . ", " . (int)$new_categories_sort_array[$i] . ")");
                }
            }
            // recalculate price based on new master_categories_id
            zen_update_products_price_sorter($products_filter);
            $messageStack->add_session(sprintf(SUCCESS_PRODUCT_LINKED_TO_CATEGORIES, $source_product_details), 'success');

            if ($verify_current_category_id) {// if product continues to be linked into the current categories_id, return to that category
                zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES, 'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id .
                    '&target_category_id=' . $target_category_id));
            } else {// if product was unlinked from the current categories_id, show product in it's master category
                $messageStack->add_session(sprintf(WARNING_PRODUCT_UNLINKED_FROM_CATEGORY, $current_category_name, $current_category_id), 'warning');
                zen_redirect(zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES,
                    'products_filter=' . $products_filter . '&current_category_id=' . $current_master_categories_id . '&target_category_id=' . $target_category_id));
            }
            break;
    }
}

if ($products_filter > 0) {
    $product_to_copy = $db->Execute("SELECT p.products_id, pd.products_name, p.products_sort_order, p.products_price_sorter, p.products_model, p.master_categories_id, p.products_image
                                 FROM " . TABLE_PRODUCTS . " p
                                 LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id AND pd.language_id = " . (int)$_SESSION['languages_id'] . ")
                                 WHERE p.products_id = " . $products_filter, 1);

    $product_linked_categories = $db->Execute("SELECT products_id, categories_id FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = " . $products_filter);
}

// Build the list of categories within the target category
$categories_info = [];
zen_get_categories_info($target_category_id); // loads $categories_info with subcategories of chosen target category
$target_subcategory_count = count($categories_info);
$max_input_vars = @ini_get("max_input_vars");
if ($target_subcategory_count > $max_input_vars) { //warning when in excess of POST limit
    $messageStack->add(sprintf(WARNING_MAX_INPUT_VARS_LIMIT, $target_subcategory_count, $max_input_vars, 'caution'));
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div class="container-fluid">
    <!-- body_text //-->
    <h1><?php echo HEADING_TITLE; ?></h1>
    <?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?>
    <!-- Product-category links block -->
    <!-- Product selection-infoBox block -->
    <div class="row">
        <!-- LEFT column block (prev/next, product select, master category) -->
        <div class="col-sm-9 col-md-9 col-lg-9">
            <h2><?php echo TEXT_HEADING_PRODUCT_SELECT; ?></h2>

            <!-- prev-cat-next navigation -->
            <div>
                <?php require(DIR_WS_MODULES . FILENAME_PREV_NEXT_DISPLAY); ?>
            </div>
            <!-- prev-cat-next navigation eof-->

            <!-- product selection -->
            <?php if ($products_filter > 0) {//a product is selected ?>
                <div>
                    <?php
                    echo zen_draw_form('set_products_filter_id', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=set_products_filter', 'post', 'class="form-horizontal"');
                    echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']);
                    echo zen_draw_hidden_field('target_category_id', $_GET['target_category_id']);

                    $excluded_products = [];
                    //              $not_for_cart = $db->Execute("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCT_TYPES . " pt on p.products_type= pt.type_id where pt.allow_add_to_cart = 'N'");
                    //              while (!$not_for_cart->EOF) {
                    //                $excluded_products[] = $not_for_cart->fields['products_id'];
                    //                $not_for_cart->MoveNext();
                    //              }
                    ?>
                    <?php echo zen_draw_label(TEXT_PRODUCT_TO_VIEW, 'products_filter'); ?>
                    <?php echo zen_draw_pulldown_products('products_filter', 'size="10" class="form-control" id="products_filter" onchange="this.form.submit()"', $excluded_products, true, $products_filter, true, true); ?>
                    <noscript><br><input type="submit" value="<?php echo IMAGE_DISPLAY; ?>"></noscript>
                    <?php echo '</form>'; ?>
                </div>
            <?php } ?>
            <!-- product selection eof -->

            <!-- master category change -->
            <?php if ($products_filter > 0) {//a product is selected ?>
                <div class="row">
                    <hr>
                    <h3><?php echo TEXT_MASTER_CATEGORIES_ID; ?></h3>
                    <div class="col-lg-6"><?php echo TEXT_INFO_MASTER_CATEGORY_CHANGE; ?></div>

                    <div class="col-lg-6">
                        <?php if ($product_to_copy->EOF) { //product not linked to ANY category: missing a master category ID/ID invalid ?>
                            <span class="alert"
                                  style="font-size: larger;padding:0;"><?php echo sprintf(TEXT_PRODUCTS_ID_INVALID, $products_filter); ?></span>

                        <?php } else { //show drop-down for master category re-assignment ?>
                            <div class="form-group">
                                <?php
                                echo zen_draw_form('restrict_product', FILENAME_PRODUCTS_TO_CATEGORIES, '', 'get', 'class="form-horizontal"', true);
                                echo zen_draw_hidden_field('action', 'set_master_categories_id');
                                echo zen_draw_hidden_field('products_filter', $products_filter);
                                echo zen_draw_hidden_field('current_category_id', $_GET['current_category_id']);
                                echo zen_hide_session_id();
                                zen_draw_label(
                                    zen_icon($product_to_copy->fields['master_categories_id'] > 0 ? 'enabled' : 'disabled', IMAGE_ICON_LINKED, 'lg') .
                                    '&nbsp;' . TEXT_MASTER_CATEGORIES_ID, 'master_category');
                                echo zen_draw_pull_down_menu('master_category', zen_get_master_categories_pulldown($products_filter, true), $product_to_copy->fields['master_categories_id'],
                                    'class="form-control" id="master_category"'); ?>
                                <button type="submit" class="btn btn-info"><?php echo IMAGE_UPDATE; ?></button>
                                <?php
                                if ($product_to_copy->fields['master_categories_id'] < 1) { ?>
                                    <span class="alert"
                                          style="font-size: larger;padding:0;"><?php echo ERROR_DEFINE_PRODUCTS_MASTER_CATEGORIES_ID; ?></span>
                                <?php } ?>
                                <?php echo '</form>'; ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <!-- master category change eof-->
        </div>
        <!-- LEFT column block (prev/next, product select, master category) eof -->

        <!-- RIGHT column block (infoBox) -->
        <div class="col-sm-3 col-md-3 col-lg-3">
            <!-- infoBox -->
            <?php if ($products_filter > 0) {//a product is selected ?>
                <div id="infoBox" style="display:table;margin:0 auto;">
                    <?php
                    $heading = [];
                    $contents = [];

                    switch ($action) {
                        case 'edit'://select a different product by ID
                            $heading[] = ['text' => '<h4>' . TEXT_INFOBOX_HEADING_SELECT_PRODUCT . '</h4>'];
                            $contents = ['form' => zen_draw_form('product_select_by_id', FILENAME_PRODUCTS_TO_CATEGORIES, '', 'post', 'class="form-horizontal"')];
                            $contents[] = ['text' => TEXT_SET_PRODUCTS_TO_CATEGORIES_LINKS];
                            $contents[] = [
                                'text' => zen_draw_label(TEXT_PRODUCTS_ID, 'products_filter', 'class="control-label"') . zen_draw_input_field('products_filter', $products_filter,
                                        'class="form-control"')
                            ];
                            $contents[] = [
                                'align' => 'center',
                                'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SELECT . '</button> <a href="' . zen_href_link(FILENAME_PRODUCTS_TO_CATEGORIES,
                                        'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
                            ];
                            break;
                        default:
                            // only show if a Product is selected
                            if ($products_filter > 0) {
                                $heading[] = ['text' => '<h4>ID#' . $product_to_copy->fields['products_id'] . ' - ' . $product_to_copy->fields['products_name'] . '</h4>'];
                                $contents[] = [
                                    'text' => zen_image(DIR_WS_CATALOG_IMAGES . $product_to_copy->fields['products_image'], $product_to_copy->fields['products_name'], SMALL_IMAGE_WIDTH,
                                        SMALL_IMAGE_HEIGHT)
                                ];
                                $contents[] = ['text' => TEXT_PRODUCTS_NAME . $product_to_copy->fields['products_name']];
                                $contents[] = ['text' => TEXT_PRODUCTS_MODEL . $product_to_copy->fields['products_model']];
                                $contents[] = ['text' => 'Sort Order: ' . $product_to_copy->fields['products_sort_order']];
                                $contents[] = ['text' => TEXT_PRODUCTS_PRICE . zen_get_products_display_price($products_filter)];
                                $display_priced_by_attributes = zen_get_products_price_is_priced_by_attributes($products_filter);
                                $contents[] = ['text' => $display_priced_by_attributes ? '<span class="alert">' . TEXT_PRICED_BY_ATTRIBUTES . '</span>' : ''];
                                $contents[] = ['text' => zen_get_products_quantity_min_units_display($products_filter, $include_break = false)];

                                switch (true) {
                                    case ($product_to_copy->fields['master_categories_id'] === 0 && $products_filter > 0):
                                        $contents[] = ['text' => '<span class="alert">' . ERROR_DEFINE_PRODUCTS_MASTER_CATEGORIES_ID . '</span>'];
                                        break;
                                    default:
                                        $contents[] = [
                                            'align' => 'center',
                                            'text' =>
                                                '<a href="' . zen_href_link(FILENAME_PRODUCT,
                                                    'action=new_product' . '&cPath=' . zen_get_parent_category_id($products_filter) . '&pID=' . $products_filter . '&product_type=' . zen_get_products_type($products_filter)) . '" class="btn btn-info" role="button">' . IMAGE_EDIT_PRODUCT . '</a>&nbsp;' .
                                                '<a href="' . zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING,
                                                    'cPath=' . zen_get_parent_category_id($products_filter) . '&pID=' . $products_filter) . '" class="btn btn-info" role="button">' . BUTTON_CATEGORY_LISTING . '</a><br><br>' .
                                                '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER,
                                                    'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . IMAGE_EDIT_ATTRIBUTES . '</a>&nbsp;' .
                                                '<a href="' . zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER,
                                                    'products_filter=' . $products_filter . '&current_category_id=' . $current_category_id) . '" class="btn btn-info" role="button">' . IMAGE_PRODUCTS_PRICE_MANAGER . '</a>'
                                        ];
                                        $contents[] = ['text' => zen_draw_separator('pixel_black.gif', '100%', '1')];
                                        $contents[] = [
                                            'align' => 'center',
                                            'text' => zen_draw_form('new_products_to_categories', FILENAME_PRODUCTS_TO_CATEGORIES,
                                                    'action=edit&current_category_id=' . $current_category_id) . zen_draw_hidden_field('products_filter',
                                                    $products_filter) . '<button type="submit" class="btn btn-primary">' . BUTTON_NEW_PRODUCTS_TO_CATEGORIES . '</button></form>'
                                        ];
                                        break;
                                }
                            }
                            break;
                    }

                    if (!empty($heading) && !empty($contents)) {
                        $box = new box();
                        echo $box->infoBox($heading, $contents);
                    }
                    ?>
                </div>
            <?php } ?>
            <!-- infoBox eof -->
        </div>
        <!-- RIGHT column block (infoBox) eof -->
    </div>
    <!-- Product selection-infoBox block eof -->
    <hr>
    <!-- Category Links -->
    <?php if ($products_filter > 0 && $product_to_copy->fields['master_categories_id'] > 0) { //a product is selected AND it has a master category ?>
        <div class="row">
            <div class="col-lg-12">
                <h3><?php echo TEXT_HEADING_LINKED_CATEGORIES; ?></h3>
                <?php echo TEXT_INFO_PRODUCTS_TO_CATEGORIES_LINKER_INTRO; ?>
                <div class="form-group text-center">
                    <?php if ($product_to_copy->fields['master_categories_id'] < 1) { ?>
                        <span class="alert"><?php echo TEXT_SET_MASTER_CATEGORIES_ID; ?></span>
                    <?php } ?>
                </div>
                <div><?php // make dropdown to select the base target category, whose subcategories are subsequently displayed
                    echo zen_draw_form('set_target_category_form', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=set_target_category' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post');
                    $select_all_categories_option = [
                        [
                            'id' => TOPMOST_CATEGORY_PARENT_ID,
                            'text' => TEXT_TOP
                        ]
                    ];
                    $category_select_values = zen_get_target_categories_products(TOPMOST_CATEGORY_PARENT_ID, '&nbsp;&nbsp;&nbsp;', $select_all_categories_option);
                    ?>
                    <label><?php echo TEXT_LABEL_CATEGORY_DISPLAY_ROOT . zen_draw_pull_down_menu('target_category_id', $category_select_values, $target_category_id, 'onChange="this.form.submit();"'); ?></label>
                    <?php
                    echo zen_draw_hidden_field('products_filter', $_GET['products_filter']);
                    echo zen_hide_session_id();
                    ?>
                    <noscript><input type="submit" value="<?php echo IMAGE_DISPLAY; ?>"></noscript>
                    <?php echo '</form>'; ?>
                    <?php if ($target_category_id !== (int)P2C_TARGET_CATEGORY_DEFAULT) { // show a Set Default button if the selected target category is different from the saved default
                        echo zen_draw_form('set_default_target_category_form', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=set_default_target_category' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post'); ?>
                        <button type="submit" class="btn btn-info" title="<?php echo BUTTON_SET_DEFAULT_TARGET_CATEGORY_TITLE; ?>"><?php echo BUTTON_SET_DEFAULT_TARGET_CATEGORY; ?></span></button>
                        <?php
                        echo zen_draw_hidden_field('default_target_category_id', $target_category_id);
                        echo '</form>';
                    } ?>
                </div>
                <div>
                    <?php
                    $selected_categories = [];
                    foreach ($product_linked_categories as $product_linked_category) {
                        $selected_categories[] = (int)$product_linked_category['categories_id'];
                    }
                    ?>
                    <span id="toggleCheckbox"></span><?php // placeholder for toggle checkbox: no content when javascript disabled ?>
                    <script title="toggle all checkboxes">
                        document.getElementById('toggleCheckbox').innerHTML = '<p><label><input type="checkbox" onClick="toggle(this)"> <?php echo TEXT_LABEL_SELECT_ALL_OR_NONE; ?></label></p>';

                        function toggle(source) {
                            let checkboxes = document.getElementsByClassName('TargetCategoryCheckbox');
                            for (let i = 0, n = checkboxes.length; i < n; i++) {
                                checkboxes[i].checked = source.checked;
                            }
                        }
                    </script>
                </div>
                <?php echo zen_draw_form('update', FILENAME_PRODUCTS_TO_CATEGORIES, 'action=update_product&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id . '&target_category_id=' . $target_category_id, 'post');
                zen_draw_hidden_field('current_master_categories_id', $product_to_copy->fields['master_categories_id']); ?>
                <table class="table-bordered">
                    <thead>
                    <?php $cnt_columns = 0; ?>
                    <tr class="dataTableHeadingRow">
                        <?php
                        while ($cnt_columns !== (int)MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS) {
                            $cnt_columns++;
                            ?>
                            <th class="dataTableHeadingContent"><?php echo TEXT_CATEGORIES_NAME; ?></th>
                            <?php
                        }
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $cnt_columns = 0;
                    $num_target_categories = count($categories_info);

                    for ($cat_i = 0; $cat_i < $num_target_categories; $cat_i++) {

                        // Create an object and populate it with the properties expected by the script (an array with
                        // the category's ID and name stored in a "fields" property)
                        $categories_list = new stdClass();
                        $categories_list->fields = $categories_info[$cat_i];
                        $cnt_columns++;
                        if (count($selected_categories) !== 0) {
                            $selected = in_array((int)$categories_list->fields['categories_id'], $selected_categories, true);
                        } else {
                            $selected = false;
                        }
                        // Add a class to the checkbox so that it can be identified as a target category checkbox, for the purposes of selecting all/none at once
                        $zc_categories_checkbox = zen_draw_checkbox_field('categories_add[]',
                            $categories_list->fields['categories_id'], $selected, '', 'class="TargetCategoryCheckbox"');

                        if ($cnt_columns === 1) {
                            ?>
                            <tr class="dataTableRow">
                            <?php
                        }

                        if ((int)$product_to_copy->fields['master_categories_id'] === (int)$categories_list->fields['categories_id']) {
                            echo '  <td class="dataTableContent" title="' . TEXT_VALID_CATEGORIES_ID . ': ' . $categories_list->fields['categories_id'] . '">' . zen_icon('enabled', TEXT_MASTER_CATEGORIES_ID . ' ' . $product_to_copy->fields['master_categories_id'], 'lg') . '&nbsp;' . htmlspecialchars($categories_list->fields['categories_name'], ENT_COMPAT, CHARSET) . '</td>' . "\n";
                        } else {
                            echo '  <td class="dataTableContent"><label class="labelForCheck" title="' . TEXT_VALID_CATEGORIES_ID . ': ' . $categories_list->fields['categories_id'] . '">' . $zc_categories_checkbox . '<span>' . htmlspecialchars($categories_list->fields['categories_name'], ENT_COMPAT, CHARSET) . '</span></label></td>' . "\n";
                        } // span is required inside label to allow css selection for highlighting when input checked

                        if ($cnt_columns === (int)MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS ||
                            $cat_i === ($num_target_categories - 1)) {
                            if ($cat_i === ($num_target_categories - 1) &&
                                $cnt_columns !== (int)MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS) {
                                while ($cnt_columns < (int)MAX_DISPLAY_PRODUCTS_TO_CATEGORIES_COLUMNS) {
                                    $cnt_columns++;
                                    ?>
                                    <td class="dataTableContent">&nbsp;</td>
                                    <?php
                                }
                            }
                            ?>
                            </tr>
                            <?php
                            $cnt_columns = 0;
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-primary floatButton"
                            title="<?php echo BUTTON_UPDATE_CATEGORY_LINKS . " - " . $product_to_copy->fields['products_name']; ?>"><?php echo BUTTON_UPDATE_CATEGORY_LINKS . '<br><span>' . $product_to_copy->fields['products_model'] . '<br>' . $product_to_copy->fields['products_name'] . '<br>(#' . $products_filter . ')'; ?></span></button>
                </div>
                <?php echo '</form>'; ?>
            </div>
        </div>
    <?php } ?>
    <!-- Category Links eof -->
    <!-- Product-category links block eof-->

    <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '2'); ?></div>

    <!-- Global Tools -->
    <div class="col-lg-12">
        <h2><?php echo HEADER_CATEGORIES_GLOBAL_TOOLS; ?></h2>
        <!-- Copy linked categories from one product to another -->
        <div class="row dataTableHeadingRow">
            <h3><?php echo TEXT_HEADING_COPY_LINKED_CATEGORIES; ?></h3>
            <div class="form-group-row">
                <?php echo sprintf(TEXT_INFO_COPY_LINKED_CATEGORIES, ($products_filter > 0 ? ': <strong>' . $source_product_details . '</strong><br>' : ' ')); ?>
            </div>
            <?php
            if ($products_filter > 0) {
                echo '<br>' . zen_draw_form('enable_copy_links_dropdown_form', FILENAME_PRODUCTS_TO_CATEGORIES, zen_get_all_get_params(), 'post');
                echo zen_draw_label(TEXT_LABEL_ENABLE_COPY_LINKS, 'enable_copy_links_dropdown_checkbox', 'class="control-label"');
                echo zen_draw_checkbox_field('enable_copy_links_dropdown_checkbox', '1', $enable_copy_links_dropdown, '', 'id="enable_copy_links_dropdown_checkbox" onClick="this.form.submit();"');
                echo zen_draw_hidden_field('enable_copy_links_dropdown', (!$enable_copy_links_dropdown ? 'true' : ''));
                echo '</form>';
                if ($enable_copy_links_dropdown) {
                    echo zen_draw_form('copy_linked_categories_to_another_product', FILENAME_PRODUCTS_TO_CATEGORIES, zen_get_all_get_params('action') . '&action=copy_linked_categories_to_another_product', 'post', 'class="form-horizontal"');
                    // Get the list of products and build a select gadget
                    $category_product_tree_array = [];
                    $category_product_tree_array[] = [
                        'id' => '',
                        'text' => TEXT_OPTION_LINKED_CATEGORIES
                    ];
                    $category_product_tree_array = zen_get_target_categories_products(TOPMOST_CATEGORY_PARENT_ID, '', $category_product_tree_array, 'product');
                    ?>
                    <div class="form-group-row">
                        <div class="col-lg-8">
                            <?php echo zen_draw_pull_down_menu('target_product_id', $category_product_tree_array, '', 'id="target_product_id"'); ?>
                        </div>
                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-primary" name="type"
                                    value="add"><?php echo BUTTON_COPY_LINKED_CATEGORIES_ADD; ?></button>
                        </div>
                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-danger" name="type"
                                    value="replace"><?php echo BUTTON_COPY_LINKED_CATEGORIES_REPLACE; ?></button>
                        </div>
                    </div>
                    <?php echo '</form>';
                }
            } ?>
        </div>
        <!-- Copy linked categories from one product to another eof -->
        <hr>
        <div><?php echo TEXT_PRODUCTS_ID_NOT_REQUIRED; ?></div>
        <!-- Copy all products from one category to another as linked products -->
        <div class="row dataTableHeadingRow">
            <?php echo zen_draw_form('linked_copy', FILENAME_PRODUCTS_TO_CATEGORIES,
                'action=copy_products_as_linked' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post',
                'class="form-horizontal"'); ?>
            <h3><?php echo TEXT_HEADING_COPY_ALL_PRODUCTS_TO_CATEGORY_LINKED; ?></h3>
            <div class="form-group-row">
                <?php echo TEXT_INFO_COPY_ALL_PRODUCTS_TO_CATEGORY_LINKED; ?>
            </div>
            <div class="form-group-row">
                <div class="col-lg-4">
                    <?php echo zen_draw_label(TEXT_LABEL_COPY_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED, 'category_id_source',
                            'class="control-label"') . zen_draw_input_field('category_id_source', '', 'id="category_id_source" class="form-control" step="1" min="1"', '',
                            'number'); ?>
                </div>
                <div class="col-lg-4">
                    <?php echo zen_draw_label(TEXT_LABEL_COPY_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED, 'category_id_target',
                            'class="control-label"') . zen_draw_input_field('category_id_target', '', 'id="category_id_target" class="form-control" step="1" min="1"', '',
                            'number'); ?>
                </div>
                <div class="col-lg-4">
                    <button type="submit" class="btn btn-primary"><?php echo BUTTON_COPY_CATEGORY_LINKED; ?></button>
                </div>
            </div>
            <?php echo '</form>'; ?>
        </div>
        <!-- Copy all products from one category to another as linked products eof -->

        <!-- Remove products from one category that are linked to another category -->
        <div class="row dataTableHeadingRow">
            <?php echo zen_draw_form('linked_remove', FILENAME_PRODUCTS_TO_CATEGORIES,
                'action=remove_linked_products' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post',
                'class="form-horizontal"'); ?>
            <h3><?php echo TEXT_HEADING_REMOVE_ALL_PRODUCTS_FROM_CATEGORY_LINKED; ?></h3>
            <div class="form-group-row">
                <?php echo sprintf(TEXT_INFO_REMOVE_ALL_PRODUCTS_TO_CATEGORY_LINKED, $current_category_id); ?>
            </div>
            <div class="form-group-row">
                <div class="col-lg-4">
                    <?php echo zen_draw_label(TEXT_LABEL_REMOVE_ALL_PRODUCTS_TO_CATEGORY_FROM_LINKED, 'category_id_reference',
                            'class="control-label"') . zen_draw_input_field('category_id_reference', '', 'id="category_id_reference" class="form-control" step="1" min="1"', '',
                            'number'); ?>
                </div>
                <div class="col-lg-4">
                    <?php echo zen_draw_label(TEXT_LABEL_REMOVE_ALL_PRODUCTS_TO_CATEGORY_TO_LINKED, 'category_id_target_remove',
                            'class="control-label"') . zen_draw_input_field('category_id_target_remove', '', 'id="category_id_target_remove" class="form-control" step="1" min="1"', '',
                            'number'); ?>
                </div>
                <div class="col-lg-4">
                    <button type="submit" class="btn btn-primary"><?php echo BUTTON_REMOVE_CATEGORY_LINKED; ?></button>
                </div>
            </div>
            <?php echo '</form>'; ?>
        </div>
        <!-- Remove products from one category that are linked to another category eof -->

        <!-- Reset master_categories_id for all products in the selected category -->
        <div class="row dataTableHeadingRow">
            <?php echo zen_draw_form('master_reset', FILENAME_PRODUCTS_TO_CATEGORIES,
                'action=reset_products_category_as_master' . '&products_filter=' . $products_filter . '&current_category_id=' . $current_category_id, 'post',
                'class="form-horizontal"'); ?>
            <h3><?php echo TEXT_HEADING_RESET_ALL_PRODUCTS_TO_CATEGORY_MASTER; ?></h3>
            <div class="form-group-row">
                <?php echo TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_MASTER; ?>
            </div>
            <div class="form-group-row">
                <div class="col-lg-8">
                    <?php echo zen_draw_label(TEXT_INFO_RESET_ALL_PRODUCTS_TO_CATEGORY_FROM_MASTER, 'category_id_as_master',
                            'class="control-label"') . zen_draw_input_field('category_id_as_master', '', ' id="category_id_as_master" class="form-control" step="1" min="1"', '',
                            'number'); ?>
                </div>
                <div class="col-lg-4">
                    <button type="submit" class="btn btn-danger"><?php echo BUTTON_RESET_CATEGORY_MASTER; ?></button>
                </div>
            </div>
            <?php echo '</form>'; ?>
        </div>
        <!-- Reset master_categories_id for all products in the selected category eof -->

    </div>
    <!-- Global Tools eof -->

    <!-- body_text_eof //-->
</div>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
