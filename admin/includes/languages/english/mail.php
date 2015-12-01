<?php
/**
 * @package admin
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mail.php 7197 2007-10-06 20:35:52Z drbyte $
 */


define('HEADING_TITLE', 'Send Email To Customers');

define('TEXT_CUSTOMER', 'Customer:');
define('TEXT_SUBJECT', 'Subject:');
define('TEXT_FROM', 'From:');
define('TEXT_MESSAGE', 'Text-Only <br />Message:');
define('TEXT_MESSAGE_HTML','Rich Text <br />Message:');
define('TEXT_SELECT_CUSTOMER', 'Select Customer');
define('TEXT_ALL_CUSTOMERS', 'All Customers');
define('TEXT_NEWSLETTER_CUSTOMERS', 'To All Newsletter Subscribers');
define('TEXT_ATTACHMENTS_LIST','Selected Attachment: ');
define('TEXT_SELECT_ATTACHMENT','Attachment<br />on server: ');
define('TEXT_SELECT_ATTACHMENT_TO_UPLOAD','Attachment<br />to upload<br />&amp; attach: ');
define('TEXT_ATTACHMENTS_DIR','Folder for upload: ');

define('NOTICE_EMAIL_SENT_TO', 'Notice: Email sent to: %s');
define('NOTICE_EMAIL_FAILED_SEND', 'Notice: FAILED to send Email to all recipients: %s');
define('ERROR_NO_CUSTOMER_SELECTED', 'Error: No customer has been selected.');
define('ERROR_NO_SUBJECT', 'Error: No subject has been entered.');
define('ERROR_ATTACHMENTS', 'Error: You cannot select to both UPLOAD and ADD separate attachments. Please choose one only.');
?>