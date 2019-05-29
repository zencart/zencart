<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2019 Mar 31 Modified in v1.5.6b $
 */
$number_of_customers = 15;
$number_of_orders = 25;
$number_of_visitors_history = 15;

$notifications = new AdminNotifications();
$availableNotifications = $notifications->getNotifications('index', $_SESSION['admin_id']);

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$customers = $db->Execute("select count(*) as count from " . TABLE_CUSTOMERS, false, true, 1800);
$products = $db->Execute("select count(*) as count from " . TABLE_PRODUCTS . " where products_status = 1", false, true, 1800);
$products_off = $db->Execute("select count(*) as count from " . TABLE_PRODUCTS . " where products_status = 0", false, true, 1800);

$reviews = $db->Execute("select count(*) as count from " . TABLE_REVIEWS);
$reviews_pending = $db->Execute("select count(*) as count from " . TABLE_REVIEWS . " where status = 0", false, true, 1800);

$newsletters = $db->Execute("select count(*) as count from " . TABLE_CUSTOMERS . " where customers_newsletter = 1", false, true, 1800);

$counter = $db->Execute("select startdate, counter from " . TABLE_COUNTER, false, true, 7200);
$counter_startdate = $counter->fields['startdate'];
if ($counter->RecordCount()) {
  $counter_startdate_formatted = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
}

$specials = $db->Execute("select count(*) as count from " . TABLE_SPECIALS . " where status = 0", false, true, 1800);
$specials_act = $db->Execute("select count(*) as count from " . TABLE_SPECIALS . " where status = 1", false, true, 1800);
$featured = $db->Execute("select count(*) as count from " . TABLE_FEATURED . " where status = 0", false, true, 1800);
$featured_act = $db->Execute("select count(*) as count from " . TABLE_FEATURED . " where status = 1", false, true, 1800);
$salemaker = $db->Execute("select count(*) as count from " . TABLE_SALEMAKER_SALES . " where sale_status = 0", false, true, 1800);
$salemaker_act = $db->Execute("select count(*) as count from " . TABLE_SALEMAKER_SALES . " where sale_status = 1", false, true, 1800);


$i = 0;
$visit_history = array();
//  Get the visitor history data
$visits_query = "select startdate, counter, session_counter from " . TABLE_COUNTER_HISTORY . " order by startdate DESC";
$visits = $db->Execute($visits_query, $number_of_visitors_history, true, 1800);
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

// Build the whos-online graph
$whos_online = $db->Execute("select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id, host_address, user_agent from " . TABLE_WHOS_ONLINE);
// Initialize array variables for display.
$user_array = $guest_array = $spider_array = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
$status = 0;

