<?php
/**
 * create_account header_php.php
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 13 Modified in v2.1.0 $
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

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
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
        if ((int)ENTRY_COMPANY_MIN_LENGTH > 0 && strlen($company) < ENTRY_COMPANY_MIN_LENGTH) {
            $error = true;
            $messageStack->add('create_account', ENTRY_COMPANY_ERROR);
        }
    }


    $nick_error = false;
    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
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

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
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
        } elseif (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
            $error = true;
            $error_state_input = true;
            $messageStack->add('create_account', ENTRY_STATE_ERROR);
        }
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
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

        // hook notifier class
        $zco_notifier->notify('NOTIFY_LOGIN_SUCCESS_VIA_CREATE_ACCOUNT', $email_address, $extra_welcome_text, $send_welcome_email);

        if ($send_welcome_email) {
            // build the message content
            $name = $firstname . ' ' . $lastname;

            if (ACCOUNT_GENDER === 'true') {
                $email_text = sprintf(($gender === 'm') ? EMAIL_GREET_MR : EMAIL_GREET_MS, $lastname);
            } else {
                $email_text = sprintf(EMAIL_GREET_NONE, $firstname);
            }
            $html_msg['EMAIL_GREETING'] = str_replace('\n', '', $email_text);
            $html_msg['EMAIL_FIRST_NAME'] = $firstname;
            $html_msg['EMAIL_LAST_NAME'] = $lastname;

            // initial welcome
            $email_text .= EMAIL_WELCOME . $extra_welcome_text;
            $html_msg['EMAIL_WELCOME'] = str_replace('\n', '', EMAIL_WELCOME . $extra_welcome_text);

            if (NEW_SIGNUP_DISCOUNT_COUPON !== '' && NEW_SIGNUP_DISCOUNT_COUPON !== '0') {
                $coupon_id = NEW_SIGNUP_DISCOUNT_COUPON;
                $coupon = $db->Execute(
                    "SELECT * FROM " . TABLE_COUPONS . " WHERE coupon_id = '" . $coupon_id . "'"
                );
                $coupon_desc = $db->Execute(
                    "SELECT coupon_description FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = '" . $coupon_id . "' AND language_id = '" . $_SESSION['languages_id'] . "'"
                );
                $db->Execute(
                    "INSERT INTO " . TABLE_COUPON_EMAIL_TRACK . "
                        (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent)
                     VALUES
                        ('" . $coupon_id . "', '0', 'Admin', '" . $email_address . "', now())"
                );

                $text_coupon_help = sprintf(TEXT_COUPON_HELP_DATE, zen_date_short($coupon->fields['coupon_start_date']), zen_date_short($coupon->fields['coupon_expire_date']));

                // if on, add in Discount Coupon explanation
                //        $email_text .= EMAIL_COUPON_INCENTIVE_HEADER .
                $email_text .=
                    "\n" . EMAIL_COUPON_INCENTIVE_HEADER .
                    (!empty($coupon_desc->fields['coupon_description']) ? $coupon_desc->fields['coupon_description'] . "\n\n" : '') .
                    $text_coupon_help . "\n\n" .
                    strip_tags(sprintf(EMAIL_COUPON_REDEEM, ' ' . $coupon->fields['coupon_code'])) . EMAIL_SEPARATOR;

                $html_msg['COUPON_TEXT_VOUCHER_IS'] = EMAIL_COUPON_INCENTIVE_HEADER;
                $html_msg['COUPON_DESCRIPTION'] = (!empty($coupon_desc->fields['coupon_description']) ? '<strong>' . $coupon_desc->fields['coupon_description'] . '</strong>' : '');
                $html_msg['COUPON_TEXT_TO_REDEEM'] = str_replace("\n", '', sprintf(EMAIL_COUPON_REDEEM, ''));
                $html_msg['COUPON_CODE'] = $coupon->fields['coupon_code'] . $text_coupon_help;
            } //endif coupon

            if (NEW_SIGNUP_GIFT_VOUCHER_AMOUNT > 0) {
                $coupon_code = Coupon::generateRandomCouponCode();
                $insert_query = $db->Execute("INSERT INTO " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, date_created) VALUES ('" . $coupon_code . "', 'G', '" . NEW_SIGNUP_GIFT_VOUCHER_AMOUNT . "', now())");
                $insert_id = $db->Insert_ID();
                $db->Execute("INSERT INTO " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) VALUES ('" . $insert_id . "', '0', 'Admin', '" . $email_address . "', now() )");

                // if on, add in GV explanation
                $email_text .=
                    "\n\n" . sprintf(EMAIL_GV_INCENTIVE_HEADER, $currencies->format(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT)) .
                    sprintf(EMAIL_GV_REDEEM, $coupon_code) .
                    EMAIL_GV_LINK . zen_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $coupon_code, 'NONSSL', false) . "\n\n" .
                    EMAIL_GV_LINK_OTHER . EMAIL_SEPARATOR;
                $html_msg['GV_WORTH'] = str_replace('\n', '', sprintf(EMAIL_GV_INCENTIVE_HEADER, $currencies->format(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT)));
                $html_msg['GV_REDEEM'] = str_replace('\n', '', str_replace('\n\n', '<br>', sprintf(EMAIL_GV_REDEEM, '<strong>' . $coupon_code . '</strong>')));
                $html_msg['GV_CODE_NUM'] = $coupon_code;
                $html_msg['GV_CODE_URL'] = str_replace('\n', '', EMAIL_GV_LINK . '<a href="' . zen_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $coupon_code, 'NONSSL', false) . '">' . TEXT_GV_NAME . ': ' . $coupon_code . '</a>');
                $html_msg['GV_LINK_OTHER'] = EMAIL_GV_LINK_OTHER;
            } // endif voucher

            // add in regular email welcome text
            $email_text .= "\n\n" . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_GV_CLOSURE;

            $html_msg['EMAIL_MESSAGE_HTML'] = str_replace('\n', '', EMAIL_TEXT);
            $html_msg['EMAIL_CONTACT_OWNER'] = str_replace('\n', '', EMAIL_CONTACT);
            $html_msg['EMAIL_CLOSURE'] = nl2br(EMAIL_GV_CLOSURE);

            // include create-account-specific disclaimer
            $email_text .= "\n\n" . sprintf(EMAIL_DISCLAIMER_NEW_CUSTOMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";
            $html_msg['EMAIL_DISCLAIMER'] = sprintf(EMAIL_DISCLAIMER_NEW_CUSTOMER, '<a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . ' </a>');

            // send welcome email
            if (trim(EMAIL_SUBJECT) !== 'n/a') {
                zen_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_NAME, EMAIL_FROM, $html_msg, 'welcome');
            }

            // send additional emails
            if (SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_STATUS === '1' && SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO !== '') {
                if (zen_is_logged_in()) {
                    $sql = "SELECT customers_firstname, customers_lastname, customers_email_address, customers_telephone, customers_fax
                            FROM " . TABLE_CUSTOMERS . "
                            WHERE customers_id = " . (int)$_SESSION['customer_id'];
                    $account = $db->Execute($sql, 1);
                }

                $extra_info = email_collect_extra_info($name, $email_address, $account->fields['customers_firstname'] . ' ' . $account->fields['customers_lastname'], $account->fields['customers_email_address'], $account->fields['customers_telephone'], $account->fields['customers_fax']);
                $html_msg['EXTRA_INFO'] = $extra_info['HTML'];
                if (trim(SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_SUBJECT) !== 'n/a') {
                    zen_mail('', SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO, SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_SUBJECT . ' ' . EMAIL_SUBJECT, $email_text . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg, 'welcome_extra');
                }
            } //endif send extra emails
        }
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
