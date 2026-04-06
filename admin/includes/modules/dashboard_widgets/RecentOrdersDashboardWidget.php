<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: ZenExpert 2026-04-06 Modified in v3.0.0 $
 */

if (!zen_is_superuser() && !check_page(FILENAME_ORDERS, '')) {
    return;
}

global $currencies, $recentOrdersMaxRows, $show_status_pills, $recentOrdersWidgetOrderStatusIDs;
// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

// Configure settings
// To override the $includeAttributesInPopoverRows or $recentOrdersMaxRows or $recentOrdersWidgetOrderStatusIDs or $show_status_pills
// values, see
// https://docs.zen-cart.com/user/admin/site_specific_overrides/
$includeAttributesInPopoverRows = $includeAttributesInPopoverRows ?? true;
$maxRows = $recentOrdersMaxRows ?? 10; // default to 10 for a cleaner dashboard
// define orders statuses to show in top bar
$show_status_pills = $show_status_pills ?? true;
if (!isset($recentOrdersWidgetOrderStatusIDs) || !is_array($recentOrdersWidgetOrderStatusIDs)) {
    $recentOrdersWidgetOrderStatusIDs = [1, 2]; // pending and processing
}
//========================

// prepare data
$currencies ??= new currencies();

// Cleanup and keep only valid integers
$recentOrdersWidgetOrderStatusIDs = array_filter($recentOrdersWidgetOrderStatusIDs, static fn($id) => is_int($id) || ctype_digit($id));

$sql = "SELECT o.orders_id, o.customers_name, o.customers_id, o.date_purchased,
               o.currency, o.currency_value, o.orders_status,
               ot.text as order_total, ot.value as order_value,
               s.orders_status_name, s.orders_status_color_code
        FROM " . TABLE_ORDERS . " o
        LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON (o.orders_id = ot.orders_id AND ot.class = 'ot_total')
        LEFT JOIN " . TABLE_ORDERS_STATUS . " s ON (o.orders_status = s.orders_status_id AND s.language_id = " . (int)$_SESSION['languages_id'] . ")
        ORDER BY o.orders_id DESC";
$orders = $db->Execute($sql, (int)$maxRows, true, 1800);

// get status metadata (name, color)
    $status_meta = [];
    if (!empty($recentOrdersWidgetOrderStatusIDs)) {
        $ids_str = implode(',', $recentOrdersWidgetOrderStatusIDs);
        $sql_meta = "SELECT orders_status_id, orders_status_name, orders_status_color_code
                 FROM " . TABLE_ORDERS_STATUS . "
                 WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                 AND orders_status_id IN (" . $ids_str . ")";
        $results_meta = $db->Execute($sql_meta);

        foreach ($results_meta as $result_meta) {
            $id = $result_meta['orders_status_id'];
            $status_meta[$id] = [
                'name' => $result_meta['orders_status_name'],
                'color' => $result_meta['orders_status_color_code']
            ];
        }
    }


