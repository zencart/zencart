<?php
/**
 * functions_gvcoupons.php
 * Functions related to processing Gift Vouchers/Certificates and coupons
 *
 * @package functions
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: functions_gvcoupons.php 8844 2008-07-05 03:47:09Z drbyte $
 */

/**
 * Update the Customers GV account
 */
  function zen_gv_account_update($customer_id, $gv_id) {
    global $db;
    $customer_gv = $db->Execute("select amount
                                 from " . TABLE_COUPON_GV_CUSTOMER . "
                                 where customer_id = '" . (int)$customer_id . "'");

    $coupon_gv = $db->Execute("select coupon_amount
                               from " . TABLE_COUPONS . "
                               where coupon_id = '" . (int)$gv_id . "'");

    if ($customer_gv->RecordCount() > 0) {
      $new_gv_amount = $customer_gv->fields['amount'] + $coupon_gv->fields['coupon_amount'];
      $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . "
                                set amount = '" . $new_gv_amount . "'
                                where customer_id = '" . (int)$customer_id . "'");
    } else {
      $db->Execute("insert into " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) values ('" . (int)$customer_id . "', '" . $coupon_gv->fields['coupon_amount'] . "')");
    }
  }

    function zen_user_has_gv_account($c_id) {
      global $db;
      if ($_SESSION['customer_id'] || IS_ADMIN_FLAG === true) {
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
      $query = $db->Execute("select coupon_code
                             from " . TABLE_COUPONS . "
                             where coupon_code = '" . $prefix . $id1 . "'");
      if ($query->RecordCount() < 1 ) $good_result = 1;
    }
    return ($good_result == 1) ? $prefix . $id1 : ''; // blank means couldn't generate a unique code (typically because the max length was encountered before being able to generate unique)
  }
