<?php
/**
 * authorizenet_admin_notification.php admin display component
 *
 * @package paymentMethod
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All Thu Nov 9 13:16:10 2017 +0100 Modified in v1.5.6 $
 */

  $outputStartBlock = '';
  $outputMain = '';
  $outputAuth = '';
  $outputCapt = '';
  $outputVoid = '';
  $outputRefund = '';
  $outputEndBlock = '';
  $output = '';

    $outputStartBlock .= '<table class="noprint">'."\n";
    $outputStartBlock .= '<tr style="background-color : #bbbbbb; border-style : dotted;">'."\n";
    $outputEndBlock .= '</tr>'."\n";
    $outputEndBlock .='</table>'."\n";


  if (method_exists($this, '_doRefund')) {
    $outputRefund .= '<td><table class="noprint">'."\n";
    $outputRefund .= '<tr style="background-color : #dddddd; border-style : dotted;">'."\n";
    $outputRefund .= '<td class="main">' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_TITLE . '<br />'. "\n";
    $outputRefund .= zen_draw_form('aimrefund', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doRefund', 'post', '', true) . zen_hide_session_id();;

    $outputRefund .= MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND . '<br />';
    $outputRefund .= MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_AMOUNT_TEXT . ' ' . zen_draw_input_field('refamt', 'enter amount', 'length="8"') . '<br />';
    $outputRefund .= MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_CC_NUM_TEXT . ' ' . zen_draw_input_field('cc_number', 'last 4 digits', 'length="20"') . '<br />';
    //trans ID field
    $outputRefund .= MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_TRANS_ID . ' ' . zen_draw_input_field('trans_id', 'transaction #', 'length="20"') . '<br />';
    // confirm checkbox
    $outputRefund .= MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_REFUND_CONFIRM_CHECK . zen_draw_checkbox_field('refconfirm', '', false) . '<br />';
    //comment field
    $outputRefund .= '<br />' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('refnote', 'soft', '50', '3', MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_DEFAULT_MESSAGE);
    //message text
    $outputRefund .= '<br />' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_SUFFIX;
    $outputRefund .= '<br /><input type="submit" name="buttonrefund" value="' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_REFUND_BUTTON_TEXT . '" />';
    $outputRefund .= '</form>';
    $outputRefund .='</td></tr></table></td>'."\n";
  }

  if (method_exists($this, '_doCapt')) {
    $outputCapt .= '<td valign="top"><table class="noprint">'."\n";
    $outputCapt .= '<tr style="background-color : #dddddd; border-style : dotted;">'."\n";
    $outputCapt .= '<td class="main">' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE_TITLE . '<br />'. "\n";
    $outputCapt .= zen_draw_form('aimcapture', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doCapture', 'post', '', true) . zen_hide_session_id();
    $outputCapt .= MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE . '<br />';
    $outputCapt .= '<br />' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE_AMOUNT_TEXT . ' ' . zen_draw_input_field('captamt', 'enter amount', 'length="8"') . '<br />';
    $outputCapt .= MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE_TRANS_ID . '<br />' . zen_draw_input_field('captauthid', 'enter auth ID', 'length="32"') . '<br />';
    // confirm checkbox
    $outputCapt .= MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_CAPTURE_CONFIRM_CHECK . zen_draw_checkbox_field('captconfirm', '', false) . '<br />';
    //comment field
    $outputCapt .= '<br />' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('captnote', 'soft', '50', '2', MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE_DEFAULT_MESSAGE);
    //message text
    $outputCapt .= '<br />' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE_SUFFIX;
    $outputCapt .= '<br /><input type="submit" name="btndocapture" value="' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_CAPTURE_BUTTON_TEXT . '" />';
    $outputCapt .= '</form>';
    $outputCapt .='</td></tr></table></td>'."\n";
  }

  if (method_exists($this, '_doVoid')) {
    $outputVoid .= '<td valign="top"><table class="noprint">'."\n";
    $outputVoid .= '<tr style="background-color : #dddddd; border-style : dotted;">'."\n";
    $outputVoid .= '<td class="main">' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_VOID_TITLE . '<br />'. "\n";
    $outputVoid .= zen_draw_form('aimvoid', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doVoid', 'post', '', true) . zen_hide_session_id();
    $outputVoid .= MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_VOID . '<br />' . zen_draw_input_field('voidauthid', 'enter auth/trans ID', 'length="32"');
    $outputVoid .= '<br />' . MODULE_PAYMENT_AUTHORIZENET_AIM_TEXT_VOID_CONFIRM_CHECK . zen_draw_checkbox_field('voidconfirm', '', false);
    //comment field
    $outputVoid .= '<br /><br />' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_VOID_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('voidnote', 'soft', '50', '3', MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_VOID_DEFAULT_MESSAGE);
    //message text
    $outputVoid .= '<br />' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_VOID_SUFFIX;
    // confirm checkbox
    $outputVoid .= '<br /><input type="submit" name="ordervoid" value="' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_VOID_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_AUTHORIZENET_AIM_ENTRY_VOID_BUTTON_TEXT . '" />';
    $outputVoid .= '</form>';
    $outputVoid .='</td></tr></table></td>'."\n";
  }




// prepare output based on suitable content components
if (defined('MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS') && MODULE_PAYMENT_AUTHORIZENET_AIM_STATUS != '') {
  $output = '<!-- BOF: aim admin transaction processing tools -->';
  $output .= $outputStartBlock;
  if (MODULE_PAYMENT_AUTHORIZENET_AIM_AUTHORIZATION_TYPE == 'Authorize' || (isset($_GET['authcapt']) && $_GET['authcapt']=='on')) {
    if (method_exists($this, '_doRefund')) $output .= $outputRefund;
    if (method_exists($this, '_doCapt')) $output .= $outputCapt;
    if (method_exists($this, '_doVoid')) $output .= $outputVoid;
  } else {
    if (method_exists($this, '_doRefund')) $output .= $outputRefund;
    if (method_exists($this, '_doVoid')) $output .= $outputVoid;
  }
  $output .= $outputEndBlock;
  $output .= '<!-- EOF: aim admin transaction processing tools -->';
}
