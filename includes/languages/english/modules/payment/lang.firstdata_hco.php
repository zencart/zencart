<?php
$define = [
    'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_ADMIN_TITLE' => 'First Data Hosted Checkout Payment Pages',
    'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_CATALOG_TITLE' => 'Credit Card',
    'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_DECLINED_MESSAGE' => 'The transaction could not be completed. Please try another card or contact your bank for more info.  ',
    'MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_ERROR_MESSAGE' => 'There has been an error processing the transaction. Please try again.  ',
];

if (IS_ADMIN_FLAG === true) {
    if (defined('MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS') && MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_STATUS == 'True') {
        $define['MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_DESCRIPTION'] = '<a rel="noreferrer noopener" target="_blank" href="https://' . (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE == 'Sandbox' ? 'demo.' : '') . 'globalgatewaye4.firstdata.com">First Data GGe4 Merchant Login</a>' .
            (MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TESTMODE != 'Production' ? '<br><br>For TEST CARDS refer to <a href="https://support.payeezy.com/hc/en-us/articles/204504235-Using-test-credit-card-numbers" rel="noreferrer noopener" target="_blank">Using Test Credit Cards</a>' : '') .
            '<br><br><strong>SETTINGS</strong><br>Your "Receipt Link URL" setting in your First Data Payment Page configuration needs to point to <u>' . zen_catalog_href_link('checkout_process') . '</u><br>' .
            'Then obtain the Payment Page ID, Transaction Key and Response Key from First Data and enter them here. They can be found by logging into your First Data account, choosing Payment Pages, and clicking on the desired Page ID and navigating to the Security section.';
    } else {
        $define['MODULE_PAYMENT_FIRSTDATA_PAYMENTPAGES_TEXT_DESCRIPTION'] = 'Hosted Checkout Payment Pages are available to all First Data, Global Gateway e4, and Linkpont merchants.<br><br>
             Your First Data account representative can assist with any account changes necessary to enable Hosted Checkout (HCO) in your account.<br><br>
             <a rel="noreferrer noopener" target="_blank" href="https://www.zen-cart.com/partners/firstdatahosted/">Click Here to Sign Up for a First Data Hosted Checkout Account</a><br><br>
             <a rel="noreferrer noopener" target="_blank" href="https://globalgatewaye4.firstdata.com/">Click to Login to the First Data GGe4 Merchant Area</a>';
    }
}

return $define;
