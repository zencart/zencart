<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

if (!zen_is_superuser() && !check_page(FILENAME_ORDERS, '')) return;

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

// Configure settings
// To override the $includeAttributesInPopoverRows or $recentOrdersMaxRows
// values, see
// https://docs.zen-cart.com/user/admin/site_specific_overrides/
$includeAttributesInPopoverRows = $includeAttributesInPopoverRows ?? true;
$maxRows = $recentOrdersMaxRows ?? 10; // default to 10 for a cleaner dashboard

$currencies ??= new currencies();

// prepare data
$sql = "SELECT o.orders_id, o.customers_name, o.customers_id, o.date_purchased,
               o.currency, o.currency_value, o.orders_status,
               ot.text as order_total, ot.value as order_value,
               s.orders_status_name
        FROM " . TABLE_ORDERS . " o
        LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON (o.orders_id = ot.orders_id AND ot.class = 'ot_total')
        LEFT JOIN " . TABLE_ORDERS_STATUS . " s ON (o.orders_status = s.orders_status_id AND s.language_id = " . (int)$_SESSION['languages_id'] . ")
        ORDER BY o.orders_id DESC";

$orders = $db->Execute($sql, (int)$maxRows, true, 1800);
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-8">
                <i class="fa fa-list-alt"></i> <?php echo BOX_ENTRY_NEW_ORDERS; ?>
            </div>
            <div class="col-xs-4 text-right">
                <a href="<?php echo zen_href_link(FILENAME_ORDERS); ?>" class="btn btn-xs btn-default">
                    <?php echo BOX_ENTRY_VIEW_ALL; ?> <i class="fa fa-angle-double-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped" style="margin-bottom: 0;">
            <thead>
            <tr>
                <th><?php echo BOX_ORDERS_ID; ?></th>
                <th><?php echo BOX_ORDERS_CUSTOMER; ?></th>
                <th><?php echo BOX_ORDERS_STATUS; ?></th>
                <th class="text-right"><?php echo BOX_ORDERS_DATE; ?></th>
                <th class="text-right"><?php echo DASHBOARD_TOTAL; ?></th>
                <th class="text-right" style="width: 150px;"><?php echo BOX_ORDERS_ACTIONS; ?></th>
            </tr>
            </thead>
            <tbody>
            <?php while (!$orders->EOF) {
                $oID = $orders->fields['orders_id'];
                $name = zen_output_string_protected($orders->fields['customers_name']);
                $date = zen_date_short($orders->fields['date_purchased']);
                $statusName = $orders->fields['orders_status_name'];
                $statusId = (int)$orders->fields['orders_status'];

                $amt = $currencies->format($orders->fields['order_value'], false);
                if ($orders->fields['currency'] != DEFAULT_CURRENCY) {
                    $amt .= '<br><small class="text-muted">(' . $orders->fields['order_total'] . ')</small>';
                }

                // status color logic (Bootstrap labels)
                // 1=Pending (Warning), 2=Processing (Info), 3=Delivered (Success), Others (Default)
                switch ($statusId) {
                    case 1: $labelClass = 'label-warning'; break; // pending
                    case 2: $labelClass = 'label-info'; break;    // processing
                    case 3: $labelClass = 'label-success'; break; // delivered
                    case 4: $labelClass = 'label-primary'; break; // room for other statuses
                    default: $labelClass = 'label-default';
                }

                // product details for Popover (quick preview)
                $product_details = '';
                $sql_prod = "SELECT products_quantity, products_name, products_model
                                 FROM " . TABLE_ORDERS_PRODUCTS . "
                                 WHERE orders_id = " . (int)$oID;
                $products = $db->Execute($sql_prod);
                foreach ($products as $prod) {
                    $product_details .= $prod['products_quantity'] . ' x ' . $prod['products_name'] . '<br>';
                }
                if (strlen($product_details) > 0) $product_details = '<div style=\'font-size:12px\'>' . $product_details . '</div>';
                ?>
                <tr>
                    <td style="vertical-align: middle;"><strong>#<?php echo $oID; ?></strong></td>

                    <td style="vertical-align: middle;">
                        <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'oID=' . $oID . '&action=edit'); ?>" style="font-weight:600; color:#555;">
                            <?php echo $name; ?>
                        </a>
                    </td>

                    <td style="vertical-align: middle;">
                        <span class="label <?php echo $labelClass; ?>" style="font-size: 90%; font-weight: normal; padding: 4px 8px;">
                            <?php echo $statusName; ?>
                        </span>
                    </td>

                    <td class="text-right" style="vertical-align: middle; color: #777;">
                        <?php echo $date; ?>
                    </td>

                    <td class="text-right" style="vertical-align: middle; font-weight: bold;">
                        <?php echo $amt; ?>
                    </td>

                    <td class="text-right" style="vertical-align: middle;">
                        <button type="button" class="btn btn-xs btn-info" data-toggle="popover" data-trigger="hover" data-placement="left" data-html="true" title="<?php echo sprintf(BOX_ORDERS_ORDER, $oID); ?>" data-content="<?php echo zen_output_string_protected($product_details); ?>">
                            <i class="fa fa-eye"></i>
                        </button>

                        <a href="<?php echo zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oID); ?>" target="_blank" class="btn btn-xs btn-default" title="<?php echo BOX_ORDERS_PRINT_INVOICE; ?>">
                            <i class="fa fa-print"></i>
                        </a>

                        <a href="<?php echo zen_href_link(FILENAME_ORDERS, 'oID=' . $oID . '&action=edit'); ?>" class="btn btn-xs btn-primary" title="<?php echo BOX_ORDERS_PROCESS; ?>">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </td>
                </tr>
                <?php
                $orders->MoveNext();
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(function () {
        $('[data-toggle="popover"]').popover();
    })
</script>
