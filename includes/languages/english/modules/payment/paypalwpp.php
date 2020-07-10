<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */

  define('MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_EC', 'PayPal Express Checkout');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_PRO20', 'PayPal Express Checkout (Pro 2.0 Payflow Edition) (UK)');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_PF_EC', 'PayPal Payflow Pro - Gateway');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_TITLE_PF_GATEWAY', 'PayPal Express Checkout via Payflow Pro');

  if (IS_ADMIN_FLAG === true) {
    if (!defined('MODULE_PAYMENT_PAYPALWPP_MODULE_MODE')) define('MODULE_PAYMENT_PAYPALWPP_MODULE_MODE', 'undefined');
    define('MODULE_PAYMENT_PAYPALWPP_TEXT_ADMIN_DESCRIPTION', '<strong>PayPal Express Checkout</strong>%s<br />' . (substr(MODULE_PAYMENT_PAYPALWPP_MODULE_MODE,0,7) == 'Payflow' ? '<a href="https://manager.paypal.com/loginPage.do?partner=ZenCart" rel="noreferrer noopener" target="_blank">Manage your PayPal account.</a>' : '<a href="https://www.paypal.com" rel="noopener" target="_blank">Manage your PayPal account.</a>') . '<br /><br /><font color="green">Configuration Instructions:</font><br /><span class="alert">1. </span><a href="https://www.zen-cart.com/partners/paypal-ec" rel="noopener" target="_blank">Sign up for your PayPal account - click here.</a><br />' .
(defined('MODULE_PAYMENT_PAYPALWPP_STATUS') ? '' : '... and click "install" above to enable PayPal Express Checkout support.<br /><a href="https://www.zen-cart.com/getpaypal" rel="noopener" target="_blank">For additional detailed help, see this FAQ article</a><br />') .
(MODULE_PAYMENT_PAYPALWPP_MODULE_MODE == 'PayPal' && (!defined('MODULE_PAYMENT_PAYPALWPP_APISIGNATURE') || MODULE_PAYMENT_PAYPALWPP_APISIGNATURE === '') ? '<br /><span class="alert">2. </span><strong>API credentials</strong> from the API Credentials option in your PayPal Profile Settings area. (Click <a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" rel="noopener" target="_blank">here for API credentials</a>.) <br />This module uses the <strong>API Signature</strong> option -- you will need the username, password and signature to enter in the fields below.' : (substr(MODULE_PAYMENT_PAYPALWPP_MODULE_MODE,0,7) == 'Payflow' && (!defined('MODULE_PAYMENT_PAYPALWPP_PFUSER') || MODULE_PAYMENT_PAYPALWPP_PFUSER == '') ? '<span class="alert">2. </span><strong>PAYFLOW credentials</strong> This module needs your <strong>PAYFLOW Partner+Vendor+User+Password settings</strong> entered in the 4 fields below. These will be used to communicate with the Payflow system and authorize transactions to your account.' : '<span class="alert">2. </span>Ensure you have entered the appropriate security data for username/pwd etc, below.') ) .
(MODULE_PAYMENT_PAYPALWPP_MODULE_MODE == 'PayPal' ? '<br /><br /><span class="alert">3. </span>In your PayPal account, enable <strong>Instant Payment Notification</strong>:<br />under "Profile", select <em>Instant Payment Notification Preferences</em><ul style="margin-top: 0.5em;"><li>click the checkbox to enable IPN</li><li>if there is not already a URL specified, set the URL to:<br /><nobr><pre>'.str_replace('index.php?main_page=index','ipn_main_handler.php',zen_catalog_href_link(FILENAME_DEFAULT, '', 'SSL')) . '</pre></nobr></li></ul>' : '') .
'<font color="green"><hr /><strong>Requirements:</strong></font><br /><hr />*<strong>CURL</strong> is used for outbound communication with the gateway over ports 80 and 443, so must be active on your hosting server and able to use SSL.<br /><hr />' );
  }

  define('MODULE_PAYMENT_PAYPALWPP_TEXT_DESCRIPTION', '<strong>PayPal</strong>');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_TITLE', 'Credit Card');
  define('MODULE_PAYMENT_PAYPALWPP_EC_TEXT_TITLE', 'PayPal');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_EC_HEADER', 'Fast, Secure Checkout with PayPal:');
  define('MODULE_PAYMENT_PAYPALWPP_EC_TEXT_TYPE', 'PayPal Express Checkout');
  define('MODULE_PAYMENT_PAYPALWPP_DP_TEXT_TYPE', 'PayPal Direct Payment');
  define('MODULE_PAYMENT_PAYPALWPP_PF_TEXT_TYPE', 'Credit Card');  //(used for payflow transactions)
  define('MODULE_PAYMENT_PAYPALWPP_ERROR_HEADING', 'We\'re sorry, but we were unable to process your credit card.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CARD_ERROR', 'The credit card information you entered contains an error.  Please check it and try again.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CREDIT_CARD_FIRSTNAME', 'Credit Card First Name:');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CREDIT_CARD_LASTNAME', 'Credit Card Last Name:');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CREDIT_CARD_OWNER', 'Cardholder Name:');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CREDIT_CARD_ISSUE', 'Credit Card Issue Date:');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CREDIT_CARD_CHECKNUMBER', 'CVV Number:');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CREDIT_CARD_CHECKNUMBER_LOCATION', '(on back of the credit card)');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_DECLINED', 'Your credit card was declined. Please try another card or contact your bank for more information.');
  define('MODULE_PAYMENT_PAYPALWPP_INVALID_RESPONSE', 'We were not able to process your order. Please try again, select an alternate payment method, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_GEN_ERROR', 'An error occurred when we tried to contact the payment processor. Please try again, select an alternate payment method, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_EMAIL_ERROR_MESSAGE', 'Dear store owner,' . "\n" . 'An error occurred when attempting to initiate a PayPal Express Checkout transaction. As a courtesy, only the error "number" was shown to your customer.  The details of the error are shown below.' . "\n\n");
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_EMAIL_ERROR_SUBJECT', 'ALERT: PayPal Express Checkout Error');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_ADDR_ERROR', 'The address information you entered does not appear to be valid or cannot be matched. Please select or add a different address and try again.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CONFIRMEDADDR_ERROR', 'The address you selected at PayPal is not a Confirmed address. Please return to PayPal and select or add a confirmed address and try again.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_INSUFFICIENT_FUNDS_ERROR', 'PayPal was unable to successfully fund this transaction. Please choose another payment option or review funding options in your PayPal account before proceeding.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_PAYPAL_DECLINED', 'Sorry. PayPal has declined the transaction and advised us to tell you to contact PayPal Customer Service for more information. To complete your purchase, please select an alternate payment method.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_ERROR', 'An error occurred when we tried to process your credit card. Please try again, select an alternate payment method, or contact the store owner for assistance.');
  define('MODULE_PAYMENT_PAYPALWPP_FUNDING_ERROR','Funding source problem; please go to Paypal.com and make payment directly to ' . STORE_OWNER_EMAIL_ADDRESS);
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_BAD_CARD', 'We apologize for the inconvenience, but the credit card you entered is not one that we accept. Please use a different credit card.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_BAD_LOGIN', 'There was a problem validating your account. Please try again.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_JS_CC_OWNER', '* The cardholder\'s name must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_PAYPALWPP_ERROR_AVS_FAILURE_TEXT', 'ALERT: Address Verification Failure. ');
  define('MODULE_PAYMENT_PAYPALWPP_ERROR_CVV_FAILURE_TEXT', 'ALERT: Card CVV Code Verification Failure. ');
  define('MODULE_PAYMENT_PAYPALWPP_ERROR_AVSCVV_PROBLEM_TEXT', ' Order is on hold pending review by Store Owner.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_UNILATERAL', ' - You need to register your PayPal API Credentials before you can do advanced transaction processing.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_STATE_ERROR', 'The state assigned to your account is not valid.  Please go into your account settings and change it.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_NOT_WPP_ACCOUNT_ERROR', 'We are sorry for the inconvenience. The payment could not be initiated because the PayPal account configured by the store owner is not a PayPal Website Payments Pro account or PayPal gateway services have not been purchased.  Please select an alternate method of payment for your order.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_SANDBOX_VS_LIVE_ERROR', 'We are sorry for the inconvenience. The PayPal account authentication settings are not yet set up, or the API security information is incorrect. We are unable to complete your transaction. Please notify the store owner so they can correct this problem.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_WPP_BAD_COUNTRY_ERROR', 'We are sorry -- the PayPal account configured by the store administrator is based in a country that is not supported for Website Payments Pro at the present time. Please choose another payment method to complete your order.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_NOT_CONFIGURED', '<span class="alert">&nbsp;(NOTE: Module is not configured yet)</span>');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_GETDETAILS_ERROR', 'There was a problem retrieving transaction details. ');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_TRANSSEARCH_ERROR', 'There was a problem locating transactions matching the criteria you specified. ');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_ERROR', 'There was a problem voiding the transaction. ');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_ERROR', 'There was a problem refunding the transaction amount specified. ');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_ERROR', 'There was a problem authorizing the transaction. ');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CAPT_ERROR', 'There was a problem capturing the transaction. ');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_REFUNDFULL_ERROR', 'Your Refund Request was rejected by PayPal.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_REFUND_AMOUNT', 'You requested a partial refund but did not specify an amount.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_FULL_CONFIRM_ERROR', 'You requested a full refund but did not check the Confirm box to verify your intent.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_AUTH_AMOUNT', 'You requested an authorization but did not specify an amount.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_CAPTURE_AMOUNT', 'You requested a capture but did not specify an amount.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_CONFIRM_CHECK', 'Confirm');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_CONFIRM_ERROR', 'You requested to void a transaction but did not check the Confirm box to verify your intent.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_FULL_CONFIRM_CHECK', 'Confirm');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_CONFIRM_ERROR', 'You requested an authorization but did not check the Confirm box to verify your intent.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CAPTURE_FULL_CONFIRM_ERROR', 'You requested funds-Capture but did not check the Confirm box to verify your intent.');

  define('MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_INITIATED', 'PayPal refund for %s initiated. Transaction ID: %s. Refresh the screen to see confirmation details updated in the Order Status History/Comments section.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_INITIATED', 'PayPal Authorization for %s initiated. Refresh the screen to see confirmation details updated in the Order Status History/Comments section.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CAPT_INITIATED', 'PayPal Capture for %s initiated. Receipt ID: %s. Refresh the screen to see confirmation details updated in the Order Status History/Comments section.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_INITIATED', 'PayPal Void request initiated. Transaction ID: %s. Refresh the screen to see confirmation details updated in the Order Status History/Comments section.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_GEN_API_ERROR', 'There was an error in the attempted transaction. Please see the API Reference guide or transaction logs for detailed information.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_INVALID_ZONE_ERROR', 'We are sorry for the inconvenience; however, at the present time we are unable to use PayPal to process orders from the geographic region you selected as your PayPal address.  Please continue using normal checkout and select from the available payment methods to complete your order.');
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_ORDER_ALREADY_PLACED_ERROR', 'It appears that your order was submitted twice. Please check the My Account area to see the actual order details.  Please use the Contact Us form if your order does not appear here but is already paid from your PayPal account so that we may check our records and reconcile this with you.');

  define('MODULE_PAYMENT_PAYPALWPP_TEXT_BUTTON_ALTTEXT', 'Click here to pay via PayPal Express Checkout');

