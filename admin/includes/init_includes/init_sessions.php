<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Oct 16 Modified in v2.1.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// require the session handling functions
require DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'sessions.php';

zen_session_name('zenAdminID');
zen_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
$path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (defined('SESSION_USE_ROOT_COOKIE_PATH') && SESSION_USE_ROOT_COOKIE_PATH === 'True') $path = '/';
$path = (defined('CUSTOM_COOKIE_PATH')) ? CUSTOM_COOKIE_PATH : $path;
$domainPrefix = (!defined('SESSION_ADD_PERIOD_PREFIX') || SESSION_ADD_PERIOD_PREFIX === 'True') ? '.' : '';
if (filter_var($cookieDomain, FILTER_VALIDATE_IP)) $domainPrefix = '';
$secureFlag = str_starts_with(HTTP_SERVER, 'https:');

$samesite = (defined('COOKIE_SAMESITE')) ? COOKIE_SAMESITE : 'lax';
if (!in_array($samesite, ['lax', 'strict', 'none'])) $samesite = 'lax';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => $path,
    'domain' => (!empty($cookieDomain) ? $domainPrefix . $cookieDomain : ''),
    'secure' => $secureFlag,
    'httponly' => true,
    'samesite' => $samesite,
]);

/**
 * Sanitize the IP address, and resolve any proxies.
 */
$_SERVER['REMOTE_ADDR'] = zen_get_ip_address();

// lets start our session
zen_session_start();
$session_started = true;

if (!isset($_SESSION ['securityToken'])) {
    $_SESSION ['securityToken'] = \bin2hex(\random_bytes(16));
}
if ((isset($_GET ['action']) || isset($_POST['action'])) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION ['securityToken'], $_POST ['securityToken']) || $_SESSION ['securityToken'] !== $_POST ['securityToken']) {
        zen_redirect(zen_href_link(FILENAME_DEFAULT, '', 'SSL'));
    }
}
