<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 17 Modified in v1.5.7 $
 */

require 'includes/application_top.php';

if (!function_exists('makeUnixTimestampFromDate')) {
    function makeUnixTimestampFromDate($input, $format)
    {
        if (strtolower($format) == 'mm/dd/yyyy') {
            // Use US date format (m/d/Y)
            return mktime(0, 0, 0, (int)substr($input, 0, 2), (int)substr($input, 3, 2), (int)substr($input, 6, 4));
        }
        if (strtolower($format) == 'dd/mm/yyyy') {
            // Use UK date format (d/m/Y)
            return mktime(0, 0, 0, (int)substr($input, 3, 2), (int)substr($input, 0, 2), (int)substr($input, 6, 4));
        }
        if (strtolower($format) == 'dd.mm.yyyy') {
            // Use CZ, SK date format (d/m/Y)
            return mktime(0, 0, 0, (int)substr($input, 3, 2), (int)substr($input, 0, 2), (int)substr($input, 6, 4));
        }
    }
}

$convertedFormat = str_replace(['mm', 'MM', 'dd', 'yyyy'], ['m', 'm', 'd', 'Y'], DATE_FORMAT_SPIFFYCAL);
$startdate = makeUnixTimestampFromDate(date($convertedFormat, strtotime('-30 days')), DATE_FORMAT_SPIFFYCAL);
$enddate = makeUnixTimestampFromDate(date($convertedFormat), DATE_FORMAT_SPIFFYCAL);
if (!empty($_POST['start_date'])) $startdate = makeUnixTimestampFromDate(zen_db_input($_POST['start_date']), DATE_FORMAT_SPIFFYCAL);
if (!empty($_POST['end_date'])) $enddate = makeUnixTimestampFromDate(zen_db_input($_POST['end_date']), DATE_FORMAT_SPIFFYCAL);


$sql = "SELECT p.products_id, pd.products_name, sum(v.views) as total_views, l.name as language, p.products_type, pt.type_handler, pt.allow_add_to_cart
        FROM " . TABLE_PRODUCTS . " p
        LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id
        LEFT JOIN " . TABLE_LANGUAGES . " l ON l.languages_id = pd.language_id
        INNER JOIN " . TABLE_COUNT_PRODUCT_VIEWS . " v ON p.products_id = v.product_id AND v.language_id = l.languages_id
        LEFT JOIN " . TABLE_PRODUCT_TYPES . " pt ON p.products_type = pt.type_id
        WHERE date_viewed BETWEEN CAST(:startdate AS DATE) AND CAST(:enddate AS DATE)
        GROUP BY p.products_id, pd.products_name, language, p.products_type, pt.type_handler, pt.allow_add_to_cart
        ORDER BY total_views DESC";
$sql = $db->bindVars($sql, ':startdate', date('Y-m-d', $startdate), 'string');
$sql = $db->bindVars($sql, ':enddate', date('Y-m-d', $enddate), 'string');
$products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $sql, $products_query_numrows);
$products = $db->Execute($sql);

?>
    <!doctype html>
    <html <?php echo HTML_PARAMS; ?>>
    <head>
        <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
        <link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
        <script src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
        <script>
            var StartDate = new ctlSpiffyCalendarBox("StartDate", "date_range", "start_date", "btnDate1", "<?php echo date($convertedFormat, $startdate); ?>", scBTNMODE_CUSTOMBLUE);
            var EndDate = new ctlSpiffyCalendarBox("EndDate", "date_range", "end_date", "btnDate2", "<?php echo date($convertedFormat, $enddate); ?>", scBTNMODE_CUSTOMBLUE);
        </script>
    </head>
    <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div id="spiffycalendar" class="text"></div>
    <div class="container-fluid">
        <!-- body //-->
        <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>


        <div class="row">
            <?php echo zen_draw_form('date_range', FILENAME_STATS_PRODUCTS_VIEWED, '', 'post', 'onsubmit="return check_dates(start_date, StartDate.required, end_date, EndDate.required);" class="form-horizontal"'); ?>

            <div class="form-group">
                <?php echo zen_draw_label(TEXT_REPORT_START_DATE, 'start_date', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-4 col-md-3">
                    <script>StartDate.writeControl();
                        StartDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";
                    </script>
                </div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(TEXT_REPORT_END_DATE, 'end_date', 'class="col-sm-3 control-label"'); ?>
                <div class="col-sm-4 col-md-3">
                    <script>EndDate.writeControl();
                        EndDate.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";
                    </script>
                </div>
            </div>

            <div class="col-sm-7 col-md-6 text-right">
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_SUBMIT; ?></button>
            </div>

            <?php echo '</form>'; ?>
        </div>
<br>

        <table class="table table-hover">
            <thead>
            <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent right"><?php echo TABLE_HEADING_NUMBER; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_VIEWED; ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($products as $product) {
                $cPath = zen_get_product_path($product['products_id']);
                $type_handler = $product['type_handler'] . '.php';
                ?>
                <tr class="dataTableRow"
                    onclick="document.location.href = '<?php echo zen_href_link($type_handler, '&product_type=' . $product['products_type'] . '&cPath=' . $cPath . '&pID=' . $product['products_id'] . '&action=new_product'); ?>'">
                    <td class="dataTableContent text-right"><?php echo $product['products_id']; ?></td>
                    <td class="dataTableContent">
                        <a href="<?php echo zen_href_link($type_handler, '&product_type=' . $product['products_type'] . '&cPath=' . $cPath . '&pID=' . $product['products_id'] . '&action=new_product'); ?>"><?php echo $product['products_name']; ?></a>
                        (<?php echo $product['language']; ?>)
                    </td>
                    <td class="dataTableContent text-center"><?php echo $product['total_views']; ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <table class="table">
            <tr>
                <td><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                <td class="text-right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
            </tr>
        </table>
        <!-- body_text_eof //-->
        <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    </body>
    </html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');
