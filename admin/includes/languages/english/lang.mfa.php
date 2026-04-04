<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Mar 23 New in v2.1.0-alpha1 $
*/

$define = [
    'HEADING_TITLE' => 'Admin Login Confirmation',
    'TEXT_MFA_INTRO' => 'Please enter the OTP code from your Authenticator app below.',
    'TEXT_MFA_BOTTOM' => 'If you have lost access to your Authenticator app, please contact the storeowner.',
    'TEXT_SUBMIT' => 'Submit',
    'TEXT_MFA_INPUT' => 'enter code',
    'TEXT_MFA_SELECT' => 'Select a method for Multi-Factor Authentication:',
    'ERROR_WRONG_CODE' => 'The token you entered is invalid.',
    'ERROR_SECURITY_ERROR' => 'There was a security error when trying to login.',
    'TEXT_ERROR_ATTEMPTED_MFA_LOGIN_WITHOUT_CSRF_TOKEN' => 'Invalid CSRF token during MFA validation',
    'TEXT_ERROR_ATTEMPTED_ADMIN_MFA_LOGIN_WITH_INVALID_CODE' => 'Invalid MFA token during two-factor-auth',
];

return $define;
