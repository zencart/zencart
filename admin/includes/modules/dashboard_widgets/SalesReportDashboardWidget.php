<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Erik Kerkhoven 2020 Jun 17 Modified in v1.5.7 $
 */

if (!zen_is_superuser() && !check_page(FILENAME_STATS_SALES_REPORT_GRAPHS, '')) return;

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

//  Build the sales stats
$report = 4;
require_once DIR_WS_CLASSES . 'stats_sales_report_graph.php';
$endDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
//$startDate = mktime() - (365 + 182) * 3600 * 24;
$startDate = time() - (365 * 2) * 3600 * 24;

//$startDate = mktime() - (365)*3600*24;
$report = new statsSalesReportGraph($report, $startDate, $endDate);
for ($i = 0, $salesData = ''; $i < $report->size; $i++) {
    $month = $report->info[$i]['text'];
    $salesData .= "['$month'," . round($report->info[$i]['sum'], 2) . "]";
    if ($i < $report->size - 1) {
        $salesData .= ",";
    }
}

require_once(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();
?>
  <div class="panel panel-default reportBox">
    <div class="panel-heading header"><?php echo TEXT_MONTHLY_SALES_TITLE; ?><a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS); ?>"><?php echo TEXT_CLICK_FOR_COMPLETE_DETAILS; ?></a></div>
    <div class="panel-body">
      <div id="salesgraph"></div>
    </div>
  </div>

<script title="build_sales_graph">
  var data;
  var chart;
  // Load the Visualization API and the piechart package.
  google.charts.load('current', {packages: ['corechart']});
  // Set a callback to run when the Google Visualization API is loaded.
  google.charts.setOnLoadCallback(drawSalesGraph);

  function drawSalesGraph() {
      data = new google.visualization.DataTable();
      data.addColumn('string', '<?php echo DASHBOARD_MONTH; ?>');
      data.addColumn('number', '<?php echo DASHBOARD_SALES; ?>');
      data.addRows(<?php echo "[" . $salesData . "]"; ?>);

      var options = {
          trendlines: {
              0: {
//              type: 'linear',
//              pointSize: 20,
//              opacity: 0.6,
//              pointsVisible: true,
//              showR2: true,
//              visibleInLegend: true
              }
          }, // Draw a trendline for data series 0.
          vAxis: {title: '<?php echo DEFAULT_CURRENCY; ?>'},
          width: '100%',
          height: '100%',
          backgroundColor: {fill: "#f7f6ef"},
          legend: {position: 'top'},
          colors: ['dodgerblue']
      };

      chart = new google.visualization.ColumnChart(document.getElementById('salesgraph'));
      chart.draw(data, options);

  }
</script>
