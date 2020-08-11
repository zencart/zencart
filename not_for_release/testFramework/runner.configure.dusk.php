<?php

define('STRICT_ERROR_REPORTING', true);

define('HTTP_SERVER', 'http://127.0.0.1:8080');
define('HTTPS_SERVER', 'http://127.0.0.1:8080');

define('DIR_WS_CATALOG', '/');
define('DIR_WS_HTTPS_CATALOG', '/');

define('DIR_FS_CATALOG', getcwd());

define('DB_TYPE', 'mysql'); // always 'mysql'
define('DB_PREFIX', ''); // prefix for database table names -- preferred to be left empty
define('DB_CHARSET', 'utf8mb4'); // 'utf8mb4' or older 'utf8' / 'latin1' are most common
define('DB_SERVER', '127.0.0.1');  // address of your db server
define('DB_SERVER_USERNAME', 'user');
define('DB_SERVER_PASSWORD', 'password');
define('DB_DATABASE', 'zencart');

define('DEVELOPER_MODE', true);

define('ADMIN_NAME', 'Admin');
define('ADMIN_EMAIL', 'test@zencart.test');
