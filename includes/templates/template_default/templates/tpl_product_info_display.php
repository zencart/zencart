<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=product_info.
 * Displays details of a typical product
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 17 Modified in v2.1.0-beta1 $
 */
// -----
// Enabling this product-information template to be reused for other product
// types.
//
$product_info_html_id = $product_info_html_id ?? 'productGeneral';
$product_info_class = $product_info_class ?? 'productGeneral';
?>
<div class="centerColumn" id="<?= $product_info_html_id ?>">

<!--bof Form start-->
<?= zen_draw_form('cart_quantity', zen_href_link(zen_get_info_page($_GET['products_id']), zen_get_all_get_params(['action']) . 'action=add_product', $request_type), 'post', 'enctype="multipart/form-data" id="addToCartForm"') . "\n" ?>
<!--eof Form start-->

<?php
if ($messageStack->size('product_info') > 0) {
    echo $messageStack->output('product_info');
}
?>

<!--bof Category Icon -->
<?php
if ($module_show_categories != 0) {
    /**
     * display the category icons
     */
    require $template->get_template_dir('/tpl_modules_category_icon_display.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_category_icon_display.php'; ?>
<?php
}
?>
<!--eof Category Icon -->

<!--bof Prev/Next top position -->
<?php
if (PRODUCT_INFO_PREVIOUS_NEXT === '1' || PRODUCT_INFO_PREVIOUS_NEXT === '3') {
    /**
     * display the product previous/next helper
     */
    require $template->get_template_dir('/tpl_products_next_previous.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_products_next_previous.php'; ?>
<?php
}
?>
<!--eof Prev/Next top position-->

<!--bof Main Product Image -->
<?php
if (!empty($products_image) || !empty($enable_additional_images_without_main_image)) {
    /**
     * display the main product image
     */
    require $template->get_template_dir('/tpl_modules_main_product_image.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_main_product_image.php'; ?>
<?php
}
?>
<!--eof Main Product Image-->

<!--bof Product Name-->
    <h1 id="productName" class="<?= $product_info_class ?>"><?= $products_name ?></h1>
<!--eof Product Name-->

<!--bof Product Price block -->
    <h2 id="productPrices" class="<?= $product_info_class ?>">
<?php
// base price
if ($show_onetime_charges_description == 'true') {
    $one_time = '<span>' . TEXT_ONETIME_CHARGE_SYMBOL . TEXT_ONETIME_CHARGE_DESCRIPTION . '</span><br>';
} else {
    $one_time = '';
}
echo
    $one_time .
    ((zen_has_product_attributes_values((int)$_GET['products_id']) && $flag_show_product_info_starting_at == 1) ? TEXT_BASE_PRICE : '') .
    zen_get_products_display_price((int)$_GET['products_id']);
?>
    </h2>
<!--eof Product Price block -->

<!--bof free ship icon  -->
<?php
if (zen_get_product_is_always_free_shipping($products_id_current) && $flag_show_product_info_free_shipping) {
?>
    <div id="freeShippingIcon"><?= TEXT_PRODUCT_FREE_SHIPPING_ICON ?></div>
<?php
}
?>
<!--eof free ship icon  -->

 <!--bof Product description -->
<?php
if ($products_description != '') {
?>
    <div id="productDescription" class="<?= $product_info_class ?> biggerText">
        <?= stripslashes($products_description) ?>
    </div>
<?php
}
?>
<!--eof Product description -->
    <br class="clearBoth">

<!--bof Add to Cart Box -->
<?php
if (CUSTOMERS_APPROVAL === '3' && TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE_SHOWROOM == '') {
  // do nothing
} else {
    $display_qty = (($flag_show_product_info_in_cart_qty == 1 && $_SESSION['cart']->in_cart($_GET['products_id'])) ? '<p>' . PRODUCTS_ORDER_QTY_TEXT_IN_CART . $_SESSION['cart']->get_quantity($_GET['products_id']) . '</p>' : '');
    if ($products_qty_box_status == 0 || $products_quantity_order_max == 1) {
        // hide the quantity box and default to 1
        $the_button =
            zen_draw_hidden_field('cart_quantity', '1') .
            zen_draw_hidden_field('products_id', (int)$_GET['products_id']) .
            zen_image_submit(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT);
    } else {
        // show the quantity box
        $the_button = 
            PRODUCTS_ORDER_QTY_TEXT .
            '<input type="text" name="cart_quantity" value="' . $products_get_buy_now_qty . '" maxlength="6" size="4" aria-label="' . ARIA_QTY_ADD_TO_CART . '">' .
            '<br>' .
            zen_get_products_quantity_min_units_display((int)$_GET['products_id']) .
            '<br>' .
            zen_draw_hidden_field('products_id', (int)$_GET['products_id']) .
            zen_image_submit(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT);
    }
    $display_button = zen_get_buy_now_button($_GET['products_id'], $the_button);

    if ($display_qty != '' || $display_button != '') {
?>
    <div id="cartAdd">
        <?= $display_qty . $display_button ?>
    </div>
<?php
    } // display qty and button
} // CUSTOMERS_APPROVAL == 3
?>
<!--eof Add to Cart Box-->

<!--bof Product details list  -->
<?php
// -----
// The product-info display is now common to all product
// types.  Some types, like product_music_info, might supply their own version
// of the product-details list.
//
// If such a file, based on the current type, is available, use that override
// instead of the base processing.
//
$product_details_filename = '/tpl_' . $current_page_base . '_display_details.php';
$product_details_filepath = $template->get_template_dir($product_details_filename, DIR_WS_TEMPLATE, $current_page_base, 'templates') . $product_details_filename;
if (file_exists($product_details_filepath)) {
    require $product_details_filepath;
} else {
    require $template->get_template_dir('/tpl_product_info_display_details.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_product_info_display_details.php';
}
?>
<!--eof Product details list -->
<?php
if ($flag_show_ask_a_question) {
?>
<!-- bof Ask a Question -->
    <br>
    <span id="productQuestions" class="biggerText">
        <b><a href="<?= zen_href_link(FILENAME_ASK_A_QUESTION, 'pid=' . $_GET['products_id'], 'SSL') ?>">
            <?= ASK_A_QUESTION ?>
        </a></b>
    </span>
    <br class="clearBoth">
<!-- eof Ask a Question -->
<?php
}
?>

<!--bof Attributes Module -->
<?php
if ($pr_attr->fields['total'] > 0) {
    /**
     * display the product attributes
     */
    require $template->get_template_dir('/tpl_modules_attributes.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_attributes.php';
}
?>
<!--eof Attributes Module -->

<!--bof Quantity Discounts table -->
<?php
if ($products_discount_type != 0) {
    /**
     * display the products quantity discount
     */
    require $template->get_template_dir('/tpl_modules_products_quantity_discounts.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_products_quantity_discounts.php'; ?>
<?php
}
?>
<!--eof Quantity Discounts table -->
<?php
// -----
// A product type's base template can identify additional formatting for the specific product type, e.g. product-music.
//
if (isset($product_info_display_extra)) {
    require $template->get_template_dir($product_info_display_extra, DIR_WS_TEMPLATE, $current_page_base, 'templates') . $product_info_display_extra;
}
?>
<!--bof Additional Product Images -->
<?php
/**
 * display the products additional images
 */
require $template->get_template_dir('/tpl_modules_additional_images.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_additional_images.php'; ?>
<!--eof Additional Product Images -->

<!--bof Prev/Next bottom position -->
<?php
if (PRODUCT_INFO_PREVIOUS_NEXT === '2' || PRODUCT_INFO_PREVIOUS_NEXT === '3') {
    /**
     * display the product previous/next helper
     */
    require $template->get_template_dir('/tpl_products_next_previous.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_products_next_previous.php';
}
?>
<!--eof Prev/Next bottom position -->

<!--bof Reviews button and count-->
<?php
if ($flag_show_product_info_reviews == 1) {
    // if more than 0 reviews, then show reviews button; otherwise, show the "write review" button
    if ($reviews->fields['count'] > 0 ) {
?>
    <div id="productReviewLink" class="buttonRow back">
        <a href="<?= zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params()) ?>">
            <?= zen_image_button(BUTTON_IMAGE_REVIEWS, BUTTON_REVIEWS_ALT) ?>
        </a>
    </div>
    <br class="clearBoth">
    <p class="reviewCount">
        <?= ($flag_show_product_info_reviews_count == 1 ? TEXT_CURRENT_REVIEWS . ' ' . $reviews->fields['count'] : '') ?>
    </p>
<?php
    } else {
?>
    <div id="productReviewLink" class="buttonRow back">
        <a href="<?= zen_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, zen_get_all_get_params()) ?>">
            <?= zen_image_button(BUTTON_IMAGE_WRITE_REVIEW, BUTTON_WRITE_REVIEW_ALT) ?>
        </a>
    </div>
    <br class="clearBoth">
<?php
    }
}
?>
<!--eof Reviews button and count -->

<!--bof Product date added/available-->
<?php
if ($products_date_available > date('Y-m-d H:i:s')) {
    if ($flag_show_product_info_date_available == 1) {
?>
    <p id="productDateAvailable" class="<?= $product_info_class ?> centeredContent">
        <?= sprintf(TEXT_DATE_AVAILABLE, zen_date_long($products_date_available)) ?>
    </p>
<?php
    }
} elseif ($flag_show_product_info_date_added == 1) {
?>
    <p id="productDateAdded" class="<?= $product_info_class ?> centeredContent">
        <?= sprintf(TEXT_DATE_ADDED, zen_date_long($products_date_added)) ?>
    </p>
<?php
} // $flag_show_product_info_date_added
?>
<!--eof Product date added/available -->

<!--bof Product URL -->
<?php
if (!empty($products_url) && $flag_show_product_info_url == 1) {
?>
    <p id="productInfoLink" class="<?= $product_info_class ?> centeredContent">
        <?= sprintf(TEXT_MORE_INFORMATION, zen_href_link(FILENAME_REDIRECT, 'action=product&products_id=' . zen_output_string_protected($_GET['products_id']), 'NONSSL', true, false)) ?>
    </p>
<?php
} // $flag_show_product_info_url
?>
<!--eof Product URL -->

<!--bof also purchased products module-->
<?php require $template->get_template_dir('tpl_modules_also_purchased_products.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_also_purchased_products.php'; ?>
<!--eof also purchased products module-->

<!--bof Form close-->
<?= '</form>'; ?>
<!--bof Form close-->
</div>
