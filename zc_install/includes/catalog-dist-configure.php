<?php
/**
 * @package Configuration Settings circa 1.6.0
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * File Built by %%_INSTALLER_METHOD_%% on %%_DATE_NOW_%%
 */
// Define the webserver and path parameters
// HTTP_SERVER is your Main webserver: eg-http://www.yourdomain.com
// HTTPS_SERVER is your Secure webserver: eg-https://www.yourdomain.com
define('HTTP_SERVER', '%%_CATALOG_HTTP_SERVER_%%');
define('HTTPS_SERVER', '%%_CATALOG_HTTPS_SERVER_%%');

// Use secure webserver for checkout procedure?
define('ENABLE_SSL', '%%_ENABLE_SSL_CATALOG_%%');

// NOTE: be sure to leave the trailing '/' at the end of these lines if you make changes!

// * DIR_WS_* = Webserver directories (virtual/URL)
// these paths are relative to top of your webspace ... (ie: under the public_html or httpdocs folder)
define('DIR_WS_CATALOG', '%%_DIR_WS_CATALOG_%%');
define('DIR_WS_HTTPS_CATALOG', '%%_DIR_WS_HTTPS_CATALOG_%%');

define('DIR_WS_IMAGES', 'images/');
define('DIR_WS_INCLUDES', 'includes/');
define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');
define('DIR_WS_DOWNLOAD_PUBLIC', DIR_WS_CATALOG . 'pub/');
define('DIR_WS_TEMPLATES', DIR_WS_INCLUDES . 'templates/');

// * DIR_FS_* = Filesystem directories (local/physical)
//the following path is a COMPLETE path to your Zen Cart files. eg: /var/www/vhost/accountname/public_html/store/
define('DIR_FS_CATALOG', '%%_DIR_FS_CATALOG_%%');
  //the following path is a COMPLETE path to the /logs/ folder  eg: /var/www/vhost/accountname/public_html/store/logs ... and no trailing slash
  define('DIR_FS_LOGS', DIR_FS_CATALOG . '/logs');

define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
define('DIR_WS_UPLOADS', DIR_WS_IMAGES . 'uploads/');
define('DIR_FS_UPLOADS', DIR_FS_CATALOG . DIR_WS_UPLOADS);
define('DIR_FS_EMAIL_TEMPLATES', DIR_FS_CATALOG . 'email/');

// define our database connection
define('DB_TYPE', '%%_DB_TYPE_%%');
define('DB_PREFIX', '%%_DB_PREFIX_%%'); // prefix for database table names -- preferred to be left empty
define('DB_CHARSET', '%%_DB_CHARSET_%%');
define('DB_SERVER', '%%_DB_SERVER_%%');
define('DB_SERVER_USERNAME', '%%_DB_SERVER_USERNAME_%%');
define('DB_SERVER_PASSWORD', '%%_DB_SERVER_PASSWORD_%%');
define('DB_DATABASE', '%%_DB_DATABASE_%%');

// The next 2 "defines" are for SQL cache support.
// For SQL_CACHE_METHOD, you can select from:  none, database, or file
// If you choose "file", then you need to set the DIR_FS_SQL_CACHE to a directory where your apache
// or webserver user has write privileges (chmod 666 or 777). We recommend using the "cache" folder inside the Zen Cart folder
// ie: /path/to/your/webspace/public_html/zen/cache   -- leave no trailing slash
define('SQL_CACHE_METHOD', '%%_SQL_CACHE_METHOD_%%');
define('DIR_FS_SQL_CACHE', '%%_DIR_FS_SQL_CACHE_%%');

define('SESSION_STORAGE', '%%_SESSION_STORAGE_%%');