// EC buttons -- Do not change these values
///// You should only use choices listed on this page: https://developer.paypal.com/docs/classic/api/buttons/ or https://www.paypal-techsupport.com/app/answers/detail/a_id/632
  //define('MODULE_PAYMENT_PAYPALWPP_EC_BUTTON_IMG', 'https://www.paypalobjects.com/en_US/i/btn/btn_xpressCheckout.gif');
  define('MODULE_PAYMENT_PAYPALWPP_EC_BUTTON_IMG', 'https://www.paypalobjects.com/webstatic/en_US/btn/btn_checkout_pp_142x27.png');
  define('MODULE_PAYMENT_PAYPALWPP_EC_BUTTON_SM_IMG', 'https://www.paypalobjects.com/en_US/i/btn/btn_xpressCheckoutsm.gif');

  //define('MODULE_PAYMENT_PAYPALWPP_MARK_BUTTON_TXT', 'Checkout with PayPal. The safer, easier way to pay.');
  define('MODULE_PAYMENT_PAYPALWPP_MARK_BUTTON_TXT', '');
  //define('MODULE_PAYMENT_PAYPALEC_MARK_BUTTON_IMG', 'https://www.paypalobjects.com/en_US/i/logo/PayPal_mark_37x23.gif');
  //define('MODULE_PAYMENT_PAYPALEC_MARK_BUTTON_IMG', 'https://www.paypalobjects.com/en_US/i/logo/PayPal_mark_50x34.gif');
  //define('MODULE_PAYMENT_PAYPALEC_MARK_BUTTON_IMG', 'https://www.paypalobjects.com/en_US/i/bnr/horizontal_solution_PP.gif');
  //define('MODULE_PAYMENT_PAYPALEC_MARK_BUTTON_IMG', 'https://www.paypalobjects.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif');
  define('MODULE_PAYMENT_PAYPALEC_MARK_BUTTON_IMG', 'https://www.paypalobjects.com/en_US/i/btn/btn1_for_hub.gif');

