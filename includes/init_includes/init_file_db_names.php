<?php
/**
 * load the filename/database table names and the compatibility functions
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Aug 23 Modified in v2.0.0-alpha1 $
 */

use Zencart\Request\Request;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/**
 * Detect the type of request (secure or not)
 * Currently only used as a helper when generating protocol-matched URLs for forms, templates, etc.
 * This is not used as a validation tool at all in Zen Cart core code.
 */
$request_type = Request::isSecure() ? 'SSL' : 'NONSSL';

/**
 * set php_self in the local scope
 */
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['SCRIPT_NAME'];
/**
 * require global definitons for Filenames
 */
require DIR_WS_INCLUDES . 'filenames.php';
/**
 * require global definitons for Database Table Names
 */
require DIR_WS_INCLUDES . 'database_tables.php';
/**
 * require pre-autoload compatibility functions
 */
require DIR_WS_FUNCTIONS . 'compatibility.php';
/**
 * include the list of extra database tables and filenames
 */
// set directories to check for databases and filename files
$extra_datafiles_directory = DIR_FS_CATALOG . DIR_WS_INCLUDES . 'extra_datafiles/';
$ws_extra_datafiles_directory = DIR_WS_INCLUDES . 'extra_datafiles/';

// Check for new database tables and filenames etc in extra_datafiles directory, usually for plugins
foreach (glob($extra_datafiles_directory . '*.php') ?? [] as $file) {
    include($ws_extra_datafiles_directory . basename($file));
}
