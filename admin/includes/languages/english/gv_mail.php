<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2019 Aug 08 Modified in v1.5.7 $
 */

require 'gv_name.php';
define('TITLE', 'Send Gift Voucher');
define('HEADING_TITLE', 'Send ' . TEXT_GV_NAME . ' To Customers');

define('TEXT_FROM', 'From:');
define('TEXT_TO', 'Email To:');
define('TEXT_TO_CUSTOMERS', 'To Customer Lists:');
define('TEXT_TO_EMAIL', 'or To an Email Address:');
define('TEXT_TO_EMAIL_NAME', 'Customer Name (optional):');
define('TEXT_TO_EMAIL_INFO', '<span class="smallText">Choose a list from the above drop-down or use the following fields for sending a single email.</span>');
define('TEXT_SUBJECT', 'Email Subject:');
define('TEXT_AMOUNT', 'Certificate Value:');
define('ERROR_GV_AMOUNT', 'Please define the amount as a number without symbols, eg.: 25.00');
define('TEXT_AMOUNT_INFO', '<span class="smallText">' . ERROR_GV_AMOUNT . '</span>');
define('TEXT_HTML_MESSAGE', 'HTML<br>Message:');
define('TEXT_MESSAGE', 'Text-Only<br>Message:');

define('TEXT_SELECT_CUSTOMER', 'Select Customer');
define('TEXT_ALL_CUSTOMERS', 'All Customers');
define('TEXT_NEWSLETTER_CUSTOMERS', 'To All Newsletter Subscribers');

define('NOTICE_EMAIL_SENT_TO', 'Notice: %1s email(s) sent to %2s');
define('ERROR_NO_CUSTOMER_SELECTED', 'Error: No Customer selected.');
define('ERROR_NO_AMOUNT_ENTERED', 'Error: Certificate Value invalid.');
define('ERROR_NO_SUBJECT', 'Error: no Email Subject entered.');

define('TEXT_GV_ANNOUNCE', 'We\'re pleased to offer you a ' . TEXT_GV_NAME . '.');
define('TEXT_GV_WORTH', 'This ' . TEXT_GV_NAME . ' is worth ');
if (!defined('TEXT_GV_REDEEM')) define ('TEXT_GV_REDEEM', 'Redemption Code');//should not be needed...but it is.
define('TEXT_TO_REDEEM', 'To redeem this ' . TEXT_GV_NAME . ', please click on the link below.' . "\n" . 'Please note the ' . TEXT_GV_REDEEM);
define('TEXT_WHICH_IS', ' which is ');
define('TEXT_IN_CASE', ' in case you have any problems with the links.');
define('TEXT_OR_VISIT', 'or visit ');
define('TEXT_ENTER_CODE', ' and enter the code on the Checkout-Payment page.');
define('TEXT_CLICK_TO_REDEEM', 'Click here to redeem the ' . TEXT_GV_NAME);

define ('TEXT_REDEEM_COUPON_MESSAGE_HEADER', 'You recently purchased a  ' . TEXT_GV_NAME . ' from our site, for security reasons, the amount of the  ' . TEXT_GV_NAME . ' was not immediately credited to you. The shop owner has now released this amount.');
define ('TEXT_REDEEM_COUPON_MESSAGE_AMOUNT', "\n\n" . 'The value of the  ' . TEXT_GV_NAME . ' was %s');
define ('TEXT_REDEEM_COUPON_MESSAGE_BODY', "\n\n" . 'You can now visit our site, login and send the  ' . TEXT_GV_NAME . ' amount to anyone you want.');
define ('TEXT_REDEEM_COUPON_MESSAGE_FOOTER', "\n\n");
