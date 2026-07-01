<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * File Built by %%_INSTALLER_METHOD_%% on %%_DATE_NOW_%%
 */

/**
 * Enter the domain URL for your store
 * If you have SSL, enter the correct https address instead of just an http address.
 */
define('HTTP_SERVER', '%%_CATALOG_HTTP_SERVER_%%');

/**
 * The DIR_WS_xxxx values refer to the name of any subdirectory in which your store is located.
 * This value gets appended to HTTP_SERVER to form the complete URL to your storefront.
 * They should always start and end with a slash ... ie: '/' or '/foldername/'
 */
define('DIR_WS_CATALOG', '%%_DIR_WS_CATALOG_%%');

/**
 * This is the complete physical path to your store's files.  eg: /var/www/vhost/accountname/public_html/store/
 * Should have a closing / on it.
 */
define('DIR_FS_CATALOG', '%%_DIR_FS_CATALOG_%%');

/**
 * The following settings define your database connection.
 */
define('DB_TYPE', '%%_DB_TYPE_%%'); // always 'mysql'
define('DB_PREFIX', '%%_DB_PREFIX_%%'); // prefix for database table names -- preferred to be left empty
define('DB_CHARSET', '%%_DB_CHARSET_%%'); // 'utf8mb4' required. If using older 'utf8' or 'latin1', convert your database to utf8mb4. See conversion util in docs site.
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
 * Optional: List of trusted proxy IP addresses.
 * Leave it as an empty string/array if you are not using a proxy.
 *
 * But if you are hosting behind a proxy such as Cloudflare, you must add the official IP addresses of the proxy here.
 * For example, for Cloudflare, you can find the list of IP addresses here: https://www.cloudflare.com/ips/
 *
 * Entries may be individual IPs or CIDR ranges (e.g. '173.245.48.0/20'),
 * both IPv4 and IPv6 are supported.
 * Supports listing entries as an array or a comma-delimited string.
 */
define('TRUSTED_PROXIES', []);

/**
 * Reserved for future use
 */
define('SESSION_STORAGE', 'set by zc_install');
