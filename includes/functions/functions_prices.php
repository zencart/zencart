<?php
/**
 * functions_prices
 *
 * @package functions
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: functions_prices.php 18697 2011-05-04 14:35:20Z wilt $
 */

////
//get specials price or sale price
  function zen_get_products_special_price($product_id, $specials_price_only=false) {
    global $db;
    $product = $db->Execute("select products_price, products_model, products_priced_by_attribute from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");

    if ($product->RecordCount() > 0) {
//  	  $product_price = $product->fields['products_price'];
      $product_price = zen_get_products_base_price($product_id);
    } else {
      return false;
    }

    $specials = $db->Execute("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status='1'");
    if ($specials->RecordCount() > 0) {
//      if ($product->fields['products_priced_by_attribute'] == 1) {
        $special_price = $specials->fields['specials_new_products_price'];
    } else {
      $special_price = false;
    }

    if(substr($product->fields['products_model'], 0, 4) == 'GIFT') {    //Never apply a salededuction to Ian Wilson's Giftvouchers
      if (zen_not_null($special_price)) {
        return $special_price;
      } else {
        return false;
      }
    }

// return special price only
    if ($specials_price_only==true) {
      if (zen_not_null($special_price)) {
        return $special_price;
      } else {
        return false;
      }
    } else {
// get sale price

// changed to use master_categories_id
//      $product_to_categories = $db->Execute("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "'");
//      $category = $product_to_categories->fields['categories_id'];

      $product_to_categories = $db->Execute("select master_categories_id from " . TABLE_PRODUCTS . " where products_id = '" . $product_id . "'");
      $category = $product_to_categories->fields['master_categories_id'];

      $sale = $db->Execute("select sale_specials_condition, sale_deduction_value, sale_deduction_type from " . TABLE_SALEMAKER_SALES . " where sale_categories_all like '%," . $category . ",%' and sale_status = '1' and (sale_date_start <= now() or sale_date_start = '0001-01-01') and (sale_date_end >= now() or sale_date_end = '0001-01-01') and (sale_pricerange_from <= '" . $product_price . "' or sale_pricerange_from = '0') and (sale_pricerange_to >= '" . $product_price . "' or sale_pricerange_to = '0')");
      if ($sale->RecordCount() < 1) {
         return $special_price;
      }

      if (!$special_price) {
        $tmp_special_price = $product_price;
      } else {
        $tmp_special_price = $special_price;
      }
      switch ($sale->fields['sale_deduction_type']) {
        case 0:
          $sale_product_price = $product_price - $sale->fields['sale_deduction_value'];
          $sale_special_price = $tmp_special_price - $sale->fields['sale_deduction_value'];
          break;
        case 1:
          $sale_product_price = $product_price - (($product_price * $sale->fields['sale_deduction_value']) / 100);
          $sale_special_price = $tmp_special_price - (($tmp_special_price * $sale->fields['sale_deduction_value']) / 100);
          break;
        case 2:
          $sale_product_price = $sale->fields['sale_deduction_value'];
          $sale_special_price = $sale->fields['sale_deduction_value'];
          break;
        default:
          return $special_price;
      }

      if ($sale_product_price < 0) {
        $sale_product_price = 0;
      }

      if ($sale_special_price < 0) {
        $sale_special_price = 0;
      }

      if (!$special_price) {
        return number_format($sale_product_price, 4, '.', '');
      } else {
        switch($sale->fields['sale_specials_condition']){
          case 0:
            return number_format($sale_product_price, 4, '.', '');
            break;
          case 1:
            return number_format($special_price, 4, '.', '');
            break;
          case 2:
            return number_format($sale_special_price, 4, '.', '');
            break;
          default:
            return number_format($special_price, 4, '.', '');
        }
      }
    }
  }


