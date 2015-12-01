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
// |                                                                      |
// |   DevosC, Developing open source Code                                |
// |   Copyright (c) 2004 DevosC.com                                      |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: paypal.php 3016 2006-02-12 05:26:46Z ajeh $
//

  // sort orders
  define('TEXT_PAYPAL_IPN_SORT_ORDER_INFO', 'Display Order: ');
  define('TEXT_SORT_PAYPAL_ID_DESC', 'PayPal Order Received (new - old)');
  define('TEXT_SORT_PAYPAL_ID', 'PayPal Order Received (old - new)');
  define('TEXT_SORT_ZEN_ORDER_ID_DESC', 'Order ID (high - low), PayPal Order Received');
  define('TEXT_SORT_ZEN_ORDER_ID', 'Order ID (low - high), PayPal Order Received');
  define('TEXT_PAYMENT_AMOUNT_DESC', 'Order Amount (high - low)');
  define('TEXT_PAYMENT_AMOUNT', 'Order Amount (low - high)');

  //begin ADMIN text
  define('HEADING_ADMIN_TITLE', 'PayPal Instant Payment Notifications');
  define('HEADING_PAYMENT_STATUS', 'Payment Status');
  define('TEXT_ALL_IPNS', 'All');

  define('TABLE_HEADING_ORDER_NUMBER', 'Order #');
  define('TABLE_HEADING_PAYPAL_ID', 'PayPal #');
  define('TABLE_HEADING_TXN_TYPE', 'Transaction Type');
  define('TABLE_HEADING_PAYMENT_STATUS', 'Payment Status');
  define('TABLE_HEADING_PAYMENT_AMOUNT', 'Amount');
  define('TABLE_HEADING_ACTION', 'Action');
  define('TABLE_HEADING_DATE_ADDED', 'Date Added');
  define('TABLE_HEADING_NUM_HISTORY_ENTRIES', 'Number of entries in Status History');
  define('TABLE_HEADING_ENTRY_NUM', 'Entry Number');
  define('TABLE_HEADING_TRANS_ID', 'Trans. ID');



  define('TEXT_INFO_PAYPAL_IPN_HEADING', 'PayPal IPN');
  define('TEXT_DISPLAY_NUMBER_OF_TRANSACTIONS', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> IPN\'s)');

  //Details section
  define('HEADING_DEATILS_CUSTOMER_REGISTRATION_TITLE', 'PayPal Customer Registration Details');
  define('HEADING_DETAILS_REGISTRATION_TITLE', 'PayPal Instant Payment Notification');
  define('TEXT_INFO_ENTRY_ADDRESS', 'Address');
  define('TEXT_INFO_ORDER_NUMBER', 'Order Number');
  define('TEXT_INFO_TXN_TYPE', 'Transaction Type');
  define('TEXT_INFO_PAYMENT_STATUS', 'Payment Status');
  define('TEXT_INFO_PAYMENT_AMOUNT', 'Amount');
  define('ENTRY_FIRST_NAME', 'First Name');
  define('ENTRY_LAST_NAME', 'Last Name');
  define('ENTRY_BUSINESS_NAME', 'Business Name');
  define('ENTRY_ADDRESS', 'Address');
  //EMAIL ALREADY DEFINED IN ORDERS
  define('ENTRY_PAYER_ID', 'Payer ID');
  define('ENTRY_PAYER_STATUS', 'Payer Status');
  define('ENTRY_ADDRESS_STATUS', 'Address Status');
  define('ENTRY_PAYMENT_TYPE', 'Payment Type');
  define('TABLE_HEADING_ENTRY_PAYMENT_STATUS', 'Payment Status');
  define('TABLE_HEADING_PENDING_REASON', 'Pending Reason');
  define('TABLE_HEADING_IPN_DATE', 'IPN Date');
  define('ENTRY_INVOICE', 'Invoice');
  define('ENTRY_PAYPAL_IPN_TXN', 'Transaction ID');
  define('ENTRY_PAYMENT_DATE', 'Payment Date');
  define('ENTRY_PAYMENT_LAST_MODIFIED', 'Last modified');
  define('ENTRY_MC_CURRENCY', 'MC Currency');
  define('ENTRY_MC_GROSS', 'MC Gross');
  define('ENTRY_MC_FEE', 'MC Fee');
  define('ENTRY_PAYMENT_GROSS', 'Payment Gross');
  define('ENTRY_PAYMENT_FEE', 'Payment Fee');
  define('ENTRY_SETTLE_AMOUNT', 'Settle Amount');
  define('ENTRY_SETTLE_CURRENCY', 'Settle Currency');
  define('ENTRY_EXCHANGE_RATE', 'Exchange Rate');
  define('ENTRY_CART_ITEMS', 'No Of Cart Items');
  define('ENTRY_CUSTOMER_COMMENTS', 'Customer Comments');
  define('TEXT_NO_IPN_HISTORY', 'No IPN history available');
  define('TEXT_TXN_SIGNATURE', 'Transaction Signature');
  //end ADMIN text
?>
