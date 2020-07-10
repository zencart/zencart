<?php
/**
 * Initialise database driver and connect
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Mar 14 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * require the query_factory clsss based on the DB_TYPE
 */
require('includes/classes/db/' .DB_TYPE . '/query_factory.php');
$db = new queryFactory();

$down_for_maint_source = 'nddbc.html';

if (!defined('USE_PCONNECT')) define('USE_PCONNECT', 'false'); 
if (!$db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false)) {
  session_write_close();
  // If can't connect, send 503 Service Unavailable header and redirect to install or message page
  header("HTTP/1.1 503 Service Unavailable");


  if (file_exists('zc_install/index.php')) {
    header('location: zc_install/index.php');
    exit;
  } elseif (file_exists($down_for_maint_source)) {
    include($down_for_maint_source );
    exit(1);
  } elseif (defined('HTTP_SERVER') && defined('DIR_WS_CATALOG')) {
    header('location: ' . HTTP_SERVER . DIR_WS_CATALOG . $down_for_maint_source );
    exit(1);
  } else {
    header('location: ' . $down_for_maint_source);
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
    die('<a href="https://docs.zen-cart.com/user/troubleshooting/error_71_maintenance_required/">https://docs.zen-cart.com/user/troubleshooting/error_71_maintenance_required/</a>'); 
}
$db->dieOnErrors = TRUE;
