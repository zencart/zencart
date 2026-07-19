<?php
/*
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ZenExpert 2026-07-13 $
 */

require('includes/application_top.php');

$products_filter = (isset($_GET['products_filter']) ? $_GET['products_filter'] : (isset($products_filter) ? $products_filter : ''));
$products_filter = str_replace(' ', ',', $products_filter);
$products_filter = str_replace(',,', ',', $products_filter);
$products_filter_name_model = (isset($_GET['products_filter_name_model']) ? $_GET['products_filter_name_model'] : (isset($products_filter_name_model) ? $products_filter_name_model : ''));
// date range and all-time variables
$start_date = (isset($_GET['start_date']) ? zen_db_prepare_input($_GET['start_date']) : '');
$end_date = (isset($_GET['end_date']) ? zen_db_prepare_input($_GET['end_date']) : '');
$all_time = (isset($_GET['all_time']) ? (int)$_GET['all_time'] : 0);
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <link rel="stylesheet" media="print" href="includes/css/stylesheet_print.css">
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<div class="container-fluid">
    <!-- body //-->
    <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
    <!-- body_text //-->
    <div class="row">
        <!-- date range filter -->
        <div class="col-sm-6">
            <?php echo zen_draw_form('date_search', FILENAME_STATS_PRODUCTS_PURCHASED, '', 'get', 'class="form-horizontal"', true); ?>
            <?php echo zen_hide_session_id(); ?>
            <div class="form-group">
                <label class="control-label col-sm-3"><?= TEXT_DATE_RANGE_START_DATE ?></label>
                <div class="col-sm-4">
                    <input type="date" name="start_date" value="<?php echo zen_output_string_protected($start_date); ?>"
                           class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3"><?= TEXT_DATE_RANGE_END_DATE ?></label>
                <div class="col-sm-4">
                    <input type="date" name="end_date" value="<?php echo zen_output_string_protected($end_date); ?>"
                           class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <button type="submit" class="btn btn-primary"><?= TEXT_SEARCH_DATE_RANGE ?></button>
                    <a href="<?php echo zen_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, 'all_time=1', 'NONSSL'); ?>"
                       class="btn btn-info"><?= TEXT_SEARCH_ALL_TIME ?></a>
                </div>
            </div>
            <?php echo '</form>'; ?>
        </div>
        <!-- ID/Name filters -->
        <div class="col-sm-6">
            <?php echo zen_draw_form('search', FILENAME_STATS_PRODUCTS_PURCHASED, '', 'get', 'class="form-horizontal"', true); ?>
            <?php echo zen_hide_session_id(); ?>
            <?php echo zen_draw_label(HEADING_TITLE_SEARCH_DETAIL_REPORTS, 'products_filter', 'class="control-label col-sm-9"'); ?>
            <div
                class="col-sm-3"><?php echo zen_draw_input_field('products_filter', '', 'class="form-control"'); ?></div>
            <?php
            if (isset($products_filter) && zen_not_null($products_filter)) {
                $products_filter = preg_replace('/[^0-9,]/', '', $products_filter);
                $products_filter = zen_db_input(zen_db_prepare_input($products_filter));
                echo '<br>' . TEXT_INFO_SEARCH_DETAIL_FILTER . $products_filter;
                ?>
                <br><a href="<?php echo zen_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, '', 'NONSSL'); ?>"
                       class="btn btn-default btn-xs"><?php echo IMAGE_RESET; ?></a>
            <?php } ?>
            <?php echo '</form>'; ?>
            <br>
            <?php echo zen_draw_form('search', FILENAME_STATS_PRODUCTS_PURCHASED, '', 'get', 'class="form-horizontal"', true); ?>
            <?php echo zen_hide_session_id(); ?>
            <?php echo zen_draw_label(HEADING_TITLE_SEARCH_DETAIL_REPORTS_NAME_MODEL, 'products_filter', 'class="control-label col-sm-9"'); ?>
            <div
                class="col-sm-3"><?php echo zen_draw_input_field('products_filter_name_model', '', 'class="form-control"'); ?></div>
            <?php
            if (isset($products_filter_name_model) && zen_not_null($products_filter_name_model)) {
                $products_filter_name_model = zen_db_input(zen_db_prepare_input($products_filter_name_model));
                echo '<br>' . TEXT_INFO_SEARCH_DETAIL_FILTER . zen_db_prepare_input($products_filter_name_model);
                ?>
                <br><a href="<?php echo zen_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, '', 'NONSSL'); ?>"
                       class="btn btn-default btn-xs"><?php echo IMAGE_RESET; ?></a>
            <?php } ?>
            <?php echo '</form>'; ?>
        </div>
    </div>
    <hr>
    <?php
    // search for a specific product ID or Model
    if ($products_filter > 0 || $products_filter_name_model != '') {
        if ($products_filter > 0) {
            // by products_id
            $chk_orders_products_query = "SELECT o.customers_id, op.orders_id, op.products_id, op.products_quantity, op.products_name, op.products_model,
                                               o.customers_name, o.customers_company, o.customers_email_address, o.date_purchased
                                        FROM " . TABLE_ORDERS . " o,
                                             " . TABLE_ORDERS_PRODUCTS . " op
                                        WHERE op.products_id IN (" . $products_filter . ")
                                        AND op.orders_id = o.orders_id
                                        ORDER BY op.products_id, o.date_purchased DESC";
        } else {
            // by products name or model
            $chk_orders_products_query = "SELECT o.customers_id, op.orders_id, op.products_id, op.products_quantity, op.products_name, op.products_model,
                                               o.customers_name, o.customers_company, o.customers_email_address, o.date_purchased
                                        FROM " . TABLE_ORDERS . " o,
                                             " . TABLE_ORDERS_PRODUCTS . " op
                                        WHERE ((op.products_model LIKE '%" . $products_filter_name_model . "%')
                                          OR (op.products_name LIKE '%" . $products_filter_name_model . "%'))
                                        AND op.orders_id = o.orders_id
                                        ORDER BY op.products_id, o.date_purchased DESC";
        }
        $chk_orders_products_query_numrows = '';
        $chk_orders_products_split = new splitPageResults($_GET['page'], (int)zen_config('MAX_DISPLAY_SEARCH_RESULTS_REPORTS'), $chk_orders_products_query, $chk_orders_products_query_numrows);

        $chk_orders_products = $db->Execute($chk_orders_products_query);
        ?>
        <table class="table table-hover">
            <thead>
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent right"><?php echo TABLE_HEADING_CUSTOMERS_ID; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDERS_ID; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDERS_DATE_PURCHASED; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS_INFO; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_PRODUCTS_QUANTITY; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_PRODUCTS_NAME; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if ($chk_orders_products->EOF) { ?>
                <tr class="dataTableRowSelectedBot">
                    <td colspan="7" class="dataTableContent text-center"><?php echo NONE; ?></td>
                </tr>
            <?php } ?>
            <?php
            foreach ($chk_orders_products as $orders_products) {
                $cPath = zen_get_product_path($orders_products['products_id']);
                $product_type = zen_get_products_type($orders_products['products_id']);
                ?>
                <tr class="dataTableRow">
                    <td class="dataTableContent"><a
                            href="<?php echo zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'page', 'products_filter')) . 'cID=' . $orders_products['customers_id'] . '&action=edit', 'NONSSL'); ?>"><?php echo $orders_products['customers_id']; ?></a>
                    </td>
                    <td class="dataTableContent"><a
                            href="<?php echo zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action', 'page', 'products_filter')) . 'oID=' . $orders_products['orders_id'] . '&action=edit', 'NONSSL'); ?>"><?php echo $orders_products['orders_id']; ?></a>
                    </td>
                    <td class="dataTableContent"><?php echo zen_date_short($orders_products['date_purchased']); ?></td>
                    <td class="dataTableContent"><?php echo zen_output_string_protected($orders_products['customers_name']) . ($orders_products['customers_company'] != '' ? '<br>' . zen_output_string_protected($orders_products['customers_company']) : '') . '<br>' . zen_output_string_protected($orders_products['customers_email_address']); ?></td>
                    <td class="dataTableContent text-center"><?php echo $orders_products['products_quantity']; ?></td>
                    <td class="dataTableContent text-center"><a
                            href="<?php echo zen_href_link(FILENAME_PRODUCT, '&product_type=' . $product_type . '&cPath=' . $cPath . '&pID=' . $orders_products['products_id'] . '&action=new_product'); ?>"><?php echo $orders_products['products_name']; ?></a>
                    </td>
                    <td class="dataTableContent text-center"><?php echo $orders_products['products_model']; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <table class="table">
            <tr>
                <td><?php echo $chk_orders_products_split->display_count($chk_orders_products_query_numrows, zen_config('MAX_DISPLAY_SEARCH_RESULTS_REPORTS'), $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                <td class="text-right"><?php echo $chk_orders_products_split->display_links($chk_orders_products_query_numrows, zen_config('MAX_DISPLAY_SEARCH_RESULTS_REPORTS'), zen_config('MAX_DISPLAY_PAGE_LINKS'), $_GET['page'], zen_get_all_get_params(array('page', 'x', 'y'))); ?></td>
            </tr>
        </table>

        <?php
        // General Best Sellers (filtered by Date or All Time)
    } elseif (($start_date != '' && $end_date != '') || $all_time === 1) {

        $date_query_addition = "";

        // if searching by date, format and join the orders table
        if ($start_date != '' && $end_date != '') {
            $db_start = zen_db_input($start_date . ' 00:00:00');
            $db_end = zen_db_input($end_date . ' 23:59:59');
            $date_query_addition = " JOIN " . TABLE_ORDERS . " o ON op.orders_id = o.orders_id
                                     WHERE o.date_purchased >= '" . $db_start . "'
                                     AND o.date_purchased <= '" . $db_end . "' ";
        }

        $products_query_raw = "SELECT SUM(op.products_quantity) AS products_ordered, op.products_name, op.products_id
                               FROM " . TABLE_ORDERS_PRODUCTS . " op " .
            $date_query_addition . "
                               GROUP BY op.products_id, op.products_name
                               ORDER BY products_ordered DESC, op.products_name";

        $products_query_numrows = '';
        $products_split = new splitPageResults($_GET['page'], (int)zen_config('MAX_DISPLAY_SEARCH_RESULTS_REPORTS'), $products_query_raw, $products_query_numrows);
        $products = $db->Execute($products_query_raw);
        ?>

        <?php if ($start_date != '') { ?>
            <h4><?= sprintf(TEXT_RESULTS_RANGE, zen_output_string_protected($start_date), zen_output_string_protected($end_date)) ?></h4>
        <?php } else { ?>
            <h4><?= TEXT_RESULTS_ALL_TIME ?></h4>
        <?php } ?>
        <table class="table">
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent right"><?php echo TABLE_HEADING_NUMBER; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_PURCHASED; ?>&nbsp;</th>
            </tr>
            <?php

            foreach ($products as $product) {
                $cPath = zen_get_product_path($product['products_id']);
                $product_type = zen_get_products_type($product['products_id']);
                ?>
                <tr class="dataTableRow"
                    onclick="document.location.href = '<?php echo zen_href_link(FILENAME_PRODUCT, '&product_type=' . $product_type . '&cPath=' . $cPath . '&pID=' . $product['products_id'] . '&action=new_product'); ?>'">
                    <td class="dataTableContent text-right"><a
                            href="<?php echo zen_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, zen_get_all_get_params(array('oID', 'action', 'page', 'products_filter')) . 'products_filter=' . $product['products_id']); ?>"><?php echo $product['products_id']; ?></a>
                    </td>
                    <td class="dataTableContent"><a
                            href="<?php echo zen_href_link(FILENAME_PRODUCT, '&product_type=' . $product_type . '&cPath=' . $cPath . '&pID=' . $product['products_id'] . '&action=new_product'); ?>"><?php echo $product['products_name']; ?></a>
                    </td>
                    <td class="dataTableContent text-center"><?php echo $product['products_ordered']; ?></td>
                </tr>
            <?php } ?>
        </table>
        <table class="table">
            <tr>
                <td><?php echo $products_split->display_count($products_query_numrows, zen_config('MAX_DISPLAY_SEARCH_RESULTS_REPORTS'), $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                <td class="text-right"><?php echo $products_split->display_links($products_query_numrows, zen_config('MAX_DISPLAY_SEARCH_RESULTS_REPORTS'), zen_config('MAX_DISPLAY_PAGE_LINKS'), $_GET['page'], zen_get_all_get_params(array('page', 'x', 'y'))); ?></td>
            </tr>
        </table>
        <?php
        // Empty Default Load State
    } else {
        ?>
        <div class="alert alert-info" style="margin-top: 20px;">
            <p class="text-center"><?= TEXT_DEFAULT_INTRO ?></p>
        </div>
    <?php } ?>
    <!-- body_text_eof //-->
    <!-- body_eof //-->
</div>
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
