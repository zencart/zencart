<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 Apr 27 Modified in v1.5.7 $
 */

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
define('TABLE_HEADING_PENDING_REASON', 'Pending Reason');

define('TEXT_INFO_PAYPAL_IPN_HEADING', 'PayPal IPN');
define('TEXT_DISPLAY_PAYPAL_IPN_NUMBER_OF_TX', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> Transactions)');

// Other constants are in includes/languages/english/modules/payment/paypal.php
//end ADMIN text
