<?php
/**
 * paypal EC button display template
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_ec_button.php drbyte  Modified in v1.6.0 $
 */

$paypalec_enabled = (defined('MODULE_PAYMENT_PAYPALWPP_STATUS') && MODULE_PAYMENT_PAYPALWPP_STATUS == 'True');
$ecs_off = (defined('MODULE_PAYMENT_PAYPALWPP_ECS_BUTTON') && MODULE_PAYMENT_PAYPALWPP_ECS_BUTTON == 'Off');
if ($ecs_off) $paypalec_enabled = FALSE;

if ($paypalec_enabled) {
  // check if logged-in customer's address is in an acceptable zone
  if ((int)MODULE_PAYMENT_PAYPALWPP_ZONE > 0 && isset($_SESSION['customer_id']) && (int)$_SESSION['customer_id'] > 0) {
    $custCountryCheck = (isset($order)) ? $order->billing['country']['id'] : $_SESSION['customer_country_id'];
    $custZoneCheck = (isset($order)) ? $order->billing['zone_id'] : $_SESSION['customer_zone_id'];
    $check_flag = false;
    $sql = "SELECT zone_id
            FROM " . TABLE_ZONES_TO_GEO_ZONES . "
            WHERE geo_zone_id = :zoneId
            AND zone_country_id = :countryId
            ORDER BY zone_id";
    $sql = $db->bindVars($sql, ':zoneId', (int)MODULE_PAYMENT_PAYPALWPP_ZONE, 'integer');
    $sql = $db->bindVars($sql, ':countryId', $custCountryCheck, 'integer');
    $result = $db->Execute($sql);
    while (!$result->EOF) {
      if ($result->fields['zone_id'] < 1 || $result->fields['zone_id'] == $custZoneCheck) {
        $check_flag = true;
        break;
      }
      $result->MoveNext();
    }
    if (!$check_flag) {
      $paypalec_enabled = false;
    }
  }

  // cart contents qty must be >0 and value >0
  if ($_SESSION['cart']->count_contents() <= 0 || $_SESSION['cart']->total == 0) {
    $paypalec_enabled = false;
  }

  // PayPal module cannot be used for purchase > $10,000 USD equiv
  if ($currencies->value($_SESSION['cart']->total, true, 'USD') > 10000) {
    $paypalec_enabled = false;
  }
}
// if all is okay, display the button
if ($paypalec_enabled) {
    // If they're here, they're either about to go to PayPal or were
    // sent back by an error, so clear these session vars.
    unset($_SESSION['paypal_ec_temp']);
    unset($_SESSION['paypal_ec_token']);
    unset($_SESSION['paypal_ec_payer_id']);
    unset($_SESSION['paypal_ec_payer_info']);

    include zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/','paypalwpp.php', 'false');
?>
<div id="PPECbutton" class="buttonRow">
  <a href="<?php echo zen_href_link('ipn_main_handler.php', 'type=ec', 'SSL', true, true, true); ?>"><img src="<?php echo MODULE_PAYMENT_PAYPALWPP_EC_BUTTON_IMG ?>" alt="<?php echo MODULE_PAYMENT_PAYPALWPP_TEXT_BUTTON_ALTTEXT; ?>" /></a>
</div>
<?php
}
?>
