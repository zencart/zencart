<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 07 Modified in v1.5.7 $
 */
require('includes/application_top.php');

// initialise form values
$page_key = $language_key = $main_page = $page_params = $menu_key = $checked = '';
$sort_order = 0;

// check if we are receiving form content and if so validate and process it
if (isset($_POST) && !empty($_POST)) {
  $error = FALSE;

  if (isset($_POST['page_key'])) {
    $page_key = zen_db_prepare_input($_POST['page_key']);
  }
  if (empty($page_key)) {
    $error = TRUE;
    $messageStack->add(ERROR_PAGE_KEY_NOT_ENTERED, 'error');
  } elseif (zen_page_key_exists($page_key)) {
    $error = TRUE;
    $messageStack->add(ERROR_PAGE_KEY_ALREADY_EXISTS, 'error');
  }

  if (isset($_POST['language_key'])) {
    $language_key = zen_db_prepare_input($_POST['language_key']);
  }
  if (empty($page_key)) {
    $error = TRUE;
    $messageStack->add(ERROR_LANGUAGE_KEY_NOT_ENTERED, 'error');
  } elseif (!defined($language_key)) {
    $error = TRUE;
    $messageStack->add(ERROR_LANGUAGE_KEY_HAS_NOT_BEEN_DEFINED, 'error');
  }

  if (isset($_POST['main_page'])) {
    $main_page = zen_db_prepare_input($_POST['main_page']);
  }
  if (empty($main_page)) {
    $error = TRUE;
    $messageStack->add(ERROR_MAIN_PAGE_NOT_ENTERED, 'error');
  } elseif (!defined($main_page)) {
    $error = TRUE;
    $messageStack->add(ERROR_FILENAME_HAS_NOT_BEEN_DEFINED, 'error');
  }

  if (isset($_POST['page_params'])) {
    $page_params = zen_db_prepare_input($_POST['page_params']);
  }

  if (isset($_POST['menu_key'])) {
    $menu_key = zen_db_prepare_input($_POST['menu_key']);
  }
  if (empty($menu_key)) {
    $error = TRUE;
    $messageStack->add(ERROR_MENU_NOT_CHOSEN, 'error');
  }

  if (isset($_POST['display_on_menu'])) {
    $checked = 'checked="true"';
    $display_on_menu = 'Y';
  } else {
    $display_on_menu = 'N';
  }

  if (isset($_POST['sort_order'])) {
    $sort_order = (int)$_POST['sort_order'];
  }

  if (!$error) {
    // register page
    zen_register_admin_page($page_key, $language_key, $main_page, $page_params, $menu_key, $display_on_menu, $sort_order);

    // prepare success message
    $messageStack->add(SUCCESS_ADMIN_PAGE_REGISTERED, 'success');

    // reset form values
    $page_key = $language_key = $main_page = $page_params = $menu_key = $checked = '';
    $sort_order = 0;
  }
}

// prepare options for menu pulldown
$menu_titles = zen_get_menu_titles();
$menu_options = array();
$menu_options[0] = array('id' => NULL, 'text' => TEXT_SELECT_MENU);
foreach ($menu_titles as $id => $title) {
  $menu_options[] = array('id' => $id, 'text' => $title);
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <link rel="stylesheet" href="includes/css/admin_access.css">
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid" id="pageWrapper">
      <h1><?php echo HEADING_TITLE ?></h1>
      <?php echo zen_draw_form('admin_page_registration_form', FILENAME_ADMIN_PAGE_REGISTRATION, 'action=insert', 'post', 'class="form-horizontal" id="adminPageRegistrationForm"'); ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PAGE_KEY, 'page_key', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_input_field('page_key', $page_key, 'class="form-control" id="pageKey" required autofocus'); ?>
          <span class="help-block"><?php echo TEXT_EXAMPLE_PAGE_KEY ?></span>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_LANGUAGE_KEY, 'language_key', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_input_field('language_key', $language_key, 'class="form-control" id="languageKey" required'); ?>
          <span class="help-block"><?php echo TEXT_EXAMPLE_LANGUAGE_KEY ?></span>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_MAIN_PAGE, 'main_page', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_input_field('main_page', $main_page, 'class="form-control" id="mainPage" required'); ?>
          <span class="help-block"><?php echo TEXT_EXAMPLE_MAIN_PAGE ?></span>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PAGE_PARAMS, 'page_params', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_input_field('page_params', $page_params, 'class="form-control" id="pageParams"'); ?>
          <span class="help-block"><?php echo TEXT_EXAMPLE_PAGE_PARAMS ?></span>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_MENU_KEY, 'menu_key', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('menu_key', $menu_options, $menu_key, 'class="form-control" id="menuKey" required'); ?>
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_DISPLAY_ON_MENU, 'display_on_menu', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <input type="checkbox" name="display_on_menu" id="displayOnMenu" <?php echo $checked ?> />
        </div>
      </div>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_SORT_ORDER, 'sort_order', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-3 col-md-1">
            <?php echo zen_draw_input_field('sort_order', $sort_order, 'class="form-control" id="sortOrder" required', false, 'number'); ?>
        </div>
      </div>
      <div class="row">
        <button type="submit" class="btn btn-primary" id="button"><?php echo IMAGE_INSERT; ?></button>
      </div>
      <?php echo '</form>' ?>
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
