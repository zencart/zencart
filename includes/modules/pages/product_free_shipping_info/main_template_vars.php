<?php
/**
 *  product_free_shipping_info main_template_vars.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 24 Modified in v2.1.0-alpha1 $
 */
/*
 * Extracts and constructs the data to be used in the product-type template tpl_TYPEHANDLER_info_display.php
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_START_PRODUCT_FREE_SHIPPING_INFO');

if (!isset($product_info) || get_class($product_info) !== 'Product' || $product_info->getID() !== (int)$_GET['products_id']) {
    $product_info = new Product((int)$_GET['products_id']);
}

$product_not_found = !$product_info->exists();

if (!defined('DISABLED_PRODUCTS_TRIGGER_HTTP200') || DISABLED_PRODUCTS_TRIGGER_HTTP200 !== 'true') {
    if (!$product_not_found && $product_info->status() !== 1) {
        $product_not_found = true;
    }
}

if ($product_not_found) {
    $tpl_page_body = '/tpl_product_info_noproduct.php';
} else {
    $tpl_page_body = '/tpl_product_free_shipping_info_display.php';

    $zco_notifier->notify('NOTIFY_PRODUCT_VIEWS_HIT_INCREMENTOR', (int)$_GET['products_id']);

    $product_data = $product_info->getDataForLanguage();

    $products_id_current = (int)$_GET['products_id'];

    $products_price_sorter = $product_data['products_price_sorter'];

    $products_tax_class_id = $product_data['products_tax_class_id'];
    $products_tax_rate = zen_get_tax_rate($products_tax_class_id);

    $products_price = $currencies->display_price($product_data['products_price'], $products_tax_rate);

    $manufacturers_name = $product_data['manufacturers_name'];

    if ($new_price = zen_get_products_special_price($products_id_current)) {
        $specials_price = $currencies->display_price($new_price, $products_tax_rate);
    }

    // set flag for attributes module usage:
    $flag_show_weight_attrib_for_this_prod_type = SHOW_PRODUCT_FREE_SHIPPING_INFO_WEIGHT_ATTRIBUTES;
    // get attributes
    require DIR_WS_MODULES . zen_get_module_directory(FILENAME_ATTRIBUTES);

    $reviews_query =
        "SELECT COUNT(*) AS count FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
          WHERE r.products_id = " . $products_id_current . "
            AND r.reviews_id = rd.reviews_id
            AND rd.languages_id = " . (int)$_SESSION['languages_id'] . "
            AND r.status = 1";

    $reviews = $db->Execute($reviews_query);

    $products_name = $product_data['products_name'];
    $products_model = $product_data['products_model'];

    // if no common markup tags in description, add line breaks for readability:
    $products_description = $product_data['products_description'] ?? '';
    $products_description = (!preg_match('/(<br|<p|<div|<dd|<li|<span)/i', $products_description) ? nl2br($products_description) : $products_description);

    $products_image = (($product_not_found || $product_data['products_image'] == '') && PRODUCTS_IMAGE_NO_IMAGE_STATUS === '1') ? PRODUCTS_IMAGE_NO_IMAGE : '';
    if ($product_data['products_image'] != '' || PRODUCTS_IMAGE_NO_IMAGE_STATUS !== '1') {
        $products_image = $product_data['products_image'];
    }

    $products_url = $product_data['products_url'];
    $products_date_available = $product_data['products_date_available'];
    $products_date_added = $product_data['products_date_added'];
    $products_manufacturer = $manufacturers_name;
    $products_weight = $product_data['products_weight'];
    $products_quantity = $product_data['products_quantity'];

    $products_qty_box_status = $product_data['products_qty_box_status'];
    $products_quantity_order_max = $product_data['products_quantity_order_max'];
    $products_get_buy_now_qty = zen_get_buy_now_qty($_GET['products_id']);

    $products_base_price = $currencies->display_price(zen_get_products_base_price($products_id_current), $products_tax_rate);

    $product_is_free = $product_data['product_is_free'];

    $products_discount_type = $product_data['products_discount_type'];
    $products_discount_type_from = $product_data['products_discount_type_from'];

    require DIR_WS_MODULES . zen_get_module_directory('product_prev_next.php');

    $module_show_categories = PRODUCT_INFO_CATEGORIES;
    $module_next_previous = PRODUCT_INFO_PREVIOUS_NEXT;

    /**
     * Load product-type-specific main_template_vars
     */
    $prod_type_specific_vars_info = DIR_WS_MODULES . 'pages/' . $current_page_base . '/main_template_vars_product_type.php';
    if (file_exists($prod_type_specific_vars_info)) {
        include_once $prod_type_specific_vars_info;
    }
    $zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_PRODUCT_TYPE_VARS_PRODUCT_FREE_SHIPPING_INFO');
  
    /**
     * Load all *.PHP files from the /includes/templates/MYTEMPLATE/PAGENAME/extra_main_template_vars
     */
    $extras_dir = $template->get_template_dir('.php', DIR_WS_TEMPLATE, $current_page_base . 'extra_main_template_vars', $current_page_base . '/extra_main_template_vars');
    foreach (zen_get_files_in_directory($extras_dir) as $file) {
        include $file;
    }

    // build show flags from product type layout settings
    $flag_show_product_info_starting_at = zen_get_show_product_switch($products_id_current, 'starting_at');
    $flag_show_product_info_model = zen_get_show_product_switch($products_id_current, 'model');
    $flag_show_product_info_weight = zen_get_show_product_switch($products_id_current, 'weight');
    $flag_show_product_info_quantity = zen_get_show_product_switch($products_id_current, 'quantity');
    $flag_show_product_info_manufacturer = zen_get_show_product_switch($products_id_current, 'manufacturer');
    $flag_show_product_info_in_cart_qty = zen_get_show_product_switch($products_id_current, 'in_cart_qty');
    $flag_show_product_info_reviews = zen_get_show_product_switch($products_id_current, 'reviews');
    $flag_show_product_info_reviews_count = zen_get_show_product_switch($products_id_current, 'reviews_count');
    $flag_show_product_info_date_available = zen_get_show_product_switch($products_id_current, 'date_available');
    $flag_show_product_info_date_added = zen_get_show_product_switch($products_id_current, 'date_added');
    $flag_show_product_info_url = zen_get_show_product_switch($products_id_current, 'url');
    $flag_show_product_info_additional_images = zen_get_show_product_switch($products_id_current, 'additional_images');
    $flag_show_product_info_free_shipping = zen_get_show_product_switch($products_id_current, 'always_free_shipping_image_switch');
    $flag_show_ask_a_question = !empty(zen_get_show_product_switch($products_id_current, 'ask_a_question'));

    require DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCTS_QUANTITY_DISCOUNTS);

    $zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_EXTRA_PRODUCT_FREE_SHIPPING_INFO');
}

require $template->get_template_dir($tpl_page_body, DIR_WS_TEMPLATE, $current_page_base, 'templates') . $tpl_page_body;

//require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_ALSO_PURCHASED_PRODUCTS));

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_END_PRODUCT_FREE_SHIPPING_INFO');
