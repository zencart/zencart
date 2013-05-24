<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: email_extras.php 19690 2011-10-04 16:41:45Z drbyte $
 */

// office use only
  define('OFFICE_FROM','<strong>From:</strong>');
  define('OFFICE_EMAIL','<strong>Email:</strong>');

  define('OFFICE_SENT_TO','<strong>Sent To:</strong>');
  define('OFFICE_EMAIL_TO','<strong>To Email:</strong>');

  define('OFFICE_USE','<strong>Office Use Only:</strong>');
  define('OFFICE_LOGIN_NAME','<strong>Login Name:</strong>');
  define('OFFICE_LOGIN_EMAIL','<strong>Login Email:</strong>');
  define('OFFICE_LOGIN_PHONE','<strong>Telephone:</strong>');
  define('OFFICE_LOGIN_FAX','<strong>Fax:</strong>');
  define('OFFICE_IP_ADDRESS','<strong>IP Address:</strong>');
  define('OFFICE_HOST_ADDRESS','<strong>Host Address:</strong>');
  define('OFFICE_DATE_TIME','<strong>Date and Time:</strong>');


// email disclaimer
  define('EMAIL_DISCLAIMER', 'This email address was given to us by you or by one of our customers. If you feel that you have received this email in error, please send an email to %s ');
  define('EMAIL_SPAM_DISCLAIMER','This email is sent in accordance with the US CAN-SPAM Law in effect 01/01/2004. Removal requests can be sent to this address and will be honored and respected.');
  define('EMAIL_FOOTER_COPYRIGHT','Copyright (c) ' . date('Y') . ' <a href="' . zen_href_link(FILENAME_DEFAULT) . '" target="_blank">' . STORE_NAME . '</a>. Powered by <a href="http://www.zen-cart.com" target="_blank">Zen Cart</a>');
  define('TEXT_UNSUBSCRIBE', "\n\nTo unsubscribe from future newsletter and promotional mailings, simply click on the following link: \n");

// email advisory for all emails customer generate - tell-a-friend and GV send
  define('EMAIL_ADVISORY', '-----' . "\n" . '<strong>IMPORTANT:</strong> For your protection and to prevent malicious use, all emails sent via this web site are logged and the contents recorded and available to the store owner. If you feel that you have received this email in error, please send an email to ' . STORE_OWNER_EMAIL_ADDRESS . "\n\n");

// email advisory included warning for all emails customer generate - tell-a-friend and GV send
  define('EMAIL_ADVISORY_INCLUDED_WARNING', '<strong>This message is included with all emails sent from this site:</strong>');


// Admin additional email subjects
  define('SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_SUBJECT','[CREATE ACCOUNT]');
  define('SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_SUBJECT','[GV CUSTOMER SENT]');
  define('SEND_EXTRA_NEW_ORDERS_EMAILS_TO_SUBJECT','[NEW ORDER]');
  define('SEND_EXTRA_CC_EMAILS_TO_SUBJECT','[EXTRA CC ORDER info] #');

// Low Stock Emails
  define('EMAIL_TEXT_SUBJECT_LOWSTOCK','Warning: Low Stock');
  define('SEND_EXTRA_LOW_STOCK_EMAIL_TITLE','Low Stock Report: ');
