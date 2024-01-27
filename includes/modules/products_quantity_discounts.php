<?php
/**
 * products_quantity_discounts module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Dec 10 Modified in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

// if customer authorization is on do not show discounts

$zc_hidden_discounts_on = false;
$zc_hidden_discounts_text = '';
switch (true) {
    case (CUSTOMERS_APPROVAL == '1' && !zen_is_logged_in()):
        // customer must be logged in to browse
        $zc_hidden_discounts_on = true;
        $zc_hidden_discounts_text = 'MUST LOGIN';
        break;
    case (STORE_STATUS === '1' || CUSTOMERS_APPROVAL === '2' && !zen_is_logged_in()):
        // customer may browse but no prices
        $zc_hidden_discounts_on = true;
        $zc_hidden_discounts_text = TEXT_LOGIN_FOR_PRICE_PRICE;
        break;
    case (CUSTOMERS_APPROVAL === '3' && TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM !== ''):
        // customer may browse but no prices
        $zc_hidden_discounts_on = true;
        $zc_hidden_discounts_text = TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM;
        break;
    case (CUSTOMERS_APPROVAL_AUTHORIZATION !== '0' && !zen_is_logged_in()):
        // customer must be logged in to browse
        $zc_hidden_discounts_on = true;
        $zc_hidden_discounts_text = TEXT_AUTHORIZATION_PENDING_PRICE;
        break;
    case (CUSTOMERS_APPROVAL_AUTHORIZATION !== '0' && CUSTOMERS_APPROVAL_AUTHORIZATION !== '3' && $_SESSION['customers_authorization'] > '0'):
        // customer must be logged in to browse
        $zc_hidden_discounts_on = true;
        $zc_hidden_discounts_text = TEXT_AUTHORIZATION_PENDING_PRICE;
        break;
    default:
        // proceed normally
        break;
}
// create products discount output table

// find out the minimum quantity for this product
$products_min_query = zen_get_product_details((int)$products_id_current);
$products_quantity_order_min = $products_min_query->fields['products_quantity_order_min'];

// retrieve the list of discount levels for this product
$products_discounts_query = $db->Execute(
    "SELECT *
       FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
      WHERE products_id = " . (int)$products_id_current . "
        AND discount_qty != 0
      ORDER BY discount_qty"
);

$discount_col_cnt = (int)DISCOUNT_QUANTITY_PRICES_COLUMN;

$display_price = zen_get_products_base_price($products_id_current);
$display_specials_price = zen_get_products_special_price($products_id_current, false);

// -----
// Set the first column's discount price ($show_price) and discount quantity ($show_quantity)
//
$show_price = ($display_specials_price === false) ? $display_price : $display_specials_price;
switch (true) {
    case ($products_discounts_query->EOF || $products_discounts_query->fields['discount_qty'] <= 2):
        $show_qty = '1';
        break;
    case ($products_quantity_order_min == ($products_discounts_query->fields['discount_qty'] - 1) || $products_quantity_order_min == ($products_discounts_query->fields['discount_qty'])):
        $show_qty = $products_quantity_order_min;
        break;
    default:
        $show_qty = $products_quantity_order_min . '-' . number_format($products_discounts_query->fields['discount_qty'] - 1);
        break;
}

$display_price = zen_get_products_base_price($products_id_current);
$display_specials_price = zen_get_products_special_price($products_id_current, false);

// -----
// The $products_discount_type is set by the product page's main_template_vars.php.
//
$discount_price_basis = ($products_discount_type_from === '0' || !$display_specials_price) ? $display_price : $display_specials_price;

// -----
// Build up the 2nd and following columns' discount-price/quantity values.
//
$quantityDiscounts = [];
$columnCount = 0;
foreach ($products_discounts_query as $next_discount) {
    if ($columnCount !== 0 && $next_discount['discount_qty'] != $show_qty) {
        if ($quantityDiscounts[$columnCount - 1]['show_qty'] < $next_discount['discount_qty'] - 1) {
            $quantityDiscounts[$columnCount - 1]['show_qty'] .= '-' . number_format($next_discount['discount_qty'] - 1);
        }
    }
    $columnCount++;

    // -----
    // Determine the discount's pricing (retail vs. wholesale).
    //
    $next_discount_price = zen_get_retail_or_wholesale_price($next_discount['discount_price'], $next_discount['discount_price_w']);

    // -----
    // The $products_discount_type is set by the product page's main_template_vars.php.
    //
    switch ($products_discount_type) {
        // none
        case '0':
            $discounted_price = 0;
            break;

        // percentage discount
        case '1':
            $discounted_price = $discount_price_basis - ($discount_price_basis * ($next_discount_price / 100));
            break;

        // actual price
        case '2':
            $discounted_price = $next_discount_price;
            break;

        // amount offprice
        case '3':
            $discounted_price = $discount_price_basis - $next_discount_price;
            break;
    }

    $quantityDiscounts[] = [
        'discounted_price' => $discounted_price,
        'show_qty' => number_format($next_discount['discount_qty']),
    ];
}

if ($columnCount === 0) {
    $disc_cnt = 1;
} else {
    $disc_cnt = 0;
    $quantityDiscounts[$columnCount - 1]['show_qty'] .= '+';
}
