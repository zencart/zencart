<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: login.php 19296 2011-07-28 18:33:38Z wilt $
 *
 * @TODO - add jquery validation to the password-reset fields, to show when passwords don't match
 * @TODO - add optional PCI-compliance indicator to password field, to show when password isn't compliant
 */
define('ADMIN_SWITCH_SEND_LOGIN_FAILURE_EMAILS', 'Yes'); // Can be set to 'No' if you don't want warning/courtesy emails to be sent after several login failures have occurred

// PCI-DSS / PA-DSS requirements for lockouts and intervals:
define('ADMIN_LOGIN_LOCKOUT_TIMER', (30 * 60));
define('ADMIN_PASSWORD_EXPIRES_INTERVAL', strtotime('- 90 day'));

//////////
require ('includes/application_top.php');
$admin_name = $admin_pass = $message = "";
$errors = array();
$error = $expired = false;
if (isset($_POST['action']) && $_POST['action'] != '')
{
  if ((! isset($_SESSION['securityToken']) || ! isset($_POST['securityToken'])) || ($_SESSION['securityToken'] !== $_POST['securityToken']))
  {
    $error = true;
    $message = ERROR_SECURITY_ERROR;
  }
  if ($_POST['action'] == 'do' . $_SESSION['securityToken'])
  {
    $admin_name = zen_db_prepare_input($_POST['admin_name']);
    $admin_pass = zen_db_prepare_input($_POST['admin_pass']);
    if ($admin_name == '' && $admin_pass == '')
    {
      sleep(4);
      $error = true;
      $message = ERROR_WRONG_LOGIN;
    } else
    {
      list($error, $expired, $message, $redirect) = zen_validate_user_login($admin_name, $admin_pass);
      if ($redirect != '') zen_redirect($redirect);
    }
  } elseif ($_POST['action'] == 'rs' . $_SESSION['securityToken'])
  {
    $expired = true;
    $admin_name = zen_db_prepare_input($_POST['admin_name-' . $_SESSION['securityToken']]);
    $adm_old_pwd = zen_db_prepare_input($_POST['oldpwd-' . $_SESSION['securityToken']]);
    $adm_new_pwd = zen_db_prepare_input($_POST['newpwd-' . $_SESSION['securityToken']]);
    $adm_conf_pwd = zen_db_prepare_input($_POST['confpwd-' . $_SESSION['securityToken']]);

    $errors = zen_validate_pwd_reset_request($admin_name, $adm_old_pwd, $adm_new_pwd, $adm_conf_pwd);
    if (sizeof($errors) > 0)
    {
      $error = TRUE;
      foreach ($errors as $text)
      {
        $message .= '<br />' . $text;
      }
    } else
    {
      $message = SUCCESS_PASSWORD_UPDATED;
      list($error, $expired, $message, $redirect) = zen_validate_user_login($admin_name, $adm_new_pwd);
      if ($redirect != '') zen_redirect($redirect);
      zen_redirect(zen_href_link(FILENAME_DEFAULT, '', 'SSL'));
    }
    if ($error) sleep(3);
  }
}
if ($expired && $message == '') $message = sprintf(ERROR_PASSWORD_EXPIRED . ' ' . ERROR_PASSWORD_RULES, ((int)ADMIN_PASSWORD_MIN_LENGTH < 7 ? 7 : (int)ADMIN_PASSWORD_MIN_LENGTH));
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/template/css/foundation.min.css">
<link href="includes/template/css/login.css" rel="stylesheet" type="text/css" />
<meta name="robots" content="noindex, nofollow" />
<script language="javascript" type="text/javascript"><!--
//--></script>
</head>
<?php if (!isset($expired) || $expired == FALSE) { ?>
<body id="login" >
  <div class="container">
    <div class="row">
    <div class="small-8 columns small-centered">

    <form id="loginForm" name="loginForm" action="<?php echo zen_href_link(FILENAME_LOGIN, zen_get_all_get_params(), 'SSL'); ?>" method="post">
      <fieldset>
        <legend><?php echo HEADING_TITLE; ?></legend>
        <div class="row">
          <div class="small-3 columns">
            <label class="left inline" for="admin_name"><?php echo TEXT_ADMIN_NAME; ?></label>
          </div>
          <div class="small-9 columns">
            <input type="text" id="admin_name" name="admin_name" value="<?php echo zen_output_string($admin_name); ?>" autocomplete="off" autofocus="autofocus" />
          </div>
        </div>
        <div class="row">
          <div class="small-3 columns">
            <label class="left inline" for="admin_pass"><?php echo TEXT_ADMIN_PASS; ?></label>
          </div>
          <div class="small-9 columns">
            <input type="password" id="admin_pass" name="admin_pass" value="" autocomplete="off" />
          </div>
        </div>
        <p class="messageStackError"><?php echo $message; ?></p>
        <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
        <input type="submit" name="submit" class="button" value="Login" id="btn_submit"/>
        <input type="hidden" name="action" value="do<?php echo $_SESSION['securityToken']; ?>" id="action1"/>
        <br /><a class="right" href="<?php echo zen_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL');?>"><?php echo TEXT_PASSWORD_FORGOTTEN; ?></a>
      </fieldset>
    </form>
    <div id="loginExpiryPolicy">
      <?php if (PADSS_ADMIN_SESSION_TIMEOUT_ENFORCED == 1 && SESSION_TIMEOUT_ADMIN > 900) {
        $sessTimeAdm = 15;
      } else {
        $sessTimeAdm = round(SESSION_TIMEOUT_ADMIN/60);
      }
        echo sprintf(LOGIN_EXPIRY_NOTICE, $sessTimeAdm);
      ?>
    </div>
    <?php if (PADSS_PWD_EXPIRY_ENFORCED == 1) { ?>
      <div id="loginPwdExpiryPolicy"><?php echo LOGIN_PASSWORD_EXPIRY_NOTICE; ?></div>
    <?php } ?>
    </div>
  </div>
</div>
</body>
<?php } else { ?>
<body id="login">
  <div class="container">
    <div class="row">
    <div class="small-12 columns small-centered">
      <form id="loginForm" name="loginForm" action="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>" method="post">
        <fieldset>
          <legend><?php echo HEADING_TITLE_EXPIRED; ?></legend>
          <p class="messageStackError"><?php echo $message; ?></p>
          <div class="row">
            <div class="small-3 columns">
              <label class="left inline" for="admin_name-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_NAME; ?></label>
            </div>
            <div class="small-9 columns">
              <input type="text" id="admin_name" name="admin_name-<?php echo $_SESSION['securityToken']; ?>" value="<?php echo zen_output_string($admin_name); ?>" autocomplete="off"/>
            </div>
          </div>
          <div class="row">
            <div class="small-3 columns">
              <label class="left inline" for="oldpwd-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_OLD_PASSWORD; ?></label>
            </div>
            <div class="small-9 columns">
              <input type="password" id="old_pwd" name="oldpwd-<?php echo $_SESSION['securityToken']; ?>" autocomplete="off" autofocus="autofocus" />
            </div>
          </div>
          <div class="row">
            <div class="small-3 columns">
              <label class="left inline" for="newpwd-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_NEW_PASSWORD; ?></label>
            </div>
            <div class="small-9 columns">
              <input style="float: left" type="password" id="admin_pass" name="newpwd-<?php echo $_SESSION['securityToken']; ?>" autocomplete="off" />
            </div>
          </div>
          <div class="row">
            <div class="small-3 columns">
              <label class="left inline" for="confpwd"-<?php echo $_SESSION['securityToken']; ?>><?php echo TEXT_ADMIN_CONFIRM_PASSWORD; ?></label>
            </div>
            <div class="small-9 columns">
              <input type="password" id="admin_pass2" name="confpwd-<?php echo $_SESSION['securityToken']; ?>" autocomplete="off" />
            </div>
          </div>
          <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
          <input type="submit" name="submit" class="button" value="Submit" id="btn_submit" />
          <input type="hidden" name="action" value="rs<?php echo $_SESSION['securityToken']; ?>" id="action1"/>
        </fieldset>
      </form>
    </div>
  </div>
</div>
</body>
<?php } ?>
</html>
<?php require('includes/application_bottom.php'); ?>
