<?php
/**
 * products_quantity_discounts module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 09 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

// if customer authorization is on do not show discounts

$zc_hidden_discounts_on = false;
$zc_hidden_discounts_text = '';
switch (true) {
  case (CUSTOMERS_APPROVAL == '1' and !zen_is_logged_in()):
  // customer must be logged in to browse
  $zc_hidden_discounts_on = true;
  $zc_hidden_discounts_text = 'MUST LOGIN';
  break;
  case (STORE_STATUS == 1 || CUSTOMERS_APPROVAL == '2' and !zen_is_logged_in()):
  // customer may browse but no prices
  $zc_hidden_discounts_on = true;
  $zc_hidden_discounts_text = TEXT_LOGIN_FOR_PRICE_PRICE;
  break;
  case (CUSTOMERS_APPROVAL == '3' and TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM != ''):
  // customer may browse but no prices
  $zc_hidden_discounts_on = true;
  $zc_hidden_discounts_text = TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM;
  break;
  case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and !zen_is_logged_in()):
  // customer must be logged in to browse
  $zc_hidden_discounts_on = true;
  $zc_hidden_discounts_text = TEXT_AUTHORIZATION_PENDING_PRICE;
  break;
  case ((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customers_authorization'] > '0'):
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
$products_min_query = $db->Execute("SELECT products_quantity_order_min FROM " . TABLE_PRODUCTS . " WHERE products_id='" . (int)$products_id_current . "'");

$products_quantity_order_min = isset($products_min_query->fields['products_quantity_order_min']) ? $products_min_query->fields['products_quantity_order_min'] : 0;

// retrieve the list of discount levels for this product
$products_discounts_query = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE products_id='" . (int)$products_id_current . "' AND discount_qty !=0 " . " ORDER BY discount_qty");


$discount_col_cnt = DISCOUNT_QUANTITY_PRICES_COLUMN;

$display_price = zen_get_products_base_price($products_id_current);
$display_specials_price = zen_get_products_special_price($products_id_current, false);

// set first price value
if ($display_specials_price == false) {
  $show_price = $display_price;
} else {
  $show_price = $display_specials_price;
}

switch (true) {
  case (!$products_discounts_query->RecordCount() || $products_discounts_query->fields['discount_qty'] <= 2):
  $show_qty = '1';
  break;
  case ($products_quantity_order_min == ($products_discounts_query->fields['discount_qty']-1) || $products_quantity_order_min == ($products_discounts_query->fields['discount_qty'])):
  $show_qty = $products_quantity_order_min;
  break;
  default:
  $show_qty = $products_quantity_order_min . '-' . number_format($products_discounts_query->fields['discount_qty']-1);
  break;
}
//$discounted_price = $products_discounts_query->fields['discount_price'];
// $currencies->display_price($discounted_price, zen_get_tax_rate(1), 1)

$display_price = zen_get_products_base_price($products_id_current);
$display_specials_price = zen_get_products_special_price($products_id_current, false);
$disc_cnt = 1;
$quantityDiscounts = array();
$columnCount = 0;
while (!$products_discounts_query->EOF) {
  $disc_cnt++;
  switch ($products_discount_type) {
    // none
    case '0':
      $quantityDiscounts[$columnCount]['discounted_price'] = 0;
    break;
    // percentage discount
    case '1':
      if ($products_discount_type_from == '0') {
        $quantityDiscounts[$columnCount]['discounted_price'] = $display_price - ($display_price * ($products_discounts_query->fields['discount_price']/100));
      } else {
        if (!$display_specials_price) {
          $quantityDiscounts[$columnCount]['discounted_price'] = $display_price - ($display_price * ($products_discounts_query->fields['discount_price']/100));
        } else {
          $quantityDiscounts[$columnCount]['discounted_price'] = $display_specials_price - ($display_specials_price * ($products_discounts_query->fields['discount_price']/100));
        }
      }
    break;
    // actual price
    case '2':
      if ($products_discount_type_from == '0') {
        $quantityDiscounts[$columnCount]['discounted_price'] = $products_discounts_query->fields['discount_price'];
      } else {
        $quantityDiscounts[$columnCount]['discounted_price'] = $products_discounts_query->fields['discount_price'];
      }
    break;
    // amount offprice
    case '3':
      if ($products_discount_type_from == '0') {
        $quantityDiscounts[$columnCount]['discounted_price'] = $display_price - $products_discounts_query->fields['discount_price'];
      } else {
        if (!$display_specials_price) {
          $quantityDiscounts[$columnCount]['discounted_price'] = $display_price - $products_discounts_query->fields['discount_price'];
        } else {
          $quantityDiscounts[$columnCount]['discounted_price'] = $display_specials_price - $products_discounts_query->fields['discount_price'];
        }
      }
    break;
  }

  $quantityDiscounts[$columnCount]['show_qty'] = number_format($products_discounts_query->fields['discount_qty']);
  $products_discounts_query->MoveNext();
  if ($products_discounts_query->EOF) {
    $quantityDiscounts[$columnCount]['show_qty'] .= '+';
  } else {
    if (($products_discounts_query->fields['discount_qty']-1) != $show_qty) {
      if ($quantityDiscounts[$columnCount]['show_qty'] < $products_discounts_query->fields['discount_qty']-1) {
        $quantityDiscounts[$columnCount]['show_qty'] .= '-' . number_format($products_discounts_query->fields['discount_qty']-1);
      }
    }
  }
  $disc_cnt=0;
  $columnCount++;
}
