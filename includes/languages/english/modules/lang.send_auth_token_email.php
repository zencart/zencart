<?php
$define = [
    'EMAIL_AUTH_TOKEN_SUBJECT' => STORE_NAME . ' - Activate Account',
    'EMAIL_AUTH_TOKEN_BODY' => "To activate your account, please click the link below or copy and paste the entire link into your browser:\n\n%1\$s\n\nThis link expires in %2\$u minutes.",

    'SUCCESS_AUTH_TOKEN_SENT' => 'An email was sent to your account email address (%1$s). Follow the instructions in that email to activate your account and be sure to check your SPAM.',
];
return $define;
