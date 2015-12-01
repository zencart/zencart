<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce<br />
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Sep 12 2014 Modified in v1.5.4 $
 */

require('includes/application_top.php');

// Check if session has timed out
if (!isset($_SESSION['admin_id'])) zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));

// make a note of the current user - they can't delete themselves (by accident) or change their own status
$currentUser = $_SESSION['admin_id'];

// determine whether an action has been requested
if (isset($_POST['action']) && in_array($_POST['action'], array('insert','update','reset'))) {
  $action = $_POST['action'];
} elseif (isset($_GET['action']) && in_array($_GET['action'], array('add','edit','password','delete', 'delete_confirm'))) {
  $action = $_GET['action'];
} else {
  $action = '';
}

// if needed, check that a valid user id has been passed
if (($action == 'update' || $action == 'reset') && isset($_POST['user']))
{
  $user = $_POST['user'];
}
elseif (($action == 'edit' || $action == 'password' || $action == 'delete' || $action == 'delete_confirm') && $_GET['user'])
{
  $user = $_GET['user'];
}
elseif(($action=='delete' || $action=='delete_confirm') && isset($_POST['user']))
{
  $user = $_POST['user'];
}
elseif (in_array($action, array('edit','password','delete','delete_confirm','update','reset')))
{
  $messageStack->add_session(ERROR_NO_USER_DEFINED, 'error');
  zen_redirect(zen_href_link(FILENAME_USERS));
}