////
// computes products_price + option groups lowest attributes price of each group when on
  function zen_get_products_base_price($products_id) {
    global $db;
      $product_check = $db->Execute("select products_price, products_priced_by_attribute from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");

// is there a products_price to add to attributes
      $products_price = $product_check->fields['products_price'];

      // do not select display only attributes and attributes_price_base_included is true
      $product_att_query = $db->Execute("select options_id, price_prefix, options_values_price, attributes_display_only, attributes_price_base_included, round(concat(price_prefix, options_values_price), 5) as value from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$products_id . "' and attributes_display_only != '1' and attributes_price_base_included='1'". " order by options_id, value");

      $the_options_id= 'x';
      $the_base_price= 0;
// add attributes price to price
      if ($product_check->fields['products_priced_by_attribute'] == '1' and $product_att_query->RecordCount() >= 1) {
        while (!$product_att_query->EOF) {
          if ( $the_options_id != $product_att_query->fields['options_id']) {
            $the_options_id = $product_att_query->fields['options_id'];
            $the_base_price += (($product_att_query->fields['price_prefix'] == '-') ? -1 : 1) * $product_att_query->fields['options_values_price'];
          }
          $product_att_query->MoveNext();
        }

        $the_base_price = $products_price + $the_base_price;
      } else {
        $the_base_price = $products_price;
      }
      return $the_base_price;
  }


////
// Display Price Retail
// Specials and Tax Included
  function zen_get_products_output_price($products_id) {
    global $db, $currencies;
    $priceinfo = array();

    $free_tag = "";
    $call_tag = "";

    // 0 = normal shopping
    // 1 = Login to shop
    // 2 = Can browse but no prices
    // verify display of prices
    switch (true) {
      case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
        // customer must be logged in to browse
        return '';
        break;
      case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
        // customer may browse but no prices
        return TEXT_LOGIN_FOR_PRICE_PRICE;
        break;
      case (CUSTOMERS_APPROVAL == '3' and TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM != ''):
        // customer may browse but no prices
        return TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM;
        break;
      case ((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customer_id'] == ''):
        // customer must be logged in to browse
        return TEXT_AUTHORIZATION_PENDING_PRICE;
        break;
      case ((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customers_authorization'] > '0'):
        // customer must be logged in to browse
        return TEXT_AUTHORIZATION_PENDING_PRICE;
        break;
      default:
        // proceed normally
        break;
    }

    // show case only
    if (STORE_STATUS != '0') {
      if (STORE_STATUS == '1') {
        return '';
      }
    }

    // $new_fields = ', product_is_free, product_is_call, product_is_showroom_only';
    $product_check = $db->Execute("select products_tax_class_id, products_price, products_priced_by_attribute, product_is_free, product_is_call, products_type from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'" . " limit 1");

    // no prices on Document General
    if ($product_check->fields['products_type'] == 3) {
      return '';
    }

    $show_display_price = $show_sale_discount = '';
    $priceinfo['normal']  = $display_normal_price  = zen_get_products_base_price($products_id);
    $priceinfo['special'] = $display_special_price = zen_get_products_special_price($products_id, true);
    $priceinfo['sale']    = $display_sale_price    = zen_get_products_special_price($products_id, false);

    if (SHOW_SALE_DISCOUNT_STATUS == '1' and ($display_special_price != 0 or $display_sale_price != 0)) {
      if ($display_sale_price) {
        if (SHOW_SALE_DISCOUNT == 1) {
          if ($display_normal_price != 0) {
            $show_discount_amount = number_format(100 - (($display_sale_price / $display_normal_price) * 100),SHOW_SALE_DISCOUNT_DECIMALS);
          } else {
            $show_discount_amount = '';
          }
          $priceinfo['show_discount_amount'] = PRODUCT_PRICE_DISCOUNT_PREFIX . $show_discount_amount . PRODUCT_PRICE_DISCOUNT_PERCENTAGE;
          $show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . $priceinfo['show_discount_amount'] . '</span>';

        } else {
          $priceinfo['show_discount_amount'] = PRODUCT_PRICE_DISCOUNT_PREFIX . $currencies->display_price(($display_normal_price - $display_sale_price), zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . PRODUCT_PRICE_DISCOUNT_AMOUNT;
          $show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . $priceinfo['show_discount_amount'] . '</span>';
        }
      } else {
        if (SHOW_SALE_DISCOUNT == 1) {
          $priceinfo['show_discount_amount'] = PRODUCT_PRICE_DISCOUNT_PREFIX . number_format(100 - (($display_special_price / $display_normal_price) * 100),SHOW_SALE_DISCOUNT_DECIMALS) . PRODUCT_PRICE_DISCOUNT_PERCENTAGE;
        } else {
          $priceinfo['show_discount_amount'] = PRODUCT_PRICE_DISCOUNT_PREFIX . $currencies->display_price(($display_normal_price - $display_special_price), zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . PRODUCT_PRICE_DISCOUNT_AMOUNT;
        }
        $show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . $priceinfo['show_discount_amount'] . '</span>';
      }
    }

    if ($display_special_price) {
      $priceinfo['show_normal_price'] = $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id']));
      $show_normal_price = '<span class="normalprice">' . $priceinfo['show_normal_price'] . ' </span>';
      if ($display_sale_price && $display_sale_price != $display_special_price) {
        $priceinfo['show_special_price_sale'] = $currencies->display_price($display_special_price, zen_get_tax_rate($product_check->fields['products_tax_class_id']));
        $show_special_price = '&nbsp;' . '<span class="productSpecialPriceSale">' . $priceinfo['show_special_price_sale'] . '</span>';
        $priceinfo['show_sale_price'] = PRODUCT_PRICE_SALE . $currencies->display_price($display_sale_price, zen_get_tax_rate($product_check->fields['products_tax_class_id']));
        if ($product_check->fields['product_is_free'] == '1') {
          $priceinfo['show_sale_price'] = PRODUCT_PRICE_SALE . '<s>' . $currencies->display_price($display_sale_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</s>';
        }
        $show_sale_price = '<br />' . '<span class="productSalePrice">' . $priceinfo['show_sale_price'] . '</span>';
      } else {
        $priceinfo['show_special_price'] = $currencies->display_price($display_special_price, zen_get_tax_rate($product_check->fields['products_tax_class_id']));
        if ($product_check->fields['product_is_free'] == '1') {
          $priceinfo['show_special_price'] = '<s>' . $priceinfo['show_special_price'] . '</s>';
        }
        $show_special_price = '&nbsp;' . '<span class="productSpecialPrice">' . $priceinfo['show_special_price']  . '</span>';
        $show_sale_price = $priceinfo['show_sale_price'] = '';
      }
    } else {
      if ($display_sale_price) {
        $priceinfo['show_normal_price'] = $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id']));
        $priceinfo['show_special_price'] = '';
        $priceinfo['show_sale_price'] = PRODUCT_PRICE_SALE . $currencies->display_price($display_sale_price, zen_get_tax_rate($product_check->fields['products_tax_class_id']));
        $show_normal_price = '<span class="normalprice">' . $priceinfo['show_normal_price'] . ' </span>';
        $show_special_price = $priceinfo['show_special_price'];
        $show_sale_price = '<br />' . '<span class="productSalePrice">' . $priceinfo['show_sale_price'] . '</span>';
      } else {
        $priceinfo['show_normal_price'] = $show_normal_price = $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id']));
        if ($product_check->fields['product_is_free'] == '1') {
          $priceinfo['show_normal_price'] = $show_normal_price = '<s>' . $priceinfo['show_normal_price'] . '</s>';
        }
        $priceinfo['show_special_price'] = $show_special_price = '';
        $priceinfo['show_sale_price'] = $show_sale_price = '';
      }
    }

    if ($display_normal_price == 0) {
      // don't show the $0.00
      $final_display_price = $show_special_price . $show_sale_price . $show_sale_discount;
      $priceinfo['show_normal_price'] = '';
    } else {
      $final_display_price = $show_normal_price . $show_special_price . $show_sale_price . $show_sale_discount;
    }
    $priceinfo['final_display_price'] = $final_display_price;

    // If Free, Show it
    if ($product_check->fields['product_is_free'] == '1') {
      if (OTHER_IMAGE_PRICE_IS_FREE_ON=='0') {
        $priceinfo['free_tag'] = PRODUCTS_PRICE_IS_FREE_TEXT;
        $free_tag = '<br />' . $priceinfo['free_tag'];
      } else {
        $priceinfo['free_tag'] = zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_PRICE_IS_FREE, PRODUCTS_PRICE_IS_FREE_TEXT);
        $free_tag = '<br />' . $priceinfo['free_tag'];
      }
    }

    // If Call for Price, Show it
    if ($product_check->fields['product_is_call']) {
      if (PRODUCTS_PRICE_IS_CALL_IMAGE_ON=='0') {
//         $priceinfo['call_tag'] = PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT;
        $priceinfo['call_tag'] = '<a class="btn-callforprice" href="' . zen_href_link(FILENAME_CONTACT_US, '', 'SSL') . '">' . PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT . '</a>';
        $call_tag = '<br />' . $priceinfo['call_tag'];
      } else {
        $priceinfo['call_tag'] = zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_CALL_FOR_PRICE, PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT);
        $call_tag = '<br />' . $priceinfo['call_tag'];
      }
    }

    $priceinfo['legacy_output'] = $final_display_price . $free_tag . $call_tag;
    return $priceinfo;
  }

////
// Display Price Retail
// Specials and Tax Included
  function zen_get_products_display_price($products_id) {
    $val = zen_get_products_output_price($products_id);
    if (is_array($val) && isset($val['legacy_output'])) $val = $val['legacy_output'];
    return $val;
  }

////
// Is the product free?
  function zen_get_products_price_is_free($products_id) {
    global $db;
    $product_check = $db->Execute("select product_is_free from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'" . " limit 1");
    if ($product_check->fields['product_is_free'] == '1') {
      $the_free_price = true;
    } else {
      $the_free_price = false;
    }
    return $the_free_price;
  }

////
// Is the product call for price?
  function zen_get_products_price_is_call($products_id) {
    global $db;
    $product_check = $db->Execute("select product_is_call from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'" . " limit 1");
    if ($product_check->fields['product_is_call'] == '1') {
      $the_call_price = true;
    } else {
      $the_call_price = false;
    }
    return $the_call_price;
  }

////
// Is the product priced by attributes?
  function zen_get_products_price_is_priced_by_attributes($products_id) {
    global $db;
    $product_check = $db->Execute("select products_priced_by_attribute from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'" . " limit 1");
    if ($product_check->fields['products_priced_by_attribute'] == '1') {
      $the_products_priced_by_attribute = true;
    } else {
      $the_products_priced_by_attribute = false;
    }
    return $the_products_priced_by_attribute;
  }

////
// Return a product's minimum quantity
// TABLES: products
  function zen_get_products_quantity_order_min($product_id) {
    global $db;

    $the_products_quantity_order_min = $db->Execute("select products_id, products_quantity_order_min from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
    return $the_products_quantity_order_min->fields['products_quantity_order_min'];
  }


////
// Return a product's minimum unit order
// TABLES: products
  function zen_get_products_quantity_order_units($product_id) {
    global $db;

    $the_products_quantity_order_units = $db->Execute("select products_id, products_quantity_order_units from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
    return $the_products_quantity_order_units->fields['products_quantity_order_units'];
  }

////
// Return a product's maximum quantity
// TABLES: products
  function zen_get_products_quantity_order_max($product_id) {
    global $db;

    $the_products_quantity_order_max = $db->Execute("select products_id, products_quantity_order_max from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
    return $the_products_quantity_order_max->fields['products_quantity_order_max'];
  }

////
// Return a product's quantity box status
// TABLES: products
  function zen_get_products_qty_box_status($product_id) {
    global $db;

    $the_products_qty_box_status = $db->Execute("select products_id, products_qty_box_status  from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
    return $the_products_qty_box_status->fields['products_qty_box_status'];
  }

////
// Return a product mixed setting
// TABLES: products
  function zen_get_products_quantity_mixed($product_id) {
    global $db;

// don't check for mixed if not attributes
    $chk_attrib = zen_has_product_attributes((int)$product_id);
    if ($chk_attrib == true) {
      $the_products_quantity_mixed = $db->Execute("select products_id, products_quantity_mixed from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
      if ($the_products_quantity_mixed->fields['products_quantity_mixed'] == '1') {
        $look_up = true;
      } else {
        $look_up = false;
      }
    } else {
      $look_up = 'none';
    }

    return $look_up;
  }


////
// Return a products quantity minimum and units display
  function zen_get_products_quantity_min_units_display($product_id, $include_break = true, $shopping_cart_msg = false) {
    $check_min = zen_get_products_quantity_order_min($product_id);
    $check_units = zen_get_products_quantity_order_units($product_id);

    $the_min_units='';

    if ($check_min != 1 or $check_units != 1) {
      if ($check_min != 1) {
        $the_min_units .= PRODUCTS_QUANTITY_MIN_TEXT_LISTING . '&nbsp;' . $check_min;
      }
      if ($check_units != 1) {
        $the_min_units .= ($the_min_units ? ' ' : '' ) . PRODUCTS_QUANTITY_UNIT_TEXT_LISTING . '&nbsp;' . $check_units;
      }

// don't check for mixed if not attributes
      $chk_mix = zen_get_products_quantity_mixed((int)$product_id);
      if ($chk_mix != 'none') {
        if (($check_min > 0 or $check_units > 0)) {
          if ($include_break == true) {
            $the_min_units .= '<br />' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_OFF : TEXT_PRODUCTS_MIX_OFF_SHOPPING_CART);
          } else {
            $the_min_units .= '&nbsp;&nbsp;' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_OFF : TEXT_PRODUCTS_MIX_OFF_SHOPPING_CART);
          }
        } else {
          if ($include_break == true) {
            $the_min_units .= '<br />' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_ON : TEXT_PRODUCTS_MIX_ON_SHOPPING_CART);
          } else {
            $the_min_units .= '&nbsp;&nbsp;' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_ON : TEXT_PRODUCTS_MIX_ON_SHOPPING_CART);
          }
        }
      }
    }

    // quantity max
    $check_max = zen_get_products_quantity_order_max($product_id);

    if ($check_max != 0) {
      if ($include_break == true) {
        $the_min_units .= ($the_min_units != '' ? '<br />' : '') . PRODUCTS_QUANTITY_MAX_TEXT_LISTING . '&nbsp;' . $check_max;
      } else {
        $the_min_units .= ($the_min_units != '' ? '&nbsp;&nbsp;' : '') . PRODUCTS_QUANTITY_MAX_TEXT_LISTING . '&nbsp;' . $check_max;
      }
    }

    return $the_min_units;
  }


////
// Return quantity buy now
  function zen_get_buy_now_qty($product_id) {
    $check_min = zen_get_products_quantity_order_min($product_id);
    $check_units = zen_get_products_quantity_order_units($product_id);
    $buy_now_qty=1;
// works on Mixed ON
    switch (true) {
      case ($_SESSION['cart']->in_cart_mixed($product_id) == 0 ):
        if ($check_min >= $check_units) {
          $buy_now_qty = $check_min;
        } else {
          $buy_now_qty = $check_units;
        }
        break;
      case ($_SESSION['cart']->in_cart_mixed($product_id) < $check_min):
        $buy_now_qty = $check_min - $_SESSION['cart']->in_cart_mixed($product_id);
        break;
      case ($_SESSION['cart']->in_cart_mixed($product_id) > $check_min):
      // set to units or difference in units to balance cart
        $new_units = $check_units - fmod_round($_SESSION['cart']->in_cart_mixed($product_id), $check_units);
//echo 'Cart: ' . $_SESSION['cart']->in_cart_mixed($product_id) . ' Min: ' . $check_min . ' Units: ' . $check_units . ' fmod: ' . fmod($_SESSION['cart']->in_cart_mixed($product_id), $check_units) . '<br />';
        $buy_now_qty = ($new_units > 0 ? $new_units : $check_units);
        break;
      default:
        $buy_now_qty = $check_units;
        break;
    }
    if ($buy_now_qty <= 0) {
      $buy_now_qty = 1;
    }
    return $buy_now_qty;
  }


////
// compute product discount to be applied to attributes or other values
  function zen_get_discount_calc($product_id, $attributes_id = false, $attributes_amount = false, $check_qty= false) {
    global $discount_type_id, $sale_maker_discount;

    // no charge
    if ($attributes_id > 0 and $attributes_amount == 0) {
      return 0;
    }

    $new_products_price = zen_get_products_base_price($product_id);
    $new_special_price = zen_get_products_special_price($product_id, true);
    $new_sale_price = zen_get_products_special_price($product_id, false);

    $discount_type_id = zen_get_products_sale_discount_type($product_id);

    if ($new_products_price != 0) {
      $special_price_discount = ($new_special_price != 0 ? ($new_special_price/$new_products_price) : 1);
    } else {
      $special_price_discount = '';
    }
    $sale_maker_discount = zen_get_products_sale_discount_type($product_id, '', 'amount');

    // percentage adjustment of discount
    if (($discount_type_id == 120 or $discount_type_id == 1209) or ($discount_type_id == 110 or $discount_type_id == 1109)) {
      $sale_maker_discount = ($sale_maker_discount != 0 ? (100 - $sale_maker_discount)/100 : 1);
    }

   $qty = $check_qty;

// fix here
// BOF: percentage discounts apply to price
    switch (true) {
      case (zen_get_discount_qty($product_id, $qty) and !$attributes_id):
        // discount quanties exist and this is not an attribute
        // $this->contents[$products_id]['qty']
        $check_discount_qty_price = zen_get_products_discount_price_qty($product_id, $qty, $attributes_amount);
//echo 'How much 1 ' . $qty . ' : ' . $attributes_amount . ' vs ' . $check_discount_qty_price . '<br />';
        return $check_discount_qty_price;
        break;

      case (zen_get_discount_qty($product_id, $qty) and zen_get_products_price_is_priced_by_attributes($product_id)):
        // discount quanties exist and this is not an attribute
        // $this->contents[$products_id]['qty']
        $check_discount_qty_price = zen_get_products_discount_price_qty($product_id, $qty, $attributes_amount);
//echo 'How much 2 ' . $qty . ' : ' . $attributes_amount . ' vs ' . $check_discount_qty_price . '<br />';

        return $check_discount_qty_price;
        break;

      case ($discount_type_id == 5):
        // No Sale and No Special
//        $sale_maker_discount = 1;
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            if ($special_price_discount != 0) {
              $calc = ($attributes_amount * $special_price_discount);
            } else {
              $calc = $attributes_amount;
            }

            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
//echo 'How much 3 - ' . $qty . ' : ' . $product_id . ' : ' . $qty . ' x ' .  $attributes_amount . ' vs ' . $check_discount_qty_price . ' - ' . $sale_maker_discount . '<br />';
        break;
      case ($discount_type_id == 59):
        // No Sale and Special
//        $sale_maker_discount = $special_price_discount;
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: percentage discount apply to price

// BOF: percentage discounts apply to Sale
      case ($discount_type_id == 120):
        // percentage discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 1209):
        // percentage discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $calc2 = $calc - ($calc * $sale_maker_discount);
            $sale_maker_discount = $calc - $calc2;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: percentage discounts apply to Sale

// BOF: percentage discounts skip specials
      case ($discount_type_id == 110):
        // percentage discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
//            $sale_maker_discount = $sale_maker_discount;
            if ($attributes_amount != 0) {
//            $calc = ($attributes_amount * $special_price_discount);
//            $calc2 = $calc - ($calc * $sale_maker_discount);
//            $sale_maker_discount = $calc - $calc2;
              $calc = $attributes_amount - ($attributes_amount * $sale_maker_discount);
              $sale_maker_discount = $calc;
            } else {
              $sale_maker_discount = $sale_maker_discount;
            }
          }
        }
        break;
      case ($discount_type_id == 1109):
        // percentage discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
//            $calc2 = $calc - ($calc * $sale_maker_discount);
//            $sale_maker_discount = $calc - $calc2;
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: percentage discounts skip specials

// BOF: flat amount discounts
      case ($discount_type_id == 20):
        // flat amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount - $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 209):
        // flat amount discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $calc2 = ($calc - $sale_maker_discount);
            $sale_maker_discount = $calc2;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: flat amount discounts

// BOF: flat amount discounts Skip Special
      case ($discount_type_id == 10):
        // flat amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount - $sale_maker_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 109):
        // flat amount discount on Sale and Special with a special
        if (!$attributes_id) {
          $sale_maker_discount = 1;
        } else {
          // compute attribute amount based on Special
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: flat amount discounts Skip Special

// BOF: New Price amount discounts
      case ($discount_type_id == 220):
        // New Price amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 2209):
        // New Price amount discount on Sale and Special with a special
        if (!$attributes_id) {
//          $sale_maker_discount = $sale_maker_discount;
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: New Price amount discounts

// BOF: New Price amount discounts - Skip Special
      case ($discount_type_id == 210):
        // New Price amount discount Sale and Special without a special
        if (!$attributes_id) {
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
            $sale_maker_discount = $calc;
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
      case ($discount_type_id == 2109):
        // New Price amount discount on Sale and Special with a special
        if (!$attributes_id) {
//          $sale_maker_discount = $sale_maker_discount;
          $sale_maker_discount = $sale_maker_discount;
        } else {
          // compute attribute amount
          if ($attributes_amount != 0) {
            $calc = ($attributes_amount * $special_price_discount);
//echo '<br />attr ' . $attributes_amount . ' spec ' . $special_price_discount . ' Calc ' . $calc . 'Calc2 ' . $calc2 . '<br />';
            $sale_maker_discount = $calc;
          } else {
            $sale_maker_discount = $sale_maker_discount;
          }
        }
        break;
// EOF: New Price amount discounts - Skip Special

      case ($discount_type_id == 0 or $discount_type_id == 9):
      // flat discount
        return $sale_maker_discount;
        break;
      default:
        $sale_maker_discount = 7000;
        break;
    }

    return $sale_maker_discount;
  }

////
// look up discount in sale makers - attributes only can have discounts if set as percentages
// this gets the discount amount this does not determin when to apply the discount
  function zen_get_products_sale_discount_type($product_id = false, $categories_id = false, $return_value = false) {
    global $currencies;
    global $db;

/*

0 = flat amount off base price with a special
1 = Percentage off base price with a special
2 = New Price with a special

5 = No Sale or Skip Products with Special

special options + option * 10
0 = Ignore special and apply to Price
1 = Skip Products with Specials switch to 5
2 = Apply to Special Price

If a special exist * 10+9

0*100 + 0*10 = flat apply to price = 0 or 9
0*100 + 1*10 = flat skip Specials = 5 or 59
0*100 + 2*10 = flat apply to special = 20 or 209

1*100 + 0*10 = Percentage apply to price = 100 or 1009
1*100 + 1*10 = Percentage skip Specials = 110 or 1109 / 5 or 59
1*100 + 2*10 = Percentage apply to special = 120 or 1209

2*100 + 0*10 = New Price apply to price = 200 or 2009
2*100 + 1*10 = New Price skip Specials = 210 or 2109 / 5 or 59
2*100 + 2*10 = New Price apply to Special = 220 or 2209

*/

// get products category
    if ($categories_id == true) {
      $check_category = $categories_id;
    } else {
      $check_category = zen_get_products_category_id($product_id);
    }
/*
    $deduction_type_array = array(array('id' => '0', 'text' => DEDUCTION_TYPE_DROPDOWN_0),
                                  array('id' => '1', 'text' => DEDUCTION_TYPE_DROPDOWN_1),
                                  array('id' => '2', 'text' => DEDUCTION_TYPE_DROPDOWN_2));
*/
    $sale_exists = 'false';
    $sale_maker_discount = '';
    $sale_maker_special_condition = '';
    $salemaker_sales = $db->Execute("select sale_id, sale_status, sale_name, sale_categories_all, sale_deduction_value, sale_deduction_type, sale_pricerange_from, sale_pricerange_to, sale_specials_condition, sale_categories_selected, sale_date_start, sale_date_end, sale_date_added, sale_date_last_modified, sale_date_status_change from " . TABLE_SALEMAKER_SALES . " where sale_status='1'");
    while (!$salemaker_sales->EOF) {
      $categories = explode(',', $salemaker_sales->fields['sale_categories_all']);
      while (list($key,$value) = each($categories)) {
        if ($value == $check_category) {
          $sale_exists = 'true';
          $sale_maker_discount = $salemaker_sales->fields['sale_deduction_value'];
          $sale_maker_special_condition = $salemaker_sales->fields['sale_specials_condition'];
          $sale_maker_discount_type = $salemaker_sales->fields['sale_deduction_type'];
          break;
        }
      }
      $salemaker_sales->MoveNext();
    }

    $check_special = zen_get_products_special_price($product_id, true);

    if ($sale_exists == 'true' and $sale_maker_special_condition != 0) {
      $sale_maker_discount_type = (($sale_maker_discount_type * 100) + ($sale_maker_special_condition * 10));
    } else {
      $sale_maker_discount_type = 5;
    }

    if (!$check_special) {
      // do nothing
    } else {
      $sale_maker_discount_type = ($sale_maker_discount_type * 10) + 9;
    }

    switch (true) {
      case (!$return_value):
        return $sale_maker_discount_type;
        break;
      case ($return_value == 'amount'):
        return $sale_maker_discount;
        break;
      default:
        return 'Unknown Request';
        break;
    }
  }

////
// look up discount in sale makers - attributes only can have discounts if set as percentages
// this gets the discount amount this does not determin when to apply the discount
  function zen_get_products_sale_discount($product_id = false, $categories_id = false, $display_type = false) {
    global $currencies;
    global $db;

// NOT USED
echo '<br />' . 'I SHOULD use zen_get_discount_calc' . '<br />';

/*

0 = flat amount off base price with a special
1 = Percentage off base price with a special
2 = New Price with a special

5 = No Sale or Skip Products with Special

special options + option * 10
0 = Ignore special and apply to Price
1 = Skip Products with Specials switch to 5
2 = Apply to Special Price

If a special exist * 10

0+7 + 0+10 = flat apply to price = 17 or 170
0+7 + 1+10 = flat skip Specials = 5 or 50
0+7 + 2+10 = flat apply to special = 27 or 270

1+7 + 0+10 = Percentage apply to price = 18 or 180
1+7 + 1+10 = Percentage skip Specials = 5 or 50
1+7 + 2+10 = Percentage apply to special = 20 or 200

2+7 + 0+10 = New Price apply to price = 19 or 190
2+7 + 1+10 = New Price skip Specials = 5 or 50
2+7 + 2+10 = New Price apply to Special = 21 or 210

*/

/*
// get products category
    if ($categories_id == true) {
      $check_category = $categories_id;
    } else {
      $check_category = zen_get_products_category_id($product_id);
    }

    $deduction_type_array = array(array('id' => '0', 'text' => DEDUCTION_TYPE_DROPDOWN_0),
                                  array('id' => '1', 'text' => DEDUCTION_TYPE_DROPDOWN_1),
                                  array('id' => '2', 'text' => DEDUCTION_TYPE_DROPDOWN_2));

    $sale_maker_discount = 0;
    $salemaker_sales = $db->Execute("select sale_id, sale_status, sale_name, sale_categories_all, sale_deduction_value, sale_deduction_type, sale_pricerange_from, sale_pricerange_to, sale_specials_condition, sale_categories_selected, sale_date_start, sale_date_end, sale_date_added, sale_date_last_modified, sale_date_status_change from " . TABLE_SALEMAKER_SALES . " where sale_status='1'");
    while (!$salemaker_sales->EOF) {
      $categories = explode(',', $salemaker_sales->fields['sale_categories_all']);
      while (list($key,$value) = each($categories)) {
        if ($value == $check_category) {
          $sale_maker_discount = $salemaker_sales->fields['sale_deduction_value'];
          $sale_maker_discount_type = $salemaker_sales->fields['sale_deduction_type'];
          break;
        }
      }
      $salemaker_sales->MoveNext();
    }

    switch(true) {
      // percentage discount only
      case ($sale_maker_discount_type == 1):
        $sale_maker_discount = (1 - ($sale_maker_discount / 100));
        break;
      case ($sale_maker_discount_type == 0 and $display_type == true):
        $sale_maker_discount = $sale_maker_discount;
        break;
      case ($sale_maker_discount_type == 0 and $display_type == false):
        $sale_maker_discount = $sale_maker_discount;
        break;
      case ($sale_maker_discount_type == 2 and $display_type == true):
        $sale_maker_discount = $sale_maker_discount;
        break;
      default:
        $sale_maker_discount = 1;
        break;
    }

    if ($display_type == true) {
      if ($sale_maker_discount != 1 and $sale_maker_discount !=0) {
        switch(true) {
          case ($sale_maker_discount_type == 0):
            $sale_maker_discount = $currencies->format($sale_maker_discount) . ' ' . $deduction_type_array[$sale_maker_discount_type]['text'];
            break;
          case ($sale_maker_discount_type == 2):
            $sale_maker_discount = $currencies->format($sale_maker_discount) . ' ' . $deduction_type_array[$sale_maker_discount_type]['text'];
            break;
          case ($sale_maker_discount_type == 1):
            $sale_maker_discount = number_format( (1.00 - $sale_maker_discount),2,".","") . ' ' . $deduction_type_array[$sale_maker_discount_type]['text'];
            break;
        }
      } else {
        $sale_maker_discount = '';
      }
    }
    return $sale_maker_discount;
*/

  }

////
// Actual Price Retail
// Specials and Tax Included
  function zen_get_products_actual_price($products_id) {
    global $db, $currencies;
    $product_check = $db->Execute("select products_tax_class_id, products_price, products_priced_by_attribute, product_is_free, product_is_call from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'" . " limit 1");

    $show_display_price = '';
    $display_normal_price = zen_get_products_base_price($products_id);
    $display_special_price = zen_get_products_special_price($products_id, true);
    $display_sale_price = zen_get_products_special_price($products_id, false);

    $products_actual_price = $display_normal_price;

    if ($display_special_price) {
      $products_actual_price = $display_special_price;
    }
    if ($display_sale_price) {
      $products_actual_price = $display_sale_price;
    }

    // If Free, Show it
    if ($product_check->fields['product_is_free'] == '1') {
      $products_actual_price = 0;
    }

    return $products_actual_price;
  }

////
// return attributes_price_factor
  function zen_get_attributes_price_factor($price, $special, $factor, $offset) {
    if (ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL =='1' and $special) {
      // calculate from specials_new_products_price
      $calculated_price = $special * ($factor - $offset);
    } else {
      // calculate from products_price
      $calculated_price = $price * ($factor - $offset);
    }
//    return '$price ' . $price . ' $special ' . $special . ' $factor ' . $factor . ' $offset ' . $offset;
    return $calculated_price;
  }


////
// return attributes_qty_prices or attributes_qty_prices_onetime based on qty
  function zen_get_attributes_qty_prices_onetime($string, $qty) {
    $attribute_qty = preg_split("/[:,]/" , $string);
    $new_price = 0;
    $size = sizeof($attribute_qty);
// if an empty string is passed then $attributes_qty will consist of a 1 element array
    if ($size > 1) {
      for ($i=0, $n=$size; $i<$n; $i+=2) {
        $new_price = $attribute_qty[$i+1];
        if ($qty <= $attribute_qty[$i]) {
          $new_price = $attribute_qty[$i+1];
          break;
        }
      }
    }
    return $new_price;
  }


////
// Check specific attributes_qty_prices or attributes_qty_prices_onetime for a given quantity price
  function zen_get_attributes_quantity_price($check_what, $check_for) {
// $check_what='1:3.00,5:2.50,10:2.25,20:2.00';
// $check_for=50;
      $attribute_table_cost = preg_split("/[:,]/" , $check_what);
      $size = sizeof($attribute_table_cost);
      for ($i=0, $n=$size; $i<$n; $i+=2) {
        if ($check_for >= $attribute_table_cost[$i]) {
          $attribute_quantity_check = $attribute_table_cost[$i];
          $attribute_quantity_price = $attribute_table_cost[$i+1];
        }
      }
//          echo '<br>Cost ' . $check_for . ' - '  .  $attribute_quantity_check . ' x ' . $attribute_quantity_price;
     return $attribute_quantity_price;
  }


////
// attributes final price
  function zen_get_attributes_price_final($attribute, $qty = 1, $pre_selected, $include_onetime = 'false') {
    global $db;

    $attributes_price_final = 0;

    if ($pre_selected == '' or $attribute != $pre_selected->fields["products_attributes_id"]) {
      $pre_selected = $db->Execute("select pa.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_attributes_id= '" . (int)$attribute . "'");
    } else {
      // use existing select
    }

    // normal attributes price
    if ($pre_selected->fields["price_prefix"] == '-') {
      $attributes_price_final -= $pre_selected->fields["options_values_price"];
    } else {
      $attributes_price_final += $pre_selected->fields["options_values_price"];
    }
    // qty discounts
    $attributes_price_final += zen_get_attributes_qty_prices_onetime($pre_selected->fields["attributes_qty_prices"], $qty);

    // price factor
    $display_normal_price = zen_get_products_actual_price($pre_selected->fields["products_id"]);
    $display_special_price = zen_get_products_special_price($pre_selected->fields["products_id"]);

    $attributes_price_final += zen_get_attributes_price_factor($display_normal_price, $display_special_price, $pre_selected->fields["attributes_price_factor"], $pre_selected->fields["attributes_price_factor_offset"]);

    // per word and letter charges
    if (zen_get_attributes_type($attribute) == PRODUCTS_OPTIONS_TYPE_TEXT) {
      // calc per word or per letter
    }

// onetime charges
    if ($include_onetime == 'true') {
      $pre_selected_onetime = $pre_selected;
      $attributes_price_final += zen_get_attributes_price_final_onetime($pre_selected->fields["products_attributes_id"], 1, $pre_selected_onetime);
    }

    return $attributes_price_final;
  }


////
// attributes final price onetime
  function zen_get_attributes_price_final_onetime($attribute, $qty= 1, $pre_selected_onetime) {
    global $db;

    if ($pre_selected_onetime == '' or $attribute != $pre_selected_onetime->fields["products_attributes_id"]) {
      $pre_selected_onetime = $db->Execute("select pa.* from " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_attributes_id= '" . (int)$attribute . "'");
    } else {
      // use existing select
    }

// one time charges
    // onetime charge
      $attributes_price_final_onetime += $pre_selected_onetime->fields["attributes_price_onetime"];

    // price factor
    $display_normal_price = zen_get_products_actual_price($pre_selected_onetime->fields["products_id"]);
    $display_special_price = zen_get_products_special_price($pre_selected_onetime->fields["products_id"]);

    // price factor one time
      $attributes_price_final_onetime += zen_get_attributes_price_factor($display_normal_price, $display_special_price, $pre_selected_onetime->fields["attributes_price_factor_onetime"], $pre_selected_onetime->fields["attributes_price_factor_onetime_offset"]);

    // onetime charge qty price
      $attributes_price_final_onetime += zen_get_attributes_qty_prices_onetime($pre_selected_onetime->fields["attributes_qty_prices_onetime"], 1);

      return $attributes_price_final_onetime;
    }


////
// get attributes type
  function zen_get_attributes_type($check_attribute) {
    global $db;
    $check_options_id_query = $db->Execute("select options_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id='" . (int)$check_attribute . "'");
    $check_type_query = $db->Execute("select products_options_type from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id='" . (int)$check_options_id_query->fields['options_id'] . "'");
    return $check_type_query->fields['products_options_type'];
  }


////
// calculate words
  function zen_get_word_count($string, $free=0) {
    $string = str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $string);

    if ($string != '') {
      while (strstr($string, '  ')) $string = str_replace('  ', ' ', $string);
      $string = trim($string);
      $word_count = substr_count($string, ' ');
      return (($word_count+1) - $free);
    } else {
      // nothing to count
      return 0;
    }
  }


////
// calculate words price
  function zen_get_word_count_price($string, $free=0, $price) {

    $word_count = zen_get_word_count($string, $free);
    if ($word_count >= 1) {
      return ($word_count * $price);
    } else {
      return 0;
    }
  }


////
// calculate letters
  function zen_get_letters_count($string, $free=0) {
    $string = str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $string);

    while (strstr($string, '  ')) $string = str_replace('  ', ' ', $string);
    $string = trim($string);
    if (TEXT_SPACES_FREE == '1') {
      $letters_count = strlen(str_replace(' ', '', $string));
    } else {
      $letters_count = strlen($string);
    }
    if ($letters_count - $free >= 1) {
      return ($letters_count - $free);
    } else {
      return 0;
    }
  }


////
// calculate letters price
  function zen_get_letters_count_price($string, $free=0, $price) {

    $letters_price = zen_get_letters_count($string, $free) * $price;
    if ($letters_price <= 0) {
      return 0;
    } else {
      return $letters_price;
    }
  }


////
// compute discount based on qty
  function zen_get_products_discount_price_qty($product_id, $check_qty, $check_amount=0) {
    global $db;
      $new_qty = $_SESSION['cart']->in_cart_mixed_discount_quantity($product_id);
      // check for discount qty mix
      if ($new_qty > $check_qty) {
        $check_qty = $new_qty;
      }
      $product_id = (int)$product_id;
      $products_query = $db->Execute("select products_discount_type, products_discount_type_from, products_priced_by_attribute from " . TABLE_PRODUCTS . " where products_id='" . (int)$product_id . "'");
      $products_discounts_query = $db->Execute("select * from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where products_id='" . (int)$product_id . "' and discount_qty <='" . (float)$check_qty . "' order by discount_qty desc");

      $display_price = zen_get_products_base_price($product_id);
      $display_specials_price = zen_get_products_special_price($product_id, true);

      switch ($products_query->fields['products_discount_type']) {
        // none
        case ($products_discounts_query->EOF):
          //no discount applies
          $discounted_price = zen_get_products_actual_price($product_id);
          break;
        case '0':
          $discounted_price = zen_get_products_actual_price($product_id);
          break;
        // percentage discount
        case '1':
          if ($products_query->fields['products_discount_type_from'] == '0') {
            // priced by attributes
            if ($check_amount != 0) {
              $discounted_price = $check_amount - ($check_amount * ($products_discounts_query->fields['discount_price']/100));
//echo 'ID#' . $product_id . ' Amount is: ' . $check_amount . ' discount: ' . $discounted_price . '<br />';
//echo 'I SEE 2 for ' . $products_query->fields['products_discount_type'] . ' - ' . $products_query->fields['products_discount_type_from'] . ' - '. $check_amount . ' new: ' . $discounted_price . ' qty: ' . $check_qty;
            } else {
              $discounted_price = $display_price - ($display_price * ($products_discounts_query->fields['discount_price']/100));
            }
          } else {
            if (!$display_specials_price) {
              // priced by attributes
              if ($check_amount != 0) {
                $discounted_price = $check_amount - ($check_amount * ($products_discounts_query->fields['discount_price']/100));
              } else {
                $discounted_price = $display_price - ($display_price * ($products_discounts_query->fields['discount_price']/100));
              }
            } else {
              $discounted_price = $display_specials_price - ($display_specials_price * ($products_discounts_query->fields['discount_price']/100));
            }
          }

          break;
        // actual price
        case '2':
          if ($products_query->fields['products_discount_type_from'] == '0') {
            $discounted_price = $products_discounts_query->fields['discount_price'];
          } else {
            $discounted_price = $products_discounts_query->fields['discount_price'];
          }
          break;
        // amount offprice
        case '3':
          if ($products_query->fields['products_discount_type_from'] == '0') {
            $discounted_price = $display_price - $products_discounts_query->fields['discount_price'];
          } else {
            if (!$display_specials_price) {
              $discounted_price = $display_price - $products_discounts_query->fields['discount_price'];
            } else {
              $discounted_price = $display_specials_price - $products_discounts_query->fields['discount_price'];
            }
          }
          break;
      }

      return $discounted_price;
  }


////
// are there discount quantities
  function zen_get_discount_qty($product_id, $check_qty) {
    global $db;

    $product_id = (int)$product_id;

    $discounts_qty_query = $db->Execute("select pqd.*, p.products_discount_type
              from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " pqd, " .
              TABLE_PRODUCTS . " p
             where pqd.products_id='" . (int)$product_id . "' and pqd.discount_qty != 0
             and p.products_id = pqd.products_id");
//echo 'zen_get_discount_qty: ' . $product_id . ' - ' . $check_qty . '<br />';
    if ($discounts_qty_query->RecordCount() > 0 and $check_qty > 0 && $discounts_qty_query->fields['products_discount_type'] !=0) {
      return true;
    } else {
      return false;
    }
  }

////
// set the products_price_sorter
  function zen_update_products_price_sorter($product_id) {
    global $db;

    $products_price_sorter = zen_get_products_actual_price($product_id);

    $db->Execute("update " . TABLE_PRODUCTS . " set
                  products_price_sorter='" . zen_db_prepare_input($products_price_sorter) . "'
                  where products_id='" . (int)$product_id . "'");
  }

////
// salemaker categories array
  function zen_parse_salemaker_categories($clist) {
    $clist_array = explode(',', $clist);

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($clist_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($clist_array[$i], $tmp_array)) {
        $tmp_array[] = $clist_array[$i];
      }
    }
    return $tmp_array;
  }

////
// update salemaker product prices per category per product
  function zen_update_salemaker_product_prices($salemaker_id) {
    global $db;
    $zv_categories = $db->Execute("select sale_categories_selected from " . TABLE_SALEMAKER_SALES . " where sale_id = '" . (int)$salemaker_id . "'");

    $za_salemaker_categories = zen_parse_salemaker_categories($zv_categories->fields['sale_categories_selected']);
    $n = sizeof($za_salemaker_categories);
    for ($i=0; $i<$n; $i++) {
      $update_products_price = $db->Execute("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id='" . (int)$za_salemaker_categories[$i] . "'");
      while (!$update_products_price->EOF) {
        zen_update_products_price_sorter($update_products_price->fields['products_id']);
        $update_products_price->MoveNext();
      }
    }
  }

