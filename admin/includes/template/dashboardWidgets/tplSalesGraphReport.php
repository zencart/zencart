<?php
/**
 * dashboard widget Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
?>
<div class="row">
  <div class="col-xs-12"><?php echo SALES_GRAPH_TEXT_MONTHLY; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo '<a href="' . zen_admin_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS) . '">' . SALES_GRAPH_TEXT_CLICK . '</a>'; ?></div>
</div>

<div class="row">
  <div class="col-xs-6">Sales <strong><?php echo TEXT_DATE_RANGE_TODAY; ?></strong> (<?php echo $tplVars['widget']['days'][0]['count']; ?>)&nbsp;&nbsp;<strong><?php echo $tplVars['widget']['days'][0]['sales']; ?></span>&nbsp;&nbsp;</strong></div>
  <div class="col-xs-6"><strong><?php echo TEXT_DATE_RANGE_YESTERDAY; ?></strong> (<?php echo $tplVars['widget']['days'][1]['count']; ?>)&nbsp;&nbsp;<strong><?php echo $tplVars['widget']['days'][1]['sales']; ?></span></strong></div>
</div>

<div class="row">
<div id="salesGraphWidget"></div>
</div>

<!--Load the AJAX API FOR GOOGLE GRAPHS, without double-loading in case another widget has already loaded it -->
<script type="text/javascript">window.google || document.write(unescape('%3Cscript type="text/javascript" src="https://www.google.com/jsapi"%3E%3C/script%3E'));</script>

<script type="text/javascript">
 var data;
 var chart;
  // Load the Visualization API and the piechart package.
  google.load('visualization', '1', {'packages':['corechart']});
  // Set a callback to run when the Google Visualization API is loaded.
  google.setOnLoadCallback(drawCharts);

  // Callback that creates and populates a data table,
  // instantiates the pie chart, passes in the data and draws it.
  function drawCharts() {
    data = new google.visualization.DataTable();
    data.addColumn('string', '<?php echo SALES_GRAPH_COLUMN_MONTH; ?>');
    data.addColumn('number', '<?php echo SALES_GRAPH_COLUMN_SALES; ?>');
    data.addRows(<?php echo "[".$tplVars['widget']['graphData']."]" ; ?>);

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
        },    // Draw a trendline for data series 0.
        vAxis: { title: '<?php echo $tplVars['widget']['graphtitle'];?>' },
        width: '100%', height: '100%',
        backgroundColor: { fill: "#f7f6ef" },
        legend: { position: 'top' },
        colors: ['dodgerblue']
    };


    // Instantiate and draw our chart, passing in some options.
    chart = new google.visualization.ColumnChart(document.getElementById('salesGraphWidget'));
    // google.visualization.events.addListener(chart, 'select', selectHandler);
    chart.draw(data, options);
  }
</script>
