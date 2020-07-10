<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright (c) 2004 DevosC.com    
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 May 22 Modified in v1.5.7 $
 */

  require('includes/application_top.php');

$paypal_ipn_sort_order_array = [
    ['id' => '0', 'text' => TEXT_SORT_PAYPAL_ID_DESC],
    ['id' => '1', 'text' => TEXT_SORT_PAYPAL_ID],
    ['id' => '2', 'text' => TEXT_SORT_ZEN_ORDER_ID_DESC],
    ['id' => '3', 'text' => TEXT_SORT_ZEN_ORDER_ID],
    ['id' => '4', 'text' => TEXT_PAYMENT_AMOUNT_DESC],
    ['id' => '5', 'text' => TEXT_PAYMENT_AMOUNT]
];

  $paypal_ipn_sort_order = 0;
  if (isset($_GET['paypal_ipn_sort_order'])) {
      $paypal_ipn_sort_order = (int)$_GET['paypal_ipn_sort_order'];
  }
//        $ipn_query_raw = "select p.order_id, p.paypal_ipn_id, p.txn_type, p.payment_type, p.payment_status, p.pending_reason, p.mc_currency, p.payer_status, p.mc_currency, p.date_added, p.mc_gross, p.first_name, p.last_name, p.payer_business_name, p.parent_txn_id, p.txn_id from " . TABLE_PAYPAL . " as p, " .TABLE_ORDERS . " as o  where o.orders_id = p.order_id " . $ipn_search . " order by o.orders_id DESC";

  switch ($paypal_ipn_sort_order) {
    case (0):
      $order_by = " ORDER BY p.paypal_ipn_id DESC";
      break;
    case (1):
      $order_by = " ORDER BY p.paypal_ipn_id";
      break;
    case (2):
      $order_by = " ORDER BY p.order_id DESC, p.paypal_ipn_id";
      break;
    case (3):
      $order_by = " ORDER BY p.order_id, p.paypal_ipn_id";
      break;
    case (4):
      $order_by = " ORDER BY p.mc_gross DESC";
      break;
    case (5):
      $order_by = " ORDER BY p.mc_gross";
      break;
    default:
      $order_by = " ORDER BY p.paypal_ipn_id DESC";
      break;
    }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $selected_status = (isset($_GET['payment_status']) ? $_GET['payment_status'] : '');

  require(DIR_FS_CATALOG_MODULES . 'payment/paypal.php');

  $payment_statuses = [];
  $payment_status_trans = $db->Execute("SELECT payment_status_name AS payment_status FROM " . TABLE_PAYPAL_PAYMENT_STATUS );
  foreach ($payment_status_trans as $payment_status_tran) {
    $payment_statuses[] = ['id' => $payment_status_tran['payment_status'],
                         'text' => $payment_status_tran['payment_status']];
  }

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
<!-- body //-->
   <div class="container-fluid">
    <h1><?php echo HEADING_ADMIN_TITLE; ?></h1>
    <div class="row">
    <?php
  $hidden_field = (isset($_GET['paypal_ipn_sort_order'])) ? zen_draw_hidden_field('paypal_ipn_sort_order', $_GET['paypal_ipn_sort_order']) : '';
  echo zen_draw_form('payment_status', FILENAME_PAYPAL, '', 'get') . HEADING_PAYMENT_STATUS . ' ' . zen_draw_pull_down_menu('payment_status', array_merge([['id' => '', 'text' => TEXT_ALL_IPNS]], $payment_statuses), $selected_status, 'onchange="this.form.submit();"') . zen_hide_session_id() . $hidden_field . '</form>';

  $hidden_field = (isset($_GET['payment_status'])) ? zen_draw_hidden_field('payment_status', $_GET['payment_status']) : '';
  echo '&nbsp;&nbsp;&nbsp;' . TEXT_PAYPAL_IPN_SORT_ORDER_INFO . zen_draw_form('paypal_ipn_sort_order', FILENAME_PAYPAL, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('paypal_ipn_sort_order', $paypal_ipn_sort_order_array, $paypal_ipn_sort_order, 'onchange="this.form.submit();"') . zen_hide_session_id() . $hidden_field . '</form>';
?>
   </div>
       <div class="row">
           <div class="col-sm-12 col-md-9 configurationColumnLeft">
              <table class="table">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDER_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYPAL_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXN_TYPE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT_STATUS; ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_PAYMENT_AMOUNT; ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  if (zen_not_null($selected_status)) {
    $ipn_search = "AND p.payment_status = :selectedStatus: ";
    $ipn_search = $db->bindVars($ipn_search, ':selectedStatus:', $selected_status, 'string');
    switch ($selected_status) {
      case 'Pending':
      case 'Completed':
      default:
        $ipn_query_raw = "SELECT p.order_id, p.paypal_ipn_id, p.txn_type, p.payment_type, p.payment_status, p.pending_reason, p.mc_currency, p.payer_status, p.mc_currency, p.date_added, p.mc_gross, p.first_name, p.last_name, p.payer_business_name, p.parent_txn_id, p.txn_id FROM " . TABLE_PAYPAL . " AS p, " .TABLE_ORDERS . " AS o  WHERE o.orders_id = p.order_id " . $ipn_search . $order_by;
        break;
    }
  } else {
        $ipn_query_raw = "SELECT p.order_id, p.paypal_ipn_id, p.txn_type, p.payment_type, p.payment_status, p.pending_reason, p.mc_currency, p.payer_status, p.mc_currency, p.date_added, p.mc_gross, p.first_name, p.last_name, p.payer_business_name, p.parent_txn_id, p.txn_id FROM " . TABLE_PAYPAL . " AS p LEFT JOIN " .TABLE_ORDERS . " AS o ON o.orders_id = p.order_id" . $order_by;
  }
  $ipn_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN, $ipn_query_raw, $ipn_query_numrows);
  $ipn_trans = $db->Execute($ipn_query_raw);
  foreach ($ipn_trans as $ipn_tran) {
    if ((!isset($_GET['ipnID']) || (isset($_GET['ipnID']) && ($_GET['ipnID'] == $ipn_tran['paypal_ipn_id']))) && !isset($ipnInfo)) {
      $ipnInfo = new objectInfo($ipn_tran);
    }

    if (isset($ipnInfo) && is_object($ipnInfo) && ($ipn_tran['paypal_ipn_id'] == $ipnInfo->paypal_ipn_id) ) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_ORDERS, 'page=' . $_GET['page'] . '&ipnID=' . $ipnInfo->paypal_ipn_id . '&oID=' . $ipnInfo->order_id . '&action=edit' . '&referer=ipn' . (zen_not_null($selected_status) ? '&payment_status=' . $selected_status : '') . (zen_not_null($paypal_ipn_sort_order) ? '&paypal_ipn_sort_order=' . $paypal_ipn_sort_order : '') ) . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_PAYPAL, 'page=' . $_GET['page'] . '&ipnID=' . $ipn_tran['paypal_ipn_id'] . (zen_not_null($selected_status) ? '&payment_status=' . $selected_status : '') . (zen_not_null($paypal_ipn_sort_order) ? '&paypal_ipn_sort_order=' . $paypal_ipn_sort_order : '') ) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $ipn_tran['order_id']; ?></td>
                <td class="dataTableContent"><?php echo $ipn_tran['paypal_ipn_id']; ?></td>
                <td class="dataTableContent"><?php echo $ipn_tran['txn_type'] . '<br>' . $ipn_tran['first_name'] . ' ' . $ipn_tran['last_name'] . ($ipn_tran['payer_business_name'] != '' ? '<br>' . $ipn_tran['payer_business_name'] : ''); ?>
                <td class="dataTableContent"><?php echo $ipn_tran['payment_status'] . '<br>Parent Trans ID:' . $ipn_tran['parent_txn_id'] . '<br>Trans ID:' . $ipn_tran['txn_id']; ?></td>
                <td class="dataTableContent text-right"><?php echo $ipn_tran['mc_currency'] . ' '.number_format($ipn_tran['mc_gross'], 2); ?></td>
                <td class="dataTableContent text-right">
                    <?php if (isset($ipnInfo) && is_object($ipnInfo) && ($ipn_tran['paypal_ipn_id'] == $ipnInfo->paypal_ipn_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_PAYPAL, 'page=' . $_GET['page'] . '&ipnID=' . $ipn_tran['paypal_ipn_id']) . (zen_not_null($selected_status) ? '&payment_status=' . $selected_status : '') . (zen_not_null($paypal_ipn_sort_order) ? '&paypal_ipn_sort_order=' . $paypal_ipn_sort_order : '') . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?></td>
              <?php echo '</tr>';
  }
