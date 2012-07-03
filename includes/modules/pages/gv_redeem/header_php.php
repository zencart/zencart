<?php
/**
 * GV redeem
 *
 * @package page
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 6736 2007-08-19 09:55:01Z drbyte $
 */

// if the customer is not logged on, redirect them to the login page
if (!$_SESSION['customer_id']) {
  $_SESSION['navigation']->set_snapshot();
  zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}
// check for a voucher number in the url
if (isset($_GET['gv_no'])) {
  $error = true;
  $gv_query = "SELECT c.coupon_id, c.coupon_amount
               FROM " . TABLE_COUPONS . " c, " . TABLE_COUPON_EMAIL_TRACK . " et
               WHERE coupon_code = :couponCode
               AND c.coupon_id = et.coupon_id
               AND c.coupon_type = 'G'";

  $gv_query = $db->bindVars($gv_query, ':couponCode', $_GET['gv_no'], 'string');
  $coupon = $db->Execute($gv_query);

  if ($coupon->RecordCount() >0) {
    $redeem_query = "SELECT coupon_id
                     FROM ". TABLE_COUPON_REDEEM_TRACK . "
                     WHERE coupon_id = :couponID";

    $redeem_query = $db->bindVars($redeem_query, ':couponID', $coupon->fields['coupon_id'], 'integer');
    $redeem = $db->Execute($redeem_query);

    if ($redeem->RecordCount() == 0 ) {
      // check for required session variables
      $_SESSION['gv_id'] = $coupon->fields['coupon_id'];
      $error = false;
    } else {
      $error = true;
    }
  }
} else {
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
}
if ((!$error) && ($_SESSION['customer_id'])) {
  // Update redeem status
  $gv_query = "INSERT INTO  " . TABLE_COUPON_REDEEM_TRACK . "(coupon_id, customer_id, redeem_date, redeem_ip)
               VALUES (:couponID, :customersID, now(), :remoteADDR)";

  $gv_query = $db->bindVars($gv_query, ':customersID', $_SESSION['customer_id'], 'integer');
  $gv_query = $db->bindVars($gv_query, ':couponID', $coupon->fields['coupon_id'], 'integer');
  $gv_query = $db->bindVars($gv_query, ':remoteADDR', zen_get_ip_address(), 'string');
  $db->Execute($gv_query);

  $gv_update = "UPDATE " . TABLE_COUPONS . "
                SET coupon_active = 'N'
                WHERE coupon_id = :couponID";

  $gv_update = $db->bindVars($gv_update, ':couponID', $coupon->fields['coupon_id'], 'integer');
  $db->Execute($gv_update);

  zen_gv_account_update($_SESSION['customer_id'], $_SESSION['gv_id']);
  $_SESSION['gv_id'] = '';
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE);

// prepare message for display in template:
$message = sprintf(TEXT_VALID_GV, $currencies->format($coupon->fields['coupon_amount']));

if ($error) {
  // if we get here then either the URL gv_no param was not set or it was invalid
  // so output a message.
  $message = TEXT_INVALID_GV;
}

?>