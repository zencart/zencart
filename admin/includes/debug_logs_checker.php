<?php
/**
 * debug_logs_checker.php
 *
 * checks for debug logs in /logs/ and /cache/ folders
 *
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: debug_logs_checker.php ver 1.51 by Linda McGrath 2012-01-19
 */

// inspired by suggested log checking by Steve Sherratt (torvista)
  define('DEBUG_LOGS_CHECKER_DISPLAY', 3);
  if (!defined('DIR_FS_LOGS')) define('DIR_FS_LOGS', DIR_FS_CATALOG . 'logs');
  if (!defined('DIR_FS_SQL_CACHE')) define('DIR_FS_SQL_CACHE', DIR_FS_CATALOG . 'cache');
  $cnt_logs = 0;
  foreach(array(DIR_FS_LOGS, DIR_FS_SQL_CACHE) as $purgeFolder) {
    $purgeFolder = rtrim($purgeFolder, '/');
    if (file_exists($purgeFolder) && is_dir($purgeFolder)) {
//      $chk_directories .= $purgeFolder . '<br />';
      $dir = dir($purgeFolder);
    } else {
      continue;
    }
    while ($logfile = $dir->read()) {
      if ( ($logfile != '.') && ($logfile != '..') && substr($logfile, 0, 1) != '.') {
        if (preg_match('/.*(\.log|\.xml)$/', $logfile)) { // xml for usps debug
          if ($cnt_logs < DEBUG_LOGS_CHECKER_DISPLAY){
            $messageStack->add('Debug log file discovered: ' . $purgeFolder . '/' . $logfile);
          }
          $cnt_logs ++;
        }
      }
    }
    $dir->close();
    unset($dir);
  }
  if ($cnt_logs > 0){
    $messageStack->add('Debug log file(s) discovered. Found: ' . $cnt_logs /* . '<br />' . 'Checked directories:<br />' . $chk_directories */);
    $messageStack->add('*** WARNING *** .log files may indicate problems that need to be resolved. Read the .log file(s) to resolve any errors, then delete them manually or via the Store Manager.');
  }

