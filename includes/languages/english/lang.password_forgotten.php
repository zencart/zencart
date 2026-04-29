<?php

$password_reset_button_colour = '#00BCE4';//change to stores theme colour

$define = [
    'NAVBAR_TITLE_1' => 'Login',
    'NAVBAR_TITLE_2' => 'Password Forgotten',
    'HEADING_TITLE' => 'Forgotten Password',

    'TEXT_MAIN' => "Enter your email address below and we'll send you instructions on how to reset your password.",

    'EMAIL_PASSWORD_RESET_SUBJECT' => STORE_NAME . ' - Password Reset',

    'EMAIL_PASSWORD_RESET_BODY' =>
        "Hello,\n\n" .
        "We received a request to reset the password for your %2\$s account.\n\n" .
        "To choose a new password, please click the link below:\n\n" .
        "%3\$s\n\n" .
        "This link is for password reset only. If you did not request this, you can safely ignore this email and your password will not be changed.\n\n" .
        "For your security, this request was made from IP address: %1\$s\n\n" .
        "Kind regards,\n" .
        STORE_NAME . "\n",

    'EMAIL_PASSWORD_RESET_HTML' =>
        '<p>Hello,</p>' .
        '<p>We received a request to reset the password for your %2$s account.</p>' .
        '<p>To choose a new password, please click the button below:</p>' .
        '<p><a href="%3$s" style="display:inline-block;padding:10px 16px;background:' . $password_reset_button_colour . ';color:#ffffff;text-decoration:none;border-radius:4px;font-weight:bold;">Reset your password</a></p>' .
        '<p>Or copy and paste this link into your browser:<br><a href="%3$s">%3$s</a></p>' .
        '<p>This link is for password reset only. If you did not request this, you can safely ignore this email and your password will not be changed.</p>' .
        '<p>For your security, this request was made from IP address: %1$s</p>' .
        '<p>Kind regards,<br>' .
        STORE_NAME .
        '</p>',

    'SUCCESS_PASSWORD_RESET_SENT' =>
        'Thank you. If that email address is in our system, we will send password recovery instructions to that email address. Please check your Spam folder if it does not arrive shortly.',
];

return $define;
