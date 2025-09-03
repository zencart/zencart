<?php
/**
 * Password Forgotten
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 *
 * @var queryFactory $db
 * @var messageStack $messageStack
 * @var breadcrumb $breadcrumb
 * @var notifier $zco_notifier
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_PASSWORD_FORGOTTEN');

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

// remove from snapshot
$_SESSION['navigation']->remove_current_page();

if (($_GET['action'] ?? '') === 'process') {
    // -----
    // Enable a site to control the number of failed login and/or password-reset attempts.
    //
    $max_login_attempts = (int)($max_login_attempts ?? 9);
    if ($max_login_attempts < 2) {
        $max_login_attempts = 9;
    }

    // Slam prevention:
    $_SESSION['login_attempt'] ??= 0;
    if ($_SESSION['login_attempt'] > $max_login_attempts) {
        header('HTTP/1.1 406 Not Acceptable');
        exit(0);
    }
    // BEGIN SLAM PREVENTION
    if (!empty($_POST['email_address'])) {
        $_SESSION['login_attempt']++;
    } // END SLAM PREVENTION


    if (empty($_POST['email_address'])) {
        $messageStack->add_session('password_forgotten', ENTRY_EMAIL_ADDRESS_ERROR, 'error');
        zen_redirect(zen_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'));
    }

    $sessionMessage = SUCCESS_PASSWORD_RESET_SENT;
    $email_address = zen_db_prepare_input(trim($_POST['email_address']));
    $check_customer = Customer::createPasswordResetToken($email_address);

    // -----
    // Check to see if a password reset-token was already sent for the
    // email address. If one was and the token's less than halfway to
    // expiration, the customer is being impatient and we'll redisplay the
    // login page indicating that they should check their email for
    // the password-reset link.
    //
    $check_token_sent = Customer::getPasswordResetTokenForEmail($email_address);
    if ($check_token_sent !== false) {
        $max_minutes_token_valid = Customer::getPasswordResetTokenMinutesValid();
        if ((strtotime($check_token_sent['created_at']) + $max_minutes_token_valid / 2) < time()) {
            $continue_with_reset_email = false;
            $check_token_sent['email_address'] = $email_address;
            $check_token_sent['max_minutes_token_valid'] = $max_minutes_token_valid;
            $zco_notifier->notify('NOTIFY_PASSWORD_FORGOTTEN_ALREADY_SENT', $check_token_sent, $sessionMessage, $continue_with_reset_email);
            if ((bool)$continue_with_reset_email === false) {
                $messageStack->add_session('login', $sessionMessage, 'success');
                zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
            }
        }
    }

    $check_customer = Customer::createPasswordResetToken($email_address);

    if ($check_customer === false) {
        $zco_notifier->notify('NOTIFY_PASSWORD_FORGOTTEN_NOT_FOUND', $email_address, $sessionMessage);
    } else {
        $zco_notifier->notify('NOTIFY_PASSWORD_FORGOTTEN_VALIDATED', $email_address, $sessionMessage);

        $token = $check_customer['token'];
        $reset_url = zen_href_link(FILENAME_PASSWORD_RESET, "reset_token=$token");

        $name = $check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'];
        $body = sprintf(EMAIL_PASSWORD_RESET_BODY, zen_get_ip_address(), STORE_NAME, $reset_url);

        $html_msg = [];
        $html_msg['EMAIL_CUSTOMERS_NAME'] = $name;
        $html_msg['EMAIL_MESSAGE_HTML'] = $body;

        // Note: If this mail frequently winds up in spam folders, try replacing $html_msg with 'none' below.
        // $html_msg = 'none';

        // Send the email
        zen_mail($name, $email_address, EMAIL_PASSWORD_RESET_SUBJECT, $body, STORE_NAME, EMAIL_FROM, $html_msg, 'password_forgotten');

        // handle 3rd-party integrations
        $zco_notifier->notify('NOTIFY_PASSWORD_RESET_URL_SENT', $email_address, $check_customer['customers_id'], $token);
    }

    $messageStack->add_session('login', $sessionMessage, 'success');

    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_LOGIN, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_PASSWORD_FORGOTTEN');
