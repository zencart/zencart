<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: coupon_admin.php 5758 2007-02-08 01:39:34Z ajeh $
 */

define('TOP_BAR_TITLE', 'Statistics');
define('HEADING_TITLE', 'Discount Coupons');
define('HEADING_TITLE_STATUS', 'Status : ');
define('TEXT_CUSTOMER', 'Customer:');
define('TEXT_COUPON', 'Coupon Name:');
define('TEXT_COUPON_ALL', 'All Coupons');
define('TEXT_COUPON_ACTIVE', 'Active Coupons');
define('TEXT_COUPON_INACTIVE', 'Inactive Coupons');
define('TEXT_SUBJECT', 'Subject:');
define('TEXT_UNLIMITED', 'Unlimited');
define('TEXT_FROM', 'From:');
define('TEXT_FREE_SHIPPING', 'Free Shipping');
define('TEXT_MESSAGE', 'Message:');
define('TEXT_RICH_TEXT_MESSAGE','Rich-Text Message:');
define('TEXT_SELECT_CUSTOMER', 'Select Customer');
define('TEXT_ALL_CUSTOMERS', 'All Customers');
define('TEXT_NEWSLETTER_CUSTOMERS', 'To All Newsletter Subscribers');
define('TEXT_CONFIRM_DELETE', 'Are you sure you want to delete this Coupon?');
define('TEXT_SEE_RESTRICT', 'Restrictions Apply');

define('TEXT_COUPON_ANNOUNCE','We\'re pleased to offer you a Store Coupon');

define('TEXT_TO_REDEEM', 'You can redeem this coupon during checkout. Just enter the code in the box provided, and click on the redeem button.');
define('TEXT_IN_CASE', ' in case you have any problems. ');
define('TEXT_VOUCHER_IS', 'The coupon code is ');
define('TEXT_REMEMBER', 'Don\'t lose the coupon code, make sure to keep the code safe so you can benefit from this special offer.');
define('TEXT_VISIT', 'Visit us at %s');
define('TEXT_ENTER_CODE', ' and enter the code ');
define('TEXT_COUPON_HELP_DATE', '<p>The coupon is valid between %s and %s</p>');
define('HTML_COUPON_HELP_DATE', '<p>The coupon is valid between %s and %s</p>');

define('TABLE_HEADING_ACTION', 'Action');

define('CUSTOMER_ID', 'Customer ID');
define('CUSTOMER_NAME', 'Customer Name');
define('REDEEM_DATE', 'Date Redeemed');
define('IP_ADDRESS', 'IP Address');

define('TEXT_REDEMPTIONS', 'Redemptions');
define('TEXT_REDEMPTIONS_TOTAL', 'In Total');
define('TEXT_REDEMPTIONS_CUSTOMER', 'For this Customer');
define('TEXT_NO_FREE_SHIPPING', 'No Free Shipping');

define('NOTICE_EMAIL_SENT_TO', 'Notice: Email sent to: %s');
define('ERROR_NO_CUSTOMER_SELECTED', 'Error: No customer has been selected.');
define('ERROR_NO_SUBJECT', 'Error: No subject has been entered.');

define('COUPON_NAME', 'Coupon Name');
//define('COUPON_VALUE', 'Coupon Value');
define('COUPON_AMOUNT', 'Coupon Amount');
define('COUPON_CODE', 'Coupon Code');
define('COUPON_STARTDATE', 'Start Date');
define('COUPON_FINISHDATE', 'End Date');
define('COUPON_RESTRICTIONS', 'Restrictions');
define('COUPON_FREE_SHIP', 'Free Shipping');
define('COUPON_DESC', 'Coupon Description <br />(Customer can see)');
define('COUPON_MIN_ORDER', 'Coupon Minimum Order');

