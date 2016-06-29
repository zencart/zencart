<?php
/**
 * CounterHistory Dashboard Widget
 *
 * @package   ZenCart\Admin\DashboardWidget
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license   http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version   GIT: $Id:  $
 */

namespace ZenCart\DashboardWidget;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

/**
 * Class CounterHistory
 * @package ZenCart\DashboardWidget
 */
class CounterHistoryGraph extends AbstractWidget
{
  public function prepareContent()
  {
    global $db;
    $counterData = '';

    $days = 14; // @TODO let this be configurable in widget settings

    //  Get the counter data
    $sql = "select startdate, counter, session_counter from " . TABLE_COUNTER_HISTORY . " order by startdate DESC limit " . (int)$days;
    $result = $db->Execute($sql);
    $i = 0 ;
    foreach ($result as $row) {
      $counter_startdate_formatted = strftime('%a %d', mktime(0, 0, 0, substr($row['startdate'], 4, 2), substr($row['startdate'], -2)));
      if ($i > 0) $counterData = "," . $counterData;
      $counterData = "['" . $counter_startdate_formatted . "'," . $row['session_counter']."," . $row['counter'] ."]" . $counterData  ;
      $i++ ;
    }
    return array('content' => $counterData);
  }
}
