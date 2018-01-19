<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions copyright COWOA authors see https://www.zen-cart.com/downloads.php?do=file&id=1115
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Modified in v1.6.0 $
 */

  require('includes/application_top.php');

  // unset variable which is sometimes tainted by bad plugins like magneticOne tools
  if (isset($module)) unset($module);

  $currencies = new currencies();

  include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php');

  // prepare order-status pulldown list
  $orders_statuses_pulldown_list = array();
  $orders_status_array = \order::get_order_statuses();
  foreach($orders_status_array as $key=>$val)
  {
    $orders_statuses_pulldown_list[] = array('id' => $key, 'text' => $val . ' [' . $key . ']');
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $order_exists = false;

  if (isset($_GET['oID']) && trim($_GET['oID']) == '') unset($_GET['oID']);
  if ($action == 'edit' && !isset($_GET['oID'])) $action = '';
  if (isset($_GET['oID'])) $_GET['oID'] = (int)$_GET['oID'];

// initialize the order from get/post
  $oID = 0;
  if (isset($_POST['oID'])) {
    $oID = (int)$_POST['oID'];
  } elseif (isset($_GET['oID'])) {
    $oID = (int)$_GET['oID'];
  }
  if ($oID) {
    $order = new \order((int)$oID);
    $order_exists = ($order->id > 0);
    if (!$order_exists) {
      if ($action != '') $messageStack->add_session(ERROR_ORDER_DOES_NOT_EXIST . ' ' . $oID, 'error');
      zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action'))));
    }
  }

  // disallow any action other than "edit" (view) if demo active
  if (zen_admin_demo() && $action != 'edit') {
    $_GET['action']= '';
    $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
    zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
  }

  // process $action events
  if (zen_not_null($action) && $order_exists == true) {
    switch ($action) {
      case 'edit':
      // reset single download to on
        if ($_GET['download_reset_on'] > 0) {
          $order->resetSingleDownloadToOn((int)$_GET['download_reset_on']);
          unset($_GET['download_reset_on']);
          $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_ON, 'success');
          zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
        }
      // reset single download to off
        if ($_GET['download_reset_off'] > 0) {
          $order->resetSingleDownloadToOff((int)$_GET['download_reset_off']);
          unset($_GET['download_reset_off']);
          $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF, 'success');
          zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
        }
      break;
      case 'update_order':
        if ((int)$_POST['status'] < 1) break;

        $order_updated = $order->update_status_and_comments($zcRequest->readPost('status'), $zcRequest->readPost('comments'), $zcRequest->readPost('notify', 0), $zcRequest->readPost('notify_comments', '') );

        if ($order_updated) {
          $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
        } else {
          $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
        }
        zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
        break;
      case 'deleteconfirm':
        $order->delete_order($oID, $_POST['restock'] == 'on');
        zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action'))));
        break;
      case 'delete_cvv':
        $order->delete_cvv($zcRequest->readGet('oID'));
        zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
        break;
      case 'mask_cc':
        $order->mask_cc($zcRequest->readGet('oID'));
        zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
        break;

      case 'doRefund':
        $order->doRefund();
        zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
        break;
      case 'doAuth':
        $order->doAuth();
        zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
        break;
      case 'doCapture':
        $order->doCapture();
        zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
        break;
      case 'doVoid':
        $order->doVoid();
        zen_redirect(zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit'));
        break;
      default:
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_DEFAULT_ACTION', $oID, $order);
        break;
    }
  }
require('includes/admin_html_head.php');
?>
<script type="text/javascript">
function couponpopupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
</script>
</head>
<body>
<!-- header //-->
<div class="header-area">
<?php
  require(DIR_WS_INCLUDES . 'header.php');
?>
</div>
<!-- header_eof //-->

<!-- body //-->
<table class="container-fluid" border="0" width="100%" cellspacing="2" cellpadding="2">
<!-- body_text //-->
  <tr>
    <td class="pageHeading"><?php echo ($action == 'edit' && $order_exists) ? HEADING_TITLE_DETAILS : HEADING_TITLE; ?></td>
  </tr>

