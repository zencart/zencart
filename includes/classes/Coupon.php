<?php
declare(strict_types=1);
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 17 Modified in v2.1.0 $
 */

class Coupon extends base
{
    public static function codeExists(string $code): bool
    {
        global $db;

        $sql = "SELECT coupon_code
                FROM " . TABLE_COUPONS . "
                WHERE coupon_code = :couponcode";
        $sql = $db->bindVars($sql, ':couponcode', $code, 'string');

        return $db->Execute($sql)->RecordCount() > 0;
    }

    public static function disable(int|string $coupon_id): void
    {
        global $db;
        $sql = "UPDATE " . TABLE_COUPONS . "
                SET coupon_active = 'N'
                WHERE coupon_id = " . (int)$coupon_id;
        $db->Execute($sql);
    }

    public static function enable(int|string $coupon_id): void
    {
        global $db;
        $sql = "UPDATE " . TABLE_COUPONS . "
                SET coupon_active = 'Y'
                WHERE coupon_id = " . (int)$coupon_id;
        $db->Execute($sql);
    }

    public static function deleteDuplicates(string $origin_prefix): array
    {
        global $db;
        $results = [];

        // report if attempted change matches the welcome coupon, because we will skip it
        $sql = "SELECT coupon_id
                FROM " . TABLE_COUPONS . "
                WHERE coupon_code LIKE ':original_code:%'
                AND coupon_id = " . (int)NEW_SIGNUP_DISCOUNT_COUPON;
        $sql = $db->bindVars($sql, ':original_code:', $origin_prefix, 'noquotestring');
        $delete_duplicate_coupons_check = $db->Execute($sql);
        if ($delete_duplicate_coupons_check->RecordCount() > 0) {
            $results['welcome_coupon'] = true;
        }

        // Find duplicates (that are not also the Welcome coupon) (and are not 'G' (GV) records)
        $sql = "SELECT coupon_id, coupon_code
                FROM " . TABLE_COUPONS . "
                WHERE coupon_code LIKE ':original_code:%'
                AND coupon_active = 'Y'
                AND coupon_id !=  " . (int)NEW_SIGNUP_DISCOUNT_COUPON . "
                AND coupon_type != 'G'";
        $sql = $db->bindVars($sql, ':original_code:', $origin_prefix, 'noquotestring');
        $delete_duplicate_coupons = $db->Execute($sql);

        // disable them
        foreach ($delete_duplicate_coupons as $delete_duplicate_coupon) {
            static::disable($delete_duplicate_coupon['coupon_id']);
            $results['deleted'][] = $delete_duplicate_coupon['coupon_code'];
        }

        return $results;
    }

    public static function make_duplicates(int|string $original_id, int|string $new_code, int $quantity): bool
    {
        for ($i = 1; $i <= $quantity; $i++) {
            $old_code_length = strlen($new_code);
            $minimum_extra_chars = 7;
            $delta_calculation = SECURITY_CODE_LENGTH - ($old_code_length + $minimum_extra_chars);
            $new_code_length = ($delta_calculation > 0) ? $minimum_extra_chars + $delta_calculation : $minimum_extra_chars;

            $generated_code = static::generateRandomCouponCode((string)$original_id, $new_code_length, $new_code);
            if ($generated_code === '') {
                // cannot create code
                return false;
            }

            static::clone($original_id, $generated_code);
        }
        return true;
    }

