<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: DrByte 2020 Apr 15 Modified in v1.5.7 $
 */
require 'includes/application_top.php';

define('ADMIN_SWITCH_SEND_LOGIN_FAILURE_EMAILS', 'Yes'); // Can be set to 'No' if you don't want warning/courtesy emails to be sent after several login failures have occurred
// PCI-DSS / PA-DSS requirements for lockouts and intervals:
define('ADMIN_LOGIN_LOCKOUT_TIMER', (30 * 60));
define('ADMIN_PASSWORD_EXPIRES_INTERVAL', strtotime('- 90 day'));

//////////
$admin_name = $admin_pass = $message = "";
$errors = array();
$error = $expired = false;
if (isset($_POST['action']) && $_POST['action'] != '') {
  if ((!isset($_SESSION['securityToken']) || !isset($_POST['securityToken'])) || ($_SESSION['securityToken'] !== $_POST['securityToken'])) {
    $error = true;
    $message = ERROR_SECURITY_ERROR;
    zen_record_admin_activity(TEXT_ERROR_ATTEMPTED_ADMIN_LOGIN_WITHOUT_CSRF_TOKEN, 'warning');
  }
  if ($_POST['action'] == 'do' . $_SESSION['securityToken']) {
    $admin_name = zen_db_prepare_input($_POST['admin_name']);
    $admin_pass = zen_db_prepare_input($_POST['admin_pass']);
    if ($admin_name == '' && $admin_pass == '') {
      sleep(4);
      $error = true;
      $message = ERROR_WRONG_LOGIN;
      zen_record_admin_activity(TEXT_ERROR_ATTEMPTED_ADMIN_LOGIN_WITHOUT_USERNAME, 'warning');
    } else {
      list($error, $expired, $message, $redirect) = zen_validate_user_login($admin_name, $admin_pass);
      if ($redirect != '') {
        zen_redirect($redirect);
      }
    }
  } elseif ($_POST['action'] == 'rs' . $_SESSION['securityToken']) {
    $expired = true;
    $admin_name = zen_db_prepare_input($_POST['admin_name-' . $_SESSION['securityToken']]);
    $adm_old_pwd = zen_db_prepare_input($_POST['oldpwd-' . $_SESSION['securityToken']]);
    $adm_new_pwd = zen_db_prepare_input($_POST['newpwd-' . $_SESSION['securityToken']]);
    $adm_conf_pwd = zen_db_prepare_input($_POST['confpwd-' . $_SESSION['securityToken']]);

    $errors = zen_validate_pwd_reset_request($admin_name, $adm_old_pwd, $adm_new_pwd, $adm_conf_pwd);
    if (sizeof($errors) > 0) {
      $error = TRUE;
      foreach ($errors as $text) {
        $message .= '<br>' . $text;
      }
    } else {
      $message = SUCCESS_PASSWORD_UPDATED;
      list($error, $expired, $message, $redirect) = zen_validate_user_login($admin_name, $adm_new_pwd);
      if ($redirect != '') {
        zen_redirect($redirect);
      }
      zen_redirect(zen_href_link(FILENAME_DEFAULT));
    }
    if ($error) {
      sleep(3);
    }
  }
}
if ($expired && $message == '') {
  $message = sprintf(ERROR_PASSWORD_EXPIRED . ' ' . ERROR_PASSWORD_RULES, ((int)ADMIN_PASSWORD_MIN_LENGTH < 7 ? 7 : (int)ADMIN_PASSWORD_MIN_LENGTH));
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
            <?php if (!isset($expired) || $expired == FALSE) { ?>
              <?php echo zen_draw_form('loginForm', FILENAME_LOGIN, zen_get_all_get_params(), 'post', 'id="loginForm" class="form-horizontal"', 'true') . PHP_EOL; ?>
              <?php echo zen_draw_hidden_field('action', 'do' . $_SESSION['securityToken'], 'id="action1"') . PHP_EOL; ?>
              <h2><?php echo HEADING_TITLE; ?></h2>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-lg fa-user"></i>
                  </span>
                  <?php echo zen_draw_input_field('admin_name', zen_output_string($admin_name), 'class="form-control input-lg" id="admin_name-' . $_SESSION['securityToken'] . '" autocapitalize="none" spellcheck="false" autocomplete="username" autofocus placeholder="' . TEXT_ADMIN_NAME . '"') . PHP_EOL; ?>
                </div>
              </div>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon">
                    <i class="fa fa-lg fa-lock"></i>
                  </span>
                  <?php echo zen_draw_password_field('admin_pass', '', false, 'class="form-control input-lg" id="admin_pass" autocomplete="current-password" placeholder="' . TEXT_ADMIN_PASS . '"') . PHP_EOL; ?>
                </div>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg"><?php echo TEXT_SUBMIT; ?></button>
              </div>
              <div class="login-forgot text-right">
                <a href="<?php echo zen_href_link(FILENAME_PASSWORD_FORGOTTEN); ?>" class="btn btn-default btn-sm"><?php echo TEXT_PASSWORD_FORGOTTEN; ?></a>
              </div>
              <?php echo '</form>' . PHP_EOL; ?>
              <br class="clearBoth">
              <?php if ($message) { ?>
                <p class="login-alert-warning alert alert-warning"><?php echo $message; ?></p>
              <?php } ?>
              <div id="loginExpiryPolicy">
                <?php echo LOGIN_EXPIRY_NOTICE . PHP_EOL; ?>
              </div>
            <?php } else { ?>
              <?php echo zen_draw_form('loginForm', FILENAME_LOGIN, '', 'post', 'id="loginForm" class="form-horizontal"', 'true'); ?>
              <?php echo zen_draw_hidden_field('action', 'rs' . $_SESSION['securityToken'], 'id="action1"'); ?>
              <h2><?php echo HEADING_TITLE_EXPIRED; ?></h2>
              <?php if ($message) { ?>
                <p class="login-alert-warning alert alert-warning"><?php echo $message; ?></p>
              <?php } ?>
              <div class="form-group">
                <?php echo zen_draw_input_field('admin_name-' . $_SESSION['securityToken'], zen_output_string($admin_name), 'class="form-control input-lg" id="admin_name" autocapitalize="none" spellcheck="false" autocomplete="username" placeholder="' . TEXT_ADMIN_NAME . '"'); ?>
              </div>
              <div class="form-group">
                <?php echo zen_draw_password_field('oldpwd-' . $_SESSION['securityToken'], '', false, 'class="form-control input-lg" id="old_pwd" placeholder="' . TEXT_ADMIN_OLD_PASSWORD . '" autocomplete="current-password"'); ?>
              </div>
              <div class="form-group">
                <?php echo zen_draw_password_field('newpwd-' . $_SESSION['securityToken'], '', false, 'class="form-control input-lg" id="admin_pass" placeholder="' . TEXT_ADMIN_NEW_PASSWORD . '" autocomplete="new-password"'); ?>
              </div>
              <div class="form-group">
                <?php echo zen_draw_password_field('confpwd-' . $_SESSION['securityToken'], '', false, 'class="form-control input-lg" id="admin_pass2" placeholder="' . TEXT_ADMIN_CONFIRM_PASSWORD . '" autocomplete="new-password"'); ?>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary btn-lg"><?php echo TEXT_SUBMIT; ?></button>
              </div>
              <?php echo '</form>'; ?>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>

<?php
require 'includes/application_bottom.php';
