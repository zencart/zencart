<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

if (!zen_is_superuser() && !check_page(FILENAME_ORDERS, '')) return;

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
$result = $db->Execute($sql, false, true, 1800);

while (!$result->EOF) {
    $status_counts[$result->fields['orders_status']] = $result->fields['total'];
    $result->MoveNext();
}
?>

<div class="panel widget-wrapper">
    <div class="panel-heading">
        <i class="fa fa-clipboard"></i> <?php echo BOX_ORDER_STATUS_HEADING; ?>
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
                <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'statusFilterSelect=' . $sID); ?>" style="<?php echo $textStyle; ?>">
                    <i class="fa <?php echo $icon; ?> text-muted" style="margin-right: 5px; width: 15px;"></i>
                    <?php echo $status['text']; ?>
                </a>
                <div class="pull-right">
                <span class="label <?php echo $badgeClass; ?>" style="min-width: 25px; display: inline-block;">
                    <?php echo $count; ?>
                </span>
                </div>
            </li>
        <?php } ?>
    </ul>

    <div class="panel-footer text-center" style="background: #fff; padding: 8px;">
        <a href="<?php echo zen_href_link(FILENAME_ORDERS_STATUS); ?>" class="small text-muted">
            <i class="fa fa-cog"></i> <?php echo BOX_ORDER_STATUS_MANAGE; ?>
        </a>
    </div>
</div>
