<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2020 Oct 27 Modified in v1.5.7a $
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
if (filter_var($cookieDomain, FILTER_VALIDATE_IP)) $domainPrefix = '';
$secureFlag = (substr(HTTP_SERVER, 0, 6) == 'https:') ? TRUE : FALSE;

$samesite = (defined('COOKIE_SAMESITE')) ? COOKIE_SAMESITE : 'lax';
if (!in_array($samesite, ['lax', 'strict', 'none'])) $samesite = 'lax';

if (PHP_VERSION_ID >= 70300) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => $path,
    'domain' => (zen_not_null($cookieDomain) ? $domainPrefix . $cookieDomain : ''),
    'secure' => $secureFlag,
    'httponly' => true,
    'samesite' => $samesite,
  ]);
} else {
  session_set_cookie_params(0, $path .'; samesite='.$samesite, (zen_not_null($cookieDomain) ? $domainPrefix . $cookieDomain : ''), $secureFlag, true);
}

/**
 * Sanitize the IP address, and resolve any proxies.
 */
$ipAddressArray = explode(',', zen_get_ip_address());
$ipAddress = (sizeof($ipAddressArray) > 0) ? $ipAddressArray[0] : '.';
$_SERVER['REMOTE_ADDR'] = $ipAddress;

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
