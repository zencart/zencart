<?php
declare(strict_types=1);
/**
 * MFA functions for Multi-Factor Authentication
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 27 New in v2.1.0-alpha1 $
 */
require_once DIR_FS_ADMIN . DIR_WS_CLASSES . 'MultiFactorAuth.php';

/**
 * Here we set this constant to use the zen_mfa_handler() function as a broker for MFA operations
 */
zen_define_default('ZC_ADMIN_TWO_FACTOR_AUTHENTICATION_SERVICE', 'zen_mfa_handler');

/**
 * Broker for MFA activity.
 *
 * Checks whether MFA is enabled for the store.
 * Checks whether the current user has MFA configured, or is exempt from it.
 * Checks whether setup is required.
 * Depending on actual configured method for the user, dispatches appropriate MFA function.
 *
 * @param array $admin_info receives four values: admin_id, email, admin_name, mfa array
 * @return bool
 */
function zen_mfa_handler(array $admin_info = []): bool
{
    if (!isset($_SESSION['mfa'])) {
        $_SESSION['mfa'] = [];
    }

    $user_mfa_data = json_decode($admin_info['mfa'] ?? '', true, 2);

    $mfa_status_of_store = MFA_ENABLED === 'True';
    if ($mfa_status_of_store === false) {
        return true;
    }

    $mfa_exempt_for_user = !empty($user_mfa_data['exempt']);
    if ($mfa_exempt_for_user) {
        return true;
    }

    $mfa_user_using_email = !empty($user_mfa_data['via_email']);
    if ($mfa_user_using_email) {
        return zen_mfa_by_email($admin_info);
    }

    $mfa_user_using_otp = !empty($user_mfa_data['secret']);
    if ($mfa_user_using_otp) {
        return zen_mfa_by_totp($admin_info);
    }

    // MFA is required but user has not selected a method yet or hasn't verified first issued code yet
    $_SESSION['mfa']['setup_required'] = true;

    return true;
}

/**
 * MFA works as follows:
 *
 * An MFA function which generates MFA/OTP codes must supply certain details to Zen Cart via the $_SESSION['mfa'] array.
 *
 * THe function must populate the following values:
 * $_SESSION['mfa']['pending'] = true; -- this flags the system to halt and ask for the OTP code
 * $_SESSION['mfa']['qrcode'] = img URL -- the URL to display a QR code for storing a user's otp secret
 * $_SESSION['mfa']['secret'] = the otp secret to use for validation, and optionally to store for the user
 * $_SESSION['mfa']['length'] = length of the token, used by the input form to set HTML validation attributes
 * $_SESSION['mfa']['type'] = type of token: 'digits', 'alphanum', 'alpha' are accepted values, used for HTML field validation
 *
 * optional:
 * $_SESSION['mfa']['token'] = the manually-generated token to be confirmed (such as when sending bespoke codes via email)
 * $_SESSION['mfa']['expires'] = expiration time in epoch seconds; ie: strtotime('5 min'); optional - used when generating your own token instead of using Authenticator
 *
 * Then when the user supplies their username and password, an OTP is generated and the user is redirected
 * to the mfa page to enter the OTP token, where the value they enter gets checked against the session values above.
 * If the token has expired, or if they click Cancel, then the MFA page logs them out and they can start login again.
 */

/**
 * Prepare to do OTP MFA validation
 */
function zen_mfa_by_totp(array $admin_info = []): bool
{
    if (!isset($_SESSION['mfa'])) {
        $_SESSION['mfa'] = [];
    }

    $domain = str_replace(['http'.'://', 'https://'], '', HTTP_SERVER);

    $ga = new MultiFactorAuth();

    $user_mfa_data = json_decode($admin_info['mfa'] ?? '', true, 2);
    $secret = !empty($user_mfa_data['secret']) ? $user_mfa_data['secret'] : $ga->createSecret();
    if (empty($user_mfa_data['secret'])) {
        $_SESSION['mfa']['secret_not_yet_persisted'] = true;
        $qrCode = $ga->getQrCode($domain, $secret, $admin_info['admin_name'] ?? '', 200);
        $_SESSION['mfa']['qrcode'] = $qrCode;
    }

    // set system to expect MFA confirmation, so that login won't progress past getting this confirmation
    $_SESSION['mfa']['pending'] = true;

    $_SESSION['mfa']['secret'] = $secret;
    $_SESSION['mfa']['length'] = $ga->getCodeLength();
    $_SESSION['mfa']['type'] = 'digits';
    $_SESSION['mfa']['admin_id'] = (int)$admin_info['admin_id'];
    $_SESSION['mfa']['admin_name'] = $admin_info['admin_id'] . ':' . $admin_info['admin_name'];

    return true;
}

/**
 * Prepare to do MFA validation via email
 */
function zen_mfa_by_email(array $admin_info = []): bool
{
    if (!isset($_SESSION['mfa'])) {
        $_SESSION['mfa'] = [];
    }

    // set system to expect MFA confirmation, so that login won't progress past getting this confirmation
    $_SESSION['mfa']['pending'] = true;

    // if token already exists and isn't expired, re-use it
    if (!empty($_SESSION['mfa']['expires']) && $_SESSION['mfa']['expires'] > time()) {
        return true;
    }

    // generate a token to be used to supply confirmation
    $num_digits = 6;
    $_SESSION['mfa']['token'] = $token = zen_create_random_value($num_digits, 'digits');
    $_SESSION['mfa']['length'] = $num_digits;
    $_SESSION['mfa']['type'] = 'digits';
    $_SESSION['mfa']['expires'] = strtotime('5 min');
    $_SESSION['mfa']['admin_id'] = (int)$admin_info['admin_id'];
    $_SESSION['mfa']['admin_name'] = $admin_info['admin_id'] . ':' . $admin_info['admin_name'];

    // prepare email to send token
    $text_msg = sprintf(TEXT_MFA_EMAIL_BODY, $token, $_SERVER['REMOTE_ADDR']);
    $html_msg = [
        'EMAIL_CUSTOMERS_NAME' => $admin_info['email'],
        'EMAIL_MESSAGE_HTML' => sprintf(TEXT_MFA_EMAIL_BODY, $token, $_SERVER['REMOTE_ADDR']),
    ];
    // send email
    $email_response = zen_mail($admin_info['admin_name'], $admin_info['email'], TEXT_MFA_EMAIL_SUBJECT, $text_msg, STORE_NAME, EMAIL_FROM, $html_msg, 'no_archive');

    // zen_mail()'s response must be a blank string (it will be false on abort, or error message string on failure)
    return $email_response === '';
}
