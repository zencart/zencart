<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piloujp 2025 Jun 30 New in v2.2.0 $
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
$result = Customer::getPasswordResetTokenInfo($reset_token);

if ($result === false) {
    // no matching token found within date range of unexpired tokens
    $messageStack->add('reset_password', PASSWORD_RESET_ENTRY_PASSWORD_TOKEN_ERROR);
    $error = true;
    $token_error = true;
} else {
    $customer_id = $result['customers_id'];
    $nickname = $result['customers_nick'];
}

if (isset($_POST['action']) && ($_POST['action'] === 'process') && !empty($customer_id)) {
    $password_new = zen_db_prepare_input($_POST['password_new']);
    $password_confirmation = zen_db_prepare_input($_POST['password_confirmation']);

    $error = false;

    if (mb_strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
        $error = true;
        $messageStack->add('reset_password', ENTRY_PASSWORD_NEW_ERROR);
    } elseif ($password_new !== $password_confirmation) {
        $error = true;
        $messageStack->add('reset_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
    }

    if ($error === false) {
        $customer = new Customer($customer_id);
        $customer->setPassword($password_new);

        $messageStack->add_session('login', PASSWORD_RESET_SUCCESS_PASSWORD_UPDATED, 'success');

        // handle 3rd-party integrations
        // same notifier as main_page=account_password
        $zco_notifier->notify('NOTIFY_HEADER_ACCOUNT_PASSWORD_CHANGED', $customer_id, $password_new, $nickname);

        zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_PASSWORD_RESET');
