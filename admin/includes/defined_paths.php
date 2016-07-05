<?php
/**
 *** NOTE: This file contains a list of system-used paths for your site.
 ***       It should NOT be necessary to edit anything here. Anything requiring overrides can be done in override files or in configure.php directly. ***
 *
 * -- NOTE: This ADMIN version of defined_paths.php is DIFFERENT from the non-admin version! --
 *
 * @package initSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sat Jan 9 22:19:03 2016 -0500 New in v1.5.5 $
 */

/**
 * Read the various elements of the URL, to use in auto-detection of admin foldername 
 * (basically a simplified parse_url equivalent which automatically supports ports and uncommon TLDs)
 */
function zen_parse_url($url, $element = 'array')
{
  $t1 = array();
  // scheme
  $s1 = explode('://', $url);
  $t1['scheme'] = $s1[0];
  // host
  $s2 = explode('/', trim($s1[1], '/'));
  $t1['host'] = $s2[0];
  array_shift($s2);
  // path/uri
  $t1['path'] = implode('/', $s2);
  $p1 = ($t1['path'] != '') ? '/' . $t1['path'] : '';

  switch($element) {
    case 'path':
    case 'host':
    case 'scheme':
      return $t1[$element];
    case '/path':
      return $p1;
    case 'array':
    default:
      return $t1;
  }
}
/**
 * calculate Admin URL 
 */
function get_admin_server_url() 
{
  switch(true) {
    case defined('ADMIN_HTTP_SERVER'):
      return ADMIN_HTTP_SERVER;
    case defined('HTTPS_SERVER'):
      return zen_parse_url(HTTPS_SERVER, 'scheme') . '://' . zen_parse_url(HTTPS_SERVER, 'host');
    case defined('HTTP_SERVER'):
      return zen_parse_url(HTTP_SERVER, 'scheme') . '://' . zen_parse_url(HTTP_SERVER, 'host');
  }
}

$admin_server_url = get_admin_server_url();
if (!defined('ADMIN_HTTP_SERVER')) define('ADMIN_HTTP_SERVER', $admin_server_url);


// define constants based on catalog configure.php and automated system detection.
if (!defined('HTTP_CATALOG_SERVER')) define('HTTP_CATALOG_SERVER', $admin_server_url);
if (!defined('HTTPS_CATALOG_SERVER')) define('HTTPS_CATALOG_SERVER', $admin_server_url);
if (!defined('DIR_WS_CATALOG')) define('DIR_WS_CATALOG', DIR_WS_ADMIN . '/../');
if (!defined('DIR_WS_HTTPS_CATALOG')) define('DIR_WS_HTTPS_CATALOG', DIR_WS_ADMIN . '/../');
if (!defined('ENABLE_SSL_CATALOG')) define('ENABLE_SSL_CATALOG', strpos(HTTPS_CATALOG_SERVER, 'tps:') ? 'true': 'false');
if (!defined('DIR_FS_CATALOG')) define('DIR_FS_CATALOG', realpath(DIR_FS_ADMIN . '/../') . '/');

if (!defined('DIR_WS_ADMIN')) define('DIR_WS_ADMIN', preg_replace('#^' . str_replace('-', '\-', zen_parse_url(ADMIN_HTTP_SERVER, '/path')) . '#', '', dirname($_SERVER['SCRIPT_NAME'])) . '/');
if (!defined('DIR_FS_ADMIN')) define('DIR_FS_ADMIN', preg_replace('#/includes/$#', '/', realpath(__DIR__ . '/../') . '/'));

if (!defined('DIR_WS_IMAGES')) define('DIR_WS_IMAGES', 'images/');
if (!defined('DIR_WS_ICONS')) define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
if (!defined('DIR_WS_CATALOG_IMAGES')) define('DIR_WS_CATALOG_IMAGES', (ENABLE_SSL_CATALOG == 'true' ? HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_CATALOG_SERVER . DIR_WS_CATALOG) . 'images/');
if (!defined('DIR_WS_CATALOG_TEMPLATE')) define('DIR_WS_CATALOG_TEMPLATE', (ENABLE_SSL_CATALOG == 'true' ? HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_CATALOG_SERVER . DIR_WS_CATALOG) . 'includes/templates/');
if (!defined('DIR_WS_INCLUDES')) define('DIR_WS_INCLUDES', 'includes/');
if (!defined('DIR_WS_FUNCTIONS')) define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
if (!defined('DIR_WS_CLASSES')) define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
if (!defined('DIR_WS_MODULES')) define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
if (!defined('DIR_WS_LANGUAGES')) define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');
if (!defined('DIR_WS_CATALOG_LANGUAGES')) define('DIR_WS_CATALOG_LANGUAGES', (ENABLE_SSL_CATALOG == 'true' ? HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_CATALOG_SERVER . DIR_WS_CATALOG) . 'includes/languages/');
if (!defined('DIR_FS_CATALOG_LANGUAGES')) define('DIR_FS_CATALOG_LANGUAGES', DIR_FS_CATALOG . 'includes/languages/');
if (!defined('DIR_FS_CATALOG_IMAGES')) define('DIR_FS_CATALOG_IMAGES', DIR_FS_CATALOG . 'images/');
if (!defined('DIR_FS_CATALOG_MODULES')) define('DIR_FS_CATALOG_MODULES', DIR_FS_CATALOG . 'includes/modules/');
if (!defined('DIR_FS_CATALOG_TEMPLATES')) define('DIR_FS_CATALOG_TEMPLATES', DIR_FS_CATALOG . 'includes/templates/');
if (!defined('DIR_FS_BACKUP')) define('DIR_FS_BACKUP', DIR_FS_ADMIN . 'backups/');
if (!defined('DIR_FS_EMAIL_TEMPLATES')) define('DIR_FS_EMAIL_TEMPLATES', DIR_FS_CATALOG . 'email/');
if (!defined('SQL_CACHE_METHOD')) define('SQL_CACHE_METHOD', 'none');

if (!defined('DIR_FS_SQL_CACHE')) define('DIR_FS_SQL_CACHE', DIR_FS_CATALOG . 'cache'); // trailing slash omitted
if (!defined('DIR_FS_LOGS')) define('DIR_FS_LOGS', DIR_FS_CATALOG . 'logs'); // trailing slash omitted
if (!defined('DIR_FS_DOWNLOAD')) define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');

if (!defined('SESSION_STORAGE')) define('SESSION_STORAGE', 'db');

if (!defined('DIR_CATALOG_LIBRARY')) {
    define('DIR_CATALOG_LIBRARY', DIR_FS_CATALOG . DIR_WS_INCLUDES . 'library/');
}

//catchalls for old things that still use it ... but which should be rewritten so this can be removed fully.
if (!defined('HTTP' . 'S_SERVER')) define('HTTP' . 'S_SERVER', $admin_server_url);
if (!defined('DIR_WS_HTTPS_ADMIN')) define('DIR_WS_HTTPS_ADMIN', DIR_WS_ADMIN);
