<?php

//namespace Tests\Support;
class CouponValidation
{
    /**
     * Product IDs that a test wants treated as on-sale (not valid for the coupon)
     */
    public static array $productsNotValidForSales = [];

    /**
     * Check whether the product is valid for the specified coupon, according to model/category/product restrictions assigned to the coupon
     */
    public static function is_product_valid(int $product_id, int $coupon_id): bool
    {
        return true;
    }

    /**
     * Check whether the product is assigned to a category which is allowed for the coupon ID
     */
    public static function validate_for_category(int $product_id, int $coupon_id): bool|string
    {
        return 'none';
    }

    /**
     * is coupon valid for specials and sales
     */
    public static function is_coupon_valid_for_sales(int $product_id, int $coupon_id): bool
    {
        return !in_array($product_id, self::$productsNotValidForSales, true);
    }

    /**
     * Check whether coupon ID is valid for the specified product
     */
    public static function validate_for_product(int $product_id, int $coupon_id): bool|string
    {
        return 'none';
    }
}
