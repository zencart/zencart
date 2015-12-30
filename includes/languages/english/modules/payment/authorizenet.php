<?php
/**
 * Authorize.net SIM Payment Module
 *
 * @package languageDefines
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: authorizenet.php  Modified in v1.5.5 $
 */

  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ADMIN_TITLE', 'Authorize.net (SIM)');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CATALOG_TITLE', 'Credit Card');  // Payment option title as displayed to the customer


  if (MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True') {
    define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION', '<a target="_blank" href="https://account.authorize.net/">Authorize.net Merchant Login</a>' . (MODULE_PAYMENT_AUTHORIZENET_TESTMODE != 'Production' ? '<br /><br />Testing Info:<br /><b>Automatic Approval Credit Card Numbers:</b><br />Visa#: 4007000000027<br />MC#: 5424000000000015<br />Discover#: 6011000000000012<br />AMEX#: 370000000000002<br /><br /><b>Note:</b> These credit card numbers will return a decline in live mode, and an approval in test mode.  Any future date can be used for the expiration date and any 3 or 4 (AMEX) digit number can be used for the CVV Code.<br /><br /><b>Automatic Decline Credit Card Number:</b><br /><br />Card #: 4222222222222<br /><br />This card number can be used to receive decline notices for testing purposes.' : '') . '<br /><br /><strong>SETTINGS</strong><br />Your "response" and "receipt" URL settings in your Authorize.net Merchant Profile can be left BLANK, or can be set to point to https://your_domain.com/foldername/index.php?main_page=checkout_process<br><br>See <a href="http://www.zen-cart.com/content.php?303-how-to-set-up-the-authorizenet-sim-payment-module" target="_blank">the SIM Setup FAQ article</a> for detailed setup instructions.');
  } else {
    define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION', '<a target="_blank" href="http://reseller.authorize.net/application.asp?id=131345">Click Here to Sign Up for an Account</a><br /><br /><a target="_blank" href="https://account.authorize.net/">Click to Login to the Authorize.net Merchant Area</a><br /><br /><strong>Requirements:</strong><br /><hr />*<strong>Authorize.net Account</strong> (see link above to signup)<br />*<strong>Authorize.net username and transaction key</strong> available from your Merchant Area<br><br>See <a href="http://www.zen-cart.com/content.php?303-how-to-set-up-the-authorizenet-sim-payment-module" target="_blank">the SIM Setup FAQ article</a> for detailed setup instructions.');
  }

  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_TYPE', 'Type:');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER', 'Card Owner:');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER', 'Card Number:');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES', 'Expiry Date:');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CVV', 'CVV Number:');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_POPUP_CVV_LINK', 'What\'s this?');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_CVV', '* The 3 or 4 digit CVV number must be entered from the back of the credit card.\n');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR_MESSAGE', 'There has been an error processing your credit card. Please try again.');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DECLINED_MESSAGE', 'Your credit card was declined. Please try another card or contact your bank for more info.');
  define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR', 'Credit Card Error!');
