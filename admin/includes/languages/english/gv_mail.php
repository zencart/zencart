<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: gv_mail.php 1105 2005-04-04 22:05:35Z birdbrain $
//

define('HEADING_TITLE', 'Send ' . TEXT_GV_NAME . ' To Customers');

define('TEXT_CUSTOMER', 'Customer:');
define('TEXT_SUBJECT', 'Subject:');
define('TEXT_FROM', 'From:');
define('TEXT_TO', 'Email To:');
define('TEXT_AMOUNT', 'Amount');
define('TEXT_MESSAGE', 'Text-Only <br />Message:');
define('TEXT_RICH_TEXT_MESSAGE', 'Rich Text <br />Message:');
define('TEXT_SINGLE_EMAIL', '<span class="smallText">Use this for sending single emails, otherwise use dropdown above</span>');
define('TEXT_SELECT_CUSTOMER', 'Select Customer');
define('TEXT_ALL_CUSTOMERS', 'All Customers');
define('TEXT_NEWSLETTER_CUSTOMERS', 'To All Newsletter Subscribers');

define('NOTICE_EMAIL_SENT_TO', 'Notice: Email sent to: %s');
define('ERROR_NO_CUSTOMER_SELECTED', 'Error: No customer has been selected.');
define('ERROR_NO_AMOUNT_SELECTED', 'Error: No amount has been selected.');
define('ERROR_NO_SUBJECT', 'Error: No subject has been entered.');
define('ERROR_GV_AMOUNT', 'Please define Amount as a Value without symbols. Example: 25.00');

define('TEXT_GV_ANNOUNCE','<font color="#0000ff">We\'re pleased to offer you a ' . TEXT_GV_NAME . '</font>');
define('TEXT_GV_WORTH', 'The ' . TEXT_GV_NAME . ' is worth ');
define('TEXT_TO_REDEEM', 'To redeem this ' . TEXT_GV_NAME . ', please click on the link below. Please also write down the ' . TEXT_GV_REDEEM);
define('TEXT_WHICH_IS', ' which is');
define('TEXT_IN_CASE', ' in case you have any problems.');
define('TEXT_OR_VISIT', 'or visit ');
define('TEXT_ENTER_CODE', ' and enter the code during the checkout process');
define('TEXT_CLICK_TO_REDEEM','Click here to redeem');

define ('TEXT_REDEEM_COUPON_MESSAGE_HEADER', 'You recently purchased a  ' . TEXT_GV_NAME . ' from our site, for security reasons, the amount of the  ' . TEXT_GV_NAME . ' was not immediately credited to you. The shop owner has now released this amount.');
define ('TEXT_REDEEM_COUPON_MESSAGE_AMOUNT', "\n\n" . 'The value of the  ' . TEXT_GV_NAME . ' was %s');
define ('TEXT_REDEEM_COUPON_MESSAGE_BODY', "\n\n" . 'You can now visit our site, login and send the  ' . TEXT_GV_NAME . ' amount to anyone you want.');
define ('TEXT_REDEEM_COUPON_MESSAGE_FOOTER', "\n\n");

?>