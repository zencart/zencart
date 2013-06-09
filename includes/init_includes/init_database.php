<?php
/**
 * Initialise database driver and connect
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package   initSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   1.6.0
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

use ZenCart\Database\Mysql\Connection as MysqlDatabase;

if (defined('DB_TYPE')) {
  $dbType = strtolower(trim(DB_TYPE));

  if ($dbType == 'mysql') {
    $db = new MysqlDatabase;
  }

  if (is_null($db)) {
    // fall back to legacy implementation
    include_once DIR_WS_CLASSES . 'db/' . DB_TYPE . '/query_factory.php';
    $db = new queryFactory;
  }

  if (!$db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, true)) {
    $down_for_maint_source = 'nddbc.html';
    if (file_exists('zc_install/index.php')) {
      header('location: zc_install/index.php');
    } elseif (file_exists($down_for_maint_source)) {
      if (defined('HTTP_SERVER') && defined('DIR_WS_CATALOG')) {
        header('location: ' . HTTP_SERVER . DIR_WS_CATALOG . $down_for_maint_source);
      } else {
        header('location: ' . $down_for_maint_source);
      }
    }
    die();
  }

  unset($dbType);
}
