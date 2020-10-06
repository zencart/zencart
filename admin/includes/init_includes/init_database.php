<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// include the cache class
  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'cache.php');
  $zc_cache = new cache;

// Load queryFactory db classes
  require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'db/' .DB_TYPE . '/query_factory.php');
  $db = new queryFactory();
  if (!$db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE)) {
    // If can't connect, send 503 Service Unavailable header and redirect to install or message page
    header("HTTP/1.1 503 Service Unavailable");
    $down_for_maint_source = 'nddbc.html';

    if (file_exists('../zc_install/index.php')) {
      header('location: ../zc_install/index.php');
      exit;
    } elseif (defined('HTTP_SERVER') && defined('DIR_WS_CATALOG')) {
      header('location: ' . HTTP_SERVER . DIR_WS_CATALOG . $down_for_maint_source);
      exit(1);
    } else {
      header('location: ../' . $down_for_maint_source);
//    header('location: mystoreisdown.html');
      exit(1);
    }
  }

  // Do a quick sanity check that system tables exist
  if (defined('SQL_CACHE_METHOD') && SQL_CACHE_METHOD == 'database') {
    $sql = "SHOW TABLES LIKE '" . TABLE_DB_CACHE . "'";
  } else {
    $sql = "SHOW TABLES LIKE '" . TABLE_PROJECT_VERSION . "'";
  }
  $db->dieOnErrors = FALSE;
  $result = $db->Execute($sql, FALSE, FALSE);
  if ($result->RecordCount() == 0) {
    if (defined('ERROR_DATABASE_MAINTENANCE_NEEDED')) die(ERROR_DATABASE_MAINTENANCE_NEEDED);
    die('<a href="https://docs.zen-cart.com/user/troubleshooting/error_71_maintenance_required/" rel="noopener" target="_blank">ERROR 0071 There appears to be a problem with the database. Maintenance is required.</a>');
  }
  $db->dieOnErrors = TRUE;

  // gc on cache history
  $zc_cache->sql_cache_flush_cache();
