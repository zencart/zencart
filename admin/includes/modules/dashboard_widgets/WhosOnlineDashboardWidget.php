<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

if (!zen_is_superuser() && !check_page(FILENAME_WHOS_ONLINE, '')) return;

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

$whos_online = new WhosOnline();
$whos_online_stats = $whos_online->getStats();
$user_array = $whos_online_stats['user_array'];
$guest_array = $whos_online_stats['guest_array'];
$spider_array = $whos_online_stats['spider_array'];

// helper to map the 4 distinct states
// 0=ActiveWithCart, 1=InactiveWithCart, 2=ActiveNoCart, 3=InactiveNoCart
function get_detailed_counts($arr) {
    return [
        'active_cart'   => (int)$arr[0], // Green: Shopping Now
        'idle_cart'     => (int)$arr[1], // Yellow: Cart but Idle
        'active_browse' => (int)$arr[2], // Blue: Just Looking
        'idle_browse'   => (int)$arr[3]  // Gray: Inactive
    ];
}

$users   = get_detailed_counts($user_array);
$guests  = get_detailed_counts($guest_array);
$spiders = get_detailed_counts($spider_array);
?>

<div class="panel widget-wrapper">
    <div class="panel-heading">
        <i class="fa fa-globe"></i> <?php echo WO_GRAPH_TITLE; ?>
        <div class="pull-right">
            <a href="<?php echo zen_href_link(FILENAME_WHOS_ONLINE); ?>" class="btn btn-xs btn-default"><?php echo BOX_ENTRY_VIEW_ALL; ?></a>
        </div>
    </div>

    <ul class="list-group">

        <li class="list-group-item">
            <h5 class="list-group-item-heading" style="margin-top:0; margin-bottom:8px; font-weight:600; color:#555;">
                <i class="fa fa-user text-primary"></i> <?php echo WO_GRAPH_CUSTOMERS; ?>
            </h5>
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 4px;">
                <span class="label label-success" title="<?php echo WO_ACTIVE_WITH_CART; ?>" data-toggle="tooltip">
                    <i class="fa fa-shopping-cart"></i> <?php echo $users['active_cart']; ?>
                </span>
                <span class="label label-info" title="<?php echo WO_ACTIVE_BROWSING; ?>" data-toggle="tooltip">
                    <i class="fa fa-eye"></i> <?php echo $users['active_browse']; ?>
                </span>
                <span class="label label-warning" title="<?php echo WO_IDLE_WITH_CART; ?>" data-toggle="tooltip">
                    <i class="fa fa-shopping-cart"></i> <?php echo $users['idle_cart']; ?>
                </span>
                <span class="label label-default" title="<?php echo WO_IDLE; ?>" data-toggle="tooltip">
                    <i class="fa fa-clock-o"></i> <?php echo $users['idle_browse']; ?>
                </span>
            </div>
        </li>

        <li class="list-group-item">
            <h5 class="list-group-item-heading" style="margin-top:0; margin-bottom:8px; font-weight:600; color:#555;">
                <i class="fa fa-users text-muted"></i> <?php echo WO_GRAPH_GUESTS; ?>
            </h5>
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 4px;">
                <span class="label label-success" title="<?php echo WO_ACTIVE_WITH_CART; ?>" data-toggle="tooltip">
                    <i class="fa fa-shopping-cart"></i> <?php echo $guests['active_cart']; ?>
                </span>
                <span class="label label-info" title="<?php echo WO_ACTIVE_BROWSING; ?>" data-toggle="tooltip">
                    <i class="fa fa-eye"></i> <?php echo $guests['active_browse']; ?>
                </span>
                <span class="label label-warning" title="<?php echo WO_IDLE_WITH_CART; ?>" data-toggle="tooltip">
                    <i class="fa fa-shopping-cart"></i> <?php echo $guests['idle_cart']; ?>
                </span>
                <span class="label label-default" title="<?php echo WO_IDLE; ?>" data-toggle="tooltip">
                    <i class="fa fa-clock-o"></i> <?php echo $guests['idle_browse']; ?>
                </span>
            </div>
        </li>

        <li class="list-group-item">
            <h5 class="list-group-item-heading" style="margin-top:0; margin-bottom:5px; font-weight:600; color:#555;">
                <i class="fa fa-bug text-danger"></i> <?php echo WO_GRAPH_SPIDERS; ?>
            </h5>
            <div class="text-right">
                <span class="label label-default" title="<?php echo WO_ACTIVE_SPIDERS; ?>" data-toggle="tooltip">
                    <?php echo $spiders['active_browse'] + $spiders['active_cart']; ?> <?php echo BOX_LABEL_ACTIVE; ?>
                </span>
            </div>
        </li>
    </ul>

    <div class="panel-footer text-center" style="background: #fff; padding: 8px;">
        <small class="text-muted"><?php echo WO_GRAPH_TOTAL; ?> <strong><?php echo $whos_online->getTotalSessions(); ?></strong></small>
    </div>
</div>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
