<?php
/**
 * create_account_send_email.php.  Split from modules/create_account.php for v2.2.0.
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
// A multi-use module to send a "welcome" email to a customer; required by any main
// processing header file.
//
// The 'parent' file must have set the following PHP variables for the currently logged-in
// customer:
//
// - $firstname
// - $last_name
// - $gender (if ACCOUNT_GENDER is set to 'true')
// - $email_address
//
if (IS_ADMIN_FLAG === false && !zen_is_logged_in()) {
    return;
}

// -----
// Load the language file containing various email constants.
//
$languageLoader->loadModuleLanguageFile('create_account_send_email.php', '');

$extra_welcome_text ??= '';
$send_welcome_email ??= true;

// hook notifier class
$zco_notifier->notify('NOTIFY_LOGIN_SUCCESS_VIA_CREATE_ACCOUNT', $email_address, $extra_welcome_text, $send_welcome_email);
if ($send_welcome_email !== true) {
    return;
}

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
    $coupon_id = (int)NEW_SIGNUP_DISCOUNT_COUPON;
    $coupon = $db->Execute(
        "SELECT * FROM " . TABLE_COUPONS . " WHERE coupon_id = " . (int)$coupon_id . " LIMIT 1"
    );
    $coupon_desc = $db->Execute(
        "SELECT coupon_description FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE coupon_id = " . (int)$coupon_id . " AND language_id = " . (int)$_SESSION['languages_id'] . " LIMIT 1"
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
    $insert_query = $db->Execute(
        "INSERT INTO " . TABLE_COUPONS . "
            (coupon_code, coupon_type, coupon_amount, date_created)
         VALUES
            ('" . $coupon_code . "', 'G', '" . NEW_SIGNUP_GIFT_VOUCHER_AMOUNT . "', now())"
    );
    $insert_id = $db->insert_ID();
    $db->Execute(
        "INSERT INTO " . TABLE_COUPON_EMAIL_TRACK . "
            (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent)
         VALUES
            (" . (int)$insert_id . ", 0, 'Admin', '" . $email_address . "', now() )"
    );

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
if (IS_ADMIN_FLAG === false && SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_STATUS === '1' && SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO !== '' && isset($_SESSION['customer_id'])) {
    $sql = "SELECT customers_firstname, customers_lastname, customers_email_address, customers_telephone, customers_fax
            FROM " . TABLE_CUSTOMERS . "
            WHERE customers_id = " . (int)$_SESSION['customer_id'];
    $account = $db->Execute($sql, 1);

    $extra_info = email_collect_extra_info($name, $email_address, $account->fields['customers_firstname'] . ' ' . $account->fields['customers_lastname'], $account->fields['customers_email_address'], $account->fields['customers_telephone'], $account->fields['customers_fax']);
    $html_msg['EXTRA_INFO'] = $extra_info['HTML'];
    if (trim(SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_SUBJECT) !== 'n/a') {
        zen_mail('', SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO, SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_SUBJECT . ' ' . EMAIL_SUBJECT, $email_text . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg, 'welcome_extra');
    }
} //endif send extra emails
