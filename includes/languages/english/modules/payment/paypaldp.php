<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2005 CardinalCommerce
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 20 Modified in v1.5.7 $
 */

  define('MODULE_PAYMENT_PAYPALDP_TEXT_ADMIN_TITLE_WPP', 'PayPal Payments Pro');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_ADMIN_TITLE_NONUSA', 'PayPal Website Payments Pro');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_ADMIN_TITLE_PRO20', 'PayPal Website Payments Pro Payflow Edition (UK)');

  if (IS_ADMIN_FLAG === true) {
    define('MODULE_PAYMENT_PAYPALDP_TEXT_ADMIN_DESCRIPTION', '<strong>PayPal Payments Pro</strong>%s<br />' . '<a href="https://www.paypal.com" rel="noreferrer noopener" target="_blank">Manage your PayPal account.</a>' . '<br /><br /><font color="green">Configuration Instructions:</font><br /><span class="alert">1. </span><a href="https://www.zen-cart.com/partners/paypal-pro" rel="noopener" target="_blank">Sign up for your PayPal account - click here.</a><br />' .
(defined('MODULE_PAYMENT_PAYPALDP_STATUS') ? '' : '... and click "install" above to enable PayPal Payments Pro.<br /><a href="https://www.zen-cart.com/getpaypal" rel="noopener" target="_blank">For additional detailed help, see this FAQ article</a><br />') .
(!defined('MODULE_PAYMENT_PAYPALWPP_APISIGNATURE') || MODULE_PAYMENT_PAYPALWPP_APISIGNATURE === '' ? '<span class="alert">2. </span><strong>API credentials</strong> from the API Credentials option in your PayPal Profile Settings area. This module uses the <strong>API Signature</strong> option -- you will need the username, password and signature to enter in the fields below.' : '<span class="alert">2. </span>Ensure you have entered the appropriate security data for username/pwd etc, below.') .
'<font color="green"><hr /><strong>Requirements:</strong></font><br /><hr />*<strong>Express Checkout</strong> must be installed and activated in order to use PayPal Payments Pro, according to PayPal Terms of Service. <br />*Also requires CURL over SSL for outbound communications. CURL should be enabled for ports 80 and 443.<hr />' );
  }

  define('MODULE_PAYMENT_PAYPALDP_TEXT_DESCRIPTION', 'Credit Card');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_TITLE', 'Credit Card');
  define('MODULE_PAYMENT_PAYPALDP_DP_TEXT_TYPE', 'Credit Card (WPP)');
  define('MODULE_PAYMENT_PAYPALDP_PF_TEXT_TYPE', 'Credit Card (PF)');
  define('MODULE_PAYMENT_PAYPALDP_ERROR_HEADING', 'We\'re sorry, but we were unable to process your credit card.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CARD_ERROR', 'The credit card information you entered contains an error.  Please check it and try again.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_FIRSTNAME', 'First Name:');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_LASTNAME', 'Last Name:');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_OWNER', 'Card Owner:');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_TYPE', 'Card Type:');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_NUMBER', 'Card Number:');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_EXPIRES', 'Expiry Date:');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_ISSUE', 'Issue Date:');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_MAESTRO_ISSUENUMBER', 'Maestro Issue No.:');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_CHECKNUMBER', 'CVV Number:');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CREDIT_CARD_CHECKNUMBER_LOCATION', '(on back of the credit card)');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_TRANSACTION_FOR', 'Transaction for');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_DECLINED', 'Your credit card was declined. Please try another card or contact your bank for more information.');
  define('MODULE_PAYMENT_PAYPALDP_CANNOT_BE_COMPLETED', 'We were not able to process your order. Please select an alternate payment method, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_PAYPALDP_INVALID_RESPONSE', 'We were not able to process your order. Please try again, select an alternate payment method, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_GEN_ERROR', 'An error occurred when we tried to contact the payment processor. Please try again, select an alternate payment method, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_EMAIL_ERROR_MESSAGE', 'Dear store owner,' . "\n" . 'An error occurred when attempting to initiate the payment-validation transaction. As a courtesy, only the error "number" was shown to your customer.  The details of the error are shown below.' . "\n\n");
  define('MODULE_PAYMENT_PAYPALDP_TEXT_EMAIL_ERROR_SUBJECT', 'ALERT: PayPal Direct Payment Error');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_ADDR_ERROR', 'The address information you entered does not appear to be valid or cannot be matched. Please select or add a different address and try again.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_INSUFFICIENT_FUNDS_ERROR', 'PayPal was unable to successfully fund this transaction. Please choose another payment option or review funding options in your PayPal account before proceeding.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_ERROR', 'An error occurred when we tried to process your credit card. Please try again, select an alternate payment method, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_BAD_CARD', 'We apologize for the inconvenience, but the credit card you entered is not one that we accept. Please use a different credit card or verify that the details you entered are correct, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_BAD_LOGIN', 'There was a problem validating your account. Please try again.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_JS_CC_OWNER', '* The cardholder\'s name must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_JS_CC_CVV', '* The 3 or 4 digit CVV number must be entered from the back of the credit card.\n');
  define('MODULE_PAYMENT_PAYPALDP_ERROR_AVS_FAILURE_TEXT', 'ALERT: Address Verification Failure. ');
  define('MODULE_PAYMENT_PAYPALDP_ERROR_CVV_FAILURE_TEXT', 'ALERT: Card CVV Code Verification Failure. ');
  define('MODULE_PAYMENT_PAYPALDP_ERROR_AVSCVV_PROBLEM_TEXT', ' Order is on hold pending review by Store Owner.');

  define('MODULE_PAYMENT_PAYPALDP_TEXT_STATE_ERROR', 'The state assigned to your account is not valid.  Please go into your account settings and change it.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_NOT_WPP_ACCOUNT_ERROR', 'We are sorry for the inconvenience. The payment could not be initiated because the PayPal account configured by the store owner is not a PayPal Payments Pro account or PayPal gateway services have not been purchased. Or you have attempted to pay with an AmEx card and the merchant has not enabled AmEx support. Please select an alternate method of payment for your order or perhaps another type of credit card.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_NOT_US_WPP_ACCOUNT_ERROR', 'We are sorry for the inconvenience. The payment could not be initiated because the PayPal account configured by the store owner is not a US PayPal Payments Pro account or PayPal gateway services have not been purchased (or have not been activated by accepting the Billing Agreement on the PayPal website).  Please select an alternate method of payment for your order.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_NOT_UKWPP_ACCOUNT_ERROR', 'We are sorry for the inconvenience. The payment could not be initiated because the PayPal account configured by the store owner is not a PayPal Website Payments Pro 2.0 (UK) account or PayPal gateway services have not been purchased or not properly activated.  Please select an alternate method of payment for your order.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_SANDBOX_VS_LIVE_ERROR', 'We are sorry for the inconvenience. The PayPal account authentication settings are not yet set up, or the API security information is incorrect. We are unable to complete your transaction. Please notify the store owner so they can correct this problem.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_WPP_BAD_COUNTRY_ERROR', 'We are sorry -- the PayPal account configured by the store administrator is based in a country that is not supported for Website Payments Pro at the present time. Please choose another payment method to complete your order.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CANNOT_USE_THIS_CURRENCY_ERROR', 'We are sorry -- the credit card you are using is not compatible with the currency you selected for checkout. Please change your currency selection or choose another payment method to complete your order.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_NOT_CONFIGURED', '<span class="alert">&nbsp;(NOTE: Module is not configured yet)</span>');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CARD_TYPE_NOT_SUPPORTED', 'You have attempted to pay for your purchase using a credit card that is not accepted by this merchant. We are sorry for the inconvenience and invite you to try again using a different type of card, or contact the store owner for alternate payment choices.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_TRY_OTHER_PAYMENT_METHOD', 'PayPal has declined the funding-source you selected. Please try another payment type in your PayPal account, or try an alternate payment method. ');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_GETDETAILS_ERROR', 'There was a problem retrieving transaction details. ');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_TRANSSEARCH_ERROR', 'There was a problem locating transactions matching the criteria you specified. ');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_VOID_ERROR', 'There was a problem voiding the transaction. ');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_REFUND_ERROR', 'There was a problem refunding the transaction amount specified. ');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_AUTH_ERROR', 'There was a problem authorizing the transaction. ');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CAPT_ERROR', 'There was a problem capturing the transaction. ');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_REFUNDFULL_ERROR', 'Your Refund Request was rejected by PayPal.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_INVALID_REFUND_AMOUNT', 'You requested a partial refund but did not specify an amount.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_REFUND_FULL_CONFIRM_ERROR', 'You requested a full refund but did not check the Confirm box to verify your intent.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_INVALID_AUTH_AMOUNT', 'You requested an authorization but did not specify an amount.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_INVALID_CAPTURE_AMOUNT', 'You requested a capture but did not specify an amount.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_CONFIRM_CHECK', 'Confirm');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_VOID_CONFIRM_ERROR', 'You requested to void a transaction but did not check the Confirm box to verify your intent.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_FULL_CONFIRM_CHECK', 'Confirm');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_AUTH_CONFIRM_ERROR', 'You requested an authorization but did not check the Confirm box to verify your intent.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CAPTURE_FULL_CONFIRM_ERROR', 'You requested funds-Capture but did not check the Confirm box to verify your intent.');

  define('MODULE_PAYMENT_PAYPALDP_TEXT_REFUND_INITIATED', 'PayPal refund for %s initiated. Transaction ID: %s. Refresh the screen to see confirmation details updated in the Order Status History/Comments section.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_AUTH_INITIATED', 'PayPal Authorization for %s initiated. Refresh the screen to see confirmation details updated in the Order Status History/Comments section.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CAPT_INITIATED', 'PayPal Capture for %s initiated. Receipt ID: %s. Refresh the screen to see confirmation details updated in the Order Status History/Comments section.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_VOID_INITIATED', 'PayPal Void request initiated. Transaction ID: %s. Refresh the screen to see confirmation details updated in the Order Status History/Comments section.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_GEN_API_ERROR', 'There was an error in the attempted transaction. Please see the API Reference guide or transaction logs for detailed information.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_INVALID_ZONE_ERROR', 'We are sorry for the inconvenience; however, at the present time we are unable to use this method to process orders from the geographic region you selected as your account address.  Please continue using normal checkout and select from the available payment methods to complete your order.');


  // These are used for displaying raw transaction details in the Admin area:
  define('MODULE_PAYMENT_PAYPAL_ENTRY_FIRST_NAME', 'First Name:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_LAST_NAME', 'Last Name:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_BUSINESS_NAME', 'Business Name:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_NAME', 'Address Name:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STREET', 'Address Street:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_CITY', 'Address City:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATE', 'Address State:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_ZIP', 'Address Zip:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_COUNTRY', 'Address Country:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_EMAIL_ADDRESS', 'Payer Email:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_EBAY_ID', 'Ebay ID:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_ID', 'Payer ID:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_STATUS', 'Payer Status:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATUS', 'Address Status:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_TYPE', 'Payment Type:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_STATUS', 'Payment Status:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_PENDING_REASON', 'Pending Reason:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_INVOICE', 'Invoice:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_DATE', 'Payment Date:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CURRENCY', 'Currency:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_GROSS_AMOUNT', 'Gross Amount:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_FEE', 'Payment Fee:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_EXCHANGE_RATE', 'Exchange Rate:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CART_ITEMS', 'Cart items:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_TXN_TYPE', 'Trans. Type:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_TXN_ID', 'Trans. ID:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_PARENT_TXN_ID', 'Parent Trans. ID:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_TITLE', '<strong>Order Refunds</strong>');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_FULL', 'If you wish to refund this order in its entirety, click here:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_FULL', 'Do Full Refund');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_PARTIAL', 'Do Partial Refund');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_TEXT_FULL_OR', '<br />... or enter the partial ');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_PAYFLOW_TEXT', 'Enter the ');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_PARTIAL_TEXT', 'refund amount here and click on Partial Refund');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_SUFFIX', '*A Full refund may not be issued after a Partial refund has been applied.<br />*Multiple Partial refunds are permitted up to the remaining unrefunded balance.');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_TEXT_COMMENTS', '<strong>Note to display to customer:</strong>');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_DEFAULT_MESSAGE', 'Refunded by store administrator.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_FULL_CONFIRM_CHECK','Confirm: ');


  define('MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_TITLE', '<strong>Order Authorizations</strong>');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_PARTIAL_TEXT', 'If you wish to authorize part of this order, enter the amount  here:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_BUTTON_TEXT_PARTIAL', 'Do Authorization');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_SUFFIX', '');

  define('MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_TITLE', '<strong>Capturing Authorizations</strong>');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_FULL', 'If you wish to capture all or part of the outstanding authorized amounts for this order, enter the Capture Amount and select whether this is the final capture for this order.  Check the confirm box before submitting your Capture request.<br />');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_BUTTON_TEXT_FULL', 'Do Capture');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_AMOUNT_TEXT', 'Amount to Capture:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_FINAL_TEXT', 'Is this the final capture?');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_SUFFIX', '');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_TEXT_COMMENTS', '<strong>Note to display to customer:</strong>');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_DEFAULT_MESSAGE', 'Thank you for your order.');
  define('MODULE_PAYMENT_PAYPALDP_TEXT_CAPTURE_FULL_CONFIRM_CHECK','Confirm: ');

  define('MODULE_PAYMENT_PAYPAL_ENTRY_VOID_TITLE', '<strong>Voiding Order Authorizations</strong>');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_VOID', 'If you wish to void an authorization, enter the authorization ID here, and confirm:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_VOID_TEXT_COMMENTS', '<strong>Note to display to customer:</strong>');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_VOID_DEFAULT_MESSAGE', 'Thank you for your patronage. Please come again.');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_VOID_BUTTON_TEXT_FULL', 'Do Void');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_VOID_SUFFIX', '');

  define('MODULE_PAYMENT_PAYPAL_ENTRY_TRANSSTATE', 'Trans. State:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_AUTHCODE', 'Auth. Code:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_AVSADDR', 'AVS Address match:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_AVSZIP', 'AVS ZIP match:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_CVV2MATCH', 'CVV2 match:');
  define('MODULE_PAYMENT_PAYPAL_ENTRY_DAYSTOSETTLE', 'Days to Settle:');

  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_ONETIME_CHARGES_PREFIX', 'One-Time Charges related to ');
  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_SURCHARGES_SHORT', 'Surcharges');
  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_SURCHARGES_LONG', 'Handling charges and other applicable fees');
  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_DISCOUNTS_SHORT', 'Discounts');
  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_DISCOUNTS_LONG', 'Credits applied, including discount coupons, gift certificates, etc');

  define('MODULES_PAYMENT_PAYPALDP_TEXT_EMAIL_FMF_SUBJECT', 'Payment in Fraud Review Status: ');
  define('MODULES_PAYMENT_PAYPALDP_TEXT_EMAIL_FMF_INTRO', 'This is an automated notification to advise you that PayPal flagged the payment for a new order as Requiring Payment Review by their Fraud team. Normally the review is completed within 36 hours. It is STRONGLY ADVISED that you DO NOT SHIP the order until payment review is completed. You can see the latest review status of the order by logging into your PayPal account and reviewing recent transactions.');

  define('MODULES_PAYMENT_PAYPALWPP_AGGREGATE_CART_CONTENTS', 'All the items in your shopping basket (see details in the store and on your store receipt).');

  define('CENTINEL_AUTHENTICATION_ERROR', 'Authentication Failed - Your financial institution has indicated that it could not successfully authenticate this transaction. To protect against unauthorized use, this card cannot be used to complete your purchase. You may complete the purchase by selecting another form of payment.');
  define('CENTINEL_PROCESSING_ERROR', 'There was a problem obtaining authorization for your transaction. Please re-enter your payment information, and/or choose an alternate form of payment.');
  define("CENTINEL_ERROR_CODE_8000", "8000");
  define("CENTINEL_ERROR_CODE_8000_DESC", "Protocol Not Recognized, must be http:// or https://");
  define("CENTINEL_ERROR_CODE_8010", "8010");
  define("CENTINEL_ERROR_CODE_8010_DESC", "Unable to Communicate with MAPS Server");
  define("CENTINEL_ERROR_CODE_8020", "8020");
  define("CENTINEL_ERROR_CODE_8020_DESC", "Error Parsing XML Response");
  define("CENTINEL_ERROR_CODE_8030", "8030");
  define("CENTINEL_ERROR_CODE_8030_DESC", "Communication Timeout Encountered");
  define("CENTINEL_ERROR_CODE_1001", "1001");
  define("CENTINEL_ERROR_CODE_1001_DESC", "Account Configuration Problem with Cardinal Centinel. Please contact your Cardinal representative immediately on implement@cardinalcommerce.com. Your transactions will not be protected by chargeback liability until this problem is resolved.\n\n" . 'There are 3 steps to configuring your Cardinal 3D-Secure service properly: ' . "\n1-Login to the Cardinal Merchant Admin URL supplied in your welcome package (NOT the test URL), and accept the license agreement.\2-Set a transaction password.\n3-Copy your Cardinal Merchant ID and Cardinal Transaction Password into your ZC PayPal module.");
  define("CENTINEL_ERROR_CODE_4243", "4243");
  define("CENTINEL_ERROR_CODE_4243_DESC", "Account Configuration Problem with Cardinal Centinel. Please contact your Cardinal representative immediately on implement@cardinalcommerce.com and inform them that you are getting Error Number 4243 when attempting to use 3D Secure with your Zen Cart site and PayPal account and that you need to have the Processor Module enabled in your account. Your transactions will not be protected by chargeback liability until this problem is resolved.");
