<?php
/**
 * paypalwpp_admin_notification.php admin display component
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright 2004 DevosC.com
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: paypalwpp_admin_notification.php 18695 2011-05-04 05:24:19Z drbyte $
 */

  $outputStartBlock = '';
  $outputPayPal = '';
  $outputPFmain = '';
  $outputAuth = '';
  $outputCapt = '';
  $outputVoid = '';
  $outputRefund = '';
  $outputEndBlock = '';
  $output = '';

  // strip slashes in case they were added to handle apostrophes:
  foreach ($ipn->fields as $key=>$value){
    $ipn->fields[$key] = stripslashes($value);
  }

    $outputStartBlock .= '<td><table class="noprint">'."\n";
    $outputStartBlock .= '<tr style="background-color : #cccccc; border-style : dotted;">'."\n";
    $outputEndBlock .= '</tr>'."\n";
    $outputEndBlock .='</table></td>'."\n";


  if ($response['RESPMSG'] != '') {
    // these would be payflow transactions

    $outputPFmain .= '<td valign="top"><table>'."\n";

    $outputPFmain .= '<tr><td class="main">'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_AUTHCODE."\n";
    $outputPFmain .= '</td><td class="main">'."\n";
    $outputPFmain .= $response['AUTHCODE'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td class="main">'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_STATUS."\n";
    $outputPFmain .= '</td><td class="main">'."\n";
    $outputPFmain .= $response['RESPMSG'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";


    $outputPFmain .= '<tr><td class="main">'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_AVSADDR."\n";
    $outputPFmain .= '</td><td class="main">'."\n";
    $outputPFmain .= $response['AVSADDR'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td class="main">'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_AVSZIP."\n";
    $outputPFmain .= '</td><td class="main">'."\n";
    $outputPFmain .= $response['AVSZIP'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td class="main">'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_CVV2MATCH."\n";
    $outputPFmain .= '</td><td class="main">'."\n";
    $outputPFmain .= $response['CVV2MATCH'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td class="main">'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_TXN_ID."\n";
    $outputPFmain .= '</td><td class="main">'."\n";
    $outputPFmain .= $response['ORIGPNREF'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td class="main">'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_DATE."\n";
    $outputPFmain .= '</td><td class="main">'."\n";
    $outputPFmain .= $ipn->fields['payment_date'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    $outputPFmain .= '<tr><td class="main">'."\n";
    $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_TRANSSTATE."\n";
    $outputPFmain .= '</td><td class="main">'."\n";
    $outputPFmain .= $response['TRANSSTATE'] ."\n";
    $outputPFmain .= '</td></tr>'."\n";

    if ($response['DAYS_TO_SETTLE'] != '' ) {
      $outputPFmain .= '<tr><td class="main">'."\n";
      $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_DAYSTOSETTLE."\n";
      $outputPFmain .= '</td><td class="main">'."\n";
      $outputPFmain .= $response['DAYS_TO_SETTLE'] ."\n";
      $outputPFmain .= '</td></tr>'."\n";
    }
    $outputPFmain .= '</table></td>'."\n";

    if ($ipn->fields['mc_gross'] > 0) {
      $outputPFmain .= '<td valign="top"><table>'."\n";

      $outputPFmain .= '<tr><td class="main">'."\n";
      $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_CURRENCY."\n";
      $outputPFmain .= '</td><td class="main">'."\n";
      $outputPFmain .= $ipn->fields['mc_currency'] ."\n";
      $outputPFmain .= '</td></tr>'."\n";

      $outputPFmain .= '<tr><td class="main">'."\n";
      $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_GROSS_AMOUNT."\n";
      $outputPFmain .= '</td><td class="main">'."\n";
      $outputPFmain .= $ipn->fields['mc_gross']."\n";
      $outputPFmain .= '</td></tr>'."\n";

      $outputPFmain .= '<tr><td class="main">'."\n";
      $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_FEE."\n";
      $outputPFmain .= '</td><td class="main">'."\n";
      $outputPFmain .= $ipn->fields['mc_fee']."\n";
      $outputPFmain .= '</td></tr>'."\n";

      $outputPFmain .= '<tr><td class="main">'."\n";
      $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_EXCHANGE_RATE."\n";
      $outputPFmain .= '</td><td class="main">'."\n";
      $outputPFmain .= $ipn->fields['exchange_rate']."\n";
      $outputPFmain .= '</td></tr>'."\n";

      $outputPFmain .= '<tr><td class="main">'."\n";
      $outputPFmain .= MODULE_PAYMENT_PAYPAL_ENTRY_CART_ITEMS."\n";
      $outputPFmain .= '</td><td class="main">'."\n";
      $outputPFmain .= $ipn->fields['num_cart_items']."\n";
      $outputPFmain .= '</td></tr>'."\n";

      $outputPFmain .= '</table></td>'."\n";
    }

  } else {
    // display all paypal status fields (in admin Orders page):
    $outputPayPal .= '<td valign="top"><table>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_FIRST_NAME."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['FIRSTNAME']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_LAST_NAME."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['LASTNAME']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_BUSINESS_NAME."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['BUSINESS']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_NAME."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['NAME']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";
    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STREET."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['SHIPTOSTREET']) . ' ' . urldecode($response['SHIPTOSTREET2']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";
    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_CITY."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['SHIPTOCITY']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";
    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATE."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['SHIPTOSTATE']) . ' ' . urldecode($response['SHIPTOZIP']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";
    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_COUNTRY."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['SHIPTOCOUNTRY']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '</table></td>'."\n";

    $outputPayPal .= '<td valign="top"><table>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_EMAIL_ADDRESS."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['EMAIL']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_EBAY_ID."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['BUYERID']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_ID."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['PAYERID']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYER_STATUS."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['PAYERSTATUS']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_ADDRESS_STATUS."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['ADDRESSSTATUS']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_TXN_ID."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= '<a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_view-a-trans&id=' . urldecode($response['TRANSACTIONID']) . '" target="_blank">' . urldecode($response['TRANSACTIONID']) . '</a>' ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PARENT_TXN_ID."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['PARENTTRANSACTIONID']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";
  if (defined('MODULE_PAYMENT_PAYPALWPP_ENTRY_PROTECTIONELIG') && isset($response['PROTECTIONELIGIBILITY']) && $response['PROTECTIONELIGIBILITY'] != '') {
    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPALWPP_ENTRY_PROTECTIONELIG."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= $response['PROTECTIONELIGIBILITY']."\n";
    $outputPayPal .= '</td></tr>'."\n";
  }
  if (defined('MODULE_PAYMENT_PAYPAL_ENTRY_COMMENTS') && $ipn->fields['memo'] != '') {
    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_COMMENTS."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= $ipn->fields['memo']."\n";
    $outputPayPal .= '</td></tr>'."\n";
  }

    $outputPayPal .= '</table></td>'."\n";

    $outputPayPal .= '<td valign="top"><table>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_TXN_TYPE."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['TRANSACTIONTYPE']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_TYPE."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['PAYMENTTYPE']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_STATUS."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['PAYMENTSTATUS']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PENDING_REASON."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['PENDINGREASON']) . ($response['REASONCODE'] == 'None' ? '' : urldecode($response['PENDINGREASON'])) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_INVOICE."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($ipn->fields['invoice']) . (urldecode($ipn->fields['invoice']) != urldecode($response['INVNUM']) ? '<br />' . urldecode($response['INVNUM']) : '') ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_DATE."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['ORDERTIME']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '</table></td>'."\n";

    $outputPayPal .= '<td valign="top"><table>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_CURRENCY."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= $ipn->fields['mc_currency'] . ' ' . urldecode($response['CURRENCY']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_GROSS_AMOUNT."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['AMT']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_PAYMENT_FEE."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['FEEAMT']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_EXCHANGE_RATE."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= urldecode($response['EXCHANGERATE']) ."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '<tr><td class="main">'."\n";
    $outputPayPal .= MODULE_PAYMENT_PAYPAL_ENTRY_CART_ITEMS."\n";
    $outputPayPal .= '</td><td class="main">'."\n";
    $outputPayPal .= $ipn->fields['num_cart_items']."\n";
    $outputPayPal .= '</td></tr>'."\n";

    $outputPayPal .= '</table></td>'."\n";
  }

  if (method_exists($this, '_doRefund')) {
    $outputRefund .= '<td><table class="noprint">'."\n";
    $outputRefund .= '<tr style="background-color : #eeeeee; border-style : dotted;">'."\n";
    $outputRefund .= '<td class="main">' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_TITLE . '<br />'. "\n";
    $outputRefund .= zen_draw_form('pprefund', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doRefund', 'post', '', true) . zen_hide_session_id();
    if (!isset($response['RESPMSG'])) {
    // full refund (only for PayPal transactions, not Payflow)
      $outputRefund .= MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_FULL;
      $outputRefund .= '<br /><input type="submit" name="fullrefund" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_FULL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_FULL . '" />' . ' ' . MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_FULL_CONFIRM_CHECK . zen_draw_checkbox_field('reffullconfirm', '', false) . '<br />';
      $outputRefund .= MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_TEXT_FULL_OR;
    } else {
      $outputRefund .= MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_PAYFLOW_TEXT;
    }
    //partial refund - input field
    $outputRefund .= MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_PARTIAL_TEXT . ' ' . zen_draw_input_field('refamt', 'enter amount', 'length="8"');
    $outputRefund .= '<input type="submit" name="partialrefund" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_PARTIAL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_BUTTON_TEXT_PARTIAL . '" /><br />';
    //comment field
    $outputRefund .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('refnote', 'soft', '50', '3', MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_DEFAULT_MESSAGE);
    //message text
    $outputRefund .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_REFUND_SUFFIX;
    $outputRefund .= '</form>';
    $outputRefund .='</td></tr></table></td>'."\n";
  }

  if (method_exists($this, '_doAuth') && !isset($response['RESPMSG'])) {
    $outputAuth .= '<td valign="top"><table class="noprint">'."\n";
    $outputAuth .= '<tr style="background-color : #eeeeee; border-style : dotted;">'."\n";
    $outputAuth .= '<td class="main">' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_TITLE . '<br />'. "\n";
    $outputAuth .= zen_draw_form('ppauth', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doAuth', 'post', '', true);
    //partial auth - input field
    $outputAuth .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_PARTIAL_TEXT . ' ' . zen_draw_input_field('authamt', 'enter amount', 'length="8"') . zen_hide_session_id();
    $outputAuth .= '<input type="submit" name="orderauth" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_BUTTON_TEXT_PARTIAL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_BUTTON_TEXT_PARTIAL . '" />' . MODULE_PAYMENT_PAYPALWPP_TEXT_AUTH_FULL_CONFIRM_CHECK . zen_draw_checkbox_field('authconfirm', '', false) . '<br />';
    //message text
    $outputAuth .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_AUTH_SUFFIX;
    $outputAuth .= '</form>';
    $outputAuth .='</td></tr></table></td>'."\n";
  }

  if (method_exists($this, '_doCapt')) {
    $outputCapt .= '<td valign="top"><table class="noprint">'."\n";
    $outputCapt .= '<tr style="background-color : #eeeeee; border-style : dotted;">'."\n";
    $outputCapt .= '<td class="main">' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_TITLE . '<br />'. "\n";
    $outputCapt .= zen_draw_form('ppcapture', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doCapture', 'post', '', true) . zen_hide_session_id();
    $outputCapt .= MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_FULL;
    $outputCapt .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_AMOUNT_TEXT . ' ' . zen_draw_input_field('captamt', 'enter amount', 'length="8"');
    $outputCapt .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_FINAL_TEXT . ' ' . zen_draw_checkbox_field('captfinal', '', true) . '<br />';
    $outputCapt .= '<input type="submit" name="btndocapture" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_BUTTON_TEXT_FULL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_BUTTON_TEXT_FULL . '" />' . ' ' . MODULE_PAYMENT_PAYPALWPP_TEXT_REFUND_FULL_CONFIRM_CHECK . zen_draw_checkbox_field('captfullconfirm', '', false);
    //comment field
    $outputCapt .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('captnote', 'soft', '50', '2', MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_DEFAULT_MESSAGE);
    //message text
    $outputCapt .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_CAPTURE_SUFFIX;
    $outputCapt .= '</form>';
    $outputCapt .='</td></tr></table></td>'."\n";
  }

  if (method_exists($this, '_doVoid')) {
    $outputVoid .= '<td valign="top"><table class="noprint">'."\n";
    $outputVoid .= '<tr style="background-color : #eeeeee; border-style : dotted;">'."\n";
    $outputVoid .= '<td class="main">' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_TITLE . '<br />'. "\n";
    $outputVoid .= zen_draw_form('ppvoid', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doVoid', 'post', '', true) . zen_hide_session_id();
    $outputVoid .= MODULE_PAYMENT_PAYPAL_ENTRY_VOID . '<br />' . zen_draw_input_field('voidauthid', 'enter auth ID', 'length="8"');
    $outputVoid .= '<input type="submit" name="ordervoid" value="' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_BUTTON_TEXT_FULL . '" title="' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_BUTTON_TEXT_FULL . '" />' . ' ' . MODULE_PAYMENT_PAYPALWPP_TEXT_VOID_CONFIRM_CHECK . zen_draw_checkbox_field('voidconfirm', '', false);
    //comment field
    $outputVoid .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('voidnote', 'soft', '50', '3', MODULE_PAYMENT_PAYPAL_ENTRY_VOID_DEFAULT_MESSAGE);
    //message text
    $outputVoid .= '<br />' . MODULE_PAYMENT_PAYPAL_ENTRY_VOID_SUFFIX;
    $outputVoid .= '</form>';
    $outputVoid .='</td></tr></table></td>'."\n";
  }




// prepare output based on suitable content components
  $output = '<!-- BOF: pp admin transaction processing tools -->';
  $output .= $outputStartBlock;

//debug
//$output .= '<pre>' . print_r($response, true) . '</pre>';

  if (isset($response['RESPMSG']) || defined('MODULE_PAYMENT_PAYFLOW_STATUS')) { // payflow
    $output .= $outputPFmain;
    if (method_exists($this, '_doVoid') && (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only' || MODULE_PAYMENT_PAYFLOW_TRANSACTION_MODE == 'Auth Only' || (isset($_GET['authcapt']) && $_GET['authcapt']=='on'))) $output .= $outputVoid;
    if (method_exists($this, '_doCapt') && (MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only' || MODULE_PAYMENT_PAYFLOW_TRANSACTION_MODE == 'Auth Only' || (isset($_GET['authcapt']) && $_GET['authcapt']=='on'))) $output .= $outputCapt;
    if (method_exists($this, '_doRefund')) $output .= $outputRefund;
  } else {  // PayPal
    $output .= $outputPayPal;

    if (defined('MODULE_PAYMENT_PAYPALWPP_STATUS') || defined('MODULE_PAYMENT_PAYPALDP_STATUS')) {
      $output .= $outputEndBlock;
      $output .= '</tr><tr>' . "\n";
      $output .= $outputStartBlock;
      $output .= $outputStartBlock;
      if ($response['TRANSACTION_TYPE'] == 'Authorization' || (in_array($response['TRANSACTIONTYPE'], array('cart','expresscheckout','webaccept') ) && $response['PAYMENTTYPE'] == 'instant' && $response['PENDINGREASON'] == 'authorization') || (isset($_GET['authcapt']) && $_GET['authcapt']=='on')) {
        if (method_exists($this, '_doRefund') && ($response['PAYMENTTYPE'] != 'instant' || $module == 'paypaldp')) $output .= $outputRefund;
        if (method_exists($this, '_doAuth') && (MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE == 'Auth Only' || MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only')) $output .= $outputAuth;
        if (method_exists($this, '_doCapt') && (MODULE_PAYMENT_PAYPALWPP_TRANSACTION_MODE == 'Auth Only' || MODULE_PAYMENT_PAYPALDP_TRANSACTION_MODE == 'Auth Only')) $output .= $outputCapt;
        if (method_exists($this, '_doVoid')) $output .= $outputVoid;
      } else {
        if (method_exists($this, '_doRefund') /* && ($response['PAYMENTTYPE'] != 'instant' || $module == 'paypaldp') */) $output .= $outputRefund;
        if (method_exists($this, '_doVoid') && $response['PAYMENTTYPE'] == 'instant' && $response['PAYMENTSTATUS'] != 'Voided' && $module != 'paypaldp') $output .= $outputVoid;
      }
    }
  }
  $output .= $outputEndBlock;
  $output .= $outputEndBlock;
  $output .= '<!-- EOF: pp admin transaction processing tools -->';
