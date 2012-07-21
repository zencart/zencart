<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: create_account.php 15405 2010-02-03 06:29:33Z drbyte $
 */

define('NAVBAR_TITLE', 'Create an Account');

define('HEADING_TITLE', 'My Account Information');

define('TEXT_ORIGIN_LOGIN', '<strong class="note">NOTE:</strong> If you already have an account with us, please login at the <a href="%s">login page</a>.');

define('ERROR_CREATE_ACCOUNT_SPAM_DETECTED', 'Thank you, your account request has been submitted for review.');


// greeting salutation
define('EMAIL_SUBJECT', 'Welcome to ' . STORE_NAME);
define('EMAIL_GREET_MR', 'Dear Mr. %s,' . "\n\n");
define('EMAIL_GREET_MS', 'Dear Ms. %s,' . "\n\n");
define('EMAIL_GREET_NONE', 'Dear %s' . "\n\n");

// First line of the greeting
define('EMAIL_WELCOME', 'We wish to welcome you to <strong>' . STORE_NAME . '</strong>.');
define('EMAIL_SEPARATOR', '--------------------');
define('EMAIL_COUPON_INCENTIVE_HEADER', 'Congratulations! To make your next visit to our online shop a more rewarding experience, listed below are details for a Discount Coupon created just for you!' . "\n\n");
// your Discount Coupon Description will be inserted before this next define
define('EMAIL_COUPON_REDEEM', 'To use the Discount Coupon, enter the ' . TEXT_GV_REDEEM . ' code during checkout:  <strong>%s</strong>' . "\n\n");
define('TEXT_COUPON_HELP_DATE', '<p>The coupon is valid between %s and %s</p>');

define('EMAIL_GV_INCENTIVE_HEADER', 'Just for stopping by today, we have sent you a ' . TEXT_GV_NAME . ' for %s!' . "\n");
define('EMAIL_GV_REDEEM', 'The ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM . ' is: %s ' . "\n\n" . 'You can enter the ' . TEXT_GV_REDEEM . ' during Checkout, after making your selections in the store. ');
define('EMAIL_GV_LINK', ' Or, you may redeem it now by following this link: ' . "\n");
// GV link will automatically be included before this line

define('EMAIL_GV_LINK_OTHER','Once you have added the ' . TEXT_GV_NAME . ' to your account, you may use the ' . TEXT_GV_NAME . ' for yourself, or send it to a friend!' . "\n\n");

define('EMAIL_TEXT', 'You are now registered with our store and have account privileges:  With your account, you can now take part in the <strong>various services</strong> we have to offer you. Some of these many services include:' . "\n\n<ul>" . '<li><strong>Order History</strong> - View the details of orders you have completed with us.' . "\n\n" . '<li><strong>Permanent Cart</strong> - Any products added to your online cart remain there until you remove them, or check them out.' . "\n\n" . '<li><strong>Address Book</strong> - We can deliver your products to an address other than yours! This is perfect to send birthday gifts direct to the birthday-person themselves.' . "\n\n" . '<li><strong>Products Reviews</strong> - Share your opinions on our products with other customers.' . "\n\n</ul>");
define('EMAIL_CONTACT', 'For help with any of our online services, please email the store-owner: <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">'. STORE_OWNER_EMAIL_ADDRESS ." </a>\n\n");
define('EMAIL_GV_CLOSURE', "\n" . 'Sincerely,' . "\n\n" . STORE_OWNER . "\nStore Owner\n\n". '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">'.HTTP_SERVER . DIR_WS_CATALOG ."</a>\n\n");

// email disclaimer - this disclaimer is separate from all other email disclaimers
define('EMAIL_DISCLAIMER_NEW_CUSTOMER', 'This email address was given to us by you or by one of our customers. If you did not signup for an account, or feel that you have received this email in error, please send an email to %s ');
