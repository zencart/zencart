<?php
$define = [
    'NAVBAR_TITLE' => 'Account Authorization Pending',
    'HEADING_TITLE' => 'Account Authorization Pending ...',
    'HEADING_TITLE_ACTIVATE' => 'Check your email',

    'CUSTOMERS_AUTHORIZATION_TEXT_INFORMATION' => 'Your account is being reviewed for authorization.',
    'CUSTOMERS_AUTHORIZATION_STATUS_TEXT' => 'To verify your authorization status ... click here:',

    'SUCCESS_AUTHORIZED' => 'Your account is now authorized for shopping. You might have other browser windows open for this site; they can safely be closed.',

    'TEXT_EXPIRED' => '**expired**',
    'TEXT_HERE' => 'here',          //- Used in the '_RESEND' data's anchor links
    'TEXT_INFORMATION_ACTIVATE' =>  //- %1$s (email address)
        'We sent an email to %1$s containing a link to activate your account. Click that link to continue with your account activation.',
    'TEXT_INFORMATION_LINK_ACTIVE' => 'Time to link expiration:',
    'TEXT_INFORMATION_LINK_EXPIRED' => 'The link has expired.',
    'TEXT_INFORMATION_RESEND' =>    //- %1$s (an anchor link to resend the token), %2$s (a link to the account_edit page)
        'Didn\'t receive an email? Verify that the email address above is correct. If it is (or if the link has expired), click %1$s to resend; otherwise, click %2$s to change your email address.',
];
return $define;
