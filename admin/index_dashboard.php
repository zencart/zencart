<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All 2019 Mar 31 Modified in v1.5.6b $
 */
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
    <script src="includes/menu.js" title="menu"></script>
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
    <script src="https://www.google.com/jsapi" title="google_graphs_api"></script>


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

