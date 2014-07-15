<?php
/**
 * Logs Dashboard Widget
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace ZenCart\Admin\DashboardWidget;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/**
 * Logs Class
 *
 * @package classes
 */
class Logs extends AbstractWidget
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
