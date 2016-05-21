<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: bislewl  Tue Feb 16 23:14:29 2016 -0600 Modified in v1.5.5 $
 *
 * @TODO - add jquery validation to the password-reset fields, to show when passwords don't match
 * @TODO - add password-strength indicator
 */
require ('includes/application_top.php');

define('ADMIN_SWITCH_SEND_LOGIN_FAILURE_EMAILS', 'Yes'); // Can be set to 'No' if you don't want warning/courtesy emails to be sent after several login failures have occurred

// PCI-DSS / PA-DSS requirements for lockouts and intervals:
define('ADMIN_LOGIN_LOCKOUT_TIMER', (30 * 60));
define('ADMIN_PASSWORD_EXPIRES_INTERVAL', strtotime('- 90 day'));

//////////
$admin_name = $admin_pass = $message = "";
$errors = array();
$error = $expired = false;
if (isset($_POST['action']) && $_POST['action'] != '')
{
  if ((! isset($_SESSION['securityToken']) || ! isset($_POST['securityToken'])) || ($_SESSION['securityToken'] !== $_POST['securityToken']))
  {
    $error = true;
    $message = ERROR_SECURITY_ERROR;
    zen_record_admin_activity(TEXT_ERROR_ATTEMPTED_ADMIN_LOGIN_WITHOUT_CSRF_TOKEN, 'warning');
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
      zen_record_admin_activity(TEXT_ERROR_ATTEMPTED_ADMIN_LOGIN_WITHOUT_USERNAME, 'warning');
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
      zen_redirect(zen_admin_href_link(FILENAME_DEFAULT));
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
<title><?php echo ADMIN_TITLE; ?></title>
<link href="includes/template/css/stylesheet.css" rel="stylesheet" type="text/css"/>
<link href="includes/template/css/login.css" rel="stylesheet" type="text/css" />
<meta name="robots" content="noindex, nofollow" />
</head>
    <?php if (!isset($expired) || $expired == FALSE) { ?>
        <body id="login">
        <div class="container-fluid">
            <div class="row">
            <div id="loginFormDiv" class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
                <?php
                echo zen_draw_form('loginForm',FILENAME_LOGIN,zen_get_all_get_params(),'post','id="loginForm" class="form-horizontal"','true');
                echo zen_draw_hidden_field('action','do'.$_SESSION['securityToken'],'id="action1"');
                ?>
                  <fieldset>
                    <legend><?php echo HEADING_TITLE; ?></legend>
                    <div class="row">
                        <div class="col-xs-12 col-sm-10 col-offset-sm-1 col-md-10 col-offset-md-2">
                          <div class="form-group">
                            <label class="col-xs-4 col-offset-xs-1 col-sm-4 col-md-6 control-label" for="admin_name-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_NAME; ?>:</label>
                            <div class="col-xs-6 col-sm-7 col-md-6">
                              <?php echo zen_draw_input_field('admin_name', zen_output_string($admin_name), 'class="form-control" id="admin_name" autocomplete="off" autofocus placeholder="' . TEXT_ADMIN_NAME . '"'); ?>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-xs-4 col-offset-xs-1 col-sm-4 col-md-6 control-label" for="admin_pass"><?php echo TEXT_ADMIN_PASS; ?>:</label>
                            <div class="col-xs-6 col-sm-7 col-md-6">
                            <?php echo zen_draw_password_field('admin_pass', '', false, 'class="form-control" id="admin_pass" placeholder="' . TEXT_ADMIN_PASS . '"', false); ?>
                            </div>
                          </div>
                        </div>
                    </div>
                    <div class="form-group">
                      <div class="col-xs-12">
                        <?php echo zen_draw_input_field('submit', TEXT_SUBMIT, 'id="btn_submit" class="button btn btn-default"', false, 'submit'); ?>
                      </div>
                    </div>
                    <br class="clearBoth"/>
                    <p class="messageStackError"><?php echo $message; ?></p>
                    <img id="actionImg" src="images/loading.gif" class="hiddenField"/>
                    <br/><a href="<?php echo zen_admin_href_link(FILENAME_PASSWORD_FORGOTTEN); ?>"><?php echo TEXT_PASSWORD_FORGOTTEN; ?></a>
                </fieldset>
                </form>
                <div id="loginExpiryPolicy"><?php echo LOGIN_EXPIRY_NOTICE; ?></div>
            </div>
            </div>
          </div>
        </body>
    <?php } else { ?>
        <body id="login">
        <div class="container-fluid">
            <div class="row">
            <div id="loginFormDiv" class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
                <?php
                echo zen_draw_form('loginForm',FILENAME_LOGIN,'','post','id="loginForm" class="form-horizontal"','true');
                echo zen_draw_hidden_field('action','rs'.$_SESSION['securityToken'],'id="action1"');
                ?>
                <fieldset>
                    <legend><?php echo HEADING_TITLE_EXPIRED; ?></legend>
                    <div class="messageStackError"><?php echo $message; ?></div>
                    <br class="clearBoth"/>
                    <div class="row">
                        <div class="col-xs-12 col-sm-10 col-offset-sm-1 col-md-10 col-offset-md-2">
                          <div class="form-group">
                            <label class="col-xs-4 col-offset-xs-1 col-sm-4 col-md-6 control-label" for="admin_name-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_NAME; ?>:</label>
                            <div class="col-xs-6 col-sm-7 col-md-6">
                              <?php echo zen_draw_input_field('admin_name-' . $_SESSION['securityToken'], zen_output_string($admin_name), 'class="form-control" id="admin_name" autocomplete="off" placeholder="' . TEXT_ADMIN_NAME . '"'); ?>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-xs-4 col-offset-xs-1 col-sm-4 col-md-6 control-label" for="oldpwd-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_OLD_PASSWORD; ?>:</label>
                            <div class="col-xs-6 col-sm-7 col-md-6">
                            <?php echo zen_draw_password_field('oldpwd-'.$_SESSION['securityToken'], '', false, 'class="form-control" id="old_pwd" placeholder="' . TEXT_ADMIN_OLD_PASSWORD . '"',false); ?>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-xs-4 col-offset-xs-1 col-sm-4 col-md-6 control-label" for="newpwd-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_NEW_PASSWORD; ?>:</label>
                            <div class="col-xs-6 col-sm-7 col-md-6">
                            <?php echo zen_draw_password_field('newpwd-'.$_SESSION['securityToken'], '', false, 'class="form-control" id="admin_pass" placeholder="' . TEXT_ADMIN_NEW_PASSWORD . '"',false); ?>
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="col-xs-4 col-offset-xs-1 col-sm-4 col-md-6 control-label" for="confpwd-<?php echo $_SESSION['securityToken']; ?>"><?php echo TEXT_ADMIN_CONFIRM_PASSWORD; ?>:</label>
                            <div class="col-xs-6 col-sm-7 col-md-6">
                            <?php echo zen_draw_password_field('confpwd-'.$_SESSION['securityToken'], '', false, 'class="form-control" id="admin_pass2" placeholder="' . TEXT_ADMIN_CONFIRM_PASSWORD . '"',false); ?>
                            </div>
                          </div>
                        </div>
                    </div>
                    <div class="form-group">
                      <div class="col-xs-12">
                        <?php echo zen_draw_input_field('submit', TEXT_SUBMIT, 'id="btn_submit" class="button btn btn-default"', false, 'submit'); ?>
                      </div>
                    </div>
                    <img id="actionImg" src="images/loading.gif" class="hiddenField"/>
                </fieldset>
                </form>
            </div>
            </div>
        </div>
        </body>
    <?php } ?>
    </html>
<?php require('includes/application_bottom.php');
