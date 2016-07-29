<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt   Modified in v1.6.0 $
 */

require('includes/application_top.php');
if (file_exists(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . 'users.php')) {
  include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . 'users.php');
}
// Check if session has timed out
if (!isset($_SESSION['admin_id'])) zen_redirect(zen_admin_href_link(FILENAME_LOGIN));
$user = $_SESSION['admin_id'];

// determine whether an action has been requested
if (isset($_POST['action']) && in_array($_POST['action'], array('update','reset'))) {
  $action = $_POST['action'];
} elseif (isset($_GET['action']) && in_array($_GET['action'], array('edit','password'))) {
  $action = $_GET['action'];
} else {
  $action = '';
}
// validate form input as not expired and not spoofed
if ($action != '' && isset($_POST['action']) && $_POST['action'] != '' && $_POST['securityToken'] != $_SESSION['securityToken']) {
  $messageStack->add_session(ERROR_TOKEN_EXPIRED_PLEASE_RESUBMIT, 'error');
  zen_redirect(zen_admin_href_link(FILENAME_ADMIN_ACCOUNT));
}

// act upon any specific action specified
switch ($action) {
  case 'edit': // display populated form for editing existing user
    $formAction = 'update';
    $profilesList = array_merge(array(array('id'=>0,'text'=>'Choose Profile')), zen_get_profiles());
    break;
  case 'password': // display unpopulated form for resetting existing user's password
    $formAction = 'reset';
    break;
  case 'update': // update existing user's details in database. Post data is prep'd for db in the first function call
    $errors = zen_update_user(FALSE, $_POST['email'], $_SESSION['admin_id'], null);
    if (sizeof($errors) > 0)
    {
      foreach ($errors as $error)
      {
        $messageStack->add($error, 'error');
      }
      $action = 'edit';
      $formAction = 'update';
      $profilesList = array_merge(array(array('id'=>0,'text'=>'Choose Profile')), zen_get_profiles());
    } else
    {
      $action = '';
      $messageStack->add(SUCCESS_USER_DETAILS_UPDATED, 'success');
    }
    break;
  case 'reset': // reset existing user's password in database. Post data is prep'd for db in the first function call
    $errors = zen_reset_password($_SESSION['admin_id'], $_POST['password'], $_POST['confirm']);
    if (sizeof($errors) > 0)
    {
      foreach ($errors as $error)
    {
      $messageStack->add($error, 'error');
    }
    $action = 'password';
    $formAction = 'reset';
    } else
    {
      $action = '';
      $messageStack->add(SUCCESS_PASSWORD_UPDATED, 'success');
    }
    break;
  default: // no action, simply drop through and display existing users
}

// get this user's details
$userList = zen_get_users($_SESSION['admin_id']);
$userDetails = $userList[0];

require('includes/admin_html_head.php');
?>
<link rel="stylesheet" type="text/css" href="includes/admin_access.css" />
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div id="pageWrapper">

  <h1><?php echo HEADING_TITLE ?></h1>

<form action="<?php echo zen_admin_href_link(FILENAME_ADMIN_ACCOUNT) ?>" method="post">
<?php if (isset($formAction)) echo zen_draw_hidden_field('action',$formAction) . zen_draw_hidden_field('securityToken', $_SESSION['securityToken']); ?>
  <table cellspacing="0">
    <tr class="headingRow">
      <th class="name"><?php echo TEXT_ADMIN_NAME ?></th>
      <th class="email"><?php echo TEXT_EMAIL ?></th>
<?php if ($action == 'password') { ?>
      <th class="password"><?php echo TEXT_ADMIN_NEW_PASSWORD ?></th>
      <th class="password"><?php echo TEXT_ADMIN_CONFIRM_PASSWORD ?></th>
<?php } ?>
      <th class="actions">&nbsp;</th>
    </tr>
    <tr>
      <td class="name"><?php echo $userDetails['name'] ?><?php echo zen_draw_hidden_field('admin_name', $userDetails['name']); ?></td>
<?php if ($action == 'edit' && $user == $userDetails['id']) { ?>
      <td class="email"><?php echo zen_draw_input_field('email', $userDetails['email'], 'class="field"', false, 'email', true) ?></td>
<?php } else { ?>
      <td class="email"><?php echo $userDetails['email'] ?></td>
<?php } ?>
<?php if ($action == 'password' && $user == $userDetails['id']) { ?>
    <td class="password"><?php echo zen_draw_input_field('password', '', 'class="field"', false, 'password', true) ?></td>
    <td class="confirm"><?php echo zen_draw_input_field('confirm', '', 'class="field"', false, 'password', true) ?></td>
<?php } elseif($action == 'add' || $action == 'password') { ?>
      <td class="password">&nbsp;</td>
      <td class="confirm">&nbsp;</td>
<?php } ?>
<?php if ($action == 'edit' || $action == 'password') { ?>
<?php if ($user == $userDetails['id']) { ?>
      <td class="actions">
        <?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE) ?>
        <a href="<?php echo zen_admin_href_link(FILENAME_ADMIN_ACCOUNT) ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL) ?></a>
      </td>
<?php } else { ?>
      <td class="actions">&nbsp;</td>
<?php } ?>
<?php } else { ?>
      <td class="actions">
        <a href="<?php echo zen_admin_href_link(FILENAME_ADMIN_ACCOUNT, 'action=edit') ?>"><?php echo zen_image_button('button_edit.gif', IMAGE_EDIT) ?></a>
        <a href="<?php echo zen_admin_href_link(FILENAME_ADMIN_ACCOUNT, 'action=password') ?>"><?php echo zen_image_button('button_reset_pwd.gif', IMAGE_RESET_PWD) ?></a>
      </td>
    </tr>
<?php } ?>
  </table>
</form>

</div>
<!-- body_eof //-->

<div class="bottom">
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</div>
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
