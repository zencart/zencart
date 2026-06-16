<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2025 Sep 24 New in v2.2.0 $
 *
 * @since ZC v2.2.0
 */
$store_name = zen_config('STORE_NAME');
$store_owner_email_address = zen_config('STORE_OWNER_EMAIL_ADDRESS');
$define = [
    'EMAIL_SUBJECT' => 'Welcome to ' . $store_name,
    'EMAIL_GREET_MR' => 'Dear Mr. %s,' . "\n\n",
    'EMAIL_GREET_MS' => 'Dear Ms. %s,' . "\n\n",
    'EMAIL_GREET_NONE' => 'Dear %s,' . "\n\n",
    'EMAIL_WELCOME' => 'We wish to welcome you to <strong>' . $store_name . '</strong>.',
    'EMAIL_SEPARATOR' => '--------------------',
    'EMAIL_COUPON_INCENTIVE_HEADER' => 'Congratulations! To make your next visit to our online shop a more rewarding experience, listed below are details for a Discount Coupon created just for you!' . "\n\n",
    'EMAIL_COUPON_REDEEM' => 'To use the Discount Coupon, enter the ' . TEXT_GV_REDEEM . ' code during checkout:  <strong>%s</strong>' . "\n\n",
    'EMAIL_GV_INCENTIVE_HEADER' => 'Just for stopping by today, we have sent you a ' . TEXT_GV_NAME . ' for %s!' . "\n",
    'EMAIL_GV_REDEEM' => 'The ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . ' is: %s ' . "\n\n" . 'You can enter the ' . TEXT_GV_REDEEM . ' during Checkout, after making your selections in the store. ',
    'EMAIL_GV_LINK' => ' Or, you may redeem it now by following this link: ' . "\n",
    'EMAIL_GV_LINK_OTHER' => 'Once you have added the ' . TEXT_GV_NAME . ' to your account, you may use the ' . TEXT_GV_NAME . ' for yourself, or send it to a friend!' . "\n\n",
    'EMAIL_TEXT' => 'You now have an account with ' . $store_name . ' providing:' . "\n\n<ul>" . '<li><strong>Order History</strong> - View order details.</li>' . "\n\n" . '<li><strong>Permanent Cart</strong> - Products you add to your cart will remain there until removed or purchased.</li>' . "\n\n" . '<li><strong>Address Book</strong> - Define additional addresses (for example to send a gift).</li>' . "\n\n" . '<li><strong>Product Reviews</strong> - Share your opinion on our products with other customers.</li>' . "\n\n</ul>",
    'EMAIL_CONTACT' => 'For help with any of our online services, please email the store-owner: <a href="mailto:' . $store_owner_email_address . '">' . $store_owner_email_address . "</a>\n\n",
    'EMAIL_GV_CLOSURE' => "\n" . 'Sincerely,' . "\n\n" . zen_config('STORE_OWNER') . "\nStore Owner\n\n" . '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">' . HTTP_SERVER . DIR_WS_CATALOG . "</a>\n\n",
    'EMAIL_DISCLAIMER_NEW_CUSTOMER' => 'This email address was given to us by you or by one of our customers. If you did not signup for an account, or feel that you have received this email in error, please send an email to %s ',
];
return $define;
