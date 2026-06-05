<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

require_once __DIR__ . '/runtime_config.php';

define('HTTP_SERVER', 'http://127.0.0.1');
define('DIR_WS_CATALOG', '/');
define('DIR_FS_CATALOG', zc_test_config_catalog_path());

define('DB_TYPE', 'mysql');
define('DB_PREFIX', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_SERVER', '127.0.0.1');
define('DB_SERVER_USERNAME', 'root');
define('DB_SERVER_PASSWORD', 'root');
define('DB_DATABASE', zc_test_config_database_name('db'));
define('DIR_FS_LOGS', zc_test_config_log_directory(DIR_FS_CATALOG));

define('SQL_CACHE_METHOD', 'none');
define('SESSION_STORAGE', 'reserved for future use');
