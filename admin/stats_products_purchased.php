<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: stats_products_purchased.php 18698 2011-05-04 14:50:06Z wilt $
 */

  require('includes/application_top.php');

  $products_filter = (isset($_GET['products_filter']) ? $_GET['products_filter'] : $products_filter);
  $products_filter = str_replace(' ', ',', $products_filter);
  $products_filter = str_replace(',,', ',', $products_filter);
  $products_filter_name_model = (isset($_GET['products_filter_name_model']) ? $_GET['products_filter_name_model'] : $products_filter_name_model);
require('includes/admin_html_head.php');
?>
</head>
<body>
<!-- header //-->
<div class="header-area">
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
</div>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right">
<?php
// show reset search
    echo zen_draw_form('search', FILENAME_STATS_PRODUCTS_PURCHASED, '', 'get', '', true);
    echo HEADING_TITLE_SEARCH_DETAIL_REPORTS . ' ' . zen_draw_input_field('products_filter') . zen_hide_session_id();
    if (isset($products_filter) && zen_not_null($products_filter)) {
      $products_filter = preg_replace('/[^0-9,]/', '', $products_filter);
      $products_filter = zen_db_input(zen_db_prepare_input($products_filter));
      echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $products_filter;
    }
    if (isset($products_filter) && zen_not_null($products_filter)) {
      echo '<br/ >' . '<a href="' . zen_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
    }
    echo '</form>';

// show reset search
    echo zen_draw_form('search', FILENAME_STATS_PRODUCTS_PURCHASED, '', 'get', '', true);
    echo '<br/ >' . HEADING_TITLE_SEARCH_DETAIL_REPORTS_NAME_MODEL . ' ' . zen_draw_input_field('products_filter_name_model') . zen_hide_session_id();
    if (isset($products_filter_name_model) && zen_not_null($products_filter_name_model)) {
      $products_filter_name_model = zen_db_input(zen_db_prepare_input($products_filter_name_model));
      echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . zen_db_prepare_input($products_filter_name_model);
    }
    if (isset($products_filter_name_model) && zen_not_null($products_filter_name_model)) {
      echo '<br/ >' . '<a href="' . zen_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
    }
    echo '</form>';
?>
            </td>
          </tr>
        </table></td>
      </tr>
<?php
if ($products_filter > 0 or $products_filter_name_model != '') {
  if ($products_filter > 0) {
    // by products_id
    $chk_orders_products_query = "SELECT o.customers_id, op.orders_id, op.products_id, op.products_quantity, op.products_name, op.products_model,
                                  o.customers_name, o.customers_company, o.customers_email_address, o.date_purchased
                                  FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op
                                  WHERE op.products_id in (" . $products_filter . ")
                                  and op.orders_id = o.orders_id
                                  ORDER by op.products_id, o.date_purchased DESC";
  } else {
    // by products name or model
    $chk_orders_products_query = "SELECT o.customers_id, op.orders_id, op.products_id, op.products_quantity, op.products_name, op.products_model,
                                  o.customers_name, o.customers_company, o.customers_email_address, o.date_purchased
                                  FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op
                                  WHERE ((op.products_model LIKE '%" . $products_filter_name_model . "%')
                                  or (op.products_name LIKE '%" . $products_filter_name_model . "%'))
                                  and op.orders_id = o.orders_id
                                  ORDER by op.products_id, o.date_purchased DESC";
}
  $chk_orders_products_query_numrows='';
  $chk_orders_products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $chk_orders_products_query, $chk_orders_products_query_numrows);

  $rows = 0;
  $chk_orders_products = $db->Execute($chk_orders_products_query);
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDERS_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS_INFO; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRODUCTS_QUANTITY; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRODUCTS_NAME; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?>&nbsp;</td>
              </tr>

