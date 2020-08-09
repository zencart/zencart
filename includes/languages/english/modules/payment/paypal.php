<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */

  define('MODULE_PAYMENT_PAYPAL_TEXT_ADMIN_TITLE', 'PayPal Payments Standard');
  define('MODULE_PAYMENT_PAYPAL_TEXT_ADMIN_TITLE_NONUSA', 'PayPal Website Payments Standard');
  define('MODULE_PAYMENT_PAYPAL_TEXT_CATALOG_TITLE', 'PayPal');
  if (IS_ADMIN_FLAG === true) {
  define('MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION', '<strong>PayPal Payments Standard</strong> (Older PayPal service, less reliable than Express Checkout)<br /><a href="https://www.paypal.com" rel="noreferrer noopener" target="_blank">Manage your PayPal account.</a><br /><br /><font color="green">Configuration Instructions:</font><br />1. <a href="http://www.zen-cart.com/partners/paypal-std" rel="noopener" target="_blank">Sign up for your PayPal account - click here.</a><br />2. In your PayPal account, under "Profile",<ul><li>set your <strong>Instant Payment Notification Preferences</strong> URL to:<br /><nobr><pre>'.str_replace('index.php?main_page=index','ipn_main_handler.php',zen_catalog_href_link(FILENAME_DEFAULT, '', 'SSL')) . '</pre></nobr><br />(If another valid URL is already entered, you may leave it alone.)<br /><span class="alert">Be sure that the Checkbox to enable IPN is checked!</span><br /><br /></li><li>in <strong>Website Payments Preferences</strong> set your <strong>Automatic Return URL</strong> to:<br /><nobr><pre>'.zen_catalog_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL',false).'</pre></nobr></li>' . (defined('MODULE_PAYMENT_PAYPALSTD_STATUS') ? '' : '<li>... and click "install" above to enable PayPal Standard support... and "edit" to tell Zen Cart your PayPal settings.</li>') . '</ul><font color="green"><hr /><strong>Requirements:</strong></font><br /><hr />*<strong>PayPal Account</strong> (<a href="https://www.zen-cart.com/partners/paypal-std" rel="noopener" target="_blank">click to setup/configure</a>)<br />*<strong>CURL with SSL</strong> is strongly recommended<br />*<strong>Port 80 (and port 443 if SSL is enabled)</strong> is used for <strong>*bidirectional*</strong> communication with the gateway, so must be open on your host\'s router/firewall.<br />*<strong>Settings</strong> within your PayPal account must be configured as described above.' );
 } else {
    define('MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION', '<strong>PayPal</strong>');
  }
  // to show the PayPal logo as the payment option name, use this:  https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif
  // to show CC icons with PayPal, use this instead:  https://www.paypal.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif
  // You should only use choices listed on this page: https://ppmts.custhelp.com/app/answers/detail/a_id/632
  define('MODULE_PAYMENT_PAYPAL_MARK_BUTTON_IMG', 'https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif');
  define('MODULE_PAYMENT_PAYPAL_MARK_BUTTON_ALT', 'Checkout with PayPal');
  define('MODULE_PAYMENT_PAYPAL_ACCEPTANCE_MARK_TEXT', 'Save time. Check out securely. <br />Pay without sharing your financial information.');

  define('MODULE_PAYMENT_PAYPAL_TEXT_CATALOG_LOGO', '<img src="' . MODULE_PAYMENT_PAYPAL_MARK_BUTTON_IMG . '" alt="' . MODULE_PAYMENT_PAYPAL_MARK_BUTTON_ALT . '" title="' . MODULE_PAYMENT_PAYPAL_MARK_BUTTON_ALT . '" /> &nbsp;' .
                                                    '<span class="smallText">' . MODULE_PAYMENT_PAYPAL_ACCEPTANCE_MARK_TEXT . '</span>');

  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_FIRST_NAME')) define('MODULE_PAYMENT_PAYPAL_ENTRY_FIRST_NAME', 'First Name:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_LAST_NAME')) define('MODULE_PAYMENT_PAYPAL_ENTRY_LAST_NAME', 'Last Name:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_BUSINESS_NAME')) define('MODULE_PAYMENT_PAYPAL_ENTRY_BUSINESS_NAME', 'Business Name:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_NAME')) define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_NAME', 'Address Name:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STREET')) define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STREET', 'Address Street:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_CITY')) define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_CITY', 'Address City:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATE')) define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATE', 'Address State:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_ZIP')) define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_ZIP', 'Address Zip:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_COUNTRY')) define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_COUNTRY', 'Address Country:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_EMAIL_ADDRESS')) define('MODULE_PAYMENT_PAYPAL_ENTRY_EMAIL_ADDRESS', 'Payer Email:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_EBAY_ID')) define('MODULE_PAYMENT_PAYPAL_ENTRY_EBAY_ID', 'Ebay ID:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_ID')) define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_ID', 'Payer ID:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_STATUS')) define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_STATUS', 'Payer Status:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATUS')) define('MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATUS', 'Address Status:');

  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_TYPE')) define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_TYPE', 'Payment Type:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_STATUS')) define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_STATUS', 'Payment Status:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_PENDING_REASON')) define('MODULE_PAYMENT_PAYPAL_ENTRY_PENDING_REASON', 'Pending Reason:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_INVOICE')) define('MODULE_PAYMENT_PAYPAL_ENTRY_INVOICE', 'Invoice:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_DATE')) define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_DATE', 'Payment Date:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_CURRENCY')) define('MODULE_PAYMENT_PAYPAL_ENTRY_CURRENCY', 'Currency:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_GROSS_AMOUNT')) define('MODULE_PAYMENT_PAYPAL_ENTRY_GROSS_AMOUNT', 'Gross Amount:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_FEE')) define('MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_FEE', 'Payment Fee:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_EXCHANGE_RATE')) define('MODULE_PAYMENT_PAYPAL_ENTRY_EXCHANGE_RATE', 'Exchange Rate:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_CART_ITEMS')) define('MODULE_PAYMENT_PAYPAL_ENTRY_CART_ITEMS', 'Cart items:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_TXN_TYPE')) define('MODULE_PAYMENT_PAYPAL_ENTRY_TXN_TYPE', 'Trans. Type:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_TXN_ID')) define('MODULE_PAYMENT_PAYPAL_ENTRY_TXN_ID', 'Trans. ID:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_PARENT_TXN_ID')) define('MODULE_PAYMENT_PAYPAL_ENTRY_PARENT_TXN_ID', 'Parent Trans. ID:');
  if (!defined('MODULE_PAYMENT_PAYPAL_ENTRY_COMMENTS')) define('MODULE_PAYMENT_PAYPAL_ENTRY_COMMENTS', 'System Comments: ');


  define('MODULE_PAYMENT_PAYPAL_PURCHASE_DESCRIPTION_TITLE', 'All the items in your shopping basket (see details in the store and on your store receipt).');
  define('MODULE_PAYMENT_PAYPAL_PURCHASE_DESCRIPTION_ITEMNUM', STORE_NAME . ' Purchase');
  define('MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_ONETIME_CHARGES_PREFIX', 'One-Time Charges related to ');
  define('MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_SURCHARGES_SHORT', 'Surcharges');
  define('MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_SURCHARGES_LONG', 'Handling charges and other applicable fees');
  define('MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_DISCOUNTS_SHORT', 'Discounts');
  define('MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_DISCOUNTS_LONG', 'Credits applied, including discount coupons, gift certificates, etc');
