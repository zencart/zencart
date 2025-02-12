<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php  $
 *
 * @var queryFactory $db
 * @var messageStack $messageStack
 * @var breadcrumb $breadcrumb
 * @var notifier $zco_notifier
 * @var sniffer $sniffer
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ACCOUNT_PASSWORD_RESET');

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

$error = false;
$token_error = false;

$reset_token = $db->prepare_input($_GET['reset_token'] ?? $_POST['reset_token'] ?? '');

$token_valid_minutes = defined('PASSWORD_RESET_TOKEN_MINUTES_VALID') ? (int)constant('PASSWORD_RESET_TOKEN_MINUTES_VALID') : 60;
if ($token_valid_minutes < 1 || $token_valid_minutes > 1440) {
    $token_valid_minutes = 60;
}

$sql = "SELECT c.customers_nick, c.customers_id
        FROM   " . TABLE_CUSTOMERS . " c, " . TABLE_CUSTOMER_PASSWORD_RESET_TOKENS . " ct
        WHERE  ct.token = :reset_token AND c.customers_id = ct.customer_id AND ct.created_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL $token_valid_minutes MINUTE)";
$sql = $db->bindVars($sql, ':reset_token', $reset_token, 'string');
$result = $db->Execute($sql);

if (empty($result->fields['customers_id'])) {
    // no matching token found within date range of unexpired tokens
    $messageStack->add('reset_password', PASSWORD_RESET_ENTRY_PASSWORD_TOKEN_ERROR);
    $error = true;
    $token_error = true;
} else {
    $customer_id = $result->fields['customers_id'];
    $nickname = $result->fields['customers_nick'];
}

if (isset($_POST['action']) && ($_POST['action'] === 'process') && !empty($customer_id)) {
    $password_new = zen_db_prepare_input($_POST['password_new']);
    $password_confirmation = zen_db_prepare_input($_POST['password_confirmation']);

    $error = false;

    if (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
        $error = true;
        $messageStack->add('reset_password', ENTRY_PASSWORD_NEW_ERROR);
    } elseif ($password_new !== $password_confirmation) {
        $error = true;
        $messageStack->add('reset_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
    }

    if ($error === false) {
        zcPassword::getInstance(PHP_VERSION)->updateLoggedInCustomerPassword($password_new, $customer_id);

        $sql = "UPDATE " . TABLE_CUSTOMERS_INFO . "
                SET customers_info_date_account_last_modified = now()
                WHERE customers_info_id = :customersID";
        $sql = $db->bindVars($sql, ':customersID', $customer_id, 'integer');
        $db->Execute($sql);

        $sql = "DELETE FROM " . TABLE_CUSTOMER_PASSWORD_RESET_TOKENS . " WHERE customer_id = :customerID";
        $sql = $db->bindVars($sql, ':customerID', $customer_id, 'integer');
        $db->Execute($sql);

        $messageStack->add_session('login', PASSWORD_RESET_SUCCESS_PASSWORD_UPDATED, 'success');

        // handle 3rd-party integrations
        // same notifier as main_page=account_password
        $zco_notifier->notify('NOTIFY_HEADER_ACCOUNT_PASSWORD_CHANGED', $customer_id, $password_new, $nickname);

        zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_PASSWORD_RESET');
