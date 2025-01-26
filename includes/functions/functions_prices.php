<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 26 Modified in v2.1.0-beta1 $
 */

/**
 * get specials price or sale price
 * @param int $product_id
 * @param bool $specials_price_only
 * @return bool|string the price or false
 */
function zen_get_products_special_price($product_id, $specials_price_only = false)
{
    global $db;
    $product = zen_get_product_details($product_id);

    if ($product->EOF || Customer::isWholesaleCustomer() === true) {
        return false;
    }
    $product_price = zen_get_products_base_price($product_id);

    $specials = $db->Execute("SELECT specials_new_products_price FROM " . TABLE_SPECIALS . " WHERE products_id = " . (int)$product_id . " AND status = 1", 1);
    if (!$specials->EOF) {
        $special_price = $specials->fields['specials_new_products_price'];
    } else {
        $special_price = false;
    }

    if (strpos(($product->fields['products_model'] ?? ''), 'GIFT') === 0) {    //Never apply a salededuction to Ian Wilson's Giftvouchers
        if (!empty($special_price)) {
            return $special_price;
        }
        return false;
    }

    // return special price only
    if ($specials_price_only == true) {
        if (!empty($special_price)) {
            return $special_price;
        }
        return false;
    }

    // get/determine sale price
    $category = $product->fields['master_categories_id'];
    $sale = zen_get_sale_for_category_and_price($category, $product_price);

    // -----
    // Give an observer the opportunity to add functionality for the salemaker sales,
    // perhaps enabling linked products to be included.
    //
    // If the 'sale' price is to be overridden, an observer sets the $sale variable
    // to an associative array with keys sale_specials_condition, sale_deduction_value and
    // sale_deduction_type to match the format returned from a query of the
    // salemaker_sales database table.
    //
    // If the observer wishes to negate a sale of the current product, the observer
    // sets the $sale to contain (bool)false.
    //
    global $zco_notifier;
    $zco_notifier->notify('NOTIFY_ZEN_GET_PRODUCTS_SPECIAL_PRICE', $product->fields, $sale, $product_price);

    if ($sale === false) {
        return $special_price;
    }

    if (!$special_price) {
        $tmp_special_price = $product_price;
    } else {
        $tmp_special_price = $special_price;
    }

    // SPECIALS_CONDITION_DROPDOWN_0: Ignore Specials Price - Apply to Product Price and Replace Special
    // SPECIALS_CONDITION_DROPDOWN_1: Ignore SaleCondition - No Sale Applied When Special Exists
    // SPECIALS_CONDITION_DROPDOWN_2: Apply SaleDeduction to Specials Price - Otherwise Apply to Price
    switch ($sale['sale_deduction_type']) {
        case 0:
            $sale_product_price = $product_price - $sale['sale_deduction_value'];
            $sale_special_price = $tmp_special_price - $sale['sale_deduction_value'];
            break;
        case 1:
            $sale_product_price = $product_price - (($product_price * $sale['sale_deduction_value']) / 100);
            $sale_special_price = $tmp_special_price - (($tmp_special_price * $sale['sale_deduction_value']) / 100);
            break;
        case 2:
            $sale_product_price = $sale['sale_deduction_value'];
            $sale_special_price = $sale['sale_deduction_value'];
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
    }

    switch ($sale['sale_specials_condition']) {
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

/**
 * Determine Display Price, considering specials/sales/taxes/free/call/etc
 * @param int $product_id
 * @return string HTML markup with spans around different segments of price texts
 */
function zen_get_products_display_price($product_id)
{
    global $currencies, $zco_notifier;

    $free_tag = '';
    $call_tag = '';

    // if in catalog, check whether customer should see prices
    if (IS_ADMIN_FLAG === false) {
        // 0 = normal shopping
        // 1 = Login to shop
        // 2 = Can browse but no prices
        // verify whether to display prices
        switch (true) {
            case (CUSTOMERS_APPROVAL === '1' && !zen_is_logged_in()):
                // customer must be logged in to browse
                return '';
                break;
            case (CUSTOMERS_APPROVAL === '2' && !zen_is_logged_in()):
                // customer may browse but no prices
                return TEXT_LOGIN_FOR_PRICE_PRICE;
                break;
            case (CUSTOMERS_APPROVAL === '3' && TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM !== ''):
                // customer may browse but no prices
                return TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM;
                break;
            case (CUSTOMERS_APPROVAL_AUTHORIZATION !== '0' && CUSTOMERS_APPROVAL_AUTHORIZATION !== '3' && !zen_is_logged_in()):
                // customer must be logged in to browse
                return TEXT_AUTHORIZATION_PENDING_PRICE;
                break;
            case (CUSTOMERS_APPROVAL_AUTHORIZATION !== '0' && CUSTOMERS_APPROVAL_AUTHORIZATION !== '3' && (int)$_SESSION['customers_authorization'] > 0):
                // customer must be logged in to browse
                return TEXT_AUTHORIZATION_PENDING_PRICE;
                break;
            case (isset($_SESSION['customers_authorization']) && (int)$_SESSION['customers_authorization'] === 2):
                // customer is logged in and was changed to must be approved to see prices
                return TEXT_AUTHORIZATION_PENDING_PRICE;
                break;
            default:
                // proceed normally
                break;
        }

        // no prices when showcase only
        if (STORE_STATUS === '1') {
            return '';
        }
    }

    $product_check = zen_get_product_details($product_id);

    // no prices on Document General
    if ($product_check->EOF || $product_check->fields['products_type'] === '3') {
        return '';
    }

    $display_special_price = false;
    $display_normal_price = zen_get_products_retail_price($product_id);
    $display_wholesale_price = zen_get_products_base_price($product_id);
    $display_sale_price = zen_get_products_special_price($product_id, false);
    $has_wholesale_price = (Customer::isWholesaleCustomer() === true && $product_check->fields['products_price_w'] !== '0');

    if ($display_sale_price !== false) {
        $display_special_price = zen_get_products_special_price($product_id, true);
    }

    $products_tax_rate = zen_get_tax_rate($product_check->fields['products_tax_class_id']);

    $show_sale_discount = '';
    if (SHOW_SALE_DISCOUNT_STATUS === '1' && ($display_special_price != 0 || $display_sale_price != 0)) {
        // -----
        // Allows an observer to inject any override to the "Sale Price" formatting.
        // If an override is performed, the observer sets the 'pricing_handled' value to true.
        //
        $pricing_handled = false;
        $zco_notifier->notify(
            'NOTIFY_ZEN_GET_PRODUCTS_DISPLAY_PRICE_SALE',
            [
                'products_id' => $product_id,
                'display_sale_price' => $display_sale_price,
                'display_special_price' => $display_special_price,
                'display_normal_price' => $display_normal_price,
                'products_tax_class_id' => $product_check->fields['products_tax_class_id']
            ],
            $pricing_handled,
            $show_sale_discount
        );
        if (!$pricing_handled) {
            if ($display_sale_price) {
                if (SHOW_SALE_DISCOUNT === '1') {
                    if ($display_normal_price != 0) {
                        $show_discount_amount = number_format(100 - (($display_sale_price / $display_normal_price) * 100), (int)SHOW_SALE_DISCOUNT_DECIMALS);
                    } else {
                        $show_discount_amount = '';
                    }
                    $show_sale_discount =
                        '<span class="productPriceDiscount">' .
                            '<br>' .
                            PRODUCT_PRICE_DISCOUNT_PREFIX .
                            $show_discount_amount .
                            PRODUCT_PRICE_DISCOUNT_PERCENTAGE .
                        '</span>';

                } else {
                    $show_sale_discount =
                        '<span class="productPriceDiscount">' .
                            $show_sale_discount .= '<br>' .
                            PRODUCT_PRICE_DISCOUNT_PREFIX .
                            $currencies->display_price(($display_normal_price - $display_sale_price), $products_tax_rate) .
                            PRODUCT_PRICE_DISCOUNT_AMOUNT .
                        '</span>';
                }
            } elseif (SHOW_SALE_DISCOUNT === '1') {
                $show_sale_discount =
                    '<span class="productPriceDiscount">' .
                        '<br>' .
                        PRODUCT_PRICE_DISCOUNT_PREFIX .
                        number_format(100 - (($display_special_price / $display_normal_price) * 100), (int)SHOW_SALE_DISCOUNT_DECIMALS) .
                        PRODUCT_PRICE_DISCOUNT_PERCENTAGE .
                    '</span>';
            } else {
                $show_sale_discount =
                    '<span class="productPriceDiscount">' .
                        '<br>' .
                        PRODUCT_PRICE_DISCOUNT_PREFIX .
                        $currencies->display_price(($display_normal_price - $display_special_price), $products_tax_rate) .
                        PRODUCT_PRICE_DISCOUNT_AMOUNT .
                    '</span>';
            }
        }
    }

    if ($display_special_price) {
        // -----
        // Allows an observer to inject any override to the "Special/Normal Prices'" formatting.
        //
        $pricing_handled = false;
        $zco_notifier->notify(
            'NOTIFY_ZEN_GET_PRODUCTS_DISPLAY_PRICE_SPECIAL',
            [
                'products_id' => $product_id,
                'display_sale_price' => $display_sale_price,
                'display_special_price' => $display_special_price,
                'display_normal_price' => $display_normal_price,
                'products_tax_class_id' => $product_check->fields['products_tax_class_id'],
                'product_is_free' => $product_check->fields['product_is_free']
            ],
            $pricing_handled,
            $show_normal_price,
            $show_special_price,
            $show_sale_price
        );
        if (!$pricing_handled) {
            $show_normal_price =
                '<span class="normalprice">' .
                    $currencies->display_price($display_normal_price, $products_tax_rate) .
                ' </span>';

            if ($display_sale_price && $display_sale_price != $display_special_price) {
                $show_special_price =
                    '&nbsp;' .
                    '<span class="productSpecialPriceSale">' .
                        $currencies->display_price($display_special_price, $products_tax_rate) .
                    '</span>';
                if ($product_check->fields['product_is_free'] === '1') {
                    $show_sale_price =
                        '<br>' .
                        '<span class="productSalePrice">' .
                            PRODUCT_PRICE_SALE .
                            '<s>' .
                                $currencies->display_price($display_sale_price, $products_tax_rate) .
                            '</s>' .
                        '</span>';
                } else {
                    $show_sale_price =
                        '<br>' .
                        '<span class="productSalePrice">' .
                            PRODUCT_PRICE_SALE .
                            $currencies->display_price($display_sale_price, $products_tax_rate) .
                        '</span>';
                }
            } else {
                if ($product_check->fields['product_is_free'] === '1') {
                    $show_special_price =
                        '&nbsp;' .
                        '<span class="productSpecialPrice">' .
                            '<s>' .
                                $currencies->display_price($display_special_price, $products_tax_rate) .
                            '</s>' .
                        '</span>';
                } else {
                    $show_special_price =
                        '&nbsp;' .
                        '<span class="productSpecialPrice">' .
                            $show_special_price .= $currencies->display_price($display_special_price, $products_tax_rate) .
                        '</span>';
                }
                $show_sale_price = '';
            }
        }
    } else {
        // -----
        // Allows an observer to inject any override to the "Normal Prices'" formatting.
        //
        $pricing_handled = false;
        $zco_notifier->notify(
            'NOTIFY_ZEN_GET_PRODUCTS_DISPLAY_PRICE_NORMAL',
            [
                'products_id' => $product_id,
                'display_sale_price' => $display_sale_price,
                'display_special_price' => $display_special_price,
                'display_normal_price' => $display_normal_price,
                'products_tax_class_id' => $product_check->fields['products_tax_class_id'],
                'product_is_free' => $product_check->fields['product_is_free'],
                'display_wholesale_price' => $display_wholesale_price,
                'has_wholesale_price' => $has_wholesale_price,
            ],
            $pricing_handled,
            $show_normal_price,
            $show_special_price,
            $show_sale_price
        );
        if (!$pricing_handled) {
            if ($display_sale_price) {
                $show_normal_price =
                    '<span class="normalprice">' .
                        $currencies->display_price($display_normal_price, $products_tax_rate) .
                    ' </span>';

                $show_special_price = '';

                $show_sale_price =
                    '<br>' .
                    '<span class="productSalePrice">' .
                        PRODUCT_PRICE_SALE .
                        $currencies->display_price($display_sale_price, $products_tax_rate) .
                    '</span>';
            } else {
                $show_special_price = '';
                $show_sale_price = '';
                if ($has_wholesale_price === true) {
                    $show_normal_price =
                        '<span class="normalprice">' .
                            $currencies->display_price($display_normal_price, $products_tax_rate) .
                        '</span>';
                    $show_sale_price =
                        '<span class="productSalePrice wholesale-price">' .
                            PRODUCT_PRICE_WHOLESALE .
                            $currencies->display_price($display_wholesale_price, $products_tax_rate) .
                        '</span>';
                } elseif ($product_check->fields['product_is_free'] === '1') {
                    $show_normal_price =
                        '<span class="productFreePrice">' .
                            '<s>' .
                                $currencies->display_price($display_normal_price, $products_tax_rate) .
                            '</s>' .
                        '</span>';
                } else {
                    $show_normal_price =
                        '<span class="productBasePrice">' .
                            $currencies->display_price($display_normal_price, $products_tax_rate) .
                        '</span>';
                }
            }
        }
    }

    if ($display_normal_price == 0) {
        // don't show the $0.00
        $final_display_price = $show_special_price . $show_sale_price . $show_sale_discount;
    } else {
        $final_display_price = $show_normal_price . $show_special_price . $show_sale_price . $show_sale_discount;
    }

    // -----
    // Allows an observer to inject any override to the "Free" and "Call for Price" formatting.
    //
    $tags_handled = false;
    $zco_notifier->notify(
        'NOTIFY_ZEN_GET_PRODUCTS_DISPLAY_PRICE_FREE_OR_CALL',
        [
            'product_is_free' => $product_check->fields['product_is_free'],
            'product_is_call' => $product_check->fields['product_is_call'],
        ],
        $tags_handled,
        $free_tag,
        $call_tag
    );
    if (!$tags_handled) {
        // If Free, Show it
        if ($product_check->fields['product_is_free'] === '1') {
            $free_tag = '<br>';
            if (OTHER_IMAGE_PRICE_IS_FREE_ON === '0') {
                $free_tag .= PRODUCTS_PRICE_IS_FREE_TEXT;
            } else {
                $free_tag .= zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_PRICE_IS_FREE, PRODUCTS_PRICE_IS_FREE_TEXT);
            }
        }

        // If Call for Price, Show it
        if ($product_check->fields['product_is_call'] === '1') {
            $call_tag = '<br>';
            if (PRODUCTS_PRICE_IS_CALL_IMAGE_ON === '0') {
                $call_tag .= PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT;
            } else {
                $call_tag .= zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_CALL_FOR_PRICE, PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT);
            }
        }
    }

    return $final_display_price . $free_tag . $call_tag;
}


