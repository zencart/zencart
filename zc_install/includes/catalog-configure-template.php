<?php
/**
 * @package Configuration Settings
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * File Built by %%_INSTALLER_METHOD_%% on %%_DATE_NOW_%%
 */

/*************** NOTE: This file is VERY similar to, but DIFFERENT from the "admin" version of configure.php. ***********/
/***************       The 2 files should be kept separate and not used to overwrite each other.              ***********/

/**
 * Enter the domain for your store
 * HTTP_SERVER is your Main webserver: eg-http://www.yourdomain.com
 * HTTPS_SERVER is your Secure/SSL webserver: eg-https://www.yourdomain.com
 */
define('HTTP_SERVER', '%%_CATALOG_HTTP_SERVER_%%');
define('HTTPS_SERVER', '%%_CATALOG_HTTPS_SERVER_%%');

/**
 *  If you want to tell Zen Cart to use your HTTPS URL on sensitive pages like login and checkout, set this to 'true'. Otherwise 'false'. (Keep the quotes)
 */
define('ENABLE_SSL', '%%_ENABLE_SSL_CATALOG_%%');

/**
 * These DIR_WS_xxxx values refer to the name of any subdirectory in which your store is located.
 * These values get added to the HTTP_CATALOG_SERVER and HTTPS_CATALOG_SERVER values to form the complete URLs to your storefront.
 * They should always start and end with a slash ... ie: '/' or '/foldername/'
 */
define('DIR_WS_CATALOG', '%%_DIR_WS_CATALOG_%%');
define('DIR_WS_HTTPS_CATALOG', '%%_DIR_WS_HTTPS_CATALOG_%%');

/**
 * This is the complete physical path to your store's files.  eg: /var/www/vhost/accountname/public_html/store/
 * Should have a closing / on it.
 */
define('DIR_FS_CATALOG', '%%_DIR_FS_CATALOG_%%');

/**
 * The following settings define your database connection.
 * These must be the SAME as you're using in your admin copy of configure.php
 */
define('DB_TYPE', '%%_DB_TYPE_%%'); // always 'mysql'
define('DB_PREFIX', '%%_DB_PREFIX_%%'); // prefix for database table names -- preferred to be left empty
define('DB_CHARSET', '%%_DB_CHARSET_%%'); // 'utf8mb4' or older 'utf8' / 'latin1' are most common
define('DB_SERVER', '%%_DB_SERVER_%%');  // address of your db server
define('DB_SERVER_USERNAME', '%%_DB_SERVER_USERNAME_%%');
define('DB_SERVER_PASSWORD', '%%_DB_SERVER_PASSWORD_%%');
define('DB_DATABASE', '%%_DB_DATABASE_%%');

/**
 * This is an advanced setting to determine whether you want to cache SQL queries.
 * Options are 'none' (which is the default) and 'file' and 'database'.
 */
define('SQL_CACHE_METHOD', '%%_SQL_CACHE_METHOD_%%');

/**
 * Reserved for future use
 */
define('SESSION_STORAGE', '%%_SESSION_STORAGE_%%');

/**
 * Advanced use only:
 * The following are OPTIONAL, and should NOT be set unless you intend to change their normal use. Most sites will leave these untouched.
 * To use them, uncomment AND add a proper defined value to them.
 */
// define('DIR_FS_SQL_CACHE' ...
// define('DIR_FS_DOWNLOAD' ...
// define('DIR_FS_LOGS' ...

// End Of File
