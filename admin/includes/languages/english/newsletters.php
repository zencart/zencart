<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: newsletters.php 4385 2006-09-04 04:10:48Z drbyte $
 */

define('HEADING_TITLE', 'Newsletter and Product Notifications Manager');

define('TABLE_HEADING_NEWSLETTERS', 'Newsletters');
define('TABLE_HEADING_SIZE', 'Size');
define('TABLE_HEADING_MODULE', 'Module');
define('TABLE_HEADING_SENT', 'Sent');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_NEWSLETTER_MODULE', 'Module:');
define('TEXT_NEWSLETTER_TITLE', 'Subject:');
define('TEXT_NEWSLETTER_CONTENT', 'Text-Only <br />Content:');
define('TEXT_NEWSLETTER_CONTENT_HTML', 'Rich Text <br />Content:');

define('TEXT_NEWSLETTER_DATE_ADDED', 'Date Added:');
define('TEXT_NEWSLETTER_DATE_SENT', 'Date Sent:');

define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this newsletter?');

define('TEXT_PLEASE_WAIT', 'Please wait .. sending emails ..<br /><br />Please do not interrupt this process!');
define('TEXT_FINISHED_SENDING_EMAILS', 'Finished sending e-mails!');

define('TEXT_AFTER_EMAIL_INSTRUCTIONS','%s emails processed. (Each checkbox indicates 1 recipient. Hover over the checkbox to see the email address.)<br /><br />Watch your mail box ('.EMAIL_FROM.') for:<UL><LI>a) bounce-back messages</LI><LI>b) email addresses that are no longer good</LI><LI>c) removal requests.</LI></UL>Removals can be processed by editing Customer records in the Admin | Customers menu.');

define('ERROR_NEWSLETTER_TITLE', 'Error: Newsletter title required');
define('ERROR_NEWSLETTER_MODULE', 'Error: Newsletter module required');
define('ERROR_PLEASE_SELECT_AUDIENCE','Error: Please select an audience to receive this newsletter');
?>