<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

$currencies ??= new currencies();

// make sure Dashboard Layout Config exists
if (!$sniffer->field_exists(TABLE_ADMIN, 'dashboard_layout')) {
    $db->Execute("ALTER TABLE " . TABLE_ADMIN ." ADD dashboard_layout TEXT NULL AFTER mfa");
}

// check database for saved layout
$dashboard_layout = $db->Execute("SELECT dashboard_layout FROM " . TABLE_ADMIN . " WHERE admin_id = " .(int)$_SESSION['admin_id']);

// pre-fetch key metrics for KPI cards
// we keep these hardcoded as they are specific to the header design
$orders_today = $db->Execute("SELECT COUNT(*) AS count FROM " . TABLE_ORDERS . " WHERE date_purchased > CURDATE()");
$revenue_today = $db->Execute("SELECT SUM(value) AS total FROM " . TABLE_ORDERS_TOTAL . " ot LEFT JOIN " . TABLE_ORDERS . " o ON o.orders_id = ot.orders_id where o.date_purchased > CURDATE() AND ot.class = 'ot_total'");
$customers_today = $db->Execute("SELECT COUNT(*) AS count FROM " . TABLE_CUSTOMERS_INFO . " WHERE customers_info_date_account_created > CURDATE()");
$reviews_pending = $db->Execute("SELECT COUNT(*) AS count FROM " . TABLE_REVIEWS . " WHERE status = 0");

// zone definitions
// each zone is an array of widget file paths to include
// layout zones:
// - main:    The big column on the left (9/12 width)
// - sidebar: The narrow column on the right (3/12 width)
// - bottom:  Full width row at the bottom (12/12 width)
$default_zones = [
    'main' => [
        'SalesReportDashboardWidget.php',
        'RecentOrdersDashboardWidget.php',
    ],
    'sidebar' => [
        'OrderStatusDashboardWidget.php',
        'MostPopularProductsDashboardWidget.php',
        'WhosOnlineDashboardWidget.php',
    ],
    'bottom' => [
        'TrafficDashboardWidget.php',
        'SpecialsDashboardWidget.php',
        'BaseStatisticsDashboardWidget.php',
    ],
];

$zones = json_decode($dashboard_layout->fields['dashboard_layout'] ?? '', true);

// if DB config is empty or invalid, use default
if (!is_array($zones) || empty($zones)) {
    $zones = $default_zones;
}

// render a zone
function render_zone($zone_name, $widgets_array)
{
    /** Globals needed inside widgets */
    global $db, $zco_notifier, $messageStack, $zcDate;

    // Define the accepted base path for widgets to prevent LFI vulnerabilities
    $acceptedPath = realPath(DIR_FS_CATALOG);

    $zone_name = zen_output_string_protected($zone_name);

    echo '<ul id="zone-' . $zone_name . '" class="sortable-list list-unstyled row" style="min-height: 200px; padding-bottom: 50px;">';

    foreach ($widgets_array as $widget_file) {
        $path = '';

        // check if this is an Encapsulated Plugin (absolute path provided)
        if (file_exists($widget_file)) {
            $path = $widget_file;
        } // treat as Core Widget (relative filename provided)
        else {
            $path = DIR_WS_MODULES . 'dashboard_widgets/' . $widget_file;
        }

        // Path validation (catch invalid path errors) and security LFI check (prevent loading files from outside)
        $realPath = realpath($path);
        if ($realPath === false || !str_starts_with($realPath, $acceptedPath) || !file_exists($path)) {
            continue; // skip this widget if path is invalid, insecure, or file doesn't exist
        }

        $widget_name = basename($path);
        $col_class = 'col-md-12';

        if ($zone_name === 'bottom') {
            if ($widget_name === 'TrafficDashboardWidget.php') {
                $col_class = 'col-xs-12 col-md-6'; // traffic gets half width
            } else {
                $col_class = 'col-xs-12 col-md-3'; // others get quarter width
            }
        }

        // data-markers for JS
        $data_attr = 'data-id="' . $widget_name . '"';
        $li_class = $col_class . ' widget-li';

        // Traffic widget - prevent it moving to sidebar
        if ($widget_name === 'TrafficDashboardWidget.php') {
            $li_class .= ' locked-bottom';
        }

        echo '<li class="' . $li_class . '" ' . $data_attr . '>';
        include $path;
        echo '</li>';
    }
    echo '</ul>';
}

