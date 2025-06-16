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

    $sql =
        "SELECT customers_firstname, customers_lastname, customers_id, customers_email_address
           FROM " . TABLE_CUSTOMERS . "
          WHERE customers_email_address = :emailAddress
            AND customers_authorization != 4";

    $sql = $db->bindVars($sql, ':emailAddress', $email_address, 'string');
    $check_customer = $db->Execute($sql, 1);

    $sessionMessage = SUCCESS_PASSWORD_RESET_SENT;

    if ($check_customer->RecordCount() > 0) {
        // customer exists for the provided email address

        $email_address = $check_customer->fields['customers_email_address'];

        $zco_notifier->notify('NOTIFY_PASSWORD_FORGOTTEN_VALIDATED', $email_address, $sessionMessage);

        $length = defined('PASSWORD_RESET_TOKEN_LENGTH') ? constant('PASSWORD_RESET_TOKEN_LENGTH') : 24;
        if ($length < 12 || $length > 100) { // under 12 is impractical; over 100 is too large for db field
            $length = 24;
        }
        $token = zen_create_random_value($length);

        $sql = "DELETE FROM " . TABLE_CUSTOMER_PASSWORD_RESET_TOKENS . " WHERE customer_id = :customerID";
        $sql = $db->bindVars($sql, ':customerID', $check_customer->fields['customers_id'], 'integer');
        $db->Execute($sql);
        $sql = "INSERT INTO " . TABLE_CUSTOMER_PASSWORD_RESET_TOKENS . " (customer_id, token) VALUES (:customerID, :token)";
        $sql = $db->bindVars($sql, ':token', $token, 'string');
        $sql = $db->bindVars($sql, ':customerID', $check_customer->fields['customers_id'], 'integer');
        $db->Execute($sql);

        $reset_url = zen_href_link(FILENAME_PASSWORD_RESET, "reset_token=$token");

        $name = $check_customer->fields['customers_firstname'] . ' ' . $check_customer->fields['customers_lastname'];
        $body = sprintf(EMAIL_PASSWORD_RESET_BODY, zen_get_ip_address(), STORE_NAME, $reset_url);

        $html_msg = [];
        $html_msg['EMAIL_CUSTOMERS_NAME'] = $name;
        $html_msg['EMAIL_MESSAGE_HTML'] = $body;

        // Note: If this mail frequently winds up in spam folders, try replacing $html_msg with 'none' below.
        // $html_msg = 'none';

        // Send the email
        zen_mail($name, $email_address, EMAIL_PASSWORD_RESET_SUBJECT, $body, STORE_NAME, EMAIL_FROM, $html_msg, 'password_forgotten');

        // handle 3rd-party integrations
        $zco_notifier->notify('NOTIFY_PASSWORD_RESET_URL_SENT', $email_address, $check_customer->fields['customers_id'], $token);
    } else {
        $zco_notifier->notify('NOTIFY_PASSWORD_FORGOTTEN_NOT_FOUND', $email_address, $sessionMessage);
    }

    $messageStack->add_session('login', $sessionMessage, 'success');

    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_LOGIN, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_PASSWORD_FORGOTTEN');
