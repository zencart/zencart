<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Thu Mar 14 20:41:52 2013 -0400 Modified in v1.5.4 $
 */


if (! defined('IS_ADMIN_FLAG'))
  die('Illegal Access');

define('SUPERUSER_PROFILE', 1);
$page = $zcRequest->readGet('cmd', basename($PHP_SELF, ".php"));
$hasDoneStartWizard = TRUE;

$val = getenv('HABITAT');
$habitat = ($val == 'zencart' || (isset($_SERVER['USER']) && $_SERVER['USER'] == 'vagrant'));

$redirectTo = false;
$authError = false;

// admin folder rename required
if ((!defined('ADMIN_BLOCK_WARNING_OVERRIDE') || ADMIN_BLOCK_WARNING_OVERRIDE == '') && ($habitat == false)) {
  if ($page != FILENAME_ALERT_PAGE)
  {
    if (substr(DIR_WS_ADMIN, - 7) == '/admin/' || substr(DIR_WS_HTTPS_ADMIN, - 7) == '/admin/')
    {
      zen_redirect(zen_admin_href_link(FILENAME_ALERT_PAGE));
    }
    $check_path = dirname($_SERVER ['SCRIPT_FILENAME']) . '/../zc_install';
    if (is_dir($check_path))
    {
        $redirectTo = zen_admin_href_link(FILENAME_ALERT_PAGE);
        $authError = ADMIN_BLOCK_WARNING;
    }
  }
}
if ($zcRequest->readGet('cmd') != FILENAME_ALERT_PAGE && !$authError) {
  if (! ($zcRequest->readGet('cmd') == FILENAME_LOGIN)) {
    if (! isset($_SESSION ['admin_id'])) {
      if (! ($zcRequest->readGet('cmd') == FILENAME_PASSWORD_FORGOTTEN)) {
          $redirectTo = zen_admin_href_link(FILENAME_LOGIN, 'camefrom=' . $zcRequest->readGet('cmd')
          . '&' . zen_get_all_get_params(array('cmd'))
        );
          $authError = AUTH_ERROR;
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
      if (check_page($zcRequest->readGet('cmd'), $zcRequest->all('get')) == FALSE) {
          if (check_related_page($zcRequest->readGet('cmd'), $zcRequest->all('get')) == FALSE) {
            zen_record_admin_activity('Attempted access to unauthorized page [' . $page . ']. Redirected to DENIED page instead.', 'notice');
              $redirectTo = zen_admin_href_link(FILENAME_DENIED);
            $authError = AUTH_ERROR;

          }
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
      zen_redirect(zen_admin_href_link(FILENAME_DEFAULT));
    }
  }
}
if ($zcRequest->getWebFactoryRequest()->isXhr() && $authError) {
    header("Status: 403 Forbidden", TRUE, 403);
    echo json_encode(array('error'=>TRUE, 'errorType'=>$authError));
    exit(1);
} elseif ($redirectTo) {
    zen_redirect($redirectTo);
}