// act upon any specific action specified
switch ($action) {
  case 'add': // display unpopulated form for adding a new user
    $formAction = 'insert';
    $profilesList = array_merge(array(array('id'=>0,'text'=>TEXT_CHOOSE_PROFILE)), zen_get_profiles());
    break;
  case 'edit': // display populated form for editing existing user
    $formAction = 'update';
    $profilesList = array_merge(array(array('id'=>0,'text'=>TEXT_CHOOSE_PROFILE)), zen_get_profiles());
    break;
  case 'password': // display unpopulated form for resetting existing user's password
    $formAction = 'reset';
    break;
  case 'delete_confirm': // remove existing user from database
    if (isset($_POST['user']))
    {
       zen_delete_user($_POST['user']);
    }
    break;
  case 'insert': // insert new user into database. Post data is prep'd for db in the first function call
    $errors = zen_insert_user($_POST['name'],$_POST['email'], $_POST['password'], $_POST['confirm'], $_POST['profile'], $_POST['mobile']);
    if (sizeof($errors) > 0)
    {
      foreach ($errors as $error)
      {
        $messageStack->add($error, 'error');
      }
      $action = 'add';
      $formAction = 'insert';
      $profilesList = array_merge(array(array('id'=>0,'text'=>TEXT_CHOOSE_PROFILE)), zen_get_profiles());
    } else
    {
      $action = '';
      $messageStack->add(SUCCESS_NEW_USER_ADDED, 'success');
    }
    break;
  case 'update': // update existing user's details in database. Post data is prep'd for db in the first function call
    $errors = zen_update_user($_POST['name'],$_POST['email'], $_POST['id'], $_POST['profile'], $_POST['mobile']);
    if (sizeof($errors) > 0)
    {
      foreach ($errors as $error)
      {
        $messageStack->add($error, 'error');
      }
      $action = 'edit';
      $formAction = 'update';
      $profilesList = array_merge(array(array('id'=>0,'text'=>TEXT_CHOOSE_PROFILE)), zen_get_profiles());
    } else
    {
      $action = '';
      $messageStack->add(SUCCESS_USER_DETAILS_UPDATED, 'success');
    }
    break;
  case 'reset': // reset existing user's password in database. Post data is prep'd for db in the first function call
    $errors = zen_reset_password($_POST['user'], $_POST['password'], $_POST['confirm']);
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

// we'll always display a list of the available users
$userList = zen_get_users();
require('includes/admin_html_head.php');
?>
<link rel="stylesheet" type="text/css" href="includes/template/css/admin_access.css" />
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div id="pageWrapper">

<h1><?php echo HEADING_TITLE ?></h1>
<?php if ($action == 'edit' || $action == 'add' || $action == 'password') { ?>
<?php echo zen_draw_form('users', FILENAME_USERS); ?>
<?php if (isset($formAction)) echo zen_draw_hidden_field('action',$formAction) ?>
<?php } ?>
<?php if ($action == 'edit' || $action == 'password') echo zen_draw_hidden_field('user',$user) ?>
  <table cellspacing="0">
    <thead>
      <tr class="headingRow">
        <th class="id"><?php echo TEXT_ID ?></th>
        <th class="name"><?php echo TEXT_NAME ?></th>
        <th class="email"><?php echo TEXT_EMAIL ?></th>
        <th class="mobile"><?php echo TEXT_MOBILE ?></th>
        <th class="profile"><?php echo TEXT_PROFILE ?></th>
<?php if ($action == 'add' || $action == 'password') { ?>
        <th class="password"><?php echo TEXT_PASSWORD ?></th>
        <th class="password"><?php echo TEXT_CONFIRM_PASSWORD ?></th>
<?php } ?>
        <th class="actions">&nbsp;</th>
      </tr>
    </thead>
    <tfoot>
<?php if ($action != 'add' && $action != 'edit' && $action != 'password') { ?>
      <tr>
        <td colspan="5"><a href="<?php echo zen_href_link(FILENAME_USERS, 'action=add') ?>"><?php echo zen_image_button('button_add_user.gif', IMAGE_ADD_USER) ?></a></td>
      </tr>
<?php } ?>
    </tfoot>

    <tbody>
<?php if ($action == 'add') { ?>
      <tr>
        <td class="id">&nbsp;</td>
        <td class="name"><?php echo zen_draw_input_field('name', isset($_POST['name']) ? $_POST['name'] : '', 'class="field"', false, 'text', true) ?></td>
        <td class="email"><?php echo zen_draw_input_field('email', isset($_POST['email']) ? $_POST['email'] : '', 'class="field"', false, 'text', true) ?></td>
        <td class="mobile"><?php echo zen_draw_input_field('mobile', isset($_POST['mobile']) ? $_POST['mobile'] : '', 'class="field"', false, 'text', true) ?></td>
        <td class="profile"><?php echo zen_draw_pull_down_menu('profile', $profilesList, isset($_POST['profile']) ? $_POST['profile'] : 0) ?></td>
        <td class="password"><?php echo zen_draw_input_field('password', isset($_POST['password']) ? $_POST['password'] : '', ' class="field"', false, 'password'); ?></td>
        <td class="confirm"><?php echo zen_draw_input_field('confirm', isset($_POST['confirm']) ? $_POST['confirm'] : '', ' class="field"', false, 'password'); ?></td>
        <td class="actions"><?php echo zen_image_submit('button_insert.gif', IMAGE_INSERT) ?> <a href="<?php echo zen_href_link(FILENAME_USERS) ?>"> <?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL) ?></a></td>
      </tr>
<?php } ?>
<?php if (sizeof($userList) > 0) { ?>
<?php foreach ($userList as $userDetails) { ?>
      <tr>
<?php if (($action == 'edit' || $action == 'password') && $user == $userDetails['id']) { ?>
        <td class="id"><?php echo $userDetails['id'] ?><?php echo zen_draw_hidden_field('id', $userDetails['id']) ?></td>
<?php } else { ?>
        <td class="id"><?php echo $userDetails['id'] ?></td>
<?php } ?>
<?php if ($action == 'edit' && $user == $userDetails['id']) { ?>
        <td class="name"><?php echo zen_draw_input_field('name', $userDetails['name'], 'class="field"') ?></td>
        <td class="email"><?php echo zen_draw_input_field('email', $userDetails['email'], 'class="field"') ?></td>
        <td class="mobile"><?php echo zen_draw_input_field('mobile', $userDetails['mobile'], 'class="field"') ?></td>
<?php } else { ?>
        <td class="name"><?php echo $userDetails['name'] ?></td>
        <td class="email"><?php echo $userDetails['email'] ?></td>
        <td class="mobile"><?php echo $userDetails['mobile'] ?></td>
<?php } ?>
<?php if ($action == 'edit' && $user == $userDetails['id'] && $user != $currentUser) { ?>
        <td class="profile"><?php echo zen_draw_pull_down_menu('profile', $profilesList, $userDetails['profile']) ?></td>
<?php } else { ?>
        <td class="profile"><?php echo $userDetails['profileName'] ?></td>
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
          <a href="<?php echo zen_href_link(FILENAME_USERS) ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL) ?></a>
        </td>
<?php } else { ?>
        <td class="actions">&nbsp;</td>
<?php } ?>
<?php } elseif ($action != 'add') { ?>
        <td class="actions">
<?php if ($action != 'delete') { ?>
          <a href="<?php echo zen_href_link(FILENAME_USERS, 'action=edit&amp;user=' . $userDetails['id']) ?>"><?php echo zen_image_button('button_edit.gif', IMAGE_EDIT) ?></a>
          <a href="<?php echo zen_href_link(FILENAME_USERS, 'action=password&amp;user=' . $userDetails['id']) ?>"><?php echo zen_image_button('button_reset_pwd.gif', IMAGE_RESET_PWD) ?></a>
<?php } ?>
<?php if ($userDetails['id'] != $currentUser) {

  $btn_img = '';
  if ($action == 'delete' && $userDetails['id'] == $user) {
    $btn_img = 'button_confirm_red.gif';
  } else if ($action != 'delete') {
    $btn_img = 'button_delete.gif';
  }
?>
          <?php echo zen_draw_form('delete_user', FILENAME_USERS, 'action=' . ($action == 'delete' ? 'delete_confirm' : 'delete')); ?>
          <?php echo zen_draw_hidden_field('user', $userDetails['id']); ?>
          <?php echo ($action == 'delete' && $userDetails['id'] == $user ? TEXT_CONFIRM_DELETE : '') . ($btn_img == '' ? '' : zen_image_submit($btn_img, IMAGE_DELETE)) ?>
<?php if ($action == 'delete' && $userDetails['id'] == $user) { ?>
            <a href="<?php echo zen_href_link(FILENAME_USERS) ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL) ?></a>
<?php } ?>
          </form>
<?php } ?>
        </td>
      </tr>
<?php } } } else { ?>
      <tr>
        <td rowspan="4"><?php echo TEXT_NO_USERS_FOUND ?></td>
      </tr>
<?php } ?>
    </tbody>
  </table>

</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
