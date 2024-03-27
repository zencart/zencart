<?php
/**
 * MFA functions
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v2.0.0 $
 */

/**
 * Define this constant to the name of the function that handles establishing a 2FA token.
 * Blank to disable.
 */
zen_define_default('ZC_ADMIN_TWO_FACTOR_AUTHENTICATION_SERVICE', 'zen_mfa_by_email');

/**
 * MFA works as follows:
 *
 * An MFA function which generates OTP codes must supply those details to Zen Cart via the
 * $_SESSION['mfa'] array. Your function must populate the following values:
 * $_SESSION['mfa']['pending'] = true; -- this flags the system to halt and ask for the OTP code
 * $_SESSION['mfa']['token'] = the token to be confirmed
 * $_SESSION['mfa']['length'] = length of the token
 * $_SESSION['mfa']['type'] = type of token: 'digits', 'alphanum', 'alpha' are accepted values
 * $_SESSION['mfa']['expires'] = expiration time in epoch seconds; ie: strtotime('5 min');
 *
 * Then when the user supplies their username and password, an OTP is generated and the user is redirected
 * to the mfa page to enter the OTP token, where the value they enter gets checked against the session values above.
 * If the token has expired, or if they click Cancel, then the MFA page logs them out and they can start login again.
 */


/**
 * @param array $params receives three values: [0]=admin_id, [1]=admin_email, [2]=admin_name
 * @return bool
 */
function zen_mfa_by_email(array $params): bool
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
    $_SESSION['mfa']['token'] = $token = zen_create_random_value(6, 'digits');
    $_SESSION['mfa']['length'] = 6;
    $_SESSION['mfa']['type'] = 'digits';
    $_SESSION['mfa']['expires'] = strtotime('5 min');


    // prepare email to send token
    $text_msg = sprintf(TEXT_MFA_EMAIL_BODY, $token, $_SERVER['REMOTE_ADDR']);
    $html_msg = [
        'EMAIL_CUSTOMERS_NAME' => $params[2],
        'EMAIL_MESSAGE_HTML' => sprintf(TEXT_MFA_EMAIL_BODY, $token, $_SERVER['REMOTE_ADDR']),
    ];
    // send email
    $email_response = zen_mail($params[2], $params[1], TEXT_MFA_EMAIL_SUBJECT, $text_msg, STORE_NAME, EMAIL_FROM, $html_msg, 'no_archive');

    // The email response must be a blank string (it will be false on abort, or error message string on failure)
    return $email_response === '';
}
