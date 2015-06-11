<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: email_extras.php  Modified in v1.6.0 $
 */

  if (!defined('EMAIL_FOOTER_COPYRIGHT')) define('EMAIL_FOOTER_COPYRIGHT','Copyright (c) ' . date('Y') . ' <a href="' . zen_catalog_href_link(FILENAME_DEFAULT) . '" target="_blank">' . STORE_NAME . '</a>. Powered by <a href="http://www.zen-cart.com" target="_blank">Zen Cart</a>');

// office use only
  define('OFFICE_FROM','From:');
  define('OFFICE_EMAIL','E-mail:');

  define('OFFICE_SENT_TO','Sent To:');
  define('OFFICE_EMAIL_TO','E-mail:');
  define('OFFICE_USE','Office Use Only:');
  define('OFFICE_LOGIN_NAME','Login Name:');
  define('OFFICE_LOGIN_EMAIL','Login e-mail:');
  define('OFFICE_LOGIN_PHONE','<strong>Telephone:</strong>');
  define('OFFICE_IP_ADDRESS','IP Address:');
  define('OFFICE_HOST_ADDRESS','Host Address:');
  define('OFFICE_DATE_TIME','Date and Time:');

// email disclaimer
  define('EMAIL_DISCLAIMER', "\n" . 'This email address was given to us by you or by one of our customers. If you feel that you have received this email in error, please send an email to %s');
  define('EMAIL_SPAM_DISCLAIMER','This e-mail is sent in accordance with the US CAN-SPAM Law in effect 01/01/2004. Removal requests can be sent to this address and will be honored and respected.');
  define('SEND_EXTRA_GV_ADMIN_EMAILS_TO_SUBJECT','[GV ADMIN SENT]');
  define('SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO_SUBJECT','[DISCOUNT COUPONS]');
  define('SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT','[ORDERS STATUS]');
  define('TEXT_UNSUBSCRIBE', "\n\nTo unsubscribe from future newsletter and promotional mailings, simply click on the following link: \n");
  define('SEND_EXTRA_GV_QUEUE_ADMIN_EMAILS_TO_SUBJECT','[' . TEXT_GV_NAME . ' QUEUE]');

// for whos_online when gethost is off
  define('OFFICE_IP_TO_HOST_ADDRESS', 'Disabled');

define('TEXT_EMAIL_SUBJECT_ADMIN_USER_ADDED', 'Admin Alert: New admin user added.');
define('TEXT_EMAIL_MESSAGE_ADMIN_USER_ADDED', 'Administrative alert: A new admin user (%s) has been ADDED to your store by %s.' . "\n\n" . 'If you or an authorized administrator did not initiate this change, it is advised that you verify your site security immediately.');
define('TEXT_EMAIL_SUBJECT_ADMIN_USER_DELETED', 'Admin Alert: An admin user has been deleted.');
define('TEXT_EMAIL_MESSAGE_ADMIN_USER_DELETED', 'Administrative alert: An admin user (%s) has been DELETED from your store by %s.' . "\n\n" . 'If you or an authorized administrator did not initiate this change, it is advised that you verify your site security immediately.');
define('TEXT_EMAIL_SUBJECT_ADMIN_USER_CHANGED', 'Admin Alert: Admin user details have been changed.');
define('TEXT_EMAIL_ALERT_ADM_EMAIL_CHANGED', 'Admin alert: Admin user (%s) email address has been changed from (%s) to (%s) by (%s)');
define('TEXT_EMAIL_ALERT_ADM_NAME_CHANGED', 'Admin alert: Admin user (%s) username has been changed from (%s) to (%s) by (%s)');
define('TEXT_EMAIL_ALERT_ADM_MOBILE_CHANGED', 'Admin alert: Admin user (%s) mobile_phone has been changed from (%s) to (%s) by (%s)');
define('TEXT_EMAIL_ALERT_ADM_PROFILE_CHANGED', 'Admin alert: Admin user (%s) security profile has been changed from (%s) to (%s) by (%s)');
define('TEXT_EMAIL_ALERT_IP_ADDRESS', 'Triggered from IP Address: %s');
