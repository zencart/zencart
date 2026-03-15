<?php
/**
 * Language definitions for the paypalr (PayPal Restful Api) payment module.
 *
 * Last updated: v1.3.1
 */
$define = [
    'MODULE_PAYMENT_PAYPALR_TEXT_TITLE' => 'PayPal Checkout',
        'MODULE_PAYMENT_PAYPALR_SUBTITLE' => '(Use either your <b>PayPal Wallet</b> or a <b>Credit Card</b>)',
    'MODULE_PAYMENT_PAYPALR_TEXT_TITLE_ADMIN' => 'PayPal Checkout (RESTful)',
    'MODULE_PAYMENT_PAYPALR_TEXT_DESCRIPTION' => '<strong>PayPal</strong>',
    'MODULE_PAYMENT_PAYPALR_TEXT_TYPE' => 'PayPal Checkout',

    // -----
    // Configuration-related errors displayed during the payment module's admin configuration.
    //
    'MODULE_PAYMENT_PAYPALR_ERROR_NO_CURL' => 'CURL not installed, cannot use.',
    'MODULE_PAYMENT_PAYPALR_ERROR_CREDS_NEEDED' => 'The <var>paypalr</var> payment module cannot be enabled until you supply valid credentials for your <b>%s</b> site.',
    'MODULE_PAYMENT_PAYPALR_ERROR_INVALID_CREDS' => 'The <b>%s</b> credentials for the <var>paypalr</var> payment module are invalid.',
    'MODULE_PAYMENT_PAYPALR_AUTO_DISABLED' => ' The payment module has been automatically disabled.',

    // -----
    // Storefront messages.
    //
    'MODULE_PAYMENT_PALPALR_PAYING_WITH_PAYPAL' => 'Paying via PayPal Wallet',     //- Used by the confirmation method, when paying via PayPal Checkout (paypal)
    'MODULE_PAYMENT_PAYPALR_TEXT_NOTIFICATION_MISSING' => 'We are unable to process your %s payment at this time.  Please contact us for assistance.',  //- %s filled in with MODULE_PAYMENT_PAYPALR_TEXT_TITLE
    'MODULE_PAYMENT_PAYPALR_TEXT_GENERAL_ERROR' => 'We are unable to process your %s payment at this time.  Please contact us for assistance.',      //- %s filled in with MODULE_PAYMENT_PAYPALR_TEXT_TITLE
    'MODULE_PAYMENT_PAYPALR_TEXT_STATUS_MISMATCH' => 'We were unable to process your payment-request.',
    'MODULE_PAYMENT_PAYPALR_TEXT_PLEASE_NOTE' => 'Please Note:',
    'MODULE_PAYMENT_PAYPALR_UNSUPPORTED_BILLING_COUNTRY' => 'Your billing address\' country not supported by PayPal; credit-card payments cannot be made.',
    'MODULE_PAYMENT_PAYPALR_UNSUPPORTED_SHIPPING_COUNTRY' => 'Your shipping address\' country not supported by PayPal; this payment method cannot be used.',

    // -----
    // Storefront text used to compose an 'after_process' customer-visible note in the
    // order's status-history.  Added for v1.0.5.
    //
    'MODULE_PAYMENT_PAYPALR_TRANSACTION_ID' => 'Transaction ID: ',  //- Should end with a space
    'MODULE_PAYMENT_PAYPALR_TRANSACTION_TYPE' => 'Payment Type: PayPal Checkout (%s)',  //- %s filled in with either 'paypal' or 'card'
    'MODULE_PAYMENT_PAYPALR_TRANSACTION_PAYMENT_STATUS' => 'Payment Status: ',  //- Should end with a space
    'MODULE_PAYMENT_PAYPALR_TRANSACTION_AMOUNT' => 'Amount: ',  //- Should end with a space
    // Added for v1.2.0:
    'MODULE_PAYMENT_PAYPALR_BUYER_EMAIL' => 'Buyer Email: ',  //- Should end with a space
    'MODULE_PAYMENT_PAYPALR_FUNDING_SOURCE' => 'Funding Source: ',  //- Should end with a space

    // -----
    // Used by the payment module's javascript_validation method.
    //
    'MODULE_PAYMENT_PAYPALR_TEXT_JS_CC_OWNER' => '* The cardholder\'s name must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n',
    'MODULE_PAYMENT_PAYPALR_TEXT_JS_CC_NUMBER' => '* The Credit Card Number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n',
    'MODULE_PAYMENT_PAYPALR_TEXT_JS_CC_CVV' => '* The 3 or 4 digit CVV Number must be entered from the back of the credit card (or front for American Express).\n',

    // -----
    // Constants used when processing credit-cards
    //
    'MODULE_PAYMENT_PAYPALR_CC_OWNER' => 'Cardholder Name:',
    'MODULE_PAYMENT_PAYPALR_CC_TYPE' => 'Credit Card Type:',
    'MODULE_PAYMENT_PAYPALR_CC_NUMBER' => 'Credit Card Number:',
    'MODULE_PAYMENT_PAYPALR_CC_EXPIRES' => 'Credit Card Expiry Date:',
    'MODULE_PAYMENT_PAYPALR_CC_CVV' => 'CVV Number:',

    'MODULE_PAYMENT_PAYPALR_TEXT_CVV_LENGTH' => 'The <em>CVV Number</em> for your %1$s card ending in <var>%2$s</var> must be %3$u digits in length.',  //- %1$s is the card type, , %2$s is the last-r, %3$u is the CVV length
    'MODULE_PAYMENT_PAYPALR_TEXT_BAD_CARD' => 'We apologize for the inconvenience, but the credit card type you entered is not one that we accept. Please use a different credit card.',

    'MODULE_PAYMENT_PAYPALR_TEXT_CC_ERROR' => 'An error occurred when we tried to process your credit card.',
    'MODULE_PAYMENT_PAYPALR_TEXT_CARD_DECLINED' => 'The card ending with <var>%s</var> was declined.',     //- %s is the last-4 of the card-number.
    'MODULE_PAYMENT_PAYPALR_TEXT_DECLINED_REASON_UNKNOWN' => 'If you continue to receive this message, please contact us and supply reason-code \'%s\'.', //- %s is ['processor_response']['response_code']

    'MODULE_PAYMENT_PAYPALR_TEXT_TRY_AGAIN' => 'Please try again, select an alternate payment method or contact us for assistance.',

    'MODULE_PAYMENT_PAYPALR_CARD_PROCESSING' => 'By paying with your card, you acknowledge that your data will be processed by PayPal subject to the %s available at PayPal.com.',  //- %s is filled in with a link
    'MODULE_PAYMENT_PAYPALR_PAYPAL_PRIVACY_STMT' => 'PayPal Privacy Statement',
    'MODULE_PAYMENT_PAYPALR_PAYPAL_PRIVACY_LINK' => 'https://www.paypal.com/us/legalhub/privacy-full',

    // -----
    // Store owner/admin alert-email messages.
    //
    'MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT' => 'ALERT: PayPal Checkout (%s)',    //- %s is an additional error descriptor, see below
        'MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_CONFIGURATION' => 'Configuration',
        'MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_ORDER_ATTN' => 'Order Requires Attention',
        'MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_UNKNOWN_DENIAL' => 'Unknown Denial Reason',
        'MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_LOST_STOLEN_CARD' => 'Lost/Stolen/Fraudulent Card',
        'MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_TOTAL_MISMATCH' => 'Calculation Mismatch',
        'MODULE_PAYMENT_PAYPALR_ALERT_SUBJECT_CONFIRMATION_ERROR' => 'Confirm Payment Choice',

    'MODULE_PAYMENT_PAYPALR_ALERT_ORDER_CREATION' => 'The status for order #%1$u was forced to &quot;Pending&quot; due to a PayPal response status of \'%2$s\'.',
    'MODULE_PAYMENT_PAYPALR_ALERT_MISSING_OBSERVER' => 'The payment module\'s observer (auto.paypalrestful.php) was not loaded; the payment module has been disabled.',
    'MODULE_PAYMENT_PAYPALR_ALERT_MISSING_NOTIFICATIONS' => 'The required notifications in the order_total.php class were not applied; the payment module cannot place orders.',
    'MODULE_PAYMENT_PAYPALR_ALERT_MISSING_ROOT_FILES' => 'Missing required root-directory files (%s); check file-system permissions.',
    'MODULE_PAYMENT_PAYPALR_ALERT_ORDER_CREATE' => 'An error was returned by PayPal when attempting to initiate an order. As a courtesy, only the error \'code\' was shown to your customer.  The details of the error are shown below.' . "\n\n",
    'MODULE_PAYMENT_PAYPALR_ALERT_TOTAL_MISMATCH' => 'A discrepancy was found between an order\'s overall value and its breakdown.  The order is being submitted to PayPal without items and cost breakdown included:',
    'MODULE_PAYMENT_PAYPALR_ALERT_CONFIRMATION_ERROR' => 'An unprocessable return was received from PayPal when attempting to confirm a customer\'s payment choice from their PayPal Wallet.',
    'MODULE_PAYMENT_PAYPALR_ALERT_EXTERNAL_TXNS' => 'Check the status of order #%u.  PayPal transactions were added outside of the payment-module\'s processing.',

    // -----
    // Alert messages for unknown "DECLINED" reasons and lost/stolen/fraudulent cards.
    // -----

    // -----
    // %1$s: ['processor_response']['response_code']
    // %2$s: $_SESSION['customer_first_name']
    // %3$s: $_SESSION['customer_last_name']
    // $4%u: $_SESSION['customer_id']
    //
    'MODULE_PAYMENT_PAYPALR_ALERT_UNKNOWN_DENIAL' =>
        'PayPal returned an unknown response code (%1$s) for a denied credit-card payment.' . "\n\n" .
        'The payment was attempted by %2$s %3$s (customer id %4$u). Formatted card-details follow:' . "\n\n",

    // -----
    // %1$s: One of the two language constants that follow.
    // %2$s: $_SESSION['customers_ip_address']
    // %3$s: $_SESSION['customer_first_name']
    // %4$s: $_SESSION['customer_last_name']
    // $5%u: $_SESSION['customer_id']
    //
    'MODULE_PAYMENT_PAYPALR_ALERT_LOST_STOLEN_CARD' =>
        'A credit-card payment was attempted with a %1$s card from IP address %2$s.' . "\n\n" .
        'The payment was attempted by %3$s %4$s (customer id %5$u). Formatted card-details follow:' . "\n\n",
    'MODULE_PAYMENT_PAYPALR_CARD_LOST' => 'lost or stolen',
    'MODULE_PAYMENT_PAYPALR_CARD_FRAUDULENT' => 'fraudulent',

    // -----
    // For these messages, %1$s is the card-type and %2$s is the last-4 of the card-number.
    //
    'MODULE_PAYMENT_PAYPALR_TEXT_CC_EXPIRED' => 'The %1$s card ending with <var>%2$s</var> has expired.',
    'MODULE_PAYMENT_PAYPALR_TEXT_INSUFFICIENT_FUNDS' => 'The %1$s card ending with <var>%2$s</var> has insufficient funds.',
    'MODULE_PAYMENT_PAYPALR_TEXT_CVV_FAILED' => 'The &quot;CVV Number&quot; you entered for the %1$s card ending with <var>%2$s</var> is not correct.',

    // -----
    // $1$s ... MODULE_PAYMENT_PAYPALR_TEXT_TITLE
    // $2%s ... The error-code returned by PayPal.
    //
    'MODULE_PAYMENT_PAYPALR_TEXT_CREATE_ORDER_ISSUE' => 'We are unable to process your %1$s payment at this time. Please contact us for assistance, providing us with this code: <b>%2$s</b>.',

    // -----
    // Buttons on checkout_payment page; see https://www.paypal.com/bm/webapps/mpp/logo-center for additional information.
    //
    'MODULE_PAYMENT_PAYPALR_BUTTON_ALTTEXT' => 'Click here to pay with your PayPal Wallet',
    'MODULE_PAYMENT_PAYPALR_BUTTON_COLOR' => 'YELLOW',   //- One of WHITE, YELLOW, GREY or BLUE; defaults to YELLOW.
        'MODULE_PAYMENT_PAYPALR_BUTTON_IMG_YELLOW' => 'https://www.paypalobjects.com/digitalassets/c/website/marketing/apac/C2/logos-buttons/optimize/44_Yellow_PayPal_Pill_Button.png',
        'MODULE_PAYMENT_PAYPALR_BUTTON_IMG_GREY' => 'https://www.paypalobjects.com/digitalassets/c/website/marketing/apac/C2/logos-buttons/optimize/44_Grey_PayPal_Pill_Button.png',
        'MODULE_PAYMENT_PAYPALR_BUTTON_IMG_BLUE' => 'https://www.paypalobjects.com/digitalassets/c/website/marketing/apac/C2/logos-buttons/optimize/44_Blue_PayPal_Pill_Button.png',
        'MODULE_PAYMENT_PAYPALR_BUTTON_IMG_WHITE' => 'https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-150px.png',

    'MODULE_PAYMENT_PAYPALR_CHOOSE_PAYPAL' => 'PayPal Wallet:',
    'MODULE_PAYMENT_PALPALR_CHOOSE_CARD' => 'Credit Card:',
    'MODULE_PAYMENT_PAYPALR_LOGO_SVG' => "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAxcHgiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAxMDEgMzIiIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaW5ZTWluIG1lZXQiIHhtbG5zPSJodHRwOiYjeDJGOyYjeDJGO3d3dy53My5vcmcmI3gyRjsyMDAwJiN4MkY7c3ZnIj48cGF0aCBmaWxsPSIjMDAzMDg3IiBkPSJNIDEyLjIzNyAyLjggTCA0LjQzNyAyLjggQyAzLjkzNyAyLjggMy40MzcgMy4yIDMuMzM3IDMuNyBMIDAuMjM3IDIzLjcgQyAwLjEzNyAyNC4xIDAuNDM3IDI0LjQgMC44MzcgMjQuNCBMIDQuNTM3IDI0LjQgQyA1LjAzNyAyNC40IDUuNTM3IDI0IDUuNjM3IDIzLjUgTCA2LjQzNyAxOC4xIEMgNi41MzcgMTcuNiA2LjkzNyAxNy4yIDcuNTM3IDE3LjIgTCAxMC4wMzcgMTcuMiBDIDE1LjEzNyAxNy4yIDE4LjEzNyAxNC43IDE4LjkzNyA5LjggQyAxOS4yMzcgNy43IDE4LjkzNyA2IDE3LjkzNyA0LjggQyAxNi44MzcgMy41IDE0LjgzNyAyLjggMTIuMjM3IDIuOCBaIE0gMTMuMTM3IDEwLjEgQyAxMi43MzcgMTIuOSAxMC41MzcgMTIuOSA4LjUzNyAxMi45IEwgNy4zMzcgMTIuOSBMIDguMTM3IDcuNyBDIDguMTM3IDcuNCA4LjQzNyA3LjIgOC43MzcgNy4yIEwgOS4yMzcgNy4yIEMgMTAuNjM3IDcuMiAxMS45MzcgNy4yIDEyLjYzNyA4IEMgMTMuMTM3IDguNCAxMy4zMzcgOS4xIDEzLjEzNyAxMC4xIFoiPjwvcGF0aD48cGF0aCBmaWxsPSIjMDAzMDg3IiBkPSJNIDM1LjQzNyAxMCBMIDMxLjczNyAxMCBDIDMxLjQzNyAxMCAzMS4xMzcgMTAuMiAzMS4xMzcgMTAuNSBMIDMwLjkzNyAxMS41IEwgMzAuNjM3IDExLjEgQyAyOS44MzcgOS45IDI4LjAzNyA5LjUgMjYuMjM3IDkuNSBDIDIyLjEzNyA5LjUgMTguNjM3IDEyLjYgMTcuOTM3IDE3IEMgMTcuNTM3IDE5LjIgMTguMDM3IDIxLjMgMTkuMzM3IDIyLjcgQyAyMC40MzcgMjQgMjIuMTM3IDI0LjYgMjQuMDM3IDI0LjYgQyAyNy4zMzcgMjQuNiAyOS4yMzcgMjIuNSAyOS4yMzcgMjIuNSBMIDI5LjAzNyAyMy41IEMgMjguOTM3IDIzLjkgMjkuMjM3IDI0LjMgMjkuNjM3IDI0LjMgTCAzMy4wMzcgMjQuMyBDIDMzLjUzNyAyNC4zIDM0LjAzNyAyMy45IDM0LjEzNyAyMy40IEwgMzYuMTM3IDEwLjYgQyAzNi4yMzcgMTAuNCAzNS44MzcgMTAgMzUuNDM3IDEwIFogTSAzMC4zMzcgMTcuMiBDIDI5LjkzNyAxOS4zIDI4LjMzNyAyMC44IDI2LjEzNyAyMC44IEMgMjUuMDM3IDIwLjggMjQuMjM3IDIwLjUgMjMuNjM3IDE5LjggQyAyMy4wMzcgMTkuMSAyMi44MzcgMTguMiAyMy4wMzcgMTcuMiBDIDIzLjMzNyAxNS4xIDI1LjEzNyAxMy42IDI3LjIzNyAxMy42IEMgMjguMzM3IDEzLjYgMjkuMTM3IDE0IDI5LjczNyAxNC42IEMgMzAuMjM3IDE1LjMgMzAuNDM3IDE2LjIgMzAuMzM3IDE3LjIgWiI+PC9wYXRoPjxwYXRoIGZpbGw9IiMwMDMwODciIGQ9Ik0gNTUuMzM3IDEwIEwgNTEuNjM3IDEwIEMgNTEuMjM3IDEwIDUwLjkzNyAxMC4yIDUwLjczNyAxMC41IEwgNDUuNTM3IDE4LjEgTCA0My4zMzcgMTAuOCBDIDQzLjIzNyAxMC4zIDQyLjczNyAxMCA0Mi4zMzcgMTAgTCAzOC42MzcgMTAgQyAzOC4yMzcgMTAgMzcuODM3IDEwLjQgMzguMDM3IDEwLjkgTCA0Mi4xMzcgMjMgTCAzOC4yMzcgMjguNCBDIDM3LjkzNyAyOC44IDM4LjIzNyAyOS40IDM4LjczNyAyOS40IEwgNDIuNDM3IDI5LjQgQyA0Mi44MzcgMjkuNCA0My4xMzcgMjkuMiA0My4zMzcgMjguOSBMIDU1LjgzNyAxMC45IEMgNTYuMTM3IDEwLjYgNTUuODM3IDEwIDU1LjMzNyAxMCBaIj48L3BhdGg+PHBhdGggZmlsbD0iIzAwOWNkZSIgZD0iTSA2Ny43MzcgMi44IEwgNTkuOTM3IDIuOCBDIDU5LjQzNyAyLjggNTguOTM3IDMuMiA1OC44MzcgMy43IEwgNTUuNzM3IDIzLjYgQyA1NS42MzcgMjQgNTUuOTM3IDI0LjMgNTYuMzM3IDI0LjMgTCA2MC4zMzcgMjQuMyBDIDYwLjczNyAyNC4zIDYxLjAzNyAyNCA2MS4wMzcgMjMuNyBMIDYxLjkzNyAxOCBDIDYyLjAzNyAxNy41IDYyLjQzNyAxNy4xIDYzLjAzNyAxNy4xIEwgNjUuNTM3IDE3LjEgQyA3MC42MzcgMTcuMSA3My42MzcgMTQuNiA3NC40MzcgOS43IEMgNzQuNzM3IDcuNiA3NC40MzcgNS45IDczLjQzNyA0LjcgQyA3Mi4yMzcgMy41IDcwLjMzNyAyLjggNjcuNzM3IDIuOCBaIE0gNjguNjM3IDEwLjEgQyA2OC4yMzcgMTIuOSA2Ni4wMzcgMTIuOSA2NC4wMzcgMTIuOSBMIDYyLjgzNyAxMi45IEwgNjMuNjM3IDcuNyBDIDYzLjYzNyA3LjQgNjMuOTM3IDcuMiA2NC4yMzcgNy4yIEwgNjQuNzM3IDcuMiBDIDY2LjEzNyA3LjIgNjcuNDM3IDcuMiA2OC4xMzcgOCBDIDY4LjYzNyA4LjQgNjguNzM3IDkuMSA2OC42MzcgMTAuMSBaIj48L3BhdGg+PHBhdGggZmlsbD0iIzAwOWNkZSIgZD0iTSA5MC45MzcgMTAgTCA4Ny4yMzcgMTAgQyA4Ni45MzcgMTAgODYuNjM3IDEwLjIgODYuNjM3IDEwLjUgTCA4Ni40MzcgMTEuNSBMIDg2LjEzNyAxMS4xIEMgODUuMzM3IDkuOSA4My41MzcgOS41IDgxLjczNyA5LjUgQyA3Ny42MzcgOS41IDc0LjEzNyAxMi42IDczLjQzNyAxNyBDIDczLjAzNyAxOS4yIDczLjUzNyAyMS4zIDc0LjgzNyAyMi43IEMgNzUuOTM3IDI0IDc3LjYzNyAyNC42IDc5LjUzNyAyNC42IEMgODIuODM3IDI0LjYgODQuNzM3IDIyLjUgODQuNzM3IDIyLjUgTCA4NC41MzcgMjMuNSBDIDg0LjQzNyAyMy45IDg0LjczNyAyNC4zIDg1LjEzNyAyNC4zIEwgODguNTM3IDI0LjMgQyA4OS4wMzcgMjQuMyA4OS41MzcgMjMuOSA4OS42MzcgMjMuNCBMIDkxLjYzNyAxMC42IEMgOTEuNjM3IDEwLjQgOTEuMzM3IDEwIDkwLjkzNyAxMCBaIE0gODUuNzM3IDE3LjIgQyA4NS4zMzcgMTkuMyA4My43MzcgMjAuOCA4MS41MzcgMjAuOCBDIDgwLjQzNyAyMC44IDc5LjYzNyAyMC41IDc5LjAzNyAxOS44IEMgNzguNDM3IDE5LjEgNzguMjM3IDE4LjIgNzguNDM3IDE3LjIgQyA3OC43MzcgMTUuMSA4MC41MzcgMTMuNiA4Mi42MzcgMTMuNiBDIDgzLjczNyAxMy42IDg0LjUzNyAxNCA4NS4xMzcgMTQuNiBDIDg1LjczNyAxNS4zIDg1LjkzNyAxNi4yIDg1LjczNyAxNy4yIFoiPjwvcGF0aD48cGF0aCBmaWxsPSIjMDA5Y2RlIiBkPSJNIDk1LjMzNyAzLjMgTCA5Mi4xMzcgMjMuNiBDIDkyLjAzNyAyNCA5Mi4zMzcgMjQuMyA5Mi43MzcgMjQuMyBMIDk1LjkzNyAyNC4zIEMgOTYuNDM3IDI0LjMgOTYuOTM3IDIzLjkgOTcuMDM3IDIzLjQgTCAxMDAuMjM3IDMuNSBDIDEwMC4zMzcgMy4xIDEwMC4wMzcgMi44IDk5LjYzNyAyLjggTCA5Ni4wMzcgMi44IEMgOTUuNjM3IDIuOCA5NS40MzcgMyA5NS4zMzcgMy4zIFoiPjwvcGF0aD48L3N2Zz4",

    // -----
    // Admin messages, from an order's display, viewing the PayPal transaction history.
    //
    'MODULE_PAYMENT_PAYPALR_TEXT_GETDETAILS_ERROR' => 'There was a problem retrieving PayPal transaction details.',
    'MODULE_PAYMENT_PAYPALR_NO_RECORDS' => 'No \'%1$s\' records were found in the database for order #%2$u.',
    'MODULE_PAYMENT_PAYPALR_EXTERNAL_ADDITION' => 'PayPal transactions were added outside of the payment-module\'s processing. Verify that the order\'s status is correct!',

    // -----
    // Used during the admin's display of the payment transactions on an
    // order's detailed view.
    //
    'MODULE_PAYMENT_PAYPALR_NO_RECORDS_FOUND' => 'No PayPal transactions are recorded in the database for this order.',

    'MODULE_PAYMENT_PAYPALR_TXN_TABLE_CAPTION' => 'PayPal Transactions',
    'MODULE_PAYMENT_PAYPALR_PAYMENTS_TABLE_CAPTION' => 'Settled Payments',
    'MODULE_PAYMENT_PAYPALR_PAYMENTS_TABLE_NOTE' => 'Note: Fees for refunds are reversed by PayPal!',
    'MODULE_PAYMENT_PAYPALR_PAYMENTS_NONE' => 'No currently-settled payments.',
    'MODULE_PAYMENT_PAYPALR_PAYMENTS_TOTAL' => 'Settled Totals:',
    'MODULE_PAYMENT_PAYPALR_NAME_EMAIL_ID' => 'Payer Name / Email / Payer ID',
    'MODULE_PAYMENT_PAYPALR_PAYER_ID' => 'Payer ID:',
    'MODULE_PAYMENT_PAYPALR_PAYER_STATUS' => 'Payer Status:',
    'MODULE_PAYMENT_PAYPALR_PAYMENT_TYPE' => 'Payment Type:',
    'MODULE_PAYMENT_PAYPALR_PAYMENT_STATUS' => 'Payment Status:',
    'MODULE_PAYMENT_PAYPALR_PENDING_REASON' => 'Pending Reason:',
    'MODULE_PAYMENT_PAYPALR_INVOICE' => 'Invoice:',
    'MODULE_PAYMENT_PAYPALR_PAYMENT_DATE' => 'Payment Date:',
    'MODULE_PAYMENT_PAYPALR_GROSS_AMOUNT' => 'Gross Amount:',
    'MODULE_PAYMENT_PAYPALR_PAYMENT_FEE' => 'Payment Fee:',
    'MODULE_PAYMENT_PAYPALR_SETTLE_AMOUNT' => 'Settled Amount:',
    'MODULE_PAYMENT_PAYPALR_EXCHANGE_RATE' => 'Exchange Rate:',

    'MODULE_PAYMENT_PAYPALR_TXN_TYPE' => 'Txn Type:',
    'MODULE_PAYMENT_PAYPALR_TXN_ID' => 'Txn ID:',
    'MODULE_PAYMENT_PAYPALR_TXN_PARENT_TXN_ID' => 'Parent Txn ID / Txn ID:',
    'MODULE_PAYMENT_PAYPALR_ACTION' => 'Action',
        'MODULE_PAYMENT_PAYPALR_ACTION_DETAILS' => 'Details',
        'MODULE_PAYMENT_PAYPALR_ACTION_REAUTH' => 'Re-Authorize',
        'MODULE_PAYMENT_PAYPALR_ACTION_VOID' => 'Void',
        'MODULE_PAYMENT_PAYPALR_ACTION_CAPTURE' => 'Capture',
        'MODULE_PAYMENT_PAYPALR_ACTION_REFUND' => 'Refund',
    'MODULE_PAYMENT_PAYPALR_TXN_STATUS' => 'Txn Status',

    'MODULE_PAYMENT_PAYPALR_CONFIRM' => 'Confirm',
    'MODULE_PAYMENT_PAYPALR_DAYSTOSETTLE' => 'Days to Settle:',
    'MODULE_PAYMENT_PAYPALR_AMOUNT' => 'Amount:',
    'MODULE_PAYMENT_PAYPALR_CUSTOMER_NOTE' => 'Customer Note:',
    'MODULE_PAYMENT_PAYPALR_DATE_CREATED' => 'Date Created:',
    'MODULE_PAYMENT_PAYPALR_AMOUNT_RANGE' => 'Enter an amount between %1$s 1.00 and %1$s %2$s.',
    'MODULE_PAYMENT_PAYPALR_NOTES' => 'Notes:',

    // -----
    // Constants used in the "Details" modal.
    //
    'MODULE_PAYMENT_PAYPALR_DETAILS_TITLE' => 'PayPal Transaction Details (%s)',    //- %s is one of the following two strings
        'MODULE_PAYMENT_PAYPALR_DETAILS_TYPE_PAYPAL' => 'PayPal Wallet',
        'MODULE_PAYMENT_PAYPALR_DETAILS_TYPE_CARD' => 'Credit Card',
    'MODULE_PAYMENT_PAYPALR_BUYER_INFO' => 'Buyer Information',
    'MODULE_PAYMENT_PAYPALR_PAYER_NAME' => 'Payer Name:',
    'MODULE_PAYMENT_PAYPALR_PAYER_EMAIL' => 'Payer Email:',
    'MODULE_PAYMENT_PAYPALR_BUSINESS_NAME' => 'Business Name:',
    'MODULE_PAYMENT_PAYPALR_ADDRESS_NAME' => 'Ship-to Name:',
    'MODULE_PAYMENT_PAYPALR_ADDRESS_STREET' => 'Street:',
    'MODULE_PAYMENT_PAYPALR_ADDRESS_CITY' => 'City:',
    'MODULE_PAYMENT_PAYPALR_ADDRESS_STATE' => 'State:',
    'MODULE_PAYMENT_PAYPALR_ADDRESS_ZIP' => 'Zip:',
    'MODULE_PAYMENT_PAYPALR_ADDRESS_COUNTRY' => 'Country:',
    'MODULE_PAYMENT_PAYPALR_SELLER_INFO' => 'Seller Information',
    'MODULE_PAYMENT_PAYPALR_CART_ITEMS' => 'Cart items:',
    'MODULE_PAYMENT_PAYPALR_MERCHANT_NAME' => 'Seller Name:',
    'MODULE_PAYMENT_PAYPALR_MERCHANT_EMAIL' => 'Seller Email:',
    'MODULE_PAYMENT_PAYPALR_MERCHANT_ID' => 'Merchant ID:',
    'MODULE_PAYMENT_PAYPALR_SELLER_PROTECTION' => 'Seller Protection:',
    'MODULE_PAYMENT_PAYPALR_PROCESSOR_RESPONSE' => 'Processor Response:',
        'MODULE_PAYMENT_PAYPALR_AVS_CODE' => 'AVS Code (%s)',
        'MODULE_PAYMENT_PAYPALR_RESPONSE_CODE' => 'Response Code (%s)',
        'MODULE_PAYMENT_PAYPALR_CVV_CODE' => 'CVV Code (%s)',
    'MODULE_PAYMENT_PAYPALR_AUTH_RESULT' => 'Authentication Result:',
        'MODULE_PAYMENT_PAYPALR_LIABILITY' => 'Liability Shift (%s)',
        'MODULE_PAYMENT_PAYPALR_AUTH_STATUS' => 'Authentication Status (%s)',
        'MODULE_PAYMENT_PAYPALR_ENROLL_STATUS' => 'Enrollment Status (%s)',
    'MODULE_PAYMENT_PAYPALR_AMOUNT_MISMATCH' => 'Order Amount Mismatch: %s',    //- %s is the base order-calculation amount/currency-code
    'MODULE_PAYMENT_PAYPALR_CALCULATED_AMOUNT' => 'Calculated Amount:',
    'MODULE_PAYMENT_PAYPALR_INVOICE_NUMBER' => 'Invoice #:',

    // -----
    // Constants used in the "Refunds" modal.
    //
    'MODULE_PAYMENT_PAYPALR_REFUND_TITLE' => 'Refund a Payment',
    'MODULE_PAYMENT_PAYPALR_REFUND_INSTRUCTIONS' => 'You can refund all or part of a captured payment.',
        'MODULE_PAYMENT_PAYPALR_REFUND_NOTE1' => 'A <em>full</em> refund refunds the remaining unrefunded balance of the captured payment.',
        'MODULE_PAYMENT_PAYPALR_REFUND_NOTE2' => 'A <em>partial</em> refund refunds a portion of the captured payment.',
        'MODULE_PAYMENT_PAYPALR_REFUND_NOTE3' => 'You can issue multiple <em>partial</em> refunds, up to the remaining unrefunded balance.',
    'MODULE_PAYMENT_PAYPALR_REFUND_CAPTURE_ID' => 'Capture Txn Id:',
    'MODULE_PAYMENT_PAYPALR_REMAINING_TO_REFUND' => 'Remaining to Refund:',
    'MODULE_PAYMENT_PAYPALR_REFUND_AMOUNT' => 'Amount to Refund:',
    'MODULE_PAYMENT_PAYPALR_REFUND_FULL' => 'Full Refund?',
    'MODULE_PAYMENT_PAYPALR_REFUND_DEFAULT_MESSAGE' => 'Refunded by store administrator.',

    'MODULE_PAYMENT_PAYPALR_REFUND_PARAM_ERROR' => 'Invalid parameters were supplied (CP %u) when attempting to refund a payment for this order; please try again.',
    'MODULE_PAYMENT_PAYPALR_REFUND_ERROR' => 'There was a problem refunding the transaction.',

    'MODULE_PAYMENT_PAYPALR_REFUND_COMPLETE' => 'A refund in the amount of %s has been completed.',

    // -----
    // Constants used in the "Re-Authorize" modal.
    //
    'MODULE_PAYMENT_PAYPALR_REAUTH_TITLE' => 'Re-Authorize an Order',
    'MODULE_PAYMENT_PAYPALR_REAUTH_INSTRUCTIONS' => 'To ensure that funds are still available, you can re-authorize a payment after its initial three-day honor period expires.',
        'MODULE_PAYMENT_PAYPALR_REAUTH_NOTE1' => 'Within the 29-day authorization period, you can issue multiple re-authorizations after the 3-day honor period expires for the previously-issued authorization.',
        'MODULE_PAYMENT_PAYPALR_REAUTH_NOTE2' => 'If 30 days have transpired since the date of the original authorization, you must create an authorized payment instead of re-authorizing the original.',
        'MODULE_PAYMENT_PAYPALR_REAUTH_NOTE3' => 'A re-authorized payment itself has a new honor period of three days.',
        'MODULE_PAYMENT_PAYPALR_REAUTH_NOTE4' => 'You can re-authorize an authorized payment <em>once</em> for up to 115%% of the original authorized amount (%s), not to exceed an increase of $75 USD.',

    'MODULE_PAYMENT_PAYPALR_REAUTH_ORIGINAL' => 'Original Amount:',
    'MODULE_PAYMENT_PAYPALR_REAUTH_NEW_AMOUNT' => 'Authorized Amount:',
    'MODULE_PAYMENT_PAYPALR_REAUTH_DAYS_FROM_LAST' => 'Days Since Last Authorization:',
    'MODULE_PAYMENT_PAYPALR_REAUTH_NOT_POSSIBLE' => 'The order cannot be re-authorized because an honor period is active.',

    'MODULE_PAYMENT_PAYPALR_REAUTH_PARAM_ERROR' => 'Invalid parameters were supplied (CP %u) when attempting to re-authorize this order; please try again.',
    'MODULE_PAYMENT_PAYPALR_REAUTH_ERROR' => 'There was a problem authorizing the transaction.',
    'MODULE_PAYMENT_PAYPALR_REAUTH_TOO_SOON' => 'A reauthorization is only allowed once from Day 4 to Day 29 since the date of the original authorization.',

    'MODULE_PAYMENT_PAYPALR_REAUTH_COMPLETE' => 'A re-authorization in the amount of %s has been completed.',

    // -----
    // Constants used in the "Capture" modal.
    //
    'MODULE_PAYMENT_PAYPALR_CAPTURE_TITLE' => 'Capture an Authorization',
    'MODULE_PAYMENT_PAYPALR_CAPTURE_INSTRUCTIONS' => 'To capture all or part of the outstanding funds for this order, enter the &quot;Amount&quot; below, indicate whether this is the <b>final</b> capture for the order and click the &quot;Capture&quot; button.',
    'MODULE_PAYMENT_PAYPALR_CAPTURE_FINAL_TEXT' => 'Final Capture?',
    'MODULE_PAYMENT_PAYPALR_CAPTURE_REMAINING' => 'Capture remaining funds?',
    'MODULE_PAYMENT_PAYPALR_CAPTURED_SO_FAR' => 'Previously Captured:',
    'MODULE_PAYMENT_PAYPALR_REMAINING_TO_CAPTURE' => 'Remaining to Capture:',
    'MODULE_PAYMENT_PAYPALR_CAPTURE_DEFAULT_MESSAGE' => 'Thank you for your order.',

    'MODULE_PAYMENT_PAYPALR_CAPTURE_PARAM_ERROR' => 'Invalid parameters were supplied (CP %u) when attempting to capture funds for this order; please try again.',
    'MODULE_PAYMENT_PAYPALR_CAPTURE_ERROR' => 'There was a problem capturing the transaction.',
    'MODULE_PAYMENT_PAYPALR_CAPTURE_AMOUNT' => 'The captured amount must be greater than zero unless you are capturing the remaining funds.',

    'MODULE_PAYMENT_PAYPALR_CAPTURE_NO_REMAINING' => 'All authorized funds for this order have been successfully captured.',
    'MODULE_PAYMENT_PAYPALR_CAPTURE_COMPLETE' => 'The payment for order#%u has been captured.',
    'MODULE_PAYMENT_PAYPALR_PARTIAL_CAPTURE' => 'Partially captured.',
    'MODULE_PAYMENT_PAYPALR_FINAL_CAPTURE' => 'Final capture.',

    // -----
    // Constants used in the "Void" modal.
    //
    'MODULE_PAYMENT_PAYPALR_VOID_TITLE' => 'Void an Authorization',
    'MODULE_PAYMENT_PAYPALR_VOID_INSTRUCTIONS' => 'To void this transaction, enter/copy the &quot;Authorization ID&quot; into the input field below and click the &quot;Void&quot; button.',
    'MODULE_PAYMENT_PAYPALR_VOID_AUTH_ID' => 'Authorization ID:',
    'MODULE_PAYMENT_PAYPALR_VOID_DEFAULT_MESSAGE' => 'Transaction voided.',

    'MODULE_PAYMENT_PAYPALR_VOID_PARAM_ERROR' => 'Invalid parameters were supplied when attempting to void an authorization for this order; please try again.',
    'MODULE_PAYMENT_PAYPALR_VOID_BAD_AUTH_ID' => 'Only an order\'s <em>primary</em> authorization can be voided; please try again.',
    'MODULE_PAYMENT_PAYPALR_VOID_ERROR' => 'There was a problem voiding the transaction.',
    'MODULE_PAYMENT_PAYPALR_VOID_MEMO' => 'Transaction voided by %1$s.',
    'MODULE_PAYMENT_PAYPALR_VOID_INVALID_TXN_ID' => 'The transaction ID you entered (%1$s) was not found; please try again.',
    'MODULE_PAYMENT_PAYPALR_VOID_COMPLETE' => 'The payment authorization for order#%u has been voided.',
];

if (IS_ADMIN_FLAG === true) {
    $define['MODULE_PAYMENT_PAYPALR_TEXT_ADMIN_DESCRIPTION'] =
        '<b>PayPal Checkout (RESTful)</b>, v%s<br><br>' .   //- %s is filled in with the current module version
        '<a href="https://www.paypal.com/login" rel="noopener noreferrer" target="_blank">Manage your PayPal <b>business</b> account</a><br><br>' .
        '<b>Configuration instructions:</b><br>' .
        '<ol>
            <li><a href="https://github.com/lat9/paypalr/wiki/Creating-PayPal-Credentials" rel="noopener noreferrer" target="_blank">Create your PayPal credentials.</a></li>
            <li><a href="https://github.com/lat9/paypalr/wiki/Configuring-the-Payment-Module" rel="noopener noreferrer" target="_blank">Configure the module\'s additional settings.</a></li>
         </ol>' .
        '<p>Refer to the payment module\'s GitHub Wiki <a href="https://github.com/lat9/paypalr/wiki" rel="noopener noreferrer" target="_blank">articles</a> for additional information.</p>';
}

return $define;
