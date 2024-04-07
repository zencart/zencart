<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 03 Modified in v2.0.0 $
 */

class CouponValidation
{

    /**
     * Check whether the product is valid for the specified coupon, according to model/category/product restrictions assigned to the coupon
     */
    public static function is_product_valid(int $product_id, int $coupon_id): bool
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

        if (str_starts_with($product->fields['products_model'], 'GIFT')) {
            return false;
        }

        // modified to manage restrictions better - leave commented for now
        if ($coupons->RecordCount() === 0) {
            return true;
        }
        if ($coupons->RecordCount() === 1) {
            // If product is restricted(deny) and is same as tested product deny
            if ($coupons->fields['product_id'] > 0 && $coupons->fields['product_id'] == $product_id && $coupons->fields['coupon_restrict'] === 'Y') {
                return false;
            }
            // If product is not restricted(allow) and is not same as tested product deny
            if ($coupons->fields['product_id'] > 0 && $coupons->fields['product_id'] != $product_id && $coupons->fields['coupon_restrict'] === 'N') {
                return false;
            }
            // if category is restricted(deny) and product in category deny
            if ($coupons->fields['category_id'] > 0 && zen_product_in_category($product_id, $coupons->fields['category_id']) && $coupons->fields['coupon_restrict'] === 'Y') {
                return false;
            }
            // if category is not restricted(allow) and product not in category deny
            if ($coupons->fields['category_id'] > 0 && !zen_product_in_category($product_id, $coupons->fields['category_id']) && $coupons->fields['coupon_restrict'] === 'N') {
                return false;
            }
            return true;
        }

        $allow_for_category = self::validate_for_category($product_id, $coupon_id);
        $allow_for_product = self::validate_for_product($product_id, $coupon_id);
//    echo '#'.$product_id . '#' . $allow_for_category;
//    echo '#'.$product_id . '#' . $allow_for_product;
        if ($allow_for_category === 'none') {
            if ($allow_for_product === 'none') {
                return true;
            }
            if ($allow_for_product === true) {
                return true;
            }
            if ($allow_for_product === false) {
                return false;
            }
        }
        if ($allow_for_category === true) {
            if ($allow_for_product === 'none') {
                return true;
            }
            if ($allow_for_product === true) {
                return true;
            }
            if ($allow_for_product === false) {
                return false;
            }
        }
        if ($allow_for_category === false) {
            if ($allow_for_product === 'none') {
                return false;
            }
            if ($allow_for_product === true) {
                return true;
            }
            if ($allow_for_product === false) {
                return false;
            }
        }
        return false; //should never get here
    }

    /**
     * Check whether the product is assigned to a category which is allowed for the coupon ID
     */
    public static function validate_for_category(int $product_id, int $coupon_id): bool|string
    {
        global $db;
        $productCatPath = zen_get_product_path($product_id);
        $catPathArray = array_reverse(explode('_', $productCatPath));
        $sql = "SELECT count(*) AS total
                FROM " . TABLE_COUPON_RESTRICT . "
                WHERE category_id = -1
                AND coupon_restrict = 'Y'
                AND coupon_id = " . (int)$coupon_id;
        $checkQuery = $db->Execute($sql, 1);
        foreach ($catPathArray as $catPath) {
            $sql = "SELECT * FROM " . TABLE_COUPON_RESTRICT . "
                    WHERE category_id = " . (int)$catPath . "
                    AND coupon_id = " . (int)$coupon_id;
            $result = $db->Execute($sql, 1);
            if ($result->RecordCount()) {
                if ($result->fields['coupon_restrict'] === 'N') {
                    return true;
                }
                if ($result->fields['coupon_restrict'] === 'Y') {
                    return false;
                }
            }
        }
        if ($checkQuery->fields['total'] > 0) {
            return false;
        }

        return 'none';
    }

    /**
     * is coupon valid for specials and sales
     */
    public static function is_coupon_valid_for_sales(int $product_id, int $coupon_id): bool
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
     * Check whether coupon ID is valid for the specified product
     */
    public static function validate_for_product(int $product_id, int $coupon_id): bool|string
    {
        global $db;
        $sql = "SELECT * FROM " . TABLE_COUPON_RESTRICT . "
                WHERE product_id = " . (int)$product_id . "
                AND coupon_id = " . (int)$coupon_id . " LIMIT 1";
        $result = $db->Execute($sql);
        if ($result->RecordCount()) {
            if ($result->fields['coupon_restrict'] === 'N') {
                return true;
            }
            if ($result->fields['coupon_restrict'] === 'Y') {
                return false;
            }
        }
        return 'none';
    }

    /**
     * Check if a referrer is already assigned to a coupon.
     * Because only one coupon can be active at a time, we can only support
     * a one-to-one relationship between coupons and referrers.
     * e.g. referrer 'abc.com' may only be assigned to one coupon, not two or more.
     *
     * @param string $referrer The domain to check e.g. 'abc.com'
     * @param int $exclude_coupon_id Optional coupon_id to exclude/ignore (ie: "self" record)
     * @return ?array
     */
    public static function referrer_already_assigned(string $referrer, ?int $exclude_coupon_id = null): ?array
    {
        global $db;
        $sql = "SELECT c.coupon_id, coupon_code
                FROM " . TABLE_COUPONS . " c
                LEFT JOIN " . TABLE_COUPON_REFERRERS . " r ON (c.coupon_id = r.coupon_id)
                WHERE referrer_domain = :referrer";
        $sql = $db->bindVars($sql, ':referrer', $referrer, 'string');
        if (!empty($exclude_coupon_id)) {
            $sql .= " AND c.coupon_id <> $exclude_coupon_id";
        }

        $result = $db->Execute($sql);

        return $result->RecordCount() !== 0 ? $result->fields : null;
    }
}
