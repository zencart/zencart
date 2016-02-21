<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions copyright COWOA authors see https://www.zen-cart.com/downloads.php?do=file&id=1115
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Modified in v1.6.0 $
 */

  require('includes/application_top.php');

  // unset variable which is sometimes tainted by bad plugins like magneticOne tools
  if (isset($module)) unset($module);

  $currencies = new currencies();

  if (isset($_GET['oID'])) $_GET['oID'] = (int)$_GET['oID'];
  if (isset($_GET['download_reset_on'])) $_GET['download_reset_on'] = (int)$_GET['download_reset_on'];
  if (isset($_GET['download_reset_off'])) $_GET['download_reset_off'] = (int)$_GET['download_reset_off'];

  include(DIR_WS_CLASSES . 'order.php');

  // prepare order-status pulldown list
  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status = $db->Execute("select orders_status_id, orders_status_name
                                 from " . TABLE_ORDERS_STATUS . "
                                 where language_id = '" . (int)$_SESSION['languages_id'] . "' order by orders_status_id");
  while (!$orders_status->EOF) {
    $orders_statuses[] = array('id' => $orders_status->fields['orders_status_id'],
                               'text' => $orders_status->fields['orders_status_name'] . ' [' . $orders_status->fields['orders_status_id'] . ']');
    $orders_status_array[$orders_status->fields['orders_status_id']] = $orders_status->fields['orders_status_name'];
    $orders_status->MoveNext();
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $order_exists = false;
  if (isset($_GET['oID']) && trim($_GET['oID']) == '') unset($_GET['oID']);
  if ($action == 'edit' && !isset($_GET['oID'])) $action = '';

  $oID = FALSE;
  if (isset($_POST['oID'])) {
    $oID = zen_db_prepare_input(trim($_POST['oID']));
  } elseif (isset($_GET['oID'])) {
    $oID = zen_db_prepare_input(trim($_GET['oID']));
  }
  if ($oID) {
    $orders = $db->Execute("select orders_id from " . TABLE_ORDERS . "
                              where orders_id = '" . (int)$oID . "'");
    $order_exists = true;
    if ($orders->RecordCount() <= 0) {
      $order_exists = false;
      if ($action != '') $messageStack->add_session(ERROR_ORDER_DOES_NOT_EXIST . ' ' . $oID, 'error');
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')), 'NONSSL'));
    }
  }

  if (zen_not_null($action) && $order_exists == true) {
    switch ($action) {
      case 'edit':
      // reset single download to on
        if ($_GET['download_reset_on'] > 0) {
          // adjust download_maxdays based on current date
          $check_status = $db->Execute("select customers_name, customers_email_address, orders_status,
                                      date_purchased, is_guest_order from " . TABLE_ORDERS . "
                                      where orders_id = '" . $_GET['oID'] . "'");

          // check for existing product attribute download days and max
          $chk_products_download_query = "SELECT orders_products_id, orders_products_filename, products_prid from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " WHERE orders_products_download_id='" . $_GET['download_reset_on'] . "'";
          $chk_products_download = $db->Execute($chk_products_download_query);

          $chk_products_download_time_query = "SELECT pa.products_attributes_id, pa.products_id, pad.products_attributes_filename, pad.products_attributes_maxdays, pad.products_attributes_maxcount
          from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
          WHERE pa.products_attributes_id = pad.products_attributes_id
          and pad.products_attributes_filename = '" . $db->prepare_input($chk_products_download->fields['orders_products_filename']) . "'
          and pa.products_id = '" . (int)$chk_products_download->fields['products_prid'] . "'";

          $chk_products_download_time = $db->Execute($chk_products_download_time_query);

          if ($chk_products_download_time->EOF) {
            $zc_max_days = (DOWNLOAD_MAX_DAYS == 0 ? 0 : zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + DOWNLOAD_MAX_DAYS);
            $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='" . $zc_max_days . "', download_count='" . DOWNLOAD_MAX_COUNT . "' where orders_id='" . $_GET['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_on'] . "'";
          } else {
            $zc_max_days = ($chk_products_download_time->fields['products_attributes_maxdays'] == 0 ? 0 : zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + $chk_products_download_time->fields['products_attributes_maxdays']);
            $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='" . $zc_max_days . "', download_count='" . $chk_products_download_time->fields['products_attributes_maxcount'] . "' where orders_id='" . $_GET['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_on'] . "'";
          }

          $db->Execute($update_downloads_query);
          unset($_GET['download_reset_on']);

          $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_ON, 'success');
          zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
        }
      // reset single download to off
        if ($_GET['download_reset_off'] > 0) {
          // adjust download_maxdays based on current date
          // *** fix: adjust count not maxdays to cancel download
//          $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='0', download_count='0' where orders_id='" . $_GET['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_off'] . "'";
          $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_count='0' where orders_id='" . $_GET['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_off'] . "'";
          $db->Execute($update_downloads_query);
          unset($_GET['download_reset_off']);

          $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF, 'success');
          zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
        }
      break;
      case 'update_order':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
        }
        $oID = zen_db_prepare_input($_GET['oID']);
        $comments = zen_db_prepare_input($_POST['comments']);
        $status = (int)zen_db_prepare_input($_POST['status']);
        if ($status < 1) break;

        $order_updated = false;
        $check_status = $db->Execute("select customers_name, customers_email_address, orders_status,
                                      date_purchased, is_guest_order from " . TABLE_ORDERS . "
                                      where orders_id = '" . (int)$oID . "'");

        if ( ($check_status->fields['orders_status'] != $status) || zen_not_null($comments)) {
          $db->Execute("update " . TABLE_ORDERS . "
                        set orders_status = '" . zen_db_input($status) . "', last_modified = now()
                        where orders_id = '" . (int)$oID . "'");

          $customer_notified = '0';
          if (isset($_POST['notify']) && ($_POST['notify'] == '1')) {

            $notify_comments = '';
            if (isset($_POST['notify_comments']) && ($_POST['notify_comments'] == 'on') && zen_not_null($comments)) {
              $notify_comments = EMAIL_TEXT_COMMENTS_UPDATE . $comments . "\n\n";
            }

            //send emails

            if (GUEST_ORDER_STATUS == 'true') {
              if ($check_status->fields['is_guest_order'] == 1)  {

                $message =
                    EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n\n" .
                    EMAIL_TEXT_INVOICE_URL . ' ' . zen_catalog_href_link(FILENAME_ORDER_STATUS, 'order_id=' . $oID, 'SSL') . "\n\n" .
                    EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']) . "\n\n" .
                    $notify_comments .
                    EMAIL_TEXT_STATUS_UPDATED . sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[$status] ) .
                    EMAIL_TEXT_STATUS_PLEASE_REPLY;

                $html_msg['EMAIL_CUSTOMERS_NAME']    = $check_status->fields['customers_name'];
                $html_msg['EMAIL_TEXT_ORDER_NUMBER'] = EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID;
                $html_msg['EMAIL_TEXT_INVOICE_URL']  = '<a href="' . zen_catalog_href_link(FILENAME_ORDER_STATUS, 'order_id=' . $oID, 'SSL') .'">'.str_replace(':','','Click here to check the status of your order:').'</a>';
                $html_msg['EMAIL_TEXT_DATE_ORDERED'] = EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']);
                $html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($notify_comments);
                $html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace('\n','', EMAIL_TEXT_STATUS_UPDATED);
                $html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace('\n','', sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[$status] ));
                $html_msg['EMAIL_TEXT_NEW_STATUS'] = $orders_status_array[$status];
                $html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace('\n','', EMAIL_TEXT_STATUS_PLEASE_REPLY);

                zen_mail($check_status->fields['customers_name'], $check_status->fields['customers_email_address'], EMAIL_TEXT_SUBJECT . ' #' . $oID, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');
                $customer_notified = '1';
              }
            }
            if (GUEST_ORDER_STATUS == 'false') {
              if ($check_status->fields['is_guest_order'] == 1)  {

                $htmlInvoiceURL='';
                $htmlInvoiceValue='';
                $message =
                  EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n\n" .
                  EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']) . "\n\n" .
                  $notify_comments .
                  EMAIL_TEXT_STATUS_UPDATED . sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[$status] ) .
                  EMAIL_TEXT_STATUS_PLEASE_REPLY;
                $html_msg['EMAIL_CUSTOMERS_NAME']    = $check_status->fields['customers_name'];
                $html_msg['EMAIL_TEXT_ORDER_NUMBER'] = EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID;
                $html_msg['INTRO_URL_TEXT']        = '';
                $html_msg['INTRO_URL_VALUE']       = '';
                $html_msg['EMAIL_TEXT_DATE_ORDERED'] = EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']);
                $html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($notify_comments);
                $html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace('\n','', EMAIL_TEXT_STATUS_UPDATED);
                $html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace('\n','', sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[$status] ));
                $html_msg['EMAIL_TEXT_NEW_STATUS'] = $orders_status_array[$status];
                $html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace('\n','', EMAIL_TEXT_STATUS_PLEASE_REPLY);

                zen_mail($check_status->fields['customers_name'], $check_status->fields['customers_email_address'], EMAIL_TEXT_SUBJECT . ' #' . $oID, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');
                $customer_notified = '1';
              }
            }
            if ($check_status->fields['is_guest_order'] != 1)  {
              $message =
                EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n\n" .
                EMAIL_TEXT_INVOICE_URL . ' ' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . "\n\n" .
                EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']) . "\n\n" .
                $notify_comments .
                EMAIL_TEXT_STATUS_UPDATED . sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[$status] ) .
                EMAIL_TEXT_STATUS_PLEASE_REPLY;

              $html_msg['EMAIL_CUSTOMERS_NAME']    = $check_status->fields['customers_name'];
              $html_msg['EMAIL_TEXT_ORDER_NUMBER'] = EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID;
              $html_msg['EMAIL_TEXT_INVOICE_URL']  = '<a href="' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') .'">'.str_replace(':','',EMAIL_TEXT_INVOICE_URL).'</a>';
              $html_msg['EMAIL_TEXT_DATE_ORDERED'] = EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($check_status->fields['date_purchased']);
              $html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br($notify_comments);
              $html_msg['EMAIL_TEXT_STATUS_UPDATED'] = str_replace('\n','', EMAIL_TEXT_STATUS_UPDATED);
              $html_msg['EMAIL_TEXT_STATUS_LABEL'] = str_replace('\n','', sprintf(EMAIL_TEXT_STATUS_LABEL, $orders_status_array[$status] ));
              $html_msg['EMAIL_TEXT_NEW_STATUS'] = $orders_status_array[$status];
              $html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = str_replace('\n','', EMAIL_TEXT_STATUS_PLEASE_REPLY);
              $html_msg['EMAIL_PAYPAL_TRANSID'] = '';

              zen_mail($check_status->fields['customers_name'], $check_status->fields['customers_email_address'], EMAIL_TEXT_SUBJECT . ' #' . $oID, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');
              $customer_notified = '1';
            }

            // PayPal Trans ID, if any
            $sql = "select txn_id, parent_txn_id from " . TABLE_PAYPAL . " where order_id = :orderID order by last_modified DESC, date_added DESC, parent_txn_id DESC, paypal_ipn_id DESC ";
            $sql = $db->bindVars($sql, ':orderID', $oID, 'integer');
            $result = $db->Execute($sql);
            if ($result->RecordCount() > 0) {
              $message .= "\n\n" . ' PayPal Trans ID: ' . $result->fields['txn_id'];
              $html_msg['EMAIL_PAYPAL_TRANSID'] = $result->fields['txn_id'];
            }

            //send extra emails
            if (SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS == '1' and SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO != '') {
              zen_mail('', SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO, SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT . ' ' . EMAIL_TEXT_SUBJECT . ' #' . $oID, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status_extra');
            }
          } elseif (isset($_POST['notify']) && ($_POST['notify'] == '-1')) {
            // hide comment
            $customer_notified = '-1';
          }

          $db->Execute("insert into " . TABLE_ORDERS_STATUS_HISTORY . "
                      (orders_id, orders_status_id, date_added, customer_notified, comments)
                      values ('" . (int)$oID . "',
                      '" . zen_db_input($status) . "',
                      now(),
                      '" . zen_db_input($customer_notified) . "',
                      '" . zen_db_input($comments)  . "')");
          $order_updated = true;
        }

        // trigger any appropriate updates which should be sent back to the payment gateway:
        $order = new order((int)$oID);
        if ($order->info['payment_module_code']) {
          if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
            require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
            require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
            $module = new $order->info['payment_module_code'];
            if (method_exists($module, '_doStatusUpdate')) {
              $response = $module->_doStatusUpdate($oID, $status, $comments, $customer_notified, $check_status->fields['orders_status']);
            }
          }
        }

        if ($order_updated == true) {
          if ($status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) {

            // adjust download_maxdays based on current date
            $chk_downloads_query = "SELECT opd.*, op.products_id from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd, " . TABLE_ORDERS_PRODUCTS . " op
                                    WHERE op.orders_id='" . (int)$oID . "'
                                    and opd.orders_products_id = op.orders_products_id";
            $chk_downloads = $db->Execute($chk_downloads_query);

            while (!$chk_downloads->EOF) {
              $chk_products_download_time_query = "SELECT pa.products_attributes_id, pa.products_id, pad.products_attributes_filename, pad.products_attributes_maxdays, pad.products_attributes_maxcount
                                                    from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                                    WHERE pa.products_attributes_id = pad.products_attributes_id
                                                    and pad.products_attributes_filename = '" . $db->prepare_input($chk_downloads->fields['orders_products_filename']) . "'
                                                    and pa.products_id = '" . $chk_downloads->fields['products_id'] . "'";

              $chk_products_download_time = $db->Execute($chk_products_download_time_query);

              if ($chk_products_download_time->EOF) {
                $zc_max_days = (DOWNLOAD_MAX_DAYS == 0 ? 0 : zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + DOWNLOAD_MAX_DAYS);
                $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='" . $zc_max_days . "', download_count='" . DOWNLOAD_MAX_COUNT . "' where orders_id='" . (int)$oID . "' and orders_products_download_id='" . $_GET['download_reset_on'] . "'";
              } else {
                $zc_max_days = ($chk_products_download_time->fields['products_attributes_maxdays'] == 0 ? 0 : zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + $chk_products_download_time->fields['products_attributes_maxdays']);
                $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='" . $zc_max_days . "', download_count='" . $chk_products_download_time->fields['products_attributes_maxcount'] . "' where orders_id='" . (int)$oID . "' and orders_products_download_id='" . $chk_downloads->fields['orders_products_download_id'] . "'";
              }

              $db->Execute($update_downloads_query);

              $chk_downloads->MoveNext();
            }
          }
          $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
          zen_record_admin_activity('Order ' . $oID . ' updated.', 'info');
        } else {
          $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
        }
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')), 'NONSSL'));
        }
        $oID = zen_db_prepare_input($_POST['oID']);

        zen_remove_order($oID, $_POST['restock']);

        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')), 'NONSSL'));
        break;
      case 'delete_cvv':
        $delete_cvv = $db->Execute("update " . TABLE_ORDERS . " set cc_cvv = '" . TEXT_DELETE_CVV_REPLACEMENT . "' where orders_id = '" . (int)$_GET['oID'] . "'");
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
        break;
      case 'mask_cc':
        $result  = $db->Execute("select cc_number from " . TABLE_ORDERS . " where orders_id = '" . (int)$_GET['oID'] . "'");
        $old_num = $result->fields['cc_number'];
        $new_num = substr($old_num, 0, 4) . str_repeat('*', (strlen($old_num) - 8)) . substr($old_num, -4);
        $mask_cc = $db->Execute("update " . TABLE_ORDERS . " set cc_number = '" . $new_num . "' where orders_id = '" . (int)$_GET['oID'] . "'");
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
        break;

      case 'doRefund':
        $order = new order($oID);
        if ($order->info['payment_module_code']) {
          if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
            require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
            require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
            $module = new $order->info['payment_module_code'];
            if (method_exists($module, '_doRefund')) {
              $module->_doRefund($oID);
            }
          }
        }
        zen_record_admin_activity('Order ' . $oID . ' refund processed. See order comments for details.', 'info');
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
        break;
      case 'doAuth':
        $order = new order($oID);
        if ($order->info['payment_module_code']) {
          if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
            require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
            require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
            $module = new $order->info['payment_module_code'];
            if (method_exists($module, '_doAuth')) {
              $module->_doAuth($oID, $order->info['total'], $order->info['currency']);
            }
          }
        }
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
        break;
      case 'doCapture':
        $order = new order($oID);
        if ($order->info['payment_module_code']) {
          if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
            require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
            require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
            $module = new $order->info['payment_module_code'];
            if (method_exists($module, '_doCapt')) {
              $module->_doCapt($oID, 'Complete', $order->info['total'], $order->info['currency']);
            }
          }
        }
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
        break;
      case 'doVoid':
        $order = new order($oID);
        if ($order->info['payment_module_code']) {
          if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
            require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
            require_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
            $module = new $order->info['payment_module_code'];
            if (method_exists($module, '_doVoid')) {
              $module->_doVoid($oID);
            }
          }
        }
        zen_record_admin_activity('Order ' . $oID . ' void processed. See order comments for details.', 'info');
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'NONSSL'));
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
<table border="0" width="100%" cellspacing="2" cellpadding="2">
<!-- body_text //-->

