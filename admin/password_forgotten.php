<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Sun Jul 14 21:04:37 2013 +0100 Modified in v1.5.2 $
 */
// reset-token is good for only 24 hours:
define('ADMIN_PWD_TOKEN_DURATION', (24 * 60 * 60) );

/////////
require ('includes/application_top.php');
// demo active test
if (zen_admin_demo())
{
  $_GET['action'] = '';
  $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
}
if (isset($_POST['login']))
{
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
if (isset($_POST['submit']))
{
  if (! $_POST['admin_email'])
  {
    $error = true;
    $email_message = ERROR_WRONG_EMAIL_NULL;
  }
  $admin_email = zen_db_prepare_input($_POST['admin_email']);
  $sql = "select admin_id, admin_name, admin_email, admin_pass from " . TABLE_ADMIN . " where admin_email = :admEmail: LIMIT 1";
  $sql = $db->bindVars($sql, ':admEmail:', $admin_email, 'string');
  $result = $db->Execute($sql);
  if (! ($admin_email == $result->fields['admin_email']))
  {
    $error = true;
    $email_message = MESSAGE_PASSWORD_SENT;
    $resetToken = 'bad';
  }
  // BEGIN SLAM PREVENTION
  if ($_POST['admin_email'] != '')
  {
    if (! isset($_SESSION['login_attempt'])) $_SESSION['login_attempt'] = 0;
    $_SESSION['login_attempt'] ++;
  } // END SLAM PREVENTION

  if ($error == false)
  {
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
<title><?php echo TITLE; ?></title>
<link href="includes/stylesheet.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<meta name="robots" content="noindex, nofollow"/>
</head>
<body id="login" onload="document.getElementById('admin_email').focus()">
  <div class="container">
    <div class="col-xs-12 col-sm-12 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4">
      <form id="loginForm" action="<?php echo zen_href_link(FILENAME_PASSWORD_FORGOTTEN, 'action=update', 'SSL'); ?>" method="post">
      <?php echo zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
        <fieldset>
          <legend><?php echo HEADING_TITLE; ?></legend>
          <?php if ($resetToken == '') { ?>
            <label for="admin_email"><?php echo TEXT_ADMIN_EMAIL; ?></label>
            <input class="left inline" type="text" id="admin_email" name="admin_email" value="" autocomplete="off" autofocus="autofocus">
          <?php } ?>
          <p class="messageStackSuccess"><?php echo $email_message; ?></p>
          <?php if ($resetToken == '') { ?>
          <input type="submit" name="submit" class="button" value="<?php echo TEXT_BUTTON_REQUEST_RESET; ?>">
          <input type="submit" name="login" class="button" value="<?php echo TEXT_BUTTON_CANCEL; ?>">
          <?php } else { ?>
          <input type="submit" name="login" class="button" value="<?php echo TEXT_BUTTON_LOGIN; ?>">
          <?php } ?>
        </fieldset>
      </form>
    </div>
</div>
</body>
</html>
<?php require('includes/application_bottom.php');

