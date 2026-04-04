<?php
/**
 * send_auth_token_email.php. Sends a customer an account-authorization token
 * via email.
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2025 Sep 24 New in v2.2.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// -----
// A multi-use module to send an account-activation token to a customer via email.
//
if (!zen_is_logged_in() || zen_in_guest_checkout()) {
    return;
}

// -----
// Load the language file containing various email constants.
//
$languageLoader->loadModuleLanguageFile('send_auth_token_email.php', '');

// -----
// If the customer (or account-activation token) isn't set, create
// a new value.
//
$customer ??= new Customer();
$token ??= $customer->createAuthToken();

// -----
// If the token-value is (bool)false, then either there's no customer
// logged-in or the site hasn't enabled the account-activation feature; the
// customer's sent back to the login page.
//
if ($token === false) {
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// -----
// Finally, check to see that the token's still valid.  If not,
// recreate it prior to sending the email.
//
if (Customer::getAuthTokenValid($token) === false) {
    $token = $customer->createAuthToken();
}

$name = $customer->getData('customers_firstname') . ' ' . $customer->getData('customers_lastname');

$reset_url = zen_href_link(CUSTOMERS_AUTHORIZATION_FILENAME, "reset_token=$token", 'SSL');

$body = sprintf(EMAIL_AUTH_TOKEN_BODY, $reset_url, Customer::getAuthTokenMinutesValid());

$html_msg = [];
$html_msg['EMAIL_CUSTOMERS_NAME'] = $name;
$html_msg['EMAIL_MESSAGE_HTML'] = $body;

$email_text = $name . "\n\n" . $body;
// Note: If this mail frequently winds up in spam folders, try replacing $html_msg with 'none' below.
// $html_msg = 'none';

// Send the email
zen_mail($name, $customer->getData('customers_email_address'), EMAIL_AUTH_TOKEN_SUBJECT, $email_text, STORE_NAME, EMAIL_FROM, $html_msg, 'password_forgotten');

$messageStack->add_session('header', sprintf(SUCCESS_AUTH_TOKEN_SENT, $customer->getData('customers_email_address')), 'success');