foreach ($whos_online as $session) {
  $which_query = $db->Execute("select sesskey, value from " . TABLE_SESSIONS . " where sesskey = '" . $session['session_id'] . "'");
  $who_query = $db->Execute("select session_id, time_entry, time_last_click, host_address, user_agent from " . TABLE_WHOS_ONLINE . " where session_id = '" . $session['session_id'] . "'");

// longer than 3 minutes light color
  $xx_mins_ago_long = (time() - 180);

  switch (true) {
    case ($which_query->RecordCount() == 0):
      if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
        $status = 3;
      } else {
        $status = 2;
      }
      break;
    case (strstr($which_query->fields['value'], '"contents";a:0:')):
      if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
        $status = 3;
      } else {
        $status = 2;
      }
      break;
    case (!strstr($which_query->fields['value'], '"contents";a:0:')):
      if ($who_query->fields['time_last_click'] < $xx_mins_ago_long) {
        $status = 1;
      } else {
        $status = 0;
      }
      break;
  }

  if (empty($session['session_id'])) {
    $spider_array[$status] += 1;
  } else {
    if ($session['full_name'] == "&yen;Guest") {
      $guest_array[$status] += 1;
    } else {
      $user_array[$status] += 1;
    }
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link href="includes/stylesheet.css" rel="stylesheet">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script title="menu_init">
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>

    <!--Load the AJAX API FOR GOOGLE GRAPHS -->
    <script src="https://www.google.com/jsapi"></script>
    <script title="build_graphs">
      var data;
      var chart;
      // Load the Visualization API and the piechart package.
      google.load('visualization', '1', {'packages': ['corechart']});
      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawCharts);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and draws it.
      function drawCharts() {

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
          chart = new google.visualization.ColumnChart(document.getElementById('graph'));
          //    google.visualization.events.addListener(chart, 'select', selectHandler);
          chart.draw(data, options);

          // Sales //
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
              vAxis: {title: '<?php echo DASHBOARD_DOLLARS; ?>'},
              width: '100%',
              height: '100%',
              backgroundColor: {fill: "#f7f6ef"},
              legend: {position: 'top'},
              colors: ['dodgerblue']
          };

          chart = new google.visualization.ColumnChart(document.getElementById('graph2'));
          chart.draw(data, options);
      }
    </script>


    <style>
      /* #coltwo div.row span.left { float: left; text-align: left; width: 50%; white-space: nowrap; }*/
      #colthree div.row span.left { float: left; text-align: left; width: 50%; white-space: nowrap; }
      #div.row span.center { margin-right: 30px; }
      .indented { padding-left: 5%; margin-right: 5%;}
      div.first { float: left; width: 90px; }
      div.col { float: left; width: 18%; }
    </style>

  </head>
  <body class="indexDashboard" onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <?php require_once(DIR_WS_MODULES . 'notificationsDisplay.php'); ?>

    <div id="colone" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
      <div class="panel panel-default reportBox">
        <div class="panel-heading header"><?php echo BOX_TITLE_STATISTICS; ?> </div>
        <table class="table table-striped table-condensed">
            <?php
            if ($counter->RecordCount()) {
              ?>
            <tr>
              <td><?php echo BOX_ENTRY_COUNTER_DATE; ?></td>
              <td class="text-right"><?php echo $counter_startdate_formatted; ?></td>
            </tr>
            <tr>
              <td><?php echo BOX_ENTRY_COUNTER; ?></td>
              <td class="text-right"><?php echo $counter->fields['counter']; ?></td>
            </tr>
            <?php
          }
          ?>
          <tr>
            <td><?php echo BOX_ENTRY_CUSTOMERS; ?></td>
            <td class="text-right"><?php echo $customers->fields['count']; ?></td>
          </tr>
          <tr>
            <td><?php echo BOX_ENTRY_PRODUCTS; ?></td>
            <td class="text-right"><?php echo $products->fields['count']; ?></td>
          </tr>
          <tr>
            <td><?php echo BOX_ENTRY_PRODUCTS_OFF; ?></td>
            <td class="text-right"><?php echo $products_off->fields['count']; ?></td>
          </tr>
          <tr>
            <td><?php echo BOX_ENTRY_REVIEWS; ?></td>
            <td class="text-right"><?php echo $reviews->fields['count']; ?></td>
          </tr>
          <?php if (REVIEWS_APPROVAL == '1') { ?>
            <tr>
              <td><a href="<?php echo zen_href_link(FILENAME_REVIEWS, 'status=1'); ?>"><?php echo BOX_ENTRY_REVIEWS_PENDING; ?></a></td>
              <td class="text-right"><?php echo $reviews_pending->fields['count']; ?></td>
            </tr>
          <?php } ?>
          <tr>
            <td><?php echo BOX_ENTRY_NEWSLETTERS; ?></td>
            <td class="text-right"><?php echo $newsletters->fields['count']; ?></td>
          </tr>
        </table>
      </div>
      <div class="panel panel-default reportBox">
        <div class="panel-heading header"><?php echo BOX_TITLE_FEATURES_SALES; ?></div>
        <table class="table table-striped table-condensed">
          <tr>
            <td><?php echo BOX_ENTRY_SPECIALS_EXPIRED; ?></td>
            <td class="text-right"><?php echo $specials->fields['count']; ?></td>
          </tr>
          <tr>
            <td><?php echo BOX_ENTRY_SPECIALS_ACTIVE; ?></td>
            <td class="text-right"><?php echo $specials_act->fields['count']; ?></td>
          </tr>
          <tr>
            <td><?php echo BOX_ENTRY_FEATURED_EXPIRED; ?></td>
            <td class="text-right"><?php echo $featured->fields['count']; ?></td>
          </tr>
          <tr>
            <td><?php echo BOX_ENTRY_FEATURED_ACTIVE; ?></td>
            <td class="text-right"><?php echo $featured_act->fields['count']; ?></td>
          </tr>
          <tr>
            <td><?php echo BOX_ENTRY_SALEMAKER_EXPIRED; ?></td>
            <td class="text-right"><?php echo $salemaker->fields['count']; ?></td>
          </tr>
          <tr>
            <td><?php echo BOX_ENTRY_SALEMAKER_ACTIVE; ?></td>
            <td class="text-right"><?php echo $salemaker_act->fields['count']; ?></td>
          </tr>
        </table>
      </div>
      <div class="panel panel-default reportBox">
        <div class="panel-heading header"><?php echo BOX_TITLE_ORDERS; ?> </div>
        <table class="table table-striped table-condensed">
            <?php
            $orders_status = $db->Execute("select orders_status_name, orders_status_id from " . TABLE_ORDERS_STATUS . " where language_id = " . (int)$_SESSION['languages_id'], false, true, 3600);

            foreach ($orders_status as $row) {
              $orders_pending = $db->Execute("select count(*) as count from " . TABLE_ORDERS . " where orders_status = " . (int)$row['orders_status_id'], false, true, 1800);
              ?>
            <tr>
              <td><a href="<?php echo zen_href_link(FILENAME_ORDERS, 'selected_box=customers&status=' . $row['orders_status_id']); ?>"><?php echo $row['orders_status_name']; ?></a>:</td>
              <td class="text-right"> <?php echo $orders_pending->fields['count']; ?></td>
            </tr>
            <?php
          }
          ?>
        </table>
      </div>
    </div>
    <div id="coltwo" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
      <div class="panel panel-default reportBox">
        <div class="panel-heading header"><?php echo BOX_ENTRY_NEW_CUSTOMERS; ?> </div>
        <table class="table table-striped table-condensed">
            <?php
            $customers = $db->Execute("SELECT c.customers_id as customers_id, c.customers_firstname as customers_firstname,
                                          c.customers_lastname as customers_lastname, c.customers_email_address as customers_email_address,
                                          a.customers_info_date_account_created as customers_info_date_account_created, a.customers_info_id
                                   FROM " . TABLE_CUSTOMERS . " c
                                   LEFT JOIN " . TABLE_CUSTOMERS_INFO . " a ON c.customers_id = a.customers_info_id
                                   ORDER BY a.customers_info_date_account_created DESC",
                $number_of_customers, true, 1800);

            foreach ($customers as $customer) {
              $customer['customers_firstname'] = zen_output_string_protected($customer['customers_firstname']);
              $customer['customers_lastname'] = zen_output_string_protected($customer['customers_lastname']);
              ?>
            <tr>
              <td>
                <a href="<?php echo zen_href_link(FILENAME_CUSTOMERS, 'search=' . $customer['customers_email_address'] . '&origin=' . FILENAME_DEFAULT); ?>" class="contentlink"><?php echo $customer['customers_firstname'] . ' ' . $customer['customers_lastname']; ?></a>
              </td>
              <td class="text-right"><?php echo zen_date_short($customer['customers_info_date_account_created']); ?></td>
            </tr>
            <?php
          }
          ?>
        </table>
      </div>

      <div class="panel panel-default reportBox">
        <div class="panel-heading header"><?php echo WO_GRAPH_TITLE . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_WHOS_ONLINE) . '">' . WO_GRAPH_MORE . '</a>'; ?></div>
        <table class="table table-striped table-condensed">
          <tr>
            <td><?php echo WO_GRAPH_REGISTERED; ?></td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #5ce400;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($user_array[0] ?: ''); ?>
            </td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #fc0;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($user_array[1] ?: ''); ?>
            </td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #f00;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($user_array[2] ?: ''); ?>
            </td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #ffbaba;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($user_array[3] ?: ''); ?>
            </td>
          </tr>
          <tr>
            <td><?php echo WO_GRAPH_GUEST; ?></td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #5ce400;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($guest_array[0] ?: ''); ?>
            </td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #fc0;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($guest_array[1] ?: ''); ?>
            </td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #f00;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($guest_array[2] ?: ''); ?>
            </td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #ffbaba;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($guest_array[3] ?: ''); ?>
            </td>
          </tr>
          <tr>
            <td><?php echo WO_GRAPH_SPIDER; ?></td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #5ce400;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($spider_array[0] ?: ''); ?>
            </td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #fc0;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($spider_array[1] ?: ''); ?></td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #f00;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($spider_array[2] ?: ''); ?>
            </td>
            <td>
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-1x" style="color: #ffbaba;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;&nbsp;<?php echo ($spider_array[3] ?: ''); ?>
            </td>
          </tr>
          <tr>
            <td colspan="4"><?php echo WO_GRAPH_TOTAL; ?></td>
            <td class="text-right"><?php echo $whos_online->RecordCount(); ?></td>
          </tr>
          <tr class="smallText">
            <td colspan="5">
              <span class="fa-stack">
                <i class="fa fa-circle fa-stack-1x" style="color: #5ce400;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;<?php echo WHOS_ONLINE_ACTIVE_TEXT; ?>&nbsp;&nbsp;
              <span class="fa-stack">
                <i class="fa fa-circle fa-stack-1x" style="color: #fc0;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;<?php echo WHOS_ONLINE_INACTIVE_TEXT; ?>&nbsp;&nbsp;
              <span class="fa-stack">
                <i class="fa fa-circle fa-stack-1x" style="color: #f00;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;<?php echo WHOS_ONLINE_ACTIVE_NO_CART_TEXT; ?>&nbsp;&nbsp;
              <span class="fa-stack">
                <i class="fa fa-circle fa-stack-1x" style="color: #ffbaba;"></i>
                <i class="fa fa-circle-o fa-stack-1x"></i>
              </span>&nbsp;<?php echo WHOS_ONLINE_INACTIVE_NO_CART_TEXT; ?>
            </td>
          </tr>
        </table>
      </div>

      <!--// Counters graph = populated by javascript  //-->
      <div class="panel panel-default reportBox">
        <div class="panel-heading header"><?php echo TEXT_COUNTER_HISTORY_TITLE; ?></div>
        <?php if (count($visit_history)) { ?>
          <div class="panel-body">
            <div id="graph"></div>
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


    </div>
    <div id="colthree" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
      <div class="panel panel-default reportBox">
        <div class="panel-heading header"><?php echo BOX_ENTRY_NEW_ORDERS; ?> </div>
        <table class="table table-striped table-condensed">
            <?php
            $orders = $db->Execute("SELECT o.orders_id as orders_id, o.customers_name as customers_name, o.customers_id,
                                           o.date_purchased as date_purchased, o.currency, o.currency_value, ot.class, ot.text as order_total, ot.value as order_value
                                    FROM " . TABLE_ORDERS . " o
                                    LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON (o.orders_id = ot.orders_id
                                      AND class = 'ot_total')
                                    ORDER BY orders_id DESC",
                $number_of_orders, true, 1800);

            $ds = $dsc = $ys = $ysc = $msc = 0;

            $now = date(DATE_FORMAT);
            $yesterday = date(DATE_FORMAT, strtotime('-1 days'));

            foreach ($orders as $order) {

              if (zen_date_short($order['date_purchased']) == $now) {
                $ds += (float)$order['order_value'];
                $dsc++;
              }
              if (zen_date_short($order['date_purchased']) == $yesterday) {
                $ys += (float)$order['order_value'];
                $ysc++;
              }

              $order['customers_name'] = str_replace("N/A", "", $order['customers_name']);

              $amt = $currencies->format($order['order_value'], false);
              if ($order['currency'] != DEFAULT_CURRENCY) {
                $amt .= " (" . $order['order_total'] . ")";
              }
              ?>
            <tr>
              <td>
                <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'oID=' . $order['orders_id'] . '&origin=' . FILENAME_DEFAULT); ?>" class="contentlink"><?php echo substr($order['customers_name'], 0, 20); ?></a>
              </td>
              <td class="text-center"><?php echo $amt; ?></td>
              <td class="text-right"><?php echo zen_date_short($order['date_purchased']); ?></td>
            </tr>
          <?php } ?>
        </table>
      </div>

      <!--//  sales graph  populated by javascript  //-->
      <div class="panel panel-default reportBox">
        <div class="panel-heading header"><?php echo TEXT_MONTHLY_SALES_TITLE; ?><a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS); ?>"><?php echo TEXT_CLICK_FOR_COMPLETE_DETAILS; ?></a></div>
        <div class="panel-body">
          <div id="graph2"></div>
        </div>
        <table class="table table-striped table-condensed">
          <tr>
            <td><?php echo sprintf(TEXT_SALES_TODAY, $dsc, number_format($ds, 2)); ?></td>
            <td class="text-right"><?php echo sprintf(TEXT_SALES_YESTERDAY, $ysc, number_format($ys, 2)); ?></td>
          </tr>
        </table>
      </div>
    </div>
