<?php
/**
 * @package admin
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: orders.php 6214 2007-04-17 02:24:25Z ajeh $
 */

define('HEADING_TITLE', 'Orders');
define('HEADING_TITLE_SEARCH', 'Order ID:');
define('HEADING_TITLE_STATUS', 'Status:');
define('HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS', 'Search by Product Name or <strong>ID:XX</strong> or Model ');
define('TEXT_INFO_SEARCH_DETAIL_FILTER_ORDERS_PRODUCTS', 'Search Filter: ');
define('TABLE_HEADING_PAYMENT_METHOD', 'Payment<br />Shipping');
define('TABLE_HEADING_ORDERS_ID','ID');

define('TEXT_BILLING_SHIPPING_MISMATCH','Billing and Shipping does not match ');

define('TABLE_HEADING_COMMENTS', 'Comments');
define('TABLE_HEADING_CUSTOMERS', 'Customers');
define('TABLE_HEADING_ORDER_TOTAL', 'Order Total');
define('TABLE_HEADING_DATE_PURCHASED', 'Date Purchased');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_TYPE', 'Order Type');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_QUANTITY', 'Qty.');
define('TABLE_HEADING_PRODUCTS_MODEL', 'Model');
define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_TAX', 'Tax');
define('TABLE_HEADING_TOTAL', 'Total');
define('TABLE_HEADING_PRICE_EXCLUDING_TAX', 'Price (ex)');
define('TABLE_HEADING_PRICE_INCLUDING_TAX', 'Price (inc)');
define('TABLE_HEADING_TOTAL_EXCLUDING_TAX', 'Total (ex)');
define('TABLE_HEADING_TOTAL_INCLUDING_TAX', 'Total (inc)');

define('TABLE_HEADING_CUSTOMER_NOTIFIED', 'Customer Notified');
define('TABLE_HEADING_DATE_ADDED', 'Date Added');

define('ENTRY_CUSTOMER', 'Customer:');
define('ENTRY_SOLD_TO', 'SOLD TO:');
define('ENTRY_DELIVERY_TO', 'Delivery To:');
define('ENTRY_SHIP_TO', 'SHIP TO:');
define('ENTRY_SHIPPING_ADDRESS', 'Shipping Address:');
define('ENTRY_BILLING_ADDRESS', 'Billing Address:');
define('ENTRY_PAYMENT_METHOD', 'Payment Method:');
define('ENTRY_CREDIT_CARD_TYPE', 'Credit Card Type:');
define('ENTRY_CREDIT_CARD_OWNER', 'Credit Card Owner:');
define('ENTRY_CREDIT_CARD_NUMBER', 'Credit Card Number:');
define('ENTRY_CREDIT_CARD_CVV', 'Credit Card CVV Number:');
define('ENTRY_CREDIT_CARD_EXPIRES', 'Credit Card Expires:');
define('ENTRY_SUB_TOTAL', 'Sub-Total:');
define('ENTRY_TAX', 'Tax:');
define('ENTRY_SHIPPING', 'Shipping:');
define('ENTRY_TOTAL', 'Total:');
define('ENTRY_DATE_PURCHASED', 'Date Purchased:');
define('ENTRY_STATUS', 'Status:');
define('ENTRY_DATE_LAST_UPDATED', 'Date Last Updated:');
define('ENTRY_NOTIFY_CUSTOMER', 'Notify Customer:');
define('ENTRY_NOTIFY_COMMENTS', 'Append Comments:');
define('ENTRY_PRINTABLE', 'Print Invoice');

define('TEXT_INFO_HEADING_DELETE_ORDER', 'Delete Order');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this order?');
define('TEXT_INFO_RESTOCK_PRODUCT_QUANTITY', 'Restock product quantity');
define('TEXT_DATE_ORDER_CREATED', 'Date Created:');
define('TEXT_DATE_ORDER_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_PAYMENT_METHOD', 'Payment Method:');
define('TEXT_PAID', 'Paid');
define('TEXT_UNPAID', 'Un-paid');

define('TEXT_ALL_ORDERS', 'All Orders');
define('TEXT_NO_ORDER_HISTORY', 'No Order History Available');

define('EMAIL_SEPARATOR', '------------------------------------------------------');
define('EMAIL_TEXT_SUBJECT', 'Order Update');
define('EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
define('EMAIL_TEXT_INVOICE_URL', 'Detailed Invoice:');
define('EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
define('EMAIL_TEXT_COMMENTS_UPDATE', '<em>The comments for your order are: </em>');
define('EMAIL_TEXT_STATUS_UPDATED', 'Your order has been updated to the following status:' . "\n");
define('EMAIL_TEXT_STATUS_LABEL', '<strong>New status:</strong> %s' . "\n\n");
define('EMAIL_TEXT_STATUS_PLEASE_REPLY', 'Please reply to this email if you have any questions.' . "\n");

define('ERROR_ORDER_DOES_NOT_EXIST', 'Error: Order does not exist.');
define('SUCCESS_ORDER_UPDATED', 'Success: Order has been successfully updated.');
define('WARNING_ORDER_NOT_UPDATED', 'Warning: Nothing to change. The order was not updated.');

define('ENTRY_ORDER_ID','Invoice No. ');
define('TEXT_INFO_ATTRIBUTE_FREE', '&nbsp;-&nbsp;<span class="alert">FREE</span>');

define('TEXT_DOWNLOAD_TITLE', 'Order Download Status');
define('TEXT_DOWNLOAD_STATUS', 'Status');
define('TEXT_DOWNLOAD_FILENAME', 'Filename');
define('TEXT_DOWNLOAD_MAX_DAYS', 'Days');
define('TEXT_DOWNLOAD_MAX_COUNT', 'Count');

define('TEXT_DOWNLOAD_AVAILABLE', 'Available');
define('TEXT_DOWNLOAD_EXPIRED', 'Expired');
define('TEXT_DOWNLOAD_MISSING', 'Not on Server');

define('IMAGE_ICON_STATUS_CURRENT', 'Status - Available');
define('IMAGE_ICON_STATUS_EXPIRED', 'Status - Expired');
define('IMAGE_ICON_STATUS_MISSING', 'Status - Missing');

define('SUCCESS_ORDER_UPDATED_DOWNLOAD_ON', 'Download was successfully enabled');
define('SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF', 'Download was successfully disabled');
define('TEXT_MORE', '... more');

define('TEXT_INFO_IP_ADDRESS', 'IP Address: ');
define('TEXT_DELETE_CVV_FROM_DATABASE','Delete CVV from database');
define('TEXT_DELETE_CVV_REPLACEMENT','Deleted');
define('TEXT_MASK_CC_NUMBER','Mask this number');

define('TEXT_INFO_EXPIRED_DATE', 'Expired Date:<br />');
define('TEXT_INFO_EXPIRED_COUNT', 'Expired Count:<br />');

define('TABLE_HEADING_CUSTOMER_COMMENTS', 'Customer<br />Comments');
define('TEXT_COMMENTS_YES', 'Customer Comments - YES');
define('TEXT_COMMENTS_NO', 'Customer Comments - NO');
?>