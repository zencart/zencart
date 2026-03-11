<?php
/**
 * Page-Redirect Listener for PayPal RESTful API payment method (paypalr)
 *
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 *
 * Last updated: v1.3.0
 */
require 'includes/application_top.php';

// -----
// If the paypalr payment module is not installed or is totally disabled, nothing further to be
// done here.  Kill any session and whitescreen since it's an invalid access.
//
if (!defined('MODULE_PAYMENT_PAYPALR_STATUS') || MODULE_PAYMENT_PAYPALR_STATUS === 'False') {
    // @TODO - set a header to 403 Forbidden? or 401 Unauthorized? or 400 Bad Request?
    require DIR_WS_INCLUDES . 'application_bottom.php';
    die();
}

require DIR_WS_MODULES . 'payment/paypal/pprAutoload.php';

use PayPalRestful\Api\PayPalRestfulApi;
use PayPalRestful\Common\Logger;

$op = $_GET['op'] ?? '';
$logger = new Logger();
if (strpos(MODULE_PAYMENT_PAYPALR_DEBUGGING, 'Log') !== false) {
    $logger->enableDebug();
}
$logger->write("ppr_listener ($op, " . MODULE_PAYMENT_PAYPALR_SERVER . ") starts.\n" . Logger::logJSON($_GET), true, 'before');

$valid_operations = ['cancel', 'return', '3ds_cancel', '3ds_return'];
if (!in_array($op, $valid_operations, true)) {
    unset($_SESSION['PayPalRestful']['Order']);
    $zco_notifier->notify('NOTIFY_PPR_LISTENER_UNKNOWN_OPERATION', ['op' => $op]);
    zen_redirect(zen_href_link(FILENAME_DEFAULT));  //- FIXME? Perhaps FILENAME_TIME_OUT would be better, since that would kill any session.
}

// -----
// Either the customer chose to pay ...
//
// 1) ... with their PayPal Wallet, was sent to PayPal to choose their means
//    of payment and the customer chose to cancel-back from PayPal.
// 2) ... with a credit-card (which required 3DS verification) and the
//    customer chose to cancel-back from the 3DS authorization link.
//
// In either case, the customer is redirected back to the payment phase of the
// checkout process.
//
if ($op === 'cancel' || $op === '3ds_cancel') {
    unset($_SESSION['PayPalRestful']['Order']['PayerAction']);
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT), '', 'SSL');
}

if ($op === 'return' && (!isset($_GET['token'], $_SESSION['PayPalRestful']['Order']['id']) || $_GET['token'] !== $_SESSION['PayPalRestful']['Order']['id'])) {
    unset($_SESSION['PayPalRestful']['Order']);
    zen_redirect(zen_href_link(FILENAME_DEFAULT));  //- FIXME? Perhaps FILENAME_TIME_OUT would be better, since that would kill any session.
}

// -----
// Customer chose to pay with their PayPal, was sent to PayPal where the chose their means
// of payment and chose to return to the site to review/pay their order prior to
// confirmation OR the customer's payment choice was a credit card which
// subsequently required a 3DS authorization and was redirected back here
// to complete the transaction.
//
// The 'PayerAction' session element is set with the values to be posted
// back to the pertinent phase of the checkout process.  If (for some
// unknown reason) that element's not present, the customer's sent
// back to the payment phase of the checkout process.
//
if (!isset($_SESSION['PayPalRestful']['Order']['PayerAction'])) {
    $logger->write('ppr_listener, redirecting to checkout_payment; no PayerAction variables.', true, 'after');
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT), '', 'SSL');
}

// -----
// If we've gotten here, this was either a successful callback for the
// customer's PayPal Wallet selection or the customer has completed
// a 3DS verification for a credit-card payment.
//
require DIR_WS_MODULES . 'payment/paypalr.php';
list($client_id, $secret) = paypalr::getEnvironmentInfo();

