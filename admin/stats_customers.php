<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
require('includes/application_top.php');

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();
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

      <table class="table table-hover">
        <thead>
          <tr class="dataTableHeadingRow">
            <th class="dataTableHeadingContent right"><?php echo TABLE_HEADING_NUMBER; ?></th>
            <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></th>
            <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_TOTAL_PURCHASED; ?>&nbsp;</th>
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
                                    AND o.orders_id = op.orders_id
                                    GROUP BY c.customers_id, c.customers_firstname, c.customers_lastname
                                    ORDER BY ordersum DESC";
            $customers_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $customers_query_raw, $customers_query_numrows);
// fix counted customers
            $customers_query_m = $db->Execute("SELECT customers_id
                                               FROM " . TABLE_ORDERS . "
                                               GROUP BY customers_id");
            $customers_query_numrows = $customers_query_m->RecordCount();
            $customers = $db->Execute($customers_query_raw);
            foreach ($customers as $customer) { ?>
            <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $customer['customers_id'], 'NONSSL'); ?>'">
              <td class="dataTableContent text-right"><?php echo $customer['customers_id']; ?>&nbsp;&nbsp;</td>
              <td class="dataTableContent"><a href="<?php echo zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $customer['customers_id'], 'NONSSL'); ?>"><?php echo $customer['customers_firstname'] . ' ' . $customers->fields['customers_lastname']; ?></a></td>
              <td class="dataTableContent text-right"><?php echo $currencies->format($customer['ordersum']); ?></td>
            </tr>
            <?php } ?>
        </tbody>
      </table>
      <table class="table">
        <tr>
          <td><?php echo $customers_split->display_count($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
          <td class="text-right"><?php echo $customers_split->display_links($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
        </tr>
      </table>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
