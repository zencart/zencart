<?php
/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All Thu Nov 15 22:35:14 2018 +0100 Modified in v1.5.6 $
 */
require('includes/application_top.php');

// determine whether an action has been requested
if (isset($_POST['action']) && in_array($_POST['action'], array('insert', 'update', 'update_name'))) {
  $action = $_POST['action'];
} elseif (isset($_GET['action']) && in_array($_GET['action'], array('add', 'edit', 'rename', 'delete', 'delete_confirm'))) {
  $action = $_GET['action'];
} else {
  $action = '';
}

// if needed, check that a valid profile id has been passed
if (isset($action) && ($action == 'update' || $action == 'update_name') && $_POST['profile']) {
  $profile = $_POST['profile'];
} elseif (isset($action) && ($action == 'edit' || $action == 'delete' || $action == 'delete_confirm') && $_GET['profile']) {
  $profile = $_GET['profile'];
} elseif (in_array($action, array('edit', 'delete', 'delete_confirm', 'update', 'update-name'))) {
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
  case 'delete_confirm':
    $error = zen_delete_profile($profile);
    if ($error != '') {
      $messageStack->add_session($error, 'error');
      zen_redirect(zen_href_link(FILENAME_PROFILES));
    } else {
      $messageStack->add(SUCCESS_PROFILE_DELETED, 'success');
      unset($action);
      $profileList = zen_get_profiles(TRUE);
    }
    break;
  case 'insert':
    $error = zen_create_profile($_POST);
    if ($error != '') {
      $messageStack->add($error, 'error');
      $pagesByMenu = zen_get_admin_pages(FALSE);
      $action = 'add';
    } else {
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
    $profileName = $_POST['profile-name'];
    $_POST['profile-name'] = trim($_POST['profile-name']);
//    $_POST['profile-name'] = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['profile-name']);
    if ($_POST['profile-name'] != '' && $_POST['profile-name'] == $profileName) {
      zen_update_profile_name($profile, $_POST['profile-name']);
      $messageStack->add_session(SUCCESS_PROFILE_NAME_UPDATED, 'success');
    } else {
      $messageStack->add_session(ERROR_INVALID_PROFILE_NAME, 'error');
    }
    zen_redirect(zen_href_link(FILENAME_PROFILES));
    break;
  case 'rename':
  case 'delete':
  default: // if no specific action requested prepare the listing data
    $profileList = zen_get_profiles(TRUE);
    break;
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <link rel="stylesheet" href="includes/css/admin_access.css">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
      function checkAll(form, header, value) {
          for (var i = 0; i < form.elements.length; i++) {
              if (form.elements[i].className == header) {
                  form.elements[i].checked = value;
              }
          }
      }
    </script>
  </head>
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid" id="pageWrapper">

      <?php if (!isset($action) || $action == '' || $action == 'rename' || $action == 'delete') { ?>

        <h1><?php echo HEADING_TITLE_ALL_PROFILES ?></h1>

        <table class="table table-striped">
          <thead>
            <tr>
              <th class="id"><?php echo TEXT_ID ?></th>
              <th class="name"><?php echo TEXT_NAME ?></th>
              <th class="users"><?php echo TEXT_USERS ?></th>
              <th class="actions">&nbsp;</th>
            </tr>
          </thead>
          <tbody>
              <?php if (sizeof($profileList) > 0) { ?>
                <?php foreach ($profileList as $profileDetails) { ?>
                <tr>
                  <td class="id"><?php echo $profileDetails['id'] ?></td>
                  <?php if ($action == 'rename' && $_GET['profile'] == $profileDetails['id']) { ?>
                    <td>
                        <?php echo zen_draw_form('profileNameForm', FILENAME_PROFILES, '', 'post', 'id="profile-update"') ?>
                        <?php echo zen_draw_hidden_field('action', 'update_name'); ?>
                        <?php echo zen_draw_hidden_field('profile', $profileDetails['id']); ?>
                        <?php echo zen_draw_input_field('profile-name', htmlspecialchars($profileDetails['name'], ENT_COMPAT, CHARSET, TRUE), 'class="form-control"'); ?>
                    </td>
                    <td></td>
                    <td>
                      <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button> <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                      <?php echo '</form>'; ?>
                    </td>
                  <?php } else { ?>
                    <td class="name"><?php echo zen_output_string($profileDetails['name'], FALSE, TRUE); ?></td>
                    <td class="users"><?php echo zen_output_string($profileDetails['users'], FALSE, TRUE) ?></td>
                    <?php if ($profileDetails['id'] != SUPERUSER_PROFILE) { ?>
                      <td class="actions">
                          <?php if ($action != 'delete') { ?>
                          <a href="<?php echo zen_href_link(FILENAME_PROFILES, 'action=edit&profile=' . $profileDetails['id']) ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT; ?></a>
                          <a href="<?php echo zen_href_link(FILENAME_PROFILES, 'action=rename&profile=' . $profileDetails['id']) ?>" class="btn btn-primary" role="button"><?php echo IMAGE_RENAME; ?></a>
                        <?php } ?>
                        <?php if ($profileDetails['users'] == 0) { ?>
                          <?php
                          if ($action == 'delete' && $profileDetails['name'] == zen_get_profile_name($profile)) {
                            echo TEXT_CONFIRM_DELETE;
                            ?>
                            <a href="<?php echo zen_href_link(FILENAME_PROFILES, 'action=delete_confirm&profile=' . $profileDetails['id']) ?>" class="btn btn-danger" role="button"><?php echo IMAGE_DELETE; ?></a>
                            <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                            <?php
                          } else if ($action != 'delete') {
                            ?>
                            <a href="<?php echo zen_href_link(FILENAME_PROFILES, 'action=delete&profile=' . $profileDetails['id']) ?>" class="btn btn-warning" role="button"><?php echo IMAGE_DELETE; ?></a>
                          <?php } ?>
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
          <?php if ($action != 'rename' && $action != 'delete') { ?>
            <tfoot>
              <tr>
                <td colspan="4"><a href="<?php echo zen_href_link(FILENAME_PROFILES, 'action=add') ?>" class="btn btn-primary" role="button"><?php echo IMAGE_ADD_PROFILE; ?></a></td>
              </tr>
            </tfoot>
          <?php } ?>
        </table>

      <?php } elseif ($action == 'edit') { ?>

        <h1><?php echo sprintf(HEADING_TITLE_INDIVIDUAL_PROFILE, $profileName) ?></h1>

        <?php echo zen_draw_form('profilesBoxes', FILENAME_PROFILES, '', 'post', 'class="form-horizontal"') ?>
        <?php echo zen_draw_hidden_field('action', 'update'); ?>
        <?php echo zen_draw_hidden_field('profile', $profile); ?>
        <div class="row formButtons">
          <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
          <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
        </div>
        <?php foreach ($pagesByMenu as $menuKey => $pageList) { ?>
          <dl>
            <dt>
              <strong class="checkLabel"><?php echo $menuTitles[$menuKey] ?></strong>
              <input class="btn btn-info checkButton" type="button" value="<?php echo TEXT_CHECK_ALL; ?>" onclick="checkAll(this.form, '<?php echo $menuKey ?>', true);">
&nbsp;&nbsp;
              <input class="btn btn-info checkButton" type="button" value="<?php echo TEXT_UNCHECK_ALL; ?>" onclick="checkAll(this.form, '<?php echo $menuKey ?>', false);">
            </dt>
            <?php foreach ($pageList as $pageKey => $page) { ?>
              <dd><label><?php echo zen_draw_checkbox_field('p[]', htmlspecialchars($pageKey, ENT_COMPAT, CHARSET, TRUE), in_array($pageKey, $permittedPages), '', ' class="' . $menuKey . ' admin-profile"'); ?><?php echo zen_output_string($page['name'], false, true); ?></label></dd>
            <?php } ?>
          </dl>
        <?php } ?>
        <div class="row formButtons">
          <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
          <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
        </div>
        <?php echo '</form>'; ?>

      <?php } elseif ($action == 'add') { ?>

        <h1><?php echo HEADING_TITLE_NEW_PROFILE ?></h1>
        <?php echo zen_draw_form('profiles', FILENAME_PROFILES, 'action=insert', 'post', 'class="form-horizontal"') ?>
        <div class="row">
          <div class="col-sm-6 col-md-4">
              <?php echo zen_draw_input_field('name', isset($_POST['name']) ? $_POST['name'] : '', 'class="form-control field"', false, 'text', true) ?>
          </div>
        </div>
        <?php echo zen_draw_hidden_field('action', 'insert'); ?>
        <div class="row formButtons">
          <button type="submit" class="btn btn-primary"><?php echo IMAGE_SAVE; ?></button>
          <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
        </div>
        <?php foreach ($pagesByMenu as $menuKey => $pageList) { ?>
          <dl>
            <dt>
              <strong><?php echo $menuTitles[$menuKey] ?></strong>
              <input class="btn btn-info checkButton" type="button" value="<?php echo TEXT_CHECK_ALL; ?>" onclick="checkAll(this.form, '<?php echo $menuKey ?>', true);">
              <input class="btn btn-info checkButton" type="button" value="<?php echo TEXT_UNCHECK_ALL; ?>" onclick="checkAll(this.form, '<?php echo $menuKey ?>', false);">
            </dt>
            <?php foreach ($pageList as $pageKey => $page) { ?>
              <dd><label><?php echo zen_draw_checkbox_field('p[]', htmlspecialchars($pageKey, ENT_COMPAT, CHARSET, TRUE), isset($_POST['p']) && in_array($pageKey, $_POST['p']), '', ' class="' . $menuKey . '"'); ?><?php echo zen_output_string($page['name'], false, true); ?></label></dd>
            <?php } ?>
          </dl>
        <?php } ?>
        <div class="row formButtons">
          <button type="submit" class="btn btn-primary"><?php echo IMAGE_SAVE; ?></button>
          <a href="<?php echo zen_href_link(FILENAME_PROFILES) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
        </div>
        <?php echo '</form>'; ?>
      <?php } ?>

    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
