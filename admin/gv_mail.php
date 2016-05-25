<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Tue Dec 29 12:22:34 2015 -0500 Modified in v1.5.5 $
 */

  require('includes/application_top.php');

  $currencies = new currencies();

  $_POST['amount'] = preg_replace('/[^0-9.%]/', '', $_POST['amount']);
  $_POST['amount'] = abs($_POST['amount']);

  if ($_GET['action'] == 'set_editor') {
    // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
    $action='';
    zen_redirect(zen_href_link(FILENAME_GV_MAIL));
  }

  if ( ($_GET['action'] == 'send_email_to_user') && ($_POST['customers_email_address'] || $_POST['email_to']) && (!$_POST['back_x']) ) {
    $audience_select = get_audience_sql_query($_POST['customers_email_address'], 'email');
    $mail = $db->Execute($audience_select['query_string']);
    $mail_sent_to = $audience_select['query_name'];
    if ($_POST['email_to']) {
      $mail_sent_to = $_POST['email_to'];
    }

    // demo active test
    if (zen_admin_demo()) {
      $_GET['action']= '';
      $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
      zen_redirect(zen_href_link(FILENAME_GV_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to)));
    }
    $from = zen_db_prepare_input($_POST['from']);
    $subject = zen_db_prepare_input($_POST['subject']);
    $recip_count=0;

    // set time-limit for processing to 5 minutes... if allowed by PHP configuration
    zen_set_time_limit(600);

    while (!$mail->EOF) {

      $id1 = create_coupon_code($mail->fields['customers_email_address']);
      $insert_query = $db->Execute("insert into " . TABLE_COUPONS . "
                                    (coupon_code, coupon_type, coupon_amount, date_created)
                                    values ('" . zen_db_input($id1) . "', 'G', '" . zen_db_input($_POST['amount']) . "', now())");

      $insert_id = $db->Insert_ID();

      $db->Execute("insert into " . TABLE_COUPON_EMAIL_TRACK . "
                    (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent)
                    values ('" . $insert_id ."', '0', 'Admin',
                            '" . zen_db_input($mail->fields['customers_email_address']) . "', now() )");

      $message = $_POST['message'];
      $html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input($_POST['message_html']);
      $message .= "\n\n" . TEXT_GV_WORTH  . $currencies->format($_POST['amount']) . "\n\n";
      $message .= TEXT_TO_REDEEM;
      $message .= TEXT_WHICH_IS . ' ' . $id1 . ' ' . TEXT_IN_CASE . "\n\n";

      $html_msg['GV_WORTH']  = TEXT_GV_WORTH;
      $html_msg['GV_AMOUNT']  = $currencies->format($_POST['amount']);
      $html_msg['GV_REDEEM'] = TEXT_TO_REDEEM . TEXT_WHICH_IS . ' <strong>' . $id1 . '</strong> ' . TEXT_IN_CASE;

      if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
        $message .= HTTP_CATALOG_SERVER  . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1 . "\n\n";
        $html_msg['GV_CODE_URL'] = '<a href="' . HTTP_CATALOG_SERVER  . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1.'">' .TEXT_CLICK_TO_REDEEM . '</a>'. "&nbsp;";
      } else {
        $message .= HTTP_CATALOG_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 . "\n\n";
        $html_msg['GV_CODE_URL'] =  '<a href="'. HTTP_CATALOG_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 .'">' .TEXT_CLICK_TO_REDEEM . '</a>' . "&nbsp;";
      }

      $message .= TEXT_OR_VISIT . HTTP_CATALOG_SERVER  . DIR_WS_CATALOG . TEXT_ENTER_CODE . "\n\n";
      $html_msg['GV_CODE_URL'] .= TEXT_OR_VISIT .  '<a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG.'">' . STORE_NAME . '</a>' . TEXT_ENTER_CODE;
      $html_msg['EMAIL_FIRST_NAME'] = $mail->fields['customers_firstname'];
      $html_msg['EMAIL_LAST_NAME']  = $mail->fields['customers_lastname'];

      // disclaimer
      $message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

      zen_mail($mail->fields['customers_firstname'] . ' ' . $mail->fields['customers_lastname'], $mail->fields['customers_email_address'], $subject , $message, $from, $from, $html_msg, 'gv_mail');
      zen_record_admin_activity('GV mail sent to ' . $mail->fields['customers_email_address'] . ' in the amount of ' . $currencies->format($_POST['amount']), 'info');
      $zco_notifier->notify('ADMIN_GV_EMAIL_SENT', $mail->fields['customers_email_address'], $currencies->format($_POST['amount']);
      $recip_count++;
      if (SEND_EXTRA_GV_ADMIN_EMAILS_TO_STATUS== '1' and SEND_EXTRA_GV_ADMIN_EMAILS_TO != '') {
        zen_mail('', SEND_EXTRA_GV_ADMIN_EMAILS_TO, SEND_EXTRA_GV_ADMIN_EMAILS_TO_SUBJECT . ' ' . $subject, $message, $from, $from, $html_msg, 'gv_mail_extra');
      }

      // Now create the coupon main and email entry
      $mail->MoveNext();
    }

    if ($_POST['email_to']) {
      $id1 = create_coupon_code($_POST['email_to']);
      $message = zen_db_prepare_input($_POST['message']);
      $message .= "\n\n" . TEXT_GV_WORTH  . $currencies->format($_POST['amount']) . "\n\n";
      $message .= TEXT_TO_REDEEM;
      $message .= TEXT_WHICH_IS . ' ' . $id1 . ' ' . TEXT_IN_CASE . "\n\n";

      $html_msg['GV_WORTH']  = TEXT_GV_WORTH;
      $html_msg['GV_AMOUNT']  = $currencies->format($_POST['amount']);
      $html_msg['GV_REDEEM'] = TEXT_TO_REDEEM . TEXT_WHICH_IS . ' <strong>' . $id1 . '</strong> ' . TEXT_IN_CASE . "\n\n";

      if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
        $message .= HTTP_CATALOG_SERVER  . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1 . "\n\n";
        $html_msg['GV_CODE_URL']  = '<a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/'.$id1.'">' .TEXT_CLICK_TO_REDEEM . '</a>'. "&nbsp;";
      } else {
        $message .= HTTP_CATALOG_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 . "\n\n";
        $html_msg['GV_CODE_URL']  =  '<a href="'. HTTP_CATALOG_SERVER  . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no='.$id1 .'">' .TEXT_CLICK_TO_REDEEM . '</a>' . "&nbsp;";
      }
      $message .= TEXT_OR_VISIT . HTTP_CATALOG_SERVER  . DIR_WS_CATALOG  . TEXT_ENTER_CODE . "\n\n";
      $html_msg['GV_CODE_URL']  .= TEXT_OR_VISIT .  '<a href="'.HTTP_CATALOG_SERVER  . DIR_WS_CATALOG.'">' . STORE_NAME . '</a>' . TEXT_ENTER_CODE;

      $html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input($_POST['message_html']);
      $html_msg['EMAIL_FIRST_NAME'] = ''; // unknown, since only an email address was supplied
      $html_msg['EMAIL_LAST_NAME']  = ''; // unknown, since only an email address was supplied

      // disclaimer
      $message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

      //Send the emails
      zen_mail('Friend', $_POST['email_to'], $subject , $message, $from, $from, $html_msg, 'gv_mail');
      $recip_count++;
      if (SEND_EXTRA_GV_ADMIN_EMAILS_TO_STATUS== '1' and SEND_EXTRA_GV_ADMIN_EMAILS_TO != '') {
        zen_mail('', SEND_EXTRA_GV_ADMIN_EMAILS_TO, SEND_EXTRA_GV_ADMIN_EMAILS_TO_SUBJECT . ' ' . $subject, $message, $from, $from, $html_msg, 'gv_mail_extra');
      }

      // Now create the coupon main entry
      $insert_query = $db->Execute("insert into " . TABLE_COUPONS . "
                                    (coupon_code, coupon_type, coupon_amount, date_created)
                                    values ('" . zen_db_input($id1) . "', 'G', '" . zen_db_input($_POST['amount']) . "', now())");

      $insert_id = $db->Insert_id();

      $insert_query = $db->Execute("insert into " . TABLE_COUPON_EMAIL_TRACK . "
                                    (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent)
                                    values ('" . $insert_id ."', '0', 'Admin',
                                            '" . zen_db_input($_POST['email_to']) . "', now() )");

    }
    zen_redirect(zen_href_link(FILENAME_GV_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to) . '&recip_count='. $recip_count ));
  }

  if ( ($_GET['action'] == 'preview') && (!$_POST['customers_email_address']) && (!$_POST['email_to']) ) {
    $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
  }

  if ( ($_GET['action'] == 'preview') && (!$_POST['subject']) ) {
    $messageStack->add(ERROR_NO_SUBJECT, 'error');
  }
  if ( ($_GET['action'] == 'preview') && ($_POST['amount'] <= 0) ) {
    $messageStack->add(ERROR_NO_AMOUNT_SELECTED, 'error');
  }

  if ($_GET['mail_sent_to']) {
    $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mail_sent_to']. '(' . $_GET['recip_count'] . ')'), 'success');
  }
require('includes/admin_html_head.php');
?>
<script type="text/javascript">
var form = "";
var submitted = false;
var error = false;
var error_message = "";

function check_recipient(field_cust, field_input, message) {
//  if (form.elements[field_cust] && form.elements[field_cust].type != "hidden" && form.elements[field_input] && form.elements[field_input].type != "hidden") {
    var field_value_cust = form.elements[field_cust].value;
    var field_value_input = form.elements[field_input].value;

    if ((field_value_input == '' || field_value_input.length < 1)  &&  field_value_cust == '') {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
//}
function check_amount(field_name, field_size, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == '' || field_value == 0 || field_value < 0 || field_value.length < field_size ) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}
function check_message(msg) {
  if (form.elements['message'] && form.elements['message_html']) {
    var field_value1 = form.elements['message'].value;
    var field_value2 = form.elements['message_html'].value;

    if ((field_value1 == '' || field_value1.length < 3) && (field_value2 == '' || field_value2.length < 3)) {
      error_message = error_message + "* " + msg + "\n";
      error = true;
    }
  }
}
function check_input(field_name, field_size, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == '' || field_value.length < field_size) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}

function check_form(form_name) {
  if (submitted == true) {
    alert("<?php echo JS_ERROR_SUBMITTED; ?>");
    return false;
  }
  error = false;
  form = form_name;
  error_message = "<?php echo JS_ERROR; ?>";

  check_recipient('customers_email_address', 'email_to', "<?php echo ERROR_NO_CUSTOMER_SELECTED; ?>");
  check_message("<?php echo ENTRY_NOTHING_TO_SEND; ?>");
  check_amount('amount',1,"<?php echo ERROR_NO_AMOUNT_SELECTED; ?>");
  check_input('subject','',"<?php echo ERROR_NO_SUBJECT; ?>");

  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}
</script>
<?php if ($editor_handler != '') include ($editor_handler); ?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="main">
<?php
// toggle switch for editor
        echo TEXT_EDITOR_INFO . zen_draw_form('set_editor_form', FILENAME_GV_MAIL, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();"') .
        zen_hide_session_id() .
        zen_draw_hidden_field('action', 'set_editor') .
        '</form>';
?>
          </td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ( ($_GET['action'] == 'preview') && ($_POST['customers_email_address'] || $_POST['email_to']) ) {
  $audience_select = get_audience_sql_query($_POST['customers_email_address']);
    $mail_sent_to = $audience_select['query_name'];
        if ($_POST['email_to']) {
          $mail_sent_to = $_POST['email_to'];
        }
?>
          <tr><?php echo zen_draw_form('mail', FILENAME_GV_MAIL, 'action=send_email_to_user'); ?>
            <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_CUSTOMER; ?></b><br /><?php echo $mail_sent_to; ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_FROM; ?></b><br /><?php echo htmlspecialchars(stripslashes($_POST['from']), ENT_COMPAT, CHARSET, TRUE); ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_SUBJECT; ?></b><br /><?php echo htmlspecialchars(stripslashes($_POST['subject']), ENT_COMPAT, CHARSET, TRUE); ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_AMOUNT; ?></b><br /><?php echo nl2br(htmlspecialchars(stripslashes($_POST['amount']), ENT_COMPAT, CHARSET, TRUE)) . ($_POST['amount'] <= 0 ? '&nbsp;<span class="alert">' . ERROR_GV_AMOUNT . '</span>' : ''); ?></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td><hr /><b><?php echo TEXT_RICH_TEXT_MESSAGE; ?></b><br /><?php echo stripslashes($_POST['message_html']); ?></td>
              </tr>
              <tr>
                <td><hr /><b><?php echo TEXT_MESSAGE; ?></b><br /><tt><?php echo nl2br(htmlspecialchars(stripslashes($_POST['message']), ENT_COMPAT, CHARSET, TRUE)); ?></tt><hr /></td>
              </tr>
              <tr>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td>
<?php
/* Re-Post all POST'ed variables */
    reset($_POST);
    while (list($key, $value) = each($_POST)) {
      if (!is_array($_POST[$key])) {
        echo zen_draw_hidden_field($key, htmlspecialchars(stripslashes($value), ENT_COMPAT, CHARSET, TRUE));
      }
    }
?>
                <table border="0" width="100%" cellpadding="0" cellspacing="2">
                  <tr>
                    <td><?php echo zen_image_submit('button_back.gif', IMAGE_BACK, 'name="back"'); ?></td>
                    <td align="right"><?php echo '<a href="' . zen_href_link(FILENAME_GV_MAIL) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . ($_POST['amount'] <= 0 ? '' : zen_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL)); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </form></tr>
<?php
  } else {
?>
          <tr><?php echo zen_draw_form('mail', FILENAME_GV_MAIL, 'action=preview','post', 'onsubmit="return check_form(mail);"'); ?>
            <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
    $customers = get_audiences_list('email');
?>
              <tr>
                <td class="main"><?php echo TEXT_CUSTOMER; ?></td>
                <td><?php echo zen_draw_pull_down_menu('customers_email_address', $customers, $_GET['customer']);?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
               <tr>
                <td class="main"><?php echo TEXT_TO; ?></td>
                <td><?php echo zen_draw_input_field('email_to', '', 'size="50"', false, 'email'); ?><?php echo '&nbsp;&nbsp;' . TEXT_SINGLE_EMAIL; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
             <tr>
                <td class="main"><?php echo TEXT_FROM; ?></td>
                <td><?php echo zen_draw_input_field('from', htmlspecialchars(EMAIL_FROM, ENT_COMPAT, CHARSET, TRUE), 'size="50"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_SUBJECT; ?></td>
                <td><?php echo zen_draw_input_field('subject', '', 'size="50"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_AMOUNT; ?></td>
                <td><?php echo zen_draw_input_field('amount'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php if (EMAIL_USE_HTML == 'true') {?>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_RICH_TEXT_MESSAGE; ?></td>
                <td><?php echo zen_draw_textarea_field('message_html', 'soft', '100%', '20', htmlspecialchars(($_POST['message_html']=='') ? TEXT_GV_ANNOUNCE : stripslashes($_POST['message_html']), ENT_COMPAT, CHARSET, TRUE), 'id="message_html" class="editorHook"'); ?></td>
              </tr>
<?php } ?>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_MESSAGE; ?></td>
                <td><?php echo zen_draw_textarea_field('message', 'soft', '60', '15', htmlspecialchars(($_POST['message']=='') ? strip_tags(TEXT_GV_ANNOUNCE) : stripslashes($_POST['message']), ENT_COMPAT, CHARSET, TRUE), 'class="noEditor"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td colspan="2" align="right"><?php echo zen_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL); ?></td>
              </tr>
            </table></td>
          </form></tr>
<?php
  }
?>
<!-- body_text_eof //-->
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
