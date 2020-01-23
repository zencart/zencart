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
require 'includes/application_top.php';
if (isset($_POST['action']) && $_POST['action'] == 'login') {
  zen_redirect(zen_href_link(FILENAME_LOGIN));
}
// Slam prevention:
if (isset($_SESSION['login_attempt']) && $_SESSION['login_attempt'] > 9) {
  header('HTTP/1.1 406 Not Acceptable');
  exit(0);
}
$error = false;
$resetToken = '';
$email_message = '';
if (isset($_POST['action']) && $_POST['action'] == 'update') {
  if (!$_POST['admin_email']) {
    $error = true;
    $email_message = ERROR_WRONG_EMAIL_NULL;
  }
  $admin_email = zen_db_prepare_input($_POST['admin_email']);
  $sql = "SELECT admin_id, admin_name, admin_email, admin_pass, lockout_expires
          FROM " . TABLE_ADMIN . "
          WHERE admin_email = :admEmail:
          LIMIT 1";
  $sql = $db->bindVars($sql, ':admEmail:', $admin_email, 'string');
  $result = $db->Execute($sql);
  if (!($admin_email == $result->fields['admin_email'])) {
    $error = true;
    $email_message = MESSAGE_PASSWORD_SENT;
    $resetToken = 'bad';
  } else if ($result->fields['lockout_expires'] != 0) {
    header('HTTP/1.1 406 Not Acceptable');
    exit(0);
  }

  // BEGIN SLAM PREVENTION
  if ($_POST['admin_email'] != '') {
    if (!isset($_SESSION['login_attempt'])) {
      $_SESSION['login_attempt'] = 0;
    }
    $_SESSION['login_attempt']++;
  } // END SLAM PREVENTION

  if ($error == false) {
    $new_password = zen_create_PADSS_password((int)ADMIN_PASSWORD_MIN_LENGTH < 7 ? 7 : (int)ADMIN_PASSWORD_MIN_LENGTH);
    $resetToken = (time() + ADMIN_PWD_TOKEN_DURATION) . '}' . zen_encrypt_password($new_password);
    $sql = "UPDATE " . TABLE_ADMIN . "
            SET reset_token = :token:
            WHERE admin_id = " . (int)$result->fields['admin_id'];
    $sql = $db->bindVars($sql, ':token:', $resetToken, 'string');
    $db->Execute($sql);
    $html_msg['EMAIL_CUSTOMERS_NAME'] = $result->fields['admin_name'];
    $html_msg['EMAIL_MESSAGE_HTML'] = sprintf(TEXT_EMAIL_MESSAGE_PWD_RESET, $_SERVER['REMOTE_ADDR'], $new_password);
    zen_mail($result->fields['admin_name'], $result->fields['admin_email'], TEXT_EMAIL_SUBJECT_PWD_RESET, sprintf(TEXT_EMAIL_MESSAGE_PWD_RESET, $_SERVER['REMOTE_ADDR'], $new_password), STORE_NAME, EMAIL_FROM, $html_msg, 'password_forgotten_admin');
    $email_message = MESSAGE_PASSWORD_SENT;
  }
}
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/css/bootstrap.min.css">
    <link rel="stylesheet" href="includes/css/font-awesome.min.css">
    <link href="includes/css/login.css" rel="stylesheet">
    <meta name="robots" content="noindex, nofollow">
  </head>
  <body id="login">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-offset-1 col-md-offset-3 col-lg-offset-4 col-xs-12 col-sm-10 col-md-6 col-lg-4 text-center">
          <div class="login-main-div login-box-shadow">
            <?php echo zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT, HEADER_LOGO_WIDTH, HEADER_LOGO_HEIGHT, 'class="login-img"') . PHP_EOL; ?>
            <?php echo zen_draw_form('loginForm', FILENAME_PASSWORD_FORGOTTEN, '', 'post', 'id="loginForm" class="form-horizontal"', 'true') . PHP_EOL; ?>
            <h2><?php echo HEADING_TITLE; ?></h2>
            <?php if ($resetToken == '') { ?>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-lg fa-at"></i>
                  </span>
                  <?php echo zen_draw_input_field('admin_email', '', 'class="form-control input-lg" id="admin_email" autocapitalize="none" spellcheck="false" autocomplete="off" autofocus placeholder="' . TEXT_ADMIN_EMAIL . '"') . PHP_EOL; ?>
                </div>
              </div>
              <?php echo zen_draw_hidden_field('action', 'update') . PHP_EOL; ?>
              <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg"><?php echo TEXT_BUTTON_REQUEST_RESET; ?></button>
              </div>
              <div class="form-group">
                <a href="<?php echo zen_href_link(FILENAME_LOGIN); ?>" class="btn btn-default btn-lg" role="button"><?php echo TEXT_BUTTON_CANCEL; ?></a>
              </div>
            <?php } else { ?>
              <?php echo zen_draw_hidden_field('action', 'login') . PHP_EOL; ?>
              <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg"><?php echo TEXT_BUTTON_LOGIN; ?></button>
              </div>
            <?php } ?>
            <br class="clearBoth">
            <?php if ($email_message) { ?>
              <p class="login-alert-warning alert alert-warning"><?php echo $email_message; ?></p>
            <?php } ?>
            <?php echo '</form>' . PHP_EOL; ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>

<?php
require 'includes/application_bottom.php';
