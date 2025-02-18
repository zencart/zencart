<?php
// -----
// Part of the "Product Options Stock" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM 5.0.0
//
require 'includes/application_top.php';

require DIR_WS_CLASSES . 'currencies.php';

// -----
// For versions of POSM prior to v4.1.0, this class was embedded in this script!
//
require DIR_WS_CLASSES . 'PosmSalesReport.php';

$timeframe = $_GET['timeframe'] ?? '';
$timeframe_type = $_GET['timeframe_type'] ?? 'preset';
if ($timeframe_type === 'preset') {
    $extra_timeframe = '';
    $extra_custom = ' class="hiddenField"';
} else {
    $extra_timeframe = ' class="hiddenField"';
    $extra_custom = '';
}

// -----
// The starting/ending dates for the report start out as 'today' at 00:00:00 and
// 23:59:59, respectively.
//
$dateTimeStart = new DateTime();
$dateTimeStart->setTime(0, 0, 0);

$dateTimeEnd = new DateTime();
$dateTimeEnd->setTime(23, 59, 59);
switch ($timeframe) {
    case 'yesterday':
        $dateTimeStart->modify('-1 day');
        $dateTimeEnd->modify('-1 day');
        break;

    case 'last_month':
        $dateTimeStart->modify('first day of last month');
        $dateTimeEnd->modify('last day of last month');
        break;

    case 'this_month':
        $dateTimeStart->modify('first day of this month');
        break;

    case 'last_year':
        $dateTimeStart->setDate(date('Y') - 1, 1, 1);
        $dateTimeEnd->setDate(date('Y') - 1, 12, 31);
        break;

    case 'YTD':
        $dateTimeStart->setDate((int)date('Y'), 1, 1);
        break;

    case 'today':
    default:
        $timeframe = 'today';
        break;
}
$start_ts = $dateTimeStart->getTimestamp();
$end_ts = $dateTimeEnd->getTimestamp();

if (!isset($_GET['startdate_year'])) {
    $custom_start_ts = time();
} else {
    $custom_start_ts = mktime(0, 0, 0, (int)$_GET['startdate_month'], (int)$_GET['startdate_day'], (int)$_GET['startdate_year']);
}

if (!isset ($_GET['enddate_year'])) {
    $custom_end_ts = time();
} else {
    $custom_end_ts = mktime(23, 59, 59, (int)$_GET['enddate_month'], (int)$_GET['enddate_day'], (int)$_GET['enddate_year']);
}

$timeframe_select = [
    ['id' => 'today', 'text' => sprintf(SEARCH_DATE_TODAY, date('M. j'))],
    ['id' => 'yesterday', 'text' => sprintf(SEARCH_DATE_YESTERDAY, date('M. j', strtotime('yesterday')))],
    ['id' => 'last_month', 'text' => sprintf(SEARCH_DATE_LAST_MONTH, date('F \'y', strtotime('last month')))],
    ['id' => 'this_month', 'text' => sprintf(SEARCH_DATE_THIS_MONTH, date('F \'y'))],
    ['id' => 'last_year', 'text' => sprintf(SEARCH_DATE_LAST_YEAR, date('Y') - 1)],
    ['id' => 'YTD', 'text' => sprintf(SEARCH_DATE_YTD, date('M. j', strtotime('first day of January')) . ' - ' . date('M. j \'y'))],
];

$pID = (int)($_GET['pID'] ?? 0);
$action = $_GET['action'] ?? '';

$products_list = $db->Execute(
    "SELECT DISTINCT op.products_id, op.products_name
       FROM " . TABLE_ORDERS_PRODUCTS . " op
            INNER JOIN " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " opa
                ON opa.orders_products_id = op.orders_products_id
   ORDER BY op.products_name"
);
$products_select = [];
$products_found = [];
foreach ($products_list as $product) {
    $current_name = trim(pos_extract_stock_type($product['products_name'], true));
    $products_found[$product['products_id']][] = $current_name;
    if ($pID === 0) {
        $pID = $product['products_id'];
    }
    if ($pID == $product['products_id']) {
        $products_name = $products_name ?? $current_name;
    }
    if (count($products_found[$product['products_id']]) === 1) {
        $products_select[] = [
            'id' => $product['products_id'],
            'text' => trim(pos_extract_stock_type($product['products_name'], true))
        ];
    }
}
unset($products_list);
$products_name = $products_name ?? '** Unknown **';

