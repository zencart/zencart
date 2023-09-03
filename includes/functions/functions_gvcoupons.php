<?php
/**
 * functions_gvcoupons.php
 * Functions related to processing Gift Vouchers/Certificates and coupons
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Feb 23 Modified in v1.5.8a $
 */

/**
 * Update the Customers GV account balance using the amount of the GV specified
 *
 * @param int $customer_id
 * @param int $gv_id
 */
function zen_gv_account_update(int $customer_id, int $gv_id)
{
    global $db;
    $sql = "SELECT amount
            FROM " . TABLE_COUPON_GV_CUSTOMER . "
            WHERE customer_id = " . (int)$customer_id;

    $customer_gv = $db->Execute($sql);

    $sql = "SELECT coupon_amount
            FROM " . TABLE_COUPONS . "
            WHERE coupon_id = " . (int)$gv_id;

    $coupon_gv = $db->Execute($sql);

    if ($coupon_gv->EOF) return;

    if ($customer_gv->RecordCount() > 0) {
        $new_gv_amount = $customer_gv->fields['amount'] + $coupon_gv->fields['coupon_amount'];
        $sql = "UPDATE " . TABLE_COUPON_GV_CUSTOMER . "
              SET amount = '" . $db->prepare_input($new_gv_amount) . "' WHERE customer_id = " . (int)$customer_id;
        $db->Execute($sql);

    } else {
        $sql = "INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount)
                VALUES (" . (int)$customer_id . ", '" . $db->prepare_input($coupon_gv->fields['coupon_amount']) . "')";
        $db->Execute($sql);
    }
}

/**
 * Return GV balance for customer
 *
 * @param int $customer_id
 * @return mixed|string
 */
function zen_user_has_gv_account(int $customer_id)
{
    global $db, $customer;
    if (!zen_is_logged_in() || zen_in_guest_checkout()) {
        return '0.00';
    }

    if (isset($customer) && is_a($customer, Customer::class) && ($customer_id === (int)$customer->getData('customers_id'))) {
        return $customer->getData('gv_balance');
    }

    $newCustomer = new Customer($customer_id);
    return $newCustomer->getData('gv_balance');
}

/**
 * Create a Coupon Code. Returns blank if cannot generate a unique code using the passed criteria.
 * @param string $salt - this is an optional string to help seed the random code with greater entropy
 * @param int $length - this is the desired length of the generated code
 * @param string $prefix - include a prefix string if you want to force the generated code to start with a specific string
 * @return string (new coupon code) (will be blank if the function failed)
 */
function zen_create_coupon_code(string $salt = "secret", $length = SECURITY_CODE_LENGTH, string $prefix = '')
{
    global $db;
    $length = (int)$length;
    static $max_db_length;
    if (!isset($max_db_length)) $max_db_length = zen_field_length(TABLE_COUPONS, 'coupon_code');  // schema is normally max 32 chars for this field
    if ($length > $max_db_length) $length = $max_db_length;
    if (strlen($prefix) > $max_db_length) return ''; // if prefix is already too long for the db, we can't generate a new code
    if (strlen($prefix) + (int)$length > $max_db_length) $length = $max_db_length - strlen($prefix);
    if ($length < 4) return ''; // if the recalculated length (esp in respect to prefixes) is less than 4 (for very basic entropy) then abort
    $ccid = md5(uniqid("", $salt));
    $ccid .= md5(uniqid("", $salt));
    $ccid .= md5(uniqid("", $salt));
    $ccid .= md5(uniqid("", $salt));
    srand((double)microtime() * 1000000); // seed the random number generator
    $good_result = 0;
    $id1 = '';
    while ($good_result == 0) {
        $random_start = @rand(0, (128 - $length));
        $id1 = substr($ccid, $random_start, $length);
        $sql = "SELECT coupon_code
                FROM " . TABLE_COUPONS . "
                WHERE coupon_code = :couponcode";
        $sql = $db->bindVars($sql, ':couponcode', $prefix . $id1, 'string');
        $result = $db->Execute($sql);
        if ($result->RecordCount() < 1) $good_result = 1;
    }
    return ($good_result == 1) ? $prefix . $id1 : ''; // blank means couldn't generate a unique code (typically because the max length was encountered before being able to generate unique)
}


/**
 * is coupon valid for specials and sales
 * @param int $product_id
 * @param int $coupon_id
 * @return bool
 */
function is_coupon_valid_for_sales($product_id, $coupon_id): bool
{
    global $db;
    $sql = "SELECT coupon_id, coupon_is_valid_for_sales
            FROM " . TABLE_COUPONS . "
            WHERE coupon_id = " . (int)$coupon_id;

    $result = $db->Execute($sql);

    if ($result->EOF) {
        return false;
    }

    // check whether coupon has been flagged for not valid with sales
    if (!empty($result->fields['coupon_is_valid_for_sales'])) {
        return true;
    }

    // check for any special on $product_id
    $chk_product_on_sale = zen_get_products_special_price($product_id, true);
    if (!$chk_product_on_sale) {
        // check for any sale on $product_id
        $chk_product_on_sale = zen_get_products_special_price($product_id, false);
    }
    if ($chk_product_on_sale) {
        return false;
    }
    return true; // is on special or sale
}


