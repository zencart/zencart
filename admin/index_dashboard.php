<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Erik Kerkhoven 2020 Jun 17 Modified in v1.5.7 $
 */

if (empty($currencies)) {
    require_once DIR_WS_CLASSES . 'currencies.php';
    $currencies = new currencies();
}

?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <!--Load the AJAX API FOR GOOGLE GRAPHS -->
    <script src="https://www.gstatic.com/charts/loader.js" title="google_graphs_api"></script>
    <style>
      /* #coltwo div.row span.left { float: left; text-align: left; width: 50%; white-space: nowrap; }*/
      #colthree div.row span.left { float: left; text-align: left; width: 50%; white-space: nowrap; }
      #div.row span.center { margin-right: 30px; }
      .indented { padding-left: 5%; margin-right: 5%;}
      div.first { float: left; width: 90px; }
      div.col { float: left; width: 18%; }
    </style>
  </head>
  <body class="indexDashboard">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <?php
    $notifications = new AdminNotifications();
    $availableNotifications = $notifications->getNotifications('index', $_SESSION['admin_id']);
    require_once(DIR_WS_MODULES . 'notificationsDisplay.php');
    ?>

    <div id="colone" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">

        <?php
        include DIR_WS_MODULES . 'dashboard_widgets/BaseStatisticsDashboardWidget.php';
        ?>

        <?php
        include DIR_WS_MODULES . 'dashboard_widgets/SpecialsDashboardWidget.php';
        ?>

        <?php
        include DIR_WS_MODULES . 'dashboard_widgets/OrderStatusDashboardWidget.php';
        ?>

    </div>
    <div id="coltwo" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">

        <?php
        include DIR_WS_MODULES . 'dashboard_widgets/RecentCustomersDashboardWidget.php';
        ?>
        <?php
        include DIR_WS_MODULES . 'dashboard_widgets/WhosOnlineDashboardWidget.php';
        ?>
        <?php
        include DIR_WS_MODULES . 'dashboard_widgets/TrafficDashboardWidget.php';
        ?>

    </div>
    <div id="colthree" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">

        <?php
        include DIR_WS_MODULES . 'dashboard_widgets/RecentOrdersDashboardWidget.php';
        ?>
        <?php
        include DIR_WS_MODULES . 'dashboard_widgets/SalesReportDashboardWidget.php';
        ?>

    </div>

