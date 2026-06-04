<?php
/**
 * dist-configure.php - SAMPLE FILE!
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2025 Apr 27 Modified in v2.2.0 $
 * @private
 */

/*************** NOTE: This file is VERY similar to, but DIFFERENT from the "store" version of configure.php. ***********/
/***************       The 2 files should be kept separate and not used to overwrite each other.              ***********/

/**
 * Enter the domain for your Admin URL. Use https:// if you have SSL enabled on your site.
 */
define('HTTP_SERVER', 'https://localhost');

/**
 * Enter the domain for your storefront URL.
 */
define('HTTP_CATALOG_SERVER', 'https://localhost');

/**
 * These DIR_WS_xxxx values refer to the name of any subdirectory in which your store is located.
 * This value gets appended to HTTP_CATALOG_SERVER to form the complete URL to your storefront.
 * They should always start and end with a slash ... ie: '/' or '/foldername/'
 */
define('DIR_WS_CATALOG', '/');

/**
 * This is the complete physical path to your store's files.  eg: /var/www/vhost/accountname/public_html/store/
 * Should have a closing / on it.
 */
define('DIR_FS_CATALOG', '/var/www/vhost/accountname/public_html/store/');

/**
 * The following settings define your database connection.
 * These must be the SAME as you're using in your non-admin copy of configure.php
 */
define('DB_TYPE', 'mysql'); // always 'mysql'
define('DB_PREFIX', ''); // prefix for database table names -- preferred to be left empty
define('DB_CHARSET', 'utf8mb4');
define('DB_SERVER', 'localhost');  // address of your db server
define('DB_SERVER_USERNAME', '');
define('DB_SERVER_PASSWORD', '');
define('DB_DATABASE', '');

/**
 * This is an advanced setting to determine whether you want to cache SQL queries.
 * Options are 'none' (which is the default) and 'file' and 'database'.
 */
define('SQL_CACHE_METHOD', 'none');

/**
 * Reserved for future use
 */
define('SESSION_STORAGE', 'set by zc_install');
