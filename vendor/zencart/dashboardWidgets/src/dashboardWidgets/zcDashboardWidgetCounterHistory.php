<?php namespace Zencart\DashboardWidgets\dashboardWidgets;
/**
 * zcDashboardWidgetCounterHistory Class.
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id:  $
 */
use Zencart\DashboardWidgets\zcDashboardWidgetBase;
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * zcDashboardWidgetCounterHistory Class
 *
 * @package classes
 */
class zcDashboardWidgetCounterHistory extends zcDashboardWidgetBase
{
  public function prepareContent()
  {
    global $db;

    $tplVars = array();
    $counter_query = "select startdate, counter, session_counter from " . TABLE_COUNTER_HISTORY . " order by startdate DESC limit 10";
    $counter = $db->Execute($counter_query);
    while (!$counter->EOF) {
      $counter_startdate = $counter->fields['startdate'];
      $counter_startdate_formatted = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
      $counter_info = $counter->fields['session_counter'] . ' - ' . $counter->fields['counter'];
      $tplVars['content'][] = array('text'=> $counter_startdate_formatted, 'value'=>$counter_info);
      $counter->MoveNext();
    }
    return $tplVars;
  }
}