if ($show_status_pills) {
    $status_counts = [];
    // pre-fill with 0 to ensure badges show even if count is 0
    foreach ($recentOrdersWidgetOrderStatusIDs as $tid) {
        $status_counts[$tid] = 0;
    }
    if (!empty($recentOrdersWidgetOrderStatusIDs)) {
        $ids_str = implode(',', $recentOrdersWidgetOrderStatusIDs);
        $sql_stats = "SELECT orders_status, count(*) as total
                      FROM " . TABLE_ORDERS . "
                      WHERE orders_status IN (" . $ids_str . ")
                      GROUP BY orders_status";
        $results_stats = $db->Execute($sql_stats);
        foreach ($results_stats as $result_stats) {
            $status_counts[$result_stats['orders_status']] = $result_stats['total'];
        }
    }
}
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-2 hidden-xs">
                <i class="fa fa-list-alt"></i> <?= BOX_ENTRY_NEW_ORDERS ?>
            </div>
            <div class="col-xs-12 col-md-8 mb-2 text-center status-pills">
                <?php
                if($show_status_pills) {
                foreach ($recentOrdersWidgetOrderStatusIDs as $sID) {
                    $count = $status_counts[$sID];
                    $name  = isset($status_meta[$sID]) ? $status_meta[$sID]['name'] : zen_get_orders_status_name($sID);
                    $customColor = isset($status_meta[$sID]) ? $status_meta[$sID]['color'] : null;

                    // determine style
                    $inlineStyle = '';
                    $badgeClass  = 'label-default';

                    if (!empty($customColor)) {
                        // custom color badge
                        $badgeClass  = 'label';
                        $inlineStyle = 'background-color: ' . zen_output_string_protected($customColor) . '; border-color: ' . zen_output_string_protected($customColor) . '; color: #fff;';
                    } else {
                        // fallback Bootstrap colors
                        switch ($sID) {
                            case 1: $badgeClass = 'label-warning'; break; // Pending
                            case 2: $badgeClass = 'label-info'; break;    // Processing
                            case 3: $badgeClass = 'label-success'; break; // Delivered
                            default: $badgeClass = 'label-default';
                        }
                    }

                    // fade out if count is 0
                    $opacity = ($count == 0) ? 'opacity: 0.5;' : '';
                    ?>
                    <a href="<?= zen_href_link(FILENAME_ORDERS, 'statusFilterSelect=' . $sID) ?>">
                        <span class="label <?= $badgeClass ?>" style="<?= $inlineStyle . $opacity ?>">
                            <?= zen_output_string_protected($name) ?>: <strong><?= $count ?></strong>
                        </span>
                    </a>
                <?php }
                } ?>
            </div>
            <div class="col-xs-12 col-md-2 text-right">
                <a href="<?= zen_href_link(FILENAME_ORDERS) ?>" class="btn btn-xs btn-default">
                    <?= BOX_ENTRY_VIEW_ALL ?> <i class="fa fa-angle-double-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive recent-orders">
        <table class="table table-hover table-striped mb-0">
            <thead>
            <tr>
                <th><?= BOX_ORDERS_ID ?></th>
                <th><?= BOX_ORDERS_CUSTOMER ?></th>
                <th><?= BOX_ORDERS_STATUS ?></th>
                <th class="text-right"><?= BOX_ORDERS_DATE ?></th>
                <th class="text-right"><?= DASHBOARD_TOTAL ?></th>
                <th class="text-right" style="width: 150px;"><?= BOX_ORDERS_ACTIONS ?></th>
            </tr>
            </thead>
            <tbody>
                <?php
                foreach ($orders as $order) {

                    // prepare data
                    $order['customers_name'] = str_replace('N/A', '', $order['customers_name']);
                    $oID = $order['orders_id'];
                    $name = zen_output_string_protected($order['customers_name']);
                    $date = zen_date_short($order['date_purchased']);
                    $statusName = $order['orders_status_name'];
                    $statusId = (int)$order['orders_status'];
                    $customColor = $order['orders_status_color_code'] ?? '';
                    $amt = $currencies->format($order['order_value'], false);
                    if ($order['currency'] != DEFAULT_CURRENCY) {
                        $amt .= '<br><small class="text-muted">(' . $order['order_total'] . ')</small>';
                    }

                    $sql = "SELECT op.orders_products_id, op.products_quantity AS qty, op.products_name AS name, op.products_model AS model
                            FROM " . TABLE_ORDERS_PRODUCTS . " op
                            WHERE op.orders_id = " . (int)$oID;

                    $orderProducts = $db->Execute($sql, false, true, 1800);
                    $product_details = '';

                    foreach($orderProducts as $product) {
                        $product_details .= $product['qty'] . ' x ' . $product['name'] . (!empty($product['model']) ? ' (' . $product['model'] . ')' :''). "\n";

                        if ($includeAttributesInPopoverRows) {
                            $sql = "SELECT products_options, products_options_values
                                    FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                                    WHERE orders_products_id = " . (int)$product['orders_products_id'] . " ORDER BY orders_products_attributes_id ASC";
                            $productAttributes = $db->Execute($sql, false, true, 1800);
                            foreach ($productAttributes as $attr) {
                                if (!empty($attr['products_options'])) {
                                    $product_details .= '&nbsp;&nbsp;- ' . $attr['products_options'] . ': ' . zen_output_string_protected($attr['products_options_values']) . "\n";
                                }
                            }
                        }
                        $product_details .= '<hr>';
                    }
                    $product_details = rtrim($product_details);
                    $product_details = preg_replace('~<hr>$~', '', $product_details);
                    $product_details = nl2br($product_details);

                    $inlineStyle = '';
                    $lblClass = 'label-default';

                    if (!empty($customColor)) {
                        $lblClass = 'label';
                        $inlineStyle = 'background-color: ' . zen_output_string_protected($customColor) . '; color: #fff;';
                    } else {
                        switch ($statusId) {
                            case 1: $lblClass = 'label-warning'; break;
                            case 2: $lblClass = 'label-info'; break;
                            case 3: $lblClass = 'label-success'; break;
                            default: $lblClass = 'label-default';
                        }
                    }
                ?>
                <tr>
                    <td><strong>#<?= $oID ?></strong></td>

                    <td><a href="<?= zen_href_link(FILENAME_ORDERS, 'oID=' . $oID . '&action=edit') ?>" style="font-weight:600; color:#555;"><?= $name ?></a></td>

                    <td>
                        <span class="label <?= $lblClass ?>" style="<?= $inlineStyle ?>">
                            <?= zen_output_string_protected($statusName) ?>
                        </span>
                    </td>

		            <td class="text-right">
                        <?= $date ?>
                    </td>

                    <td class="text-right"><strong><?= $amt ?></strong></td>

                    <td class="text-right">
                        <button tabindex="0" class="btn btn-xs btn-info orderProductsPopover" role="button"
                                data-toggle="popover"
                                data-trigger="focus"
                                data-placement="left"
                                data-html="true"
                                title="<?= TEXT_PRODUCT_POPUP_TITLE ?? 'Products' ?>"
                                data-content="<?= zen_output_string($product_details, array('"' => '&quot;', "'" => '&#39;', '<br />' => '<br>')) ?>">
                             <i class="fa fa-eye"></i>
                        </button>

                        <a href="<?= zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oID) ?>" target="_blank" class="btn btn-xs btn-default" title="<?= BOX_ORDERS_PRINT_INVOICE ?>">
                            <i class="fa fa-print"></i>
                        </a>

                        <a href="<?= zen_href_link(FILENAME_ORDERS, 'oID=' . $oID . '&action=edit') ?>" class="btn btn-xs btn-primary" title="<?= BOX_ORDERS_VIEW_ORDER ?>">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
