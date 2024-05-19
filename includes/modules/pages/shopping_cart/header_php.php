<?php
/**
 * shopping_cart header_php.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2024 Apr 16 Modified in v2.0.1 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_SHOPPING_CART');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE);
if (isset($_GET['jscript']) && $_GET['jscript'] == 'no') {
    $messageStack->add('shopping_cart', PAYMENT_JAVASCRIPT_DISABLED, 'error');
}
// Validate Cart for checkout
$_SESSION['valid_to_checkout'] = true;
$_SESSION['cart_errors'] = '';
$_SESSION['cart']->get_products(true);

// used to display invalid cart issues when checkout is selected that validated cart and returned to cart due to errors
if (isset($_SESSION['valid_to_checkout']) && $_SESSION['valid_to_checkout'] == false) {
    $messageStack->add('shopping_cart', ERROR_CART_UPDATE . $_SESSION['cart_errors'], 'caution');
}

$shipping_weight = $_SESSION['cart']->show_weight();
$numberOfItemsInCart = $_SESSION['cart']->count_contents();
$cartTotalPrice = $_SESSION['cart']->show_total();


$prodImgWidth = (int)IMAGE_SHOPPING_CART_WIDTH;
$prodImgHeight = (int)IMAGE_SHOPPING_CART_HEIGHT;

$flagAnyOutOfStock = false;

$productArray = [];
$products = $_SESSION['cart']->get_products();

$zco_notifier->notify('NOTIFY_HEADER_SHOPPING_CART_BEFORE_PRODUCTS_LOOP', null, $products);

for ($i = 0, $n = count($products); $i < $n; $i++) {
    $flagStockCheck = '';
    $ppe = $ppt = 0;
    $rowClass = (($i / 2) == floor($i / 2)) ? "rowEven" : "rowOdd";

    $attributeHiddenField = "";
    $attrArray = [];

    $productsName = $products[$i]['name'];
    $productsModel = $products[$i]['model'];
    // Push all attribute information into an array
    if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        if (PRODUCTS_OPTIONS_SORT_ORDER == '0') {
            $options_order_by = " ORDER BY LPAD(popt.products_options_sort_order,11,'0')";
        } else {
            $options_order_by = ' ORDER BY popt.products_options_name';
        }
        foreach ($products[$i]['attributes'] as $option => $value) {
            $sql = "SELECT popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.attributes_image
                    FROM " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                    WHERE pa.products_id = :productsID
                    AND pa.options_id = :optionsID
                    AND pa.options_id = popt.products_options_id
                    AND pa.options_values_id = :optionsValuesID
                    AND pa.options_values_id = poval.products_options_values_id
                    AND popt.language_id = :languageID
                    AND poval.language_id = :languageID " . $options_order_by;

            $sql = $db->bindVars($sql, ':productsID', $products[$i]['id'], 'integer');
            $sql = $db->bindVars($sql, ':optionsID', $option, 'integer');
            $sql = $db->bindVars($sql, ':optionsValuesID', $value, 'integer');
            $sql = $db->bindVars($sql, ':languageID', $_SESSION['languages_id'], 'integer');
            $attributes_values = $db->Execute($sql);

            if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
                $attributeHiddenField .= zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . TEXT_PREFIX . $option . ']', $products[$i]['attributes_values'][$option]);
                $attr_value = htmlspecialchars($products[$i]['attributes_values'][$option], ENT_COMPAT, CHARSET, TRUE);
            } else {
                $attributeHiddenField .= zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
                $attr_value = $attributes_values->fields['products_options_values_name'];
            }

            $attrArray[$option]['products_options_name'] = $attributes_values->fields['products_options_name'];
            $attrArray[$option]['options_values_id'] = $value;
            $attrArray[$option]['products_options_values_name'] = $attr_value;
            $attrArray[$option]['options_values_price'] = $attributes_values->fields['options_values_price'];
            $attrArray[$option]['price_prefix'] = $attributes_values->fields['price_prefix'];
            $attrArray[$option]['image'] = $attributes_values->fields['attributes_image'];

            $zco_notifier->notify('NOTIFY_HEADER_SHOPPING_CART_IN_ATTRIBUTES_LOOP', $option, $attrArray, $attributes_values->fields, $value, $products, $i);
        }
    } //end foreach [attributes]

    // Stock Check
    if (STOCK_CHECK == 'true') {
        $qtyAvailable = zen_get_products_stock($products[$i]['id']);
        // compare against product inventory, and against mixed=YES
        if ($qtyAvailable - $products[$i]['quantity'] < 0 || $qtyAvailable - $_SESSION['cart']->in_cart_mixed($products[$i]['id']) < 0) {
            $flagStockCheck = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
            $flagAnyOutOfStock = true;
        }
    }

    $linkProductsImage = zen_href_link(zen_get_info_page($products[$i]['id']), 'products_id=' . $products[$i]['id']);
    $linkProductsName = zen_href_link(zen_get_info_page($products[$i]['id']), 'products_id=' . $products[$i]['id']);
    $productsImage = (IMAGE_SHOPPING_CART_STATUS == 1 ? zen_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], $prodImgWidth, $prodImgHeight) : '');
    $show_products_quantity_max = zen_get_products_quantity_order_max($products[$i]['id']);
    $showFixedQuantity = (($show_products_quantity_max == 1 or zen_get_products_qty_box_status($products[$i]['id']) == 0) ? true : false);
    $showFixedQuantityAmount = $products[$i]['quantity'] . zen_draw_hidden_field('cart_quantity[]', $products[$i]['quantity']);
    $showMinUnits = zen_get_products_quantity_min_units_display($products[$i]['id']);
    $quantityField = zen_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="4" class="cart_input_' . $products[$i]['id'] . '" aria-label="' . ARIA_EDIT_QTY_IN_CART . '"');

    // $ppe is product price each, before one-time charges added
    $ppe = $products[$i]['final_price'];
    $ppe = zen_add_tax($ppe, zen_get_tax_rate($products[$i]['tax_class_id']));
    // $ppt is product price total, before one-time charges added
    $ppt = $ppe * $products[$i]['quantity'];

    $productsPriceEach = $currencies->format($ppe) . ($products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '');
    $productsPriceTotal = $currencies->format($ppt) . ($products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '');

    $buttonDelete = true;
    $checkBoxDelete = true;
    if (SHOW_SHOPPING_CART_DELETE == 1) {
        $checkBoxDelete = false;
    } elseif (SHOW_SHOPPING_CART_DELETE == 2) {
        $buttonDelete = false;
    }

    $buttonUpdate = '';
    if (SHOW_SHOPPING_CART_UPDATE == 1 or SHOW_SHOPPING_CART_UPDATE == 3) {
        if (!$showFixedQuantity) {
            $buttonUpdate = zen_image_submit(ICON_IMAGE_UPDATE, ICON_UPDATE_ALT);
        } else {
            $buttonUpdate = zen_image_submit(ICON_IMAGE_UPDATE, ICON_UPDATE_ALT, 'style="opacity: 0.25" disabled="disabled"');
        }
    }
    $buttonUpdate .= zen_draw_hidden_field('products_id[]', $products[$i]['id']);

    $productArray[$i] = [
        'attributeHiddenField' => $attributeHiddenField,
        'flagStockCheck' => $flagStockCheck,
        'flagShowFixedQuantity' => $showFixedQuantity,
        'linkProductsImage' => $linkProductsImage,
        'linkProductsName' => $linkProductsName,
        'productsImage' => $productsImage,
        'productsName' => $productsName,
        'productsModel' => $productsModel,
        'showFixedQuantity' => $showFixedQuantity,
        'showFixedQuantityAmount' => $showFixedQuantityAmount,
        'showMinUnits' => $showMinUnits,
        'quantityField' => $quantityField,
        'buttonUpdate' => $buttonUpdate,
        'productsPrice' => $productsPriceTotal,
        'productsPriceEach' => $productsPriceEach,
        'rowClass' => $rowClass,
        'buttonDelete' => $buttonDelete,
        'checkBoxDelete' => $checkBoxDelete,
        'id' => $products[$i]['id'],
        'attributes' => empty($attrArray) ? false : $attrArray,
    ];

    $zco_notifier->notify('NOTIFY_HEADER_SHOPPING_CART_IN_PRODUCTS_LOOP', $i, $productArray);

} // end FOR loop

$zco_notifier->notify('NOTIFY_HEADER_SHOPPING_CART_AFTER_PRODUCTS_LOOP', $productArray);

$flagHasCartContents = ($numberOfItemsInCart > 0);
$cartShowTotal = $currencies->format($cartTotalPrice);

// build shipping/items message with Tare included. We do this here in case any custom product stuff needs to alter the original values from the cart class
$totalsDisplay = '';
switch (SHOW_TOTALS_IN_CART) {
    case ('1'):
        $totalsDisplay = TEXT_TOTAL_ITEMS . $numberOfItemsInCart . TEXT_TOTAL_WEIGHT . $shipping_weight . TEXT_PRODUCT_WEIGHT_UNIT . TEXT_TOTAL_AMOUNT . $cartShowTotal;
        break;
    case ('2'):
        $totalsDisplay = TEXT_TOTAL_ITEMS . $numberOfItemsInCart . ($shipping_weight > 0 ? TEXT_TOTAL_WEIGHT . $shipping_weight . TEXT_PRODUCT_WEIGHT_UNIT : '') . TEXT_TOTAL_AMOUNT . $cartShowTotal;
        break;
    case ('3'):
        $totalsDisplay = TEXT_TOTAL_ITEMS . $numberOfItemsInCart . TEXT_TOTAL_AMOUNT . $cartShowTotal;
        break;
}

$define_page = zen_get_file_directory(DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/', FILENAME_DEFINE_SHOPPING_CART, 'false');

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_SHOPPING_CART');
