<?php
/**
 * functions_gvcoupons.php
 * Functions related to processing Gift Vouchers/Certificates and coupons
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Dec 16 Modified in v1.5.7 $
 */

////
// Update the Customers GV account
  function zen_gv_account_update($c_id, $gv_id) {
    global $db;
    $customer_gv_query = "select amount
                          from " . TABLE_COUPON_GV_CUSTOMER . "
                          where customer_id = '" . (int)$c_id . "'";

    $customer_gv = $db->Execute($customer_gv_query);
    $coupon_gv_query = "select coupon_amount
                        from " . TABLE_COUPONS . "
                        where coupon_id = '" . (int)$gv_id . "'";

    $coupon_gv = $db->Execute($coupon_gv_query);

    if ($customer_gv->RecordCount() > 0) {

      $new_gv_amount = $customer_gv->fields['amount'] + $coupon_gv->fields['coupon_amount'];
      $gv_query = "update " . TABLE_COUPON_GV_CUSTOMER . "
                   set amount = '" . $new_gv_amount . "' where customer_id = '" . (int)$c_id . "'";

      $db->Execute($gv_query);

    } else {

      $gv_query = "insert into " . TABLE_COUPON_GV_CUSTOMER . "
                                   (customer_id, amount)
                          values ('" . (int)$c_id . "', '" . $coupon_gv->fields['coupon_amount'] . "')";

      $db->Execute($gv_query);
    }
  }

    function zen_user_has_gv_account($c_id) {
      global $db;
      if (zen_is_logged_in() && !zen_in_guest_checkout()) {
        $gv_result = $db->Execute("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . (int)$c_id . "'");
        if ($gv_result->RecordCount() > 0) {
          if ($gv_result->fields['amount'] > 0) {
            return $gv_result->fields['amount'];
          }
        }
        return '0.00';
      } else {
        return '0.00';
      }
    }

/**
 * Create a Coupon Code. Returns blank if cannot generate a unique code using the passed criteria.
 * @param string $salt - this is an optional string to help seed the random code with greater entropy
 * @param int $length - this is the desired length of the generated code
 * @param string $prefix - include a prefix string if you want to force the generated code to start with a specific string
 * @return string (new coupon code) (will be blank if the function failed)
 */
  function zen_create_coupon_code($salt="secret", $length=SECURITY_CODE_LENGTH, $prefix = '') {
    global $db;
    $length = (int)$length;
    static $max_db_length;
    if (!isset($max_db_length)) $max_db_length = zen_field_length(TABLE_COUPONS, 'coupon_code');  // schema is normally max 32 chars for this field
    if ($length > $max_db_length) $length = $max_db_length;
    if (strlen($prefix) > $max_db_length) return ''; // if prefix is already too long for the db, we can't generate a new code
    if (strlen($prefix) + (int)$length > $max_db_length) $length = $max_db_length - strlen($prefix);
    if ($length < 4) return ''; // if the recalculated length (esp in respect to prefixes) is less than 4 (for very basic entropy) then abort
    $ccid = md5(uniqid("",$salt));
    $ccid .= md5(uniqid("",$salt));
    $ccid .= md5(uniqid("",$salt));
    $ccid .= md5(uniqid("",$salt));
    srand((double)microtime()*1000000); // seed the random number generator
    $good_result = 0;
    $id1 = '';
    while ($good_result == 0) {
      $random_start = @rand(0, (128-$length));
      $id1=substr($ccid, $random_start, $length);
      $sql = "select coupon_code
              from " . TABLE_COUPONS . "
              where coupon_code = :couponcode";
      $sql = $db->bindVars($sql, ':couponcode', $prefix . $id1, 'string');
      $result = $db->Execute($sql);
      if ($result->RecordCount() < 1 ) $good_result = 1;
    }
    return ($good_result == 1) ? $prefix . $id1 : ''; // blank means couldn't generate a unique code (typically because the max length was encountered before being able to generate unique)
  }


/**
 * is coupon valid for specials and sales
 * @param int $product_id
 * @param int $coupon_id
 * @return bool
 */
  function is_coupon_valid_for_sales($product_id, $coupon_id) {
    global $db;
    $sql = "SELECT coupon_id, coupon_is_valid_for_sales
            FROM " . TABLE_COUPONS . "
            WHERE coupon_id = " . (int)$coupon_id;

    $result = $db->Execute($sql);

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
