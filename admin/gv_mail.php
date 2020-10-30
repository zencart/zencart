<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Oct 30 Modified in v1.5.7a $
 */

require('includes/application_top.php');

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

if (!empty($_GET['action']) && $_GET['action'] == 'set_editor') {
    // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
    $action = '';
    zen_redirect(zen_href_link(FILENAME_GV_MAIL));
}

$_POST['amount'] = !empty($_POST['amount']) ? preg_replace('/[^0-9.%]/', '', $_POST['amount']) : 0;
$_POST['amount'] = abs($_POST['amount']);
if (!isset($_POST['message_html'])) $_POST['message_html'] = '';
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
                    $customers_lastname = $mail_sent_to_names[1];
                } else {
                    $customers_firstname = '';
                    $customers_lastname = TEXT_CUSTOMER;
                }
                $mail = array('fields' => array('customers_firstname' => $customers_firstname, 'customers_lastname' => $customers_lastname, 'customers_email_address' => $mail_sent_to));
            } else {
                $audience_select = get_audience_sql_query($_POST['customers_email_address'], 'email');
                $mail = $db->Execute($audience_select['query_string']);
                $mail_sent_to = $audience_select['query_name'];
                // set time-limit for processing to 5 minutes... if allowed by PHP configuration
                zen_set_time_limit(600);
            }

            foreach ($mail as $row) {
                $id1 = zen_create_coupon_code($row['customers_email_address']);
                $insert_query = $db->Execute("insert into " . TABLE_COUPONS . "
                                    (coupon_code, coupon_type, coupon_amount, date_created)
                                    values ('" . zen_db_input($id1) . "', 'G', '" . zen_db_input($_POST['amount']) . "', now())");

                $insert_id = $db->Insert_ID();

                $db->Execute("insert into " . TABLE_COUPON_EMAIL_TRACK . "
                    (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent)
                    values ('" . $insert_id . "', '0', 'Admin',
                            '" . zen_db_input($row['customers_email_address']) . "', now() )");

                $message = EMAIL_SALUTATION . ' ' . $row['customers_firstname'] . ' ' . $row['customers_lastname'] . ',' . "\n\n";
                $html_msg['EMAIL_SALUTATION'] = EMAIL_SALUTATION;
                $html_msg['EMAIL_FIRST_NAME'] = $row['customers_firstname'];
                $html_msg['EMAIL_LAST_NAME'] = $row['customers_lastname'];

                $message .= zen_db_prepare_input($_POST['message']);
                $html_msg['EMAIL_MESSAGE_HTML'] = zen_db_prepare_input($_POST['message_html']);

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
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script>
        function init() {
            cssjsmenu('navbar');
            if (document.getElementById) {
                let kill = document.getElementById('hoverJS');
                kill.disabled = true;
            }
        }
    </script>
    <script>
        let form = "";
        let submitted = false;
        let error = false;
        let error_message = "";

        function check_recipient(field_cust, field_input, message) {
//  if (form.elements[field_cust] && form.elements[field_cust].type != "hidden" && form.elements[field_input] && form.elements[field_input].type != "hidden") {
            let field_value_cust = form.elements[field_cust].value;
            let field_value_input = form.elements[field_input].value;

            if ((field_value_input === '' || field_value_input.length < 1) && field_value_cust === '') {
                error_message = error_message + "* " + message + "\n";
                error = true;
            }
        }

        //}

        function check_input(field_name, field_size, message) {
            if (form.elements[field_name] && (form.elements[field_name].type !== "hidden")) {
                let field_value = form.elements[field_name].value;

                if (field_value === '' || field_value.length < field_size) {
                    error_message = error_message + "* " + message + "\n";
                    error = true;
                }
            }
        }

        function check_amount(field_name, field_size, message) {
            if (form.elements[field_name] && (form.elements[field_name].type !== "hidden")) {
                let field_value = form.elements[field_name].value;
                const decimal_check = /^(\d*\.)?\d+$/;
                if (field_value === '' || field_value === 0 || field_value < 0 || field_value.length < field_size || !field_value.match(decimal_check)) {
                    error_message = error_message + "* " + message + "\n";
                    error = true;
                }
            }
        }

        function check_message(msg) {
            if (form.elements['message'] && form.elements['message_html']) {
                let field_value1 = form.elements['message'].value;
                let field_value2 = form.elements['message_html'].value;

                if ((field_value1 === '' || field_value1.length < 3) && (field_value2 === '' || field_value2.length < 3)) {
                    error_message = error_message + "* " + msg + "\n";
                    error = true;
                }
            }
        }

        function check_form(form_name) {
            if (submitted === true) {
                alert("<?php echo JS_ERROR_SUBMITTED; ?>");
                return false;
            }
            error = false;
            form = form_name;
            error_message = "<?php echo JS_ERROR; ?>";

            check_recipient('customers_email_address', 'email_to', "<?php echo ERROR_NO_CUSTOMER_SELECTED; ?>");
            check_input('subject', '', "<?php echo ERROR_NO_SUBJECT; ?>");
            check_amount('amount', 1, "<?php echo ERROR_NO_AMOUNT_ENTERED; ?>");
            //check_message("<?php //echo ENTRY_NOTHING_TO_SEND; ?>");//text is optional

            if (error === true) {
                alert(error_message);
                return false;
            } else {
                submitted = true;
                return true;
            }
        }
    </script>
    <?php if ($editor_handler != '') {
        include($editor_handler);
    } ?>
</head>
<body onLoad="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<div class="container-fluid">
    <!-- body //-->
    <h1><?php echo HEADING_TITLE; ?></h1>
    <!-- body_text //-->
    <?php
    if ($action == 'preview') {
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
        <table>
            <tr>
                <td><b><?php echo TEXT_FROM; ?></b> <?php echo htmlspecialchars(stripslashes($_POST['from']), ENT_COMPAT, CHARSET, true); ?></td>
            </tr>
            <tr>
                <td><b><?php echo TEXT_TO; ?></b> <?php echo $mail_sent_to . (!empty($_POST['email_to']) && $mail_sent_to_name != '' ? ' - ' . $mail_sent_to_name : ''); ?></td>
            </tr>
            <tr>
                <td><b><?php echo TEXT_SUBJECT; ?></b> <?php echo htmlspecialchars(stripslashes($_POST['subject']), ENT_COMPAT, CHARSET, true); ?></td>
            </tr>
            <tr>
                <td><b><?php echo TEXT_AMOUNT; ?></b> <?php echo $currencies->format(nl2br(htmlspecialchars(stripslashes($_POST['amount']), ENT_COMPAT, CHARSET, true))) . ($_POST['amount'] <= 0 ? '&nbsp;<span class="alert">' . ERROR_NO_AMOUNT_ENTERED . '</span>' : ''); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <hr/>
                    <b><?php echo TEXT_HTML_MESSAGE; ?></b><br><?php echo stripslashes($_POST['message_html']); ?></td>
            </tr>
            <tr>
                <td>
                    <hr/>
                    <b><?php echo TEXT_MESSAGE; ?></b><br><span class="tt"><?php echo nl2br(htmlspecialchars(stripslashes($_POST['message']), ENT_COMPAT, CHARSET, true)); ?></span>
                    <hr/>
                </td>
            </tr>
            <tr>
                <td>
                    <?php
                    /* Re-Post all POST'ed variables */
                    foreach ($_POST as $key => $value) {
                        if (!is_array($_POST[$key])) {
                            echo zen_draw_hidden_field($key, htmlspecialchars(stripslashes($value), ENT_COMPAT, CHARSET, true));
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
        <div class="form-group">
            <div>
                <button type="submit" class="btn btn-primary" name="cancel"><?php echo IMAGE_CANCEL; ?></button>
                <button type="submit" class="btn btn-primary" name="back"><?php echo IMAGE_BACK; ?></button>
                <div class="right"><?php echo($_POST['amount'] <= 0 ? '' : '<button type="submit" class="btn btn-primary" name="send">' . IMAGE_SEND . '</button>'); ?></div>
            </div>
        </div>
        <?php echo '</form>';
    } else { ?>
        <div class="row text-right">
            <?php
            // toggle switch for editor
            echo TEXT_EDITOR_INFO . zen_draw_form('set_editor_form', FILENAME_GV_MAIL, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();"') .
                zen_hide_session_id() .
                zen_draw_hidden_field('action', 'set_editor') .
                '</form>';
            ?>
        </div>
        <?php
        echo zen_draw_form('mail', FILENAME_GV_MAIL, 'action=preview', 'post', 'onsubmit="return check_form(mail);"');
        $customers = get_audiences_list('email');
        ?>
        <table>
            <tr>
                <td class="main"><?php echo TEXT_FROM; ?></td>
                <td colspan="3"><?php echo zen_draw_input_field('from', htmlspecialchars(EMAIL_FROM, ENT_COMPAT, CHARSET, true), 'size="50"'); ?></td>
            </tr>
            <tr>
                <td colspan="4">
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="main"><?php echo TEXT_TO_CUSTOMERS; ?></td>
                <td colspan="3"><?php echo zen_draw_pull_down_menu('customers_email_address', $customers, (!empty($_POST['customers_email_address']) ? $_POST['customers_email_address'] : '')); ?></td>
            </tr>
            <tr>
                <td class="main" colspan="4"><?php echo '&nbsp;' . TEXT_TO_EMAIL_INFO; ?></td>
            </tr>
            <tr>
                <td class="main"><?php echo TEXT_TO_EMAIL; ?></td>
                <td><?php echo zen_draw_input_field('email_to', (!empty($_POST['email_to']) ? $_POST['email_to'] : ''), 'size="25"', false, 'email'); ?></td>
                <td class="main text-right">&nbsp;<?php echo TEXT_TO_EMAIL_NAME; ?></td>
                <td><?php echo zen_draw_input_field('email_to_name', (!empty($_POST['email_to_name']) ? $_POST['email_to_name'] : ''), 'size="25"', false); ?></td>
            </tr>
            <tr>
                <td colspan="4">
                    <hr>
                </td>
            </tr>
            <tr>
                <td class="main"><?php echo TEXT_SUBJECT; ?></td>
                <td colspan="3"><?php echo zen_draw_input_field('subject', (!empty($_POST['subject']) ? $_POST['subject'] : ''), 'size="50"'); ?></td>
            </tr>
            <tr>
                <td class="main"><?php echo TEXT_AMOUNT; ?></td>
                <td colspan="3"><?php echo zen_draw_input_field('amount', (!empty($_POST['amount']) ? $_POST['amount'] : ''), 'step="any" style="padding: 3px 0" required', '', 'number'); ?><?php echo '&nbsp;' . TEXT_AMOUNT_INFO; ?></td>
            </tr>
            <tr>
                <td colspan="4"><hr></td>
            </tr>
            <tr>
                <td colspan="4"><?php echo TEXT_MESSAGE_INFO; ?></td>
            </tr>
            <?php if (EMAIL_USE_HTML == 'true') { ?>
                <tr>
                    <td class="main" style="vertical-align: top"><?php echo TEXT_HTML_MESSAGE; ?></td>
                    <td colspan="3"><?php echo zen_draw_textarea_field('message_html', 'soft', '', '10', htmlspecialchars(empty($_POST['message_html']) ? '' : stripslashes($_POST['message_html']), ENT_COMPAT, CHARSET, true),
                            'id="message_html" class="editorHook"'); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <td class="main" style="vertical-align: top"><?php echo TEXT_MESSAGE; ?></td>
                <td colspan="3"><?php echo zen_draw_textarea_field('message', 'soft', '', '10', htmlspecialchars(empty($_POST['message']) ? '' : stripslashes($_POST['message']), ENT_COMPAT, CHARSET, true), 'id="message" class="noEditor tt"'); ?></td>
            </tr>
        </table>
        <div class="form-group">
            <div class="col-sm-12 text-right">
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_SUBMIT; ?></button>
            </div>
        </div>
        <?php echo '</form>';
    }
    ?>
    <!-- body_text_eof //-->
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
