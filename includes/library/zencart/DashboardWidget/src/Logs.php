<?php
/**
 * Logs Dashboard Widget
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace ZenCart\DashboardWidget;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/**
 * Class Logs
 * @package ZenCart\DashboardWidget
 */
class Logs extends AbstractWidget
{
  private function getDisplayName($log)
  {
     $str = $log['datetime']; 
     if (strpos($log['filename'], "-adm-") !== false) { 
        $str .= TEXT_ADMIN_LOG_SUFFIX; 
     }
     return $str;
  }

  public function prepareContent()
  {
    $tplVars = array();

    $count = get_logs_data('count');
    if ($count == 0) {
     $tplVars['content'][] = array('text'=>TEXT_NO_LOGFILES_FOUND, 'value'=>'');
     return $tplVars;
    }

    // @TODO - in future when widgets support configurable settings, allow this number to be set there.
    $max_logs_to_list = 20;

    $logs = get_logs_data($max_logs_to_list);
    // keys in $logs are: 'path', 'filename', 'filesize', 'unixtime', 'datetime'

    foreach ($logs as $log) {
      $tplVars['content'][] = array(
                                    'text'=> '<a href="' . zen_admin_href_link(FILENAME_VIEW_LOG, 'logname=' . $log['filename']) . '">' . $this->getDisplayName($log) . '</a>', 
                                    'value'=>$log['filesize'],
                                    );
    }

    // display summary
    $final_message = sprintf(TEXT_TOTAL_LOGFILES_FOUND, $count);
    if ($count > $max_logs_to_list) {
      $final_message .= sprintf(TEXT_DISPLAYING_RECENT_COUNT, $max_logs_to_list);
    }
    $tplVars['content'][] = array('text'=> $final_message, 'value'=> '');

    $clean_message = '<a href="' . zen_href_link(FILENAME_STORE_MANAGER) . '">' . TEXT_CLEANUP_LOGFILES . '</a>';
    $tplVars['content'][] = array('text'=> $clean_message, 'value'=> '');

    return $tplVars;
  }
}
