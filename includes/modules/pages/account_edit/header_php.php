<?php
/**
 * Header code file for the customer's Account-Edit page
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2025 Sep 24 Modified in v2.2.0 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ACCOUNT_EDIT');

if (!zen_is_logged_in() || zen_in_guest_checkout()) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

require DIR_WS_MODULES . zen_get_module_directory('require_languages.php');

$error = false;

if (!empty($_POST['action']) && $_POST['action'] === 'process') {
    if (ACCOUNT_GENDER === 'true') {
        $gender = zen_db_prepare_input($_POST['gender']);
    }
    $firstname = zen_db_prepare_input($_POST['firstname']);
    $lastname = zen_db_prepare_input($_POST['lastname']);
    $nick = (!empty($_POST['nick']) ? zen_db_prepare_input($_POST['nick']) : '');
    if (ACCOUNT_DOB === 'true') {
        $dob = (empty($_POST['dob']) ? zen_db_prepare_input('0001-01-01 00:00:00') : zen_db_prepare_input($_POST['dob']));
    }
    $email_address = zen_db_prepare_input($_POST['email_address']);
    $telephone = zen_db_prepare_input($_POST['telephone']);
    $fax = zen_db_prepare_input($_POST['fax'] ?? '');
    $email_format = in_array($_POST['email_format'], ['HTML', 'TEXT', 'NONE', 'OUT'], true) ? $_POST['email_format'] : 'TEXT';

    $customers_referral = ''; 
    if (CUSTOMERS_REFERRAL_STATUS === '2' && !empty($_POST['customers_referral']) ) {
        $customers_referral = zen_db_prepare_input($_POST['customers_referral']);
    }

    if (ACCOUNT_GENDER === 'true' && $gender !== 'm' && $gender !== 'f') {
        $error = true;
        $messageStack->add('account_edit', ENTRY_GENDER_ERROR);
    }

    if (mb_strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
        $error = true;
        $messageStack->add('account_edit', ENTRY_FIRST_NAME_ERROR);
    }

    if (mb_strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
        $error = true;
        $messageStack->add('account_edit', ENTRY_LAST_NAME_ERROR);
    }

    if (ACCOUNT_DOB === 'true' && (ENTRY_DOB_MIN_LENGTH > 0 || !empty($_POST['dob']))) {
        if (strlen($dob) > 10 || zen_valid_date($dob) === false) {
            $error = true;
            $messageStack->add('account_edit', ENTRY_DATE_OF_BIRTH_ERROR);
        }
    }

    if (mb_strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
        $error = true;
        $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR);
    }

    if (!zen_validate_email($email_address)) {
        $error = true;
        $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }

    $check_email_query =
        "SELECT COUNT(*) AS total
           FROM " . TABLE_CUSTOMERS . "
          WHERE customers_email_address = :emailAddress
            AND customers_id != :customersID";

    $check_email_query = $db->bindVars($check_email_query, ':emailAddress', $email_address, 'string');
    $check_email_query = $db->bindVars($check_email_query, ':customersID', $_SESSION['customer_id'], 'integer');
    $check_email = $db->Execute($check_email_query);

    if ($check_email->fields['total'] > 0) {
        $error = true;
        $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
    }

    // check external hook for duplicate email address, so we can reject the change if duplicates aren't allowed externally
    // (the observers should set any messageStack output as needed)
    $nick_error = false;
    $zco_notifier->notify('NOTIFY_NICK_CHECK_FOR_EXISTING_EMAIL', $email_address, $nick_error, $nick);
    if ($nick_error) {
        $error = true;
    }


    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
        $error = true;
        $messageStack->add('account_edit', ENTRY_TELEPHONE_NUMBER_ERROR);
    }

    $zco_notifier->notify('NOTIFY_HEADER_ACCOUNT_EDIT_VERIFY_COMPLETE');

    if ($error === false) {
        //update external bb system with submitted email address
        $zco_notifier->notify('NOTIFY_NICK_UPDATE_EMAIL_ADDRESS', $nick, $email_address);

        // build array of data to store the requested changes
        $sql_data_array = [
            ['fieldName' => 'customers_firstname', 'value' => $firstname, 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_lastname', 'value' => $lastname, 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_email_address', 'value' => $email_address, 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_telephone', 'value' => $telephone, 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_fax', 'value' => $fax, 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'customers_email_format', 'value' => $email_format, 'type' => 'stringIgnoreNull'],
        ];

        if (CUSTOMERS_REFERRAL_STATUS === '2' && $customers_referral !== '') {
            $sql_data_array[] = ['fieldName' => 'customers_referral', 'value' => $customers_referral, 'type' => 'stringIgnoreNull'];
        }
        if (ACCOUNT_GENDER === 'true') {
            $sql_data_array[] = ['fieldName' => 'customers_gender', 'value' => $gender, 'type' => 'stringIgnoreNull'];
        }
        if (ACCOUNT_DOB === 'true') {
            if ($dob === '0001-01-01 00:00:00' || $_POST['dob'] === '') {
                $sql_data_array[] = ['fieldName' => 'customers_dob', 'value' => '0001-01-01 00:00:00', 'type' => 'date'];
            } else {
                $sql_data_array[] = ['fieldName' => 'customers_dob', 'value' => zen_date_raw($_POST['dob']), 'type' => 'date'];
            }
        }

        $customer = new Customer();
        $email_address_changed = false;
        if (CUSTOMERS_ACTIVATION_REQUIRED === 'true' && $customer->getData('customers_email_address') !== $email_address) {
            $email_address_changed = true;
            $sql_data_array[] = ['fieldName' => 'activation_required', 'value' => 1, 'type' => 'integer'];
            $sql_data_array[] = ['fieldName' => 'customers_authorization', 'value' => Customer::AUTH_NO_PURCHASE, 'type' => 'integer'];
        }

        $customer_data = $customer->update($sql_data_array);

        $sql_data_array = [
            ['fieldName' => 'entry_firstname', 'value' => $firstname, 'type' => 'stringIgnoreNull'],
            ['fieldName' => 'entry_lastname', 'value' => $lastname, 'type' => 'string'],
        ];
        $customer->updatePrimaryAddress($sql_data_array);

        $zco_notifier->notify('NOTIFY_HEADER_ACCOUNT_EDIT_UPDATES_COMPLETE');

        // reset the session variables
        $_SESSION['customer_first_name'] = $firstname;
        $_SESSION['customer_last_name'] = $lastname;
        $_SESSION['customers_email_address'] = $email_address;
        $_SESSION['customers_authorization'] = (int)$customer_data['customers_authorization'];

        $messageStack->add_session('account', SUCCESS_ACCOUNT_UPDATED, 'success');

        if ($customer_data['activation_required']) {
            $auth_token_info = $customer->getAuthTokenInfo();
            $token_valid_minutes = Customer::getAuthTokenMinutesValid();
            if ($auth_token_info === false || $auth_token_info['email_address'] !== $email_address || strtotime($auth_token_info['created_at']) + $token_valid_minutes > time()) {
                require DIR_WS_MODULES . zen_get_module_directory(FILENAME_SEND_AUTH_TOKEN_EMAIL);
            }
            zen_redirect(zen_href_link(CUSTOMERS_AUTHORIZATION_FILENAME, '', 'SSL'));
        }

        zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }
}

$customer = new Customer();
$account_data = $customer->getData();
if (ACCOUNT_GENDER === 'true') {
    if (isset($gender)) {
        $male = ($gender === 'm');
    } else {
        $male = ($account_data['customers_gender'] === 'm');
    }
    $female = !$male;
}

if (($_POST['action'] ?? '') !== 'process') {
    $dob = zen_date_short($account_data['customers_dob']);
    if ($dob <= '0001-01-01') {
        $dob = '0001-01-01 00:00:00';
    }
}
// if DOB field has database default setting, show blank:
$dob = (empty($dob) || $dob === '0001-01-01 00:00:00') ? '' : $dob;

$customers_referral = $account_data['customers_referral'];

if (isset($customers_email_format)) {
    $email_pref_html = ($customers_email_format === 'HTML');
    $email_pref_none = ($customers_email_format === 'NONE');
    $email_pref_optout = ($customers_email_format === 'OUT');
} else {
    $email_pref_html = ($account_data['customers_email_format'] === 'HTML');
    $email_pref_none = ($account_data['customers_email_format'] === 'NONE');
    $email_pref_optout = ($account_data['customers_email_format'] === 'OUT');
}
$email_pref_text = !($email_pref_html || $email_pref_none || $email_pref_optout);  // if not in any of the others, assume TEXT

// -----
// Convert customer's account-data array to mimic a MySQL object returned for
// template compatibility.
//
$account = new stdClass();
$account->fields = $account_data;

$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ACCOUNT_EDIT');
