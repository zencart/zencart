<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Sun Jul 14 21:04:37 2013 +0100 Modified in v1.5.2 $
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link href="includes/stylesheet.css" rel="stylesheet" type="text/css" />
<meta name="robots" content="noindex, nofollow" />
<script language="javascript" type="text/javascript"><!--
function animate(f)
{
  var button = document.getElementById("btn_submit");
  var img = document.getElementById("actionImg");
  button.style.cursor="wait";
  button.disabled = true;
  button.className = 'hiddenField';
  img.className = '';
  return true;
}
//--></script>
</head>
<?php if (!isset($expired) || $expired == FALSE) { ?>
<body id="login" onload="document.getElementById('admin_name').focus()">
<form id="loginForm" name="loginForm" action="<?php echo zen_href_link(FILENAME_LOGIN, zen_get_all_get_params(), 'SSL'); ?>" method="post" onsubmit="animate(this)">
  <fieldset>
    <legend><?php echo HEADING_TITLE; ?></legend>
    <label class="loginLabel" for="admin_name"><?php echo TEXT_ADMIN_NAME; ?></label>
    <input style="float: left" type="text" id="admin_name" name="admin_name" value="<?php echo zen_output_string($admin_name); ?>" autocomplete="off" />
    <br class="clearBoth" />
    <label  class="loginLabel" for="admin_pass"><?php echo TEXT_ADMIN_PASS; ?></label>
    <input style="float: left" type="password" id="admin_pass" name="admin_pass" value="" autocomplete="off" />
    <br class="clearBoth" />
    <p class="messageStackError"><?php echo $message; ?></p>
    <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
    <input type="submit" name="submit" class="button" value="Login" id="btn_submit"/>
    <input type="hidden" name="action" value="do<?php echo $_SESSION['securityToken']; ?>" id="action1"/>
    <img id="actionImg" src = "images/loading.gif" class="hiddenField" />
    <br /><a style="float: right;" href="<?php echo zen_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL');?>"><?php echo TEXT_PASSWORD_FORGOTTEN; ?></a>
  </fieldset>
</form>
<div id="loginExpiryPolicy"><?php echo LOGIN_EXPIRY_NOTICE; ?></div>
</body>
<?php } else { ?>
<body id="login" onload="document.getElementById('old_pwd').focus()">
<form id="loginForm" name="loginForm" action="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>" method="post" onsubmit="animate(this)">
  <fieldset>
    <legend><?php echo HEADING_TITLE_EXPIRED; ?></legend>
    <p class="messageStackError"><?php echo $message; ?></p>
    <label class="loginLabel" for="admin_name-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_NAME; ?></label>
    <input style="float: left" type="text" id="admin_name" name="admin_name-<?php echo $_SESSION['securityToken']; ?>" value="<?php echo zen_output_string($admin_name); ?>" autocomplete="off"/>
    <br class="clearBoth" />
    <label class="loginLabel" for="oldpwd-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_OLD_PASSWORD; ?></label>
    <input style="float: left" type="password" id="old_pwd" name="oldpwd-<?php echo $_SESSION['securityToken']; ?>" autocomplete="off" />
    <br class="clearBoth" />
    <label  class="loginLabel" for="newpwd-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_NEW_PASSWORD; ?></label>
    <input style="float: left" type="password" id="admin_pass" name="newpwd-<?php echo $_SESSION['securityToken']; ?>" autocomplete="off" />
    <br class="clearBoth" />
    <label  class="loginLabel" for="confpwd"-<?php echo $_SESSION['securityToken']; ?>><?php echo TEXT_ADMIN_CONFIRM_PASSWORD; ?></label>
    <input style="float: left" type="password" id="admin_pass2" name="confpwd-<?php echo $_SESSION['securityToken']; ?>" autocomplete="off" />
    <br class="clearBoth" />
    <input type="hidden" name="securityToken" value="<?php echo $_SESSION['securityToken']; ?>">
    <input type="submit" name="submit" class="button" value="Submit" id="btn_submit" />
    <input type="hidden" name="action" value="rs<?php echo $_SESSION['securityToken']; ?>" id="action1"/>
    <img id="actionImg" src = "images/loading.gif" class="hiddenField" />
  </fieldset>
</form>
</body>
<?php } ?>
</html>
<?php require('includes/application_bottom.php'); ?>