?>
              <tr>
                    <td colspan="3" class="smallText"><?php echo $ipn_split->display_count($ipn_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN, $_GET['page'], TEXT_DISPLAY_PAYPAL_IPN_NUMBER_OF_TX); ?></td>
                    <td colspan="3" class="smallText text-right"><?php echo $ipn_split->display_links($ipn_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN, MAX_DISPLAY_PAGE_LINKS, isset($_GET['page']) ? (int)$_GET['page'] : 1, zen_get_all_get_params(['page'])); ?></td>
              </tr>
            </table>
           </div>
<?php
  $heading = [];
  $contents = [];

  switch ($action) {
    case 'new':
      break;
    case 'edit':
      break;
    case 'delete':
      break;
    default:
      if (isset($ipnInfo) && is_object($ipnInfo)) {
        $heading[] = ['text' => '<strong>' . TEXT_INFO_PAYPAL_IPN_HEADING.' #' . $ipnInfo->paypal_ipn_id . '</strong>'];
        $ipn = $db->Execute("SELECT * FROM " . TABLE_PAYPAL_PAYMENT_STATUS_HISTORY . " WHERE paypal_ipn_id = '" . $ipnInfo->paypal_ipn_id . "'");
        $ipn_count = $ipn->RecordCount();

        $contents[] = ['align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['ipnID', 'action']) . 'oID=' . $ipnInfo->order_id .'&' . 'ipnID=' . $ipnInfo->paypal_ipn_id .'&action=edit' . '&referer=ipn') . '">' . zen_image_button('button_orders.gif', IMAGE_ORDERS) . '</a>'];
        $contents[] = ['text' => TABLE_HEADING_NUM_HISTORY_ENTRIES . ': '. $ipn_count];
        $count = 1;
        foreach ($ipn as $ipn_status_history) {
          $contents[] = ['text' =>  TABLE_HEADING_ENTRY_NUM . ': ' . $count];
          $contents[] = ['text' =>  TABLE_HEADING_DATE_ADDED . ': ' . zen_datetime_short($ipn_status_history['date_added'])];
          $contents[] = ['text' =>  TABLE_HEADING_TRANS_ID . ': ' . $ipn_status_history['txn_id']];
          $contents[] = ['text' =>  TABLE_HEADING_PAYMENT_STATUS . ': ' . $ipn_status_history['payment_status']];
          $contents[] = ['text' =>  TABLE_HEADING_PENDING_REASON . ': ' . $ipn_status_history['pending_reason']];
          $count++;
        }
      }
      break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    $box = new box();
      echo '<div class="col-sm-12 col-md-3 configurationColumnRight">';
    echo $box->infoBox($heading, $contents);
      echo '</div>';
  }
?>
       </div>
</div>
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