<?php
  if ($chk_orders_products->EOF) {
?>
              <tr class="dataTableRowSelectedBot">
                <td colspan="7" class="dataTableContent" align="center"><?php echo NONE; ?></td>
              </tr>
<?php } ?>
<?php
  while (!$chk_orders_products->EOF) {
    $rows++;

    if (strlen($rows) < 2) {
      $rows = '0' . $rows;
    }
    if ($products_filter != '') {
    // products_id
      $cPath = zen_get_product_path($products_filter);
    } else {
    // products_name or products_model
      $cPath = zen_get_product_path($chk_orders_products->fields['products_id']);
    }
?>
              <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action', 'page', 'products_filter')) . 'cID=' . $chk_orders_products->fields['customers_id'] . '&action=edit', 'NONSSL') . '">' . $chk_orders_products->fields['customers_id'] . '</a>'; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action', 'page', 'products_filter')) . 'oID=' . $chk_orders_products->fields['orders_id'] . '&action=edit', 'NONSSL') . '">' . $chk_orders_products->fields['orders_id'] . '</a>'; ?></td>
                <td class="dataTableContent"><?php echo zen_date_short($chk_orders_products->fields['date_purchased']); ?></td>
                <td class="dataTableContent"><?php echo $chk_orders_products->fields['customers_name'] . ($chk_orders_products->fields['customers_company'] !='' ? '<br />' . $chk_orders_products->fields['customers_company'] : '') . '<br />' . $chk_orders_products->fields['customers_email_address']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $chk_orders_products->fields['products_quantity']; ?>&nbsp;</td>
                <td class="dataTableContent" align="center"><?php echo '<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products_filter) . '">' . $chk_orders_products->fields['products_name'] . '</a>'; ?>&nbsp;</td>
                <td class="dataTableContent" align="center"><?php echo $chk_orders_products->fields['products_model']; ?>&nbsp;</td>

              </tr>
<?php
    $chk_orders_products->MoveNext();
  }
?>
            </table></td>
          </tr>
          <tr>
            <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText" valign="top"><?php echo $chk_orders_products_split->display_count($chk_orders_products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                <td class="smallText" align="right"><?php echo $chk_orders_products_split->display_links($chk_orders_products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'x', 'y'))); ?>&nbsp;</td>
              </tr>
            </table></td>
          </tr>

<?php
} else {
// all products by name and quantity display
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PURCHASED; ?>&nbsp;</td>
              </tr>
<?php
  if (isset($_GET['page']) && ($_GET['page'] > 1)) $rows = $_GET['page'] * MAX_DISPLAY_SEARCH_RESULTS_REPORTS - MAX_DISPLAY_SEARCH_RESULTS_REPORTS;
// The following OLD query only considers the "products_ordered" value from the products table.
// Thus this older query is somewhat deprecated
  $products_query_raw = "select p.products_id, p.products_ordered, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $_SESSION['languages_id']. "' and p.products_ordered > 0 group by pd.products_id, pd.products_name order by p.products_ordered DESC, pd.products_name";

// The new query uses real order info from the orders_products table, and is theoretically more accurate.
// To use this newer query, remove the "1" from the following line ($products_query_raw1 becomes $products_query_raw )
    $products_query_raw1 =
      "select sum(op.products_quantity) as products_ordered, pd.products_name, op.products_id
      from ".TABLE_ORDERS_PRODUCTS." op
      left join " . TABLE_PRODUCTS_DESCRIPTION . " pd
        on (pd.products_id = op.products_id )
      where pd.language_id = '" . $_SESSION['languages_id']. "'
      group by pd.products_id, pd.products_name
      order by products_ordered DESC, products_name";

  $products_query_numrows='';
  $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $products_query_raw, $products_query_numrows);

  $rows = 0;
  $products = $db->Execute($products_query_raw);
  while (!$products->EOF) {
    $rows++;

    if (strlen($rows) < 2) {
      $rows = '0' . $rows;
    }
    $cPath = zen_get_product_path($products->fields['products_id']);
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products->fields['products_id'] . '&page='); ?>'">
                <td class="dataTableContent" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, zen_get_all_get_params(array('oID', 'action', 'page', 'products_filter')) . 'products_filter=' . $products->fields['products_id']) . '">' . $products->fields['products_id'] . '</a>'; ?>&nbsp;&nbsp;</td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products->fields['products_id'] . '&page=') . '">' . $products->fields['products_name'] . '</a>'; ?></td>
                <td class="dataTableContent" align="center"><?php echo $products->fields['products_ordered']; ?>&nbsp;</td>
              </tr>
<?php
    $products->MoveNext();
  }
?>
            </table></td>
          </tr>
          <tr>
            <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?>&nbsp;</td>
              </tr>
            </table></td>
          </tr>
<?php
} // $products_filter > 0
?>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<div class="footer-area">
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</div>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>