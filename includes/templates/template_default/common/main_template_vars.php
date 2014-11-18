<?php
/**
 * Common Template main_template_vars handler
 *
 * Normally a page will automatically load its own template based on the page name.<br />
 * so that a page called some_page will load tpl_some_page_default.php from the template directory.<br />
 * <br />
 * However sometimes a page may need to choose the template it displays based on a set of criteria.<br />
 * Placing a file in the includes/modules/pages/some_page/ directory called main_template_vars.php<br />
 * allows you to override this page and choose the template that loads.<br />
 * <br />
 * to override the global settings and turn off columns, adjust the $flag_disable_xxxxxx variables below as needed.
 * <br />
 * to change which tpl_template_file handles various components, make the necessary adjustments below.
 * <br />
 * <br />
 * NOTE: All of these variables can be intercepted and updated using an observer class.
 * <br />
 * // example to not display right column on main page when Always Show Categories is OFF<br />
 * <br />
 * if ($current_page_base == 'index' and $cPath == '') {<br />
 *  $flag_disable_right = true;<br />
 * }<br />
 * <br />
 * example to not display right column on main page when Always Show Categories is ON and set to categories_id 3<br />
 * <br />
 * if ($current_page_base == 'index' and $cPath == '' or $cPath == '3') {<br />
 *  $flag_disable_right = true;<br />
 * }<br />
 *
 * @package templateSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: main_template_vars.php 2620 2005-12-20 00:52:57Z drbyte $
 */
  // Notifier hook to allow for dynamic changes to template operation
  $zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_START', $template_dir);

  $body_id = ($this_is_home_page) ? 'indexHome' : base::camelize($_GET['main_page']) . 'Body';
  $bodyClasses = (isset($bodyClasses)) ? trim($bodyClasses) : FALSE; // just in case someone has left junk in it from an observer class, or has done something to prevent it from being set

  $homepage_link = zen_href_link(FILENAME_DEFAULT . '.php', '', $request_type, TRUE, TRUE, TRUE);
  $logo_image = zen_image($template->get_template_dir(HEADER_LOGO_IMAGE, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT);

  $header_template = 'tpl_header.php';
  $header_nav_menu_template = 'tpl_header_nav_menu.php';
  $footer_template = 'tpl_footer.php';
  $left_column_file = 'column_left.php';
  $right_column_file = 'column_right.php';


  // Disable sidebars on checkout pages
  if (in_array($current_page_base,explode(",",'checkout,checkout_shipping,checkout_payment,checkout_confirmation,checkout_success,checkout_shipping_address,checkout_payment_address')) ) {
    $flag_disable_right = true;
    $flag_disable_left = true;
  }

  // the following IF statement can be duplicated/modified as needed to set additional flags
  if (in_array($current_page_base,explode(",",'list_pages_to_skip_all_right_sideboxes_on_here,separated_by_commas,and_no_spaces')) ) {
    $flag_disable_right = true;
  }
  // global disable of column_left
  if (COLUMN_LEFT_STATUS == 0 || (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == '') || (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 && CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF == 'true' and ($_SESSION['customers_authorization'] != 0 or $_SESSION['customer_id'] == '')) || (int)$_SESSION['customers_authorization'] == 1) {
    $flag_disable_left = true;
  }
  // global disable of column_right:
  if (COLUMN_RIGHT_STATUS == 0 || (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == '') || (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 && CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF == 'true' and ($_SESSION['customers_authorization'] != 0 or $_SESSION['customer_id'] == '')) || (int)$_SESSION['customers_authorization'] == 1) {
    $flag_disable_right = true;
  }

  // header flag
  if (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 && CUSTOMERS_AUTHORIZATION_HEADER_OFF == 'true' and ($_SESSION['customers_authorization'] != 0 or $_SESSION['customer_id'] == '')) {
    $flag_disable_header = true;
  }

  // footer flag
  if (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 && CUSTOMERS_AUTHORIZATION_FOOTER_OFF == 'true' and ($_SESSION['customers_authorization'] != 0 or $_SESSION['customer_id'] == '')) {
    $flag_disable_footer = true;
  }

  // nav menu flag
  $flag_disable_nav_menu = FALSE;

  if (file_exists(DIR_WS_MODULES . 'pages/' . $current_page_base . '/main_template_vars.php')) {
    $body_code = DIR_WS_MODULES . 'pages/' . $current_page_base . '/main_template_vars.php';
  } else {
    $body_code = $template->get_template_dir('tpl_' . preg_replace('/.php/', '',$_GET['main_page']) . '_default.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_' . $_GET['main_page'] . '_default.php';
  }

  $zco_notifier->notify('NOTIFY_MAIN_TEMPLATE_VARS_END', $template_dir);
