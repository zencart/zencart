<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * File Built by Zen Cart Installer on Sun May 21 2023 07:48:07
 */

require_once __DIR__ . '/runtime_config.php';

/*************** NOTE: This file is VERY similar to, but DIFFERENT from the "admin" version of configure.php. ***********/
/***************       The 2 files should be kept separate and not used to overwrite each other.              ***********/

/**
 * Enter the domain for your store
 */
define('HTTP_SERVER', 'http://127.0.0.1');

define('DIR_WS_CATALOG', '/');
define('DIR_FS_CATALOG', zc_test_config_catalog_path());

/**
 * The following settings define your database connection.
 * These must be the SAME as you're using in your admin copy of configure.php
 */
define('DB_TYPE', 'mysql'); // always 'mysql'
define('DB_PREFIX', ''); // prefix for database table names -- preferred to be left empty
define('DB_CHARSET', 'utf8mb4'); // 'utf8mb4' or older 'utf8' / 'latin1' are most common
define('DB_SERVER', '127.0.0.1');  // address of your db server
define('DB_SERVER_USERNAME', 'root');
define('DB_SERVER_PASSWORD', 'root');
define('DB_DATABASE', zc_test_config_database_name('db'));
define('DIR_FS_LOGS', zc_test_config_log_directory(DIR_FS_CATALOG));

/**
 * This is an advanced setting to determine whether you want to cache SQL queries.
 * Options are 'none' (which is the default) and 'file' and 'database'.
 */
define('SQL_CACHE_METHOD', 'none');
