<?php
$define = [
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_ADMIN_TITLE' => 'Authorize.net (SIM)',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_CATALOG_TITLE' => 'Credit Card',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_TYPE' => 'Type:',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER' => 'Card Owner:',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER' => 'Card Number:',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES' => 'Expiry Date:',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_CVV' => 'CVV Number:',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_POPUP_CVV_LINK' => 'What\'s this?',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_OWNER' => '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_NUMBER' => '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_CVV' => '* The 3 or 4 digit CVV number must be entered from the back of the credit card.\n',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR_MESSAGE' => 'There has been an error processing your credit card. Please try again.',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_DECLINED_MESSAGE' => 'Your credit card was declined. Please try another card or contact your bank for more info.',
    'MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR' => 'Credit Card Error!',
];

if (defined('MODULE_PAYMENT_AUTHORIZENET_STATUS') && MODULE_PAYMENT_AUTHORIZENET_STATUS == 'True') {
    $define['MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION'] = '<a rel="noreferrer noopener" target="_blank" href="https://account.authorize.net/">Authorize.net Merchant Login</a>' . (MODULE_PAYMENT_AUTHORIZENET_TESTMODE != 'Production' ? '<br><br>Testing Info:<br><b>Automatic Approval Credit Card Numbers:</b><br>Visa#: 4007000000027<br>MC#: 5424000000000015<br>Discover#: 6011000000000012<br>AMEX#: 370000000000002<br><br><b>Note:</b> These credit card numbers will return a decline in live mode, and an approval in test mode.  Any future date can be used for the expiration date and any 3 or 4 (AMEX) digit number can be used for the CVV Code.<br><br><b>Automatic Decline Credit Card Number:</b><br><br>Card #: 4222222222222<br><br>This card number can be used to receive decline notices for testing purposes.' : '') . '<br><br><strong>SETTINGS</strong><br>Your "response" and "receipt" and "relay" URL settings in your Authorize.net Merchant Profile can be left BLANK, or if necessary you can set the "relay URL" to point to https://your_domain.com/foldername/index.php?main_page=checkout_process<br><br>If you are having problems with this, see <a href="https://docs.zen-cart.com/user/payment/authorizenet_sim/" rel="noreferrer noopener" target="_blank">the SIM Setup FAQ article</a> for detailed setup instructions.';
} else {
    $define['MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION'] = '<a rel="noreferrer noopener" target="_blank" href="https://reseller.authorize.net/application?resellerId=10023">Click Here to Sign Up for an Account</a><br><br><a rel="noreferrer noopener" target="_blank" href="https://account.authorize.net/">Click to Login to the Authorize.net Merchant Area</a><br><br><strong>Requirements:</strong><br><hr>*<strong>Authorize.net Account</strong> (see link above to signup)<br>*<strong>Authorize.net username and transaction key</strong> available from your Merchant Area<br><br>See <a href="https://docs.zen-cart.com/user/payment/authorizenet_sim/" rel="noreferrer noopener" target="_blank">the SIM Setup FAQ article</a> for detailed setup instructions.';
}
return $define;
