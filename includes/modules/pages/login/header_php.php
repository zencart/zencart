<?php
/**
 * Login Page
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: proseLA 2024 Aug 11 Modified in v2.1.0-alpha2 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_LOGIN');
$login_page = true;

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
if ($session_started == false) {
    zen_redirect(zen_href_link(FILENAME_COOKIE_USAGE));
}

// if the customer is logged in already, not in guest-checkout, and not a new EMP Automatic Login, redirect them to the My account page
if (!zen_in_guest_checkout() && zen_is_logged_in() && !isset($_GET['hmac'])) {
    zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_CREATE_ACCOUNT));

// -----
// Gather any posted email_address prior to the processing loop, in case this is a 'Place Order'
// request coming from the admin.
//
$email_address = zen_db_prepare_input(isset($_POST['email_address']) ? trim($_POST['email_address']) : '');

$error = false;
if (isset($_GET['action']) && $_GET['action'] == 'process') {
    $loginAuthorized = false;

    if (isset($_GET['hmac'])) {
        // we have already validated the hmac in init_sanitize
        // now lets check the timestamp and admin id.
        if (!zen_validate_hmac_timestamp() || !$adminId = zen_validate_hmac_admin_id($_POST['aid'])) {
            zen_redirect(zen_href_link(FILENAME_TIME_OUT));
        }
        unset($_SESSION['billto'], $_SESSION['sendto'], $_SESSION['customer_default_address_id'], $_SESSION['cart_address_id'],);
        $_SESSION['cart'] = new shoppingCart();
        $loginAuthorized = true;
        $_SESSION['emp_admin_login'] = true;
        $_SESSION['emp_admin_id'] = $adminId;
        $_SESSION['emp_customer_email_address'] = $email_address;
        zen_log_hmac_login(['emailAddress' => $email_address, 'message' => 'EMP Automatic Login', 'action' => 'emp_automatic_login']);
    }

    $password = zen_db_prepare_input(isset($_POST['password']) ? trim($_POST['password']) : '');

    /* Privacy-policy-read does not need to be checked during "login"
    if (DISPLAY_PRIVACY_CONDITIONS == 'true') {
    if (!isset($_POST['privacy_conditions']) || ($_POST['privacy_conditions'] != '1')) {
    $error = true;
    $messageStack->add('create_account', ERROR_PRIVACY_STATEMENT_NOT_ACCEPTED, 'error');
    }
    }
    */


    $customer = new Customer;
    $login_attempt = $customer->doLoginLookupByEmail($email_address);

    if ($login_attempt === false) {
        $error = true;
        $messageStack->add('login', TEXT_LOGIN_ERROR);
    } elseif ($login_attempt['customers_authorization'] == '4') {
        // this account is banned
        $zco_notifier->notify('NOTIFY_LOGIN_BANNED');
        $messageStack->add('login', TEXT_LOGIN_BANNED);
    } else {
        if (!$loginAuthorized) {
            $dbPassword = $login_attempt['customers_password'];
            // Check whether the password is good
            if (zen_validate_password($password, $dbPassword)) {
                $loginAuthorized = true;
                if (password_needs_rehash($dbPassword, PASSWORD_DEFAULT)) {
                    $newPassword = zcPassword::getInstance(PHP_VERSION)->updateNotLoggedInCustomerPassword(
                        $password, $email_address);
                }
            } else {
                $loginAuthorized = zen_validate_storefront_admin_login($password, $email_address);
            }
        }
        $zco_notifier->notify('NOTIFY_PROCESS_3RD_PARTY_LOGINS', $email_address, $password, $loginAuthorized);

        if (!$loginAuthorized) {
            $error = true;
            $messageStack->add('login', TEXT_LOGIN_ERROR);
        } else {

            $zc_check_basket_before = 0;
            // save current cart contents count if required
            if (SHOW_SHOPPING_CART_COMBINED > 0) {
                $zc_check_basket_before = $_SESSION['cart']->count_contents();
            }

            // login and restore cart
            $customer->login($login_attempt['customers_id'], $restore_cart = true);

            if (SESSION_RECREATE == 'True') {
                zen_session_recreate();
            }

            $zco_notifier->notify('NOTIFY_LOGIN_SUCCESS');

            // check current cart contents count if required
            $zc_check_basket_after = $_SESSION['cart']->count_contents();
            if (SHOW_SHOPPING_CART_COMBINED > 0 && $zc_check_basket_after > 0 && $zc_check_basket_before != $zc_check_basket_after) {
                if (SHOW_SHOPPING_CART_COMBINED == 2) {
                    // warning only do not send to cart
                    $messageStack->add_session('header', WARNING_SHOPPING_CART_COMBINED, 'caution');
                }
                if (SHOW_SHOPPING_CART_COMBINED == 1) {
                    // show warning and send to shopping cart for review
                    if (!(isset($_GET['gv_no']))) {
                        $messageStack->add_session('shopping_cart', WARNING_SHOPPING_CART_COMBINED, 'caution');
                        zen_redirect(zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'));
                    } else {
                        $messageStack->add_session('header', WARNING_SHOPPING_CART_COMBINED, 'caution');
                    }
                }
            }
            // end contents merge notice

            if (count($_SESSION['navigation']->snapshot) > 0) {
                //    $back = sizeof($_SESSION['navigation']->path)-2;
                $origin_href = zen_href_link($_SESSION['navigation']->snapshot['page'], zen_array_to_string($_SESSION['navigation']->snapshot['get'], [zen_session_name()]), $_SESSION['navigation']->snapshot['mode']);
                //            $origin_href = zen_back_link_only(true);
                $_SESSION['navigation']->clear_snapshot();
                zen_redirect($origin_href);
            } else {
                zen_redirect(zen_href_link(FILENAME_DEFAULT, '', $request_type));
            }
        }
    }
}
if ($error == true) {
    $zco_notifier->notify('NOTIFY_LOGIN_FAILURE');
}

$breadcrumb->add(NAVBAR_TITLE);

// Check for PayPal express checkout button suitability:
$paypalec_enabled = (defined('MODULE_PAYMENT_PAYPALWPP_STATUS') && MODULE_PAYMENT_PAYPALWPP_STATUS == 'True' && defined('MODULE_PAYMENT_PAYPALWPP_ECS_BUTTON') && MODULE_PAYMENT_PAYPALWPP_ECS_BUTTON == 'On');
// Check for express checkout button suitability (must have cart contents, value > 0, and value < 10000USD):
require_once DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/paypal_currency_check.php';
$ec_button_enabled = ($paypalec_enabled && $_SESSION['cart']->count_contents() > 0 && $_SESSION['cart']->total > 0 && paypalUSDCheck($_SESSION['cart']->total) === true);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_LOGIN');