define('COUPON_TOTAL', 'Coupon Minimum calculated from: ');
define('TEXT_COUPON_TOTAL_PRODUCTS', 'Allowed Products');
define('TEXT_COUPON_TOTAL_PRODUCTS_BASED', '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Based on Total of Allowed Products according to Coupon Restriction Rules)');
define('TEXT_COUPON_TOTAL_ORDER', 'All Products');
define('TEXT_COUPON_TOTAL_ORDER_BASED', '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Based on Full Order Total of All Products Regardless of Qualifying Coupon Restrictions)');

define('COUPON_USES_COUPON', 'Uses per Coupon');
define('COUPON_USES_USER', 'Uses per Customer');
define('COUPON_PRODUCTS', 'Valid Product List');
define('COUPON_CATEGORIES', 'Valid Categories List');
define('VOUCHER_NUMBER_USED', 'Number Used');
define('DATE_CREATED', 'Date Created');
define('DATE_MODIFIED', 'Date Modified');
define('TEXT_HEADING_NEW_COUPON', 'Create New Coupon');
define('TEXT_NEW_INTRO', 'Please fill out the following information for the new coupon.<br />');
define('COUPON_ZONE_RESTRICTION', 'Coupon Zone Restriction: ');
define('TEXT_COUPON_ZONE_RESTRICTION', 'Coupon Zone Restriction are optional.');
define('COUPON_ORDER_LIMIT', 'Customer previous Orders less than: ');
define('COUPON_ORDER_LIMIT_HELP', 'Customer must have previous Orders less than, leave blank for unlimited');


define('ERROR_NO_COUPON_AMOUNT', 'No coupon amount entered');
define('ERROR_NO_COUPON_NAME', 'No coupon name entered ');
define('ERROR_COUPON_EXISTS', 'A coupon with that code already exists');


define('COUPON_NAME_HELP', 'A short name for the coupon');
define('COUPON_AMOUNT_HELP', 'The value of the discount for the coupon, either fixed or add a % on the end for a percentage discount.');
define('COUPON_CODE_HELP', 'You can enter your own code here, or leave blank for an auto generated one.');
define('COUPON_STARTDATE_HELP', 'The date the coupon will be valid from');
define('COUPON_FINISHDATE_HELP', 'The date the coupon expires');
define('COUPON_FREE_SHIP_HELP', 'The coupon gives free shipping on an order.');
define('COUPON_DESC_HELP', 'A description of the coupon for the customer');
define('COUPON_MIN_ORDER_HELP', 'Coupon Minimum Order');
define('COUPON_TOTAL_HELP', 'If you specify a Coupon Minimum Order for this Discount Coupon, do you want the Minimum amount to be based on Allowed Products according to Coupon Restriction Rules or the Full Order Total, when determining if the Coupon Minimum Order has been met?<br />NOTE: Full Order Total means at least 1 of the Qualifying Restricted Products must be in the cart for the Discount Coupon to work.');
define('COUPON_USES_COUPON_HELP', 'The maximum number of times the coupon can be used, leave blank if you want no limit.');
define('COUPON_USES_USER_HELP', 'Number of times a user can use the coupon, leave blank for no limit.');
define('COUPON_PRODUCTS_HELP', 'A comma separated list of product_ids that this coupon can be used with. Leave blank for no restrictions.');
define('COUPON_CATEGORIES_HELP', 'A comma separated list of cpaths that this coupon can be used with, leave blank for no restrictions.');
define('COUPON_BUTTON_PREVIEW', 'Preview');
define('COUPON_BUTTON_CONFIRM', 'Confirm');
define('COUPON_BUTTON_BACK', 'Back');

define('COUPON_ACTIVE', 'Status');
define('COUPON_START_DATE', 'Starts');
define('COUPON_EXPIRE_DATE', 'Expires');