/**
 * Check whether the product is valid for the specified coupon, according to model/category/product restrictions assigned to the coupon
 * @param int $product_id
 * @param int $coupon_id
 * @return bool
 */
function is_product_valid($product_id, $coupon_id): bool
{
    global $db;

    $product_id = (int)$product_id;

    $coupons_query = "SELECT * FROM " . TABLE_COUPON_RESTRICT . "
                      WHERE coupon_id = " . (int)$coupon_id . "
                      ORDER BY coupon_restrict ASC";

    $coupons = $db->Execute($coupons_query);

    $product_query = "SELECT products_model FROM " . TABLE_PRODUCTS . "
                      WHERE products_id = $product_id";

    $product = $db->Execute($product_query);

    if (preg_match('/^GIFT/', $product->fields['products_model'])) {
        return false;
    }

    // modified to manage restrictions better - leave commented for now
    if ($coupons->RecordCount() == 0) return true;
    if ($coupons->RecordCount() == 1) {
        // If product is restricted(deny) and is same as tested product deny
        if (($coupons->fields['product_id'] != 0) && $coupons->fields['product_id'] == $product_id && $coupons->fields['coupon_restrict'] == 'Y') return false;
        // If product is not restricted(allow) and is not same as tested product deny
        if (($coupons->fields['product_id'] != 0) && $coupons->fields['product_id'] != $product_id && $coupons->fields['coupon_restrict'] == 'N') return false;
        // if category is restricted(deny) and product in category deny
        if (($coupons->fields['category_id'] != 0) && (zen_product_in_category($product_id, $coupons->fields['category_id'])) && ($coupons->fields['coupon_restrict'] == 'Y')) return false;
        // if category is not restricted(allow) and product not in category deny
        if (($coupons->fields['category_id'] != 0) && (!zen_product_in_category($product_id, $coupons->fields['category_id'])) && ($coupons->fields['coupon_restrict'] == 'N')) return false;
        return true;
    }
    $allow_for_category = validate_for_category($product_id, $coupon_id);
    $allow_for_product = validate_for_product($product_id, $coupon_id);
//    echo '#'.$product_id . '#' . $allow_for_category;
//    echo '#'.$product_id . '#' . $allow_for_product;
    if ($allow_for_category == 'none') {
        if ($allow_for_product === 'none') return true;
        if ($allow_for_product === true) return true;
        if ($allow_for_product === false) return false;
    }
    if ($allow_for_category === true) {
        if ($allow_for_product === 'none') return true;
        if ($allow_for_product === true) return true;
        if ($allow_for_product === false) return false;
    }
    if ($allow_for_category === false) {
        if ($allow_for_product === 'none') return false;
        if ($allow_for_product === true) return true;
        if ($allow_for_product === false) return false;
    }
    return false; //should never get here
}

/**
 * Check whether the product is assigned to a category which is allowed for the coupon ID
 * @param int $product_id
 * @param int $coupon_id
 * @return bool|string
 */
function validate_for_category(int $product_id, int $coupon_id)
{
    global $db;
    $productCatPath = zen_get_product_path($product_id);
    $catPathArray = array_reverse(explode('_', $productCatPath));
    $sql = "SELECT count(*) AS total
            FROM " . TABLE_COUPON_RESTRICT . "
            WHERE category_id = -1
            AND coupon_restrict = 'Y'
            AND coupon_id = " . (int)$coupon_id . " LIMIT 1";
    $checkQuery = $db->Execute($sql);
    foreach ($catPathArray as $catPath) {
        $sql = "SELECT * FROM " . TABLE_COUPON_RESTRICT . "
                WHERE category_id = " . (int)$catPath . "
                AND coupon_id = " . (int)$coupon_id;
        $result = $db->Execute($sql);
        if ($result->recordCount() > 0 && $result->fields['coupon_restrict'] == 'N') return true;
        if ($result->recordCount() > 0 && $result->fields['coupon_restrict'] == 'Y') return false;
    }
    if ($checkQuery->fields['total'] > 0) {
        return false;
    }

    return 'none';
}

/**
 * Check whether coupon ID is valid for the specified product
 *
 * @param int $product_id
 * @param int $coupon_id
 * @return bool|string
 */
function validate_for_product(int $product_id, int $coupon_id)
{
    global $db;
    $sql = "SELECT * FROM " . TABLE_COUPON_RESTRICT . "
            WHERE product_id = " . (int)$product_id . "
            AND coupon_id = " . (int)$coupon_id . " LIMIT 1";
    $result = $db->Execute($sql);
    if ($result->RecordCount()) {
        if ($result->fields['coupon_restrict'] == 'N') return true;
        if ($result->fields['coupon_restrict'] == 'Y') return false;
    }
    return 'none';
}