<?php if ($action == '') { ?>
<!-- search -->
  <tr>
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
         <tr><?php echo zen_draw_form('search', FILENAME_ORDERS, '', 'get', '', true); ?>
            <td width="65%" class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td colspan="2" class="smallText" align="right">
<?php
// show reset search
  if ((isset($_GET['search']) && zen_not_null($_GET['search'])) or $_GET['cID'] !='') {
    echo '<a href="' . zen_href_link(FILENAME_ORDERS, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a><br />';
  }
?>
<?php
  echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search') . zen_hide_session_id();
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
  }
?>
            </td>
          </form>


         <?php echo zen_draw_form('search_orders_products', FILENAME_ORDERS, '', 'get', '', true); ?>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td colspan="2" class="smallText" align="right">
<?php
// show reset search orders_products
  if ((isset($_GET['search_orders_products']) && zen_not_null($_GET['search_orders_products'])) or $_GET['cID'] !='') {
    echo '<a href="' . zen_href_link(FILENAME_ORDERS, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a><br />';
  }
?>
<?php
  echo HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS . ' ' . zen_draw_input_field('search_orders_products') . zen_hide_session_id();
  if (isset($_GET['search_orders_products']) && zen_not_null($_GET['search_orders_products'])) {
    $keywords_orders_products = zen_db_input(zen_db_prepare_input($_GET['search_orders_products']));
    echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER_ORDERS_PRODUCTS . zen_db_prepare_input($keywords_orders_products);
  }
?>
            </td>
          </form>

          </tr>
        </table></td>
      </tr>
<!-- search -->
<?php } ?>


<?php
  if (($action == 'edit') && ($order_exists == true)) {
    $order = new order($oID);
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_EDIT_BEGIN', $oID, $order);
    if ($order->info['payment_module_code']) {
      if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
        require(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
        require(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order->info['payment_module_code'] . '.php');
        $module = new $order->info['payment_module_code'];
//        echo $module->admin_notification($oID);
      }
    }
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="pageHeading" align="right"><?php echo '<a href="javascript:history.back()">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
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
                <td class="main" valign="top"><strong><?php echo ENTRY_CUSTOMER; ?></strong></td>
                <td class="main"><?php echo zen_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td class="main"><?php echo $order->customer['telephone']; ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_EMAIL_ADDRESS; ?></strong></td>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo TEXT_INFO_IP_ADDRESS; ?></strong></td>
                <td class="main"><?php echo $order->info['ip_address']; ?></td>
              </tr>
              <tr>
                <td class="main"><strong><?php echo ENTRY_CUSTOMER; ?></strong></td>
                <td class="main"><?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'search=' . $order->customer['email_address'], 'SSL') . '" . >' . TEXT_CUSTOMER_LOOKUP . '</a>'; ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><strong><?php echo ENTRY_SHIPPING_ADDRESS; ?></strong></td>
                <td class="main"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><strong><?php echo ENTRY_BILLING_ADDRESS; ?></strong></td>
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
            <td class="main"><?php echo $order->info['cc_number'] . (zen_not_null($order->info['cc_number']) && !strstr($order->info['cc_number'],'X') && !strstr($order->info['cc_number'],'********') ? '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_ORDERS, '&action=mask_cc&oID=' . $oID, 'NONSSL') . '" class="noprint">' . TEXT_MASK_CC_NUMBER . '</a>' : ''); ?></td>
          </tr>
<?php if (zen_not_null($order->info['cc_cvv'])) { ?>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_CVV; ?></td>
            <td class="main"><?php echo $order->info['cc_cvv'] . (zen_not_null($order->info['cc_cvv']) && !strstr($order->info['cc_cvv'],TEXT_DELETE_CVV_REPLACEMENT) ? '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_ORDERS, '&action=delete_cvv&oID=' . $oID, 'NONSSL') . '" class="noprint">' . TEXT_DELETE_CVV_FROM_DATABASE . '</a>' : ''); ?></td>
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
          echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(htmlspecialchars($order->products[$i]['attributes'][$j]['value'], ENT_COMPAT, CHARSET, FALSE));
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
    $orders_history = $db->Execute("select orders_status_id, date_added, customer_notified, comments
                                    from " . TABLE_ORDERS_STATUS_HISTORY . "
                                    where orders_id = '" . zen_db_input($oID) . "'
                                    order by date_added");

    if ($orders_history->RecordCount() > 0) {
      while (!$orders_history->EOF) {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" align="center">' . zen_datetime_short($orders_history->fields['date_added']) . '</td>' . "\n" .
             '            <td class="smallText" align="center">';
        if ($orders_history->fields['customer_notified'] == '1') {
          echo zen_image(DIR_WS_ICONS . 'tick.gif', TEXT_YES) . "</td>\n";
        } else if ($orders_history->fields['customer_notified'] == '-1') {
          echo zen_image(DIR_WS_ICONS . 'locked.gif', TEXT_HIDDEN) . "</td>\n";
        } else {
          echo zen_image(DIR_WS_ICONS . 'unlocked.gif', TEXT_VISIBLE) . "</td>\n";
        }
        echo '            <td class="smallText">' . $orders_status_array[$orders_history->fields['orders_status_id']] . '</td>' . "\n";
        echo '            <td class="smallText">' . nl2br(htmlspecialchars($orders_history->fields['comments'], ENT_COMPAT, CHARSET, TRUE)) . '&nbsp;</td>' . "\n" .
             '          </tr>' . "\n";
        $orders_history->MoveNext();
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
        <td><table border="0" cellspacing="0" cellpadding="2" class="noprint">
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><strong><?php echo ENTRY_STATUS; ?></strong> <?php echo zen_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></td>
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
        <td colspan="2" align="right" class="noprint"><?php echo '<a href="' . zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . zen_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> <a href="' . zen_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . zen_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> <a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('action'))) . '">' . zen_image_button('button_orders.gif', IMAGE_ORDERS) . '</a>'; ?></td>
      </tr>
<?php
// check if order has open gv
        $gv_check = $db->Execute("select order_id, unique_id
                                  from " . TABLE_COUPON_GV_QUEUE ."
                                  where order_id = '" . $_GET['oID'] . "' and release_flag='N' limit 1");
        if ($gv_check->RecordCount() > 0) {
          $goto_gv = '<a href="' . zen_href_link(FILENAME_GV_QUEUE, 'order=' . $_GET['oID']) . '">' . zen_image_button('button_gift_queue.gif',IMAGE_GIFT_QUEUE) . '</a>';
          echo '      <tr><td align="right"><table width="225"><tr>';
          echo '        <td align="center">';
          echo $goto_gv . '&nbsp;&nbsp;';
          echo '        </td>';
          echo '      </tr></table></td></tr>';
        }
?>
<?php
  } else {
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr><?php echo zen_draw_form('orders', FILENAME_ORDERS, '', 'get', '', true); ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . zen_draw_input_field('oID', '', 'size="12"') . zen_draw_hidden_field('action', 'edit') . zen_hide_session_id(); ?></td>
              </form></tr>
              <tr><?php echo zen_draw_form('status', FILENAME_ORDERS, '', 'get', '', true); ?>
                <td class="smallText" align="right">
                  <?php
                    echo HEADING_TITLE_STATUS . ' ' . zen_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), $_GET['status'], 'onChange="this.form.submit();"');
                    echo zen_hide_session_id();
                  ?>
                </td>
              </form></tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="smallText"><?php echo TEXT_LEGEND . ' ' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10) . ' ' . TEXT_BILLING_SHIPPING_MISMATCH; ?>
          </td>
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
<?php
// Sort Listing
          switch ($_GET['list_order']) {
              case "id-asc":
              $disp_order = "c.customers_id";
              break;
              case "firstname":
              $disp_order = "c.customers_firstname";
              break;
              case "firstname-desc":
              $disp_order = "c.customers_firstname DESC";
              break;
              case "lastname":
              $disp_order = "c.customers_lastname, c.customers_firstname";
              break;
              case "lastname-desc":
              $disp_order = "c.customers_lastname DESC, c.customers_firstname";
              break;
              case "company":
              $disp_order = "a.entry_company";
              break;
              case "company-desc":
              $disp_order = "a.entry_company DESC";
              break;
              default:
              $disp_order = "c.customers_id DESC";
          }
?>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                <td class="dataTableHeadingContent" align="left" width="50"><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_CUSTOMER_COMMENTS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>

<?php
// Only one or the other search
// create search_orders_products filter
  $search = '';
  $new_table = '';
  $new_fields = '';
  if (isset($_GET['search_orders_products']) && zen_not_null($_GET['search_orders_products'])) {
    $new_fields = '';
    $search_distinct = ' distinct ';
    $new_table = " left join " . TABLE_ORDERS_PRODUCTS . " op on (op.orders_id = o.orders_id) ";
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search_orders_products']));
    $search = " and (op.products_model like '%" . $keywords . "%' or op.products_name like '" . $keywords . "%')";
    if (substr(strtoupper($_GET['search_orders_products']), 0, 3) == 'ID:') {
      $keywords = TRIM(substr($_GET['search_orders_products'], 3));
      $search = " and op.products_id ='" . (int)$keywords . "'";
    }
  } else {
?>
<?php
// create search filter
  $search = '';
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $search_distinct = ' ';
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    $search = " and (o.customers_city like '%" . $keywords . "%' or o.customers_postcode like '%" . $keywords . "%' or o.date_purchased like '%" . $keywords . "%' or o.billing_name like '%" . $keywords . "%' or o.billing_company like '%" . $keywords . "%' or o.billing_street_address like '%" . $keywords . "%' or o.delivery_city like '%" . $keywords . "%' or o.delivery_postcode like '%" . $keywords . "%' or o.delivery_name like '%" . $keywords . "%' or o.delivery_company like '%" . $keywords . "%' or o.delivery_street_address like '%" . $keywords . "%' or o.billing_city like '%" . $keywords . "%' or o.billing_postcode like '%" . $keywords . "%' or o.customers_email_address like '%" . $keywords . "%' or o.customers_name like '%" . $keywords . "%' or o.customers_company like '%" . $keywords . "%' or o.customers_street_address  like '%" . $keywords . "%' or o.customers_telephone like '%" . $keywords . "%' or o.ip_address  like '%" . $keywords . "%')";
    $new_table = '';
//    $new_fields = ", o.customers_company, o.customers_email_address, o.customers_street_address, o.delivery_company, o.delivery_name, o.delivery_street_address, o.billing_company, o.billing_name, o.billing_street_address, o.payment_module_code, o.shipping_module_code, o.ip_address ";
  }
} // eof: search orders or orders_products
    $new_fields = ", o.customers_company, o.customers_email_address, o.customers_street_address, o.delivery_company, o.delivery_name, o.delivery_street_address, o.billing_company, o.billing_name, o.billing_street_address, o.payment_module_code, o.shipping_module_code, o.ip_address ";
?>
<?php
    if (isset($_GET['cID'])) {
      $cID = zen_db_prepare_input($_GET['cID']);
      $orders_query_raw =   "select o.orders_id, o.customers_id, o.customers_name, o.payment_method, o.shipping_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total" .
                            $new_fields . "
                            from (" . TABLE_ORDERS_STATUS . " s, " .
                            TABLE_ORDERS . " o " .
                            $new_table . ")
                            left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and ot.class = 'ot_total') " . "
                            where o.customers_id = '" . (int)$cID . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$_SESSION['languages_id'] . "' order by orders_id DESC";

//echo '<BR><BR>I SEE A: ' . $orders_query_raw . '<BR><BR>';

    } elseif ($_GET['status'] != '') {
      $status = zen_db_prepare_input($_GET['status']);
      $orders_query_raw = "select o.orders_id, o.customers_id, o.customers_name, o.payment_method, o.shipping_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total" .
                          $new_fields . "
                          from (" . TABLE_ORDERS_STATUS . " s, " .
                          TABLE_ORDERS . " o " .
                          $new_table . ")
                          left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and ot.class = 'ot_total') " . "
                          where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$_SESSION['languages_id'] . "' and s.orders_status_id = '" . (int)$status . "'  " .
                          $search . " order by o.orders_id DESC";

//echo '<BR><BR>I SEE B: ' . $orders_query_raw . '<BR><BR>';

    } else {
      $orders_query_raw = "select " . $search_distinct . " o.orders_id, o.customers_id, o.customers_name, o.payment_method, o.shipping_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total" .
                          $new_fields . "
                          from (" . TABLE_ORDERS_STATUS . " s, " .
                          TABLE_ORDERS . " o " .
                          $new_table . ")
                          left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and ot.class = 'ot_total') " . "
                          where (o.orders_status = s.orders_status_id and s.language_id = '" . (int)$_SESSION['languages_id'] . "')  " .
                          $search . " order by o.orders_id DESC";

//echo '<BR><BR>I SEE C: ' . $orders_query_raw . '<BR><BR>';

    }

// Split Page
// reset page when page is unknown
if (($_GET['page'] == '' or $_GET['page'] <= 1) and $_GET['oID'] != '') {
  $check_page = $db->Execute($orders_query_raw);
  $check_count=1;
  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_ORDERS) {
    while (!$check_page->EOF) {
      if ($check_page->fields['orders_id'] == $_GET['oID']) {
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
    if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $orders->fields['orders_id']))) && !isset($oInfo)) {
        $oInfo = new objectInfo($orders->fields);
      }

      if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID')) . 'oID=' . $orders->fields['orders_id'], 'NONSSL') . '\'">' . "\n";
      }

      $show_difference = '';
      if ((strtoupper($orders->fields['delivery_name']) != strtoupper($orders->fields['billing_name']) and trim($orders->fields['delivery_name']) != '')) {
        $show_difference = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10) . '&nbsp;';
      }
      if ((strtoupper($orders->fields['delivery_street_address']) != strtoupper($orders->fields['billing_street_address']) and trim($orders->fields['delivery_street_address']) != '')) {
        $show_difference = zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', TEXT_BILLING_SHIPPING_MISMATCH, 10, 10) . '&nbsp;';
      }
      $show_payment_type = $orders->fields['payment_module_code'] . '<br />' . $orders->fields['shipping_module_code'];
?>
                <td class="dataTableContent" align="right"><?php echo $show_difference . $orders->fields['orders_id']; ?></td>
                <td class="dataTableContent" align="left" width="50"><?php echo $show_payment_type; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $orders->fields['customers_id'], 'NONSSL') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW . ' ' . TABLE_HEADING_CUSTOMERS) . '</a>&nbsp;' . $orders->fields['customers_name'] . ($orders->fields['customers_company'] != '' ? '<br />' . $orders->fields['customers_company'] : ''); ?></td>
                <td class="dataTableContent" align="right"><?php echo strip_tags($orders->fields['order_total']); ?></td>
                <td class="dataTableContent" align="center"><?php echo zen_datetime_short($orders->fields['date_purchased']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $orders->fields['orders_status_name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo (zen_get_orders_comments($orders->fields['orders_id']) == '' ? '' : zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', TEXT_COMMENTS_YES, 16, 16)); ?></td>

                <td class="dataTableContent" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders->fields['orders_id'] . '&action=edit', 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?><?php if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID')) . 'oID=' . $orders->fields['orders_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
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
                        echo '<a href="' . zen_href_link(FILENAME_ORDERS, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>';
                        if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
                          $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
                          echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
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
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id, 'NONSSL') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<strong>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . zen_datetime_short($oInfo->date_purchased) . '</strong>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete', 'NONSSL') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">' . zen_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> <a href="' . zen_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">' . zen_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>');
        $contents[] = array('text' => '<br />' . TEXT_DATE_ORDER_CREATED . ' ' . zen_date_short($oInfo->date_purchased));
        $contents[] = array('text' => '<br />' . $oInfo->customers_email_address);
        $contents[] = array('text' => TEXT_INFO_IP_ADDRESS . ' ' . $oInfo->ip_address);
        if (zen_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . zen_date_short($oInfo->last_modified));
        $contents[] = array('text' => '<br />' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method);
        $contents[] = array('text' => '<br />' . ENTRY_SHIPPING . ' '  . $oInfo->shipping_method);

