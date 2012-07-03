<?php
/**
 * functions_gvcoupons.php
 * Functions related to processing Gift Vouchers/Certificates and coupons
 *
 * @package functions
 * @copyright Copyright 2003-2008 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: functions_gvcoupons.php 8844 2008-07-05 03:47:09Z drbyte $
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
      if ($_SESSION['customer_id']) {
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

////
// Create a Coupon Code. length may be between 1 and 16 Characters
// $salt needs some thought.

  function zen_create_coupon_code($salt="secret", $length = SECURITY_CODE_LENGTH) {
    global $db;
    $ccid = md5(uniqid("", $salt));
    $ccid .= md5(uniqid("", $salt));
    $ccid .= md5(uniqid("", $salt));
    $ccid .= md5(uniqid("", $salt));
    srand((double)microtime()*1000000); // seed the random number generator
    $random_start = @rand(0, (128-$length));
    $good_result = 0;
    while ($good_result == 0) {
      $id1=substr($ccid, $random_start,$length);
      $query = "select coupon_code
                from " . TABLE_COUPONS . "
                where coupon_code = '" . $id1 . "'";

      $rs = $db->Execute($query);

      if ($rs->RecordCount() == 0) $good_result = 1;
    }
    return $id1;
  }
?>