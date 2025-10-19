<?php
/**
 * module to process a completed checkout
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Oct 25 Modified in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_BEGIN');

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

// if the customer is not logged in, redirect them to the time out page
if (!zen_is_logged_in()) {
    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
} else {
    // validate customer
    if (zen_get_customer_validate_session($_SESSION['customer_id']) == false) {
        $_SESSION['navigation']->set_snapshot(['mode' => 'SSL', 'page' => FILENAME_CHECKOUT_SHIPPING]);
        zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
}

// BEGIN CC SLAM PREVENTION
$slamming_threshold = 3;
if (!isset($_SESSION['payment_attempt'])) {
    $_SESSION['payment_attempt'] = 0;
}
$_SESSION['payment_attempt']++;
$zco_notifier->notify('NOTIFY_CHECKOUT_SLAMMING_ALERT', $_SESSION['payment_attempt'], $slamming_threshold);
if ($_SESSION['payment_attempt'] > $slamming_threshold) {
    $zco_notifier->notify('NOTIFY_CHECKOUT_SLAMMING_LOCKOUT');
    $_SESSION['cart']->reset(true);
    zen_session_destroy();
    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}
// END CC SLAM PREVENTION

if (!isset($credit_covers)) {
    $credit_covers = false;
}

// load selected payment module
require DIR_WS_CLASSES . 'payment.php';
$payment_modules = new payment($_SESSION['payment']);

require DIR_WS_CLASSES . 'order.php';
$order = new order;

// load the selected shipping module
require DIR_WS_CLASSES . 'shipping.php';
$shipping_modules = new shipping($_SESSION['shipping']);

// prevent 0-entry orders from being generated/spoofed
if (count($order->products) < 1) {
    zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

require DIR_WS_CLASSES . 'order_total.php';
$order_total_modules = new order_total;

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cart']->cartID) && $_SESSION['cartID']) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
        $payment_modules->clear_payment();
        $order_total_modules->clear_posts();
        unset($_SESSION['payment']);
        unset($_SESSION['shipping']);

        zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
}

$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_BEFORE_ORDER_TOTALS_PRE_CONFIRMATION_CHECK');
if (empty($_SESSION['payment']) || strpos($GLOBALS[$_SESSION['payment']]->code, 'paypal') !== 0) {
    $order_totals = $order_total_modules->pre_confirmation_check();
}

// -----
// The order-totals::pre_confirmation_check method could have set the indication that
// either a Gift Certificate or coupon has 'covered' the payment.  Let the payment
// class perform any updates needed for its proper follow-on operation.
//
$payment_modules->checkCreditCovered();

if ($credit_covers === true) {
    $order->info['payment_method'] = $order->info['payment_module_code'] = '';
}
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_BEFORE_ORDER_TOTALS_PROCESS');
$order_totals = $order_total_modules->process();
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_TOTALS_PROCESS');

if (!isset($_SESSION['payment']) && $credit_covers === false) {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

// load the before_process function from the payment modules
$payment_modules->before_process();

// -----
// Account for any order-status change based on the payment module's processing.
//
if (isset($GLOBALS[$_SESSION['payment']]->order_status) && ((int)$GLOBALS[$_SESSION['payment']]->order_status) > 0) {
    $order->info['order_status'] = (int)$GLOBALS[$_SESSION['payment']]->order_status;
}
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_PAYMENT_MODULES_BEFOREPROCESS');

// create the order record
$insert_id = $order->create($order_totals);
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_CREATE', $insert_id);
$payment_modules->after_order_create($insert_id);
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_PAYMENT_MODULES_AFTER_ORDER_CREATE', $insert_id);
// store the product info to the order
$order->create_add_products($insert_id);
$_SESSION['order_number_created'] = $insert_id;
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_CREATE_ADD_PRODUCTS', $insert_id, $order);
//send email notifications
$order->send_order_email($insert_id);
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_SEND_ORDER_EMAIL', $insert_id, $order);

// clear slamming protection since payment was accepted
if (isset($_SESSION['payment_attempt'])) {
    unset($_SESSION['payment_attempt']);
}

/**
 * Calculate order amount for display purposes on checkout-success page as well as adword campaigns etc
 * Takes the product subtotal and subtracts all credits from it
 */
$oshipping = $otax = $ototal = $order_subtotal = $credits_applied = 0;
for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
    if ($order_totals[$i]['code'] === 'ot_subtotal') $order_subtotal = $order_totals[$i]['value'];
    if (!empty(${$order_totals[$i]['code']}->credit_class)) $credits_applied += $order_totals[$i]['value'];
    if ($order_totals[$i]['code'] === 'ot_total') $ototal = $order_totals[$i]['value'];
    if ($order_totals[$i]['code'] === 'ot_tax') $otax = $order_totals[$i]['value'];
    if ($order_totals[$i]['code'] === 'ot_shipping') $oshipping = $order_totals[$i]['value'];
}
$commissionable_order = ($order_subtotal - $credits_applied);
$commissionable_order_formatted = $currencies->format($commissionable_order);
$_SESSION['order_summary']['order_number'] = $insert_id;
$_SESSION['order_summary']['order_subtotal'] = $order_subtotal;
$_SESSION['order_summary']['credits_applied'] = $credits_applied;
$_SESSION['order_summary']['order_total'] = $ototal;
$_SESSION['order_summary']['commissionable_order'] = $commissionable_order;
$_SESSION['order_summary']['commissionable_order_formatted'] = $commissionable_order_formatted;
$_SESSION['order_summary']['coupon_code'] = urlencode($order->info['coupon_code']);
$_SESSION['order_summary']['currency_code'] = $order->info['currency'];
$_SESSION['order_summary']['currency_value'] = $order->info['currency_value'];
$_SESSION['order_summary']['payment_module_code'] = $order->info['payment_module_code'];
$_SESSION['order_summary']['shipping_method'] = $order->info['shipping_method'];
$_SESSION['order_summary']['order_status'] = $order->info['order_status'];
$_SESSION['order_summary']['orders_status'] = $order->info['order_status']; // alias for older versions
$_SESSION['order_summary']['tax'] = $otax;
$_SESSION['order_summary']['shipping'] = $oshipping;
$products_array = [];
foreach ($order->products as $key => $val) {
    $products_array[urlencode($val['id'])] = urlencode($val['model']);
}
$_SESSION['order_summary']['products_ordered_ids'] = implode('|', array_keys($products_array));
$_SESSION['order_summary']['products_ordered_models'] = implode('|', array_values($products_array));
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_HANDLE_AFFILIATES');