    public static function clone(int|string $original_id, string $new_code): int|false
    {
        global $db;

        // check if new coupon code already exists
        if (static::codeExists($new_code)) {
            return false;
        }

        $sql = "SELECT *
                FROM " . TABLE_COUPONS . "
                WHERE coupon_id = " . (int)$original_id;
        $copied_coupon = $db->Execute($sql);

        // create duplicate coupon
        $sql_data_array = [
            'coupon_code' => zen_db_prepare_input($new_code),
            'coupon_amount' => zen_db_prepare_input($copied_coupon->fields['coupon_amount']),
            'coupon_product_count' => (int)$copied_coupon->fields['coupon_product_count'],
            'coupon_type' => zen_db_prepare_input($copied_coupon->fields['coupon_type']),
            'uses_per_coupon' => (int)$copied_coupon->fields['uses_per_coupon'],
            'uses_per_user' => (int)$copied_coupon->fields['uses_per_user'],
            'coupon_minimum_order' => (float)$copied_coupon->fields['coupon_minimum_order'],
            'restrict_to_products' => zen_db_prepare_input($copied_coupon->fields['restrict_to_products']),
            'restrict_to_categories' => zen_db_prepare_input($copied_coupon->fields['restrict_to_categories']),
            'coupon_start_date' => $copied_coupon->fields['coupon_start_date'],
            'coupon_expire_date' => $copied_coupon->fields['coupon_expire_date'],
            'date_created' => 'now()',
            'date_modified' => 'now()',
            'coupon_zone_restriction' => (int)$copied_coupon->fields['coupon_zone_restriction'],
            'coupon_calc_base' => (int)$copied_coupon->fields['coupon_calc_base'],
            'coupon_order_limit' => (int)$copied_coupon->fields['coupon_order_limit'],
            'coupon_is_valid_for_sales' => (int)$copied_coupon->fields['coupon_is_valid_for_sales'],
            'coupon_active' => 'Y',
        ];

        zen_db_perform(TABLE_COUPONS, $sql_data_array);
        $cid = $db->insert_ID();

        // create duplicate coupon description
        $sql = "SELECT *
                FROM " . TABLE_COUPONS_DESCRIPTION . "
                WHERE coupon_id = " . (int)$original_id;
        $new_coupon_descriptions = $db->Execute($sql);

        foreach ($new_coupon_descriptions as $new_coupon_description) {
            $sql_mdata_array = [
                'coupon_id' => (int)$cid,
                'language_id' => (int)$new_coupon_description['language_id'],
                'coupon_name' => zen_db_prepare_input('COPY: ' . $new_coupon_description['coupon_name']),
                'coupon_description' => zen_db_prepare_input($new_coupon_description['coupon_description']),
            ];
            zen_db_perform(TABLE_COUPONS_DESCRIPTION, $sql_mdata_array);
        }

        // copy restrictions
        $sql = "SELECT *
                FROM " . TABLE_COUPON_RESTRICT . "
                WHERE coupon_id = " . (int)$original_id;
        $copy_coupon_restrictions = $db->Execute($sql);

        foreach ($copy_coupon_restrictions as $copy_coupon_restriction) {
            $sql_rdata_array = [
                'coupon_id' => (int)$cid,
                'product_id' => (int)$copy_coupon_restriction['product_id'],
                'category_id' => (int)$copy_coupon_restriction['category_id'],
                'coupon_restrict' => zen_db_prepare_input($copy_coupon_restriction['coupon_restrict']),
            ];
            zen_db_perform(TABLE_COUPON_RESTRICT, $sql_rdata_array);
        }

        return $cid;
    }

    /**
     * Create a Coupon Code. Returns blank if cannot generate a unique code using the passed criteria.
     * @param string $salt - this is an optional string to help seed the random code with greater entropy
     * @param int $length - this is the desired length of the generated code; will be ignored if longer than length of db field
     * @param string $prefix - include a prefix string if you want to force the generated code to start with a specific string
     * @return string (new coupon code) (will be blank if the function failed)
     */
    public static function generateRandomCouponCode(string $salt = "secret", $length = SECURITY_CODE_LENGTH, string $prefix = ''): string
    {
        $length = (int)$length;
        static $max_db_length;

        if (!isset($max_db_length)) {
            // schema is normally max 32 chars for this field
            $max_db_length = zen_field_length(TABLE_COUPONS, 'coupon_code');
        }
        if ($length > $max_db_length) {
            $length = $max_db_length;
        }
        if (strlen($prefix) > $max_db_length) {
            // if prefix is already too long for the db, we can't generate a new code
            return '';
        }
        if (strlen($prefix) + (int)$length > $max_db_length) {
            $length = $max_db_length - strlen($prefix);
        }
        if ($length < 4) {
            // if the recalculated length (esp in respect to prefixes) is less than 4 (for very basic entropy) then abort
            return '';
        }
        $random_string = bin2hex(random_bytes(128)); // 128 generates 256 chars
        $random_string_length = strlen($random_string);
        if ($length > ($random_string_length / 4)) {
            // safeguard: this should never happen, but can't pass a negative max-number to random_int when min is 0 (which we need)
            $length = (int)($length / 4);
        }
        for ($i = 0; $i < $random_string_length; $i++) {
            $random_start = random_int(0, ($random_string_length - $length));
            $new_code = substr($random_string, $random_start, $length);

            $new_code = strtoupper($new_code);

            if (!static::codeExists($prefix . $new_code)) {
                return $prefix . $new_code;
            }
        }

        // blank means couldn't generate a unique code (typically because the max length was encountered before being able to generate unique)
        return '';
    }

    public static function getAllCouponsByName(): array
    {
        global $db;
        $results = $db->Execute("SELECT cd.coupon_name, c.coupon_id, c.coupon_code
                                FROM " . TABLE_COUPONS . " c, " . TABLE_COUPONS_DESCRIPTION . " cd
                                WHERE cd.coupon_id = c.coupon_id
                                AND cd.language_id = " . (int)$_SESSION['languages_id']);

        $coupons = [];
        foreach ($results as $coupon) {
            $coupons[] = [
                'coupon_id' => $coupon['coupon_id'],
                'coupon_name' => $coupon['coupon_name'],
                'coupon_code' => $coupon['coupon_code'],
            ];
        }

        return $coupons;
    }
}
