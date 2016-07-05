<?php
/**
 * SalesGraphReport Dashboard Widget
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
 * Class SalesGraphReport
 * @package ZenCart\DashboardWidget
 */
class SalesGraphReport extends AbstractWidget
{
  public function prepareContent()
  {
    global $db;
    $currencies = new \currencies();
    require_once(DIR_FS_ADMIN . DIR_WS_CLASSES . 'stats_sales_report_graph.php');

    
    $sales_report_view = 4;  // @TODO - allow this to be configurable in the widget

    $endDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"))   ;
    //$startDate = mktime() - (365+182)*3600*24;
    $startDate = mktime() - (365*2)*3600*24;
    //$startDate = mktime() - (365)*3600*24;

    $report = new \statsSalesReportGraph($sales_report_view, $startDate, $endDate, $sales_report_filter);
    for ($i = 0; $i < $report->size; $i++) {
        $month = $report->info[$i]['text'];
        $graphData .= "['$month',".round($report->info[$i]['sum'],2)."]" ;
        if ($i < $report->size - 1) { $graphData .= ",";}
    }

    $days = array();
    $result = $db->Execute("select o.date_purchased as date_purchased, date(o.date_purchased) as dateshort, count(date_purchased) as number_of_orders, sum(ot.value) as sum_of_orders
                            from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and class = 'ot_total') 
                            group by date(date_purchased)
                            having o.date_purchased >= (CURDATE() - INTERVAL 3 DAY)
                            order by date_purchased DESC");
    foreach($result as $row) {
        $days[] = array('count' => $row['number_of_orders'], 'sales' => $currencies->format($row['sum_of_orders']));
    }

    $graph_title = DEFAULT_CURRENCY;

    return array('graphData' => $graphData, 'days' => $days, 'graphTitle' => $graph_title);
  }
}
