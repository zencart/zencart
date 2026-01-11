<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

$currencies ??= new currencies();

// pre-fetch key metrics for KPI cards
// we keep these hardcoded as they are specific to the header design
$orders_today    = $db->Execute("select count(*) as count from " . TABLE_ORDERS . " where date_purchased > '" . date('Y-m-d') . "'");
$revenue_today   = $db->Execute("select sum(value) as total from " . TABLE_ORDERS_TOTAL . " ot left join " . TABLE_ORDERS . " o on o.orders_id = ot.orders_id where o.date_purchased > '" . date('Y-m-d') . "' AND ot.class = 'ot_total'");
$customers_today = $db->Execute("select count(*) as count from " . TABLE_CUSTOMERS_INFO . " where customers_info_date_account_created > '" . date('Y-m-d') . "'");
$reviews_pending = $db->Execute("select count(*) as count from " . TABLE_REVIEWS . " where status = 0");

// zone definitions
// each zone is an array of widget file paths to include
// layout zones:
// - main:    The big column on the left (9/12 width)
// - sidebar: The narrow column on the right (3/12 width)
// - bottom:  Full width row at the bottom (12/12 width)
// bottom widgets must include their own column divs for proper layout
$zones = [
    'main'    => [], // The big column (Left)
    'sidebar' => [], // The narrow column (Right)
    'bottom'  => []  // Full width (Bottom)
];

// main zone widgets
$zones['main'][] = DIR_WS_MODULES . 'dashboard_widgets/SalesReportDashboardWidget.php';
$zones['main'][] = DIR_WS_MODULES . 'dashboard_widgets/RecentOrdersDashboardWidget.php';

// sidebar zone widgets
$zones['sidebar'][] = DIR_WS_MODULES . 'dashboard_widgets/OrderStatusDashboardWidget.php';
$zones['sidebar'][] = DIR_WS_MODULES . 'dashboard_widgets/WhosOnlineDashboardWidget.php';
$zones['sidebar'][] = DIR_WS_MODULES . 'dashboard_widgets/MostPopularProductsDashboardWidget.php';

// bottom zone widgets
$zones['bottom'][] = DIR_WS_MODULES . 'dashboard_widgets/TrafficDashboardWidget.php';
$zones['bottom'][] = DIR_WS_MODULES . 'dashboard_widgets/SpecialsDashboardWidget.php';
$zones['bottom'][] = DIR_WS_MODULES . 'dashboard_widgets/BaseStatisticsDashboardWidget.php';

// Notifier for plugins to inject their own widgets into zones
$zco_notifier->notify('NOTIFY_ADMIN_DASHBOARD_ZONES', null, $zones);
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .dashboard-wrapper {background-color: #ecf0f5; padding: 20px 15px;}
        .dashboard-wrapper a, .dashboard-wrapper .label {font-size: 12px;}

        .widget-wrapper { margin-bottom: 25px; background: #fff; border-radius: 3px; border-top: 3px solid #d2d6de; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
        .widget-wrapper.no-padding .panel-body { padding: 0; }

        .widget-wrapper .panel-heading { color: #444; background-color: #fff; border-bottom: 1px solid #f4f4f4; padding: 10px 15px; font-size: 16px; font-weight: 600; }
        .widget-wrapper .panel-heading i { margin-right: 8px; color: #666; }

        .kpi-card { border-radius: 3px; color: #fff; margin-bottom: 20px; box-shadow: 0 1px 1px rgba(0,0,0,0.1); overflow: hidden; position: relative; }
        .kpi-card .inner { padding: 15px; }
        .kpi-card h3 { font-size: 34px; font-weight: 700; margin: 0 0 5px 0; }
        .kpi-card p { font-size: 14px; margin: 0; opacity: 0.9; text-transform: uppercase; font-weight: 600;}
        .kpi-card .icon { position: absolute; top: 10px; right: 20px; font-size: 65px; color: rgba(0,0,0,0.10); transition: 0.3s; }
        .kpi-card:hover .icon { font-size: 75px; color: rgba(0,0,0,0.15); }
        .kpi-card-footer { display: block; padding: 6px 0; text-align: center; background: rgba(0,0,0,0.1); color: #fff; text-decoration: none; font-size: 13px; font-weight: 500;}
        .kpi-card-footer:hover { background: rgba(0,0,0,0.2); color: #fff; text-decoration: none; }

        .bg-aqua { background-color: #00c0ef !important; }
        .bg-green { background-color: #00a65a !important; }
        .bg-yellow { background-color: #f39c12 !important; }
        .bg-red { background-color: #dd4b39 !important; }
    </style>
</head>
<body class="indexDashboard">

<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<?php
$notifications = new AdminNotifications();
$availableNotifications = $notifications->getNotifications('index', $_SESSION['admin_id']);
require_once(DIR_WS_MODULES . 'notificationsDisplay.php');
?>

<div class="container-fluid dashboard-wrapper">

    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="kpi-card bg-aqua">
                <div class="inner">
                    <h3><?php echo $orders_today->fields['count']; ?></h3>
                    <p><?php echo BOX_KPI_ORDERS_TODAY; ?></p>
                </div>
                <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                <a href="<?php echo zen_href_link(FILENAME_ORDERS); ?>" class="kpi-card-footer"><?php echo BOX_KPI_MORE_INFO; ?> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="kpi-card bg-green">
                <div class="inner">
                    <h3><?php echo $currencies->format($revenue_today->fields['total']); ?></h3>
                    <p><?php echo BOX_KPI_REVENUE_TODAY; ?></p>
                </div>
                <div class="icon"><i class="fa fa-dollar"></i></div>
                <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS); ?>" class="kpi-card-footer"><?php echo BOX_KPI_MORE_INFO; ?> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="kpi-card bg-yellow">
                <div class="inner">
                    <h3><?php echo $customers_today->fields['count']; ?></h3>
                    <p><?php echo BOX_KPI_CUSTOMERS_TODAY; ?></p>
                </div>
                <div class="icon"><i class="fa fa-user-plus"></i></div>
                <a href="<?php echo zen_href_link(FILENAME_CUSTOMERS); ?>" class="kpi-card-footer"><?php echo BOX_KPI_MORE_INFO; ?> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="kpi-card bg-red">
                <div class="inner">
                    <h3><?php echo $reviews_pending->fields['count']; ?></h3>
                    <p><?php echo BOX_KPI_REVIEWS_PENDING; ?></p>
                </div>
                <div class="icon"><i class="fa fa-comments"></i></div>
                <a href="<?php echo zen_href_link(FILENAME_REVIEWS, 'status=1'); ?>" class="kpi-card-footer"><?php echo BOX_KPI_MORE_INFO; ?> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <?php
            foreach($zones['main'] as $widget) {
                if (file_exists($widget)) include $widget;
            }
            ?>
        </div>

        <div class="col-md-3">
            <?php
            foreach($zones['sidebar'] as $widget) {
                if (file_exists($widget)) include $widget;
            }
            ?>
        </div>
    </div>

    <div class="row">
        <?php
        foreach($zones['bottom'] as $widget) {
            if (file_exists($widget)) include $widget;
        }
        ?>
    </div>

</div>
