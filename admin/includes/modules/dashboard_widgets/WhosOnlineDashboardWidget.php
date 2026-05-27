<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: ZenExpert 2026-04-06 Modified in v3.0.0 $
 */

if (!zen_is_superuser() && !check_page(FILENAME_WHOS_ONLINE, '')) {
    return;
}

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

$whos_online = new WhosOnline();
$whos_online_stats = $whos_online->getStats();
$user_array = $whos_online_stats['user_array'];
$guest_array = $whos_online_stats['guest_array'];
$spider_array = $whos_online_stats['spider_array'];

// helper to map the 4 distinct states
// 0=ActiveWithCart, 1=InactiveWithCart, 2=ActiveNoCart, 3=InactiveNoCart
$get_detailed_counts = static function ($arr) {
    return [
        'active_cart'   => (int)$arr[0], // Green: Shopping Now
        'idle_cart'     => (int)$arr[1], // Yellow: Cart but Idle
        'active_browse' => (int)$arr[2], // Blue: Just Looking
        'idle_browse'   => (int)$arr[3]  // Gray: Inactive
    ];
};

$users   = $get_detailed_counts($user_array);
$guests  = $get_detailed_counts($guest_array);
$spiders = $get_detailed_counts($spider_array);
?>

<div class="panel widget-wrapper">
    <div class="panel-heading">
        <i class="fa fa-globe"></i> <?= WO_GRAPH_TITLE ?>
        <div class="pull-right">
            <a href="<?= zen_href_link(FILENAME_WHOS_ONLINE) ?>" class="btn btn-xs btn-default"><?= BOX_ENTRY_VIEW_ALL ?></a>
        </div>
    </div>

    <ul class="list-group whos-online-widget">

        <li class="list-group-item">
            <h5 class="list-group-item-heading">
                <i class="fa fa-user text-primary"></i> <?= WO_GRAPH_CUSTOMERS ?>
            </h5>
            <div class="users-row">
                <span class="label label-success" title="<?= WO_ACTIVE_WITH_CART ?>" data-toggle="tooltip">
                    <i class="fa fa-shopping-cart"></i> <?= $users['active_cart'] ?>
                </span>
                <span class="label label-info" title="<?= WO_ACTIVE_BROWSING ?>" data-toggle="tooltip">
                    <i class="fa fa-eye"></i> <?= $users['active_browse'] ?>
                </span>
                <span class="label label-warning" title="<?= WO_IDLE_WITH_CART ?>" data-toggle="tooltip">
                    <i class="fa fa-shopping-cart"></i> <?= $users['idle_cart'] ?>
                </span>
                <span class="label label-default" title="<?= WO_IDLE ?>" data-toggle="tooltip">
                    <i class="fa fa-clock-o"></i> <?= $users['idle_browse'] ?>
                </span>
            </div>
        </li>

        <li class="list-group-item">
            <h5 class="list-group-item-heading">
                <i class="fa fa-users text-muted"></i> <?= WO_GRAPH_GUESTS ?>
            </h5>
            <div class="users-row">
                <span class="label label-success" title="<?= WO_ACTIVE_WITH_CART ?>" data-toggle="tooltip">
                    <i class="fa fa-shopping-cart"></i> <?= $guests['active_cart'] ?>
                </span>
                <span class="label label-info" title="<?= WO_ACTIVE_BROWSING ?>" data-toggle="tooltip">
                    <i class="fa fa-eye"></i> <?= $guests['active_browse'] ?>
                </span>
                <span class="label label-warning" title="<?= WO_IDLE_WITH_CART ?>" data-toggle="tooltip">
                    <i class="fa fa-shopping-cart"></i> <?= $guests['idle_cart'] ?>
                </span>
                <span class="label label-default" title="<?= WO_IDLE ?>" data-toggle="tooltip">
                    <i class="fa fa-clock-o"></i> <?= $guests['idle_browse'] ?>
                </span>
            </div>
        </li>

        <li class="list-group-item">
            <h5 class="list-group-item-heading">
                <i class="fa fa-bug text-danger"></i> <?= WO_GRAPH_SPIDERS ?>
            </h5>
            <div class="text-right">
                <span class="label label-default" title="<?= WO_ACTIVE_SPIDERS ?>" data-toggle="tooltip">
                    <?= $spiders['active_browse'] + $spiders['active_cart'] ?> <?= BOX_LABEL_ACTIVE ?>
                </span>
            </div>
        </li>
    </ul>

    <div class="panel-footer text-center">
        <small class="text-muted"><?= WO_GRAPH_TOTAL ?> <strong><?= $whos_online->getTotalSessions() ?></strong></small>
    </div>
</div>
