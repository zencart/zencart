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
<div id="counterHistoryGraph"></div>

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
    data = new google.visualization.arrayToDataTable( [['Day', 'Sessions', 'Total'],
               <?php echo $tplVars['widget']['content'] ; ?>]) ;

    var options = {
        width: '100%', height: '100%',
        backgroundColor: { fill: "#f7f6ef" },
        legend: { position: 'top' },
        colors: ['dodgerblue', 'navy'],
        // trendlines: { 1: {        type: 'exponential',
        // visibleInLegend: true,} }    // Draw a trendline for data series 0.
    };

    // Instantiate and draw our chart, passing in some options.
    chart = new google.visualization.ColumnChart(document.getElementById('counterHistoryGraph'));
    // google.visualization.events.addListener(chart, 'select', selectHandler);
    chart.draw(data, options);
  }
</script>
