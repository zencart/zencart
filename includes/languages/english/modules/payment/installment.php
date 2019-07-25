<?php
//
// +----------------------------------------------------------------------+
// |Credit card installment plan                                          |
// |Copyright (c) 2007, That Software Guy                                 |
// +----------------------------------------------------------------------+
// | Portions Copyright (c) 2003-2006 The zen-cart developers             |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// Based on cc.php; modified by That Software Guy.
//

  define('MODULE_PAYMENT_INSTALLMENT_TEXT_TITLE', 'Installment Plan');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_EXPLAIN_TITLE', 'How it works');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_EXPLAIN_DETAILS', 'Details'); 
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_EXPLAIN_VERBAGE', 'We charge your card monthly for %d months'); 
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_DESCRIPTION', 'Credit Card Test Info:<br /><br />CC#: 4111111111111111<br />Expiration: Any');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_CREDIT_CARD_OWNER', 'Card Owner\'s Name:');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_CREDIT_CARD_NUMBER', 'Card Number:');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_CREDIT_CARD_CVV', 'CVV Number (<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . 'More Info' . '</a>)');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_CREDIT_CARD_EXPIRES', 'Expiration Date:');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_ERROR', 'Credit Card Error:');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_JS_CC_CVV', '* The CVV number must be at least ' . CC_CVV_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_EMAIL_ERROR','Warning - Configuration Error: ');
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_EMAIL_WARNING','WARNING: You have enabled the installment payment module but have not properly configured it to send CC information to you by email. As a result, you will not be able to process the CC number for orders placed using this method.  Go to Admin->Modules->Payment->Installment->Edit and set the preferred email address for sending CC information.' . "\n\n\n\n");
  define('MODULE_PAYMENT_INSTALLMENT_TEXT_MIDDLE_DIGITS_MESSAGE', 'Please direct this email to the Accounting department so that it may be filed along with the online order it relates to: ' . "\n\n" . 'Order: %s' . "\n\n" . 'Middle Digits: %s' . "\n\n");
  define('MODULE_PAYMENT_INSTALLMENT_PAYMENT_AMOUNT', 'Your monthly payment: '); 
?>
