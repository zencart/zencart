<?php
/**
 * dist-configure.php - SAMPLE FILE!
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2025 Apr 27 Modified in v3.0.0 $
 * @private
 */

/**
 * Enter the domain URL for your store
 * If you have SSL, enter the correct https address instead of just an http address.
 */
define('HTTP_SERVER', 'http://localhost');

/**
 * These DIR_WS_xxxx values refer to the name of any subdirectory in which your store is located.
 * This value gets appended to HTTP_SERVER to form the complete URL to your storefront.
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
 * Optional: List of trusted proxy IP addresses.
 * Leave it as an empty string/array if you are not using a proxy.
 *
 * But if you are hosting behind a proxy such as Cloudflare, you must add the official IP addresses of the proxy here.
 * For example, for Cloudflare, you can find the list of IP addresses here: https://www.cloudflare.com/ips/
 *
 * Supports listing IPs as an array or a comma-delimited string.
 */
define('TRUSTED_PROXIES', []);

/**
 * Reserved for future use
 */
define('SESSION_STORAGE', 'set by zc_install');