define('TEXT_INFO_DUPLICATE_MANAGEMENT', '<strong>Multiple Discount Coupons Management</strong><br /><br />Click on Discount Coupon to base changes on<br />or use the selected Base Coupon Code: <strong>%s</strong>');
define('ERROR_DISCOUNT_COUPON_WELCOME', 'Discount Coupon CANNOT be deactivated. This Discount Coupon is the Welcome Discount Coupon<br /><br />Change the Welcome Discount Coupon before trying to delete it. See Admin->Configuration->GV Coupons');
define('SUCCESS_COUPON_DISABLED', 'Success! Discount Coupon was set to Inactive ...');
define('TEXT_COUPON_NEW', 'Use NEW Discount Coupon Code:');
define('ERROR_DISCOUNT_COUPON_DUPLICATE', 'WARNING! Duplicate Coupon exists ... Copy cancelled for Coupon Code: ');
define('TEXT_CONFIRM_COPY', 'Are you sure you want to Copy this Discount Coupon to another Discount Coupon?');
define('SUCCESS_COUPON_DUPLICATE', 'Success! Discount Coupon was duplicated ...<br /><br />Be sure to check Coupon Name and Dates ...');
define('WARNING_COUPON_DUPLICATE', 'Warning! No Discount Coupons were made! Number of Discount Coupons to create was not defined ... ');

define('TEXT_COUPON_COPY_INFO', 'Copy for multiple duplicates');
define('TEXT_COUPON_COPY_DUPLICATE', 'Create Multiple Coupons with Base Coupon Code of: ');
define('TEXT_COUPON_COPY_DUPLICATE_CNT', 'How many duplicate Discount Coupons do you want to create? ');

define('TEXT_CONFIRM_DELETE_DUPLICATE', 'Delete all matching Discount Coupons based on the Base coupon code<br />Example: <strong>%s</strong> would delete all Discount Coupons codes starting with: <strong>%s</strong>');
define('TEXT_COUPON_DELETE_DUPLICATE', 'Delete all Discount Coupons matching base code: ');

define('TEXT_DISCOUNT_COUPON_EMAIL', 'Email Discount Coupon');
define('TEXT_DISCOUNT_COUPON_CONFIRM_DELETE', 'Confirm Delete Discount Coupon');
define('TEXT_DISCOUNT_COUPON_CONFIRM_RESTORE', 'Confirm Restore Discount Coupon');

define('TEXT_DISCOUNT_COUPON_EDIT', 'Edit Discount Coupon');
define('TEXT_DISCOUNT_COUPON_DELETE', 'Delete Discount Coupon');
define('TEXT_DISCOUNT_COUPON_RESTORE', 'Restore Discount Coupon');
define('TEXT_DISCOUNT_COUPON_RESTRICT', 'Restrict Discount Coupon');
define('TEXT_DISCOUNT_COUPON_REPORT', 'Discount Coupon Report');
define('TEXT_DISCOUNT_COUPON_COPY', 'Copy Discount Coupon');
define('TEXT_DISCOUNT_COUPON_COPY_MULTIPLE', 'Copy to Multiple Discount Coupons');
define('TEXT_DISCOUNT_COUPON_DELETE_MULTIPLE', 'Delete Multiple Discount Coupons');
define('TEXT_DISCOUNT_COUPON_REPORT_MULTIPLE', 'Multiple Discount Coupons Report');
define('TEXT_DISCOUNT_COUPON_DOWNLOAD', 'Download Multiple Coupon Codes');
define('REDEEM_ORDER_ID', 'Order #');
define('SUCCESS_COUPON_REACTIVATE', 'Successful Reactivate');
define('TEXT_CONFIRM_REACTIVATE', 'Are you sure you want to restore this Coupon?<br />NOTE: Restore does not affect Start/Expiration Dates.<br />Restore does not affect limits on use per coupon/use per customer if already redeemed.');

define('SUCCESS_COUPON_FOUND', 'Discount Coupon found!');
define('ERROR_COUPON_NOT_FOUND', 'Discount Coupon not found!');
define('ERROR_NO_COUPON_CODE', 'Discount Coupon coupon code not entered!');
