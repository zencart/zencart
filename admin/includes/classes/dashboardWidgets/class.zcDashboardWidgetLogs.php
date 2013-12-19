<?php
/**
 * zcDashboardWidgetCounterHistory Class.
 *
 * @package classes
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Scott Wilson Fri Aug 17 17:42:37 2012 +0100 New in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcDashboardWidgetLogs Class
 *
 * @package classes
 */
class zcDashboardWidgetLogs extends zcDashboardWidgetBase
{
  public function prepareContent()
  {
    $tplVars = array();
    require_once(DIR_WS_FUNCTIONS . "logs_functions.php");
    $logs = get_logs_data();
    if (sizeof($logs) == 0) return $tplVars;
    foreach ($logs as $log) {
      $filename = str_replace(DIR_FS_CATALOG, '', $log);
      $filesize = filesize($log);
      $tplVars['content'][] = array('text'=> $filename, 'value'=>$filesize);
    }
    return $tplVars;
  }
}
