<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: ZenExpert 2026-04-06 Modified in v3.0.0 $
 */

if (!zen_is_superuser() && !check_page(FILENAME_ORDERS, '')) {
    return;
}

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

// prepare data
// get all defined Status Names (ID => Name)
$ordersStatus = zen_getOrdersStatuses();
$statuses = $ordersStatus['orders_statuses'];

// get order counts in a single query
$status_counts = [];
$sql = "SELECT orders_status, count(*) as total
        FROM " . TABLE_ORDERS . "
        GROUP BY orders_status";
// cached for 30 minutes to reduce load
$results = $db->Execute($sql, false, true, 1800);

foreach ($results as $result) {
    $status_counts[$result['orders_status']] = $result['total'];
}
?>

<div class="panel widget-wrapper">
    <div class="panel-heading">
        <i class="fa fa-clipboard"></i> <?= BOX_ORDER_STATUS_HEADING ?>
    </div>

    <ul class="list-group">
        <?php foreach ($statuses as $status) {
            $sID = (int)$status['id'];
            $count = isset($status_counts[$sID]) ? $status_counts[$sID] : 0;

            // dim the empty ones, highlight the active ones
            $badgeClass = ($count > 0) ? 'label-primary' : 'label-default';
            $textStyle  = ($count > 0) ? 'color: #444; font-weight: 600;' : 'color: #999;';
            $icon       = ($count > 0) ? 'fa-folder-open' : 'fa-folder-o';
            ?>
            <li class="list-group-item">
                <a href="<?= zen_href_link(FILENAME_ORDERS, 'statusFilterSelect=' . $sID) ?>" style="<?= $textStyle ?>">
                    <i class="fa <?= $icon ?> text-muted"></i>
                    <?= zen_output_string_protected($status['text']) ?>
                </a>
                <div class="pull-right">
                <span class="label order-status-label <?= $badgeClass ?>">
                    <?= $count ?>
                </span>
                </div>
            </li>
        <?php } ?>
    </ul>

    <div class="panel-footer text-center">
        <a href="<?= zen_href_link(FILENAME_ORDERS_STATUS) ?>" class="small text-muted">
            <i class="fa fa-cog"></i> <?= BOX_ORDER_STATUS_MANAGE ?>
        </a>
    </div>
</div>
