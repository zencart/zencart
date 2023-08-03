<?php
$define = [
    'MODULE_PAYMENT_PAYPAL_TEXT_ADMIN_TITLE' => 'PayPal Payments Standard',
    'MODULE_PAYMENT_PAYPAL_TEXT_ADMIN_TITLE_NONUSA' => 'PayPal Website Payments Standard',
    'MODULE_PAYMENT_PAYPAL_TEXT_CATALOG_TITLE' => 'PayPal',
    'MODULE_PAYMENT_PAYPAL_MARK_BUTTON_IMG' => 'https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif',
    'MODULE_PAYMENT_PAYPAL_MARK_BUTTON_ALT' => 'Checkout with PayPal',
    'MODULE_PAYMENT_PAYPAL_ACCEPTANCE_MARK_TEXT' => 'Save time. Check out securely. <br>Pay without sharing your financial information.',
    'MODULE_PAYMENT_PAYPAL_PURCHASE_DESCRIPTION_TITLE' => 'All the items in your shopping basket (see details in the store and on your store receipt).',
    'MODULE_PAYMENT_PAYPAL_PURCHASE_DESCRIPTION_ITEMNUM' => STORE_NAME . ' Purchase',
    'MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_ONETIME_CHARGES_PREFIX' => 'One-Time Charges related to ',
    'MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_SURCHARGES_SHORT' => 'Surcharges',
    'MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_SURCHARGES_LONG' => 'Handling charges and other applicable fees',
    'MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_DISCOUNTS_SHORT' => 'Discounts',
    'MODULES_PAYMENT_PAYPALSTD_LINEITEM_TEXT_DISCOUNTS_LONG' => 'Credits applied, including discount coupons, gift certificates, etc',
    'MODULES_PAYMENT_PAYPALSTD_NOT_RECOMMENDED' => 'Please note this module is no longer recommended.  See <a href="https://docs.zen-cart.com/user/payment/paypal_standard/" target="_blank" rel="noreferrer noopener">this page</a> for an explanation.',
    'MODULE_PAYMENT_PAYPAL_ENTRY_FIRST_NAME' => 'First Name:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_LAST_NAME' => 'Last Name:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_BUSINESS_NAME' => 'Business Name:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_NAME' => 'Address Name:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STREET' => 'Address Street:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_CITY' => 'Address City:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATE' => 'Address State:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_ZIP' => 'Address Zip:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_COUNTRY' => 'Address Country:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_EMAIL_ADDRESS' => 'Payer Email:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_EBAY_ID' => 'Ebay ID:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_ID' => 'Payer ID:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_STATUS' => 'Payer Status:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATUS' => 'Address Status:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_TYPE' => 'Payment Type:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_STATUS' => 'Payment Status:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_PENDING_REASON' => 'Pending Reason:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_INVOICE' => 'Invoice:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_DATE' => 'Payment Date:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_CURRENCY' => 'Currency:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_GROSS_AMOUNT' => 'Gross Amount:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_FEE' => 'Payment Fee:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_EXCHANGE_RATE' => 'Exchange Rate:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_CART_ITEMS' => 'Cart items:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_TXN_TYPE' => 'Trans. Type:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_TXN_ID' => 'Trans. ID:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_PARENT_TXN_ID' => 'Parent Trans. ID:',
    'MODULE_PAYMENT_PAYPAL_ENTRY_COMMENTS' => 'System Comments: ',
];

if (IS_ADMIN_FLAG === true) {
    $define['MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION'] = '<strong>PayPal Payments Standard</strong> (Older PayPal service, less reliable than Express Checkout)<br><a href="https://www.paypal.com" rel="noreferrer noopener" target="_blank">Manage your PayPal account.</a><br><br><b>Configuration Instructions:</b><br>1. <a href="https://www.zen-cart.com/partners/paypal-std" rel="noopener" target="_blank">Sign up for your PayPal account - click here.</a><br>2. In your PayPal account, under "Profile",<ul><li>set your <strong>Instant Payment Notification Preferences</strong> URL to:<br><pre>' . str_replace('index.php?main_page=index', 'ipn_main_handler.php', zen_catalog_href_link(FILENAME_DEFAULT)) . '</pre><br>(If another valid URL is already entered, you may leave it alone.)<br><span class="alert">Be sure that the Checkbox to enable IPN is checked!</span><br><br></li><li>in <strong>Website Payments Preferences</strong> set your <strong>Automatic Return URL</strong> to:<br><pre>' . zen_catalog_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', false) . '</pre></li>' . (defined('MODULE_PAYMENT_PAYPALSTD_STATUS') ? '' : '<li>... and click "install" above to enable PayPal Standard support... and "edit" to tell Zen Cart your PayPal settings.</li>') . '</ul><hr><strong>Requirements:</strong><br><br>*<strong>PayPal Account</strong> (<a href="https://www.zen-cart.com/partners/paypal-std" rel="noopener" target="_blank">click to setup/configure</a>)<br>*<strong>CURL with SSL</strong> is strongly recommended<br>*<strong>Port 80 (and port 443 if SSL is enabled)</strong> is used for <strong>*bidirectional*</strong> communication with the gateway, so must be open on your host\'s router/firewall.<br>*<strong>Settings</strong> within your PayPal account must be configured as described above.';
} else {
    $define['MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION'] = '<strong>PayPal</strong>';
}
$define['MODULE_PAYMENT_PAYPAL_TEXT_CATALOG_LOGO'] = '<img src="' . $define['MODULE_PAYMENT_PAYPAL_MARK_BUTTON_IMG'] . '" alt="' . $define['MODULE_PAYMENT_PAYPAL_MARK_BUTTON_ALT'] . '" title="' . $define['MODULE_PAYMENT_PAYPAL_MARK_BUTTON_ALT'] . '"> &nbsp;' .
        '<span class="smallText">' . $define['MODULE_PAYMENT_PAYPAL_ACCEPTANCE_MARK_TEXT'] . '</span>';

return $define;
