<?php
/**
 * sagepay form
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: Wilt New in v1.5.5 $
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_VNO', '1.0 Alpha');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_ADMIN_TEXT_TITLE', 'Sagepay Form');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_ADMIN_TEXT_DESCRIPTION', '<fieldset style="background: #eee; margin-bottom: 1.5em"><legend style="font-size: 1.2em; font-weight: bold">Test Cards Infomation</legend><br />Note : Use these card details on the simulater or test site only!!<br /><br />VISA 4929000000006<br />MASTERCARD 5404000000000001<br />DELTA 4462000000000003<br />SOLO 6334900000000005 Issue 01<br />DOMESTIC MAESTRO 5641820000000005 Issue 01<br />AMEX 374200000000004<br />ELECTRON 4917300000000008<br />JCB 3569990000000009<br />DINERS 36000000000008<br /><br />You will need to supply the following values for<br />CV2, Billing Address and Billing Post Code Numbers.<br /><br />CV2 123<br />Billing Address Numbers 88<br />Billing Post Code Numbers 412<br />These are the only values which will return as Matched.<br /><br />You will also need to enter the<br />3D Secure password as " password " (it is case sensitive)<br />so as the 3D Secure authentication returns<br />Fully Authenticated.');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_CATALOG_TEXT_TITLE', 'Credit/Debit Card (Secured by Sage Pay)');


define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_ERROR', 'Debit/Credit Card Error!');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_NOTAUTHED_MESSAGE', 'Your card could not be authorised! Please try again, try another card or <a href="index.php?main_page=contact_us">contact the administrator</a> for further assistance.<p class="ExtraErrorInfo">(%s)</p>');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_MALFORMED_MESSAGE', 'Your card could not be reconised! Please try again, try another card or <a href="index.php?main_page=contact_us">contact the administrator</a> for further assistance.<p class="ExtraErrorInfo">(%s)</p>');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_INVALID_MESSAGE', 'Your card details could not be reconised! Please try again, try another card or <a href="index.php?main_page=contact_us">contact the administrator</a> for further assistance.<p class="ExtraErrorInfo">(%s)</p>');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_ABORT_MESSAGE', 'The Transaction could not be completed because the user clicked the CANCEL button on the payment pages, went inactive for 15 minutes or longer or there was a problem with the users internet connection to our servers.Please try again or <a href="index.php?main_page=contact_us">contact the administrator</a> for further assistance.<p class="ExtraErrorInfo">(%s)</p>');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_REJECTED_MESSAGE', 'Unable to continue! A problem has occurred with our systems. Please <strong><a href="index.php?main_page=contact_us">contact the administrator</a> for further assistance.<p class="ExtraErrorInfo">(%s)</p>');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_ERROR_MESSAGE', 'Unable to continue! A problem has occurred with our systems. Please <strong><a href="index.php?main_page=contact_us">contact the administrator</a></strong> for assistance.<p class="ExtraErrorInfo">(%s)</p>');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_DECLINED_MESSAGE', 'Your card could not be authorised! Please try again or <a href="index.php?main_page=contact_us">contact the administrator</a> for further assistance.<p class="ExtraErrorInfo">(%s)</p>');

// Admin text definitions
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_ADMIN_TITLE', 'SagePay Form v%s');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_DESCRIPTION_BASE', '<fieldset style="background: #F7F6F0; margin-bottom: 1.5em"><legend style="font-size: 1.2em; font-weight: bold">Test Card Details</legend>Visa#: 4929000000006<br />MasterCard#: 5404000000000001<br />Visa Debit#: 4462000000000003<br />Solo#: 6334900000000005 - Issue #: 1<br />UK Maestro#: 5641820000000005 - Issue #: 01<br />International Maestro#: 3000000000000004<br />Visa Electron (UKE)#: 4917300000000008<br />AMEX#: 374200000000004<br />JCB#: 3569990000000009<br />Diners Club#: 36000000000008<br />Laser#: 6304990000000000044<p>Any future date can be used for the expiration date.</p><p>The only CVV Code which will return a match is 123.</p><p>The AVS Verification will only return a match if the following Billing Address details are used: <br /><br />Billing Address: 88<br />Billing Postcode: 412</p><p>These are the default billing address details which will be submitted by the module in test mode if the &ldquo;Use Test Billing Address&rdquo; checkbox is ticked.</fieldset><fieldset style="background: #F7F6F0; margin-bottom: 1.5em"><legend style="font-size: 1.2em; font-weight: bold">Admin Links</legend><a target="_blank" href="https://live.sagepay.com/mysagepay">My Sage Pay Live Account Admin</a><br /><br /><a target="_blank" href="https://test.sagepay.com/mysagepay">My Sage Pay Test Account Admin</a><br /><br /><a target="_blank" href="https://test.sagepay.com/simulator">Simulator Admin</a></fieldset>');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_SAGEPAY_TRANSACTION_ID', 'SagePay Transaction ID:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_VENDOR_TRANSACTION_CODE', 'Vendor Transaction Code (Unique ID):');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TEXT_STATUS', 'Status:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_STATUS_DETAIL', 'Status Message:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_TX_AUTH_NO', 'SagePay Authorisation Code:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_AVSCV2', 'AVS and CV2 Response:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_ADDRESS_RESULT', 'Specific Address Results:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_POSTCODE_RESULT', 'Specific Postcode Results:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_CV2_RESULT', 'Specific CV2 Results:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_3D_SECURE_STATUS', '3D Secure Status:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_CAVV_RESULT', 'Specific CAVV Result:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_CARD_TYPE', 'Card Type:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_LAST_4_CARD_DIGITS', 'Last 4 Digits of Card:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_PAYPAL_ADDRESS_STATUS', 'PayPal Address Status:');
define('MODULE_PAYMENT_SAGEPAY_ZC_FORM_PAYPAL_PAYER_STATUS', 'PayPal Payer Status:');

define('TEXT_TITLE_MCRYPT_ERROR', ' (mcrypt problem)');
define('TEXT_DESCRIPTION_MCRYPT_ERROR', ' The PHP mcrypt extension is not available. Sagepay Form will be disabled at checkout.');
