<?php
$define = [
    'NAVBAR_TITLE_1' => 'Login',
    'NAVBAR_TITLE_2' => 'Password Forgotten',
    'HEADING_TITLE' => 'Forgotten Password',
    'TEXT_MAIN' => 'Enter your email address below and we\'ll send you an email message containing your new password.',
    'EMAIL_PASSWORD_REMINDER_SUBJECT' => STORE_NAME . ' - New Password',
    'EMAIL_PASSWORD_REMINDER_BODY' => 'A new password was requested from ' . $_SERVER['REMOTE_ADDR'] . '.' . "\n\n" . 'Your new password to \'' . STORE_NAME . '\' is:' . "\n\n" . '   %s' . "\n\nAfter you have logged in using the new password, you may change it by going to the 'My Account' area.",
    'SUCCESS_PASSWORD_SENT' => 'Thank you. If that email address is in our system, we will send password recovery instructions to that email address (remember to check your Spam folder)',
];

return $define;
