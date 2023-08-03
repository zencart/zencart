<?php
$define = [
    'NAVBAR_TITLE' => 'Create an Account',
    'HEADING_TITLE' => 'My Account Information',
    'TEXT_ORIGIN_LOGIN' => '<strong class="note">NOTE:</strong> If you already have an account with us, please login at the <a href="%s">login page</a>.',
    'ERROR_CREATE_ACCOUNT_SPAM_DETECTED' => 'Thank you, your account request has been submitted for review.',
    'EMAIL_SUBJECT' => 'Welcome to ' . STORE_NAME,
    'EMAIL_GREET_MR' => 'Dear Mr. %s,' . "\n\n",
    'EMAIL_GREET_MS' => 'Dear Ms. %s,' . "\n\n",
    'EMAIL_GREET_NONE' => 'Dear %s,' . "\n\n",
    'EMAIL_WELCOME' => 'We wish to welcome you to <strong>' . STORE_NAME . '</strong>.',
    'EMAIL_SEPARATOR' => '--------------------',
    'EMAIL_COUPON_INCENTIVE_HEADER' => 'Congratulations! To make your next visit to our online shop a more rewarding experience, listed below are details for a Discount Coupon created just for you!' . "\n\n",
    'EMAIL_COUPON_REDEEM' => 'To use the Discount Coupon, enter the ' . TEXT_GV_REDEEM . ' code during checkout:  <strong>%s</strong>' . "\n\n",
    'EMAIL_GV_INCENTIVE_HEADER' => 'Just for stopping by today, we have sent you a ' . TEXT_GV_NAME . ' for %s!' . "\n",
    'EMAIL_GV_REDEEM' => 'The ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . ' is: %s ' . "\n\n" . 'You can enter the ' . TEXT_GV_REDEEM . ' during Checkout, after making your selections in the store. ',
    'EMAIL_GV_LINK' => ' Or, you may redeem it now by following this link: ' . "\n",
    'EMAIL_GV_LINK_OTHER' => 'Once you have added the ' . TEXT_GV_NAME . ' to your account, you may use the ' . TEXT_GV_NAME . ' for yourself, or send it to a friend!' . "\n\n",
    'EMAIL_TEXT' => 'You now have an account with ' . STORE_NAME . ' providing:' . "\n\n<ul>" . '<li><strong>Order History</strong> - View order details.</li>' . "\n\n" . '<li><strong>Permanent Cart</strong> - Products you add to your cart will remain there until removed or purchased.</li>' . "\n\n" . '<li><strong>Address Book</strong> - Define additional addresses (for example to send a gift).</li>' . "\n\n" . '<li><strong>Product Reviews</strong> - Share your opinion on our products with other customers.</li>' . "\n\n</ul>",
    'EMAIL_CONTACT' => 'For help with any of our online services, please email the store-owner: <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . "</a>\n\n",
    'EMAIL_GV_CLOSURE' => "\n" . 'Sincerely,' . "\n\n" . STORE_OWNER . "\nStore Owner\n\n" . '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">' . HTTP_SERVER . DIR_WS_CATALOG . "</a>\n\n",
    'EMAIL_DISCLAIMER_NEW_CUSTOMER' => 'This email address was given to us by you or by one of our customers. If you did not signup for an account, or feel that you have received this email in error, please send an email to %s ',
];

return $define;
