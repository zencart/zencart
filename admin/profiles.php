<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: profiles.php 19326 2011-08-02 22:11:40Z kuroi $
 */

require('includes/application_top.php');

// determine whether an action has been requested
if (isset($_POST['action']) && in_array($_POST['action'], array('insert','update','update_name')))
{
  $action = $_POST['action'];
} elseif (isset($_GET['action']) && in_array($_GET['action'], array('add','edit','rename','delete'))) {
  $action = $_GET['action'];
} else {
  $action = '';
}

// if needed, check that a valid profile id has been passed
if (isset($action) && ($action == 'update' || $action == 'update_name') && $_POST['profile'])
{
  $profile = $_POST['profile'];
}
elseif (isset($action) && ($action == 'edit' || $action == 'delete') && $_GET['profile'])
{
  $profile = $_GET['profile'];
}
elseif (in_array($action, array('edit','delete','update','update-name')))
{
  $messageStack->add_session(ERROR_NO_PROFILE_DEFINED, 'error');
  zen_redirect(zen_href_link(FILENAME_PROFILES));
}

// take appropriate steps depending upon the action requested
switch ($action) {
  case 'add':
    $pagesByMenu = zen_get_admin_pages(FALSE);
    $menuTitles = zen_get_menu_titles();
    break;
  case 'edit':
    $pagesByMenu = zen_get_admin_pages(FALSE);
    $menuTitles = zen_get_menu_titles();
    $profileName = zen_get_profile_name($profile);
    $permittedPages = zen_get_permitted_pages_for_profile($profile);
    break;
  case 'delete':
    $error = zen_delete_profile($profile);
    if ($error != '')
    {
      $messageStack->add_session($error, 'error');
      zen_redirect(zen_href_link(FILENAME_PROFILES));
    }
    else
    {
      $messageStack->add(SUCCESS_PROFILE_DELETED, 'success');
      unset($action);
      $profileList = zen_get_profiles(TRUE);
    }
    break;
  case 'insert':
    $error = zen_create_profile($_POST);
    if ($error != '')
    {
      $messageStack->add($error, 'error');
      $pagesByMenu = zen_get_admin_pages(FALSE);
      $action = 'add';
    }
    else
    {
      $messageStack->add_session(SUCCESS_PROFILE_INSERTED, 'success');
      zen_redirect(zen_href_link(FILENAME_PROFILES));
    }
    break;
  case 'update':
    zen_remove_profile_permits($profile);
    zen_insert_pages_into_profile($profile, $_POST['p']);
    $messageStack->add_session(SUCCESS_PROFILE_UPDATED, 'success');
    zen_redirect(zen_href_link(FILENAME_PROFILES));
    break;
  case 'update_name':
    zen_update_profile_name($profile, $_POST['profile-name']);
    $messageStack->add_session(SUCCESS_PROFILE_NAME_UPDATED, 'success');
    zen_redirect(zen_href_link(FILENAME_PROFILES));
    break;
  case 'rename':
  default: // if no specific action requested prepare the listing data
    $profileList = zen_get_profiles(TRUE);
    break;
}
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<link rel="stylesheet" type="text/css" href="includes/admin_access.css">
<script type="text/javascript" src="includes/menu.js"></script>
<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  function checkAll(form,header,value){
    for (var i = 0; i < form.elements.length; i++){
      if (form.elements[i].className == header){
        form.elements[i].checked = value;
      }
    }
  }
  // -->
</script>
</head>
<body onload="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div id="pageWrapper">

<?php if (!isset($action) || $action == '' || $action == 'rename') { ?>

  <h1><?php echo HEADING_TITLE_ALL_PROFILES ?></h1>

  <table cellspacing="0">
    <thead>
      <tr>
        <th class="id"><?php echo TEXT_ID ?></th>
        <th class="name"><?php echo TEXT_NAME ?></th>
        <th class="users"><?php echo TEXT_USERS ?></th>
        <th class="actions">&nbsp;</th>
      </tr>
    </head>

<?php if ($action != 'rename') { ?>
    <tfoot>
      <tr>
        <td colspan="4"><a href="<?php echo zen_href_link(FILENAME_PROFILES, 'action=add') ?>"><?php echo zen_image_button('button_add_profile.gif', IMAGE_ADD_PROFILE) ?></a></td>
      </tr>
    </tfoot>
<?php } ?>

    <tbody>
<?php if (sizeof($profileList) > 0) { ?>
<?php foreach ($profileList as $profileDetails) { ?>
      <tr>
        <td class="id"><?php echo $profileDetails['id'] ?></td>
<?php if ($action == 'rename' && $_GET['profile'] == $profileDetails['id']) { ?>
        <td colspan="3">
          <?php echo zen_draw_form('profileNameForm', FILENAME_PROFILES, '', 'post', 'id="profile-update"') ?>
            <input type="hidden" name="action" value="update_name"/>
            <input type="hidden" name="profile" value ="<?php echo $profileDetails['id'] ?>"/>
            <input type="text" name="profile-name" value="<?php echo $profileDetails['name'] ?>"/>
            <?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE) ?>
            <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL) ?></a>
          </form>
        </td>
<?php } else { ?>
        <td class="name"><?php echo $profileDetails['name'] ?></td>
        <td class="users"><?php echo $profileDetails['users'] ?></td>
<?php if ($profileDetails['id'] != SUPERUSER_PROFILE) { ?>
        <td class="actions">
          <a href="<?php echo zen_href_link(FILENAME_PROFILES, 'action=edit&amp;profile=' . $profileDetails['id']) ?>"><?php echo zen_image_button('button_edit.gif', IMAGE_EDIT) ?></a>
          <a href="<?php echo zen_href_link(FILENAME_PROFILES, 'action=rename&amp;profile=' . $profileDetails['id']) ?>"><?php echo zen_image_button('button_rename.gif', IMAGE_RENAME) ?></a>
<?php if ($profileDetails['users'] == 0) { ?>
          <a href="<?php echo zen_href_link(FILENAME_PROFILES, 'action=delete&amp;profile=' . $profileDetails['id']) ?>"><?php echo zen_image_button('button_delete.gif', IMAGE_DELETE) ?></a>
<?php } ?>
        </td>
<?php } else { ?>
        <td>&nbsp;</td>
<?php } ?>
<?php } ?>
<?php } // end foreach ?>
      </tr>
