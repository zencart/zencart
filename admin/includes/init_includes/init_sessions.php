<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_sessions.php 19956 2011-11-07 15:40:25Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// require the session handling functions
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'sessions.php');

  zen_session_name('zenAdminID');
  zen_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
$path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (defined('SESSION_USE_ROOT_COOKIE_PATH') && SESSION_USE_ROOT_COOKIE_PATH  == 'True') $path = '/';
$path = (defined('CUSTOM_COOKIE_PATH')) ? CUSTOM_COOKIE_PATH : $path;
$domainPrefix = (!defined('SESSION_ADD_PERIOD_PREFIX') || SESSION_ADD_PERIOD_PREFIX == 'True') ? '.' : '';
$secureFlag = ((ENABLE_SSL_ADMIN == 'true' && substr(HTTP_SERVER, 0, 6) == 'https:' && substr(HTTPS_SERVER, 0, 6) == 'https:') || (ENABLE_SSL_ADMIN == 'false' && substr(HTTP_SERVER, 0, 6) == 'https:')) ? TRUE : FALSE;

if (PHP_VERSION >= '5.2.0') {
  session_set_cookie_params(0, $path, (zen_not_null($cookieDomain) ? $domainPrefix . $cookieDomain : ''), $secureFlag, TRUE);
} else {
  session_set_cookie_params(0, $path, (zen_not_null($cookieDomain) ? $domainPrefix . $cookieDomain : ''), $secureFlag);
}

// lets start our session
  zen_session_start();
  $session_started = true;

if (! isset ( $_SESSION ['securityToken'] ))
{
  $_SESSION ['securityToken'] = md5 ( uniqid ( rand (), true ) );
}
if ((isset ( $_GET ['action'] ) || isset($_POST['action']) ) && $_SERVER['REQUEST_METHOD'] == 'POST')
{
  if ((! isset ( $_SESSION ['securityToken'] ) || ! isset ( $_POST ['securityToken'] )) || ($_SESSION ['securityToken'] !== $_POST ['securityToken']))
  {
    zen_redirect ( zen_href_link ( FILENAME_DEFAULT, '', 'SSL' ) );
  }
}