////////////////////////////////////////
// Styling of the PayPal Payment Page. Uncomment to customize.
// A BETTER WAY, HOWEVER, is to simply create a Custom Page Style at PayPal and mark it as Primary or name it in your Zen Cart PayPal EC settings.
  //define('MODULE_PAYMENT_PAYPALWPP_HEADER_IMAGE', '');  // this should be an HTTPS URL to the image file
  //define('MODULE_PAYMENT_PAYPALWPP_PAGECOLOR', '');  // 6-digit hex value
 ////// Styling of pseudo cart contents display section
  //define('MODULE_PAYMENT_PAYPAL_LOGO_IMAGE', ''); // https path to your customized logo
  //define('MODULE_PAYMENT_PAYPAL_CART_BORDER_COLOR', ''); // 6-digit hex value
////////////////////////////////////////


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
  define('MODULE_PAYMENT_PAYPAL_ENTRY_COMMENTS', 'System Comments: ');
  define('MODULE_PAYMENT_PAYPALWPP_ENTRY_PROTECTIONELIG', 'Protection Eligibility:');

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
  define('MODULE_PAYMENT_PAYPALWPP_TEXT_CAPTURE_FULL_CONFIRM_CHECK','Confirm: ');

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

// this text is used to announce the username/password when the module creates the customer account and emails data to them:
  define('EMAIL_EC_ACCOUNT_INFORMATION', 'Your account login details, which you can use to review your purchase, are as follows:');

  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_ONETIME_CHARGES_PREFIX', 'One-Time Charges related to ');
  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_SURCHARGES_SHORT', 'Surcharges');
  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_SURCHARGES_LONG', 'Handling charges and other applicable fees');
  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_DISCOUNTS_SHORT', 'Discounts');
  define('MODULES_PAYMENT_PAYPALWPP_LINEITEM_TEXT_DISCOUNTS_LONG', 'Credits applied, including discount coupons, gift certificates, etc');
  define('MODULES_PAYMENT_PAYPALDP_TEXT_EMAIL_FMF_SUBJECT', 'Payment in Fraud Review Status: ');
  define('MODULES_PAYMENT_PAYPALDP_TEXT_EMAIL_FMF_INTRO', 'This is an automated notification to advise you that PayPal flagged the payment for a new order as Requiring Payment Review by their Fraud team. Normally the review is completed within 36 hours. It is STRONGLY ADVISED that you DO NOT SHIP the order until payment review is completed. You can see the latest review status of the order by logging into your PayPal account and reviewing recent transactions.');

  define('MODULES_PAYMENT_PAYPALWPP_TEXT_BLANK_ADDRESS', 'PROBLEM: We&#39;re sorry. PayPal has unexpectedly returned a blank address. <br />In order to complete your purchase, please provide your address by clicking the &quot;Sign Up&quot; button below to create an account in our store. Then you may select PayPal again when you continue with checkout. We apologize for the inconvenience. If you have any trouble with checkout, please click the Contact Us link to explain the details to us so we can help you with your purchase and prevent the problem in the future. Thanks.');

  define('MODULES_PAYMENT_PAYPALWPP_AGGREGATE_CART_CONTENTS', 'All the items in your shopping basket (see details in the store and on your store receipt).');

