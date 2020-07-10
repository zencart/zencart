<?php
/**
 * checkout_payment header_php.php
 *
 * @package page
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Apr 30 Modified in v1.5.6b $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_CHECKOUT_PAYMENT');
// if (!isset($_SESSION['jscript_enabled'])) {
//     $messageStack->add_session ('shopping_cart', PAYMENT_JAVASCRIPT_DISABLED, 'error');
//   zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
// }

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() <= 0) {
    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}

// if the customer is not logged on, redirect them to the login page
  if (!zen_is_logged_in()) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  } else {
    // validate customer
    if (zen_get_customer_validate_session($_SESSION['customer_id']) == false) {
      $_SESSION['navigation']->set_snapshot();
      zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset($_SESSION['shipping']) || !$_SESSION['shipping']) {
  zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}
if (isset($_SESSION['shipping']['id']) && $_SESSION['shipping']['id'] == 'free_free' && $_SESSION['cart']->get_content_type() != 'virtual' && defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true' && defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER') && $_SESSION['cart']->show_total() < MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
  zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cart']->cartID) && $_SESSION['cartID']) {
  if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
    zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }
}

// Stock Check
if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
  $products = $_SESSION['cart']->get_products();
  for ($i=0, $n=sizeof($products); $i<$n; $i++) {
    $qtyAvailable = zen_get_products_stock($products[$i]['id']);
    // compare against product inventory, and against mixed=YES
    if ($qtyAvailable - $products[$i]['quantity'] < 0 || $qtyAvailable - $_SESSION['cart']->in_cart_mixed($products[$i]['id']) < 0) {
      zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
      break;
    }
  }
}

// get coupon code
if (!empty($_SESSION['cc_id'])) {
  $discount_coupon_query = "SELECT coupon_code
                            FROM " . TABLE_COUPONS . "
                            WHERE coupon_id = :couponID";

  $discount_coupon_query = $db->bindVars($discount_coupon_query, ':couponID', $_SESSION['cc_id'], 'integer');
  $discount_coupon = $db->Execute($discount_coupon_query);
}

// if no billing destination address was selected, use the customers own address as default
if (empty($_SESSION['billto'])) {
  $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
} else {
  // verify the selected billing address
  $check_address_query = "SELECT count(*) AS total FROM " . TABLE_ADDRESS_BOOK . "
                          WHERE customers_id = :customersID
                          AND address_book_id = :addressBookID";

  $check_address_query = $db->bindVars($check_address_query, ':customersID', $_SESSION['customer_id'], 'integer');
  $check_address_query = $db->bindVars($check_address_query, ':addressBookID', $_SESSION['billto'], 'integer');
  $check_address = $db->Execute($check_address_query);

  if ($check_address->fields['total'] != '1') {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
    $_SESSION['payment'] = '';
  }
}

require(DIR_WS_CLASSES . 'order.php');
$order = new order;
// Load the selected shipping module(needed to calculate tax correctly)
require(DIR_WS_CLASSES . 'shipping.php');
$shipping_modules = new shipping($_SESSION['shipping']);
require(DIR_WS_CLASSES . 'order_total.php');
$order_total_modules = new order_total;
$order_total_modules->collect_posts();
$order_total_modules->pre_confirmation_check();

//  $_SESSION['comments'] = '';
$comments = !empty($_SESSION['comments']) ? $_SESSION['comments'] : '';

$total_weight = $_SESSION['cart']->show_weight();
$total_count = $_SESSION['cart']->count_contents();

// load all enabled payment modules
require(DIR_WS_CLASSES . 'payment.php');
$payment_modules = new payment;
$flagOnSubmit = sizeof($payment_modules->selection());


require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
  $messageStack->add('checkout_payment', $error['error'], 'error');
}
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_CHECKOUT_PAYMENT');
?>