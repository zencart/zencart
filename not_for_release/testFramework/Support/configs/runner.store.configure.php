<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * File Built by Zen Cart Installer on Sun May 21 2023 07:48:07
 */

/*************** NOTE: This file is VERY similar to, but DIFFERENT from the "admin" version of configure.php. ***********/
/***************       The 2 files should be kept separate and not used to overwrite each other.              ***********/

/**
 * Enter the domain for your store
 * If you have SSL, enter the correct https address in BOTH the HTTP_SERVER and HTTPS_SERVER settings, instead of just an http address.
 */
define('HTTP_SERVER', 'http://127.0.0.1');
define('HTTPS_SERVER', 'http://127.0.0.1');

/**
 * If you have https enabled on your website, set this to 'true'
 */
define('ENABLE_SSL', 'false');

/**
 * These DIR_WS_xxxx values refer to the name of any subdirectory in which your store is located.
 * These values get added to the HTTP_CATALOG_SERVER and HTTPS_CATALOG_SERVER values to form the complete URLs to your storefront.
 * They should always start and end with a slash ... ie: '/' or '/foldername/'
 */
define('DIR_WS_CATALOG', '/');
define('DIR_WS_HTTPS_CATALOG', '/');

/**
 * This is the complete physical path to your store's files.  eg: /var/www/vhost/accountname/public_html/store/
 * Should have a closing / on it.
 */
define('DIR_FS_CATALOG', $_SERVER['DOCUMENT_ROOT'] . '/');

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
define('DB_DATABASE', 'db');

/**
 * This is an advanced setting to determine whether you want to cache SQL queries.
 * Options are 'none' (which is the default) and 'file' and 'database'.
 */
define('SQL_CACHE_METHOD', 'none');

/**
 * Reserved for future use
 */
define('SESSION_STORAGE', 'reserved for future use');

/**
 * Advanced use only:
 * The following are OPTIONAL, and should NOT be set unless you intend to change their normal use. Most sites will leave these untouched.
 * To use them, uncomment AND add a proper defined value to them.
 */
// define('DIR_FS_SQL_CACHE' ...
// define('DIR_FS_DOWNLOAD' ...
// define('DIR_FS_LOGS' ...

// End Of File
