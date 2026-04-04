<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 27 Modified in v2.1.0-alpha1 $
*/

$define = [
    'HEADING_TITLE' => 'Reset Password',
    'TEXT_ADMIN_EMAIL' => 'Admin Email Address',
    'TEXT_ADMIN_USERNAME' => 'Admin Username',
    'TEXT_BUTTON_REQUEST_RESET' => 'Request Reset',
    'TEXT_BUTTON_LOGIN' => 'Login',
    'TEXT_BUTTON_CANCEL' => 'Cancel',
    'ERROR_WRONG_EMAIL' => 'You entered the wrong email address.',
    'ERROR_WRONG_EMAIL_NULL' => 'Go away gooberbrain :-P',
    'MESSAGE_PASSWORD_SENT' => 'Thank you. If the email address and username you entered matches an admin account in our database, then a new password will be sent to that email address.<br>Please read that email and then click "login" to use the new temporary password.',
    'TEXT_EMAIL_SUBJECT_PWD_RESET' => 'Your Requested change',
    'TEXT_EMAIL_MESSAGE_PWD_RESET' => 'A new password was requested from %1$s.' . "\n\n" . 'Your new temporary password is:' . "\n\n" . '%2$s' . "\n\nYou will be asked to choose a new password before logging in.\n\nThis temporary password expires in 24 hours.\n\n\n",
    'TEXT_EMAIL_SUBJECT_PWD_FAILED_RESET' => 'Access Alert!',
    'TEXT_EMAIL_MESSAGE_PWD_FAILED_RESET' => "Failed attempts for admin password resets have been received from %s\n\nInvalid email and/or username supplied.\n\nIf you have admin accounts sharing the same email address you should consider assigning unique email addresses to them, to make resets easier.",
];

return $define;