<?php $order_list_button = '<button type="button" class="btn btn-default" onclick="window.location.href=\'' . zen_admin_href_link(FILENAME_ORDERS) . '\'"><i class="fa fa-th-list" aria-hidden="true"></i> ' . BUTTON_TO_LIST . '</button>'; ?>
<?php if ($action == '') { ?>
<!-- search -->
  <tr>
    <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="row noprint">
          <div class="form-inline">
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
              <?php echo zen_draw_form('search', FILENAME_ORDERS, '', 'get', '', true); ?>
                <label for="allSearch" class="sr-only"><?php echo HEADING_TITLE_SEARCH_ALL; ?></label>
                <div class="input-group">
                  <?php
                  $placeholder = zen_output_string_protected(isset($_GET['search']) && $_GET['search_orders_products'] != '' ? $_GET['search'] : HEADING_TITLE_SEARCH_ALL);
                  echo zen_draw_input_field('search', '', 'id="allSearch" class="form-control" placeholder="' . $placeholder . '"');
                  ?>
                <?php if ((isset($_GET['search']) && zen_not_null($_GET['search'])) or $_GET['cID'] !='') { ?>
                  <div class="input-group-btn">
                    <a class="btn btn-danger" role="button" aria-label="<?php echo TEXT_RESET_FILTER; ?>" href="<?php echo zen_admin_href_link(FILENAME_ORDERS); ?>"><span aria-hidden="true">&times;</span></a>
                  </div>
                <?php } ?>
                </div>
              <?php echo '</form>'; ?>
            </div>
            <div class="form-group col-xs-6 col-sm-3 col-md-3 col-lg-3">
              <?php echo zen_draw_form('search_orders_products', FILENAME_ORDERS, '', 'get', '', true); ?>
                <label for="productSearch" class="sr-only"><?php echo HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS; ?></label>
                <div class="input-group">
                  <?php
                  $placeholder = zen_output_string_protected(isset($_GET['search_orders_products']) && $_GET['search_orders_products'] != '' ? $_GET['search_orders_products'] : HEADING_TITLE_SEARCH_PRODUCTS);
                  echo zen_draw_input_field('search_orders_products', '', 'id="productSearch" class="form-control" aria-describedby="helpBlock3" placeholder="' . $placeholder . '"');
                  ?>
                <?php if ((isset($_GET['search_orders_products']) && zen_not_null($_GET['search_orders_products'])) or $_GET['cID'] !='') { ?>
                  <div class="input-group-btn">
                    <a class="btn btn-danger" role="button" aria-label="<?php echo TEXT_RESET_FILTER; ?>" href="<?php echo zen_admin_href_link(FILENAME_ORDERS); ?>"><span aria-hidden="true">&times;</span></a>
                  </div>
                <?php } ?>
                </div>
                <span id="helpBlock3" class="help-block"><?php echo HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS; ?></span>
              <?php echo '</form>'; ?>
            </div>
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
              <?php echo zen_draw_form('orders', FILENAME_ORDERS, '', 'get', '', true);
              ?>
                <label for="orderSearch" class="sr-only"><?php echo HEADING_TITLE_SEARCH; ?></label>
                <?php
                echo zen_draw_input_field('oID', '', 'size="11" maxlength="11" id="orderSearch" class="form-control" placeholder="' . HEADING_TITLE_SEARCH . '"', '', 'number');
                echo zen_draw_hidden_field('action', 'edit');
                echo '</form>';
              ?>
            </div>
            <div class="form-group col-xs-4 col-sm-3 col-md-3 col-lg-3">
              <?php echo zen_draw_form('status', FILENAME_ORDERS, '', 'get', '', true);
                echo '<label for="selectstatus" class="sr-only">' . HEADING_TITLE_STATUS . '</label> ' . zen_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses_pulldown_list), (int)$_GET['status'], 'class="form-control" onChange="this.form.submit();" id="selectstatus"');
                echo '</form>';
              ?>
            </div>
         </div>
        </td>

      </tr>
