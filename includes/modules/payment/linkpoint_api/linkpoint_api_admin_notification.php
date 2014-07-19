<?php
/**
 * @package linkpoint_api_payment_module
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright 2003 Jason LeBaron
 * @copyright Portions Copyright 2004 DevosC.com
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: linkpoint_api_admin_notification.php 15881 2010-04-11 16:32:39Z wilt $
 */

  $outputStartBlock = '';
  $outputMain = '';
  $outputAuth = '';
  $outputCapt = '';
  $outputVoid = '';
  $outputRefund = '';
  $outputEndBlock = '';
  $output = '';

// strip slashes in case they were added to handle apostrophes:
  if (!is_array($lp_api->fields)) $lp_api->fields = array();
  foreach ($lp_api->fields as $key=>$value){
    $lp_api->fields[$key] = stripslashes($value);
  }

    $outputStartBlock .= '<td><table class="noprint">'."\n";
    $outputStartBlock .= '<tr style="background-color : #bbbbbb; border-style : dotted;">'."\n";
    $outputEndBlock .= '</tr>'."\n";
    $outputEndBlock .='</table></td>'."\n";

// display all Linkpoint API status fields (in admin Orders page):
          $outputMain .= '<td valign="top"><table>'."\n";

          $outputMain .= '<tr><td class="main" width="120">'."\n";
          $outputMain .= MODULE_PAYMENT_LINKPOINT_API_LINKPOINT_ORDER_ID."\n";
          $outputMain .= '</td><td class="main">'."\n";
          $outputMain .= $lp_api->fields['lp_trans_num']."\n";
          $outputMain .= '</td></tr>'."\n";

          $outputMain .= '<tr><td class="main">'."\n";
          $outputMain .= MODULE_PAYMENT_LINKPOINT_API_APPROVAL_CODE."\n";
          $outputMain .= '</td><td class="main">'."\n";
          $outputMain .= $lp_api->fields['approval_code']."\n";
          $outputMain .= '</td></tr>'."\n";

          $outputMain .= '<tr><td class="main">'."\n";
          $outputMain .= MODULE_PAYMENT_LINKPOINT_API_TEXT_ORDERTYPE ."\n";
          $outputMain .= '</td><td class="main">'."\n";
          $outputMain .= $lp_api->fields['ordertype']."\n";
          $outputMain .= '</td></tr>'."\n";

          $outputMain .= '<tr><td class="main">'."\n";
          $outputMain .= MODULE_PAYMENT_LINKPOINT_API_TRANSACTION_REFERENCE_NUMBER."\n";
          $outputMain .= '</td><td class="main">'."\n";
          $outputMain .= $lp_api->fields['transaction_reference_number']."\n";
          $outputMain .= '</td></tr>'."\n";

          $outputMain .= '<tr><td class="main">'."\n";
          $outputMain .= MODULE_PAYMENT_LINKPOINT_API_AVS_RESPONSE."\n";
          $outputMain .= '</td><td class="main">'."\n";
          $outputMain .= $lp_api->fields['avs_response']."\n";
          $outputMain .= '</td></tr>'."\n";

          $outputMain .= '<tr><td class="main">'."\n";
          $outputMain .= MODULE_PAYMENT_LINKPOINT_API_FRAUD_SCORE."\n";
          $outputMain .= '</td><td class="main">'."\n";
          $outputMain .= $lp_api->fields['fraud_score']."\n";
          $outputMain .= '</td></tr>'."\n";

          $outputMain .= '<tr><td class="main" valign="top">'."\n";
          $outputMain .= MODULE_PAYMENT_LINKPOINT_API_MESSAGE."\n";
          $outputMain .= '</td><td class="main">'."\n";
          $outputMain .= str_replace('-- <br />', '-- ', str_replace(' r_', '<br />r_', $lp_api->fields['message']))."\n";
          $outputMain .= '</td></tr>'."\n";

          $outputMain .= '</table></td>'."\n";



  if (method_exists($this, '_doRefund')) {
    $outputRefund .= '<td><table class="noprint">'."\n";
    $outputRefund .= '<tr style="background-color : #dddddd; border-style : dotted;">'."\n";
    $outputRefund .= '<td class="main">' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_TITLE . '<br />'. "\n";
    $outputRefund .= zen_draw_form('lpapirefund', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doRefund', 'post', '', true) . zen_hide_session_id();;

    $outputRefund .= MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND . '<br />';
    $outputRefund .= MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_AMOUNT_TEXT . ' ' . zen_draw_input_field('refamt', 'enter amount', 'length="8"') . '<br />';
    $outputRefund .= MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_CC_NUM_TEXT . ' ' . zen_draw_input_field('cc_number', 'last 4 digits', 'length="20"') . '<br />';
    //trans ID field
    $outputRefund .= MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_TRANS_ID . ' ' . zen_draw_input_field('trans_id', MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_DEFAULT_TEXT, 'length="20"') . '<br />';
    // confirm checkbox
    $outputRefund .= MODULE_PAYMENT_LINKPOINT_API_TEXT_REFUND_CONFIRM_CHECK . zen_draw_checkbox_field('refconfirm', '', false) . '<br />';
    //comment field
    $outputRefund .= '<br />' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('refnote', 'soft', '50', '3', MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_DEFAULT_MESSAGE);
    //message text
    $outputRefund .= '<br />' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_SUFFIX;
    $outputRefund .= '<br /><input type="submit" name="buttonrefund" value="' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_REFUND_BUTTON_TEXT . '" />';
    $outputRefund .= '</form>';
    $outputRefund .='</td></tr></table></td>'."\n";
  }

  if (method_exists($this, '_doCapt')) {
    $outputCapt .= '<td valign="top"><table class="noprint">'."\n";
    $outputCapt .= '<tr style="background-color : #dddddd; border-style : dotted;">'."\n";
    $outputCapt .= '<td class="main">' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_TITLE . '<br />'. "\n";
    $outputCapt .= zen_draw_form('lpapicapture', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doCapture', 'post', '', true) . zen_hide_session_id();
    $outputCapt .= MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE . '<br />';
    $outputCapt .= '<br />' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_AMOUNT_TEXT . ' ' . zen_draw_input_field('captamt', 'enter amount', 'length="8"') . '<br />';
    $outputCapt .= MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_TRANS_ID . zen_draw_input_field('captauthid', MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_DEFAULT_TEXT, 'length="32"') . '<br />';
    // confirm checkbox
    $outputCapt .= MODULE_PAYMENT_LINKPOINT_API_TEXT_CAPTURE_CONFIRM_CHECK . zen_draw_checkbox_field('captconfirm', '', false) . '<br />';
    //comment field
    $outputCapt .= '<br />' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('captnote', 'soft', '50', '2', MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_DEFAULT_MESSAGE);
    //message text
    $outputCapt .= '<br />' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_SUFFIX;
    $outputCapt .= '<br /><input type="submit" name="btndocapture" value="' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_CAPTURE_BUTTON_TEXT . '" />';
    $outputCapt .= '</form>';
    $outputCapt .='</td></tr></table></td>'."\n";
  }

  if (method_exists($this, '_doVoid')) {
    $outputVoid .= '<td valign="top"><table class="noprint">'."\n";
    $outputVoid .= '<tr style="background-color : #dddddd; border-style : dotted;">'."\n";
    $outputVoid .= '<td class="main">' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_TITLE . '<br />'. "\n";
    $outputVoid .= zen_draw_form('lpapivoid', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doVoid', 'post', '', true) . zen_hide_session_id();
    $outputVoid .= MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID . zen_draw_input_field('voidauthid', MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_DEFAULT_TEXT, 'length="32"');
    $outputVoid .= '<br />' . MODULE_PAYMENT_LINKPOINT_API_TEXT_VOID_CONFIRM_CHECK . zen_draw_checkbox_field('voidconfirm', '', false);
    //comment field
    $outputVoid .= '<br /><br />' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('voidnote', 'soft', '50', '3', MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_DEFAULT_MESSAGE);
    //message text
    $outputVoid .= '<br />' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_SUFFIX;
    // confirm checkbox
    $outputVoid .= '<br /><input type="submit" name="ordervoid" value="' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_LINKPOINT_API_ENTRY_VOID_BUTTON_TEXT . '" />';
    $outputVoid .= '</form>';
    $outputVoid .='</td></tr></table></td>'."\n";
  }




// prepare output based on suitable content components
if (defined('MODULE_PAYMENT_LINKPOINT_API_STATUS') && MODULE_PAYMENT_LINKPOINT_API_STATUS != '') {
  $output = '<!-- BOF: lpapi admin transaction processing tools -->';
  $output .= $outputStartBlock;
//debug
//$output .= '<pre>' . print_r($response, true) . '</pre>';

  $output .= $outputMain;

  if (method_exists($this, '_doRefund')) $output .= $outputRefund;
  //if (method_exists($this, '_doCapt') && (MODULE_PAYMENT_LINKPOINT_API_AUTHORIZATION_MODE == 'Authorize Only' || (isset($_GET['authcapt']) && $_GET['authcapt']=='on'))) {
  if (method_exists($this, '_doCapt') ) {
      $output .= $outputEndBlock;
      $output .= $outputEndBlock;
      $output .= $outputStartBlock;
      $output .= $outputStartBlock;
    if (method_exists($this, '_doCapt') && $lp_api->fields['ordertype'] == 'PREAUTH') $output .= $outputCapt;
    if (method_exists($this, '_doVoid')) $output .= $outputVoid;
  }
  $output .= $outputEndBlock;
  $output .= $outputEndBlock;
  $output .= '<!-- EOF: lpapi admin transaction processing tools -->';
}
