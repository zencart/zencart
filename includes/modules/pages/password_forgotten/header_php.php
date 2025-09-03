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

if (isset($_GET['action']) && $_GET['action'] === 'process') {

    // Slam prevention:
    if (isset($_SESSION['login_attempt']) && $_SESSION['login_attempt'] > 9) {
        header('HTTP/1.1 406 Not Acceptable');
        exit(0);
    }
    // BEGIN SLAM PREVENTION
    if (!empty($_POST['email_address'])) {
        if (!isset($_SESSION['login_attempt'])) {
            $_SESSION['login_attempt'] = 0;
        }
        $_SESSION['login_attempt']++;
    } // END SLAM PREVENTION


    if (empty($_POST['email_address'])) {
        $messageStack->add_session('password_forgotten', ENTRY_EMAIL_ADDRESS_ERROR, 'error');
        zen_redirect(zen_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'));
    }

    $email_address = zen_db_prepare_input(trim($_POST['email_address']));
    $check_customer = Customer::createPasswordResetToken($email_address);

    $sessionMessage = SUCCESS_PASSWORD_RESET_SENT;

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