<!-- search -->
<?php } ?>


<?php
  if ($action == 'edit' && $order_exists) {
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_EDIT_BEGIN', $oID, $order);
    if ($order->info['payment_module_code']) {
      if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
        require(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
        require(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
        $module = new $order->info['payment_module_code'];
//        echo $module->admin_notification($oID);
      }
    }


    $prev_button = '';
    $result = $db->Execute("SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id < " . (int)$oID . " ORDER BY orders_id DESC LIMIT 1");
    if ($result->RecordCount()) {
      $prev_button = '<button type="button" class="btn btn-default" onclick="window.location.href=\'' . zen_admin_href_link(FILENAME_ORDERS, 'oID=' . $result->fields['orders_id'] . '&action=edit') . '\'">&laquo; ' . $result->fields['orders_id'] . '</button>';
    }

    $next_button = '';
    $result = $db->Execute("SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id > '" . $oID . "' ORDER BY orders_id ASC LIMIT 1");
    if ($result->RecordCount()) {
      $next_button = '<button type="button" class="btn btn-default" onclick="window.location.href=\'' . zen_admin_href_link(FILENAME_ORDERS, 'oID=' . $result->fields['orders_id'] . '&action=edit') . '\'">' . $result->fields['orders_id'] . ' &raquo;</button>';
    }

?>
      <tr>
        <td width="100%" class="noprint"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="row">
              <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-5 col-md-offset-3 col-lg-4 col-lg-offset-4">
                <div class="input-group">
                  <div class="input-group-btn">
                    <?php echo $prev_button; ?>
                  </div>
                  <?php
                  echo zen_draw_form('input_oid', FILENAME_ORDERS, '', 'get', '', true);
                  echo zen_draw_input_field('oID', '', 'size="11" maxlength="11" class="form-control" placeholder="' . SELECT_ORDER_LIST . '"', '', 'number');
                  echo zen_draw_hidden_field('action', 'edit');
                  echo '</form>';
                  ?>
                  <div class="input-group-btn">
                    <?php echo ($next_button == '') ? $order_list_button : $next_button; ?>
                  </div>
                  <div class="input-group-btn">
                    <button type="button" class="btn btn-default" onclick="javascript:history.back()"><i class="fa fa-undo" aria-hidden="true"></i> <?php echo IMAGE_BACK; ?></button>
                  </div>
              </div>
            </td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="3"><?php echo zen_draw_separator(); ?></td>
          </tr>
          <tr>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><strong><?php echo ENTRY_CUSTOMER_ADDRESS . '<br /><i class="fa fa-2x fa-user"></i>'; ?></strong></td>
                <td class="main"><?php echo zen_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td class="main"><a href="tel:<?php echo $order->customer['telephone']; ?>"><?php echo $order->customer['telephone']; ?></a></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_EMAIL_ADDRESS; ?></strong></td>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo TEXT_INFO_IP_ADDRESS; ?></strong></td>
                <?php if ($order->info['ip_address'] != '') {
                  $lookup_ip = substr($order->info['ip_address'], 0, strpos($order->info['ip_address'], ' '));
                ?>
                <td class="main"><a href="http://www.dnsstuff.com/tools#whois|type=ipv4&&value=<?php echo $lookup_ip; ?>"  target="_blank"><?php echo $order->info['ip_address']; ?></a></td>
                <?php } else { ?>
                <td class="main"><?php echo TEXT_UNKNOWN; ?></td>
                <?php } ?>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_CUSTOMER; ?></strong></td>
                <td class="main"><?php echo '<a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, 'search=' . $order->customer['email_address']) . '" . >' . '<i class="fa fa-search"></i> ' . TEXT_CUSTOMER_LOOKUP . '</a>'; ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><strong><?php echo ENTRY_SHIPPING_ADDRESS . '<br /><i class="fa fa-2x fa-truck"></i>'; ?></strong></td>
                <td class="main"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><strong><?php echo ENTRY_BILLING_ADDRESS . '<br /><i class="fa fa-2x fa-credit-card"></i>'; ?></strong></td>
                <td class="main"><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><strong><?php echo ENTRY_ORDER_ID . $oID; ?></strong></td>
      </tr>
      <tr>
     <td><table border="0" cellspacing="0" cellpadding="2">
        <tr>
           <td class="main"><strong><?php echo ENTRY_DATE_PURCHASED; ?></strong></td>
           <td class="main"><?php echo zen_date_long($order->info['date_purchased']); ?></td>
        </tr>
        <tr>
           <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
           <td class="main"><?php echo $order->info['payment_method']; ?></td>
        </tr>
<?php
    if (zen_not_null($order->info['cc_type']) || zen_not_null($order->info['cc_owner']) || zen_not_null($order->info['cc_number'])) {
?>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
            <td class="main"><?php echo $order->info['cc_type']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
            <td class="main"><?php echo $order->info['cc_owner']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['cc_number'] . (zen_not_null($order->info['cc_number']) && !strstr($order->info['cc_number'],'X') && !strstr($order->info['cc_number'],'********') ? '&nbsp;&nbsp;<a href="' . zen_admin_href_link(FILENAME_ORDERS, '&action=mask_cc&oID=' . $oID) . '" class="noprint">' . TEXT_MASK_CC_NUMBER . '</a>' : ''); ?></td>
          </tr>
<?php if (zen_not_null($order->info['cc_cvv'])) { ?>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_CVV; ?></td>
            <td class="main"><?php echo $order->info['cc_cvv'] . (zen_not_null($order->info['cc_cvv']) && !strstr($order->info['cc_cvv'],TEXT_DELETE_CVV_REPLACEMENT) ? '&nbsp;&nbsp;<a href="' . zen_admin_href_link(FILENAME_ORDERS, '&action=delete_cvv&oID=' . $oID) . '" class="noprint">' . TEXT_DELETE_CVV_FROM_DATABASE . '</a>' : ''); ?></td>
          </tr>
<?php } ?>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
            <td class="main"><?php echo $order->info['cc_expires']; ?></td>
          </tr>
<?php
    }
?>
        </table></td>
      </tr>
<?php
      if (is_object($module) && method_exists($module, 'admin_notification')) {
?>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <?php echo $module->admin_notification($oID); ?>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      if (DISPLAY_PRICE_WITH_TAX_ADMIN == 'true')
      {
        $priceIncTax = $currencies->format(zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']),$currencies->get_decimal_places($order->info['currency'])) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
      } else
      {
        $priceIncTax = $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
      }
      echo '          <tr class="dataTableRow">' . "\n" .
           '            <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
           '            <td class="dataTableContent" valign="top">' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
          echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value']));
          if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          if ($order->products[$i]['attributes'][$j]['product_attribute_is_free'] == '1' and $order->products[$i]['product_is_free'] == '1') echo TEXT_INFO_ATTRIBUTE_FREE;
          echo '</i></small></nobr>';
        }
      }

      echo '            </td>' . "\n" .
           '            <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top">' . zen_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><strong>' .
                          $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</strong></td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><strong>' .
                          $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</strong></td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><strong>' .
                          $currencies->format(zen_round($order->products[$i]['final_price'], $currencies->get_decimal_places($order->info['currency'])) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</strong></td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><strong>' .
                          $priceIncTax .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</strong></td>' . "\n";
      echo '          </tr>' . "\n";
    }
?>
          <tr>
            <td align="right" colspan="8"><?php echo sprintf(TEXT_TOTAL_WEIGHT, $order->info['order_weight']); ?></td>
          </tr>
          <tr>
            <td align="right" colspan="8"><table border="0" cellspacing="0" cellpadding="2">
<?php
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo '              <tr>' . "\n" .
           '                <td align="right" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Text">' . $order->totals[$i]['title'] . '</td>' . "\n" .
           '                <td align="right" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Amount">' . $currencies->format($order->totals[$i]['value'], true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n" .
           '              </tr>' . "\n";
    }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>

<?php
  // show downloads
  require(DIR_WS_MODULES . 'orders_download.php');
?>

      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><table border="1" cellspacing="0" cellpadding="5">
          <tr>
            <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
            <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></strong></td>
            <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></td>
            <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
          </tr>
<?php
    $orders_history = $order->status_history;
    if (count($orders_history)) {
      foreach($orders_history as $history) {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" align="center">' . zen_datetime_short($history['date_added']) . '</td>' . "\n" .
             '            <td class="smallText" align="center">';
        if ($history['customer_notified'] == '1') {
          echo zen_image(DIR_WS_ICONS . 'tick.gif', TEXT_YES) . "</td>\n";
        } else if ($history['customer_notified'] == '-1') {
          echo zen_image(DIR_WS_ICONS . 'locked.gif', TEXT_HIDDEN) . "</td>\n";
        } else {
          echo zen_image(DIR_WS_ICONS . 'unlocked.gif', TEXT_VISIBLE) . "</td>\n";
        }
        echo '            <td class="smallText">' . $orders_status_array[$history['orders_status_id']] . '</td>' . "\n";
        echo '            <td class="smallText">' . nl2br(zen_output_string_protected($history['comments'])) . '&nbsp;</td>' . "\n" .
             '          </tr>' . "\n";
      }
    } else {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" colspan="5">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '          </tr>' . "\n";
    }
?>
        </table></td>
      </tr>
      <tr>
        <td class="main noprint"><br /><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
      </tr>
      <tr>
        <td class="noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr><?php echo zen_draw_form('status', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=update_order', 'post', '', true); ?>
        <td class="main noprint"><?php echo zen_draw_textarea_field('comments', 'soft', '60', '5'); ?></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main noprint">
          <strong><?php echo TEXT_INFO_ORDER_LANGUAGE; ?></strong> <?php echo $order->language['name']; ?>
        </td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2" class="noprint">
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><strong><?php echo ENTRY_STATUS; ?></strong> <?php echo zen_draw_pull_down_menu('status', $orders_statuses_pulldown_list, $order->info['orders_status']); ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_NOTIFY_CUSTOMER; ?></strong> [<?php echo zen_draw_radio_field('notify', '1', true) . '-' . TEXT_EMAIL . ' ' . zen_draw_radio_field('notify', '0', FALSE) . '-' . TEXT_NOEMAIL . ' ' . zen_draw_radio_field('notify', '-1', FALSE) . '-' . TEXT_HIDE; ?>]&nbsp;&nbsp;&nbsp;</td>
                <td class="main"><strong><?php echo ENTRY_NOTIFY_COMMENTS; ?></strong> <?php echo zen_draw_checkbox_field('notify_comments', '', true); ?></td>
              </tr>
              <tr><td><br /></td></tr>
            </table></td>
            <td valign="top"><?php echo zen_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
          </tr>
        </table></td>
      </form></tr>
      <tr>
<?php
        // -----
        // Enable the addition of extra buttons when editing the order.
        //
        $extra_buttons = '';
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_EDIT_BUTTONS', $oID, $order, $extra_buttons);
?>
        <td colspan="2" align="right" class="noprint"><?php echo '<a href="' . zen_admin_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $order->id) . '" target="_blank">' . zen_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> <a href="' . zen_admin_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $order->id) . '" target="_blank">' . zen_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> <a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action'))) . '">' . zen_image_button('button_orders.gif', IMAGE_ORDERS) . '</a>' . $extra_buttons; ?></td>
      </tr>
