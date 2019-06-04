<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @author inspired from sales_report_graphs.php,v 0.01 2002/11/27 19:02:22 cwi Exp  Released under the GNU General Public License $
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 Jan 24 Modified in v1.5.6b $
 */
require 'includes/application_top.php';

//if (!defined('SALES_REPORT_GRAPHS_FILTER_DEFAULT')) define('SALES_REPORT_GRAPHS_FILTER_DEFAULT', '00000000110000000000');

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

if (!empty($_GET['report'])) {
  $sales_report_view = (int)$_GET['report'];
}
// default is 4
if (!isset($sales_report_view) || $sales_report_view < 1 || $sales_report_view > 5) {
  $sales_report_view = 4;
}

// report views (1:hourly, 2:daily, 3:weekly, 4:monthly, 5:yearly)

switch ($sales_report_view) {
  case('1'):
    $summary1 = CHART_TEXT_AVERAGE . ' ' . REPORT_TEXT_HOURLY;
    $summary2 = TODAY_TO_DATE;
    $report_desc = REPORT_TEXT_HOURLY;
    break;
  case('2'):
    $summary1 = CHART_TEXT_AVERAGE . ' ' . REPORT_TEXT_DAILY;
    $summary2 = WEEK_TO_DATE;
    $report_desc = REPORT_TEXT_DAILY;
    break;
  case('3'):
    $summary1 = CHART_TEXT_AVERAGE . ' ' . REPORT_TEXT_WEEKLY;
    $summary2 = WEEK_TO_DATE;
    $report_desc = REPORT_TEXT_WEEKLY;
    break;

  case('4'):
    $summary1 = CHART_TEXT_AVERAGE . ' ' . REPORT_TEXT_MONTHLY;
    $summary2 = MONTH_TO_DATE;
    $report_desc = REPORT_TEXT_MONTHLY;
    break;

  case('5'):
    $summary1 = CHART_TEXT_AVERAGE . ' ' . REPORT_TEXT_YEARLY;
    $summary2 = YEARLY_TOTAL;
    $report_desc = REPORT_TEXT_YEARLY;
    break;
}

// check start and end Date
$startDate = "";
if (!empty($_GET['startDate']) && $_GET['startDate'] >= '0001-01-01') {
  $startDate = $_GET['startDate'];
}
$endDate = "";
if (!empty($_GET['endDate']) && $_GET['endDate'] >= '0001-01-01') {
  $endDate = $_GET['endDate'];
}
// check filters
$sales_report_filter = '';
if (isset($_GET['filter']) && $_GET['filter'] && zen_not_null($_GET['filter'])) {
  $sales_report_filter = $_GET['filter'];
  $sales_report_filter_link = "&filter=$sales_report_filter";
} elseif (defined('SALES_REPORT_GRAPHS_FILTER_DEFAULT')) {
  $sales_report_filter = SALES_REPORT_GRAPHS_FILTER_DEFAULT;
  $sales_report_filter_link = "&filter=$sales_report_filter";
}

require DIR_WS_CLASSES . 'stats_sales_report_graph.php';
$report = new statsSalesReportGraph($sales_report_view, $startDate, $endDate, $sales_report_filter);