/**
 * computes products_price + option groups lowest attributes price of each group when on
 * @param int $product_id
 * @param bool $force_retail_price
 * @return float|int
 */
function zen_get_products_base_price($product_id, bool $force_retail_price = false)
{
    global $db, $zco_notifier;

    // Give an observer the opportunity to override the product's base price.
    $products_base_price = 0;
    $base_price_is_handled = false;
    $zco_notifier->notify('ZEN_GET_PRODUCTS_BASE_PRICE', $product_id, $products_base_price, $base_price_is_handled);
    if ($base_price_is_handled === true) {
        return $products_base_price;
    }

    $product_check = zen_get_product_details($product_id);
    if ($product_check->EOF) {
        return false;
    }

    if ($force_retail_price === true) {
        $products_price = $product_check->fields['products_price'];
    } else {
        $products_price = zen_get_retail_or_wholesale_price($product_check->fields['products_price'], $product_check->fields['products_price_w']);
    }

    if ($product_check->fields['products_priced_by_attribute'] !== '1') {
        return $products_price;
    }

    // do not select display only attributes and attributes_price_base_included is true
    $sql = "SELECT options_id, price_prefix, options_values_price, options_values_price_w,
                    attributes_display_only, attributes_price_base_included,
             CAST(CONCAT(price_prefix, options_values_price) AS decimal(15,4)) AS value
             FROM " . TABLE_PRODUCTS_ATTRIBUTES . "
             WHERE products_id = " . (int)$product_id . "
             AND attributes_display_only != 1
             AND attributes_price_base_included=1
             ORDER BY options_id, value";
    $results = $db->Execute($sql);

    $the_options_id = 'x';
    $the_base_price = 0;

    // add attributes price to price

    foreach ($results as $result) {
        if ($the_options_id != $result['options_id']) {
            $the_options_id = $result['options_id'];
            if ($force_retail_price === true) {
                $options_values_price = $result['options_values_price'];
            } else {
                $options_values_price = zen_get_retail_or_wholesale_price($result['options_values_price'], $result['options_values_price_w']);
            }
            $factor = $result['price_prefix'] == '-' ? -1 : 1;
            $the_base_price += $factor * $options_values_price;
        }
    }

    return $products_price + $the_base_price;
}