if ($timeframe_type === 'preset') {
    $pos_report = new PosmSalesReport($pID, $start_ts, $end_ts);
} else {
    $pos_report = new PosmSalesReport($pID, $custom_start_ts, $custom_end_ts);
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <style>
    table > tbody > tr.name-list > td {
        background-color: <?= POSM_DIVIDER_COLOR ?>;
        font-weight: bold;
    }
    table.breakdown > tbody > tr > th {
        border-top: none;
    }
    table.breakdown {
        margin-bottom: 0;
    }
    .sr-heading {
        font-weight: bold;
    }
    </style>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<div class="container-fluid">
    <h1><?= HEADING_TITLE ?></h1>
    <p><?= TEXT_INSTRUCTIONS ?></p>

    <?= zen_draw_form('report', FILENAME_PRODUCTS_OPTIONS_STOCK_REPORT, 'action=generate', 'get', 'class="form-horizontal"') ?>
        <?= zen_draw_hidden_field('timeframe_type', $timeframe_type, 'id="timeframe_type"') ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <?= TEXT_CHOOSE_TIMEFRAME ?>
                            <a href="#" id="toggle"><span id="timeframe_text"><?= ($timeframe_type === 'preset') ? TEXT_CUSTOM : TEXT_PRESET ?></span></a>
                        </th>
                        <th><?= TEXT_CHOOSE_PRODUCT ?></th>
                        <th><?= TEXT_CLICK_HERE ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div id="timeframe"<?= $extra_timeframe ?>><?= zen_draw_pull_down_menu('timeframe', $timeframe_select, $timeframe) ?></div>
                            <div id="custom"<?= $extra_custom ?>><table class="table-condensed">
                                <tr>
                                    <td><?= TEXT_STARTDATE ?></td>
                                    <td><?= zen_draw_date_selector('startdate', $custom_start_ts) ?></td>
                                </tr>
                                <tr>
                                    <td><?= TEXT_ENDDATE ?></td>
                                    <td><?= zen_draw_date_selector('enddate', $custom_end_ts) ?></td>
                                </tr>
                            </table></div>
                        </td>
                        <td><?= zen_draw_pull_down_menu('pID', $products_select, $pID) ?></td>
                        <td>
                            <button class="btn btn-primary" type="submit"><?= IMAGE_DISPLAY ?></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?= '</form>' ?>

    <table class="table table-condensed">

<?php
if ($pos_report->get_order_count() === 0) {
?>
        <tr>
            <td class="text-center sr-heading"><?= sprintf(NO_PRODUCTS_ORDERED_TIMEFRAME, $products_name, $timeframe) ?></td>
        </tr>
<?php
} else {
    $product_option_count = $pos_report->get_option_count();
    $option_columns = $product_option_count + 3;
    $total_quantity = $pos_report->get_product_total_quantity();

    // -----
    // This is the default sprintf template when a product's name, option or option-name has more
    // than one value in the range of orders selected.
    //
    $popover_template =
        '<a tabindex="0" class="btn btn-xs btn-link" role="button" data-toggle="popover" data-trigger="focus" data-placement="auto" title="' . TEXT_POPOVER_ADDITIONAL_NAMES . '" data-content="%s">' .
            TEXT_PRODUCT_POPUP_BUTTON .
        '</a>';

    // -----
    // If more than name is found for the ordered product, display a popover
    // identifying those additional names.
    //
    $products_names_found = array_unique($products_found[$pID]);
    if (count($products_names_found) !== 1) {
        array_shift($products_names_found); //- Drop the first name, it's contained in $products_name
        $additional_names = implode("\n", $products_names_found);
        $additional_names = zen_output_string(
            nl2br($additional_names, false),
            ['"' => '&quot;', "'" => '&#39;', '<br />' => '<br>']
        );
        $products_name .= sprintf($popover_template, $additional_names);
    }
?>
        <tr>
            <td class="text-center sr-heading" colspan="<?= $option_columns ?>">
                <?= sprintf(PRODUCTS_ORDERED_TIMEFRAME, $total_quantity, $products_name, $timeframe, $pos_report->get_order_count(), $pos_report->get_product_total_price()) ?>
            </td>
        </tr>

        <tr class="name-list">
            <td>&nbsp;</td>
<?php
    $option_names_found = [];
    foreach ($pos_report->getOptionNames() as $options_id => $option_names) {
        $option_name = array_shift($option_names); //- Grab the first name, removing it from the array.
        if (count($option_names) !== 0) {
            $additional_names = implode("\n", $option_names);
            $additional_names = zen_output_string(
                nl2br($additional_names, false),
                ['"' => '&quot;', "'" => '&#39;', '<br />' => '<br>']
            );
            $option_name .= sprintf($popover_template, $additional_names);
        }
        $option_names_found[$options_id] = $option_name;
?>
            <td><?= $option_name ?></td>
<?php
    }
?>
            <td colspan="2">&nbsp;</td>
        </tr>

        <tr>
            <td>&nbsp;</td>
<?php
    $option_values_names_found = [];
    foreach ($pos_report->getOptions() as $options_id => $info) {
?>
            <td class="align-top"><table class="table table-condensed breakdown">
                <tr>
                    <th class="text-center"><?= TEXT_QTY ?></th>
                    <th><?= TEXT_OPTION_NAME ?></th>
                    <th class="text-right"><?= TEXT_PERCENT_QTY ?></th>
                    <th class="text-right"><?= TEXT_PERCENT_PRICE ?></th>
                </tr>
<?php
        foreach ($info['values'] as $options_values_id => $value_info) {
            $values_names = $value_info['names'];
            $option_value_name = array_shift($values_names); //- Grab the first name, removing it from the array.
            if (count($values_names) !== 0) {
                $additional_names = implode("\n", $values_names);
                $additional_names = zen_output_string(
                    nl2br($additional_names, false),
                    ['"' => '&quot;', "'" => '&#39;', '<br />' => '<br>']
                );
                $option_value_name .= sprintf($popover_template, $additional_names);
            }
            $option_values_names_found[$options_values_id] = $option_value_name;
?>
                <tr>
                    <td class="text-center"><?= $value_info['quantity'] ?></td>
                    <td class="the-name"><?= $option_value_name ?></td>
                    <td class="text-right">
                        <?= sprintf(TEXT_QUANTITY_PERCENTAGE, ($value_info['quantity'] / $total_quantity) * 100) ?>
                    </td>
                    <td class="text-right">
                        <?= sprintf(TEXT_PRICE_PERCENTAGE, $value_info['total_price'] / $pos_report->get_product_total_price(false) * 100) ?>
                    </td>
                </tr>
<?php
        }
?>
            </table></td>
<?php
    }
?>
            <td colspan="2">&nbsp;</td>
        </tr>

        <tr class="name-list">
            <td class="text-center"><?= TEXT_QTY ?></td>
<?php
    foreach ($option_names_found as $option_id => $option_name) {
?>
            <td><?= $option_name ?></td>
<?php
    }
?>
            <td class="text-right"><?= TEXT_PERCENT_QTY ?></td>
            <td class="text-right"><?= TEXT_PERCENT_PRICE ?></td>
        </tr>
<?php
    foreach ($pos_report->getOrders() as $order_key => $order_info) {
?>
        <tr>
            <td class="text-center"><?= $order_info['quantity'] ?></td>
<?php
        foreach ($order_info['options'] as $options_id => $options_values_id) {
            $current_name = null;
            foreach ($option_values_names_found as $opt_val_id => $options_values_name) {
                if ($opt_val_id == $options_values_id) {
                    $current_name = $options_values_name;
                    break;
                }
            }
?>
            <td class="the-name">
                <?= $current_name ?? '&mdash;' ?>
            </td>
<?php
        }
?>
            <td class="text-right">
                <?= sprintf(TEXT_QUANTITY_PERCENTAGE, $order_info['quantity'] / $total_quantity * 100) ?>
            </td>
            <td class="text-right">
                <?= sprintf(TEXT_PRICE_PERCENTAGE, $order_info['total_price'] / $pos_report->get_product_total_price(false) * 100) ?>
            </td>
        </tr>
<?php
    }
}
?>
    </table>
</div>

<?php require DIR_WS_INCLUDES . 'footer.php'; ?>

<script>
$(function() {
    $('#toggle').click(function() {
        if ($('#timeframe_type').val() === 'preset') {
            $('#timeframe_text').text('<?= TEXT_PRESET ?>');
            $('#timeframe').hide();
            $('#custom').show();
            $('#timeframe_type').val('custom');
        } else {
            $('#timeframe_text').text('<?= TEXT_CUSTOM ?>');
            $('#timeframe').show();
            $('#custom').hide();
            $('#timeframe_type').val('preset');
        }
    });
    $('[data-toggle="popover"]').popover({html:true,sanitize: true});
});
</script>
</body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
