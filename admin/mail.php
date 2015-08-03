<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson   Modified in v1.6.0 $
 */

  require('includes/application_top.php');

  //DEBUG:  // these defines will become configuration switches in ADMIN in a future version.
  //DEBUG:  // right now, attachments aren't working right unless only sending HTML messages with NO text-only version supplied.
  if (!defined('EMAIL_ATTACHMENTS_ENABLED'))        define('EMAIL_ATTACHMENTS_ENABLED',false);
  if (!defined('EMAIL_ATTACHMENT_UPLOADS_ENABLED')) define('EMAIL_ATTACHMENT_UPLOADS_ENABLED',false);


  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if ($action == 'set_editor') {
    // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
    $action='';
    zen_redirect(zen_href_link(FILENAME_MAIL));
  }

  if ( ($action == 'send_email_to_user') && isset($_POST['customers_email_address']) && !isset($_POST['back_x']) ) {
    $audience_select = get_audience_sql_query(zen_db_input($_POST['customers_email_address']), 'email');
    $mail = $db->Execute($audience_select['query_string']);
    $mail_sent_to = $audience_select['query_name'];
    if ($_POST['email_to']) {
      $mail_sent_to = zen_db_prepare_input($_POST['email_to']);
    }

    // error message if no email address
    if (empty($mail_sent_to)) {
      $messageStack->add_session(ERROR_NO_CUSTOMER_SELECTED, 'error');
      $_GET['action']='';
      zen_redirect(zen_href_link(FILENAME_MAIL));
    }

    $from = zen_db_prepare_input($_POST['from']);
    $subject = zen_db_prepare_input($_POST['subject']);
    $message = zen_db_prepare_input($_POST['message']);
    $html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input($_POST['message_html']);
    $attachment_file = $_POST['attachment_file'];
    $attachment_fname = basename($_POST['attachment_file']);
    $attachment_filetype = $_POST['attachment_filetype'];

    // demo active test
    if (zen_admin_demo()) {
      $_GET['action']= '';
      $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
      zen_redirect(zen_href_link(FILENAME_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to)));
    }

    //send message using the zen email function
    //echo'EOF-attachments_list='.$attachment_file.'->'.$attachment_filetype;
    $recip_count=0;
    while (!$mail->EOF) {
      $html_msg['EMAIL_FIRST_NAME'] = $mail->fields['customers_firstname'];
      $html_msg['EMAIL_LAST_NAME']  = $mail->fields['customers_lastname'];
      zen_mail($mail->fields['customers_firstname'] . ' ' . $mail->fields['customers_lastname'], $mail->fields['customers_email_address'], $subject, $message, STORE_NAME, $from, $html_msg, 'direct_email', array('file' => $attachment_file, 'name' => basename($attachment_file), 'mime_type'=>$attachment_filetype) );
      $recip_count++;
      $mail->MoveNext();
    }
    if ($recip_count > 0) {
      $messageStack->add_session(sprintf(NOTICE_EMAIL_SENT_TO, $mail_sent_to .  ' (' . $recip_count . ')'), 'success');
    } else {
      $messageStack->add_session(sprintf(NOTICE_EMAIL_FAILED_SEND, $mail_sent_to .  ' (' . $recip_count . ')'), 'error');
    }
    zen_redirect(zen_href_link(FILENAME_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to) . '&recip_count='. $recip_count  . (isset($_GET['origin']) ? '&origin='.zen_output_string_protected($_GET['origin']): '') . (isset($_GET['cID']) ? '&cID=' . (int)$_GET['cID'] : '') . (isset($_GET['customer']) ? '&customer='.zen_output_string_protected($_GET['customer']): '')));
  }

  if ( EMAIL_ATTACHMENTS_ENABLED && $action == 'preview') {
    // PROCESS UPLOAD ATTACHMENTS
    if (isset($_FILES['upload_file']) && zen_not_null($_FILES['upload_file']) && ($_POST['upload_file'] != 'none')) {
      if ($attachments_obj = new upload('upload_file')) {
        $attachments_obj->set_destination(DIR_WS_ADMIN_ATTACHMENTS . $_POST['attach_dir']);
        if ($attachments_obj->parse() && $attachments_obj->save()) {
          $attachment_file = $_POST['attach_dir'] . $attachments_obj->filename;
          $attachment_fname = $attachments_obj->filename;
          $attachment_filetype= $_FILES['upload_file']['type'];
        }
      }
    }

    //DEBUG:
    //$messageStack->add('EOF-attachments_list='.$attachment_file.'->'.$attachment_filetype, 'caution');
  } //end attachments upload

  // error detection
  if ($action == 'preview') {
    if (!isset($_POST['customers_email_address']) ) {
      $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
    }

    if ( !$_POST['subject'] ) {
      $messageStack->add(ERROR_NO_SUBJECT, 'error');
    }

    if ( !$_POST['message'] && !$_POST['message_html'] ) {
      $messageStack->add(ENTRY_NOTHING_TO_SEND, 'error');
    }
  }

require('includes/admin_html_head.php');
?>
<?php if ($editor_handler != '') include ($editor_handler); ?>
<script type="text/javascript">
var form = "";
var submitted = false;
var error = false;
var error_message = "";

