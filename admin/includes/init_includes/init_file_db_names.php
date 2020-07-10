<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2019 Aug 22 Modified in v1.5.7 $
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
if ($dir = @dir($extra_datafiles_dir)) {
  while ($file = $dir->read()) {
    if (!is_dir($extra_datafiles_dir . $file)) {
      if (preg_match('~^[^\._].*\.php$~i', $file) > 0) {
        require($extra_datafiles_dir . $file);
      }
    }
  }
  $dir->close();
}
