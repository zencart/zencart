<?php
/**
 * First Data Hosted Checkout Payment Pages Module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 *
 * To create a USD sandbox account for testing, see: https://support.payeezy.com/hc/en-us/articles/203730579-Payeezy-Gateway-Demo-Accounts
 * And find test credit card numbers (for sandbox only) at https://support.payeezy.com/hc/en-us/articles/204504235-Using-test-credit-card-numbers
 * To test failures, see https://support.payeezy.com/hc/en-us/articles/204504175-How-to-generate-unsuccessful-transactions-during-testing-
 */

  define('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_ADMIN_TITLE', 'First Data Hosted Checkout Payment Pages');
  define('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_CATALOG_TITLE', 'Credit Card');  // Payment option title as displayed to the customer

  if (IS_ADMIN_FLAG === true) {
    if (defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS') && MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS == 'True') {
      define('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_DESCRIPTION', '<a rel="noreferrer noopener" target="_blank" href="https://' . (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE == 'Sandbox' ? 'demo.' : '') . 'globalgatewaye4.firstdata.com">First Data GGe4 Merchant Login</a>' .
        (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE != 'Production' ? '<br /><br />For TEST CARDS refer to <a href="https://support.payeezy.com/hc/en-us/articles/204504235-Using-test-credit-card-numbers" rel="noreferrer noopener" target="_blank">Using Test Credit Cards</a>' : '') .
        '<br /><br /><strong>SETTINGS</strong><br />Your "Receipt Link URL" setting in your First Data Payment Page configuration needs to point to <u>' . zen_catalog_href_link('checkout_process', '', 'SSL') . '</u><br>' .
        'Then obtain the Payment Page ID, Transaction Key and Response Key from First Data and enter them here. They can be found by logging into your First Data account, choosing Payment Pages, and clicking on the desired Page ID and navigating to the Security section.'
      );
    } else {
      define('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_DESCRIPTION', 'Hosted Checkout Payment Pages are available to all First Data, Global Gateway e4, and Linkpont merchants.<br><br>
             Your First Data account representative can assist with any account changes necessary to enable Hosted Checkout (HCO) in your account.<br><br>
             <a rel="noreferrer noopener" target="_blank" href="https://www.zen-cart.com/partners/firstdatahosted/">Click Here to Sign Up for a First Data Hosted Checkout Account</a><br><br>
             <a rel="noreferrer noopener" target="_blank" href="https://globalgatewaye4.firstdata.com/">Click to Login to the First Data GGe4 Merchant Area</a>');
    }
  }
  define('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_DECLINED_MESSAGE', 'The transaction could not be completed. Please try another card or contact your bank for more info.  ');
  define('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_ERROR_MESSAGE', 'There has been an error processing the transaction. Please try again.  ');