function check_select(field_name, field_default, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == field_default) {
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
function check_attachments(message) {
  if (form.elements['upload_file'] && (form.elements['upload_file'].type != "hidden") && form.elements['attachment_file'] && (form.elements['attachment_file'].type != "hidden")) {
    var field_value_upload = form.elements['upload_file'].value;
    var field_value_file = form.elements['attachment_file'].value;

    if (field_value_upload != '' && field_value_file != '') {
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

  check_select("customers_email_address", "", "<?php echo ERROR_NO_CUSTOMER_SELECTED; ?>");
  check_input('subject','',"<?php echo ERROR_NO_SUBJECT; ?>");
  //  check_message("<?php echo ENTRY_NOTHING_TO_SEND; ?>");
  check_attachments("<?php echo ERROR_ATTACHMENTS; ?>");

  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}
</script>
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
  echo TEXT_EDITOR_INFO . zen_draw_form('set_editor_form', FILENAME_MAIL, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();"') .
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
  if ( ($action == 'preview') && isset($_POST['customers_email_address']) ) {
    $audience_select = get_audience_sql_query(zen_db_input($_POST['customers_email_address']));
    $mail_sent_to = $audience_select['query_name'];
?>
        <tr>
          <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
            <tr>
              <td class="smallText"><b><?php echo TEXT_CUSTOMER; ?></b>&nbsp;&nbsp;&nbsp;<?php echo $mail_sent_to; ?></td>
            </tr>
            <tr>
              <td class="smallText"><b><?php echo TEXT_FROM; ?></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars(stripslashes($_POST['from']), ENT_COMPAT, CHARSET, TRUE); ?></td>
            </tr>
            <tr>
              <td class="smallText"><b><?php echo TEXT_SUBJECT; ?></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars(stripslashes($_POST['subject']), ENT_COMPAT, CHARSET, TRUE); ?></td>
            </tr>
            <tr>
              <td class="smallText"><b><hr /><?php echo strip_tags(TEXT_MESSAGE_HTML); ?></b></td>
            </tr>
            <tr>
              <td width="500">
<?php if (EMAIL_USE_HTML != 'true') echo TEXT_WARNING_HTML_DISABLED.'<br />'; ?>
<?php $html_preview = zen_output_string_protected($_POST['message_html']); echo (stristr($html_preview, '<br') ? $html_preview : nl2br($html_preview)); ?><hr /></td>
            </tr>
            <tr>
              <td class="smallText"><b><?php echo strip_tags(TEXT_MESSAGE); ?></b><br /></td>
            </tr>
            <tr>
              <td>
<?php
  $message_preview = ((is_null($_POST['message']) || $_POST['message']=='') ? $_POST['message_html'] : $_POST['message'] );
  $message_preview = (stristr($message_preview, '<br') ? $message_preview : nl2br($message_preview));
  $message_preview = str_replace(array('<br>','<br />'), "<br />\n", $message_preview);
  $message_preview = str_replace('</p>', "</p>\n", $message_preview);
  echo '<tt>' . nl2br(htmlspecialchars(stripslashes(strip_tags($message_preview)), ENT_COMPAT, CHARSET, TRUE) ) . '</tt>';
?>
                <hr />
              </td>
            </tr>
<?php if (EMAIL_ATTACHMENTS_ENABLED && ($upload_file_name != '' || $attachment_file != '')) { ?>
            <tr>
              <td class="smallText"><b><?php echo TEXT_ATTACHMENTS_LIST; ?></b><?php echo '&nbsp;&nbsp;&nbsp;' . ((EMAIL_ATTACHMENT_UPLOADS_ENABLED && zen_not_null($upload_file_name)) ? $upload_file_name : $attachment_file) ; ?></td>
            </tr>
<?php } ?>
            <tr>
              <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
            <tr><?php echo zen_draw_form('mail', FILENAME_MAIL, 'action=send_email_to_user' . (isset($_GET['cID']) ? '&cID=' . (int)$_GET['cID'] : '') . (isset($_GET['customer']) ? '&customer='.zen_output_string_protected($_GET['customer']): '') . (isset($_GET['origin']) ? '&origin='.zen_output_string_protected($_GET['origin']): '')); ?>
              <td>
<?php
  /* Re-Post all POST'ed variables */
  reset($_POST);
  while (list($key, $value) = each($_POST)) {
    if (!is_array($_POST[$key])) {
      echo zen_draw_hidden_field($key, stripslashes($value));
    }
  }
  echo zen_draw_hidden_field('upload_file', stripslashes($upload_file_name));
  echo zen_draw_hidden_field('attachment_file', $attachment_file);
  echo zen_draw_hidden_field('attachment_filetype', $attachment_filetype);
?>
                <table border="0" width="100%" cellpadding="0" cellspacing="2">
                  <tr>
                    <td><?php echo zen_image_submit('button_back.gif', IMAGE_BACK, 'name="back"'); ?></td>
                    <td align="right"><?php echo '<a href="' . zen_href_link(FILENAME_MAIL, 'cID=' . zen_db_prepare_input($_GET['cID']) . (isset($_GET['customer']) ? '&customer=' . zen_output_string_protected($_GET['customer']) : '') . (isset($_GET['origin']) ? '&origin='.zen_output_string_protected($_GET['origin']): '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . zen_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL); ?></td>
                  </tr>
                </table></td>
              </tr>
              </table></td>
            </form></tr>
<?php
} else {
?>
            <tr><?php echo zen_draw_form('mail', FILENAME_MAIL,'action=preview' . (isset($_GET['cID']) ? '&cID=' . (int)$_GET['cID'] : '') . (isset($_GET['customer']) ? '&customer='.zen_output_string_protected($_GET['customer']): '') . (isset($_GET['origin']) ? '&origin='.zen_output_string_protected($_GET['origin']): ''), 'post', 'onsubmit="return check_form(mail);" enctype="multipart/form-data"'); ?>
              <td><table border="0" cellpadding="0" cellspacing="2">
            <tr>
              <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
<?php
  $customers = get_audiences_list('email', '', (isset($_GET['customer']) ? $_GET['customer'] : ''));
?>
            <tr>
              <td class="main"><?php echo TEXT_CUSTOMER; ?></td>
              <td><?php echo zen_draw_pull_down_menu('customers_email_address', $customers, (isset($_GET['customer']) ? $_GET['customer'] : ''));  //, 'multiple' ?></td>
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
              <td><?php echo zen_draw_input_field('subject', htmlspecialchars($_POST['subject'], ENT_COMPAT, CHARSET, TRUE), 'size="50"'); ?></td>
            </tr>
            <tr>
              <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
            <tr>
              <td valign="top" class="main"><?php echo TEXT_MESSAGE_HTML; //HTML version?></td>
              <td class="main" width="750">
<?php if (EMAIL_USE_HTML != 'true') echo TEXT_WARNING_HTML_DISABLED; ?>
<?php if (EMAIL_USE_HTML == 'true') {
  echo zen_draw_textarea_field('message_html', 'soft', '100%', '25', htmlspecialchars(stripslashes($_POST['message_html']), ENT_COMPAT, CHARSET, TRUE), 'id="message_html" class="editorHook"');
} ?>
              </td>
            </tr>
            <tr>
              <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
            <tr>
              <td valign="top" class="main"><?php echo TEXT_MESSAGE; ?></td>
              <td><?php echo zen_draw_textarea_field('message', 'soft', '100%', '15', htmlspecialchars($_POST['message'], ENT_COMPAT, CHARSET, TRUE), 'class="noEditor"'); ?></td>
            </tr>

<?php if (defined('EMAIL_ATTACHMENTS_ENABLED') && EMAIL_ATTACHMENTS_ENABLED === true && defined('DIR_WS_ADMIN_ATTACHMENTS') && is_dir(DIR_WS_ADMIN_ATTACHMENTS) && is_writable(DIR_WS_ADMIN_ATTACHMENTS) ) { ?>
            <tr>
              <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
<?php if (defined('EMAIL_ATTACHMENT_UPLOADS_ENABLED') && EMAIL_ATTACHMENT_UPLOADS_ENABLED === true) { ?>
<?php
  $dir_info = zen_build_subdirectories_array(DIR_WS_ADMIN_ATTACHMENTS, 'admin-attachments');
?>
            <tr>
              <td class="main" valign="top"><?php echo TEXT_SELECT_ATTACHMENT_TO_UPLOAD; ?></td>
              <td class="main"><?php echo zen_draw_file_field('upload_file') . '<br />' . stripslashes($_POST['upload_file']) . zen_draw_hidden_field('prev_upload_file', stripslashes( $_POST['upload_file']) ); ?><br />
<?php echo TEXT_ATTACHMENTS_DIR; ?>&nbsp;<?php echo zen_draw_pull_down_menu('attach_dir', $dir_info); ?></td>
            </tr>
            <tr>
              <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
<?php  } // end uploads-enabled dialog ?>
<?php
  $dir_info = zen_build_subdirectories_array(DIR_WS_ADMIN_ATTACHMENTS, '(none)');
?>
            <tr>
              <td class="main" valign="top"><?php echo TEXT_SELECT_ATTACHMENT; ?></td>
              <td class="main"><?php echo zen_draw_pull_down_menu('attachment_file', $file_list, $_POST['attachment_file']); ?></td>
            </tr>
<?php } // end attachments fields ?>
            <tr>
              <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
<?php
  if (isset($_GET['origin'])) {
    $origin = $_GET['origin'];
  } else {
    $origin = FILENAME_DEFAULT;
  }
  if (isset($_GET['mode']) && $_GET['mode'] == 'SSL') {
    $mode = 'SSL';
  } else {
    $mode = 'NONSSL';
  }
?>
            <tr>
              <td colspan="2" align="right"><?php echo zen_image_submit('button_preview.gif', IMAGE_PREVIEW) . '&nbsp;' .
              '<a href="' . zen_href_link($origin, 'cID=' . zen_db_prepare_input($_GET['cID']), $mode) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
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