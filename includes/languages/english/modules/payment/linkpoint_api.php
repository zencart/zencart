<?php
/**
 * @package Linkpoint/Yourpay API payment_module
 * @copyright Copyright 2003-2009 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright 2003 Jason LeBaron
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: linkpoint_api.php 14141 2009-08-10 19:34:47Z wilt $
 */

  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_ADMIN_TITLE', 'FirstData/Linkpoint/YourPay API');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CATALOG_TITLE', 'Credit Card');

  if (defined('MODULE_PAYMENT_LINKPOINT_API_STATUS') && MODULE_PAYMENT_LINKPOINT_API_STATUS == 'True') {
    define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DESCRIPTION', '<a target="_blank" href="https://secure.linkpt.net/lpcentral/servlet/LPCLogin">FirstData/Linkpoint/YourPay Merchant Login</a>' . (MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_MODE_RESPONSE != 'LIVE: Production' ? '<br /><br /><strong>Linkpoint/YourPay API Test Card Numbers:</strong><br /><strong>Visa:</strong> 4111111111111111<br /><strong>MasterCard:</strong> 5419840000000003<br /><strong>Amex:</strong> 371111111111111<br /><strong>Discover:</strong> 6011111111111111' : '') . '<br /><a target="_blank" href="http://tutorials.zen-cart.com/index.php?article=298">Troubleshooting/Setup FAQ</a>');
  } else {
    define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DESCRIPTION', '<a target="_blank" href="http://www.zen-cart.com/partners/firstdata">Click Here to Sign Up for an Account</a><br /><br /><a target="_blank" href="https://secure.linkpt.net/lpcentral/servlet/LPCLogin">FirstData/Linkpoint/YourPay API Merchant Area</a><br /><br /><a target="_blank" href="http://tutorials.zen-cart.com/index.php?article=298">Click here for <strong>SETUP/Troubleshooting Instructions</strong></a><br /><br /><strong>Requirements:</strong><br /><hr />*<strong>LinkPoint or YourPay Account</strong> (see link above to signup)<br />*<strong>cURL is required </strong>and MUST be compiled into PHP by your hosting company<br />*<strong>Port 1129</strong> is used for bidirectional communication with the gateway, so must be open on your host\'s router/firewall<br />*<strong>PEM RSA Key File </strong>Digital Certificate:<br />To obtain and upload your Digital Certificate (.PEM) key:<br />- Log in to your LinkPoint/Yourpay account on their website<br />- Click on "Support" in the Main Menu Bar.<br />- Click on the word "Download Center" under Downloads in the Side Menu Box.<br />- Click on the word "download" beside the "Store PEM File" section on the right-hand side of the page.<br />- Key in necessary information to start download. You will need to supply your actual SSN or Tax ID which you submitted during the merchant account boarding process.<br />- Upload this file to includes/modules/payment/linkpoint_api/XXXXXX.pem (provided by FirstData - xxxxxx is your store id)');
  }
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CVV', 'CVV Number:');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_JS_CC_OWNER', '* The cardholder name must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_JS_CC_CVV', '* You must enter the 3 or 4 digit number on the back of your credit card');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_ERROR', 'Credit Card Error!');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DECLINED_MESSAGE', 'Your card has been declined.  Please re-enter your card information, try another card, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DECLINED_AVS_MESSAGE', 'Invalid Billing Address.  Please re-enter your card information, try another card, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DECLINED_GENERAL_MESSAGE', 'Your card has been declined.  Please re-enter your card information, try another card, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_POPUP_CVV_LINK', 'What\'s this?');
  define('ALERT_LINKPOINT_API_PREAUTH_TRANS', '***AUTHORIZATION ONLY -- CHARGES WILL BE SETTLED LATER BY THE ADMINISTRATOR.***' . "\n");
  define('ALERT_LINKPOINT_API_TEST_FORCED_SUCCESSFUL', 'NOTE: This was a TEST transaction...forced to return a SUCCESS response.');
  define('ALERT_LINKPOINT_API_TEST_FORCED_DECLINED', 'NOTE: This was a TEST transaction...forced to return a DECLINED response.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_NOT_CONFIGURED', '<span class="alert">&nbsp;(NOTE: Module is not configured yet)</span>');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_PEMFILE_MISSING', '<span class="alert">&nbsp;The xyzxyz.pem certificate file cannot be found.</span>');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_ERROR_CURL_NOT_FOUND', 'CURL functions not found - required for FirstData/Linkpoint API payment module');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_DUPLICATE_MESSAGE', 'This appears to be a duplicate transaction, and we do not want to double-charge your card! Perhaps you resubmitted your order by mistake? If not, please try again in 5 minutes, or contact the storeowner for additional assistance. Sorry for the inconvenience.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_FAILURE_MESSAGE', 'We apologize for the inconvenience, but we are presently unable to contact the Credit Card company for authorization. Please contact the Store Owner for payment alternatives.');
  // note: the above error can occur as a result of:
     // - port 1129 not open for bidirectional communication
     // - CURL is not installed or not functioning
     // - incorrect or invalid or "no" .PEM file found in modules/payment/linkpoint_api folder
     // - In general it means that there was no valid connection made to the gateway... it was stopped before it got outside your server

  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_GENERAL_ERROR', 'We are sorry. There was a system error while processing your card. Your information is safe. Please notify the Store Owner to arrange alternate payment options.');
  // note: the above error is a general error message which is reported when serious and known error conditions occur. Further details are given immediately following the display of this message. If database storage is enabled, details can be found there too.


  // Admin definitions

  define('MODULE_PAYMENT_LINKPOINT_API_LINKPOINT_ORDER_ID', 'FirstData Order ID:');
  define('MODULE_PAYMENT_LINKPOINT_API_AVS_RESPONSE', 'AVS Response:');
  define('MODULE_PAYMENT_LINKPOINT_API_MESSAGE', 'Response Message:');
  define('MODULE_PAYMENT_LINKPOINT_API_APPROVAL_CODE', 'Approval Code:');
  define('MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_REFERENCE_NUMBER', 'Reference Number:');
  define('MODULE_PAYMENT_LINKPOINT_API_FRAUD_SCORE', 'Fraud Score:');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_TEST_MODE', '<span class="alert">&nbsp;(NOTE: Module is in testing mode)</span>');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_ORDERTYPE', 'Order Type:');