$ppr = new PayPalRestfulApi(MODULE_PAYMENT_PAYPALR_SERVER, $client_id, $secret);
$ppr->setKeepTxnLinks(true);
$order_status = $ppr->getOrderStatus($_SESSION['PayPalRestful']['Order']['id']);
if ($order_status === false) {
    unset($_SESSION['PayPalRestful']['Order']);
    $logger->write('==> getOrderStatus failed, redirecting to shopping-cart', true, 'after');
    zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

// -----
// If this is a 3DS verification return, check the associated parameters to see
// if the order should proceed.  Refer to the following link for additional information:
//
// https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/
//
if ($op === '3ds_return') {
    $auth_result = $order_status['payment_source']['card']['authentication_result'];
    $liability_shift = $auth_result['liability_shift'];
    $enrollment_status = $auth_result['three_d_secure']['enrollment_status'];
    if ($liability_shift === 'UNKNOWN' || ($enrollment_status === 'Y' && $liability_shift === 'NO')) {
        $messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYPALR_REDIRECT_LISTENER_TRY_AGAIN, 'error');
        unset($_SESSION['PayPalRestful']['Order']['PayerAction'], $_SESSION['PayPalRestful']['Order']['authentication_result']);
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT), '', 'SSL');
    }
}

// -----
// Save the PayPal status response's status in the session-based PayPal
// order array and indicate that the wallet- (or card-) payment has been confirmed so that
// the base payment module "knows" that the payment-confirmation (or creation) at PayPal
// has been completed.
//
$_SESSION['PayPalRestful']['Order']['status'] = $order_status['status'];
if ($op === 'return') {
    $_SESSION['PayPalRestful']['Order']['wallet_payment_confirmed'] = true;
} else {
    $_SESSION['PayPalRestful']['Order']['3DS_response'] = $_SESSION['PayPalRestful']['Order']['PayerAction']['ccInfo'];
    $_SESSION['PayPalRestful']['Order']['authentication_result'] = $auth_result;
}

// -----
// Create a self-submitting form to post back to the page
// from which the PayPal 'payer-action' was sent.  This is especially
// required for the integration with OPC when the associated payment-module
// doesn't require that the confirmation page be displayed.
//
// NOTE: CSS-based spinner compliments of 'loading.io css spinner' ( https://loading.io/css/ )
//
$redirect_page = $_SESSION['PayPalRestful']['Order']['PayerAction']['current_page_base'];
$logger->write("Order's status set to {$order_status['status']}; posting back to $redirect_page.", true, 'after');
?>
<html>
<body onload="document.transfer_form.submit();">
    <style>
#lds-wrapper {
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
.lds-ring {
    display: inline-block;
    position: relative;
    top: 50%;
    margin-top: -40px;
    left: 50%;
    margin-left: -40px;
    width: 80px;
    height: 80px;
}
.lds-ring div {
    box-sizing: border-box;
    display: block;
    position: absolute;
    width: 64px;
    height: 64px;
    margin: 8px;
    border: 8px solid #002b7f;
    border-radius: 50%;
    animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border-color: #002b7f transparent transparent transparent;
}
.lds-ring div:nth-child(1) {
    animation-delay: -0.45s;
}
.lds-ring div:nth-child(2) {
    animation-delay: -0.3s;
}
.lds-ring div:nth-child(3) {
    animation-delay: -0.15s;
}
@keyframes lds-ring {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
    </style>
    <div id="lds-wrapper"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>
    <form action="<?php echo zen_href_link($redirect_page); ?>" name="transfer_form" method="post">
<?php
foreach ($_SESSION['PayPalRestful']['Order']['PayerAction']['savedPosts'] as $key => $value) {
    if (is_string($value)) {
        echo zen_draw_hidden_field($key, $value);
        continue;
    }

    $array_key_name = $key . '[:sub_key:]';
    foreach ($value as $sub_key => $sub_value) {
        echo zen_draw_hidden_field(str_replace(':sub_key:', $sub_key, $array_key_name), $sub_value);
    }
}
unset($_SESSION['PayPalRestful']['Order']['PayerAction']);
?>
    </form>
</body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