if (strlen($sales_report_filter) == 0) {
  $sales_report_filter = $report->filter;
  $sales_report_filter_link = "";
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" media="print" href="includes/stylesheet_print.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script src="https://www.google.com/jsapi"></script>
    <script title="menu_init">
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
    <script title="build_graphs">
      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages': ['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      function drawChart() {

          // Create the data table.
          var data = new google.visualization.DataTable();
          data.addColumn('string', 'label');
          data.addColumn('number', '<?php echo CHART_TOTAL_SALES; ?>');
<?php if ($sales_report_view < 5) { ?>
            data.addColumn('number', '<?php echo CHART_AVERAGE_SALE_AMOUNT; ?>');
<?php } ?>

          data.addRows([
<?php
for ($i = 0; $i < $report->size; $i++) {

  // column name
  echo "           ['";

  if ($sales_report_view == 5 && $report->size > 5) {
    echo substr($report->info[$i]['text'], 0, 1);
  } elseif ($sales_report_view == 4) {
    echo substr($report->info[$i]['text'], 0, 3);
  } elseif ($sales_report_view == 3) {
    echo substr($report->info[$i]['text'], 0, 5);
  } elseif ($sales_report_view == 1) {
    echo ltrim(substr($report->info[$i]['text'], 0, 2), '0');
  } elseif ($report->size > 5) {
    echo substr($report->info[$i]['text'], 3, 2);
  } else {
    echo substr($report->info[$i]['text'], 0, 5);
  }

  echo "', ";

  // first value
  echo round($report->info[$i]['sum'], 2);

  // second value
  if ($sales_report_view < 5) {
    echo ',';
    echo round($report->info[$i]['avg'], 2);
  }

  echo ']';
  if (($i + 1) < $report->size) {
    echo ',' . "\n";
  }
}
?>

          ]);

          // Set chart options
          var options = {
              'title': '<?php echo $report_desc; ?>',
              'legend': 'bottom',
              'is3D': false,
              'width': 600,
              'height': 450
          };
<?php
// echo $scale;
?>

          // Instantiate and draw our chart, passing in some options.
          var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
          chart.draw(data, options);
      }
    </script>
  </head>
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo $report_desc . ' ' . HEADING_TITLE; ?></h1>
      <!-- body_text //-->
      <table class="table">
        <tr>
          <td class="menuBoxHeading text-right">
            <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, 'report=1' . $sales_report_filter_link); ?>"><?php echo REPORT_TEXT_HOURLY; ?></a> | <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, 'report=2' . $sales_report_filter_link); ?>"><?php echo REPORT_TEXT_DAILY; ?></a> | <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, 'report=3' . $sales_report_filter_link); ?>"><?php echo REPORT_TEXT_WEEKLY; ?></a> | <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, 'report=4' . $sales_report_filter_link); ?>"><?php echo REPORT_TEXT_MONTHLY; ?></a> | <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, 'report=5' . $sales_report_filter_link); ?>"><?php echo REPORT_TEXT_YEARLY; ?></a>
          </td>
        </tr>
      </table>

      <div class="col-sm-12 col-md-6">
        <div id="chart_div"></div>
      </div>
      <div class="col-sm-12 col-md-6">
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"></th>
                <th class="dataTableHeadingContent text-center"><?php echo REPORT_TEXT_ORDERS; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo REPORT_TEXT_CONVERSION_PER_ORDER; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo REPORT_TEXT_CONVERSION; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo REPORT_TEXT_VARIANCE; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $last_value = 0;
                $sum = 0;
                $avg = 0;
                for ($i = 0; $i < $report->size; $i++) {
                  if ($last_value != 0) {
                    $percent = 100 * $report->info[$i]['sum'] / $last_value - 100;
                  } else {
                    $percent = "0";
                  }
                  $sum += $report->info[$i]['sum'];
                  $avg += $report->info[$i]['avg'];
                  $last_value = $report->info[$i]['sum'];
                  ?>
                <tr class="dataTableRow">
                  <td class="dataTableContent">
                      <?php
                      if (strlen($report->info[$i]['link']) > 0) {
                        echo '<a href="' . zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, $report->info[$i]['link']) . '">';
                      }
                      echo $report->info[$i]['text'];
                      if (strlen($report->info[$i]['link']) > 0) {
                        echo '</a>';
                      }
                      ?></td>
                  <td class="dataTableContent text-center"><?php echo $report->info[$i]['count'] ?></td>
                  <td class="dataTableContent text-right"><?php echo $currencies->format($report->info[$i]['avg']) ?></td>
                  <td class="dataTableContent text-right"><?php echo $currencies->format($report->info[$i]['sum']) ?></td>
                  <td class="dataTableContent text-right">
                      <?php
                      if ($percent == 0) {
                        echo "---";
                      } else {
                        echo number_format($percent, 0) . "%";
                      }
                      ?>
                  </td>
                </tr>
                <?php
              }
              ?>
            <tbody>
            <tfoot>
                <?php
                if (strlen($report->previous . " " . $report->next) > 1) {
                  ?>
                <tr>
                  <td colspan="2">
                      <?php if (strlen($report->previous) > 0) { ?>
                      <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, $report->previous); ?>"><?php echo TEXT_PREVIOUS_LINK; ?></a>
                    <?php } ?>
                  </td>
                  <td>&nbsp;</td>
                  <td colspan="2" class="text-right">
                      <?php if (strlen($report->next) > 0) { ?>
                      <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, $report->next); ?>"><?php echo TEXT_NEXT_LINK; ?></a>
                    <?php } ?>
                  </td>
                </tr>
                <?php
              }
              ?>
            </tfoot>
          </table>
        </div>
        <table class="table">
            <?php if (!empty($order_cnt)) { /* This section of code does not appear to be executed */
              ?>
            <tr class="dataTableRow">
              <td class="dataTableContent text-right"><?php echo '<strong>' . AVERAGE_ORDER . ' </strong>' ?></td>
              <td class="dataTableContent text-right"><?php echo $currencies->format($sum / $order_cnt) ?></td>
            </tr>
            <?php
          }
          if ($report->size != 0) {
            ?>
            <tr class="dataTableRow">
              <td class="dataTableContent text-right"><?php echo '<strong>' . $summary1 . ' </strong>' ?></td>
              <td class="dataTableContent text-right"><?php echo $currencies->format($sum / $report->size) ?></td>
            </tr>
          <?php } ?>
          <tr class="dataTableRow">
            <td class="dataTableContent text-right"><?php echo '<strong>' . $summary2 . ' </strong>' ?></td>
            <td class="dataTableContent text-right"><?php echo $currencies->format($sum) ?></td>
          </tr>
        </table>
        <table class="table table-condensed">
          <tr class="dataTableRow">
            <td class="dataTableContent" width="80%" align="left"><?php echo FILTER_STATUS ?></td>
            <td class="dataTableContent" align="right"><?php echo FILTER_VALUE ?></td>
          </tr>
          <?php
          if (($sales_report_filter) == 0) {
            for ($i = 0; $i < $report->status_available_size; $i++) {
              $sales_report_filter .= "0";
            }
          }
          for ($i = 0; $i < $report->status_available_size; $i++) {
            ?>
            <tr>
              <td class="dataTableContent" align="left"><?php echo $report->status_available[$i]['value'] ?></a></td>
              <?php
              if (substr($sales_report_filter, $i, 1) == "0") {
                $tmp = substr($sales_report_filter, 0, $i) . "1" . substr($sales_report_filter, $i + 1, $report->status_available_size - ($i + 1));
                $tmp = zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, $report->filter_link . "&filter=" . $tmp);
                ?>
                <td class="dataTableContent" width="100%" align="right">
                  <?php echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) ?>&nbsp;
                  <a href="<?php echo $tmp; ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) ?></a></td>
                <?php
              } else {
                $tmp = substr($sales_report_filter, 0, $i) . "0" . substr($sales_report_filter, $i + 1);
                $tmp = zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS, $report->filter_link . "&filter=" . $tmp);
                ?>
                <td class="dataTableContent" width="100%" align="right">
                  <a href="<?php echo $tmp; ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) ?></a>
                  &nbsp;<?php echo zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) ?></td>
                <?php
              }
              ?>
            </tr>
            <?php
          }
          ?>
        </table>
      </div>
      <!-- body_text_eof //-->
    </div>
    <?php //die('<pre>' . print_r($report, true));  ?>

    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
