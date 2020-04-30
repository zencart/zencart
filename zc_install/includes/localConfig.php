<?php
/**
 * @package install
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.5.7 $
 *
 */

/**
 * Optionally set a MySQL mode during installation. The more strict mode is probably preferable when doing development
 * Ref: https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html
 */
define('DB_MYSQL_MODE', 'TRADITIONAL');
// define('DB_MYSQL_MODE', 'STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION,NO_ZERO_DATE,NO_ZERO_IN_DATE,ONLY_FULL_GROUP_BY,NO_AUTO_VALUE_ON_ZERO');


// optionally turn on developer mode
// enabled automagically if environment var is set: HABITAT=zencart
// or uncomment below:
// define('DEVELOPER_MODE', true);


// Following are various default db-name formats you might try in your local development strategy. Feel free to uncomment the one you prefer
//$dev_db_default_name = 'zencart';
//$dev_db_default_name = basename(DIR_FS_ROOT);
//$dev_db_default_name = 'zencart-' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
$dev_db_default_name = 'zencart' . PROJECT_VERSION_MAJOR . preg_replace('/\D/', '', PROJECT_VERSION_MINOR);


// optional additional developer-mode defaults:  (which only take effect if DEVELOPER_MODE===true)
define('DEVELOPER_DBNAME_DEFAULT', $dev_db_default_name);
define('DEVELOPER_DBUSER_DEFAULT', 'root');
define('DEVELOPER_DBPASSWORD_DEFAULT', '');
define('DEVELOPER_INSTALL_DEMO_DATA', true);
