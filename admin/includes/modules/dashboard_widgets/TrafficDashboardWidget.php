<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Erik Kerkhoven 2020 Jun 17 Modified in v1.5.7 $
 */

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

$maxRows = 15;


$i = 0;
$visit_history = array();
//  Get the visitor history data
$visits_query = "SELECT startdate, counter, session_counter FROM " . TABLE_COUNTER_HISTORY . " ORDER BY startdate DESC";
$visits = $db->Execute($visits_query, (int)$maxRows, true, 1800);
$counterData = '';
foreach ($visits as $data) {
    // table
    $countdate = $data['startdate'];
    $visit_date = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($countdate, 4, 2), substr($countdate, -2), substr($countdate, 0, 4)));
    $visit_history[] = array('date' => $visit_date, 'sessions' => $data['session_counter'], 'count' => $data['counter']);
    // graph
    if ($i > 0) {
        $counterData = "," . $counterData;
    }
    $date = strftime('%a %d', mktime(0, 0, 0, substr($data['startdate'], 4, 2), substr($data['startdate'], -2)));
    $counterData = "['$date'," . $data['session_counter'] . "," . $data['counter'] . "]" . $counterData;
    $i++;
}
?>
  <div class="panel panel-default reportBox">
    <div class="panel-heading header"><?php echo sprintf(TEXT_COUNTER_HISTORY_TITLE, (int)$maxRows); ?></div>
    <?php if (count($visit_history)) { ?>
      <div class="panel-body">
        <div id="trafficgraph"></div>
      </div>
      <table class="table table-striped table-condensed">
        <tr>
          <td class="indented"><?php echo DASHBOARD_DAY; ?></td>
          <td class="text-right indented"> <?php echo DASHBOARD_SESSIONS; ?> - <?php echo DASHBOARD_TOTAL; ?></td>
        </tr>
        <?php
        // table
        foreach ($visit_history as $row) {
          ?>
          <tr>
            <td class="indented"><?php echo $row['date']; ?></td>
            <td class="text-right indented"> <?php echo $row['sessions']; ?> - <?php echo $row['count']; ?></td>
          </tr>
        <?php } ?>
      </table>
    <?php } else { ?>
      <div class="row">
        <p><?php echo TEXT_NONE; ?></p>
      </div>
    <?php } ?>
  </div>


<script title="build_traffic_graph">
  var data;
  var chart;
  // Load the Visualization API and the piechart package.
  google.charts.load('current', {packages: ['corechart']});
  // Set a callback to run when the Google Visualization API is loaded.
  google.charts.setOnLoadCallback(drawTrafficChart);

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and draws it.
  function drawTrafficChart() {

      data = new google.visualization.arrayToDataTable([['<?php echo DASHBOARD_DAY; ?>', '<?php echo DASHBOARD_SESSIONS; ?>', '<?php echo DASHBOARD_TOTAL; ?>'],
<?php echo $counterData; ?>]);

      var options = {
          width: '100%',
          height: '100%',
          backgroundColor: {fill: "#f7f6ef"},
          legend: {position: 'top'},
          colors: ['dodgerblue', 'navy'],
//        trendlines: { 1: {        type: 'exponential',
//        visibleInLegend: true,} }    // Draw a trendline for data series 0.
      };
      // Instantiate and draw our chart, passing in some options.
      chart = new google.visualization.ColumnChart(document.getElementById('trafficgraph'));
      //    google.visualization.events.addListener(chart, 'select', selectHandler);
      chart.draw(data, options);

  }
</script>