// legacy widgets support
$widgets = [];

// Notifier for plugins to inject their own widgets into zones
$zco_notifier->notify('NOTIFY_ADMIN_DASHBOARD_WIDGETS', null, $widgets, $zones);

foreach ($widgets as $widget) {
    if (!isset($widget['path'])) {
        continue;
    }

    $file_path = $widget['path'];
    $file_name = basename($file_path);
    $found_in_zones = false;

    // check existing zones to see if this widget is already there
    foreach ($zones as $z_name => &$z_files) {
        if (!is_array($z_files)) {
            continue;
        }

        foreach ($z_files as $key => &$z_file) {
            if (basename($z_file) === $file_name) {
                $found_in_zones = true;

                // If the DB has just the filename, but the plugin gives us a full path,
                // update the zone entry to use the full path.
                if ($z_file !== $file_path) {
                    $z_file = $file_path;
                }
                break 2;
            }
        }
    }

    // if not found in zones, inject it
    if (!$found_in_zones) {
        // legacy column 3 -> sidebar zone
        if (isset($widget['column']) && $widget['column'] == 3) {
            if (!isset($zones['sidebar'])) {
                $zones['sidebar'] = [];
            }
            array_unshift($zones['sidebar'], $file_path);
        } else {
            // legacy column 1 or 2 -> main zone
            if (!isset($zones['main'])) {
                $zones['main'] = [];
            }
            $zones['main'][] = $file_path;
        }
    }
}
?>

<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js" integrity="sha256-SERKgtTty1vsDxll+qzd4Y2cF9swY9BCq62i9wXJ9Uo=" crossorigin="anonymous"></script>
</head>
<body class="indexDashboard">

<?php require DIR_WS_INCLUDES . 'header.php'; ?>

<div class="container-fluid dashboard-wrapper">

    <div class="row">
        <div class="col-xs-6 col-lg-3">
            <div class="kpi-card bg-aqua">
                <div class="inner">
                    <h3><?php echo $orders_today->fields['count']; ?></h3>
                    <p><?php echo BOX_KPI_ORDERS_TODAY; ?></p>
                </div>
                <div class="icon"><i class="fa fa-shopping-cart"></i></div>
                <a href="<?php echo zen_href_link(FILENAME_ORDERS); ?>"
                   class="kpi-card-footer"><?php echo BOX_KPI_MORE_INFO; ?> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-xs-6 col-lg-3">
            <div class="kpi-card bg-green">
                <div class="inner">
                    <h3><?php echo $currencies->format($revenue_today->fields['total']); ?></h3>
                    <p><?php echo BOX_KPI_REVENUE_TODAY; ?></p>
                </div>
                <div class="icon"><i class="fa fa-dollar"></i></div>
                <a href="<?php echo zen_href_link(FILENAME_STATS_SALES_REPORT_GRAPHS); ?>"
                   class="kpi-card-footer"><?php echo BOX_KPI_MORE_INFO; ?> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-xs-6 col-lg-3">
            <div class="kpi-card bg-yellow">
                <div class="inner">
                    <h3><?php echo $customers_today->fields['count']; ?></h3>
                    <p><?php echo BOX_KPI_CUSTOMERS_TODAY; ?></p>
                </div>
                <div class="icon"><i class="fa fa-user-plus"></i></div>
                <a href="<?php echo zen_href_link(FILENAME_CUSTOMERS); ?>"
                   class="kpi-card-footer"><?php echo BOX_KPI_MORE_INFO; ?> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-xs-6 col-lg-3">
            <div class="kpi-card bg-red">
                <div class="inner">
                    <h3><?php echo $reviews_pending->fields['count']; ?></h3>
                    <p><?php echo BOX_KPI_REVIEWS_PENDING; ?></p>
                </div>
                <div class="icon"><i class="fa fa-comments"></i></div>
                <a href="<?php echo zen_href_link(FILENAME_REVIEWS, 'status=1'); ?>"
                   class="kpi-card-footer"><?php echo BOX_KPI_MORE_INFO; ?> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <?php render_zone('main', $zones['main']); ?>
        </div>

        <div class="col-md-3">
            <?php render_zone('sidebar', $zones['sidebar']); ?>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <?php render_zone('bottom', $zones['bottom']); ?>
        </div>
    </div>

</div>