// check if order has open gv
        $gv_check = $db->Execute("select order_id, unique_id
                                  from " . TABLE_COUPON_GV_QUEUE ."
                                  where order_id = '" . $oInfo->orders_id . "' and release_flag='N' limit 1");
        if ($gv_check->RecordCount() > 0) {
          $goto_gv = '<a href="' . zen_href_link(FILENAME_GV_QUEUE, 'order=' . $oInfo->orders_id) . '">' . zen_image_button('button_gift_queue.gif',IMAGE_GIFT_QUEUE) . '</a>';
          $contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3'));
          $contents[] = array('align' => 'center', 'text' => $goto_gv);
        }
      }

// display customers comment if any exist (comment from first available order status)
      if ($orders_comment = zen_get_orders_comments($oInfo->orders_id)) {
        $contents[] = array('align' => 'left', 'text' => '<br />' . TABLE_HEADING_COMMENTS . ': ' . $orders_comment);
      }

      $contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3'));
      $order = new order($oInfo->orders_id);
      $contents[] = array('text' => TABLE_HEADING_PRODUCTS . ': ' . sizeof($order->products) );
      for ($i=0; $i<sizeof($order->products); $i++) {
        $contents[] = array('text' => $order->products[$i]['qty'] . '&nbsp;x&nbsp;' . $order->products[$i]['name']);

        if (sizeof($order->products[$i]['attributes']) > 0) {
          for ($j=0; $j<sizeof($order->products[$i]['attributes']); $j++) {
            $contents[] = array('text' => '&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(htmlspecialchars($order->products[$i]['attributes'][$j]['value'], ENT_COMPAT, CHARSET, FALSE)) . '</i></nobr>' );
          }
        }
        if ($i > MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING and MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING != 0) {
          $contents[] = array('align' => 'left', 'text' => TEXT_MORE);
          break;
        }
      }

      if (sizeof($order->products) > 0) {
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
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
