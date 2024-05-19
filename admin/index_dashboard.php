<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 15 Modified in v2.0.1 $
 *
 * @var notifier $zco_notifier
 */

if (empty($currencies)) {
    require_once DIR_WS_CLASSES . 'currencies.php';
    $currencies = new currencies();
}

$widgets = [];
$widgets[] = ['column' => 1, 'sort' => 10, 'visible' => true, 'path' => DIR_WS_MODULES . 'dashboard_widgets/BaseStatisticsDashboardWidget.php'];
$widgets[] = ['column' => 1, 'sort' => 15, 'visible' => true, 'path' => DIR_WS_MODULES . 'dashboard_widgets/SpecialsDashboardWidget.php'];
$widgets[] = ['column' => 1, 'sort' => 20, 'visible' => true, 'path' => DIR_WS_MODULES . 'dashboard_widgets/OrderStatusDashboardWidget.php'];
$widgets[] = ['column' => 2, 'sort' => 10, 'visible' => true, 'path' => DIR_WS_MODULES . 'dashboard_widgets/RecentCustomersDashboardWidget.php'];
$widgets[] = ['column' => 2, 'sort' => 15, 'visible' => true, 'path' => DIR_WS_MODULES . 'dashboard_widgets/WhosOnlineDashboardWidget.php'];
$widgets[] = ['column' => 2, 'sort' => 20, 'visible' => true, 'path' => DIR_WS_MODULES . 'dashboard_widgets/TrafficDashboardWidget.php'];
$widgets[] = ['column' => 3, 'sort' => 10, 'visible' => true, 'path' => DIR_WS_MODULES . 'dashboard_widgets/RecentOrdersDashboardWidget.php'];
$widgets[] = ['column' => 3, 'sort' => 15, 'visible' => true, 'path' => DIR_WS_MODULES . 'dashboard_widgets/SalesReportDashboardWidget.php'];

$zco_notifier->notify('NOTIFY_ADMIN_DASHBOARD_WIDGETS', null, $widgets);

// Prepare for sorting: ensure each has its dependent columns, so multisort doesn't complain about inconsistent array sizes
foreach ($widgets as $key => $widget) {
    if (!isset($widget['sort'])) {
        $widgets[$key]['sort'] = 999;
    }
    if (!isset($widget['column'])) {
        $widgets[$key]['column'] = 0; // 0-unspecified, will be ignored
    }
}

// Sort in advance so the template can simply loop over each column without re-sorting.
array_multisort(array_column($widgets, 'column'), SORT_ASC, array_column($widgets, 'sort'), SORT_ASC, $widgets);

// Path validation (catch invalid path errors) and security LFI check (prevent loading files from outside)
$acceptedPath = realPath(DIR_FS_CATALOG);
foreach ($widgets as $key => $widget) {
    $realPath = realpath($widget['path']);
    if ($realPath === false || !str_starts_with($realPath, $acceptedPath) || !file_exists($widget['path'])) {
        unset($widgets[$key]); // Skip if it's not under the intended directory or doesn't exist
    }
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
    foreach ($widgets as $widget) {
        if ($widget['column'] === 1 && !empty($widget['visible'])) {
            include $widget['path'];
        }
    }
    ?>
    </div>
    <div id="coltwo" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
    <?php
    foreach ($widgets as $widget) {
        if ($widget['column'] === 2 && !empty($widget['visible'])) {
            include $widget['path'];
        }
    }
    ?>
    </div>
    <div id="colthree" class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
    <?php
    foreach ($widgets as $widget) {
        if ($widget['column'] === 3 && !empty($widget['visible'])) {
            include $widget['path'];
        }
    }
    ?>
    </div>