// admin tools:
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_NO_MATCHING_ORDER_FOUND', 'Error: Could not find transaction details for the record specified.');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_BUTTON_TEXT', 'Do Refund');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_CONFIRM_ERROR', 'Error: You requested to do a refund but did not check the Confirmation box.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_INVALID_REFUND_AMOUNT', 'Error: You requested a refund but entered an invalid amount.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CC_NUM_REQUIRED_ERROR', 'Error: You requested a refund but didn\'t enter the last 4 digits of the Credit Card number.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_INITIATED', 'Refund Initiated. Transaction ID: %s - Order ID: %s');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPTURE_CONFIRM_ERROR', 'Error: You requested to do a capture but did not check the Confirmation box.');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_BUTTON_TEXT', 'Do Capture');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_INVALID_CAPTURE_AMOUNT', 'Error: You requested a capture but need to enter an amount.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_TRANS_ID_REQUIRED_ERROR', 'Error: You need to specify a Transaction ID.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPT_INITIATED', 'Funds Capture initiated. Amount: %s.  Transaction ID: %s - AuthCode: %s');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_BUTTON_TEXT', 'Do Void');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_CONFIRM_ERROR', 'Error: You requested a Void but did not check the Confirmation box.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_INITIATED', 'Void Initiated. Transaction ID: %s - Order ID: %s ');

  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_TITLE', '<strong>Refund Transactions</strong>');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND', 'You may refund money to the customer\'s original credit card here.');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_CONFIRM_CHECK', 'Check this box to confirm your intent: ');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_AMOUNT_TEXT', 'Enter the amount you wish to refund');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_DEFAULT_TEXT', 'enter Trans.ID');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_CC_NUM_TEXT', 'Enter the last 4 digits of the Credit Card you are refunding.');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_TRANS_ID', 'Enter the original Transaction ID <em>(which usually looks like this: <strong>1193684363</strong>)</em>:');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_TEXT_COMMENTS', 'Notes (will show on Order History):');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_DEFAULT_MESSAGE', 'Refund Issued');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_SUFFIX', 'You may refund an order up to the amount already captured. You must supply the last 4 digits of the credit card number used on the initial order.<br />Refunds cannot be issued if the card has expired. To refund an expired card, issue a credit using the merchant terminal instead.');

  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_TITLE', '<strong>Capture Transactions</strong>');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE', 'You may capture previously-authorized funds here:');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_AMOUNT_TEXT', 'Enter the amount to Capture: ');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPTURE_CONFIRM_CHECK', 'Check this box to confirm your intent: ');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_TRANS_ID', 'Enter the original Order Number <em>(ie: <strong>5138-i4wcYM</strong>)</em> : ');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_DEFAULT_TEXT', 'enter Order Number');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_TEXT_COMMENTS', 'Notes (will show on Order History):');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_DEFAULT_MESSAGE', 'Settled previously-authorized funds.');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_SUFFIX', 'Captures must be performed within 2-10 days of the original authorization depending on your merchant bank requirement. You may  capture an order ONLY ONCE. <br />Please be sure the amount specified is correct.<br />If you leave the amount blank, the original amount will be used instead.');

  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_TITLE', '<strong>Voiding Transactions</strong>');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID', 'You may void a transaction (preauth/capture/refund) which has not yet been settled. Please enter the original Transaction ID <em>(usually looks like this: <strong>1193684363</strong>)</em>:');
  define('MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_CONFIRM_CHECK', 'Check this box to confirm your intent:');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_DEFAULT_TEXT', 'enter Trans.ID');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_TEXT_COMMENTS', 'Notes (will show on Order History):');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_DEFAULT_MESSAGE', 'Transaction Canceled');
  define('MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_SUFFIX', 'Voids must be completed before the original transaction is settled in the daily batch, which occurs at 7:00PM Pacific Time.');


?>