<?php
/**
 * GV FAQ 
 * 
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Nov 14 Modified in v1.5.7 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_GV_FAQ');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

$customer_has_gv_balance = false;
$customer_gv_balance = 0;

if (zen_is_logged_in() && !zen_in_guest_checkout()) {

  $gv_query = "SELECT amount
               FROM " . TABLE_COUPON_GV_CUSTOMER . "
               WHERE customer_id = :customersID";

  $gv_query = $db->bindVars($gv_query, ':customersID', $_SESSION['customer_id'], 'integer');
  $gv_result = $db->Execute($gv_query);

  if ($gv_result->fields['amount'] > 0 ) {
    $customer_has_gv_balance = true;
    $customer_gv_balance = $currencies->format($gv_result->fields['amount']);
  }
}
$breadcrumb->add(NAVBAR_TITLE);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_GV_FAQ');