<?php
// check if order has open gv
        if ($order->info['gv_count_in_queue'] > 0) {
          $goto_gv = '<a href="' . zen_admin_href_link(FILENAME_GV_QUEUE, 'order=' . $order->id) . '">' . zen_image_button('button_gift_queue.gif',IMAGE_GIFT_QUEUE) . '</a>';
          echo '      <tr><td align="right"><table width="225"><tr>';
          echo '        <td align="center">';
          echo $goto_gv . '&nbsp;&nbsp;';
          echo '        </td>';
          echo '      </tr></table></td></tr>';
        }
?>
<?php
  } else {
      $extra_legends = '';
      $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_MENU_LEGEND', array (), $extra_legends);
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="smallText"><?php echo TEXT_LEGEND . ' ' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10) . ' ' . TEXT_BILLING_SHIPPING_MISMATCH . $extra_legends; ?>
          </td>
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                <td class="dataTableHeadingContent" align="left" width="50"><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_CUSTOMER_COMMENTS; ?></td>
                <td class="dataTableHeadingContent noprint" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>

<?php
$orders_query_raw = \order::build_search_query($_GET['search'], $_GET['search_orders_products'], $_GET['cID'], $_GET['status']);

// Split Page
// reset page when page is unknown
if (($_GET['page'] == '' or $_GET['page'] <= 1) and (int)$oID > 0) {
  $check_page = $db->Execute($orders_query_raw);
  $check_count=1;
  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_ORDERS) {
    while (!$check_page->EOF) {
      if ($check_page->fields['orders_id'] == (int)$oID) {
        break;
      }
      $check_count++;
      $check_page->MoveNext();
    }
    $_GET['page'] = round((($check_count/MAX_DISPLAY_SEARCH_RESULTS_ORDERS)+(fmod_round($check_count,MAX_DISPLAY_SEARCH_RESULTS_ORDERS) !=0 ? .5 : 0)),0);
  } else {
    $_GET['page'] = 1;
  }
}

