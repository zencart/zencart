<?php
/**
 * paypalwpp_admin_notification.php admin display component
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright 2004 DevosC.com
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 18 Modified in v1.5.7 $
 */
if (!defined('TEXT_MAXIMUM_CHARACTERS_ALLOWED')) {
    define('TEXT_MAXIMUM_CHARACTERS_ALLOWED', ' chars allowed');
}

$outputPayPal = '';
$outputPFmain = '';
$outputAuth = '';
$outputCapt = '';
$outputVoid = '';
$outputRefund = '';

// strip slashes in case they were added to handle apostrophes:
foreach ($ipn->fields as $key => $value) {
    $ipn->fields[$key] = stripslashes($value);
}

if (!empty($response['RESPMSG'])) {
    // these would be payflow transactions

    $outputPFmain .= '<td style="vertical-align: top"><table id="outputPFmain">'."\n";

    $outputPFmain .= '<tr><td>'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_AUTHCODE."\n";
    $outputPFmain .= '</td><td>'."\n";
    $outputPFmain .= $response['AUTHCODE'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td>'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_STATUS."\n";
    $outputPFmain .= '</td><td>'."\n";
    $outputPFmain .= $response['RESPMSG'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";


    $outputPFmain .= '<tr><td>'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_AVSADDR."\n";
    $outputPFmain .= '</td><td>'."\n";
    $outputPFmain .= $response['AVSADDR'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td>'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_AVSZIP."\n";
    $outputPFmain .= '</td><td>'."\n";
    $outputPFmain .= $response['AVSZIP'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td>'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_CVV2MATCH."\n";
    $outputPFmain .= '</td><td>'."\n";
    $outputPFmain .= $response['CVV2MATCH'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td>'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_TXN_ID."\n";
    $outputPFmain .= '</td><td>'."\n";
    $outputPFmain .= $response['ORIGPNREF'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td>'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_DATE."\n";
    $outputPFmain .= '</td><td>'."\n";
    $outputPFmain .= $ipn->fields['payment_date'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td>'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_TRANSSTATE."\n";
    $outputPFmain .= '</td><td>'."\n";
    $outputPFmain .= $response['TRANSSTATE'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    if (!empty($response['DAYS_TO_SETTLE'])) {
        $outputPFmain .= '<tr><td>'."\n";
        $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_DAYSTOSETTLE."\n";
        $outputPFmain .= '</td><td>'."\n";
        $outputPFmain .= $response['DAYS_TO_SETTLE'] ."\n";
        $outputPFmain .= '</td></tr>'."\n";
    }
    $outputPFmain .= '</table></td>'."\n\n";

    if ($ipn->fields['mc_gross'] > 0) {
        $outputPFmain .= '<td style="vertical-align: top"><table id="outputPFmain-mc_gross">'."\n";

        $outputPFmain .= '<tr><td>'."\n";
        $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_CURRENCY."\n";
        $outputPFmain .= '</td><td>'."\n";
        $outputPFmain .= $ipn->fields['mc_currency'] ."\n";
        $outputPFmain .= '</td></tr>'."\n";

        $outputPFmain .= '<tr><td>'."\n";
        $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_GROSS_AMOUNT."\n";
        $outputPFmain .= '</td><td>'."\n";
        $outputPFmain .= $ipn->fields['mc_gross']."\n";
        $outputPFmain .= '</td></tr>'."\n";

        $outputPFmain .= '<tr><td>'."\n";
        $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_FEE."\n";
        $outputPFmain .= '</td><td>'."\n";
        $outputPFmain .= $ipn->fields['mc_fee']."\n";
        $outputPFmain .= '</td></tr>'."\n";

        $outputPFmain .= '<tr><td>'."\n";
        $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_EXCHANGE_RATE."\n";
        $outputPFmain .= '</td><td>'."\n";
        $outputPFmain .= $ipn->fields['exchange_rate']."\n";
        $outputPFmain .= '</td></tr>'."\n";

        $outputPFmain .= '<tr><td>'."\n";
        $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_CART_ITEMS."\n";
        $outputPFmain .= '</td><td>'."\n";
        $outputPFmain .= $ipn->fields['num_cart_items']."\n";
        $outputPFmain .= '</td></tr>'."\n";

        $outputPFmain .= '</table></td>'."\n\n";
    }
} else {
    // display all paypal status fields (in admin Orders page):
    $outputPayPal .= '<td style="vertical-align: top"><table id="outputPayPal_1">'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_FIRST_NAME."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['FIRSTNAME']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_LAST_NAME."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['LASTNAME']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    if (!empty($response['BUSINESS'])) {
        $outputPayPal .= '<tr><td>'."\n";
        $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_BUSINESS_NAME."\n";
        $outputPayPal .= '</td><td>'."\n";
        $outputPayPal .= urldecode($response['BUSINESS']) ."\n";
        $outputPayPal .= '</td></tr>'."\n";
    }

    $optional_fields = [
        'SHIPTONAME',
        'SHIPTOSTREET',
        'SHIPTOCITY',
        'SHIPTOSTATE',
        'SHIPTOZIP',
        'SHIPTOCOUNTRYNAME',
        'FEEAMT',               //- Not present when the last PayPal action was a refund/void
    ];
    foreach ($optional_fields as $optional) {
        if (!isset($response[$optional])) {
            $response[$optional] = 'n/a';
        }
    }
    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_NAME."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['SHIPTONAME']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";
    
    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STREET."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['SHIPTOSTREET']) . ' ' . (!empty($response['SHIPTOSTREET2']) ? urldecode($response['SHIPTOSTREET2']) : '') ."\n";
    $outputPayPal .= '</td></tr>'."\n";
    
    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_CITY."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['SHIPTOCITY']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";
    
    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATE."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['SHIPTOSTATE']) . ' ' . urldecode($response['SHIPTOZIP']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";
    
    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_COUNTRY."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['SHIPTOCOUNTRYNAME']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '</table></td>'."\n\n";

    $outputPayPal .= '<td style="vertical-align: top"><table id="outputPayPal_2">'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_EMAIL_ADDRESS."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['EMAIL']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_EBAY_ID."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= (!empty($response['BUYERID']) ? urldecode($response['BUYERID']) : '') ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_ID."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['PAYERID']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_STATUS."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['PAYERSTATUS']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATUS."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['ADDRESSSTATUS']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_TXN_ID."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= '<a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_view-a-trans&amp;id=' . urldecode($response['TRANSACTIONID']) . '" rel="noopener" target="_blank">' . urldecode($response['TRANSACTIONID']) . '</a>' ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PARENT_TXN_ID."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= (!empty($response['PARENTTRANSACTIONID']) ? urldecode($response['PARENTTRANSACTIONID']) : '') ."\n";
    $outputPayPal .= '</td></tr>'."\n";
    
    if (defined('MODULE_PAYMENT_PAYPALWPP_ENTRY_PROTECTIONELIG') && !empty($response['PROTECTIONELIGIBILITY'])) {
        $outputPayPal .= '<tr><td>'."\n";
        $outputPayPal .= MODULE_PAYMENT_PAYPALWPP_ENTRY_PROTECTIONELIG."\n";
        $outputPayPal .= '</td><td>'."\n";
        $outputPayPal .= $response['PROTECTIONELIGIBILITY']."\n";
        $outputPayPal .= '</td></tr>'."\n";
    }
    if (defined('MODULE_PAYMENT_PAYPAL_ENTRY_COMMENTS') && $ipn->fields['memo'] != '') {
        $outputPayPal .= '<tr><td>'."\n";
        $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_COMMENTS."\n";
        $outputPayPal .= '</td><td>'."\n";
        $outputPayPal .= $ipn->fields['memo']."\n";
        $outputPayPal .= '</td></tr>'."\n";
    }

    $outputPayPal .= '</table></td>'."\n\n";

    $outputPayPal .= '<td style="vertical-align: top"><table id="outputPayPal_3">'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_TXN_TYPE."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['TRANSACTIONTYPE']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_TYPE."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['PAYMENTTYPE']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_STATUS."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['PAYMENTSTATUS']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PENDING_REASON."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['PENDINGREASON']) . ($response['REASONCODE'] == 'None' ? '' : urldecode($response['PENDINGREASON'])) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_INVOICE."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($ipn->fields['invoice']) . (urldecode($ipn->fields['invoice']) != urldecode($response['INVNUM']) ? '<br>' . urldecode($response['INVNUM']) : '') ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_DATE."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['ORDERTIME']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '</table></td>'."\n\n";

    $outputPayPal .= '<td style="vertical-align: top"><table id="outputPayPal_4">'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_CURRENCY."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= $ipn->fields['mc_currency']."\n";
    if ($ipn->fields['mc_currency'] !== urldecode($response['CURRENCYCODE'])) {
        $outputPayPal .= ' ' . urldecode($response['CURRENCYCODE']);
    }
    $outputPayPal .= "\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_GROSS_AMOUNT."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['AMT']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_FEE."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= urldecode($response['FEEAMT']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_EXCHANGE_RATE."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= (!empty($response['EXCHANGERATE']) ? urldecode($response['EXCHANGERATE']) : '') ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td>'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_CART_ITEMS."\n";
    $outputPayPal .= '</td><td>'."\n";
    $outputPayPal .= $ipn->fields['num_cart_items']."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '</table></td>'."\n\n";
}

if (method_exists($this, '_doRefund')) {
    $outputRefund .= '<td><table id="outputRefund" class="noprint">'."\n";
    $outputRefund .= '<tr style="background-color: #eeeeee;border: solid thin black;">'."\n";
    $outputRefund .= '<td>' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_TITLE . '<br>'. "\n";
    $outputRefund .= zen_draw_form('pprefund', FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=doRefund', 'post', '', true) . zen_hide_session_id();
    if (!isset($response['RESPMSG'])) {
        // full refund (only for PayPal transactions, not Payflow)
        $outputRefund .= MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_FULL;
        $outputRefund .= '<br><input type="submit" name="fullrefund" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_FULL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_FULL . '" />' . ' ' . MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_FULL_CONFIRM_CHECK . zen_draw_checkbox_field('reffullconfirm', '', false) . '<br>';
        $outputRefund .= MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_TEXT_FULL_OR;
    } else {
        $outputRefund .= MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_PAYFLOW_TEXT;
    }
    //partial refund - input field
    $outputRefund .= MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_PARTIAL_TEXT . ' ' . zen_draw_input_field('refamt', 'enter amount', 'size="8"');
    $outputRefund .= '<input type="submit" name="partialrefund" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_PARTIAL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_PARTIAL . '" /><br>';
    //comment field
    $counterParams = 'onkeydown="characterCount(this.form[\'refnote\'],this.form.remainingRefund,255);" onkeyup="characterCount(this.form[\'refnote\'],this.form.remainingRefund,255);"';
    $outputRefund .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_TEXT_COMMENTS;
    $outputRefund .= '<div style="text-align:right;margin-top:-1.2em"><input disabled="disabled" type="text" name="remainingRefund" size="1" maxlength="3" value="255" /> ' . TEXT_MAXIMUM_CHARACTERS_ALLOWED . '</div>';
    $outputRefund .= zen_draw_textarea_field('refnote', 'soft', '50', '3', MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_DEFAULT_MESSAGE, $counterParams);
    //message text
    $outputRefund .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_SUFFIX;
    $outputRefund .= '</form>';
    $outputRefund .='</td></tr></table></td>'."\n\n";
}

if (method_exists($this, '_doAuth') && !isset($response['RESPMSG'])) {
    $outputAuth .= '<td style="vertical-align: top"><table id="outputAuth" class="noprint">'."\n";
    $outputAuth .= '<tr style="background-color: #eeeeee;border: solid thin black;">'."\n";
    $outputAuth .= '<td>' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_TITLE . '<br>'. "\n";
    $outputAuth .= zen_draw_form('ppauth', FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=doAuth', 'post', '', true);
    //partial auth - input field
    $outputAuth .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_PARTIAL_TEXT . ' ' . zen_draw_input_field('authamt', 'enter amount', 'length="8"') . zen_hide_session_id();
    $outputAuth .= '<input type="submit" name="orderauth" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_BUTTON_TEXT_PARTIAL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_BUTTON_TEXT_PARTIAL . '" />' . MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_FULL_CONFIRM_CHECK . zen_draw_checkbox_field('authconfirm', '', false) . '<br>';
    //message text
    $outputAuth .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_SUFFIX;
    $outputAuth .= '</form>';
    $outputAuth .='</td></tr></table></td>'."\n\n";
}

if (method_exists($this, '_doCapt')) {
    $outputCapt .= '<td style="vertical-align: top"><table id="outputCapt" class="noprint">'."\n";
    $outputCapt .= '<tr style="background-color: #eeeeee;border: solid thin black;">'."\n";
    $outputCapt .= '<td>' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_TITLE . '<br>'. "\n";
    $outputCapt .= zen_draw_form('ppcapture', FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=doCapture', 'post', '', true) . zen_hide_session_id();
    $outputCapt .= MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_FULL;
    $outputCapt .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_AMOUNT_TEXT . ' ' . zen_draw_input_field('captamt', 'enter amount', 'length="8"');
    $outputCapt .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_FINAL_TEXT . ' ' . zen_draw_checkbox_field('captfinal', '', true) . '<br>';
    $outputCapt .= '<input type="submit" name="btndocapture" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_BUTTON_TEXT_FULL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_BUTTON_TEXT_FULL . '" />' . ' ' . MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_FULL_CONFIRM_CHECK . zen_draw_checkbox_field('captfullconfirm', '', false);
    //comment field
    $outputCapt .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_TEXT_COMMENTS . '<br>' . zen_draw_textarea_field('captnote', 'soft', '50', '2', MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_DEFAULT_MESSAGE);
    //message text
    $outputCapt .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_SUFFIX;
    $outputCapt .= '</form>';
    $outputCapt .='</td></tr></table></td>'."\n\n";
}

if (method_exists($this, '_doVoid')) {
    $outputVoid .= '<td style="vertical-align: top"><table id="outputVoid" class="noprint">'."\n";
    $outputVoid .= '<tr style="background-color: #eeeeee;border: solid thin black;">'."\n";
    $outputVoid .= '<td>' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_TITLE . '<br>'. "\n";
    $outputVoid .= zen_draw_form('ppvoid', FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=doVoid', 'post', '', true) . zen_hide_session_id();
    $outputVoid .= MODULE_PAYMENT_PAYPAL_ENTRY_VOID . '<br>' . zen_draw_input_field('voidauthid', 'enter auth ID', 'size="8"');
    $outputVoid .= '<input type="submit" name="ordervoid" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_BUTTON_TEXT_FULL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_BUTTON_TEXT_FULL . '" />' . ' ' . MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_CONFIRM_CHECK . zen_draw_checkbox_field('voidconfirm', '', false);
    //comment field
    $outputVoid .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_TEXT_COMMENTS . '<br>' . zen_draw_textarea_field('voidnote', 'soft', '50', '3', MODULE_PAYMENT_PAYPAL_ENTRY_VOID_DEFAULT_MESSAGE);
    //message text
    $outputVoid .= '<br>' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_SUFFIX;
    $outputVoid .= '</form>';
    $outputVoid .='</td></tr></table></td>'."\n\n";
}

//reused components
$outputStartBlock = '<table class="noprint">' . "\n" . '<tr style="background-color: #cccccc;border: solid thin black;">' . "\n";
$outputEndBlock   = '</tr>' . "\n" . '</table>' . "\n\n";

// prepare output based on suitable content components
$output = '<!-- BOF: paypalwpp_admin_notification -->' . "\n";
$output.= '<script title="paypalwpp_admin_notification">
function characterCount(field, count, maxchars) {
  var realchars = field.value.replace(/\t|\r|\n|\r\n/g,\'\');
  var excesschars = realchars.length - maxchars;
  if (excesschars > 0) {
    field.value = field.value.substring(0, maxchars);
    alert("Error!\n\nYou are only allowed to enter up to " + maxchars + " characters.");
  } else {
    count.value = maxchars - realchars.length;
  }
}
</script>' . "\n";

$output .= $outputStartBlock;

$authcapt_on = (isset($_GET['authcapt']) && $_GET['authcapt'] == 'on');

if (isset($response['RESPMSG']) /*|| defined('MODULE_PAYMENT_PAYFLOW_STATUS')*/) { // payflow
    $output .= $outputPFmain;
    if (method_exists($this, '_doVoid') && (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only' || MODULE_PAYMENT_PAYFLOW_TRANSACTION_MODE == 'Auth Only' || $authcapt_on)) {
        $output .= $outputVoid;
    }
    if (method_exists($this, '_doCapt') && (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only' || MODULE_PAYMENT_PAYFLOW_TRANSACTION_MODE == 'Auth Only' || $authcapt_on)) {
        $output .= $outputCapt;
    }
    if (method_exists($this, '_doRefund')) {
        $output .= $outputRefund;
    }
} else {  // PayPal
    $output .= $outputPayPal; // one table row, four cells, one table in each
    $output .= $outputEndBlock; // close first table

    if (defined('MODULE_PAYMENT_PAYPALWPP_STATUS') || defined('MODULE_PAYMENT_PAYPALDP_STATUS')) {
        $output .= $outputStartBlock; //start second table

        $transaction_type_authorization = (isset($response['TRANSACTION_TYPE']) && $response['TRANSACTION_TYPE'] == 'Authorization');
        $transactiontype_payment = in_array($response['TRANSACTIONTYPE'], ['cart', 'expresscheckout', 'webaccept']);
        if ($transaction_type_authorization || ($transactiontype_payment && $response['PAYMENTTYPE'] == 'instant' && $response['PENDINGREASON'] == 'authorization') || $authcapt_on) {
            if (method_exists($this, '_doRefund') && ($response['PAYMENTTYPE'] != 'instant' || $module == 'paypaldp')) {
                $output .= $outputRefund;
            }
            if (MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE == 'Auth Only' || MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only') {
                if (method_exists($this, '_doAuth')) {
                    $output .= $outputAuth;
                }
                if (method_exists($this, '_doCapt')) {
                    $output .= $outputCapt;
                }
            }
            if (method_exists($this, '_doVoid')) {
                $output .= $outputVoid;
            }
        } else {
            if (method_exists($this, '_doRefund') /* && ($response['PAYMENTTYPE'] != 'instant' || $module == 'paypaldp') */) {
                $output .= $outputRefund;
            }
            if (method_exists($this, '_doVoid') && $response['PAYMENTTYPE'] == 'instant' && $response['PAYMENTSTATUS'] != 'Voided' && $module != 'paypaldp') {
                $output .= $outputVoid;
            }
        }
    }
}
$output .= $outputEndBlock; //close second table
$output .= '<!-- EOF: paypalwpp_admin_notification -->';
