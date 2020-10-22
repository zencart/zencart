<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 28 Modified in v1.5.7 $
 */

define('HEADING_TITLE', 'Orders');
define('HEADING_TITLE_DETAILS', 'Order Details (#%u)');     //-%u is filled in with the actual order-number
define('HEADING_TITLE_SEARCH', 'Order ID:');
define('HEADING_TITLE_STATUS', 'Status:');
define('HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS', 'Product Name or ID:XX or Model');
define('HEADING_TITLE_SEARCH_ALL','Search: ');
define('HEADING_TITLE_SEARCH_PRODUCTS','Product search: ');
define('TEXT_RESET_FILTER', 'Remove search filter');
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
define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_TAX', 'Tax');
define('TABLE_HEADING_TOTAL', 'Total');
define('TABLE_HEADING_PRICE_EXCLUDING_TAX', 'Price (excl)');
define('TABLE_HEADING_PRICE_INCLUDING_TAX', 'Price (incl)');
define('TABLE_HEADING_TOTAL_EXCLUDING_TAX', 'Total (excl)');
define('TABLE_HEADING_TOTAL_INCLUDING_TAX', 'Total (incl)');
define('TABLE_HEADING_PRICE', 'Price');
define('TABLE_HEADING_UPDATED_BY', 'Updated By');

define('TABLE_HEADING_CUSTOMER_NOTIFIED', 'Customer Notified');
define('TABLE_HEADING_DATE_ADDED', 'Date Added');

define('ENTRY_CUSTOMER', 'Customer:');
define('ENTRY_CUSTOMER_ADDRESS', 'Customer Address:<br><i class="fa fa-2x fa-user"></i>');
define('ENTRY_SOLD_TO', 'SOLD TO:');
define('ENTRY_SHIP_TO', 'SHIP TO:');
define('ENTRY_SHIPPING_ADDRESS', 'Shipping Address:<br><i class="fa fa-2x fa-truck"></i>');
define('ENTRY_BILLING_ADDRESS', 'Billing Address:<br><i class="fa fa-2x fa-credit-card"></i>');
define('ENTRY_PAYMENT_METHOD', 'Payment Method:');
define('ENTRY_CREDIT_CARD_TYPE', 'Credit Card Type:');
define('ENTRY_CREDIT_CARD_OWNER', 'Credit Card Owner:');
define('ENTRY_CREDIT_CARD_NUMBER', 'Credit Card Number:');
define('ENTRY_CREDIT_CARD_CVV', 'Credit Card CVV Number:');
define('ENTRY_CREDIT_CARD_EXPIRES', 'Credit Card Expires:');
define('ENTRY_SHIPPING', 'Shipping:');
define('ENTRY_DATE_PURCHASED', 'Date Purchased:');
define('ENTRY_STATUS', 'Status:');
define('ENTRY_NOTIFY_CUSTOMER', 'Notify Customer:');
define('ENTRY_NOTIFY_COMMENTS', 'Append Comments:');

define('TEXT_INFO_HEADING_DELETE_ORDER', 'Delete Order');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this order?');
define('TEXT_INFO_RESTOCK_PRODUCT_QUANTITY', 'Restock product quantity');
define('TEXT_DATE_ORDER_CREATED', 'Date Created:');
define('TEXT_DATE_ORDER_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_PAYMENT_METHOD', 'Payment Method:');

define('TEXT_ALL_ORDERS', 'All Orders');

define('EMAIL_SEPARATOR', '------------------------------------------------------');
define('EMAIL_TEXT_SUBJECT', 'Order Update');
define('EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
define('EMAIL_TEXT_INVOICE_URL', 'Order Details:');
define('EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
define('EMAIL_TEXT_COMMENTS_UPDATE', '<em>The comments for your order are: </em>');
define('EMAIL_TEXT_STATUS_UPDATED', 'Your order has been updated to the following status:' . "\n");
define('EMAIL_TEXT_STATUS_LABEL', '<strong>New status:</strong> %s' . "\n\n");
define('EMAIL_TEXT_STATUS_PLEASE_REPLY', 'Please reply to this email if you have any questions.' . "\n");

define('ERROR_ORDER_DOES_NOT_EXIST', 'Error: Order does not exist.');
define('SUCCESS_ORDER_UPDATED', 'Success: Order has been successfully updated.');
define('WARNING_ORDER_NOT_UPDATED', 'Warning: Nothing to change. The order was not updated.');

define('ENTRY_ORDER_ID','Order No. ');
define('TEXT_INFO_ATTRIBUTE_FREE', '&nbsp;-&nbsp;<span class="alert">FREE</span>');

define('TEXT_DOWNLOAD','Download'); 
define('TEXT_DOWNLOAD_TITLE', 'Order Download Status');
define('TEXT_DOWNLOAD_STATUS', 'Status');
define('TEXT_DOWNLOAD_FILENAME', 'Filename');
define('TEXT_DOWNLOAD_MAX_DAYS', 'Days');
define('TEXT_DOWNLOAD_MAX_COUNT', 'Count');

define('TEXT_DOWNLOAD_AVAILABLE', 'Available');
define('TEXT_DOWNLOAD_EXPIRED', 'Expired');
define('TEXT_DOWNLOAD_MISSING', 'Not on Server');

define('TEXT_EXTENSION_NOT_UNDERSTOOD', 'File extension %s not supported'); 
define('TEXT_FILE_NOT_FOUND', 'File not found'); 
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

define('TEXT_CUSTOMER_LOOKUP', '<i class="fa fa-search"></i> Lookup Customer');

define('TEXT_INVALID_ORDER_STATUS', '<span class="alert">(Invalid Order Status)</span>');

define('BUTTON_TO_LIST', 'Order List');
define('SELECT_ORDER_LIST', 'Jump to Order:');

define('TEXT_MAP_CUSTOMER_ADDRESS', 'Map Customer Address');
define('TEXT_MAP_SHIPPING_ADDRESS', 'Map Shipping Address');
define('TEXT_MAP_BILLING_ADDRESS', 'Map Billing Address');

define('TEXT_EMAIL_LANGUAGE', 'Order Language: %s');
define('SUCCESS_EMAIL_SENT', 'Email %s sent to customer');