/**
 * Forces the return of a product's retail price.
 * @param int $product_id
 * @return float|int
 */
function zen_get_products_retail_price($product_id)
{
    return zen_get_products_base_price($product_id, true);
}

/**
 * Lookup whether the product is marked as free
 * @param int $product_id
 * @return bool
 */
function zen_get_products_price_is_free($product_id)
{
    $result = zen_get_product_details($product_id);
    return (!$result->EOF && $result->fields['product_is_free'] === '1');
}

/**
 * Lookup whether the product is call-for-price
 * @param int $product_id
 * @return bool
 */
function zen_get_products_price_is_call($product_id)
{
    $result = zen_get_product_details($product_id);
    return (!$result->EOF && $result->fields['product_is_call'] === '1');
}

/**
 * Lookup whether the product is priced by attributes
 * @param int $product_id
 * @return bool
 */
function zen_get_products_price_is_priced_by_attributes($product_id)
{
    $result = zen_get_product_details($product_id);
    return (!$result->EOF && $result->fields['products_priced_by_attribute'] === '1');
}

/**
 * Lookup a product's minimum quantity
 * @param int $product_id
 * @return string
 */
function zen_get_products_quantity_order_min($product_id)
{
    $result = zen_get_product_details($product_id);
    return ($result->EOF) ? '' : $result->fields['products_quantity_order_min'];
}

/**
 * Lookup a product's minimum unit order
 * @param int $product_id
 * @return string
 */
function zen_get_products_quantity_order_units($product_id)
{
    $result = zen_get_product_details($product_id);
    return ($result->EOF) ? '' : $result->fields['products_quantity_order_units'];
}

/**
 * Lookup a product's maximum quantity
 * @param int $product_id
 * @return string
 */
function zen_get_products_quantity_order_max($product_id)
{
    $result = zen_get_product_details($product_id);
    return ($result->EOF) ? '' : $result->fields['products_quantity_order_max'];
}

/**
 * Lookup a product's quantity box status
 * @param int $product_id
 * @return bool
 */
function zen_get_products_qty_box_status($product_id)
{
    $result = zen_get_product_details($product_id);
    return (!$result->EOF && $result->fields['products_qty_box_status'] === '1');
}

/**
 * Lookup whether a product's settings allow for mix/match quantities
 * @param int $product_id
 * @return bool
 */
function zen_get_products_quantity_mixed($product_id)
{
    $result = zen_get_product_details($product_id);
    return (!$result->EOF && $result->fields['products_quantity_mixed'] === '1');
}

/**
 * Return a products quantity minimum and units display
 * @param int $product_id
 * @param bool $include_break include BR tag in markup
 * @param bool $message_is_for_shopping_cart
 * @return string
 */
