<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: dennisns7d 2019 May 12 Modified in v1.5.6b $
 */
// reset-token is good for only 24 hours:
define('ADMIN_PWD_TOKEN_DURATION', (24 * 60 * 60));

/////////
require('includes/application_top.php');
// demo active test
if (zen_admin_demo()) {
    $_GET['action'] = '';
    $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
}
if (isset($_POST['login'])) {
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}
// Slam prevention:
if ($_SESSION['login_attempt'] > 9) {
    header('HTTP/1.1 406 Not Acceptable');
    exit(0);
}
$error = false;
$reset_token = '';
$email_message = '';
if (isset($_POST['submit'])) {
    if (!$_POST['admin_email']) {
        $error = true;
        $email_message = ERROR_WRONG_EMAIL_NULL;
    }
    $admin_email = zen_db_prepare_input($_POST['admin_email']);
    $sql = "select admin_id, admin_name, admin_email, admin_pass from " . TABLE_ADMIN . " where admin_email = :admEmail: LIMIT 1";
    $sql = $db->bindVars($sql, ':admEmail:', $admin_email, 'string');
    $result = $db->Execute($sql);
    if (!($admin_email == $result->fields['admin_email'])) {
        $error = true;
        $email_message = MESSAGE_PASSWORD_SENT;
        $resetToken = 'bad';
    }
    // BEGIN SLAM PREVENTION
    if ($_POST['admin_email'] != '') {
        if (!isset($_SESSION['login_attempt'])) $_SESSION['login_attempt'] = 0;
        $_SESSION['login_attempt']++;
    } // END SLAM PREVENTION

    if ($error == false) {
        $new_password = zen_create_PADSS_password((int)ADMIN_PASSWORD_MIN_LENGTH < 7 ? 7 : (int)ADMIN_PASSWORD_MIN_LENGTH);
        $resetToken = (time() + ADMIN_PWD_TOKEN_DURATION) . '}' . zen_encrypt_password($new_password);
        $sql = "update " . TABLE_ADMIN . " set reset_token = :token: where admin_id = :admID: ";
        $sql = $db->bindVars($sql, ':token:', $resetToken, 'string');
        $sql = $db->bindVars($sql, ':admID:', $result->fields['admin_id'], 'string');
        $db->Execute($sql);
        $html_msg['EMAIL_CUSTOMERS_NAME'] = $result->fields['admin_name'];
        $html_msg['EMAIL_MESSAGE_HTML'] = sprintf(TEXT_EMAIL_MESSAGE_PWD_RESET, $_SERVER['REMOTE_ADDR'], $new_password);
        zen_mail($result->fields['admin_name'], $result->fields['admin_email'], TEXT_EMAIL_SUBJECT_PWD_RESET, sprintf(TEXT_EMAIL_MESSAGE_PWD_RESET, $_SERVER['REMOTE_ADDR'], $new_password), STORE_NAME, EMAIL_FROM, $html_msg, 'password_forgotten_admin');
        $email_message = MESSAGE_PASSWORD_SENT;
    }
}
?>
<!DOCTYPE html >
<html <?php echo HTML_PARAMS; ?>>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
  <link href="includes/alt-stylesheet.css" rel="stylesheet">
  <title>
    <?php echo TITLE; ?>

  </title>
  <meta name="robots" content="noindex, nofollow" />
</head>
<body id="login" onload="document.getElementById('admin_email').focus()">
  <div class="container-fluid">
    <div class="login-form">
      <div class="login-main-div login-box-shadow">
        <?php echo zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT, HEADER_LOGO_WIDTH, HEADER_LOGO_HEIGHT, 'class="login-img"'); ?>
        <br>
        <?php echo zen_draw_form('loginForm', FILENAME_PASSWORD_FORGOTTEN, 'action=update', 'post', 'id="loginForm"', 'true'); ?>

        <h2>
          <?php echo HEADING_TITLE; ?>

        </h2>
        <?php if ($resetToken == '') { ?>

        <div class="form-group">
          <?php echo zen_draw_input_field('admin_email', '', 'class="form-control" id="admin_email" autocapitalize="none" spellcheck="false" autocomplete="off" placeholder="' . TEXT_ADMIN_EMAIL . '"'); ?>

        </div>
        <?php } ?>
        <?php if ($resetToken == '') { ?>
 
        <div class="form-group">
          <?php echo zen_draw_input_field('submit', TEXT_BUTTON_REQUEST_RESET, 'class="btn btn-primary"', false, 'submit'); ?>

        </div>
        <div class="form-group">
          <?php echo zen_draw_input_field('login', TEXT_BUTTON_CANCEL, 'class="btn btn-secondary"', false, 'submit'); ?>

        </div>
        <?php } else { ?>

        <div class="form-group">
          <?php echo zen_draw_input_field('login', TEXT_BUTTON_LOGIN, 'class="btn btn-primary"', false, 'submit'); ?>

        </div>
        <?php } ?>

        <br class="clearBoth" />
        <?php if ($email_message) { ?>

        <p class="login-alert-warning">
          <?php echo $email_message; ?>

        </p>
        <?php } ?>

        </form>
      </div>
    </div>
  </div>
</body>
</html>

<?php require('includes/application_bottom.php');