//    $orders_query_numrows = '';
    $orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_ORDERS, $orders_query_raw, $orders_query_numrows);
    $orders = $db->Execute($orders_query_raw);
    while (!$orders->EOF) {
    if ((!isset($oID) || (isset($oID) && ($oID == $orders->fields['orders_id']))) && !isset($oInfo)) {
        $oInfo = new objectInfo($orders->fields);
      }

      if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID')) . 'oID=' . $orders->fields['orders_id']) . '\'">' . "\n";
      }

      $show_difference = '';
      if ((strtoupper($orders->fields['delivery_name']) != strtoupper($orders->fields['billing_name']) and trim($orders->fields['delivery_name']) != '')) {
        $show_difference = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10) . '&nbsp;';
      }
      if ((strtoupper($orders->fields['delivery_street_address']) != strtoupper($orders->fields['billing_street_address']) and trim($orders->fields['delivery_street_address']) != '')) {
        $show_difference = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10) . '&nbsp;';
      }
      $extra_action_icons = '';
      $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_LISTING_ROW', array (), $orders->fields, $show_difference, $extra_action_icons);
      $show_payment_type = $orders->fields['payment_module_code'] . '<br />' . $orders->fields['shipping_module_code'];
?>
                <td class="dataTableContent" align="right"><?php echo $show_difference . $orders->fields['orders_id']; ?></td>
                <td class="dataTableContent" align="left" width="50"><?php echo $show_payment_type; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_admin_href_link(FILENAME_CUSTOMERS, 'cID=' . $orders->fields['customers_id']) . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW . ' ' . TABLE_HEADING_CUSTOMERS) . '</a>&nbsp;' . $orders->fields['customers_name'] . ($orders->fields['customers_company'] != '' ? '<br />' . $orders->fields['customers_company'] : ''); ?></td>
                <td class="dataTableContent" align="right"><?php echo strip_tags($orders->fields['order_total']); ?></td>
                <td class="dataTableContent" align="center"><?php echo zen_datetime_short($orders->fields['date_purchased']); ?></td>
                <td class="dataTableContent" align="right"><?php echo ($orders->fields['orders_status_name'] != '' ? $orders->fields['orders_status_name'] : TEXT_INVALID_ORDER_STATUS); ?></td>
                <td class="dataTableContent" align="center"><?php echo (zen_get_orders_comments($orders->fields['orders_id']) == '' ? '' : zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', TEXT_COMMENTS_YES, 16, 16)); ?></td>

                <td class="dataTableContent noprint" align="right"><?php echo '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders->fields['orders_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>' . $extra_action_icons; ?><?php if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID')) . 'oID=' . $orders->fields['orders_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $orders->MoveNext();
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ORDERS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                    <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ORDERS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
                  </tr>
<?php
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
?>
                  <tr>
                    <td class="smallText" align="right" colspan="2">
                      <?php
                        echo '<a href="' . zen_admin_href_link(FILENAME_ORDERS) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>';
                        if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
                          echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . zen_output_string_protected($_GET['search']);
                        }
                      ?>
                    </td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ORDER . '</strong>');

      $contents = array('form' => zen_draw_form('orders', FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . '&action=deleteconfirm', 'post', '', true) . zen_draw_hidden_field('oID', $oInfo->orders_id));
