<?php
/**
 * logs_function.php 
 *
 * checks for debug logs in /logs/ and /cache/ folders
 *
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

// inspired by suggested log checking by Steve Sherratt (torvista)
function get_logs_data() { 
  if (!defined('DIR_FS_LOGS')) define('DIR_FS_LOGS', DIR_FS_CATALOG . 'logs');
  if (!defined('DIR_FS_SQL_CACHE')) define('DIR_FS_SQL_CACHE', DIR_FS_CATALOG . 'cache');
  $logs = array(); 
  foreach(array(DIR_FS_LOGS, DIR_FS_SQL_CACHE) as $purgeFolder) {
    $purgeFolder = rtrim($purgeFolder, '/');
    if (file_exists($purgeFolder) && is_dir($purgeFolder)) {
      $dir = dir($purgeFolder);
    } else {
      continue;
    }
    while ($logfile = $dir->read()) {
      if ( ($logfile != '.') && ($logfile != '..') && substr($logfile, 0, 1) != '.') {
        if (preg_match('/.*(\.log|\.xml)$/', $logfile)) { // xml for usps debug
           $logs[] = $purgeFolder . "/" . $logfile; 
        }
      }
    }
    $dir->close();
    unset($dir);
  }
  return $logs; 
}