<?php } else { ?>
      <tr>
        <td colspan="4"><?php echo TEXT_NO_PROFILES_FOUND ?></td>
      </tr>
<?php } ?>
    </tbody>
  </table>

<?php } elseif ($action == 'edit') { ?>

  <h1><?php echo sprintf(HEADING_TITLE_INDIVIDUAL_PROFILE, $profileName) ?></h1>

  <form id="profileBoxes" name="profileBoxes" action="<?php echo zen_href_link(FILENAME_PROFILES) ?>" method="post">
    <input type="hidden" name="action" value="update"/>
    <input type="hidden" name="profile" value="<?php echo $profile ?>"/>
    <div class="formButtons">
      <?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE) ?>
      <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL) ?></a>
    </div>
<?php foreach($pagesByMenu as $menuKey => $pageList) { ?>
    <dl>
      <dt>
        <strong class="checkLabel"><?php echo $menuTitles[$menuKey] ?></strong>
        <input class="checkButton" type="button" value="check All" onclick="checkAll(this.form,'<?php echo $menuKey ?>',true);">
        <input class="checkButton" type="button" value="Uncheck All" onclick="checkAll(this.form,'<?php echo $menuKey ?>',false);">
      </dt>
<?php foreach ($pageList as $pageKey => $page) { ?>
<?php $checked = in_array($pageKey,$permittedPages) ? 'checked="checked"' : '' ?>
      <dd><label><input name="p[]" class="<?php echo $menuKey ?>" type="checkbox" value="<?php echo $pageKey ?>" <?php echo $checked ?>><?php echo $page['name'] ?></label></dd>
<?php } ?>
    </dl>
<?php } ?>
    <div class="formButtons">
      <?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE) ?>
      <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL) ?></a>
    </div>
  </form>

<?php } elseif ($action == 'add') { ?>

  <h1><?php echo HEADING_TITLE_NEW_PROFILE ?></h1>
    <?php echo zen_draw_form('profiles', FILENAME_PROFILES, 'action=insert') ?>
    <?php echo zen_draw_input_field('name', isset($_POST['name']) ? $_POST['name'] : '', 'class="field"', false, 'text', true) ?>
    <input type="hidden" name="action" value="insert"/>
    <div class="formButtons">
      <?php echo zen_image_submit('button_save.gif', IMAGE_SAVE) ?>
      <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL) ?></a>
    </div>
<?php foreach($pagesByMenu as $menuKey => $pageList) { ?>
    <dl>
      <dt>
        <strong><?php echo $menuTitles[$menuKey] ?></strong>
        <input class="checkButton" type="button" value="check All" onclick="checkAll(this.form,'<?php echo $menuKey ?>',true);">
        <input class="checkButton" type="button" value="Uncheck All" onclick="checkAll(this.form,'<?php echo $menuKey ?>',false);">
      </dt>
<?php foreach ($pageList as $pageKey => $page) {
        $checked = isset($_POST['p']) && in_array($pageKey, $_POST['p']) ? ' checked="checked"' : '' ?>
      <dd><label><input name="p[]" class="<?php echo $menuKey ?>" type="checkbox" value="<?php echo $pageKey ?>"<?php echo $checked ?>><?php echo $page['name'] ?></label></dd>
<?php } ?>
    </dl>
<?php } ?>
    <div class="formButtons">
      <?php echo zen_image_submit('button_save.gif', IMAGE_SAVE) ?>
      <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>"><?php echo zen_image_button('button_cancel.gif', IMAGE_CANCEL) ?></a>
    </div>
  </form>
<?php } ?>

</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>