//      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br /><br /><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br /><br /><strong>' . ENTRY_ORDER_ID . $oInfo->orders_id . '<br />' . $oInfo->order_total . '<br />' . $oInfo->customers_name . ($oInfo->customers_company != '' ? '<br />' . $oInfo->customers_company : '') . '</strong>');
      $contents[] = array('text' => '<br />' . zen_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $order = new \order($oInfo->orders_id);
        $heading[] = array('text' => '<strong>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . zen_datetime_short($oInfo->date_purchased) . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id) . '" target="_blank">' . zen_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> <a href="' . zen_admin_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" target="_blank">' . zen_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>');
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_MENU_BUTTONS', $oInfo, $contents);

        $contents[] = array('text' => TEXT_DATE_ORDER_CREATED . ' ' . zen_date_short($oInfo->date_purchased));
        $contents[] = array('text' => $oInfo->customers_email_address);
        $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . ' ' . $oInfo->ip_address);
        if (zen_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . zen_date_short($oInfo->last_modified));
        $contents[] = array('text' => TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method);
        $contents[] = array('text' => ENTRY_SHIPPING . ' '  . $oInfo->shipping_method);
        $contents[] = array('text' => TEXT_INFO_ORDER_LANGUAGE . ' ' . $order->language['name']);

// check if order has open gv
        if ($order->info['gv_count_in_queue'] > 0) {
          $goto_gv = '<a href="' . zen_admin_href_link(FILENAME_GV_QUEUE, 'order=' . $oInfo->orders_id) . '">' . zen_image_button('button_gift_queue.gif',IMAGE_GIFT_QUEUE) . '</a>';
          $contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3'));
          $contents[] = array('align' => 'center', 'text' => $goto_gv);
        }

// display customers comment if any exist (comment from first available order status)
        if (sizeof($order->status_history) && trim($order->status_history[0]['comments']) != '') {
          $contents[] = array('align' => 'left', 'text' => '<br />' . TABLE_HEADING_COMMENTS . ': ' . $order->status_history[0]['comments']);
        }

        $contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3'));

        $contents[] = array('text' => TABLE_HEADING_PRODUCTS . ': ' . sizeof($order->products) );
        for ($i=0; $i<sizeof($order->products); $i++) {
          $contents[] = array('text' => $order->products[$i]['qty'] . '&nbsp;x&nbsp;' . $order->products[$i]['name']);

          if (sizeof($order->products[$i]['attributes']) > 0) {
            for ($j=0; $j<sizeof($order->products[$i]['attributes']); $j++) {
              $contents[] = array('text' => '&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value'])) . '</i></nobr>' );
            }
          }
          if ($i > MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING and MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING != 0) {
            $contents[] = array('align' => 'left', 'text' => TEXT_MORE);
            break;
          }
        }

        if (sizeof($order->products) > 0) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
        }
      }
      break;
  }
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_MENU_BUTTONS_END', (isset($oInfo) ? $oInfo : array()), $contents);

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td class="noprint" width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
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
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
