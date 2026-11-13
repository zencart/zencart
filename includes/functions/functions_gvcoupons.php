<?php
/**
 * functions_gvcoupons.php
 * Functions related to processing Gift Vouchers/Certificates
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

/**
 * Update the Customer's GV account balance using the amount of the GV specified
 *
 * @param int $customer_id
 * @param int $gv_id
 * @since ZC v1.0.3
 */
function zen_gv_account_update(int $customer_id, int $gv_id)
{
    global $customer;

    Customer::addCouponToGvBalance($customer_id, $gv_id);

    // keep a request-wide Customer instance's cached balance in step with the database
    if (isset($customer) && $customer instanceof Customer && $customer_id === (int)$customer->getData('customers_id')) {
        $customer->refreshGvBalance();
    }
}

/**
 * Return GV balance for customer
 *
 * @param int $customer_id
 * @return mixed|string
 * @since ZC v1.1.0
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
 * @since ZC v1.0.3
 */
function zen_create_coupon_code(string $salt = "secret", $length = SECURITY_CODE_LENGTH, string $prefix = '')
{
    return Coupon::generateRandomCouponCode($salt, $length, $prefix);
}

/**
 * @deprecated v2.0.0 use CouponValidation::is_coupon_valid_for_sales
 * @since ZC v1.5.6
 */
function is_coupon_valid_for_sales($product_id, $coupon_id): bool
{
    return CouponValidation::is_coupon_valid_for_sales($product_id, $coupon_id);
}

/**
 * @deprecated v2.0.0 use CouponValidation::is_product_valid
 * @since ZC v1.0.3
 */
function is_product_valid($product_id, $coupon_id): bool
{
    return CouponValidation::is_product_valid($product_id, $coupon_id);
}

/**
 * @deprecated v2.0.0 use CouponValidation::validate_for_category
 * @since ZC v1.3.0
 */
function validate_for_category(int $product_id, int $coupon_id)
{
    return CouponValidation::validate_for_category($product_id, $coupon_id);
}

/**
 * @deprecated v2.0.0 use CouponValidation::validate_for_product
 * @since ZC v1.3.0
 */
function validate_for_product(int $product_id, int $coupon_id)
{
    return CouponValidation::validate_for_product($product_id, $coupon_id);
}
