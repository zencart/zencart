<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
if (! defined('IS_ADMIN_FLAG'))
  die('Illegal Access');

define('SUPERUSER_PROFILE', 1);
$page = (isset($_GET ['cmd'])) ? $_GET ['cmd'] : basename($PHP_SELF, ".php");
$hasDoneStartWizard = TRUE;

// admin folder rename required
// if (! defined('ADMIN_BLOCK_WARNING_OVERRIDE') || ADMIN_BLOCK_WARNING_OVERRIDE == '')
// {
// if (basename($_SERVER ['SCRIPT_FILENAME']) != FILENAME_ALERT_PAGE . '.php')
// {
// if (substr(DIR_WS_ADMIN, - 7) == '/admin/' || substr(DIR_WS_HTTPS_ADMIN, - 7) == '/admin/')
// {
// zen_redirect(zen_href_link(FILENAME_ALERT_PAGE));
// }
// $check_path = dirname($_SERVER ['SCRIPT_FILENAME']) . '/../zc_install';
// if (is_dir($check_path))
// {
// zen_redirect(zen_href_link(FILENAME_ALERT_PAGE));
// }
// }
// }
if ($_GET ['cmd'] != FILENAME_ALERT_PAGE) {
  if (! ($_GET ['cmd'] == FILENAME_LOGIN)) {
    if (! isset($_SESSION ['admin_id'])) {
      if (! ($_GET ['cmd'] == FILENAME_PASSWORD_FORGOTTEN)) {
        zen_redirect(zen_href_link(FILENAME_LOGIN, 'camefrom=' . $_GET ['cmd'] . '&' . zen_get_all_get_params(array(
            'cmd'
        )), 'SSL'));
      }
    }
    if (! in_array($page, array(
        FILENAME_DEFAULT,
        FILENAME_ADMIN_ACCOUNT,
        FILENAME_LOGOFF,
        FILENAME_ALERT_PAGE,
        FILENAME_PASSWORD_FORGOTTEN,
        FILENAME_DENIED,
        FILENAME_ALT_NAV
    )) && ! zen_is_superuser()) {
      if (check_page($_GET ['cmd'], $_GET) == FALSE) {
        zen_redirect(zen_href_link(FILENAME_DENIED, '', 'SSL'));
      }
    }
  }
  if (STORE_NAME == '' || STORE_OWNER == '') {
    $hasDoneStartWizard = FALSE;
    if (! in_array($page, array(
        FILENAME_DEFAULT,
        FILENAME_LOGOFF,
        FILENAME_ALERT_PAGE,
        FILENAME_PASSWORD_FORGOTTEN,
        FILENAME_DENIED,
        FILENAME_ALT_NAV
    )) && isset($_SESSION ['admin_id'])) {
      zen_redirect(zen_href_link(FILENAME_DEFAULT, '', 'SSL'));
    }
  }
}
