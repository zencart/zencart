<?php
/**
 * @package admin
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions Copyright (c) 2004 DevosC.com    
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: paypal.php 18799 2011-05-25 12:29:41Z wilt $
 */

  require('includes/application_top.php');

  $paypal_ipn_sort_order_array = array(array('id' => '0', 'text' => TEXT_SORT_PAYPAL_ID_DESC),
                             array('id' => '1', 'text' => TEXT_SORT_PAYPAL_ID),
                             array('id' => '2', 'text' => TEXT_SORT_ZEN_ORDER_ID_DESC),
                             array('id' => '3', 'text'=> TEXT_SORT_ZEN_ORDER_ID),
                             array('id' => '4', 'text'=> TEXT_PAYMENT_AMOUNT_DESC),
                             array('id' => '5', 'text'=> TEXT_PAYMENT_AMOUNT)
                             );

  if (isset($_GET['paypal_ipn_sort_order'])) {
    $paypal_ipn_sort_order = $_GET['paypal_ipn_sort_order'];
  } else {
    $paypal_ipn_sort_order = 0;
  }
//        $ipn_query_raw = "select p.order_id, p.paypal_ipn_id, p.txn_type, p.payment_type, p.payment_status, p.pending_reason, p.mc_currency, p.payer_status, p.mc_currency, p.date_added, p.mc_gross, p.first_name, p.last_name, p.payer_business_name, p.parent_txn_id, p.txn_id from " . TABLE_PAYPAL . " as p, " .TABLE_ORDERS . " as o  where o.orders_id = p.order_id " . $ipn_search . " order by o.orders_id DESC";

  switch ($paypal_ipn_sort_order) {
    case (0):
      $order_by = " order by p.paypal_ipn_id DESC";
      break;
    case (1):
      $order_by = " order by p.paypal_ipn_id";
      break;
    case (2):
      $order_by = " order by p.order_id DESC, p.paypal_ipn_id";
      break;
    case (3):
      $order_by = " order by p.order_id, p.paypal_ipn_id";
      break;
    case (4):
      $order_by = " order by p.mc_gross DESC";
      break;
    case (5):
      $order_by = " order by p.mc_gross";
      break;
    default:
      $order_by = " order by p.paypal_ipn_id DESC";
      break;
    }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $selected_status = (isset($_GET['payment_status']) ? $_GET['payment_status'] : '');

  require(DIR_FS_CATALOG_MODULES . 'payment/paypal.php');

  $payment_statuses = array();
  $payment_status_trans = $db->Execute("select payment_status_name as payment_status from " . TABLE_PAYPAL_PAYMENT_STATUS );
  while (!$payment_status_trans->EOF) {
    $payment_statuses[] = array('id' => $payment_status_trans->fields['payment_status'],
                                'text' => $payment_status_trans->fields['payment_status']);
    $payment_status_trans->MoveNext();
  }

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" />
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onLoad="SetFocus(), init();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_ADMIN_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right">
<?php
  echo zen_draw_form('payment_status', FILENAME_PAYPAL, '', 'get') . HEADING_PAYMENT_STATUS . ' ' . zen_draw_pull_down_menu('payment_status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_IPNS)), $payment_statuses), $selected_status, 'onChange="this.form.submit();"') . zen_hide_session_id() . zen_draw_hidden_field('paypal_ipn_sort_order', $_GET['paypal_ipn_sort_order']) . '</form>';
?>
<?php
  echo '&nbsp;&nbsp;&nbsp;' . TEXT_PAYPAL_IPN_SORT_ORDER_INFO . zen_draw_form('paypal_ipn_sort_order', FILENAME_PAYPAL, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('paypal_ipn_sort_order', $paypal_ipn_sort_order_array, $reset_paypal_ipn_sort_order, 'onChange="this.form.submit();"') . zen_hide_session_id() . zen_draw_hidden_field('payment_status', $_GET['payment_status']) . '</form>';
?>
            </td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDER_NUMBER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYPAL_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TXN_TYPE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PAYMENT_AMOUNT; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  if (zen_not_null($selected_status)) {
    $ipn_search = "and p.payment_status = :selectedStatus: ";
    $ipn_search = $db->bindVars($ipn_search, ':selectedStatus:', $selected_status, 'string');
    switch ($selected_status) {
      case 'Pending':
      case 'Completed':
      default:
        $ipn_query_raw = "select p.order_id, p.paypal_ipn_id, p.txn_type, p.payment_type, p.payment_status, p.pending_reason, p.mc_currency, p.payer_status, p.mc_currency, p.date_added, p.mc_gross, p.first_name, p.last_name, p.payer_business_name, p.parent_txn_id, p.txn_id from " . TABLE_PAYPAL . " as p, " .TABLE_ORDERS . " as o  where o.orders_id = p.order_id " . $ipn_search . $order_by;
        break;
    }
  } else {
        $ipn_query_raw = "select p.order_id, p.paypal_ipn_id, p.txn_type, p.payment_type, p.payment_status, p.pending_reason, p.mc_currency, p.payer_status, p.mc_currency, p.date_added, p.mc_gross, p.first_name, p.last_name, p.payer_business_name, p.parent_txn_id, p.txn_id from " . TABLE_PAYPAL . " as p left join " .TABLE_ORDERS . " as o on o.orders_id = p.order_id" . $order_by;
  }
  $ipn_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN, $ipn_query_raw, $ipn_query_numrows);
  $ipn_trans = $db->Execute($ipn_query_raw);
  while (!$ipn_trans->EOF) {
    if ((!isset($_GET['ipnID']) || (isset($_GET['ipnID']) && ($_GET['ipnID'] == $ipn_trans->fields['paypal_ipn_id']))) && !isset($ipnInfo) ) {
      $ipnInfo = new objectInfo($ipn_trans->fields);
    }

    if (isset($ipnInfo) && is_object($ipnInfo) && ($ipn_trans->fields['paypal_ipn_id'] == $ipnInfo->paypal_ipn_id) ) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_ORDERS, 'page=' . $_GET['page'] . '&ipnID=' . $ipnInfo->paypal_ipn_id . '&oID=' . $ipnInfo->order_id . '&action=edit' . '&referer=ipn' . (zen_not_null($selected_status) ? '&payment_status=' . $selected_status : '') . (zen_not_null($paypal_ipn_sort_order) ? '&paypal_ipn_sort_order=' . $paypal_ipn_sort_order : '') ) . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_PAYPAL, 'page=' . $_GET['page'] . '&ipnID=' . $ipn_trans->fields['paypal_ipn_id'] . (zen_not_null($selected_status) ? '&payment_status=' . $selected_status : '') . (zen_not_null($paypal_ipn_sort_order) ? '&paypal_ipn_sort_order=' . $paypal_ipn_sort_order : '') ) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"> <?php echo $ipn_trans->fields['order_id']; ?> </td>
                <td class="dataTableContent"> <?php echo $ipn_trans->fields['paypal_ipn_id']; ?> </td>
                <td class="dataTableContent"> <?php echo $ipn_trans->fields['txn_type'] . '<br />' . $ipn_trans->fields['first_name'] . ' ' . $ipn_trans->fields['last_name'] . ($ipn_trans->fields['payer_business_name'] != '' ? '<br />' . $ipn_trans->fields['payer_business_name'] : ''); ?>
                <td class="dataTableContent"><?php echo $ipn_trans->fields['payment_status_name'] . ' '. $ipn_trans->fields['payment_status'] . '<br />Parent Trans ID:' . $ipn_trans->fields['parent_txn_id'] . '<br />Trans ID:' . $ipn_trans->fields['txn_id']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $ipn_trans->fields['mc_currency'] . ' '.number_format($ipn_trans->fields['mc_gross'], 2); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($ipnInfo) && is_object($ipnInfo) && ($ipn_trans->fields['paypal_ipn_id'] == $ipnInfo->paypal_ipn_id) ) { echo ADMIN_ROW_ICON_ARROW_RIGHT; } else { echo '<a href="' . zen_href_link(FILENAME_PAYPAL, 'page=' . $_GET['page'] . '&ipnID=' . $ipn_trans->fields['paypal_ipn_id']) . (zen_not_null($selected_status) ? '&payment_status=' . $selected_status : '') . (zen_not_null($paypal_ipn_sort_order) ? '&paypal_ipn_sort_order=' . $paypal_ipn_sort_order : '') . '">' . ADMIN_ROW_ICON_INFO . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $ipn_trans->MoveNext();
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $ipn_split->display_count($ipn_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN, $_GET['page'], "Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> IPN's)"); ?></td>
                    <td class="smallText" align="right"><?php echo $ipn_split->display_links($ipn_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_PAYPAL_IPN, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], (zen_not_null($selected_status) ? '&payment_status=' . $selected_status : '') . (zen_not_null($paypal_ipn_sort_order) ? '&paypal_ipn_sort_order=' . $paypal_ipn_sort_order : '')); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      break;
    case 'edit':
      break;
    case 'delete':
      break;
    default:
      if (is_object($ipnInfo)) {
        $heading[] = array('text' => '<strong>' . TEXT_INFO_PAYPAL_IPN_HEADING.' #' . $ipnInfo->paypal_ipn_id . '</strong>');
        $ipn = $db->Execute("select * from " . TABLE_PAYPAL_PAYMENT_STATUS_HISTORY . " where paypal_ipn_id = '" . $ipnInfo->paypal_ipn_id . "'");
        $ipn_count = $ipn->RecordCount();

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('ipnID', 'action')) . 'oID=' . $ipnInfo->order_id .'&' . 'ipnID=' . $ipnInfo->paypal_ipn_id .'&action=edit' . '&referer=ipn') . '">' . zen_image_button('button_orders.gif', IMAGE_ORDERS) . '</a>');
        $contents[] = array('text' => '<br>' . TABLE_HEADING_NUM_HISTORY_ENTRIES . ': '. $ipn_count);
        $count = 1;
        while (!$ipn->EOF) {
          $contents[] = array('text' => '<br>' . TABLE_HEADING_ENTRY_NUM . ': '. $count);
          $contents[] = array('text' =>  TABLE_HEADING_DATE_ADDED . ': '. zen_datetime_short($ipn->fields['date_added']));
          $contents[] = array('text' =>  TABLE_HEADING_TRANS_ID . ': '.$ipn->fields['txn_id']);
          $contents[] = array('text' =>  TABLE_HEADING_PAYMENT_STATUS . ': '.$ipn->fields['payment_status']);
          $contents[] = array('text' =>  TABLE_HEADING_PENDING_REASON . ': '.$ipn->fields['pending_reason']);
          $count++;
          $ipn->MoveNext();
        }
      }
      break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');
