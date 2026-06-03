<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Aug 23 Modified in v2.0.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/** @deprecated since v3.0.0 */
$request_type = '';

// set php_self in the local scope
//  if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

// include the list of project filenames
  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'database_tables.php');

// include the list of compatibility issues
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'compatibility.php');

// include the list of extra database tables and filenames
$extra_datafiles_dir = DIR_WS_INCLUDES . 'extra_datafiles/';
foreach (glob($extra_datafiles_dir . '*.php') ?? [] as $file) {
    require($file);
}
