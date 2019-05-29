<?php
/**
 * load the filename/database table names and the compatiblity functions
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Apr 11 Modified in v1.5.6b $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Detect the type of request (secure or not)
 * Currently only used as a helper when generating protocol-matched URLs for forms, templates, etc.
 * This is not used as a validation tool at all in Zen Cart core code.
 */
$request_type = (((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1'))) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_BY']) && strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_BY']), 'SSL') !== false) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && (strpos(strtoupper($_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') !== false || strpos(strtolower($_SERVER['HTTP_X_FORWARDED_HOST']), str_replace('https://', '', HTTPS_SERVER)) !== false)) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_SERVER']) && strpos(strtolower($_SERVER['HTTP_X_FORWARDED_SERVER']), str_replace('https://', '', HTTPS_SERVER)) !== false) ||
                 (isset($_SERVER['SCRIPT_URI']) && strtolower(substr($_SERVER['SCRIPT_URI'], 0, 6)) == 'https:') ||
                 (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL'] == '1' || strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on')) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'ssl' || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')) ||
                 (isset($_SERVER['HTTP_SSLSESSIONID']) && $_SERVER['HTTP_SSLSESSIONID'] != '') ||
                 (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == '443') ||
                 (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) ? 'SSL' : 'NONSSL';

/**
 * set php_self in the local scope
 */
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['SCRIPT_NAME'];
/**
 * require global definitons for Filenames
 */
require(DIR_WS_INCLUDES . 'filenames.php');
/**
 * require global definitons for Database Table Names
 */
require(DIR_WS_INCLUDES . 'database_tables.php');
/**
 * require compatibility functions
 */
require(DIR_WS_FUNCTIONS . 'compatibility.php');
/**
 * include the list of extra database tables and filenames
 */
// set directories to check for databases and filename files
$extra_datafiles_directory = DIR_FS_CATALOG . DIR_WS_INCLUDES . 'extra_datafiles/';
$ws_extra_datafiles_directory = DIR_WS_INCLUDES . 'extra_datafiles/';

// Check for new databases and filename etc in extra_datafiles directory
$directory_array = array();

if ($dir = @dir($extra_datafiles_directory)) {
  while ($file = $dir->read()) {
    if (!is_dir($extra_datafiles_directory . $file)) {
      if (preg_match('~^[^\._].*\.php$~', $file) > 0) {
        $directory_array[] = $file;
      }
    }
  }
  if (sizeof($directory_array)) {
    sort($directory_array);
  }
  $dir->close();
}

$file_cnt=0;
for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
  $file_cnt++;
  $file = $directory_array[$i];

  if (file_exists($ws_extra_datafiles_directory . $file)) {
      /**
       * require 3rd party datafiles (ussually to add extra filename/DB Table name definitions)
       */
    include($ws_extra_datafiles_directory . $file);
  }
}
