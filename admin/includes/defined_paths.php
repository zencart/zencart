<?php
/**
 *** NOTE: This file contains a list of system-used paths for your site.
 ***       It should NOT be necessary to edit anything here. Anything requiring overrides can be done in override files or in configure.php directly. ***
 * -- ADMIN version --
 *
 * @package initSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All Wed Nov 15 10:59:33 2017 +0100 Modified in v1.5.6 $
 */
function zen_parse_url($url, $element = 'array')
{
  // Read the various elements of the URL, to use in auto-detection of admin foldername (basically a simplified parse_url equivalent which automatically supports ports and uncommon TLDs)
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

// make guesses in case the essentials from admin configure.php are missing (such as when someone uses a non-admin configure.php in their admin)
if (!defined('HTTP_CATALOG_SERVER')) define('HTTP_CATALOG_SERVER', HTTP_SERVER);
if (!defined('HTTPS_CATALOG_SERVER')) define('HTTPS_CATALOG_SERVER', HTTP_SERVER);
if (!defined('DIR_WS_CATALOG')) define('DIR_WS_CATALOG', DIR_WS_ADMIN . '/../');
if (!defined('DIR_WS_HTTPS_CATALOG')) define('DIR_WS_HTTPS_CATALOG', DIR_WS_ADMIN . '/../');
if (!defined('ENABLE_SSL_CATALOG')) define('ENABLE_SSL_CATALOG', strpos(HTTPS_CATALOG_SERVER, 'tps:') ? 'true': 'false');
if (!defined('DIR_FS_CATALOG')) define('DIR_FS_CATALOG', realpath(DIR_FS_ADMIN . '/../') . '/');


// now define all the admin constants
if (!defined('HTTPS_SERVER')) define('HTTPS_SERVER', HTTP_SERVER);

if (!defined('DIR_WS_ADMIN')) define('DIR_WS_ADMIN', preg_replace('#^' . str_replace('-', '\-', zen_parse_url(HTTP_SERVER, '/path')) . '#', '', dirname($_SERVER['SCRIPT_NAME'])) . '/');

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
if (!defined('DIR_WS_EDITORS')) define('DIR_WS_EDITORS', 'editors/');

if (!defined('DIR_FS_SQL_CACHE')) define('DIR_FS_SQL_CACHE', DIR_FS_CATALOG . 'cache'); // trailing slash omitted
if (!defined('DIR_FS_LOGS')) define('DIR_FS_LOGS', DIR_FS_CATALOG . 'logs'); // trailing slash omitted
if (!defined('DIR_FS_DOWNLOAD')) define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');

if (!defined('SESSION_STORAGE')) define('SESSION_STORAGE', 'db');

if (!defined('DIR_CATALOG_LIBRARY')) {
    define('DIR_CATALOG_LIBRARY', DIR_FS_CATALOG . DIR_WS_INCLUDES . 'library/');
}

//catchall for old things that still use it ... but should be rewritten so this can be removed fully.
if (!defined('DIR_WS_HTTPS_ADMIN')) define('DIR_WS_HTTPS_ADMIN', DIR_WS_ADMIN);
if (!defined('ENABLE_SSL_ADMIN')) define('ENABLE_SSL_ADMIN', substr(HTTP_SERVER, 0, 6) == 'https:' ? 'true' : 'false');
