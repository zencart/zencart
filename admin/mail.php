<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Oct 16 Modified in v1.5.7a $
 */
require('includes/application_top.php');

//DEBUG:  // these defines will become configuration switches in ADMIN in a future version.
//DEBUG:  // right now, attachments aren't working right unless only sending HTML messages with NO text-only version supplied.
if (!defined('EMAIL_ATTACHMENTS_ENABLED')) {
  define('EMAIL_ATTACHMENTS_ENABLED', false);
}
if (!defined('EMAIL_ATTACHMENT_UPLOADS_ENABLED')) {
  define('EMAIL_ATTACHMENT_UPLOADS_ENABLED', false);
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$file_list = array();
$upload_file_name = $attachment_file = $attachment_filetype = '';

if ($action == 'set_editor') {
  // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
  $action = '';
  zen_redirect(zen_href_link(FILENAME_MAIL));
}

if (($action == 'send_email_to_user') && isset($_POST['customers_email_address']) && !isset($_POST['back'])) {
  $audience_select = get_audience_sql_query(zen_db_input($_POST['customers_email_address']), 'email');
  $mail = $db->Execute($audience_select['query_string']);
  $mail_sent_to = $audience_select['query_name'];
  if (!empty($_POST['email_to'])) {
    $mail_sent_to = zen_db_prepare_input($_POST['email_to']);
  }

  // error message if no email address
  if (empty($mail_sent_to)) {
    $messageStack->add_session(ERROR_NO_CUSTOMER_SELECTED, 'error');
    $_GET['action'] = '';
    zen_redirect(zen_href_link(FILENAME_MAIL));
  }

  $from = zen_db_prepare_input($_POST['from']);
  $subject = zen_db_prepare_input($_POST['subject']);
  $message = zen_db_prepare_input($_POST['message']);
  $html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input(isset($_POST['message_html']) ? $_POST['message_html'] : '');
  $attachment_file = $_POST['attachment_file'];
  $attachment_fname = basename($_POST['attachment_file']);
  $attachment_filetype = $_POST['attachment_filetype'];

  //send message using the zen email function
  //echo'EOF-attachments_list='.$attachment_file.'->'.$attachment_filetype;
  $recip_count = 0;
  foreach ($mail as $item) {
    $html_msg['EMAIL_SALUTATION'] = EMAIL_SALUTATION;
    $html_msg['EMAIL_FIRST_NAME'] = $item['customers_firstname'];
    $html_msg['EMAIL_LAST_NAME'] = $item['customers_lastname'];
    $rc = zen_mail($item['customers_firstname'] . ' ' . $item['customers_lastname'], $item['customers_email_address'], $subject, $message, STORE_NAME, $from, $html_msg, 'direct_email', array('file' => $attachment_file, 'name' => basename($attachment_file), 'mime_type' => $attachment_filetype));
    if ($rc === '') $recip_count++;
  }
  if ($recip_count > 0) {
    $messageStack->add_session(sprintf(NOTICE_EMAIL_SENT_TO, $mail_sent_to . ' (' . $recip_count . ')'), 'success');
  } else {
    $messageStack->add_session(sprintf(NOTICE_EMAIL_FAILED_SEND, $mail_sent_to . ' (' . $recip_count . ')'), 'error');
  }
  zen_redirect(zen_href_link(FILENAME_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to) . '&recip_count=' . $recip_count . (isset($_GET['origin']) ? '&origin=' . zen_output_string_protected($_GET['origin']) : '') . (isset($_GET['cID']) ? '&cID=' . (int)$_GET['cID'] : '') . (isset($_GET['customer']) ? '&customer=' . zen_output_string_protected($_GET['customer']) : '')));
}

