<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_database.php 3001 2006-02-09 21:45:06Z wilt $
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
  $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);

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
    die('<a href="http://www.zen-cart.com/content.php?334-ERROR-0071-There-appears-to-be-a-problem-with-the-database-Maintenance-is-required" target="_blank">ERROR 0071: There appears to be a problem with the database. Maintenance is required.</a>');
  }
  $db->dieOnErrors = TRUE;

  // gc on cache history
  $zc_cache->sql_cache_flush_cache();