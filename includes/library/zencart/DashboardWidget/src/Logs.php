<?php
/**
 * Logs Dashboard Widget
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
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
  protected $count; 
  public function __construct($widgetKey, $widgetInfo = NULL) 
  {
      parent::__construct($widgetKey, $widgetInfo);
      $this->count = 0;
  }

  private function getDisplayName($log)
  {
     $str = $log['datetime']; 
     if (strpos($log['filename'], "-adm-") !== false) { 
        $str .= TEXT_ADMIN_LOG_SUFFIX; 
     }
     return $str;
  }

  public function updatewidgetInfo(&$info)
  {
     if ($this->count > 0) { 
        $info['widget_theme'] = 'bg-red-gradient'; 
        $info['widget_icon'] = 'fa-warning'; 
        $this->widgetInfoChanged = true;
     }
  }

  public function prepareContent()
  {
    $tplVars = array();

    $this->count = get_logs_data('count');
    if ($this->count == 0) {
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
    $final_message = sprintf(TEXT_TOTAL_LOGFILES_FOUND, $this->count);
    if ($this->count > $max_logs_to_list) {
      $final_message .= sprintf(TEXT_DISPLAYING_RECENT_COUNT, $max_logs_to_list);
    }
    $tplVars['content'][] = array('text'=> $final_message, 'value'=> '', 'fullrow' => true);

    $clean_message = '<a href="' . zen_admin_href_link(FILENAME_STORE_MANAGER) . '">' . TEXT_CLEANUP_LOGFILES . '</a>';
    $tplVars['content'][] = array('text'=> $clean_message, 'value'=> '', 'span' => 2);

    return $tplVars;
  }
}
