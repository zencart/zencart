<?php
/**
 * PayEezy payment module language defines
 *
 * @package payeezy
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson   New in v1.5.5 $
 */

define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_DESCRIPTION', 'Payeezy Gateway module.<br>Process PCI Compliant payments without making the customer leave your store.<br><a href="https://www.zen-cart.com/partners/firstdatapayeezy" target="_blank">Sign Up</a><br><a href="https://globalgatewaye4.firstdata.com" target="_blank">Log In To Account</a>');

define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_ADMIN_TITLE', 'Payeezy JS'); // Payment option title as displayed in the admin
define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CATALOG_TITLE', 'Credit Card');  // Payment option title as displayed to the customer
define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_OWNER', 'Card Owner:');
define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_NUMBER', 'Card Number:');
define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_EXPIRES', 'Expiry Date:');
define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CVV', 'CVV Number:');
define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');

define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_ERROR', "Your transaction could not be completed because of an error: ");
define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_MISCONFIGURATION', "Your transaction could not be completed due to a misconfiguration in our store. Please report this error to the Store Owner: ");
define('MODULE_PAYMENT_PAYEEZYJSZC_TEXT_COMM_ERROR', 'Unable to process payment due to a communications error. You may try again or contact us for assistance.');
define('MODULE_PAYMENT_PAYEEZYJSZC_ERROR_MISSING_FDTOKEN', "We could not initiate your transaction because of a system scripting error. Please report this error to the Store Owner: PAYEEZY-FDTOKEN-MISSING");

/* Test Cards for use in Sandbox only. Sandbox available at https://developer.payeezy.com:
  Expiry Date: Any future date.
  Cvv:Any 3 digit number for Visa, Mastercard, Diners Club, JCB & Discover and 4 digit number for American Express
  Visa  4012 0000 3333 0026
        4005 5192 0000 0004
  MasterCard  5424 1802 7979 1732
              5526 3990 0064 8568
              5405 0101 0000 0016
  American Express  3739 5319 2351 004
                    3411 1159 7241 002
  Discover  6510 0000 0000 1248
  JCB 3566 0020 2014 0006
  Diners Club 3643 8999 9600 16
*/
