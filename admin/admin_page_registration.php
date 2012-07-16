<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_page_registration.php 18695 2011-05-04 05:24:19Z drbyte $
 */

require('includes/application_top.php');

// initialise form values
$page_key = $language_key = $main_page = $page_params = $menu_key = $checked = '';
$sort_order = 0;

// check if we are receiving form content and if so validate and process it
if (isset($_POST) && !empty($_POST))
{
  $error = FALSE;

  if (isset($_POST['page_key'])) $page_key = zen_db_prepare_input($_POST['page_key']);
  if (empty($page_key))
  {
    $error = TRUE;
    $messageStack->add(ERROR_PAGE_KEY_NOT_ENTERED, 'error');
  } elseif (zen_page_key_exists($page_key))
  {
    $error = TRUE;
    $messageStack->add(ERROR_PAGE_KEY_ALREADY_EXISTS, 'error');
  }

  if (isset($_POST['language_key'])) $language_key = zen_db_prepare_input($_POST['language_key']);
  if (empty($page_key))
  {
    $error = TRUE;
    $messageStack->add(ERROR_LANGUAGE_KEY_NOT_ENTERED, 'error');
  } elseif (!defined($language_key))
  {
    $error = TRUE;
    $messageStack->add(ERROR_LANGUAGE_KEY_HAS_NOT_BEEN_DEFINED, 'error');
  }

  if (isset($_POST['main_page'])) $main_page = zen_db_prepare_input($_POST['main_page']);
  if (empty($main_page))
  {
    $error = TRUE;
    $messageStack->add(ERROR_MAIN_PAGE_NOT_ENTERED, 'error');
  } elseif (!defined($main_page))
  {
    $error = TRUE;
    $messageStack->add(ERROR_FILENAME_HAS_NOT_BEEN_DEFINED, 'error');
  }

  if (isset($_POST['page_params']))
  {
    $page_params = zen_db_prepare_input($_POST['page_params']);
  }

  if (isset($_POST['menu_key'])) $menu_key = zen_db_prepare_input($_POST['menu_key']);
  if (empty($menu_key))
  {
    $error = TRUE;
    $messageStack->add(ERROR_MENU_NOT_CHOSEN, 'error');
  }

  if (isset($_POST['display_on_menu']))
  {
    $checked = 'checked="true"';
    $display_on_menu = 'Y';
  } else
  {
    $display_on_menu = 'N';
  }

  if (isset($_POST['sort_order']))
  {
    $sort_order = (int)$_POST['sort_order'];
  }

  if (!$error)
  {
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
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<link rel="stylesheet" type="text/css" href="includes/admin_access.css" />
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init() {
    cssjsmenu('navbar');
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
  <h1><?php echo HEADING_TITLE ?></h1>
  <?php echo zen_draw_form('admin_page_registration_form', FILENAME_ADMIN_PAGE_REGISTRATION, 'action=insert', 'post', 'id="adminPageRegistrationForm"'); ?>
    <div>
      <label for="pageKey"><?php echo TEXT_PAGE_KEY ?></label>
      <?php echo zen_draw_input_field('page_key', $page_key, ' id="pageKey');?>
      <span><?php echo TEXT_EXAMPLE_PAGE_KEY ?></span>
    </div>
    <div>
      <label for="languageKey"><?php echo TEXT_LANGUAGE_KEY ?></label>
      <?php echo zen_draw_input_field('language_key', $language_key, ' id="languageKey');?>
      <span><?php echo TEXT_EXAMPLE_LANGUAGE_KEY ?></span>
    </div>
    <div>
      <label for="mainPage"><?php echo TEXT_MAIN_PAGE ?></label>
      <?php echo zen_draw_input_field('main_page', $main_page, ' id="mainPage');?>
      <span><?php echo TEXT_EXAMPLE_MAIN_PAGE ?></span>
    </div>
    <div>
      <label for="pageParams"><?php echo TEXT_PAGE_PARAMS ?></label>
      <?php echo zen_draw_input_field('page_params', $page_$page_paramskey, ' id="pageParams');?>
      <span><?php echo TEXT_EXAMPLE_PAGE_PARAMS ?></span>
    </div>
    <div>
      <label for="menuKey"><?php echo TEXT_MENU_KEY ?></label>
      <?php echo zen_draw_pull_down_menu('menu_key', $menu_options, $menu_key) ?>
    </div>
    <div>
      <label for="displayOnMenu"><?php echo TEXT_DISPLAY_ON_MENU ?></label>
      <input type="checkbox" name="display_on_menu" id="displayOnMenu" <?php echo $checked ?> />
    </div>
    <div>
      <label for="sortOrder"><?php echo TEXT_SORT_ORDER ?></label>
      <?php echo zen_draw_input_field('sort_order', $sort_order, ' id="sortOrder');?>
    </div>
    <div>
      <?php echo zen_image_submit('button_insert.gif', IMAGE_INSERT, 'id="button"') ?>
    </div>
  <?php echo '</form>' ?>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
