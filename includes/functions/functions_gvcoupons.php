<?php
/**
 * functions_gvcoupons.php
 * Functions related to processing Gift Vouchers/Certificates
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */

/**
 * Update the Customer's GV account balance using the amount of the GV specified
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
    global $customer;
    if (!zen_is_logged_in() || zen_in_guest_checkout()) {
        return 0.00;
    }

    if (isset($customer) && is_a($customer, Customer::class) && ($customer_id === (int)$customer->getData('customers_id'))) {
        return $customer->getData('gv_balance');
    }

    $newCustomer = new Customer($customer_id);
    return $newCustomer->getData('gv_balance');
}

/**
 * @deprecated v2.0.0; use Coupon::generateRandomCouponCode() instead.
 */
function zen_create_coupon_code(string $salt = "secret", $length = SECURITY_CODE_LENGTH, string $prefix = '')
{
    return Coupon::generateRandomCouponCode($salt, $length, $prefix);
}

/**
 * @deprecated v2.0.0 use CouponValidation::is_coupon_valid_for_sales
 */
function is_coupon_valid_for_sales($product_id, $coupon_id): bool
{
    return CouponValidation::is_coupon_valid_for_sales($product_id, $coupon_id);
}

/**
 * @deprecated v2.0.0 use CouponValidation::is_product_valid
 */
function is_product_valid($product_id, $coupon_id): bool
{
    return CouponValidation::is_product_valid($product_id, $coupon_id);
}

/**
 * @deprecated v2.0.0 use CouponValidation::validate_for_category
 */
function validate_for_category(int $product_id, int $coupon_id)
{
    return CouponValidation::validate_for_category($product_id, $coupon_id);
}

/**
 * @deprecated v2.0.0 use CouponValidation::validate_for_product
 */
function validate_for_product(int $product_id, int $coupon_id)
{
    return CouponValidation::validate_for_product($product_id, $coupon_id);
}
