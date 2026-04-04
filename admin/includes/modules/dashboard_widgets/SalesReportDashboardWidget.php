<?php

/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Oct 03 Modified in v2.2.0 $
 */

if (!zen_is_superuser() && !check_page(FILENAME_STATS_SALES_REPORT_GRAPHS, '')) return;

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

//  Build the sales stats
$report = 4;
require_once DIR_WS_CLASSES . 'stats_sales_report_graph.php';
$endDate = mktime(0, 0, 0, (int)date('m'), (int)date('d'), (int)date('Y'));
//$startDate = mktime() - (365 + 182) * 3600 * 24;
$startDate = time() - (365 * 2) * 3600 * 24;

//$startDate = mktime() - (365)*3600*24;
$report = new statsSalesReportGraph($report, $startDate, $endDate);
for ($i = 0, $salesData = ''; $i < $report->size; $i++) {
    $month = $zcDate->output(DATE_FORMAT_SHORT_NO_DAY, $report->info[$i]['startDates']);
    $salesData .= "['$month'," . round($report->info[$i]['sum'], $currencies->get_decimal_places(DEFAULT_CURRENCY)) . "]";
    if ($i < $report->size - 1) {
        $salesData .= ",";
    }
}

$currencies ??= new currencies();
?>
  <div class="panel panel-default reportBox">
    <div class="panel-heading header"><?= TEXT_MONTHLY_SALES_TITLE ?><a href="<?= zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS) ?>"><?= TEXT_CLICK_FOR_COMPLETE_DETAILS ?></a></div>
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
      data.addColumn('string', '<?= DASHBOARD_MONTH ?>');
      data.addColumn('number', '<?= DASHBOARD_SALES ?>');
      data.addRows(<?= "[" . $salesData . "]" ?>);

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
          vAxis: {title: '<?= DEFAULT_CURRENCY ?>'},
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
