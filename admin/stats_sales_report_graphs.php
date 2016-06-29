<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @author inspired from sales_report_graphs.php,v 0.01 2002/11/27 19:02:22 cwi Exp  Released under the GNU General Public License $
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */

require('includes/application_top.php');

if (!defined('SALES_REPORT_GRAPHS_FILTER_DEFAULT')) define('SALES_REPORT_GRAPHS_FILTER_DEFAULT', '00000000110000000000');

if (($_GET['report']) && (zen_not_null($_GET['report']))) {
    $sales_report_view = (int)$_GET['report'];
}

switch ($_GET['report']) {
    case('1'):
        $summary1    = 'Average Hourly';
        $summary2    = TODAY_TO_DATE;
        $report_desc = 'Hourly';
        break;
    case('2'):
        $summary1    = 'Average Daily';
        $summary2    = WEEK_TO_DATE;
        $report_desc = 'Daily';
        break;
    case('3'):
        $summary1    = 'Average Weekly';
        $summary2    = WEEK_TO_DATE;
        $report_desc = 'Weekly';
        break;

    case('4'):
        $summary1    = 'Average Monthly';
        $summary2    = MONTH_TO_DATE;
        $report_desc = 'Monthly';
        break;

    case('5'):
        $summary1    = 'Average Yearly';
        $summary2    = 'Yearly Total';
        $report_desc = 'Yearly';
        break;

    default:
        // default view (monthly)
        $sales_report_view = 4;
        // report views (1:hourly, 2:daily, 3:weekly, 4:monthly, 5:yearly)
        $report      = 4;
        $summary1    = 'Average monthly';
        $summary2    = MONTH_TO_DATE;
        $report_desc = 'Monthly';
        break;

}

// check start and end Date
$startDate = "";
if (($_GET['startDate']) && (zen_not_null($_GET['startDate']))) {
    $startDate = $_GET['startDate'];
}
$endDate = "";
if (($_GET['endDate']) && (zen_not_null($_GET['endDate']))) {
    $endDate = $_GET['endDate'];
}
// check filters
if (($_GET['filter']) && (zen_not_null($_GET['filter']))) {
    $sales_report_filter      = $_GET['filter'];
    $sales_report_filter_link = "&filter=$sales_report_filter";
} elseif (defined('SALES_REPORT_GRAPHS_FILTER_DEFAULT')) {
    $sales_report_filter      = SALES_REPORT_GRAPHS_FILTER_DEFAULT;
    $sales_report_filter_link = "&filter=$sales_report_filter";
}

require(DIR_WS_CLASSES . 'stats_sales_report_graph.php');
$report = new statsSalesReportGraph($sales_report_view, $startDate, $endDate, $sales_report_filter);

if (strlen($sales_report_filter) == 0) {
    $sales_report_filter      = $report->filter;
    $sales_report_filter_link = "";
}

