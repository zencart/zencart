<?php
/**
 * Customer Authorization 
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
// -----
// NOTE: The multiple lines requiring 'require_languages.php' are needed so
// that the page's 'title' metatag can be set differently if the site
// is waiting for the customer to respond to an account auth-token email!
//
if (!empty($_GET['reset_token'])) {
    $auth_token_info = Customer::getAuthTokenValid($_GET['reset_token']);
    if ($auth_token_info === false) {
        // -----
        // Enable a site to control the number of failed auth-token requests.
        //
        $max_auth_token_activation_attempts = (int)($max_auth_token_activation_attempts ?? 9);
        if ($max_auth_token_activation_attempts < 2) {
            $max_auth_token_activation_attempts = 9;
        }

        $_SESSION['auth_token_activation_attempts'] ??= 0;
        $_SESSION['auth_token_activation_attempts']++;
        if ($_SESSION['auth_token_activation_attempts'] > $max_auth_token_activation_attempts) {
            header('HTTP/1.1 406 Not Acceptable');
            zen_exit();
        }
        zen_redirect(zen_href_link(CUSTOMERS_AUTHORIZATION_FILENAME, '', 'SSL'));
    }

    unset($_SESSION['auth_token_activation_attempts']);

    require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
    $messageStack->add_session('header', SUCCESS_AUTHORIZED, 'success');

    $customer_data = Customer::authorizeCustomer((int)$auth_token_info['customers_id']);
    if ($customer_data['welcome_email_sent'] === '0') {
        $firstname = $customer_data['customers_firstname'];
        $lastname = $customer_data['customers_lastname'];
        $gender = $customer_data['customers_gender'];
        $email_address = $customer_data['customers_email_address'];

        require DIR_WS_MODULES . zen_get_module_directory(FILENAME_CREATE_ACCOUNT_SEND_EMAIL);
        Customer::setWelcomeEmailSent((int)$auth_token_info['customers_id']);
        zen_redirect(zen_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
    }
    zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

if (!zen_is_logged_in() || zen_in_guest_checkout()) {
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$customer = new Customer();
$customer_data = $customer->refreshCustomerAuthorization();

if (!in_array($_SESSION['customers_authorization'], [Customer::AUTH_NO_BROWSE, Customer::AUTH_NO_PRICES, Customer::AUTH_NO_PURCHASE])) {
    zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

if (empty($customer_data['activation_required'])) {
    require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
    $customer_authorization_heading_title = HEADING_TITLE;
    $main_content = CUSTOMERS_AUTHORIZATION_TEXT_INFORMATION;
} else {
    $resend_requested = ($_GET['action'] ?? '') === 'resend';
    $auth_token_info = $customer->getAuthTokenInfo();
    $token = ($auth_token_info['token'] ?? null);

    if ($token === null || ($_GET['action'] ?? '') === 'resend') {
        require DIR_WS_MODULES . zen_get_module_directory(FILENAME_SEND_AUTH_TOKEN_EMAIL);
        $auth_token_info = $customer->getAuthTokenInfo();
    }

    require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');
    $customer_authorization_heading_title = HEADING_TITLE_ACTIVATE;
    $main_content = sprintf(TEXT_INFORMATION_ACTIVATE, '<b>' . $auth_token_info['email_address'] . '</b>');

    $auth_token_time_remaining = strtotime($auth_token_info['created_at']) + (Customer::getAuthTokenMinutesValid() * 60) - time();
    if ($auth_token_time_remaining < 0) {
        $main_content .= ' ' . TEXT_INFORMATION_LINK_EXPIRED;
    } else {
        $main_content .= ' ' . TEXT_INFORMATION_LINK_ACTIVE . ' <span id="countdown">&nbsp;</span>';
    }

    $resend_activation_link = '<a href="' . zen_href_link(CUSTOMERS_AUTHORIZATION_FILENAME, 'action=resend', 'SSL') . '">' . TEXT_HERE . '</a>';
    $account_edit_link = '<a href="' . zen_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . TEXT_HERE . '</a>';
    $main_content .= '<br><br>' . sprintf(TEXT_INFORMATION_RESEND, $resend_activation_link, $account_edit_link) . '<br><br>';
}

$breadcrumb->add(NAVBAR_TITLE);

$flag_disable_right ??= (CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF === 'true');
$flag_disable_left ??= (CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF === 'true');
$flag_disable_footer ??= (CUSTOMERS_AUTHORIZATION_FOOTER_OFF === 'true');
$flag_disable_header ??= (CUSTOMERS_AUTHORIZATION_HEADER_OFF === 'true');
