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

  require_once(DIR_WS_FUNCTIONS . "logs_functions.php"); 
  $logs = get_logs_data();
  $cnt_logs = sizeof($logs); 
  if ($cnt_logs > 0){
    $messageStack->add(DEBUG_LOGS_DISCOVERED . $cnt_logs);
    $messageStack->add(DEBUG_LOGS_WARNING);
  }
