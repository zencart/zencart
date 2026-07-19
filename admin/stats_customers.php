<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ZenExpert 2026-07-15 $
 */

require('includes/application_top.php');

$currencies = new currencies();

$canViewCustomers = check_page(FILENAME_CUSTOMERS, $_GET);

// date range and all-time variables
$start_date = (isset($_GET['start_date']) ? zen_db_prepare_input($_GET['start_date']) : '');
$end_date = (isset($_GET['end_date']) ? zen_db_prepare_input($_GET['end_date']) : '');
$all_time = (isset($_GET['all_time']) ? (int)$_GET['all_time'] : 0);

?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<div class="container-fluid">
    <!-- body //-->

    <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
    <!-- date range filter -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-sm-6">
            <?php echo zen_draw_form('date_search', FILENAME_STATS_CUSTOMERS, '', 'get', 'class="form-horizontal"', true); ?>
            <?php echo zen_hide_session_id(); ?>
            <div class="form-group">
                <label class="control-label col-sm-3"><?= TEXT_DATE_RANGE_START_DATE ?></label>
                <div class="col-sm-5">
                    <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3"><?= TEXT_DATE_RANGE_END_DATE ?></label>
                <div class="col-sm-5">
                    <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <button type="submit" class="btn btn-primary"><?= TEXT_SEARCH_DATE_RANGE ?></button>
                    <a href="<?php echo zen_href_link(FILENAME_STATS_CUSTOMERS, 'all_time=1', 'NONSSL'); ?>" class="btn btn-info"><?= TEXT_SEARCH_ALL_TIME ?></a>
                </div>
            </div>
            <?php echo '</form>'; ?>
        </div>
    </div>
    <?php if (($start_date != '' && $end_date != '') || $all_time === 1) {

        $date_query_addition = "";
        $date_query_addition_m = "";

        // if searching by date, format and append to the queries
        if ($start_date != '' && $end_date != '') {
            $db_start = zen_db_input($start_date . ' 00:00:00');
            $db_end = zen_db_input($end_date . ' 23:59:59');
            $date_query_addition = " AND o.date_purchased >= '" . $db_start . "' AND o.date_purchased <= '" . $db_end . "' ";
            $date_query_addition_m = " WHERE date_purchased >= '" . $db_start . "' AND date_purchased <= '" . $db_end . "' ";
        }

        ?>

        <?php if ($start_date != '') { ?>
            <h4><?= sprintf(TEXT_RESULTS_RANGE, htmlspecialchars($start_date), htmlspecialchars($end_date)) ?></h4>
        <?php } else { ?>
            <h4><?= TEXT_RESULTS_ALL_TIME ?></h4>
        <?php } ?>

        <table class="table table-hover">
            <thead>
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent right"><?= TABLE_HEADING_NUMBER ?></th>
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_CUSTOMERS ?></th>
                <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_TOTAL_PURCHASED ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $customers_query_raw = "SELECT c.customers_id, c.customers_firstname, c.customers_lastname,
                                           SUM(op.products_quantity * op.final_price) + SUM(op.onetime_charges) AS ordersum
                                    FROM " . TABLE_CUSTOMERS . " c,
                                         " . TABLE_ORDERS_PRODUCTS . " op,
                                         " . TABLE_ORDERS . " o
                                    WHERE c.customers_id = o.customers_id
                                        AND o.orders_id = op.orders_id " .
                $date_query_addition . "
                                    GROUP BY c.customers_id, c.customers_firstname, c.customers_lastname
                                    ORDER BY ordersum DESC";
            $customers_split = new splitPageResults($_GET['page'], (int)zen_config('MAX_DISPLAY_SEARCH_RESULTS_REPORTS'), $customers_query_raw, $customers_query_numrows);
            // fix counted customers
            $customers_query_m = $db->Execute("SELECT customers_id
                                                FROM " . TABLE_ORDERS .
                $date_query_addition_m . "
                                               GROUP BY customers_id");
            $customers_query_numrows = $customers_query_m->RecordCount();
            $customers = $db->Execute($customers_query_raw);
            foreach ($customers as $customer) { ?>
                <tr class="dataTableRow"<?php echo($canViewCustomers ? ' onclick="document.location.href = \'' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $customer['customers_id'], 'NONSSL') . '\'"' : ''); ?>>
                    <td class="dataTableContent text-right"><?php echo $customer['customers_id']; ?>&nbsp;&nbsp;</td>
                    <td class="dataTableContent"><?php echo ($canViewCustomers ? '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $customer['customers_id'], 'NONSSL') . '">' : '') . $customer['customers_firstname'] . ' ' . $customers->fields['customers_lastname'] . ($canViewCustomers ? '</a>' : ''); ?></td>
                    <td class="dataTableContent text-right"><?php echo $currencies->format($customer['ordersum']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <table class="table">
            <tr>
                <td><?php echo $customers_split->display_count($customers_query_numrows, zen_config('MAX_DISPLAY_SEARCH_RESULTS_REPORTS'), $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
                <td class="text-right"><?php echo $customers_split->display_links($customers_query_numrows, zen_config('MAX_DISPLAY_SEARCH_RESULTS_REPORTS'), zen_config('MAX_DISPLAY_PAGE_LINKS'), $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
            </tr>
        </table>
    <?php } else { ?>
        <div class="alert alert-info">
            <p class="text-center"><?= TEXT_DEFAULT_INTRO ?></p>
        </div>
    <?php } ?>
    <!-- body_text_eof //-->
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
