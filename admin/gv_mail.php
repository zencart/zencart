<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 11 Modified in v2.0.0-alpha1 $
 */
require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';
$currencies = new currencies();

if (!empty($_GET['action']) && $_GET['action'] == 'set_editor') {
  // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
  $action = '';
  zen_redirect(zen_href_link(FILENAME_GV_MAIL));
}

$_POST['amount'] = !empty($_POST['amount']) ? preg_replace('/[^0-9.%]/', '', $_POST['amount']) : 0;
$_POST['amount'] = abs($_POST['amount']);
$action = isset($_GET['action']) ? zen_db_prepare_input($_GET['action']) : '';
if ($action != '') {
  switch ($action) {
    case ('preview'):
      $error = 0;
      if (empty($_POST['customers_email_address']) && empty($_POST['email_to'])) {
        $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
        $error++;
      }
      if (empty($_POST['subject'])) {
        $messageStack->add(ERROR_NO_SUBJECT, 'error');
        $error++;
      }
      if (empty($_POST['amount']) || $_POST['amount'] <= 0) {
        $messageStack->add(ERROR_NO_AMOUNT_ENTERED, 'error');
        $error++;
      }
      if ($error > 0) {
        $action = $_GET['action'] = '';
      }
      break;

    case ('send_email_to_user'):
      if (isset($_POST['back'])) {
        break;
      }
      if (isset($_POST['cancel'])) {
        unset($_POST);
        break;
      }

      $from_name = $from_email_address = zen_db_prepare_input($_POST['from']);
      $subject = zen_db_prepare_input($_POST['subject']);
      $recip_count = 0;

      if ($_POST['email_to']) {
        $mail_sent_to = zen_db_prepare_input($_POST['email_to']);
        if (!empty($_POST['email_to_name'])) {
          $mail_sent_to_names = explode(' ', zen_db_prepare_input($_POST['email_to_name']), 2);
          $customers_firstname = $mail_sent_to_names[0];
          $customers_lastname = (!empty($mail_sent_to_names[1]) ? $mail_sent_to_names[1] : ''); 
        } else {
          $customers_firstname = '';
          $customers_lastname = TEXT_CUSTOMER;
        }
        $mail = [
          'fields' => [
            'customers_firstname' => $customers_firstname,
            'customers_lastname' => $customers_lastname,
            'customers_email_address' => $mail_sent_to
          ]
        ];
      } else {
        $audience_select = get_audience_sql_query($_POST['customers_email_address'], 'email');
        $mail = $db->Execute($audience_select['query_string']);
        $mail_sent_to = $audience_select['query_name'];
        // set time-limit for processing to 5 minutes... if allowed by PHP configuration
        zen_set_time_limit(600);
      }

      foreach ($mail as $row) {
        $id1 = Coupon::generateRandomCouponCode($row['customers_email_address']);
        $insert_query = $db->Execute("INSERT INTO " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, date_created)
                                      VALUES ('" . zen_db_input($id1) . "', 'G', '" . zen_db_input($_POST['amount']) . "', now())");

        $insert_id = $db->Insert_ID();

        $db->Execute("INSERT INTO " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent)
                      VALUES (" . (int)$insert_id . ", 0, 'Admin', '" . zen_db_input($row['customers_email_address']) . "', now() )");

        $message = EMAIL_SALUTATION . ' ' . $row['customers_firstname'] . ' ' . $row['customers_lastname'] . ',' . "\n\n";
        $html_msg['EMAIL_SALUTATION'] = EMAIL_SALUTATION;
        $html_msg['EMAIL_FIRST_NAME'] = $row['customers_firstname'];
        $html_msg['EMAIL_LAST_NAME'] = $row['customers_lastname'];

        $message .= zen_db_prepare_input($_POST['message']);
        if (EMAIL_USE_HTML == 'true') {
          $html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input($_POST['message_html']);
        } else {
          $html_msg['EMAIL_MESSAGE_HTML'] = '';
        }

        $gv_value = $currencies->format($_POST['amount']);
        if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
          $url = HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php/gv_redeem/gv_no/';
        } else {
          $url = HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'index.php?main_page=gv_redeem&gv_no=';
        }

        $message .= "\n\n" . sprintf(TEXT_GV_ANNOUNCE, $gv_value) . "\n\n";
        $html_msg['GV_ANNOUNCE'] = sprintf(TEXT_GV_ANNOUNCE, $gv_value);

        $message .= sprintf(TEXT_GV_TO_REDEEM_TEXT, $url, $id1);
        $html_msg['GV_REDEEM'] = sprintf(TEXT_GV_TO_REDEEM_HTML, $url, $id1);

        // disclaimer
        $message .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";

        zen_mail($row['customers_firstname'] . ' ' . $row['customers_lastname'], $row['customers_email_address'], $subject, $message, $from_name, $from_email_address, $html_msg, 'gv_mail');
        zen_record_admin_activity('GV mail sent to ' . $row['customers_email_address'] . ' for ' . $currencies->format($_POST['amount']), 'info');
        $recip_count++;

        if (SEND_EXTRA_GV_ADMIN_EMAILS_TO_STATUS == '1' and SEND_EXTRA_GV_ADMIN_EMAILS_TO != '') {
          zen_mail('', SEND_EXTRA_GV_ADMIN_EMAILS_TO, SEND_EXTRA_GV_ADMIN_EMAILS_TO_SUBJECT . ' ' . $subject, $message, $from_name, $from_email_address, $html_msg, 'gv_mail_extra');
        }
      }

      zen_redirect(zen_href_link(FILENAME_GV_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to) . '&recip_count=' . $recip_count));
      break;
  }
}
if (!empty($_GET['mail_sent_to']) && $_GET['mail_sent_to']) {
  $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['recip_count'], $_GET['mail_sent_to']), 'success');
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <script>
      let form = "";
      let submitted = false;
      let error = false;
      let error_message = "";

      function check_recipient(field_cust, field_input, message) {
//  if (form.elements[field_cust] && form.elements[field_cust].type != "hidden" && form.elements[field_input] && form.elements[field_input].type != "hidden") {
        const field_value_cust = form.elements[field_cust].value;
        const field_value_input = form.elements[field_input].value;

        if ((field_value_input === '' || field_value_input.length < 1) && field_value_cust === '') {
          error_message = error_message + "* " + message + "\n";
          error = true;
        }
      }

      //}
      function check_form(form_name) {
        if (submitted === true) {
          alert("<?php echo JS_ERROR_SUBMITTED; ?>");
          return false;
        }
        error = false;
        form = form_name;
        error_message = "<?php echo JS_ERROR; ?>";

        check_recipient('customers_email_address', 'email_to', "<?php echo ERROR_NO_CUSTOMER_SELECTED; ?>");

        if (error === true) {
          alert(error_message);
          return false;
        } else {
          submitted = true;
          return true;
        }
      }
    </script>
    <?php
    if ($editor_handler != '') {
      include $editor_handler;
    }
    ?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
      <h1><?php echo HEADING_TITLE; ?></h1>
      <!-- body_text //-->
      <?php
      switch ($action) {
        case 'preview' :
          if (!empty($_POST['email_to'])) {
            $mail_sent_to = $_POST['email_to'];
            $mail_sent_to_name = $_POST['email_to_name'];
          } else {
            $audience_select = get_audience_sql_query($_POST['customers_email_address']);
            $mail_sent_to = $audience_select['query_name'];
            $mail_sent_to_name = '';
          }
          ?>
          <?php echo zen_draw_form('mail', FILENAME_GV_MAIL, 'action=send_email_to_user'); ?>
          <table class="table">
            <tr>
              <td class="text-right col-sm-3"><b><?php echo TEXT_FROM; ?></b></td>
              <td><?php echo htmlspecialchars(stripslashes($_POST['from']), ENT_COMPAT, CHARSET, true); ?></td>
            </tr>
            <tr>
              <td class="text-right"><b><?php echo TEXT_TO; ?></b></td>
              <td><?php echo $mail_sent_to . (!empty($_POST['email_to']) && $mail_sent_to_name != '' ? ' - ' . $mail_sent_to_name : ''); ?></td>
            </tr>
            <tr>
              <td class="text-right"><b><?php echo TEXT_SUBJECT; ?></b></td>
              <td><?php echo htmlspecialchars(stripslashes($_POST['subject']), ENT_COMPAT, CHARSET, true); ?></td>
            </tr>
            <tr>
              <td class="text-right"><b><?php echo TEXT_AMOUNT; ?></b></td>
              <td><?php echo $currencies->format(nl2br(htmlspecialchars(stripslashes($_POST['amount']), ENT_COMPAT, CHARSET, true))) . ($_POST['amount'] <= 0 ? '&nbsp;<span class="alert">' . ERROR_NO_AMOUNT_ENTERED . '</span>' : ''); ?>
              </td>
            </tr>
            <?php if (EMAIL_USE_HTML == 'true') { ?>
              <tr>
                <td class="text-right"><b><?php echo TEXT_HTML_MESSAGE; ?></b></td>
                <td><?php echo stripslashes($_POST['message_html']); ?></td>
              </tr>
            <?php } ?>
            <tr>
              <td class="text-right"><b><?php echo TEXT_MESSAGE; ?></b></td>
              <td class="tt"><?php echo nl2br(htmlspecialchars(stripslashes($_POST['message']), ENT_COMPAT, CHARSET, true)); ?></td>
            </tr>
          </table>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
              <?php echo($_POST['amount'] <= 0 ? '' : '<button type="submit" class="btn btn-primary" name="send">' . IMAGE_SEND . '</button>&nbsp;'); ?><button type="submit" class="btn btn-info" name="back"><?php echo IMAGE_BACK; ?></button>&nbsp;<button type="submit" class="btn btn-default" name="cancel"><?php echo IMAGE_CANCEL; ?></button>
            </div>
          </div>
          <?php
          /* Re-Post all POST'ed variables */
          foreach ($_POST as $key => $value) {
            if (!is_array($_POST[$key])) {
              echo zen_draw_hidden_field($key, htmlspecialchars(stripslashes($value), ENT_COMPAT, CHARSET, true));
            }
          }
          echo '</form>';
          break;
        default:
          ?>
          <div class="row">
            <div class="col-sm-offset-8 col-sm-4 text-right">
              <?php echo zen_draw_form('set_editor_form', FILENAME_GV_MAIL, '', 'get', 'class="form-horizontal"'); ?>
              <div class="form-group">
                <?php echo zen_draw_label(TEXT_EDITOR_INFO, 'reset_editor', 'class="control-label col-sm-3"'); ?>
                <div class="col-sm-9">
                  <?php echo zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();" class="form-control" id="reset_editor"'); ?>
                </div>
                <?php echo zen_hide_session_id(); ?>
                <?php echo zen_draw_hidden_field('action', 'set_editor'); ?>
                <?php echo'</form>'; ?>
              </div>
            </div>
          </div>
          <?php
          echo zen_draw_form('mail', FILENAME_GV_MAIL, 'action=preview', 'post', 'onsubmit="return check_form(mail);" class="form-horizontal"');
          $customers = get_audiences_list('email');
          ?>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_FROM, 'from', 'class="control-label col-sm-3"'); ?>
            <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('from', htmlspecialchars(EMAIL_FROM, ENT_COMPAT, CHARSET, true), 'size="50" class="form-control" id="from"'); ?>
            </div>
          </div>
          <hr>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_TO_CUSTOMERS, 'customers_email_address', 'class="control-label col-sm-3"'); ?>
            <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_pull_down_menu('customers_email_address', $customers, (!empty($_POST['customers_email_address']) ? $_POST['customers_email_address'] : ''), 'class="form-control" id="customers_email_address"'); ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12"><?php echo TEXT_TO_EMAIL_INFO; ?></div>
          </div>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_TO_EMAIL, 'email_to', 'class="control-label col-sm-3"'); ?>
            <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('email_to', (!empty($_POST['email_to']) ? $_POST['email_to'] : ''), 'size="25" class="form-control" id="email_to"', false, 'email'); ?>
            </div>
          </div>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_TO_EMAIL_NAME, 'email_to_name', 'class="control-label col-sm-3"'); ?>
            <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('email_to_name', (!empty($_POST['email_to_name']) ? $_POST['email_to_name'] : ''), 'size="25" class="form-control" id="email_to_name"', false); ?>
            </div>
          </div>
          <hr>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_SUBJECT, 'subject', 'class="control-label col-sm-3"'); ?>
            <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('subject', (!empty($_POST['subject']) ? $_POST['subject'] : ''), 'size="50" class="form-control" id="subject"', true); ?>
            </div>
          </div>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_AMOUNT, 'amount', 'class="control-label col-sm-3"'); ?>
            <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('amount', (!empty($_POST['amount']) ? $_POST['amount'] : ''), 'step="any" class="form-control" id="amount"', true, 'number'); ?>
              <span class="help-block"><?php echo TEXT_AMOUNT_INFO; ?></span>
            </div>
          </div>
          <hr>
          <div class="form-group">
            <div class="col-sm-12"><?php echo TEXT_MESSAGE_INFO; ?></div>
          </div>
          <?php if (EMAIL_USE_HTML == 'true') { ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_HTML_MESSAGE, 'message_html', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9 col-md-6">
                <?php echo zen_draw_textarea_field('message_html', 'soft', '', '10', htmlspecialchars(empty($_POST['message_html']) ? '' : stripslashes($_POST['message_html']), ENT_COMPAT, CHARSET, true), 'id="message_html" class="editorHook form-control"'); ?>
              </div>
            </div>
          <?php } ?>
          <div class="form-group">
            <?php echo zen_draw_label(TEXT_MESSAGE, 'message', 'class="control-label col-sm-3"'); ?>
            <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_textarea_field('message', 'soft', '', '10', htmlspecialchars(empty($_POST['message']) ? '' : stripslashes($_POST['message']), ENT_COMPAT, CHARSET, true), 'id="message" class="noEditor tt form-control"'); ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9 col-md-6 text-right">
              <button type="submit" class="btn btn-primary"><?php echo IMAGE_PREVIEW; ?></button>
            </div>
          </div>
          <?php
          echo '</form>';
      }
      ?>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->

  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