require('includes/admin_html_head.php');
?>
  <script type="text/javascript" src="https://www.google.com/jsapi"></script>
  <script type="text/javascript">
    // Load the Visualization API and the piechart package.
    google.load('visualization', '1.0', {'packages':['corechart']});

    // Set a callback to run when the Google Visualization API is loaded.
    google.setOnLoadCallback(drawChart);

     function drawChart() {

         // Create the data table.
         var data = new google.visualization.DataTable();
         data.addColumn('string', 'label');
         data.addColumn('number', '<?php echo 'Total Sales'; ?>');
<?php if ($sales_report_view < 5) { ?>
         data.addColumn('number', '<?php echo 'Average Sale Amount'; ?>');
<?php } ?>

         data.addRows([
<?php
  for ($i = 0; $i < $report->size; $i ++) {

    // column name
    echo "           ['";

    if ($sales_report_view == 5 && $report->size > 5) {
      echo substr($report->info[$i]['text'], 0,1);
    } elseif ($sales_report_view == 4) {
      echo substr($report->info[$i]['text'], 0,3);
    } elseif ($sales_report_view == 3) {
      echo substr($report->info[$i]['text'], 0,5);
    } elseif ($sales_report_view == 1) {
      echo ltrim(substr($report->info[$i]['text'], 0,2), '0');
    } elseif ($report->size > 5) {
      echo substr($report->info[$i]['text'], 3,2);
    } else {
      echo substr($report->info[$i]['text'], 0,5);
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
    if (($i+1) < $report->size) {
      echo ',' . "\n";
    }
  }
?>

         ]);

         // Set chart options
         var options = {
                'title':'<?php echo $report_desc; ?>',
                'legend':'bottom',
                'is3D':false,
                'height':400,
                'width':600  <?php //echo ($report->size > 2 ? '200' : ($report->size * 50); ?>
               };
         <?php // echo $scale; ?>

     // Instantiate and draw our chart, passing in some options.
     var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
     chart.draw(data, options);
   }
</script>
</head>
<body onload="init()">
<!-- header //-->
<div class="header-area">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
</div>
<!-- header_eof //-->
<!-- body //-->
<h1><?php echo $report_desc . ' ' . HEADING_TITLE; ?></h1>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
    <tr>
        <!-- body_text //-->
        <td width="100%" valign="top">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                    <td colspan="2">
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                            <tr>
                                <td align="right" class="menuBoxHeading">
                                    <?php
                                    echo '<a href="' . zen_href_link('stats_sales_report_graphs.php', 'report=1' . $sales_report_filter_link) . '">' . 'Hourly' . '</a> | ';
                                    echo '<a href="' . zen_href_link('stats_sales_report_graphs.php', 'report=2' . $sales_report_filter_link) . '">' . 'Daily' . '</a> | ';
                                    echo '<a href="' . zen_href_link('stats_sales_report_graphs.php', 'report=3' . $sales_report_filter_link) . '">' . 'Weekly' . '</a> | ';
                                    echo '<a href="' . zen_href_link('stats_sales_report_graphs.php', 'report=4' . $sales_report_filter_link) . '">' . 'Monthly' . '</a> | ';
                                    echo '<a href="' . zen_href_link('stats_sales_report_graphs.php', 'report=5' . $sales_report_filter_link) . '">' . 'Yearly' . '</a>';
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td valign="top" width="200" align="center">

                    <div id="chart_div"></div>

                    </td>
                    <td width="100%" valign="top">
                        <table border="0" width="100%" cellspacing="0" cellpadding="2">
                            <tr>
                                <td valign="top">
                                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                        <tr class="dataTableHeadingRow">
                                            <td class="dataTableHeadingContent"></td>
                                            <td class="dataTableHeadingContent" align="center"><?php echo 'Orders'; ?></td>
                                            <td class="dataTableHeadingContent" align="right"><?php echo 'Conversion per order'; ?></td>
                                            <td class="dataTableHeadingContent" align="right"><?php echo 'Conversion'; ?></td>
                                            <td class="dataTableHeadingContent" align="right"><?php echo 'Variance'; ?></td>
                                        </tr>
                                        <?php
                                        $last_value = 0;
                                        $sum        = 0;
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
                                            <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver';this.style.cursor='hand'" onmouseout="this.className='dataTableRow'">
                                                <td class="dataTableContent">
                                                    <?php
                                                    if (strlen($report->info[$i]['link']) > 0) {
                                                        echo '<a href="' . zen_href_link('stats_sales_report_graphs.php', $report->info[$i]['link']) . '">';
                                                    }
                                                    echo $report->info[$i]['text'];
                                                    if (strlen($report->info[$i]['link']) > 0) {
                                                        echo '</a>';
                                                    }
                                                    ?></td>
                                                <td class="dataTableContent" align="center"><?php echo $report->info[$i]['count'] ?></td>
                                                <td class="dataTableContent" align="right"><?php echo $currencies->format($report->info[$i]['avg']) ?></td>
                                                <td class="dataTableContent" align="right"><?php echo $currencies->format($report->info[$i]['sum']) ?></td>
                                                <td class="dataTableContent" align="right">
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
                                    <?php
                                        if (strlen($report->previous . " " . $report->next) > 1) {
                                            ?>
                                            <tr>
                                                <td width="100%" colspan="5">
                                                    <table width="100%">
                                                        <tr>
                                                            <td align="left">
                                                                <?php
                                                                if (strlen($report->previous) > 0) {
                                                                    echo '<a href="' . zen_href_link('stats_sales_report_graphs.php', $report->previous, 'NONSSL') . '">&lt;&lt;&nbsp;Previous</a>';
                                                                }
                                                                ?>
                                                            </td>
                                                            <td align="right">
                                                                <?php
                                                                if (strlen($report->next) > 0) {
                                                                    echo '<a href="' . zen_href_link('stats_sales_report_graphs.php', $report->next, 'NONSSL') . '">Next&nbsp;&gt;&gt;</a>';
                                                                    echo "";
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                    <p>
                                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                        <?php if ($order_cnt != 0) {
                                            ?>
                                            <tr class="dataTableRow">
                                                <td class="dataTableContent" width="100%" align="right"><?php echo '<b>' . AVERAGE_ORDER . ' </b>' ?></td>
                                                <td class="dataTableContent" align="right"><?php echo $currencies->format($sum / $order_cnt) ?></td>
                                            </tr>
                                        <?php }
                                        if ($report->size != 0) {
                                            ?>
                                            <tr class="dataTableRow">
                                                <td class="dataTableContent" width="100%" align="right"><?php echo '<b>' . $summary1 . ' </b>' ?></td>
                                                <td class="dataTableContent" align="right"><?php echo $currencies->format($sum / $report->size) ?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr class="dataTableRow">
                                            <td class="dataTableContent" width="100%" align="right"><?php echo '<b>' . $summary2 . ' </b>' ?></td>
                                            <td class="dataTableContent" align="right"><?php echo $currencies->format($sum) ?></td>
                                        </tr>
                                    </table>
                                    <br>
                                    <table border="0" width="40%" cellspacing="0" cellpadding="2">
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
                                                    $tmp = zen_href_link('stats_sales_report_graphs.php', $report->filter_link . "&filter=" . $tmp, 'NONSSL');
                                                    ?>
                                                    <td class="dataTableContent" width="100%" align="right">
                                                        <?php echo zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) ?>&nbsp;
                                                        <a href="<?php echo $tmp; ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) ?></a></td>
                                                    <?php
                                                } else {
                                                    $tmp = substr($sales_report_filter, 0, $i) . "0" . substr($sales_report_filter, $i + 1);
                                                    $tmp = zen_href_link('stats_sales_report_graphs.php', $report->filter_link . "&filter=" . $tmp, 'NONSSL');
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
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
        <!-- body_text_eof //-->
    </tr>
</table>
<?php //die('<pre>' . print_r($report, true)); ?>

<!-- body_eof //-->
<!-- footer //-->
<div class="footer-area">
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</div>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
