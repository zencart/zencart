<?php
/**
 * create_account header_php.php
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2025 Sep 24 Modified in v2.2.0 $
 */
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_MODULE_START_CREATE_ACCOUNT');

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
/**
 * Set some defaults
 */
$process = false;
$zone_name = '';
$entry_state_has_zones = '';
$error_state_input = false;
$state = '';
$zone_id = 0;
$error = false;
$email_format = (ACCOUNT_EMAIL_PREFERENCE === '1' ? 'HTML' : 'TEXT');
$newsletter = !(ACCOUNT_NEWSLETTER_STATUS === '1' || ACCOUNT_NEWSLETTER_STATUS === '0');
$extra_welcome_text = '';
$send_welcome_email = true;

$antiSpamFieldName = $_SESSION['antispam_fieldname'] ?? 'should_be_empty';

/**
 * Process form contents
 */
if (isset($_POST['action']) && ($_POST['action'] === 'process') && !isset($login_page)) {
    $process = true;
    $antiSpam = !empty($_POST[$antiSpamFieldName]) ? 'spam' : '';
    if (!empty($_POST['firstname']) && preg_match('~https?://?~', $_POST['firstname'])) {
        $antiSpam = 'spam';
    }
    if (!empty($_POST['lastname']) && preg_match('~https?://?~', $_POST['lastname'])) {
        $antiSpam = 'spam';
    }

    $zco_notifier->notify('NOTIFY_CREATE_ACCOUNT_CAPTCHA_CHECK', $antiSpamFieldName, $antiSpam);

    $gender = false;
    if (ACCOUNT_GENDER === 'true' && isset($_POST['gender'])) {
        $gender = zen_db_prepare_input($_POST['gender']);
    }

    $email_format = 'TEXT';
    if (isset($_POST['email_format'])) {
        if (!in_array($_POST['email_format'], ['HTML', 'TEXT', 'NONE', 'OUT'], true)) {
            $antiSpam = 'spam';
        } else {
            $email_format = $_POST['email_format'];
        }
    }

    $company = '';
    $dob = '';
    $suburb = '';
    $state = '';
    $zone_id = false;
    if (ACCOUNT_COMPANY === 'true') {
        $company = zen_db_prepare_input($_POST['company']);
    }
    $firstname = zen_db_prepare_input(zen_sanitize_string($_POST['firstname']));
    $lastname = zen_db_prepare_input(zen_sanitize_string($_POST['lastname']));
    $nick = zen_db_prepare_input($_POST['nick'] ?? '');
    if (ACCOUNT_DOB === 'true') {
        $dob = zen_db_prepare_input($_POST['dob']);
    }
    $email_address = zen_db_prepare_input($_POST['email_address']);
    $street_address = zen_db_prepare_input($_POST['street_address']);
    if (ACCOUNT_SUBURB === 'true') {
        $suburb = zen_db_prepare_input($_POST['suburb']);
    }
    $postcode = zen_db_prepare_input($_POST['postcode']);
    $city = zen_db_prepare_input($_POST['city']);
    if (ACCOUNT_STATE === 'true') {
        $state = zen_db_prepare_input($_POST['state'] ?? '');
        if (isset($_POST['zone_id'])) {
            $zone_id = zen_db_prepare_input($_POST['zone_id']);
        }
    }
    $country = zen_db_prepare_input($_POST['zone_country_id']);
    $telephone = zen_db_prepare_input($_POST['telephone']);
    $fax = zen_db_prepare_input($_POST['fax'] ?? '');
    $customers_authorization = (int)CUSTOMERS_APPROVAL_AUTHORIZATION;
    $customers_referral = zen_db_prepare_input($_POST['customers_referral'] ?? '');

    $newsletter = 0;
    if ((ACCOUNT_NEWSLETTER_STATUS === '1' || ACCOUNT_NEWSLETTER_STATUS === '2') && isset($_POST['newsletter'])) {
        $newsletter = zen_db_prepare_input($_POST['newsletter']);
    }

    $password = zen_db_prepare_input($_POST['password']);
    $confirmation = zen_db_prepare_input($_POST['confirmation']);


    if (DISPLAY_PRIVACY_CONDITIONS === 'true') {
        if (!isset($_POST['privacy_conditions']) || ($_POST['privacy_conditions'] !== '1')) {
            $error = true;
            $messageStack->add('create_account', ERROR_PRIVACY_STATEMENT_NOT_ACCEPTED, 'error');
        }
    }

    if (ACCOUNT_GENDER === 'true' && !in_array(strtolower($gender), ['m', 'f'])) {
        $error = true;
        $messageStack->add('create_account', ENTRY_GENDER_ERROR);
    }

    if (mb_strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_FIRST_NAME_ERROR);
    }

    if (mb_strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_LAST_NAME_ERROR);
    }

    if (ACCOUNT_DOB === 'true') {
        if (ENTRY_DOB_MIN_LENGTH > 0 or !empty($_POST['dob'])) {
            if (strlen($dob) >10 || zen_valid_date($dob) === false) {
                $error = true;
                $messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
            }
        }
    }

    if (ACCOUNT_COMPANY === 'true') {
        if ((int)ENTRY_COMPANY_MIN_LENGTH > 0 && mb_strlen($company) < ENTRY_COMPANY_MIN_LENGTH) {
            $error = true;
            $messageStack->add('create_account', ENTRY_COMPANY_ERROR);
        }
    }


    $nick_error = false;
    if (mb_strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR);
    } elseif (zen_validate_email($email_address) == false) {
        $error = true;
        $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    } else {

        $already_exists = !zen_check_email_address_not_already_used($email_address);
        $zco_notifier->notify('NOTIFY_CREATE_ACCOUNT_LOOKUP_BY_EMAIL', $email_address, $already_exists, $send_welcome_email);

        if ($already_exists) {
            $error = true;
            $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
        } else {
            $nick_error = false;
            $zco_notifier->notify('NOTIFY_NICK_CHECK_FOR_EXISTING_EMAIL', $email_address, $nick_error, $nick);
            if ($nick_error) {
                $error = true;
            }
        }
    }

    $nick_length_min = ENTRY_NICK_MIN_LENGTH;
    $zco_notifier->notify('NOTIFY_NICK_CHECK_FOR_MIN_LENGTH', $nick, $nick_error, $nick_length_min);
    if ($nick_error) {
        $error = true;
    }
    $zco_notifier->notify('NOTIFY_NICK_CHECK_FOR_DUPLICATE', $nick, $nick_error);
    if ($nick_error) {
        $error = true;
    }

    // check Zen Cart for duplicate nickname
    if ($error === false && !empty($nick)) {
        $sql = "SELECT * FROM " . TABLE_CUSTOMERS . " WHERE customers_nick = :nick:";
        $check_nick_query = $db->bindVars($sql, ':nick:', $nick, 'string');
        $check_nick = $db->Execute($check_nick_query, 1);
        if (!$check_nick->EOF) {
            $error = true;
            $messageStack->add('create_account', ENTRY_NICK_DUPLICATE_ERROR);
        }
    }

    if (mb_strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (mb_strlen($city) < ENTRY_CITY_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_CITY_ERROR);
    }

    if (ACCOUNT_STATE === 'true') {
        $check_query =
            "SELECT COUNT(*) AS total
               FROM " . TABLE_ZONES . "
              WHERE zone_country_id = :zoneCountryID";
        $check_query = $db->bindVars($check_query, ':zoneCountryID', $country, 'integer');
        $check = $db->Execute($check_query);
        $entry_state_has_zones = ($check->fields['total'] !== '0');
        if ($entry_state_has_zones === true) {
            $zone_query =
                "SELECT DISTINCT zone_id, zone_name, zone_code
                   FROM " . TABLE_ZONES . "
                  WHERE zone_country_id = :zoneCountryID
                    AND " .
                        ((trim($state) !== '' && (int)$zone_id === 0) ? "(UPPER(zone_name) LIKE ':zoneState%' OR UPPER(zone_code) LIKE '%:zoneState%') OR " : '') .
                        "zone_id = :zoneID
                  ORDER BY zone_code ASC, zone_name";

            $zone_query = $db->bindVars($zone_query, ':zoneCountryID', $country, 'integer');
            $zone_query = $db->bindVars($zone_query, ':zoneState', strtoupper($state), 'noquotestring');
            $zone_query = $db->bindVars($zone_query, ':zoneID', $zone_id, 'integer');
            $zone = $db->Execute($zone_query);

            //look for an exact match on zone ISO code
            $found_exact_iso_match = ((int)$zone->RecordCount() === 1);
            if ((int)$zone->RecordCount() > 1) {
                $state_uppercased = strtoupper($state);
                foreach ($zone as $next_zone) {
                    if (strtoupper($next_zone['zone_code']) === $state_uppercased || strtoupper($next_zone['zone_name']) === $state_uppercased) {
                        $found_exact_iso_match = true;
                        break;
                    }
                }
            }

            if ($found_exact_iso_match === true) {
                $zone_id = $zone->fields['zone_id'];
                $zone_name = $zone->fields['zone_name'];
            } else {
                $error = true;
                $error_state_input = true;
                $messageStack->add('create_account', ENTRY_STATE_ERROR_SELECT);
            }
        } elseif (mb_strlen($state) < ENTRY_STATE_MIN_LENGTH) {
            $error = true;
            $error_state_input = true;
            $messageStack->add('create_account', ENTRY_STATE_ERROR);
        }
    }

    if (mb_strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_POST_CODE_ERROR);
    }

    if (is_numeric($country) === false || $country < 1) {
        $error = true;
        $messageStack->add('create_account', ENTRY_COUNTRY_ERROR);
    }

    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_TELEPHONE_NUMBER_ERROR);
    }

    $zco_notifier->notify('NOTIFY_CREATE_ACCOUNT_VALIDATION_CHECK', [], $error, $send_welcome_email);

    if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_PASSWORD_ERROR);
    } elseif ($password !== $confirmation) {
        $error = true;
        $messageStack->add('create_account', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
    }

    if ($error === true) {
        // hook notifier class
        $zco_notifier->notify('NOTIFY_FAILURE_DURING_CREATE_ACCOUNT');
    } elseif ($antiSpam !== '') {
        $zco_notifier->notify('NOTIFY_SPAM_DETECTED_DURING_CREATE_ACCOUNT');
        $messageStack->add_session('header', (defined('ERROR_CREATE_ACCOUNT_SPAM_DETECTED') ? ERROR_CREATE_ACCOUNT_SPAM_DETECTED : 'Thank you, your account request has been submitted for review.'), 'success');
        zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
    } else {
        $ip_address = zen_get_ip_address();

        $customer = new Customer();

        $data = compact(
            'firstname', 'lastname', 'email_address', 'nick', 'email_format', 'telephone', 'fax',
            'newsletter', 'password', 'customers_authorization', 'customers_referral',
            'gender', 'dob', 'company', 'street_address',
            'suburb', 'city', 'zone_id', 'state', 'postcode', 'country', 'ip_address'
        );

        $result = $customer->create($data);
        if (!empty($result)) {
            $customer->login($result['customers_id'], $restore_cart = true);
            if (SESSION_RECREATE === 'True') {
                zen_session_recreate();
            }
        }

        // do any 3rd-party nick creation
        $nick_email = $email_address;
        $zco_notifier->notify('NOTIFY_NICK_CREATE_NEW', $nick, $password, $nick_email, $extra_welcome_text);

        if ($result['activation_required']) {
            require DIR_WS_MODULES . zen_get_module_directory(FILENAME_SEND_AUTH_TOKEN_EMAIL);
            zen_redirect(zen_href_link(CUSTOMERS_AUTHORIZATION_FILENAME, '', 'SSL'));
        }

        require DIR_WS_MODULES . zen_get_module_directory(FILENAME_CREATE_ACCOUNT_SEND_EMAIL);
        Customer::setWelcomeEmailSent((int)$result['customers_id']);

        zen_redirect(zen_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));

    } //endif !error
}

/*
 * Set flags for template use:
 */
$selected_country = !empty($_POST['zone_country_id']) ? (int)$_POST['zone_country_id'] : $country ?? (int)SHOW_CREATE_ACCOUNT_DEFAULT_COUNTRY;
$flag_show_pulldown_states = (ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN === 'true' || ($process === true && $entry_state_has_zones === true && $zone_name === '' && $error_state_input === true));
$state = ($flag_show_pulldown_states === true) ? ($state == '' ? '&nbsp;' : $state) : $zone_name;
$state_field_label = ($flag_show_pulldown_states === true) ? '' : ENTRY_STATE;

$display_nick_field = false;
$zco_notifier->notify('NOTIFY_NICK_SET_TEMPLATE_FLAG', 0, $display_nick_field);

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_MODULE_END_CREATE_ACCOUNT');