if (EMAIL_ATTACHMENTS_ENABLED && $action == 'preview') {
  // PROCESS UPLOAD ATTACHMENTS
  if (isset($_FILES['upload_file']) && zen_not_null($_FILES['upload_file']) && ($_POST['upload_file'] != 'none')) {
    if ($attachments_obj = new upload('upload_file')) {
      $attachments_obj->set_extensions(array('jpg', 'jpeg', 'gif', 'png', 'zip', 'gzip', 'pdf', 'mp3', 'wma', 'wmv', 'wav', 'epub', 'ogg', 'webm', 'm4v', 'm4a'));
      $attachments_obj->set_destination(DIR_WS_ADMIN_ATTACHMENTS . $_POST['attach_dir']);
      if ($attachments_obj->parse() && $attachments_obj->save()) {
        $attachment_file = $_POST['attach_dir'] . $attachments_obj->filename;
        $attachment_fname = $attachments_obj->filename;
        $attachment_filetype = $_FILES['upload_file']['type'];
      }
    }
  }

  //DEBUG:
  //$messageStack->add('EOF-attachments_list='.$attachment_file.'->'.$attachment_filetype, 'caution');
} //end attachments upload
// error detection
if ($action == 'preview') {
  if (!isset($_POST['customers_email_address'])) {
    $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
  }

  if (!$_POST['subject']) {
    $messageStack->add(ERROR_NO_SUBJECT, 'error');
  }

  if (!$_POST['message'] && !$_POST['message_html']) {
    $messageStack->add(ENTRY_NOTHING_TO_SEND, 'error');
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
    <?php if ($editor_handler != '') include ($editor_handler); ?>
    <script>
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
          check_input('subject', '', "<?php echo ERROR_NO_SUBJECT; ?>");
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
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row text-right">
          <?php
          // toggle switch for editor
          echo TEXT_EDITOR_INFO . zen_draw_form('set_editor_form', FILENAME_MAIL, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();"') .
          zen_hide_session_id() .
          zen_draw_hidden_field('action', 'set_editor') .
          '</form>';
          ?>
      </div>
      <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '1'); ?></div>
      <?php
      if (($action == 'preview') && isset($_POST['customers_email_address'])) {
        $audience_select = get_audience_sql_query(zen_db_input($_POST['customers_email_address']));
        $mail_sent_to = $audience_select['query_name'];
        ?>
        <div class="row">
          <div class="col-sm-3 text-right"><b><?php echo TEXT_CUSTOMER; ?></b></div>
          <div class="col-sm-9"><?php echo $mail_sent_to; ?></div>
          <div class="col-sm-12"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '1'); ?></div>
          <div class="col-sm-3 text-right"><b><?php echo TEXT_FROM; ?></b></div>
          <div class="col-sm-9"><?php echo htmlspecialchars(stripslashes($_POST['from']), ENT_COMPAT, CHARSET, TRUE); ?></div>
          <div class="col-sm-12"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '1'); ?></div>
          <div class="col-sm-3 text-right"><b><?php echo TEXT_SUBJECT; ?></b></div>
          <div class="col-sm-9"><?php echo htmlspecialchars(stripslashes($_POST['subject']), ENT_COMPAT, CHARSET, TRUE); ?></div>
          <div class="col-sm-12"><hr></div>
          <div class="col-sm-3 text-right"><b><?php echo strip_tags(TEXT_MESSAGE_HTML); ?></b></div>
          <div class="col-sm-9">
              <?php if (EMAIL_USE_HTML != 'true') echo TEXT_WARNING_HTML_DISABLED . '<br />'; ?>
              <?php
              $html_preview = zen_output_string(isset($_POST['message_html']) ? $_POST['message_html'] : '');
              echo (false !== stripos($html_preview, '<br') ? $html_preview : nl2br($html_preview));
              ?>
          </div>
          <div class="col-sm-12"><hr></div>
          <div class="col-sm-3 text-right"><b><?php echo strip_tags(TEXT_MESSAGE); ?></b></div>
          <div class="col-sm-9">
              <?php
              $message_preview = empty($_POST['message']) ? $_POST['message_html'] : $_POST['message'];
              $message_preview = (false !== stripos($message_preview, '<br') ? $message_preview : nl2br($message_preview));
              $message_preview = str_replace(array('<br>', '<br />'), "<br />\n", $message_preview);
              $message_preview = str_replace('</p>', "</p>\n", $message_preview);
              echo '<tt>' . nl2br(htmlspecialchars(stripslashes(strip_tags($message_preview)), ENT_COMPAT, CHARSET, TRUE)) . '</tt>';
              ?>
          </div>
          <div class="col-sm-12"><hr></div>
          <?php if (EMAIL_ATTACHMENTS_ENABLED && ($upload_file_name != '' || $attachment_file != '')) { ?>
            <div class="col-sm-3 text-right"><b><?php echo TEXT_ATTACHMENTS_LIST; ?></b></div>
            <div class="col-sm-9">
                <?php echo ((EMAIL_ATTACHMENT_UPLOADS_ENABLED && zen_not_null($upload_file_name)) ? $upload_file_name : $attachment_file); ?>
            </div>
          <?php } ?>
          <div class="col-sm-12"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
          <?php echo zen_draw_form('mail', FILENAME_MAIL, 'action=send_email_to_user' . (isset($_GET['cID']) ? '&cID=' . (int)$_GET['cID'] : '') . (isset($_GET['customer']) ? '&customer=' . zen_output_string_protected($_GET['customer']) : '') . (isset($_GET['origin']) ? '&origin=' . zen_output_string_protected($_GET['origin']) : '')); ?>
          <?php
          /* Re-Post all POST'ed variables */
          foreach($_POST as $key => $value) {
            if (!is_array($_POST[$key])) {
              echo zen_draw_hidden_field($key, stripslashes($value));
            }
          }
          echo zen_draw_hidden_field('upload_file', stripslashes($upload_file_name));
          echo zen_draw_hidden_field('attachment_file', $attachment_file);
          echo zen_draw_hidden_field('attachment_filetype', $attachment_filetype);
          ?>
          <div class="col-sm-6">
            <button type="submit" name="back" value="back" class="btn btn-default"><?php echo IMAGE_BACK; ?></button>
          </div>
          <div class="col-sm-6 text-right">
            <a href="<?php echo zen_href_link(FILENAME_MAIL, (isset($_GET['cID']) ? 'cID=' . (int)$_GET['cID'] : '') . (isset($_GET['customer']) ? '&customer=' . zen_output_string_protected($_GET['customer']) : '') . (isset($_GET['origin']) ? '&origin=' . zen_output_string_protected($_GET['origin']) : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a> <button type="submit" class="btn btn-primary"><?php echo IMAGE_SEND_EMAIL; ?></button>
          </div>
          <?php echo '</form>'; ?>
        </div>
        <?php
      } else {
        ?>
        <div class="row">
            <?php echo zen_draw_form('mail', FILENAME_MAIL, 'action=preview' . (isset($_GET['cID']) ? '&cID=' . (int)$_GET['cID'] : '') . (isset($_GET['customer']) ? '&customer=' . zen_output_string_protected($_GET['customer']) : '') . (isset($_GET['origin']) ? '&origin=' . zen_output_string_protected($_GET['origin']) : ''), 'post', 'onsubmit="return check_form(mail);" enctype="multipart/form-data" class="form-horizontal"'); ?>
            <?php
            $customers = get_audiences_list('email', 'false', (isset($_GET['customer']) ? zen_output_string_protected($_GET['customer']) : ''));
            ?>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_CUSTOMER, 'customers_email_address', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9"><?php echo zen_draw_pull_down_menu('customers_email_address', $customers, (isset($_GET['customer']) ? zen_output_string_protected($_GET['customer']) : ''), 'class="form-control"');  //, 'multiple'        ?></div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_FROM, 'from', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9"><?php echo zen_draw_input_field('from', htmlspecialchars(EMAIL_FROM, ENT_COMPAT, CHARSET, TRUE), 'size="50" class="form-control"'); ?></div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_SUBJECT, 'subject', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9"><?php echo zen_draw_input_field('subject', htmlspecialchars(isset($_POST['subject']) ? $_POST['subject'] : '', ENT_COMPAT, CHARSET, TRUE), 'size="50" class="form-control"'); ?></div>
          </div>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_MESSAGE_HTML, 'message_html', 'class="col-sm-3 control-label"'); //HTML version   ?>
            <div class="col-sm-9">
                <?php
                if (EMAIL_USE_HTML == 'true') {
                  echo zen_draw_textarea_field('message_html', 'soft', '100%', '25', htmlspecialchars(stripslashes(isset($_POST['message_html'])?$_POST['message_html']:''), ENT_COMPAT, CHARSET, TRUE), 'id="message_html" class="editorHook form-control"');
                } else {
                  echo TEXT_WARNING_HTML_DISABLED;
                }
                ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_MESSAGE, 'message', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9"><?php echo zen_draw_textarea_field('message', 'soft', '100%', '15', htmlspecialchars(isset($_POST['message']) ? $_POST['message'] : '', ENT_COMPAT, CHARSET, TRUE), 'class="noEditor form-control"'); ?></div>
          </div>

          <?php if (defined('EMAIL_ATTACHMENTS_ENABLED') && EMAIL_ATTACHMENTS_ENABLED === true && defined('DIR_WS_ADMIN_ATTACHMENTS') && is_dir(DIR_WS_ADMIN_ATTACHMENTS) && is_writable(DIR_WS_ADMIN_ATTACHMENTS)) { ?>
            <?php if (defined('EMAIL_ATTACHMENT_UPLOADS_ENABLED') && EMAIL_ATTACHMENT_UPLOADS_ENABLED === true) { ?>
              <?php
              $dir_info = zen_build_subdirectories_array(DIR_WS_ADMIN_ATTACHMENTS, 'admin-attachments');
              ?>
              <div class="form-group">
                  <?php echo zen_draw_label(TEXT_SELECT_ATTACHMENT_TO_UPLOAD, 'upload_file', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9"><?php echo zen_draw_file_field('upload_file') . '<br>' . stripslashes($_POST['upload_file']) . zen_draw_hidden_field('prev_upload_file', stripslashes($_POST['upload_file'])); ?></div>
                <?php echo zen_draw_label(TEXT_ATTACHMENTS_DIR, 'attach_dir', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-9"><?php echo zen_draw_pull_down_menu('attach_dir', $dir_info, '', 'class="form-control"'); ?></div>
              </div>
            <?php } // end uploads-enabled dialog ?>
            <?php
            $dir_info = zen_build_subdirectories_array(DIR_WS_ADMIN_ATTACHMENTS, '(none)');
            ?>
            <div class="form-group">
                <?php echo zen_draw_label(TEXT_SELECT_ATTACHMENT, 'attachment_file', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9"><?php echo zen_draw_pull_down_menu('attachment_file', $file_list, $_POST['attachment_file'], 'class="form-control"'); ?></div>
            </div>
          </div>
        <?php } // end attachments fields   ?>
        <?php
        if (isset($_GET['origin'])) {
          $origin = zen_output_string_protected($_GET['origin']);
        } else {
          $origin = FILENAME_DEFAULT;
        }
        ?>
        <div class="row text-right">
          <button type="submit" class="btn btn-primary"><?php echo IMAGE_PREVIEW; ?></button> <a href="<?php echo zen_href_link($origin, (!empty($_GET['cID']) ? 'cID=' . (int)$_GET['cID'] : ''), $request_type); ?>" class="btn btn-default"><?php echo IMAGE_CANCEL; ?></a>
        </div>
        <?php
      }
      ?>
      <!-- body_text_eof //-->
      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
