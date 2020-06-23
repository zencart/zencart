<?php
/**
 * Square payment module language defines
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 21 Modified in v1.5.7 $
 */

define('MODULE_PAYMENT_SQUARE_TEXT_DESCRIPTION', 'Accept credit cards in less than 5 minutes.<br>No monthly fees and no setup fees.<br>PCI Compliant. Customer never leaves your store!<br>Standard rates are 2.9% + $0.30 per transaction.<br>Funds are deposited in your bank account in 1-2 business days.<br><br>
       <a href="https://www.zen-cart.com/partners/square" rel="noopener" target="_blank">Get more information, or Sign up for an account</a><br><br>
       <a href="https://squareup.com/login" rel="noopener" target="_blank">Log In To Your Square Account</a>');

define('MODULE_PAYMENT_SQUARE_TEXT_ADMIN_TITLE', 'Square'); // Payment option title as displayed in the admin
define('MODULE_PAYMENT_SQUARE_TEXT_CATALOG_TITLE', 'Credit Card');  // Payment option title as displayed to the customer

define('MODULE_PAYMENT_SQUARE_TEXT_NOTICES_TO_CUSTOMER', '');  // Payment option sub-text displayed to the customer, above the cc input fields

define('MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_POSTCODE', 'Postal Code:');
define('MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_NUMBER', 'Card Number:');
define('MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_EXPIRES', 'Expiry Date:');
define('MODULE_PAYMENT_SQUARE_TEXT_CVV', 'CVV Number:');
define('MODULE_PAYMENT_SQUARE_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');

define('MODULE_PAYMENT_SQUARE_TEXT_ERROR', '(SQ-ERR) Your transaction could not be completed because of an error: ');
define('MODULE_PAYMENT_SQUARE_TEXT_MISCONFIGURATION', 'Your transaction could not be completed due to a misconfiguration in our store. Please report this error to the Store Owner: SQ-MISCONF');
define('MODULE_PAYMENT_SQUARE_TEXT_COMM_ERROR', 'Unable to process payment due to a communications error. You may try again or contact us for assistance.');
define('MODULE_PAYMENT_SQUARE_ERROR_INVALID_CARD_DATA',
    'We could not initiate your transaction because of a problem with the card data you entered. Please correct the card data, or report this error to the Store Owner: SQ-NONCE-FAILURE');
define('MODULE_PAYMENT_SQUARE_ERROR_DECLINED', 'Sorry, your payment could not be authorized. Please select an alternate method of payment.');

if (IS_ADMIN_FLAG === true) {
    define('MODULE_PAYMENT_SQUARE_TEXT_NEED_ACCESS_TOKEN',
        '<span class="text-danger"><strong>ALERT: Access Token not set:</strong></span> <br>
    1. Make sure the OAuth Redirect URL in your Square Account "app" is set to <u><nobr><pre>' . str_replace(array('index.php?main_page=index', 'http://'), array('square_handler.php', 'https://'), zen_catalog_href_link(FILENAME_DEFAULT, '', 'SSL')) . '</pre></nobr></u><br>
    2. And then <a href="%s" rel="noopener" target="_blank" class="onClickStartCheck"><button class="btn btn-xs btn-success">Click here to login and Authorize your account</button></a>');
}


define('MODULE_PAYMENT_SQUARE_ENTRY_TRANSACTION_SUMMARY', '<strong>Transaction Summary</strong>');
define('MODULE_PAYMENT_SQUARE_ENTRY_TRANSACTION_ACTIONS', '<strong>Actions</strong>');
define('MODULE_PAYMENT_SQUARE_TEXT_UPDATE_FAILED', 'Sorry, the attempted transaction update failed unexpectedly. See logs for details.');

define('MODULE_PAYMENT_SQUARE_ENTRY_REFUND_TITLE', '<strong>Refund Transaction</strong>');
define('MODULE_PAYMENT_SQUARE_ENTRY_REFUND', 'You may refund money to the customer here:');
define('MODULE_PAYMENT_SQUARE_TEXT_REFUND_CONFIRM_CHECK', 'Check this box to confirm your intent: ');
define('MODULE_PAYMENT_SQUARE_ENTRY_REFUND_AMOUNT_TEXT', 'Enter the amount you wish to refund');
define('MODULE_PAYMENT_SQUARE_ENTRY_REFUND_TEXT_COMMENTS', 'Notes (will show on Order History):');
define('MODULE_PAYMENT_SQUARE_ENTRY_REFUND_DEFAULT_MESSAGE', 'Refund Issued');
define('MODULE_PAYMENT_SQUARE_ENTRY_REFUND_SUFFIX', 'You may refund an order within 120 days, up to the original amount tendered. You must supply the original transaction ID and tender ID<br>See the Square site for more <a href="https://squareup.com/help/us/en/article/5060" rel="noopener" target="_blank">information on Square refunds</a>.');
define('MODULE_PAYMENT_SQUARE_ENTRY_REFUND_BUTTON_TEXT', 'Do Refund');
define('MODULE_PAYMENT_SQUARE_TEXT_REFUND_CONFIRM_ERROR', 'Error: You requested to do a refund but did not check the Confirmation box.');
define('MODULE_PAYMENT_SQUARE_TEXT_INVALID_REFUND_AMOUNT', 'Error: You requested a refund but entered an invalid amount.');
define('MODULE_PAYMENT_SQUARE_TEXT_REFUND_INITIATED', 'Refunded ');

define('MODULE_PAYMENT_SQUARE_ENTRY_CAPTURE_TITLE', '<strong>Capture Transaction</strong>');
define('MODULE_PAYMENT_SQUARE_ENTRY_CAPTURE', 'You may capture previously-authorized funds here:');
define('MODULE_PAYMENT_SQUARE_TEXT_CAPTURE_CONFIRM_CHECK', 'Check this box to confirm your intent: ');
define('MODULE_PAYMENT_SQUARE_ENTRY_CAPTURE_TEXT_COMMENTS', 'Notes (will show on Order History):');
define('MODULE_PAYMENT_SQUARE_ENTRY_CAPTURE_DEFAULT_MESSAGE', '');
define('MODULE_PAYMENT_SQUARE_ENTRY_CAPTURE_SUFFIX', 'Captures must be performed within 6 days of the original authorization. You may only capture an order ONCE.');
define('MODULE_PAYMENT_SQUARE_TEXT_CAPTURE_CONFIRM_ERROR', 'Error: You requested to do a capture but did not check the Confirmation box.');
define('MODULE_PAYMENT_SQUARE_ENTRY_CAPTURE_BUTTON_TEXT', 'Do Capture');
define('MODULE_PAYMENT_SQUARE_TEXT_TRANS_ID_REQUIRED_ERROR', 'Error: You need to specify a Transaction ID.');
define('MODULE_PAYMENT_SQUARE_TEXT_CAPT_INITIATED', 'Funds Capture initiated. Transaction ID: %s');

define('MODULE_PAYMENT_SQUARE_ENTRY_VOID_TITLE', '<strong>Voiding Transaction</strong>');
define('MODULE_PAYMENT_SQUARE_ENTRY_VOID', 'You may void an authorization which has not been captured.');
define('MODULE_PAYMENT_SQUARE_TEXT_VOID_CONFIRM_CHECK', 'Check this box to confirm your intent:');
define('MODULE_PAYMENT_SQUARE_ENTRY_VOID_TEXT_COMMENTS', 'Notes (will show on Order History):');
define('MODULE_PAYMENT_SQUARE_ENTRY_VOID_DEFAULT_MESSAGE', 'Transaction Cancelled');
define('MODULE_PAYMENT_SQUARE_ENTRY_VOID_SUFFIX', '');
define('MODULE_PAYMENT_SQUARE_ENTRY_VOID_BUTTON_TEXT', 'Do Void');
define('MODULE_PAYMENT_SQUARE_TEXT_VOID_CONFIRM_ERROR', 'Error: You requested a Void but did not check the Confirmation box.');
define('MODULE_PAYMENT_SQUARE_TEXT_VOID_INITIATED', 'Void Initiated. Transaction ID: %s');