function zen_get_products_quantity_min_units_display($product_id, $include_break = true, $message_is_for_shopping_cart = false)
{
    $result = zen_get_product_details($product_id);

    if ($result->EOF) {
        return '';
    }

    $check_min = $result->fields['products_quantity_order_min'];
    $check_max = $result->fields['products_quantity_order_max'];
    $check_units = $result->fields['products_quantity_order_units'];
    $allows_mixed = (bool)$result->fields['products_quantity_mixed'];

    $the_min_units = '';

    if ($check_min != 1 || $check_units != 1) {
        if ($check_min != 1) {
            $the_min_units .= '<span class="qmin">' . PRODUCTS_QUANTITY_MIN_TEXT_LISTING . '&nbsp;' . $check_min . '</span>';
        }

        if ($check_units != 1) {
            $the_min_units .= '<span class="qunit">' . (zen_not_null($the_min_units) ? ' ' : '') . PRODUCTS_QUANTITY_UNIT_TEXT_LISTING . '&nbsp;' . $check_units . '</span>';
        }

        // don't check for mixed if no attributes
        $chk_mix = ($allows_mixed === true && zen_has_product_attributes((int)$product_id));
        if ($chk_mix === true) {
            $the_min_units .= '<span class="qmix">';
            if ($check_min > 0 || $check_units > 0) {
                if ($include_break) {
                    $the_min_units .= '<br>';
                } else {
                    $the_min_units .= '&nbsp;&nbsp;';
                }
                $the_min_units .= ($message_is_for_shopping_cart == false ? TEXT_PRODUCTS_MIX_OFF : TEXT_PRODUCTS_MIX_OFF_SHOPPING_CART);

            } else {
                if ($include_break) {
                    $the_min_units .= '<br>';
                } else {
                    $the_min_units .= '&nbsp;&nbsp;';
                }
                $the_min_units .= ($message_is_for_shopping_cart == false ? TEXT_PRODUCTS_MIX_ON : TEXT_PRODUCTS_MIX_ON_SHOPPING_CART);
            }
            $the_min_units .= '</span>';
        }
    }

    if ($check_max > 0) {
        $the_min_units .= '<span class="qmax">';
        if ($include_break == true) {
            $the_min_units .= ($the_min_units !== '' ? '<br>' : '');
        } else {
            $the_min_units .= ($the_min_units !== '' ? '&nbsp;&nbsp;' : '');
        }
        $the_min_units .= PRODUCTS_QUANTITY_MAX_TEXT_LISTING . '&nbsp;' . $check_max;
        $the_min_units .= '</span>';
    }

    return $the_min_units;
}

/**
 * Calculate buy-now quantity
 * Works on Mixed ON
 *
 * @param int $product_id
 * @return float|int
 */
function zen_get_buy_now_qty($product_id)
{
    $result = zen_get_product_details($product_id);
    $check_min = $result->fields['products_quantity_order_min'];
    $check_units = $result->fields['products_quantity_order_units'];
    $buy_now_qty = 1;

    $mixed_products_in_cart = $_SESSION['cart']->in_cart_mixed($product_id);

    switch (true) {
        case ($mixed_products_in_cart == 0):
            if ($check_min >= $check_units) {
                // Set the buy now quantity (associated product is not yet in the cart) to the first value satisfying both the minimum and the units.
                if ($check_units == 0) $check_units = 1;
                $buy_now_qty = $check_units * ceil($check_min / $check_units);
                // Uncomment below to set the buy now quantity to the value of the minimum required regardless if it is a multiple of the units.
                //$buy_now_qty = $check_min;
            } else {
                $buy_now_qty = $check_units;
            }
            break;
        case ($mixed_products_in_cart < $check_min):
            $buy_now_qty = $check_min - $mixed_products_in_cart;
            break;
        case ($mixed_products_in_cart > $check_min):
            // set to units or difference in units to balance cart
            $new_units = $check_units - fmod_round($mixed_products_in_cart, $check_units);
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

/**
 * compute product discount to be applied to attributes or other values
 * @param int $product_id
 * @param int $attribute_id
 * @param int|float $attributes_amount
 * @param int|float $check_qty
 * @return bool|float|int|mixed|string
 */
function zen_get_discount_calc($product_id, $attribute_id = 0, $attributes_amount = 0, $check_qty = 0)
{
    global $discount_type_id, $sale_maker_discount;

    // no charge
    if (!empty($attribute_id) && empty($attributes_amount)) {
        return 0;
    }

    $new_products_price = zen_get_products_base_price($product_id);
    $new_special_price = false;
    $new_sale_price = zen_get_products_special_price($product_id, false);

    if ($new_sale_price !== false) {
        $new_special_price = zen_get_products_special_price($product_id, true);
    }

    $discount_type_id = zen_get_products_sale_discount_type($product_id);

    $special_price_discount = 0;
    if ($new_products_price != 0) {
        $special_price_discount = ($new_special_price != 0 ? ($new_special_price / $new_products_price) : 1);
    }

    $sale_price_discount = 0;
    if ($new_products_price != 0) {
        $sale_price_discount = ($new_sale_price != 0 ? ($new_sale_price / $new_products_price) : 1);
    }
    $sale_maker_discount = zen_get_products_sale_discount_type($product_id, '', 'amount');

    // percentage adjustment of discount
    if (($discount_type_id == 120 or $discount_type_id == 1209) or ($discount_type_id == 110 or $discount_type_id == 1109)) {
        $sale_maker_discount = ($sale_maker_discount != 0 ? (100 - $sale_maker_discount) / 100 : 1);
    }

    $qty = $check_qty;

// fix here
// BOF: percentage discounts apply to price
    switch (true) {
        case (zen_get_discount_qty($product_id, $qty) && !$attribute_id):
            // discount quantities exist and this is not an attribute
            $check_discount_qty_price = zen_get_products_discount_price_qty($product_id, $qty, $attributes_amount);
            return $check_discount_qty_price;
            break;

        case (zen_get_discount_qty($product_id, $qty) && zen_get_products_price_is_priced_by_attributes($product_id)):
            // discount quantities exist and this is priced by attribute
            return zen_get_products_discount_price_qty($product_id, $qty, $attributes_amount);
            break;

        case ($discount_type_id == 5):
            // No Sale and No Special
            /*
                      Possible reasons to be in this discount_type_id:

                      No Sale nor special,
                      a sale without a special and sale price is to apply against the price,
                      a sale without a special and percentage is to apply against the price,
                      a sale without a special and sale's new price is to apply against the price
            */
            if (!$attribute_id) {
                $sale_maker_discount = $sale_maker_discount;
            } else {
                // compute attribute amount
                if ($attributes_amount != 0) {
                    if ($sale_price_discount != 0) {
                        $calc = ($attributes_amount * $sale_price_discount);
                    } else {
                        $calc = $attributes_amount;
                    }

                    $sale_maker_discount = $calc;
                } else {
                    $sale_maker_discount = $sale_maker_discount;
                }
            }
            break;
        case ($discount_type_id == 59):
            // No Sale and has a Special OR there is Sale and a special but the price is the special
            /*
                      Possible reasons to be in this discount_type_id:

                      No Sale but a special,
                      a sale with a special and sale price is to apply against the price,
                      a sale with a special and percentage is to apply against the price,
                      a sale with a special and sale's new price is to apply against the price
            */
            if (!$attribute_id) {
                $sale_maker_discount = $sale_maker_discount;
            } else {
                // compute attribute amount sale_price_discount will have either the sale price or if no sale the special price
                if ($attributes_amount != 0) {
                    $calc = ($attributes_amount * $sale_price_discount);
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
            if (!$attribute_id) {
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
            if (!$attribute_id) {
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
            if (!$attribute_id) {
                $sale_maker_discount = $sale_maker_discount;
            } else {
                // compute attribute amount
                if ($attributes_amount != 0) {
                    $calc = ($attributes_amount * $sale_maker_discount);
                    $sale_maker_discount = $calc;
                } else {
                    if ($attributes_amount != 0) { // This code is never run.
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
            if (!$attribute_id) {
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
// EOF: percentage discounts skip specials

// BOF: flat amount discounts
        case ($discount_type_id == 20): // This option should not do anything to basic attributes without further consideration of the overall effect on the price and the starting price.
            // flat amount discount Sale and Special without a special
            if (!$attribute_id) {
                $sale_maker_discount = $sale_maker_discount;
            } else {
                // compute attribute amount
                if ($attributes_amount != 0) {
                    $calc = ($attributes_amount /*- $sale_maker_discount*/);
                    $sale_maker_discount = $calc;
                } else {
                    $sale_maker_discount = $sale_maker_discount;
                }
            }
            break;
        case ($discount_type_id == 209): // This option for attributes should not do anything unless the price of the product is solely dependent on a single attribute, all attributes can be reduced a constant amount (non-zero).
            // flat amount discount on Sale and Special with a special
            if (!$attribute_id) {
                $sale_maker_discount = $sale_maker_discount;
            } else {
                // compute attribute amount
                if ($attributes_amount != 0) {
                    $calc = ($attributes_amount * $special_price_discount);
                    // Should be that if product is not priced by attributes then no change in attribute price.
                    $calc2 = ($calc - $sale_maker_discount);
                    $sale_maker_discount = $calc2;
                } else {
                    $sale_maker_discount = $sale_maker_discount;
                }
            }
            break;
// EOF: flat amount discounts

// BOF: flat amount discounts Skip Special
        case ($discount_type_id == 10): // This option for attributes should not do anything unless the price of the product is solely dependent on a single attribute, all attributes can be reduced a constant amount (non-zero).
            // flat amount discount Sale and Special without a special
            if (!$attribute_id) {
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
            if (!$attribute_id) {
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
        case ($discount_type_id == 210):
        case ($discount_type_id == 220):
            // New Price amount discount Sale and Special without a special
            if (!$attribute_id) {
                $sale_maker_discount = $sale_maker_discount;
            } else {
                // compute attribute amount
                if ($attributes_amount != 0) {
                    $calc = ($attributes_amount * $sale_price_discount);
                    $sale_maker_discount = $calc;
                } else {
                    $sale_maker_discount = $sale_maker_discount;
                }
            }
            break;
        case ($discount_type_id == 2109):
        case ($discount_type_id == 2209):
            // New Price amount discount on Sale and Special with a special
            if (!$attribute_id) {
//          $sale_maker_discount = $sale_maker_discount;
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
// EOF: New Price amount discounts

        // Neither of these values are possible nor occur
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

/**
 * look up discount in sale makers - attributes only can have discounts if set as percentages
 * this gets the discount amount; this does not determine when to apply the discount
 * @param int|bool $product_id
 * @param int|bool $categories_id
 * @param string $return_value 'type'|'amount'
 * @return float|int
 */
function zen_get_products_sale_discount_type($product_id = false, $categories_id = false, $return_value = 'type')
{
    global $db;

    /*
    $salemaker_discount_type comes from 'sale_deduction_type' field which is an optional database value
    0 = flat amount off base price with a special
    1 = Percentage off base price with a special
    2 = New Price with a special

    5 = No Sale or Skip Products with Special or skip product if there is a sale and sale condition is to ignore a special and apply to Price: Result of function

    $sale_maker_special_condition comes from 'sale_specials_condition' field which is an optional database value
    if a sale exists then is used in the following equation
    special options + option * 10
    which is like:
    special options * 100 + option * 10 or specifically:
    $salemaker_discount_type * 100 + $salemaker_discount_type * 10 though does not apply if the $sale_maker_special_condition is 0
    0 = Ignore special and apply to Price switch to 5
    1 = Skip Products with Specials
    2 = Apply to Special Price

    If a special exist * 10+9
    Where special exist = special options * option * 10 and then multiplies by 10 and adds 9 to it

    No Sale No special: 5
    No Sale but a special: 59

    Grouping of parentheses at the beginning of each line reflects the value
    assigned to the following variables:
    ($sale_maker_discount_type, $sale_maker_special_condition)
    Results shown on right are first No special with a sale OR special with a sale second
    (0, 0) 5 or 5 * 10 + 9 = flat apply to price         = 5 or 59
    (0, 1) 0*100 + 1*10    = flat skip Specials          = 10 or 109 (First use sale price, second use special)
    (0, 2) 0*100 + 2*10    = flat apply to special       = 20 or 209

    (1, 0) 5 or 5 * 10 + 9 = Percentage apply to price   = 5 or 59
    (1, 1) 1*100 + 1*10    = Percentage skip Specials    = 110 or 1109 (First use sale price, second use special)
    (1, 2) 1*100 + 2*10    = Percentage apply to special = 120 or 1209

    (2, 0) 5 or 5 * 10 + 9 = New Price apply to price    = 5 or 59
    (2, 1) 2*100 + 1*10    = New Price skip Specials     = 210 or 2109 (First use sale price, second use special)
    (2, 2) 2*100 + 2*10    = New Price apply to Special  = 220 or 2209

    In result:
    5 if:
       No Sale nor special,
       a sale without a special and sale price is to apply against the price,
       a sale without a special and percentage is to apply against the price,
       a sale without a special and sale's new price is to apply against the price

    59 if:
       No Sale but a special,
       a sale with a special and sale price is to apply against the price,
       a sale with a special and percentage is to apply against the price,
       a sale with a special and sale's new price is to apply against the price

    possible return values in numerical order:
    5, 59, 110, 120, 210, 220, 1109, 1209, 2109, 2209

    */

// get products category
    if (!empty($categories_id)) {
        $check_category = $categories_id;
    } else {
        $check_category = zen_get_products_category_id($product_id);
    }

    $sale_exists = false;
    $sale_maker_discount = 0;
    $sale_maker_special_condition = 0;
    $sql = "SELECT * FROM " . TABLE_SALEMAKER_SALES . " WHERE sale_status=1";
    $results = $db->Execute($sql);
    foreach ($results as $result) {
       if (empty($result['sale_categories_all'])) {
          continue;
       }
        $categories = explode(',', $result['sale_categories_all']);
        foreach ($categories as $key => $value) {
            if ($value == $check_category) {
                $sale_exists = true;
                $sale_maker_discount = $result['sale_deduction_value'];
                $sale_maker_special_condition = $result['sale_specials_condition'];
                $sale_maker_discount_type = $result['sale_deduction_type'];
                break;
            }
        }
    }

    // return the sale deduction amount for the last sale found in the loop
    if ($return_value == 'amount') {
        return $sale_maker_discount;
    }

    // else we return the calculated discount type:
    $check_special = zen_get_products_special_price($product_id, true);

    if ($sale_exists == true && $sale_maker_special_condition != 0) {
        $sale_maker_discount_type = (($sale_maker_discount_type * 100) + ($sale_maker_special_condition * 10));
    } else {
        $sale_maker_discount_type = 5;
    }

    if ($check_special) {
        $sale_maker_discount_type = ($sale_maker_discount_type * 10) + 9;
    }

    return $sale_maker_discount_type;
}


/**
 * look up discount in sale makers - attributes only can have discounts if set as percentages
 * this gets the discount amount this does not determine when to apply the discount
 * @deprecated since v1.5.5 use zen_get_discount_calc()
 */
function zen_get_products_sale_discount($product_id = false, $categories_id = false, $display_type = false)
{
    trigger_error('Call to deprecated function zen_get_products_sale_discount. Use zen_get_discount_calc() instead', E_USER_DEPRECATED);
}

/**
 * Get display price, using Actual Price
 * Specials and Tax Included
 * @param int $product_id
 * @return bool|float|string
 */
function zen_get_products_actual_price($product_id)
{
    $result = zen_get_product_details($product_id);

    // If Free, Show it
    if ($result->fields['product_is_free'] === '1') {
        return 0;
    }

    $display_sale_price = zen_get_products_special_price($product_id, false);
    if ($display_sale_price !== false) {
        return $display_sale_price;
    }

    $display_special_price = zen_get_products_special_price($product_id, true);
    if ($display_special_price !== false) {
        return $display_special_price;
    }

    return zen_get_products_base_price($product_id);
}

/**
 * Calculate attribute price based on specified factors
 * @param float $price
 * @param float $special
 * @param float $factor
 * @param float $offset
 * @return float|int
 */
function zen_get_attributes_price_factor($price, $special, $factor, $offset)
{
    if (defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL') && ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL == 1 && $special) {
        // calculate from specials_new_products_price
        $calculated_price = $special * ($factor - $offset);
    } else {
        // calculate from products_price
        $calculated_price = $price * ($factor - $offset);
    }
    return $calculated_price;
}

/**
 * get attributes_qty_prices or attributes_qty_prices_onetime based on qty
 * @param string $string
 * @param int|float $qty
 * @return int|mixed
 */
function zen_get_attributes_qty_prices_onetime($string, $qty)
{
    if (empty($string)) {
        return 0;
    }
    $attribute_qty = preg_split("/[:,]/", str_replace(' ', '', $string));
    $new_price = 0;
    $size = count($attribute_qty);
// if an empty string is passed then $attributes_qty will consist of a 1 element array
    if ($size > 1) {
        for ($i = 0, $n = $size; $i < $n; $i += 2) {
            $new_price = $attribute_qty[$i + 1];
            if ($qty <= $attribute_qty[$i]) {
                $new_price = $attribute_qty[$i + 1];
                break;
            }
        }
    }
    return $new_price;
}

/**
 * @param string $check_what
 * @param int|float $check_for
 * @return mixed|string
 * @deprecated since 1.5.8 use zen_get_attributes_qty_prices_onetime()
 */
function zen_get_attributes_quantity_price($check_what, $check_for)
{
    trigger_error('Call to deprecated function zen_get_attributes_quantity_price. Use zen_get_attributes_qty_prices_onetime() instead', E_USER_DEPRECATED);

    return zen_get_attributes_qty_prices_onetime($check_what, $check_for);
}

/**
 * determine attribute final price
 * @param int $attribute_id
 * @param int|float $qty
 * @param queryFactoryResult $pre_selected
 * @param bool $include_onetime
 * @param bool $prod_priced_by_attr
 * @param int $attributes_discounted
 * @param bool $include_products_price_in
 * @return bool|float|int|mixed|string
 */
function zen_get_attributes_price_final($attribute_id, $qty = 1, $pre_selected = null, $include_onetime = false, $prod_priced_by_attr = false, $attributes_discounted = 0, $include_products_price_in = false)
{
    $attributes_price_final = 0;

    if (empty($pre_selected) || $attribute_id != $pre_selected->fields['products_attributes_id']) {
        $pre_selected = zen_get_attribute_details_by_id((int)$attribute_id);
    }

    $products_id = $pre_selected->fields['products_id'];
    $products_attributes_id = $pre_selected->fields['products_attributes_id'];
    $options_values_price = zen_get_retail_or_wholesale_price($pre_selected->fields['options_values_price'], $pre_selected->fields['options_values_price_w']);
    $price_prefix = $pre_selected->fields['price_prefix'];
    $products_price_without_attributes = zen_get_product_retail_or_wholesale_price((int)$products_id);

    // normal attributes price discounted by sales/specials or not discounted if neither a sale nor a special
    if ($price_prefix === '-') {
        $attributes_price_final -= zen_get_discount_calc($products_id, $products_attributes_id, ($prod_priced_by_attr ? $products_price_without_attributes : 0) + ($prod_priced_by_attr ? -1 : 1) * $options_values_price);
    } else {
        $attributes_price_final += zen_get_discount_calc($products_id, $products_attributes_id, ($prod_priced_by_attr ? $products_price_without_attributes : 0) + $options_values_price);
    }
    // qty discounts
    $attributes_price_final += zen_get_attributes_qty_prices_onetime($pre_selected->fields['attributes_qty_prices'], $qty);

    // price factor
    $display_normal_price = zen_get_discount_calc($products_id, $products_attributes_id, $products_price_without_attributes + $options_values_price);

    // if the product is priced by attributes
    if ($prod_priced_by_attr && empty($options_values_price)) {
        if ($price_prefix === '-') {
            $attributes_price_final += $display_normal_price;
        } else {
            $attributes_price_final -= $display_normal_price;
        }
    }
    $display_special_price = zen_get_products_special_price($products_id);

    $attributes_price_final += zen_get_attributes_price_factor(
        $display_normal_price,
        $display_special_price,
        $pre_selected->fields['attributes_price_factor'],
        $pre_selected->fields['attributes_price_factor_offset']
    );

    // per word and letter charges
    if (zen_get_attributes_type($attribute_id) == PRODUCTS_OPTIONS_TYPE_TEXT) {
        // calc per word or per letter
    }

    // onetime charges
    if ($include_onetime === true || $include_onetime === 'true') { // string for backward compat prior to 1.5.8
        $attributes_price_final += zen_get_attributes_price_final_onetime($products_attributes_id, 1, $pre_selected);
    }

    if ($include_products_price_in === false && $prod_priced_by_attr === true) {
        return $attributes_price_final - $products_price_without_attributes;
    }
    return $attributes_price_final;
}

/**
 * determine attribute final price, for onetime charges
 * @param int $attribute_id
 * @param int $qty
 * @param queryFactoryResult $pre_selected_onetime
 * @return float|int|mixed|string
 */
function zen_get_attributes_price_final_onetime($attribute_id, $qty = 1, $pre_selected_onetime = null)
{
    // re-query the db if necessary
    if (empty($pre_selected_onetime) || $attribute_id != $pre_selected_onetime->fields['products_attributes_id']) {
        $pre_selected_onetime = zen_get_attribute_details_by_id((int)$attribute_id);
    }

    // onetime charges
    $attributes_price_final_onetime = $pre_selected_onetime->fields['attributes_price_onetime'];

    // price factor
    $display_normal_price = zen_get_products_actual_price($pre_selected_onetime->fields['products_id']);
    $display_special_price = zen_get_products_special_price($pre_selected_onetime->fields['products_id']);

    // price factor one time
    $attributes_price_final_onetime += zen_get_attributes_price_factor(
        $display_normal_price,
        $display_special_price,
        $pre_selected_onetime->fields['attributes_price_factor_onetime'],
        $pre_selected_onetime->fields['attributes_price_factor_onetime_offset']
    );

    // onetime charge qty price
    $attributes_price_final_onetime += zen_get_attributes_qty_prices_onetime($pre_selected_onetime->fields['attributes_qty_prices_onetime'], 1);

    return $attributes_price_final_onetime;
}

/**
 * get attributes type
 * @param int $attribute_id
 * @return int|mixed
 */
function zen_get_attributes_type($attribute_id)
{
    $check_options_id = zen_get_attribute_details_by_id((int)$attribute_id);
    if ($check_options_id->EOF) {
        return 0;
    }

    $result = zen_get_option_details((int)$check_options_id->fields['options_id']);
    return ($result->EOF) ? 0 : $result->fields['products_options_type'];
}

/**
 * calculate words in a string
 * @param string $string
 * @param int $free number of free words to allow
 * @return int
 */
function zen_get_word_count($string, $free = 0)
{
    $string = str_replace(["\r\n", "\n", "\r", "\t"], ' ', $string);
    if ($string !== '') {
        $string = preg_replace('/[ ]+/', ' ', $string);
        $string = trim($string);
        $word_count = substr_count($string, ' ');
        return (($word_count + 1) - $free);
    }

    // nothing to count
    return 0;
}

/**
 * calculate price of words
 * @param string $string
 * @param int $free number of free words to allow
 * @param int|float $price per word
 * @return float
 */
function zen_get_word_count_price($string, $free = 0, $price = 0)
{
    $word_count = zen_get_word_count($string, $free);
    if ($word_count >= 1) {
        return ($word_count * $price);
    }

    return 0;
}

/**
 * calculate letters
 * @param string $string
 * @param int $free number of free letters to allow
 * @return int
 */
function zen_get_letters_count($string, $free = 0)
{
    $string = str_replace(["\r\n", "\n", "\r", "\t"], ' ', $string);
    $string = preg_replace('/[ ]+/', ' ', $string);
    $string = trim($string);
    if (TEXT_SPACES_FREE === '1') {
        $letters_count = strlen(str_replace(' ', '', $string));
    } else {
        $letters_count = strlen($string);
    }
    if ($letters_count - $free >= 1) {
        return ($letters_count - $free);
    }

    return 0;
}

/**
 * calculate letters price
 * @param string $string
 * @param int $free number of free letters to allow
 * @param int|float $price price per letter
 * @return float
 */
function zen_get_letters_count_price($string, $free = 0, $price = 0)
{
    $letters_price = zen_get_letters_count($string, $free) * $price;

    if ($letters_price <= 0) {
        return 0;
    }

    return $letters_price;
}

/**
 * compute discount based on qty
 * @param int $product_id
 * @param int|float $check_qty Quantity to check against (eg: how many are in the cart)
 * @param int $check_amount
 * @return bool|float|string
 */
function zen_get_products_discount_price_qty($product_id, $check_qty, $check_amount = 0)
{
    global $db;

    $product_id = (int)$product_id;

    if (IS_ADMIN_FLAG === false) {
        $new_qty = $_SESSION['cart']->in_cart_mixed_discount_quantity($product_id);
        // check for discount qty mix
        if ($new_qty > $check_qty) {
            $check_qty = $new_qty;
        }
    }

    $result = zen_get_product_details($product_id);
    if ($result->EOF) {
        return false;
    }

    $product = $result->fields;

    $sql =
        "SELECT * FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
          WHERE products_id= $product_id
            AND discount_qty <= '" . zen_db_input($check_qty) . "'
          ORDER BY discount_qty DESC";
    $result = $db->Execute($sql, 1);

    if ($result->EOF) {
        return zen_get_products_actual_price($product_id);
    }

    $discount = $result->fields;
    $discount_price = zen_get_retail_or_wholesale_price($discount['discount_price'], $discount['discount_price_w']);

    $display_price = zen_get_products_base_price($product_id);
    $display_specials_price = zen_get_products_special_price($product_id, false);

    switch ($product['products_discount_type']) {
        // none
        case '0':
            $discounted_price = zen_get_products_actual_price($product_id);
            break;

        // percentage discount
        case '1':
            if ($product['products_discount_type_from'] === '0') {
                // priced by attributes
                if ($check_amount != 0) {
                    $discounted_price = $check_amount - ($check_amount * ($discount_price / 100));
                } else {
                    $discounted_price = $display_price - ($display_price * ($discount_price / 100));
                }
            } elseif (!$display_specials_price) {
                // priced by attributes
                if ($check_amount != 0) {
                    $discounted_price = $check_amount - ($check_amount * ($discount_price / 100));
                } else {
                    $discounted_price = $display_price - ($display_price * ($discount_price / 100));
                }
            } else {
                $discounted_price = $display_specials_price - ($display_specials_price * ($discount_price / 100));
            }
            break;

        // actual price
        case '2':
            $discounted_price = $discount_price;
            break;

        // amount offprice
        case '3':
            if ($product['products_discount_type_from'] === '0' || !$display_specials_price) {
                $discounted_price = $display_price - $discount_price;
            } else {
                $discounted_price = $display_specials_price - $discount_price;
            }
            break;
    }

    return $discounted_price;
}

/**
 * Check whether there are discount quantities defined for the product, greater than the specified threshold
 * @param int $product_id
 * @param int|float $check_qty Quantity of product to check against (eg: that we have in the cart)
 * @return bool
 */
function zen_get_discount_qty($product_id, $check_qty = 0)
{
    global $db;

    if (empty($check_qty)) {
        return false;
    }

    $sql = "SELECT * FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . "
            WHERE products_id = " . (int)$product_id . "
            AND discount_qty != 0";

    $results = $db->Execute($sql, 1);

    return !$results->EOF;
}

/**
 * recalculate and set the products_price_sorter field for the specified $product_id
 * @param int $product_id
 */
function zen_update_products_price_sorter($product_id)
{
    global $db;

    if (empty($product_id)) {
        return;
    }

    $products_price_sorter = zen_get_products_actual_price($product_id);
    $sql =
        "UPDATE " . TABLE_PRODUCTS . "
            SET products_price_sorter = '" . zen_db_prepare_input($products_price_sorter) . "'
          WHERE products_id=" . (int)$product_id;
    $db->Execute($sql, 1);
}

/**
 * salemaker categories array
 * @param string $categories_csv
 * @return array
 */
function zen_parse_salemaker_categories($categories_csv)
{
    if (empty($categories_csv)) {
       return [];
    }
    $clist_array = explode(',', $categories_csv);
    return array_unique($clist_array);
}

/**
 * update salemaker product prices per category per product for the specified $salemaker_id
 * @param int $salemaker_id
 * @return bool
 */
function zen_update_salemaker_product_prices($salemaker_id)
{
    global $db;
    $zv_categories = $db->Execute("SELECT sale_categories_selected FROM " . TABLE_SALEMAKER_SALES . " WHERE sale_id = " . (int)$salemaker_id, 1);
    if ($zv_categories->EOF || empty($zv_categories->fields['sale_categories_selected'])) {
       return false;
    }

    $za_salemaker_categories = zen_parse_salemaker_categories($zv_categories->fields['sale_categories_selected']);
    foreach ($za_salemaker_categories as $category) {
        $update_products_price = zen_get_linked_products_for_category((int)$category);
        foreach ($update_products_price as $product_id) {
            zen_update_products_price_sorter($product_id);
        }
    }
    return true;
}

/**
 * Get details of an active sale for the specified category within the specified price range
 * @param int $category_id
 * @param float $price price range
 * @return array|bool
 */
function zen_get_sale_for_category_and_price($category_id, $price)
{
    global $db;
    $sql =
        "SELECT sale_specials_condition, sale_deduction_value, sale_deduction_type
           FROM " . TABLE_SALEMAKER_SALES . "
          WHERE sale_categories_all LIKE '%," . (int)$category_id . ",%'
            AND sale_status = 1
            AND (sale_date_start <= now() OR sale_date_start <= '0001-01-01')
            AND (sale_date_end >= now() OR sale_date_end <= '0001-01-01')
            AND (sale_pricerange_from <= '" . (float)$price . "' OR sale_pricerange_from = 0)
            AND (sale_pricerange_to >= '" . (float)$price . "' OR sale_pricerange_to = 0)";
    $result = $db->Execute($sql, 1, true, 1800);

    return ($result->EOF) ? false : $result->fields;
}

/**
 * Get either the retail or wholesale price, based on the current customer's wholesale status, for
 * the specified products_id.
 *
 * @param int $prid
 * @return string
 */
function zen_get_product_retail_or_wholesale_price(int $prid): string
{
    $product = zen_get_product_details($prid);
    return zen_get_retail_or_wholesale_price($product->fields['products_price'], $product->fields['products_price_w']);
}

/**
 * Get either the retail or wholesale price, based on the current customer's wholesale status.  Used
 * for product/attribute/discount-qty prices.
 *
 * @param mixed $retail_price
 * @param string $wholesale_pricing_tier
 * @return string
 */
function zen_get_retail_or_wholesale_price($retail_price, string $wholesale_pricing_tier): string
{
    // -----
    // If the customer is not a wholesale customer or no wholesale pricing was specified, return the retail price.
    //
    $retail_price = (float)$retail_price;
    if (Customer::isWholesaleCustomer() === false || empty($wholesale_pricing_tier)) {
        return number_format($retail_price, 4, '.', '');
    }

    // -----
    // Locate the wholesale tier pricing for this customer's pricing tier.  Search
    // starting at the end of the pricing tiers to locate the first not 'empty'
    // wholesale price at or below this customer's pricing tier.
    //
    // If a price isn't found, return the specified retail price.
    //
    $wholesale_index = Customer::getCustomerWholesaleTier() - 1;
    $pricing_tiers = explode('-', $wholesale_pricing_tier);
    if (!empty($pricing_tiers[$wholesale_index])) {
        $wholesale_price = $pricing_tiers[$wholesale_index];
    } else {
        $wholesale_index--;
        $last_tier_index = count($pricing_tiers) - 1;
        $start_index = ($last_tier_index < $wholesale_index) ? $last_tier_index : $wholesale_index;
        for ($i = $start_index; $i >= 0; $i--) {
            if (!empty($pricing_tiers[$i])) {
                $wholesale_price = $pricing_tiers[$i];
                break;
            }
        }
        if (!isset($wholesale_price)) {
            return number_format($retail_price, 4, '.', '');
        }
    }

    // -----
    // If the wholesale price is to be a percent-off the retail price, calculate
    // that prior to return.
    //
    if (str_ends_with($wholesale_price, '%')) {
        $wholesale_percent = rtrim($wholesale_price, '%');
        $wholesale_price = $retail_price * (100 - (float)$wholesale_percent) / 100;
    }
    return number_format($wholesale_price, 4, '.', '');
}
