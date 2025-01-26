<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2024 Oct 07 Modified in v2.1.0 $
 */
require('includes/application_top.php');

// unset variable which is sometimes tainted by bad plugins like magneticOne tools
if (isset($module)) {
  unset($module);
}

$whois_provider_url = 'https://ipdata.co/%s?utm_source=zen_cart';
//$whois_provider_url = 'https://whois.domaintools.com/%s';

// Override instructions in:
// https://docs.zen-cart.com/user/admin/site_specific_overrides/
$quick_view_popover_enabled = $quick_view_popover_enabled ?? false;
$includeAttributesInProductDetailRows = $includeAttributesInProductDetailRows ?? true;
$show_product_tax = $show_product_tax ?? true;
$show_zone_info = $show_zone_info ?? true;

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

if (isset($_GET['download_reset_on'])) {
  $_GET['download_reset_on'] = (int)$_GET['download_reset_on'];
}
if (isset($_GET['download_reset_off'])) {
  $_GET['download_reset_off'] = (int)$_GET['download_reset_off'];
}
if (!isset($_GET['statusFilterSelect'])) $_GET['statusFilterSelect'] = '';
if (!isset($_GET['list_order'])) $_GET['list_order'] = '';
if (!isset($_GET['page'])) $_GET['page'] = '';

include DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php';
$show_including_tax = (DISPLAY_PRICE_WITH_TAX === 'true');
// prepare order-status look-up list
$ordersStatus = zen_getOrdersStatuses();
$orders_status_array = $ordersStatus['orders_status_array'];

$action = ($_GET['action'] ?? '');
$order_exists = false;
if (isset($_GET['oID']) && empty($_GET['oID'])) {
  unset($_GET['oID']);
}
if (isset($_GET['oID'])) {
  $_GET['oID'] = (int)$_GET['oID'];
}
if ($action === 'edit' && !isset($_GET['oID'])) {
  $action = '';
}

$oID = false;
if (isset($_POST['oID'])) {
  $oID = (int)$_POST['oID'];
} elseif (isset($_GET['oID'])) {
  $oID = (int)$_GET['oID'];
}
if ($oID) {
  $orders = $db->Execute("SELECT orders_id
                          FROM " . TABLE_ORDERS . "
                          WHERE orders_id = " . $oID, 1);
  $order_exists = true;
  if ($orders->RecordCount() <= 0) {
    $order_exists = false;
    if ($action !== '') {
      $messageStack->add_session(ERROR_ORDER_DOES_NOT_EXIST . ' ' . $oID, 'error');
    }
    zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['oID', 'action']), 'NONSSL'));
  }
}


if (!empty($oID) && !empty($action)) {
  $zco_notifier->notify('NOTIFY_ADMIN_ORDER_PREDISPLAY_HOOK', $oID, $action);
}

// -----
// Determine which of the 'Notify Customer' radio buttons should be selected initially,
// based on configuration setting in 'My Store'.  Set a default, just in case that configuration
// setting isn't set!
//
if (!defined('NOTIFY_CUSTOMER_DEFAULT')) define('NOTIFY_CUSTOMER_DEFAULT', '1');
switch (NOTIFY_CUSTOMER_DEFAULT) {
    case '0':
        $notify_email = false;
        $notify_no_email = true;
        $notify_hidden = false;
        break;
    case '-1':
        $notify_email = false;
        $notify_no_email = false;
        $notify_hidden = true;
        break;
    default:
        $notify_email = true;
        $notify_no_email = false;
        $notify_hidden = false;
        break;
}

if (!empty($action) && $order_exists === true) {
    $order = new order($oID);
  switch ($action) {
    case 'download':

      $fileName = basename($_GET['filename']);
      $file_extension = strtolower(substr(strrchr($fileName, '.'), 1));
        switch ($file_extension) {
            case 'ai':
            case 'eps':
                $content = 'application/postscript';
                break;
            case 'bmp':
                $content = 'image/bmp';
                break;
            case 'cdr':
                $content = 'application/cdr';
                break;
            case 'csv':
                $content = 'text/csv';
                break;
            case 'gif':
                $content = 'image/gif';
                break;
            case 'jpg':
            case 'jpeg':
                $content = 'image/jpeg';
                break;
            case 'numbers':
            case 'xls':
                $content = 'application/vnd.ms-excel';
                break;
            case 'pdf':
                $content = 'application/pdf';
                break;
            case 'png':
                $content = 'image/png';
                break;
            case 'tif':
            case 'tiff':
                $content = 'image/tiff';
                break;
            case 'zip':
                $content = 'application/zip';
                break;
            default:
                $messageStack->add_session(sprintf(TEXT_EXTENSION_NOT_UNDERSTOOD, $file_extension), 'error');
                zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['download', 'action']) . 'action=edit', 'NONSSL'));
        }
      $fs_path = DIR_FS_CATALOG_IMAGES . 'uploads/' . $fileName;
      if (!file_exists($fs_path)) {
        $messageStack->add_session(TEXT_FILE_NOT_FOUND, 'error');
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['download', 'action']) . 'action=edit'));
      }
      header('Content-type: ' . $content);
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      header('Content-Transfer-Encoding: binary');
      header('Cache-Control: no-cache, must-revalidate');
      header("Expires: Mon, 22 Jan 2002 00:00:00 GMT");
      readfile($fs_path);
      exit();

    case 'edit':
      // reset single download to on
      if (!empty($_GET['download_reset_on'])) {
        // adjust download_maxdays based on current date

        // check for existing product attribute download days and max
        $chk_products_download_query = "SELECT orders_products_id, orders_products_filename, products_prid
                                        FROM " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                        WHERE orders_products_download_id = " . (int)$_GET['download_reset_on'];
        $chk_products_download = $db->Execute($chk_products_download_query);

        $chk_products_download_time_query = "SELECT pa.products_attributes_id, pa.products_id,
                                                    pad.products_attributes_filename, pad.products_attributes_maxdays, pad.products_attributes_maxcount
                                             FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                                   " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                             WHERE pa.products_attributes_id = pad.products_attributes_id
                                             AND pad.products_attributes_filename = '" . $db->prepare_input($chk_products_download->fields['orders_products_filename']) . "'
                                             AND pa.products_id = " . (int)$chk_products_download->fields['products_prid'];

        $chk_products_download_time = $db->Execute($chk_products_download_time_query);

        if ($chk_products_download_time->EOF) {
          $zc_max_days = DOWNLOAD_MAX_DAYS == 0 ? 0 : zen_date_diff($order->info['date_purchased'], date('Y-m-d H:i:s')) + (int)DOWNLOAD_MAX_DAYS;
          $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                     SET download_maxdays = " . (int)$zc_max_days . ",
                                         download_count = " . (int)DOWNLOAD_MAX_COUNT . "
                                     WHERE orders_id = " . (int)$_GET['oID'] . "
                                     AND orders_products_download_id = " . (int)$_GET['download_reset_on'];
        } else {
          $zc_max_days = ($chk_products_download_time->fields['products_attributes_maxdays'] == 0 ? 0 : zen_date_diff($order->info['date_purchased'], date('Y-m-d H:i:s')) + $chk_products_download_time->fields['products_attributes_maxdays']);
          $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                     SET download_maxdays = " . (int)$zc_max_days . ",
                                         download_count = " . (int)$chk_products_download_time->fields['products_attributes_maxcount'] . "
                                     WHERE orders_id = " . (int)$_GET['oID'] . "
                                     AND orders_products_download_id = " . (int)$_GET['download_reset_on'];
        }

        $db->Execute($update_downloads_query);
        unset($_GET['download_reset_on']);

        $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_ON, 'success');
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=edit', 'NONSSL'));
      }
      // reset single download to off
      if (!empty($_GET['download_reset_off'])) {
        // adjust download_maxdays based on current date
        // *** fix: adjust count not maxdays to cancel download
//          $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='0', download_count='0' where orders_id='" . $_GET['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_off'] . "'";
        $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                   SET download_count = 0
                                   WHERE orders_id = " . (int)$_GET['oID'] . "
                                   AND orders_products_download_id = " . (int)$_GET['download_reset_off'];
        $db->Execute($update_downloads_query);
        unset($_GET['download_reset_off']);

        $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF, 'success');
        zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=edit', 'NONSSL'));
      }
      break;
    case 'update_order':
      $comments = !empty($_POST['comments']) ? zen_db_prepare_input($_POST['comments']) : '';
      $admin_language = zen_db_prepare_input($_POST['admin_language'] ?? $_SESSION['languages_code']);
      $status = (int)$_POST['statusUpdateSelect'];
      if ($status < 1) {
         break;
      }

      $email_include_message = (isset($_POST['notify_comments']) && $_POST['notify_comments'] === 'on');
      $customer_notified = isset($_POST['notify']) ? (int)$_POST['notify'] : 0;

      // -----
      // Give an observer the opportunity to add to the to-be-recorded comments and/or
      // capture any posted values that it has inserted into the order-update form.
      //
      $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_UPDATE_ORDER_START', $oID);

      $order_updated = false;
      $status_updated = zen_update_orders_history($oID, $comments, null, $status, $customer_notified, $email_include_message);
      $order_updated = ($status_updated > 0);

      // trigger any appropriate updates which should be sent back to the payment gateway:
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          zen_include_language_file($order->info['payment_module_code'] . '.php', '/modules/payment/','inline');
          $module = new $order->info['payment_module_code']();
          if (method_exists($module, '_doStatusUpdate')) {
            $response = $module->_doStatusUpdate($oID, $status, $comments, $customer_notified, $order->info['orders_status']);
          }
        }
      }

      if ($order_updated === true) {
        if ($status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) {

          // adjust download_maxdays based on current date
          $chk_downloads_query = "SELECT opd.*, op.products_id
                                  FROM " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd,
                                       " . TABLE_ORDERS_PRODUCTS . " op
                                  WHERE op.orders_id = " . $oID . "
                                  AND opd.orders_products_id = op.orders_products_id";
          $chk_downloads = $db->Execute($chk_downloads_query);

          foreach ($chk_downloads as $chk_download) {
            $chk_products_download_time_query = "SELECT pa.products_attributes_id, pa.products_id,
                                                        pad.products_attributes_filename, pad.products_attributes_maxdays, pad.products_attributes_maxcount
                                                 FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                                 WHERE pa.products_attributes_id = pad.products_attributes_id
                                                 AND pad.products_attributes_filename = '" . $db->prepare_input($chk_download['orders_products_filename']) . "'
                                                 AND pa.products_id = " . (int)$chk_download['products_id'];

            $chk_products_download_time = $db->Execute($chk_products_download_time_query);

            if ($chk_products_download_time->EOF) {
              $zc_max_days = DOWNLOAD_MAX_DAYS == 0 ? 0 : zen_date_diff($order->info['date_purchased'], date('Y-m-d H:i:s')) + (int)DOWNLOAD_MAX_DAYS;
              $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                         SET download_maxdays = " . (int)$zc_max_days . ",
                                             download_count = " . (int)DOWNLOAD_MAX_COUNT . "
                                         WHERE orders_id = " . $oID . "
                                         AND orders_products_download_id = " . (int)$_GET['download_reset_on'];
            } else {
              $zc_max_days = ($chk_products_download_time->fields['products_attributes_maxdays'] == 0 ? 0 : zen_date_diff($order->info['date_purchased'], date('Y-m-d H:i:s')) + $chk_products_download_time->fields['products_attributes_maxdays']);
              $update_downloads_query = "UPDATE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . "
                                         SET download_maxdays = " . (int)$zc_max_days . ",
                                             download_count = " . (int)$chk_products_download_time->fields['products_attributes_maxcount'] . "
                                         WHERE orders_id = " . $oID . "
                                         AND orders_products_download_id = " . (int)$chk_download['orders_products_download_id'];
            }

            $db->Execute($update_downloads_query);
          }
        }
        $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
          if ($customer_notified === 1) {
              $messageStack->add_session(sprintf(SUCCESS_EMAIL_SENT, ($admin_language !== $_SESSION['languages_code'] ? '(' . strtoupper($_SESSION['languages_code']) . ') ' : '')), 'success'); // show an email sent confirmation message, with a language indicator if the order/email language was different to the admin user language
          }
        zen_record_admin_activity('Order ' . $oID . ' updated.', 'info');
      } else {
        $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
      }

        $redirect = zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action', 'language']) . ($admin_language !== $_SESSION['languages_code'] ? '&language=' . $admin_language : ''), 'NONSSL');
        if (isset($_POST['camefrom']) && $_POST['camefrom'] === 'orderEdit') {
            $redirect .= '&action=edit';
        }
        zen_redirect($redirect);
      break;

    case 'deleteconfirm':
      zen_remove_order($oID, (!empty($_POST['restock']) && $_POST['restock'] === 'on'));

      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['oID', 'action']), 'NONSSL'));
      break;
    case 'delete_cvv':
      $delete_cvv = $db->Execute("UPDATE " . TABLE_ORDERS . "
                                  SET cc_cvv = '" . TEXT_DELETE_CVV_REPLACEMENT . "'
                                  WHERE orders_id = " . (int)$_GET['oID']);
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=edit', 'NONSSL'));
      break;
    case 'mask_cc':
      $old_num = $order->info['cc_number'];
      $new_num = substr($old_num, 0, 4) . str_repeat('*', (strlen($old_num) - 8)) . substr($old_num, -4);
      $mask_cc = $db->Execute("UPDATE " . TABLE_ORDERS . "
                               SET cc_number = '" . $new_num . "'
                               WHERE orders_id = " . (int)$_GET['oID']);
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=edit', 'NONSSL'));
      break;

    case 'doRefund':
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          zen_include_language_file($order->info['payment_module_code'] . '.php', '/modules/payment/','inline');
          $module = new $order->info['payment_module_code']();
          if (method_exists($module, '_doRefund')) {
            $module->_doRefund($oID);
          }
        }
      }
      zen_record_admin_activity('Order ' . $oID . ' refund processed. See order comments for details.', 'info');
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=edit', 'NONSSL'));
      break;
    case 'doAuth':
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          zen_include_language_file($order->info['payment_module_code'] . '.php', '/modules/payment/','inline');
          $module = new $order->info['payment_module_code']();
          if (method_exists($module, '_doAuth')) {
            $module->_doAuth($oID, $order->info['total'], $order->info['currency']);
          }
        }
      }
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=edit', 'NONSSL'));
      break;
    case 'doCapture':
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          zen_include_language_file($order->info['payment_module_code'] . '.php', '/modules/payment/','inline');
          $module = new $order->info['payment_module_code']();
          if (method_exists($module, '_doCapt')) {
            $module->_doCapt($oID, 'Complete', $order->info['total'], $order->info['currency']);
          }
        }
      }
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=edit', 'NONSSL'));
      break;
    case 'doVoid':
      if ($order->info['payment_module_code']) {
        if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
          require_once(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
          zen_include_language_file($order->info['payment_module_code'] . '.php', '/modules/payment/','inline');
          $module = new $order->info['payment_module_code']();
          if (method_exists($module, '_doVoid')) {
            $module->_doVoid($oID);
          }
        }
      }
      zen_record_admin_activity('Order ' . $oID . ' void processed. See order comments for details.', 'info');
      zen_redirect(zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['action']) . 'action=edit', 'NONSSL'));
      break;
      default:
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_DEFAULT_ACTION', $oID, $order, $action);
        break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <link rel="stylesheet" media="print" href="includes/css/stylesheet_print.css">
    <script>
      function couponpopupWindow(url) {
          window.open(url, 'popupWindow', 'toolbar=no,location=no,directories=no,status=no,menu bar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
      }
    </script>
      <?php
      if ($action === 'edit' && $editor_handler !== '') {
          include ($editor_handler);
      }
      ?>
  </head>
  <body>
    <!-- header //-->
    <?php
    require(DIR_WS_INCLUDES . 'header.php');
    ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <!-- body_text //-->
<?php
    // -----
    // Give an observer the chance to (a) change the page's title and (b) insert additional content just below (or after) the level-1 heading.
    //
    // Notes:
    // - The 'oID' value is (bool)false when the orders' listing is being displayed.
    // - Any added content is printed **only** if an observer sets $print_extra_top_content to (bool)true.
    //
    $heading_title = ($action === 'edit' && $order_exists) ? sprintf(HEADING_TITLE_DETAILS, (int)$oID) : HEADING_TITLE;
    $extra_top_content = '';
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_HEADING_TITLE', ['action' => $action, 'order_exists' => $order_exists, 'oID' => $oID], $heading_title, $extra_top_content);
?>
      <h1><?php echo $heading_title; ?></h1>
<?php
    echo $extra_top_content;
?>

      <?php $order_list_button = '<a role="button" class="btn btn-default" href="' . zen_href_link(FILENAME_ORDERS) . '"><i class="fa-solid fa-table-list" aria-hidden="true">&nbsp;</i> ' . BUTTON_TO_LIST . '</a>'; ?>
      <?php if ($action === '') { ?>
        <!-- search -->

        <div class="row noprint">
          <div class="form-inline">
            <div class="form-group col-xs-6 col-sm-3 col-md-3 col-lg-3">
                <?php
                echo zen_draw_form('search', FILENAME_ORDERS, '', 'get', '', true);
                echo zen_draw_label(HEADING_TITLE_SEARCH_ALL, 'searchAll', 'class="sr-only"');
                $placeholder = zen_output_string_protected(isset($_GET['search']) && $_GET['search'] != '' ? $_GET['search'] : HEADING_TITLE_SEARCH_ALL);
                ?>
              <div class="input-group">
                  <?php
                  echo zen_draw_input_field('search', '', 'id="searchAll" class="form-control" placeholder="' . $placeholder . '"');
                  if (!empty($_GET['search']) || !empty($_GET['cID'])) {
                    ?>
                  <a class="btn btn-info input-group-addon" role="button" aria-label="<?php echo TEXT_RESET_FILTER; ?>" href="<?php echo zen_href_link(FILENAME_ORDERS); ?>">
                    <i class="fa-solid fa-xmark" aria-hidden="true">&nbsp;</i>
                  </a>
                <?php } ?>
              </div>
              <?php echo '</form>'; ?>
            </div>
            <div class="form-group col-xs-6 col-sm-3 col-md-3 col-lg-3">
                <?php
                echo zen_draw_form('search_orders_products', FILENAME_ORDERS, '', 'get', '', true);
                echo zen_draw_label(HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS, 'searchProduct', 'class="sr-only"');
                $placeholder = zen_output_string_protected(isset($_GET['search_orders_products']) && !empty($_GET['search_orders_products']) ? $_GET['search_orders_products'] : HEADING_TITLE_SEARCH_PRODUCTS);
                ?>
              <div class="input-group">
                  <?php
                  echo zen_draw_input_field('search_orders_products', '', 'id="searchProduct" class="form-control" aria-describedby="helpBlock3" placeholder="' . $placeholder . '"');
                  if (!empty($_GET['search_orders_products']) || !empty($_GET['cID'])) {
                    ?>
                  <a class="btn btn-info input-group-addon" role="button" aria-label="<?php echo TEXT_RESET_FILTER; ?>" href="<?php echo zen_href_link(FILENAME_ORDERS); ?>">
                    <i class="fa-solid fa-xmark" aria-hidden="true">&nbsp;</i>
                  </a>
                <?php } ?>
              </div>
              <span id="helpBlock3" class="help-block"><?php echo HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS; ?></span>
              <?php echo '</form>'; ?>
            </div>
            <div class="form-group col-xs-6 col-sm-3 col-md-3 col-lg-3">
                <?php
                echo zen_draw_form('orders', FILENAME_ORDERS, '', 'get', '', true);
                echo zen_draw_label(HEADING_TITLE_SEARCH, 'oID', 'class="sr-only"');
                echo zen_draw_input_field('oID', '', 'id="oID" class="form-control" placeholder="' . HEADING_TITLE_SEARCH . '"', '', 'number');
                echo zen_draw_hidden_field('action', 'edit');
                echo '</form>';
                ?>
            </div>
            <div class="form-group col-xs-6 col-sm-3 col-md-3 col-lg-3">
                <?php
                echo zen_draw_form('statusFilterForm', FILENAME_ORDERS, '', 'get', '', true);
                echo zen_draw_label(HEADING_TITLE_STATUS, 'statusFilterSelect', 'class="sr-only"');
                echo zen_draw_order_status_dropdown('statusFilterSelect', (int)$_GET['statusFilterSelect'], ['id' => '', 'text' => TEXT_ALL_ORDERS], 'class="form-control" onChange="this.form.submit();" id="statusFilterSelect"');
                echo '</form>';
                ?>
            </div>
          </div>
        </div>

        <!-- search -->
      <?php } ?>

      <?php
      if ($action === 'edit' && $order_exists) {
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_EDIT_BEGIN', $oID, $order);
        if ($order->info['payment_module_code'] && $order->info['payment_module_code'] !== PAYMENT_MODULE_GV) {
          $messageStack->reset();
          $payment_module = DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php';
          if (!file_exists($payment_module)) {
              $messageStack->add(sprintf(WARNING_PAYMENT_MODULE_DOESNT_EXIST, $order->info['payment_module_code']), 'warning');
          } else {
            require $payment_module;
            zen_include_language_file($order->info['payment_module_code'] . '.php', '/modules/payment/','inline');
            $module = new $order->info['payment_module_code']();
            if ((is_object($module) && method_exists($module, 'admin_notification')) && !$module->enabled) {
                $messageStack->add(sprintf(WARNING_PAYMENT_MODULE_NOTIFICATIONS_DISABLED, $order->info['payment_module_code']), 'warning');
            }
//        echo $module->admin_notification($oID);
          }
          if ($messageStack->size !== 0) {
?>
        <div class="messageStack-header noprint"><?php echo $messageStack->output(); ?></div><br>
<?php
          }
        }

        $prev_button = '';
        $result = $db->Execute("SELECT orders_id
                                  FROM " . TABLE_ORDERS . "
                                  WHERE orders_id < " . (int)$oID . "
                                  ORDER BY orders_id DESC
                                  LIMIT 1");
        if ($result->RecordCount()) {
          $prev_button = '<a role="button" class="btn btn-default" href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $result->fields['orders_id'] . '&action=edit') . '">&laquo; ' . $result->fields['orders_id'] . '</a>';
        }

        $next_button = '';
        $result = $db->Execute("SELECT orders_id
                                  FROM " . TABLE_ORDERS . "
                                  WHERE orders_id > " . (int)$oID . "
                                  ORDER BY orders_id ASC
                                  LIMIT 1");
        if ($result->RecordCount()) {
          $next_button = '<a role="button" class="btn btn-default" href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $result->fields['orders_id'] . '&action=edit') . '">' . $result->fields['orders_id'] . ' &raquo;</a>';
        }

        // -----
        // Enable an observer to inject additional buttons, either to the right or left (or both)
        // of the navigation.
        //
        $left_side_buttons = '';
        $right_side_buttons = '';
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_UPPER_BUTTONS', $oID, $left_side_buttons, $right_side_buttons);
        ?>
        <div class="row">
          <div class="col-sm-3 col-lg-4 text-left noprint">
            <?php echo $left_side_buttons; ?>
          </div>
          <div class="col-sm-6 col-lg-4 noprint">
            <div class="input-group">
              <span class="input-group-btn">
                  <?php echo $prev_button; ?>
              </span>
              <?php
              echo zen_draw_form('input_oid', FILENAME_ORDERS, '', 'get', '', true);
              echo zen_draw_input_field('oID', '', 'class="form-control" placeholder="' . SELECT_ORDER_LIST . '"', '', 'number');
              echo zen_draw_hidden_field('action', 'edit');
              echo '</form>';
              ?>
              <div class="input-group-btn">
                <?php echo $next_button; ?>
                <?php echo $order_list_button; ?>
                <button type="button" class="btn btn-default" onclick="history.back()"><i class="fa-solid fa-arrow-rotate-left" aria-hidden="true">&nbsp;</i> <?php echo IMAGE_BACK; ?></button>
              </div>
            </div>
          </div>
          <div class="col-sm-3 col-lg-4 text-right noprint">
             <?php echo $right_side_buttons; ?>
          </div>
        </div>
        <div class="row noprint"><?php echo zen_draw_separator(); ?></div>
        <div class="row">
          <div class="col-sm-4">
            <table class="table" id="addressCustomer">
              <tr>
                <td><strong><?php echo ENTRY_CUSTOMER_ADDRESS; ?></strong><br>
                    <button type="button" class="btn btn-xs btn-default mt-3" title="<?= TEXT_COPY ?>" onclick="copyToClipboard('customer', this)"><?= TEXT_COPY ?></button>
                </td>
                <td><?php echo zen_address_format($order->customer['format_id'], $order->customer, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td class="noprint"><a href="https://maps.google.com/maps/search/?api=1&amp;query=<?php echo urlencode($order->customer['street_address'] . ',' . $order->customer['city'] . ',' .  $order->customer['state'] . ',' . $order->customer['postcode']); ?>" rel="noreferrer" target="map"><i class="fa-regular fa-map">&nbsp;</i> <u><?php echo TEXT_MAP_CUSTOMER_ADDRESS; ?></u></a></td>
              </tr>
                <?php
                $address_footer_suffix = '';
                $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_ADDRESS_FOOTERS', 'customer', $address_footer_suffix, $order->customer);
                if (is_array($address_footer_suffix)) {
                    foreach ($address_footer_suffix as $label => $data) {
                        ?>
                        <tr>
                            <td><strong><?= $label ?></td>
                            <td><?= $data ?></td>
                        </tr>
                        <?php
                    }
                } elseif (!empty($address_footer_suffix)) { ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td><?= $address_footer_suffix ?></td>
                    </tr>
                <?php
                } ?>
              <tr>
                <td><strong><?php echo ENTRY_TELEPHONE_NUMBER; ?></strong></td>
                <td><a href="tel:<?php echo preg_replace('/\s+/', '', zen_output_string_protected($order->customer['telephone'])); ?>"><?php echo zen_output_string_protected($order->customer['telephone']); ?></a></td>
              </tr>
              <tr>
                <td><strong><?php echo ENTRY_EMAIL_ADDRESS; ?></strong></td>
                <td><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></td>
              </tr>
              <tr>
                <td><strong><?php echo TEXT_INFO_IP_ADDRESS; ?></strong></td>
                <?php
                if (!empty($order->info['ip_address'])) {
                  $ips = explode(' - ', $order->info['ip_address']);
                  $lookup_ip = $ips[0];
                  $whois_url = sprintf($whois_provider_url, $lookup_ip);
                  $lookup_ip2 = $ips[1] ?? '';
                  $whois_url2 = empty($lookup_ip2) ? '' : sprintf($whois_provider_url, $lookup_ip2);
                  $zco_notifier->notify('ADMIN_ORDERS_IP_LINKS', $lookup_ip, $whois_url, $whois_provider_url, $lookup_ip2, $whois_url2);
                  ?>
                  <td class="noprint">
                      <a href="<?= $whois_url ?>" rel="noreferrer noopener" target="_blank"><?= $lookup_ip ?></a>
                  <?php if (!empty($lookup_ip2) && $lookup_ip !== $lookup_ip2) { ?> -
                      <a href="<?= $whois_url2 ?>" rel="noreferrer noopener" target="_blank"><?= $lookup_ip2 ?></a>
                  <?php } ?>
                  </td>
                <?php } else { ?>
                  <td><?php echo TEXT_UNKNOWN; ?></td>
                <?php } ?>
              </tr>
              <tr>
                <td class="noprint"><strong><?php echo ENTRY_CUSTOMER; ?></strong></td>
                <td class="noprint">
                  <?php
                  if ($order->customer['id'] == 0) {
                       echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'search=' . $order->customer['email_address'], 'SSL') . '">' . TEXT_CUSTOMER_LOOKUP . '</a>';
                  } else {
                       echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $order->customer['id'], 'SSL') . '">' . TEXT_CUSTOMER_LOOKUP . '</a>';
                  }
                  ?>
                </td>
              </tr>
            </table>
          </div>
          <div class="col-sm-4">
            <table class="table" id="addressDelivery">
              <tr>
                <td><strong><?php echo ENTRY_SHIPPING_ADDRESS; ?></strong><br>
                    <?php if (!empty($order->delivery)) { ?>
                    <button type="button" class="btn btn-xs btn-default mt-3" title="<?= TEXT_COPY ?>" onclick="copyToClipboard('delivery', this)"><?= TEXT_COPY ?></button>
                    <?php } ?>
                </td>
                <td><?php echo (empty($order->delivery)) ? TEXT_NONE : zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
              </tr>
<?php if (!empty($order->delivery)) { ?>
              <tr>
                <td>&nbsp;</td>
                <td class="noprint"><a href="https://maps.google.com/maps/search/?api=1&amp;query=<?php echo urlencode($order->delivery['street_address'] . ',' . $order->delivery['city'] . ',' . $order->delivery['state'] . ',' . $order->delivery['postcode']); ?>" rel="noreferrer" target="map"><i class="fa-regular fa-map">&nbsp;</i> <u><?php echo TEXT_MAP_SHIPPING_ADDRESS; ?></u></a></td>
              </tr>
<?php
  }
  $address_footer_suffix = '';
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_ADDRESS_FOOTERS', 'delivery', $address_footer_suffix, $order->delivery);
    if (is_array($address_footer_suffix)) {
        foreach ($address_footer_suffix as $label => $data) {
            ?>
            <tr>
                <td><strong><?= $label ?></td>
                <td><?= $data ?></td>
            </tr>
            <?php
        }
    } elseif (!empty($address_footer_suffix)) { ?>
        <tr>
            <td>&nbsp;</td>
            <td><?= $address_footer_suffix ?></td>
        </tr>
        <?php
    } ?>
            </table>
          </div>
          <div class="col-sm-4">
            <table class="table" id="addressBilling">
              <tr>
                <td><strong><?php echo ENTRY_BILLING_ADDRESS; ?></strong><br>
                    <button type="button" class="btn btn-xs btn-default mt-3" title="<?= TEXT_COPY ?>" onclick="copyToClipboard('billing', this)"><?= TEXT_COPY ?></button>
                </td>
                <td><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, '', '<br>'); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td class="noprint"><a href="https://maps.google.com/maps/search/?api=1&amp;query=<?php echo urlencode($order->billing['street_address'] . ',' . $order->billing['city'] . ',' . $order->billing['state'] . ',' . $order->billing['postcode']); ?>" rel="noreferrer" target="map"><i class="fa-regular fa-map">&nbsp;</i> <u><?php echo TEXT_MAP_BILLING_ADDRESS; ?></u></a></td>
              </tr>
<?php
  $address_footer_suffix = '';
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_ADDRESS_FOOTERS', 'billing', $address_footer_suffix, $order->billing);
if (is_array($address_footer_suffix)) {
    foreach ($address_footer_suffix as $label => $data) {
        ?>
        <tr>
            <td><strong><?= $label ?></td>
            <td><?= $data ?></td>
        </tr>
        <?php
    }
} elseif (!empty($address_footer_suffix)) { ?>
    <tr>
        <td>&nbsp;</td>
        <td><?= $address_footer_suffix ?></td>
    </tr>
    <?php
}
  // -----
  // Determine, based on a 'soft' configuration setting in admin/extra_datafiles/site_specific_admin_overrides.php,
  // whether to display the order's overall and product-specific weights.
  //
  // Note: Zen Cart versions *prior to* 1.5.6 stored neither orders::order_weight nor
  // orders_products::products_weight.  If the order's weight is stored as null, don't
  // display the weights!
  //
  $show_orders_weights = ($order->info['order_weight'] !== null && ((bool)($show_orders_weights ?? true)));
?>
            </table>
          </div>
        </div>
        <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
        <div class="row"><strong><?php echo ENTRY_ORDER_ID . $oID; ?></strong></div>
        <div class="row">
          <table>
            <tr>
              <td class="main"><strong><?php echo ENTRY_DATE_PURCHASED; ?></strong></td>
              <td class="main"><?php echo zen_date_long($order->info['date_purchased']); ?></td>
            </tr>
<?php
            // -----
            // If the order's weight isn't null (the field was added to the stored order in
            // zc156), display the order's weight.
            //
            if ($show_orders_weights === true) {
?>
            <tr>
              <td class="main"><strong><?php echo ENTRY_WEIGHT; ?></strong></td>
              <td class="main"><?php echo $order->info['order_weight'] . ' ' . ltrim(TEXT_PRODUCT_WEIGHT_UNIT, ' '); ?></td>
            </tr>
<?php
            }
?>
            <tr>
              <td class="main"><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong></td>
              <td class="main"><?php echo $order->info['payment_method']; ?></td>
            </tr>
            <?php
            // -----
            // Note: Using loose comparison since the value is recorded (currently) as decimal(14,6)
            // and shows up in the order as (string)1.00000 if the order's placed in the store's
            // default currency.
            //
            if ($order->info['currency_value'] != 1) {
?>
            <tr>
              <td class="main"><strong><?php echo ENTRY_CURRENCY_VALUE; ?></strong></td>
              <td class="main"><?php echo $order->info['currency_value']; ?></td>
            </tr>
<?php
            }

            if (!empty($order->info['cc_type']) || !empty($order->info['cc_owner']) || !empty($order->info['cc_number'])) {
              ?>
              <tr class="noprint">
                <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <?php if (!empty($order->info['cc_type'])) { ?>
              <tr>
                <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
                <td class="main"><?php echo $order->info['cc_type']; ?></td>
              </tr>
              <?php
              }
              if (!empty($order->info['cc_owner'])) { ?>
              <tr>
                <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
                <td class="main"><?php echo $order->info['cc_owner']; ?></td>
              </tr>
              <?php
              }
              if (!empty($order->info['cc_number'])) {
                      require DIR_FS_CATALOG . DIR_WS_CLASSES . 'cc_validation.php';
                      $cc_valid = new cc_validation();
                      $cc_needs_mask = $cc_valid->validate($order->info['cc_number'], date('m'), date('y')+1);
                  ?>
              <tr>
                <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
                <td class="main"><?php echo $order->info['cc_number'] . ($cc_needs_mask == 1 ? '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_ORDERS, '&action=mask_cc&oID=' . $oID, 'NONSSL') . '" class="noprint">' . TEXT_MASK_CC_NUMBER . '</a>' : ''); ?></td>
              </tr>
              <?php
                  }
                  if (!empty($order->info['cc_cvv'])) { ?>
                <tr>
                  <td class="main"><?php echo ENTRY_CREDIT_CARD_CVV; ?></td>
                  <td class="main"><?php echo $order->info['cc_cvv'] . (!empty($order->info['cc_cvv']) && !strstr($order->info['cc_cvv'], TEXT_DELETE_CVV_REPLACEMENT) ? '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_ORDERS, '&action=delete_cvv&oID=' . $oID, 'NONSSL') . '" class="noprint">' . TEXT_DELETE_CVV_FROM_DATABASE . '</a>' : ''); ?></td>
                </tr>
              <?php } ?>
              <?php if (!empty($order->info['cc_expires'])) { ?>
              <tr>
                <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
                <td class="main"><?php echo $order->info['cc_expires']; ?></td>
              </tr>
              <?php
                  }
            }
            ?>
          </table>
          <?php $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_PAYMENTDATA_COLUMN2', $oID, $order); ?>
        </div>
        <?php
        if (isset($module) && (is_object($module) && method_exists($module, 'admin_notification')) && $module->enabled) {
          ?>
          <br>
          <div class="row noprint">
            <a href="javascript:void(0);" class="noprint" data-toggle="collapse" data-target="#payment-details-section">
                <?php echo TEXT_ADDITIONAL_PAYMENT_OPTIONS; ?>
            </a>
          </div>
          <div class="row collapse" id="payment-details-section">
            <?php echo $module->admin_notification($oID); ?>
          </div>
          <br>
          <?php
        }
        ?>

        <div class="row">
          <table class="table">
            <tr class="dataTableHeadingRow">
              <th class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS_NAME; ?></th>
              <th class="dataTableHeadingContent hidden-xs"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
<?php if ($show_orders_weights === true) { ?>
              <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_PRODUCTS_WEIGHT; ?></th>
<?php } ?>
<?php if ($show_product_tax) { ?>
              <th class="dataTableHeadingContent text-right hidden-xs"><?php echo TABLE_HEADING_TAX; ?></th>
<?php } ?>
              <th class="dataTableHeadingContent text-right"><?php echo ($show_including_tax) ? TABLE_HEADING_PRICE_EXCLUDING_TAX : TABLE_HEADING_PRICE; ?></th>
<?php if ($show_including_tax)  { ?>
              <th class="dataTableHeadingContent text-right hidden-xs"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></th>
<?php } ?>
              <th class="dataTableHeadingContent text-right"><?php echo ($show_including_tax) ? TABLE_HEADING_TOTAL_EXCLUDING_TAX : TABLE_HEADING_TOTAL; ?></th>
<?php if ($show_including_tax)  { ?>
              <th class="dataTableHeadingContent text-right hidden-xs"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></th>
<?php } ?>
            </tr>
            <?php
            $weight_unit = ' ' . ltrim(TEXT_PRODUCT_WEIGHT_UNIT, ' ');
            for ($i = 0, $n = count($order->products); $i < $n; $i++) {
              if (DISPLAY_PRICE_WITH_TAX_ADMIN === 'true') {
                $priceIncTax = $currencies->format(zen_round(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), $currencies->get_decimal_places($order->info['currency'])) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
              } else {
                $priceIncTax = $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']);
              }
              ?>
              <tr class="dataTableRow">
                <td class="dataTableContent text-right">
                  <?php echo $order->products[$i]['qty']; ?>&nbsp;x
                </td>
                <td class="dataTableContent">
                <?php
                    echo $order->products[$i]['name'];
                    if (isset($order->products[$i]['attributes']) && (count($order->products[$i]['attributes']) > 0)) {
                        for ($j = 0, $k = count($order->products[$i]['attributes']); $j < $k; $j++) {
                            echo '<br><span style="white-space:nowrap;"><small>&nbsp;<i> - ';
                            echo $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value']));
                            if (zen_is_option_file($order->products[$i]['attributes'][$j]['option_id'])) {
                                $upload_name = zen_get_uploaded_file($order->products[$i]['attributes'][$j]['value']);
                                echo ' ' . '<a href="' . zen_href_link(FILENAME_ORDERS, 'action=download&oID=' . $oID . '&filename=' .  $upload_name) . '">' . TEXT_DOWNLOAD . '</a>' . ' ';
                            }
                            if ($order->products[$i]['attributes'][$j]['price'] != '0') {
                                echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
                            }
                            if ($order->products[$i]['attributes'][$j]['product_attribute_is_free'] == '1' && $order->products[$i]['product_is_free'] == '1') {
                                echo TEXT_INFO_ATTRIBUTE_FREE;
                            }
                            // -----
                            // Uncomment the 'echo' statement below if you want to display each attribute's
                            // contribution to the ordered-product's weight (the weights are already included
                            // in the product's overall weight).
                            //
                            if ($show_orders_weights === true && $order->products[$i]['attributes'][$j]['weight'] != 0) {
//                                echo ' (' . $order->products[$i]['attributes'][$j]['weight_prefix'] . $order->products[$i]['attributes'][$j]['weight'] . $weight_unit . ')';
                            }
                            echo '</i></small></span>';
                        }
                    }
                    // Mobile phones only
                    echo '<span class="visible-xs">';
                    echo ' (' . $order->products[$i]['model'] .')';
                    echo '</span>';
                ?>
                </td>
                <td class="dataTableContent hidden-xs">
                  <?php echo $order->products[$i]['model']; ?>
                </td>
                <?php
                    if ($show_orders_weights === true) {
                        $products_weight_unit = $order->products[$i]['products_weight'];
                        if ($products_weight_unit === null) {
                            $products_weight = '&mdash;';
                        } else {
                            // ----
                            // For the total weight, format the value with 4 decimal digits, trimming
                            // any trailing 0's from the decimals -- e.g. '20.0200' becomes '20.02'.  The
                            // second rtrim removes a trailing decimal point, in case the overall weight
                            // is an integral value -- e.g. '20.0000' first becomes '20.' and then '20'.
                            //
                            $products_weight_total = rtrim(number_format((float)($products_weight_unit * $order->products[$i]['qty']), 4, '.', ''), '0');
                            $products_weight_total = rtrim($products_weight_total, '.');
                            $products_weight = "$products_weight_unit$weight_unit / $products_weight_total$weight_unit";
                        }
                ?>
                <td class="dataTableContent text-right">
                  <?php echo $products_weight; ?>
                </td>
                <?php
                    }
                ?>
<?php if ($show_product_tax) { ?>
                <td class="dataTableContent text-right hidden-xs">
                  <?php echo zen_display_tax_value($order->products[$i]['tax']); ?>%
                </td>
<?php } ?>
                <td class="dataTableContent text-right">
                  <strong><?php echo $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : ''); ?></strong>
                </td>
<?php if ($show_including_tax)  { ?>
                <td class="dataTableContent text-right hidden-xs">
                  <strong><?php echo $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : ''); ?></strong>
                </td>
<?php } ?>
                <td class="dataTableContent text-right">
                  <strong><?php echo $currencies->format(zen_round($order->products[$i]['final_price'], $currencies->get_decimal_places($order->info['currency'])) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ($order->products[$i]['onetime_charges'] != 0 ? '<br>' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : ''); ?></strong>
                </td>
<?php if ($show_including_tax)  { ?>
                <td class="dataTableContent text-right hidden-xs">
                  <strong><?php echo $priceIncTax; ?>
                    <?php if ($order->products[$i]['onetime_charges'] != 0) {
                          echo '<br>' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']);
                      }
                    ?>
                  </strong>
                </td>
<?php } ?>
              </tr>
              <?php
            }
            ?>
            <tr>

<?php
$base_orders_columns = 6;
if ($show_including_tax)  {
    $base_orders_columns += 2;
}
if ($show_orders_weights === true) {
    $base_orders_columns++;
}
?>
              <td colspan="<?php echo $base_orders_columns; ?>">
                <table style="margin-right: 0; margin-left: auto;">
                    <?php
                    for ($i = 0, $n = count($order->totals); $i < $n; $i++) {
                      ?>
                    <tr>
                      <td class="<?php echo str_replace('_', '-', $order->totals[$i]['class']); ?>-Text text-right">
                          <?php echo $order->totals[$i]['title']; ?>
                      </td>
                      <td class="<?php echo str_replace('_', '-', $order->totals[$i]['class']); ?>-Amount text-right">
                          <?php echo $currencies->format($order->totals[$i]['value'], true, $order->info['currency'], $order->info['currency_value']); ?>
                      </td>
                    </tr>
                    <?php
                  }
                  ?>
                </table>
              </td>
            </tr>
          </table>
        </div>
<?php
    // -----
    // Give an observer the chance to insert content just below the purchased products' table.
    //
    $extra_content = '';
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_CONTENT_UNDER_PRODUCTS', ['oID' => $oID], $extra_content);
    echo $extra_content;
?>
        <div class="row">
            <?php
            // show downloads
            require(DIR_WS_MODULES . 'orders_download.php');
            ?>
        </div>
        <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
        <div class="row">
          <table class="table-condensed table-striped table-bordered">
            <thead>
              <tr>
                <th class="text-center"><?php echo TABLE_HEADING_DATE_ADDED; ?></th>
                <th class="text-center hidden-xs"><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></th>
                <th class="text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
<?php
  // -----
  // A watching observer can provide an associative array in the form:
  //
  // $extra_headings = array(
  //     array(
  //       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
  //       'text' => $value
  //     ),
  // );
  //
  // Observer note:  Be sure to check that the $p2/$extra_headings value is specifically (bool)false before initializing, since
  // multiple observers might be injecting content!
  //
  $extra_headings = false;
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_STATUS_HISTORY_EXTRA_COLUMN_HEADING', [], $extra_headings);
  if (is_array($extra_headings)) {
      foreach ($extra_headings as $heading_info) {
          $align = (isset($heading_info['align'])) ? (' class="text-' . $heading_info['align'] . '"') : '';
?>
                <th<?php echo $align; ?>><?php echo $heading_info['text']; ?></th>
<?php
      }
  }
?>
                <th class="text-center"><?php echo TABLE_HEADING_COMMENTS; ?></th>
                <th class="text-center hidden-xs"><?php echo TABLE_HEADING_UPDATED_BY; ?></th>
              </tr>
            </thead>
            <tbody>
                <?php
                $orders_history = $order->statuses;

                if (count($orders_history) !== 0) {
                  $first = true;
                  foreach ($orders_history as $item) {
                    ?>
                  <tr>
                    <td class="text-center"><?php echo zen_datetime_short($item['date_added']); ?></td>
                    <td class="text-center hidden-xs">
                        <?php
                        if ($item['customer_notified'] == '1') {
                          echo zen_icon('tick', TEXT_YES, 'lg');
                        } elseif ($item['customer_notified'] == '-1') {
                          echo zen_icon('locked', TEXT_HIDDEN, 'lg');
                        } else {
                          echo zen_icon('unlocked', TEXT_VISIBLE, 'lg');
                        }
                        ?>
                    </td>
                    <td><?php echo $orders_status_array[$item['orders_status_id']] ?? ''; ?></td>
<?php
                    // -----
                    // A watching observer can provide an associative array in the form:
                    //
                    // $extra_data = array(
                    //     array(
                    //       'align' => $alignment,    // One of 'center', 'right' or 'left' (optional)
                    //       'text' => $value
                    //     ),
                    // );
                    //
                    // Observer note:  Be sure to check that the $p2/$extra_data value is specifically (bool)false before initializing, since
                    // multiple observers might be injecting content!
                    //
                    $extra_data = false;
                    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_STATUS_HISTORY_EXTRA_COLUMN_DATA', $item, $extra_data);
                    if (is_array($extra_data)) {
                        foreach ($extra_data as $data_info) {
                            $align = (isset($data_info['align'])) ? (' text-' . $data_info['align']) : '';
                  ?>
                                  <td class="smallText<?php echo $align; ?>"><?php echo $data_info['text']; ?></td>
                  <?php
                        }
                    }
?>
                    <td>
<?php
                        if ($first) {
                           echo nl2br(zen_output_string_protected($item['comments'] ?? ''));
                           $first = false;
                        } else {
                           echo nl2br($item['comments'] ?? '');
                        }
?>
                    </td>
                    <td class="text-center hidden-xs"><?php echo (!empty($item['updated_by'])) ? $item['updated_by'] : '&nbsp;'; ?></td>
                  </tr>
                  <?php
                }
              } else {
                ?>
                <tr>
                  <td colspan="4"><?php echo TEXT_NO_ORDER_HISTORY; ?></td>
                </tr>
                <?php
              }
              ?>
            </tbody>
          </table>
        </div>
<?php
    $additional_content = false;
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_AFTER_STATUS_LISTING', $oID, $additional_content);
    if ($additional_content !== false) {
?>
        <div class="row noprint"><?php echo $additional_content; ?></div>
<?php
    }
?>
        <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></div>
        <div class="row noprint">
          <div class="formArea">
              <?php echo zen_draw_form('statusUpdateForm', FILENAME_ORDERS, zen_get_all_get_params(['action', 'language']) . 'action=update_order&language=' . $order->info['language_code'], 'post', 'class="form-horizontal"', true);
               echo zen_draw_hidden_field('camefrom', 'orderEdit'); // identify from where the form was submitted (infoBox/listing or details), to redirect back to this same page ?>
              <div class="form-group">
                  <?php echo zen_draw_label(TABLE_HEADING_COMMENTS, 'comments', 'class="col-sm-3 control-label"'); ?>
                  <div class="col-sm-9">
                      <?php echo zen_draw_textarea_field('comments', 'soft', '60', '5', '', 'id="comments" class="editorHook form-control"');
                      // remind admin user of the order/customer language in case of writing a comment.
                      if (count(zen_get_languages()) > 1) {
                          echo '<br>' . zen_get_language_icon($order->info['language_code']) . ' <strong>' . sprintf(TEXT_EMAIL_LANGUAGE, ucfirst(zen_get_language_name($order->info['language_code']))) . '</strong>';
                          echo zen_draw_hidden_field('admin_language', $_SESSION['languages_code']);
                      } ?>
                  </div>
              </div>
<?php
    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_ADDL_HISTORY_INPUTS', []);
?>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_STATUS, 'statusUpdateSelect', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9">
                  <?php echo zen_draw_order_status_dropdown('statusUpdateSelect', $order->info['orders_status'], '', 'id="statusUpdateSelect" class="form-control"'); ?>
              </div>
            </div>
<?php
        // -----
        // Give an observer the chance to supply some additional status-related inputs.  Each
        // entry in the $extra_status_inputs returned contains:
        //
        // array(
        //    'label' => array(
        //        'text' => 'The label text',   (required)
        //        'addl_class' => {Any additional class to be applied to the label} (optional)
        //        'parms' => {Any additional parameters for the label, e.g. 'style="font-weight: 700;"} (optional)
        //    ),
        //    'input' => 'The HTML to be inserted' (required)
        // )
        $extra_status_inputs = [];
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_EXTRA_STATUS_INPUTS', $order, $extra_status_inputs);
        if (!empty($extra_status_inputs)) {
            foreach ($extra_status_inputs as $extra_status) {
                $addl_class = (isset($extra_status['label']['addl_class'])) ? (' ' . $extra_status['label']['addl_class']) : '';
                $parms = (isset($extra_status['label']['parms'])) ? (' ' . $extra_status['label']['parms']) : '';
?>
            <div class="form-group">
                <div class="col-sm-3 control-label<?php echo $addl_class; ?>"<?php echo $parms; ?>><?php echo $extra_status['label']['text']; ?></div>
                <div class="col-sm-9"><?php echo $extra_status['input']; ?></div>
            </div>
<?php
            }
        }
?>
            <div class="form-group">
                <div class="col-sm-3 control-label font-weight-bold"><?php echo ENTRY_NOTIFY_CUSTOMER; ?></div>
              <div class="col-sm-9">
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('notify', '1', $notify_email) . TEXT_EMAIL; ?></label>
                </div>
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('notify', '0', $notify_no_email) . TEXT_NOEMAIL; ?></label>
                </div>
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('notify', '-1', $notify_hidden) . TEXT_HIDE; ?></label>
                </div>
              </div>
            </div>
            <div class="form-group">
                <?php echo zen_draw_label(ENTRY_NOTIFY_COMMENTS, 'notify_comments', 'class="col-sm-3 control-label"'); ?>
              <div class="col-sm-9">
                  <?php echo zen_draw_checkbox_field('notify_comments', '', true, '', 'id="notify_comments"'); ?>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-9 col-sm-offset-3">
                <button type="submit" class="btn btn-info"><?php echo IMAGE_UPDATE; ?></button>
              </div>
            </div>
            <?php echo '</form>'; ?>
          </div>
        </div>
        <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></div>
<?php
        // -----
        // Enable the addition of extra buttons when editing the order.
        //
        $extra_buttons = '';
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_EDIT_BUTTONS', $oID, $order, $extra_buttons);
?>
        <div class="row text-right noprint">
          <a href="<?php echo zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $_GET['oID']); ?>" target="_blank" class="btn btn-primary" role="button"><?php echo IMAGE_ORDERS_INVOICE; ?></a>
          <a href="<?php echo zen_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']); ?>" target="_blank" class="btn btn-primary" role="button"><?php echo IMAGE_ORDERS_PACKINGSLIP; ?></a>
          <?php echo $order_list_button; ?>
          <?php echo $extra_buttons; ?>
        </div>
        <?php
// check if order has open gv
        $gv_check = $db->Execute("SELECT order_id, unique_id
                                    FROM " . TABLE_COUPON_GV_QUEUE . "
                                    WHERE order_id = " . (int)$_GET['oID'] . "
                                    AND release_flag = 'N'
                                    LIMIT 1");
        if ($gv_check->RecordCount() > 0) {
          ?>
          <div class="row noprint"><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></div>
          <div class="row text-right noprint">
            <a href="<?php echo zen_href_link(FILENAME_GV_QUEUE, 'order=' . $_GET['oID']); ?>"><?php echo IMAGE_GIFT_QUEUE; ?></a>
          </div>
          <?php
        }
        ?>
        <?php
      } else {
        ?>
<?php
        // Additional notification, allowing admin-observers to include additional legend icons
        $extra_legends = '';
        $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_MENU_LEGEND', [], $extra_legends);
?>
        <div class="row"><?php echo TEXT_LEGEND . ' ' . zen_icon('status-red', TEXT_BILLING_SHIPPING_MISMATCH) . ' ' . TEXT_BILLING_SHIPPING_MISMATCH . $extra_legends; ?></div>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table id="orders-table" class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ORDERS_ID; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></th>
<?php if ($show_zone_info) { ?>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_ZONE_INFO; ?></th>
<?php } ?>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></th>
<?php if ($quick_view_popover_enabled) { ?>
                  <th></th>
<?php } ?>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_STATUS; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_CUSTOMER_COMMENTS; ?></th>
<?php
  // -----
  // A watching observer can provide an associative array in the form:
  //
  // $extra_headings = array(
  //     array(
  //       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
  //       'text' => $value
  //     ),
  // );
  //
  // Observer note:  Be sure to check that the $p2/$extra_headings value is specifically (bool)false before initializing, since
  // multiple observers might be injecting content!
  //
  $extra_headings = false;
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_LIST_EXTRA_COLUMN_HEADING', [], $extra_headings);
  if (is_array($extra_headings)) {
      foreach ($extra_headings as $heading_info) {
          $align = (isset($heading_info['align'])) ? (' text-' . $heading_info['align']) : '';
?>
                <th class="dataTableHeadingContent<?php echo $align; ?>"><?php echo $heading_info['text']; ?></th>
<?php
      }
  }
?>
                  <th class="dataTableHeadingContent noprint text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
              </thead>
              <tbody>
                  <?php
// Only one or the other search
// create search_orders_products filter
                  $search = '';
                  $search_distinct = ' ';
                  $new_table = '';
                  $new_fields = '';
                  $keywords = '';
                  if (!empty($_GET['search_orders_products'])) {
                    $search_distinct = ' DISTINCT ';
                    $new_table = " LEFT JOIN " . TABLE_ORDERS_PRODUCTS . " op ON (op.orders_id = o.orders_id) ";
                    $keywords = zen_db_input(zen_db_prepare_input($_GET['search_orders_products']));
                      $keyword_search_fields = [
                          'op.products_name',
                          'op.products_model',
                          'op.products_id',
                      ];
                      $search = zen_build_keyword_where_clause($keyword_search_fields, trim($keywords), true);
                    if (substr(strtoupper($_GET['search_orders_products']), 0, 3) === 'ID:') {
                      $keywords = trim(substr($_GET['search_orders_products'], 3));
                      $search .= " OR op.products_id ='" . (int)$keywords . "'";
                    }
                  } elseif (!empty($_GET['search'])) {
// create search filter
                      $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
                      $keyword_search_fields = [
                          'o.customers_name',
                          'o.customers_company',
                          'o.customers_street_address',
                          'o.customers_city',
                          'o.customers_postcode',
                          'o.customers_email_address',
                          'o.customers_telephone',
                          'o.date_purchased',
                          'o.billing_name',
                          'o.billing_company',
                          'o.billing_street_address',
                          'o.billing_city',
                          'o.billing_postcode',
                          'o.delivery_name',
                          'o.delivery_company',
                          'o.delivery_street_address',
                          'o.delivery_city',
                          'o.delivery_postcode',
                          'o.ip_address',
                      ];
                      $search = zen_build_keyword_where_clause($keyword_search_fields, trim($keywords), true);
                  }
                  $new_fields .= ", o.customers_company, o.customers_email_address, o.customers_street_address, o.delivery_company, o.delivery_name, o.delivery_street_address, o.delivery_postcode, o.billing_company, o.billing_name, o.billing_street_address, o.billing_postcode, o.payment_module_code, o.shipping_module_code, o.orders_status, o.ip_address, o.language_code, o.delivery_state, o.delivery_country, o.customers_state, o.customers_country ";

                  $order_by = " ORDER BY o.orders_id DESC";
                  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_SEARCH_PARMS', $keywords, $search, $search_distinct, $new_fields, $new_table, $order_by);

                  $orders_query_raw = "SELECT " . $search_distinct . " o.orders_id, o.customers_id, o.customers_name, o.payment_method, o.shipping_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, o.order_total" .
                      $new_fields . "
                          FROM (" . TABLE_ORDERS . " o " .
                      $new_table . ")
                          LEFT JOIN " . TABLE_ORDERS_STATUS . " s ON (o.orders_status = s.orders_status_id AND s.language_id = " . (int)$_SESSION['languages_id'] . ")";


                  if (!empty($_GET['cID'])) {
                    $cID = (int)zen_db_prepare_input($_GET['cID']);
                    $orders_query_raw .= " WHERE o.customers_id = " . (int)$cID;
                  } elseif ($_GET['statusFilterSelect'] !== '') {
                    $status_filter = (int)zen_db_prepare_input($_GET['statusFilterSelect']);
                    $orders_query_raw .= " WHERE s.orders_status_id = " . (int)$status_filter . $search;
                  } else {
                    $orders_query_raw .= $search;
                  }

                  $orders_query_raw .= $order_by;

// Split Page
// reset page when page is unknown
                  if ((empty($_GET['page']) || $_GET['page'] <= 1) && !empty($_GET['oID'])) {
                    $check_page = $db->Execute($orders_query_raw);
                    $check_count = 0;
                    if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_ORDERS) {
                      while (!$check_page->EOF) {
                        $check_count++;
                        if ($check_page->fields['orders_id'] == $_GET['oID']) {
                          break;
                        }
                        $check_page->MoveNext();
                      }
                      $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS_ORDERS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS_ORDERS) != 0 ? .5 : 0)), 0);
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
                      echo '<tr id="defaultSelected" class="dataTableRowSelected order-listing-row" data-oid="' . $orders->fields['orders_id'] . '" data-current="current">' . "\n";
                    } else {
                      echo '<tr class="dataTableRow order-listing-row" data-oid="' . $orders->fields['orders_id'] . '">' . "\n";
                    }

                    $show_difference = '';
                    if (!empty($orders->fields['delivery_name']) && (strtoupper($orders->fields['delivery_name']) !== strtoupper($orders->fields['billing_name']))) {
                      $show_difference = zen_icon('status-red', TEXT_BILLING_SHIPPING_MISMATCH) . '&nbsp;';
                    }
                    if (!empty($orders->fields['delivery_street_address']) && (strtoupper($orders->fields['delivery_street_address']) !== strtoupper($orders->fields['billing_street_address']))) {
                      $show_difference = zen_icon('status-red', TEXT_BILLING_SHIPPING_MISMATCH) . '&nbsp;';
                    }
                    if (strtoupper($orders->fields['delivery_postcode']) !== strtoupper($orders->fields['billing_postcode']) && trim($orders->fields['delivery_postcode']) !== '') {
                      $show_difference = zen_icon('status-red', TEXT_BILLING_SHIPPING_MISMATCH) . '&nbsp;';
                    }
                    //-Additional "difference" icons can be added on a per-order basis and/or additional icons to be added to the "action" column.
                    $extra_action_icons = '';
                    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_SHOW_ORDER_DIFFERENCE', [], $orders->fields, $show_difference, $extra_action_icons);

                    $show_payment_type = $orders->fields['payment_module_code'] . '<br>' . $orders->fields['shipping_module_code'];

                    $product_details = '';
                    if ($quick_view_popover_enabled) {
                        $sql = "SELECT op.orders_products_id, op.products_quantity AS qty, op.products_name AS name, op.products_model AS model
                                FROM " . TABLE_ORDERS_PRODUCTS . " op
                                WHERE op.orders_id = " . (int)$orders->fields['orders_id'];
                        $orderProducts = $db->Execute($sql, false, true, 1800);
                        foreach ($orderProducts as $product) {
                            $product_details .= $product['qty'] . ' x ' . $product['name'] . (!empty($product['model']) ? ' (' . $product['model'] . ')' :'') . "\n";
                            if ($includeAttributesInProductDetailRows) {
                                $sql = "SELECT products_options, products_options_values
                                    FROM " .  TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                                    WHERE orders_products_id = " . (int)$product['orders_products_id'] . " ORDER BY orders_products_attributes_id ASC";
                                $productAttributes = $db->Execute($sql, false, true, 1800);
                                foreach ($productAttributes as $attr) {
                                  if (!empty($attr['products_options'])) {
                                     $product_details .= '&nbsp;&nbsp;- ' . $attr['products_options'] . ': ' . zen_output_string_protected($attr['products_options_values']) . "\n";
                                  }
                                }
                            }
                            $product_details .= '<hr>'; // add HR
                        }
                        $product_details = rtrim($product_details);
                        $product_details = preg_replace('~<hr>$~', '', $product_details); // remove last HR
                        $product_details = nl2br($product_details);
                    }
                    ?>
                <td class="dataTableContent text-center"><?php echo $show_difference . $orders->fields['orders_id']; ?></td>
                <td class="dataTableContent"><?php echo $show_payment_type; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $orders->fields['customers_id'], 'NONSSL') . '"><i class="fa-solid fa-magnifying-glass"></i></a>&nbsp;' . $orders->fields['customers_name'] . ($orders->fields['customers_company'] !== '' ? '<br>' . zen_output_string_protected($orders->fields['customers_company']) : ''); ?></td>
<?php if ($show_zone_info) { ?>
                <td class="dataTableContent text-left">
<?php
                    if (!empty($orders->fields['delivery_country'])) {
                       echo zen_output_string_protected($orders->fields['delivery_state']) . '<br>' . zen_output_string_protected($orders->fields['delivery_country']);
                    } else {
                       echo zen_output_string_protected($orders->fields['customers_state']) . '<br>' . zen_output_string_protected($orders->fields['customers_country']);
                    }
?>
                </td>
<?php } ?>
                <td class="dataTableContent text-right" title="<?php echo zen_output_string($product_details, ['"' => '&quot;', "'" => '&#39;', '<br>' => '', '<br />' => '', '<hr>' => "----\n"]); ?>">
                  <?php echo strip_tags($currencies->format($orders->fields['order_total'], true, $orders->fields['currency'], $orders->fields['currency_value'])); ?>
                </td>
<?php if ($quick_view_popover_enabled) { ?>
                <td class="dataTableContent text-right dataTableButtonCell">
                    <a tabindex="0" class="btn btn-xs btn-link mt-3 orderProductsPopover" role="button" data-toggle="popover"
                       data-trigger="focus"
                       data-placement="left"
                       title="<?php echo TEXT_PRODUCT_POPUP_TITLE; ?>"
                       data-content="<?php echo zen_output_string($product_details, ['"' => '&quot;', "'" => '&#39;', '<br />' => '<br>']); ?>"
                    >
                        <?php echo TEXT_PRODUCT_POPUP_BUTTON; ?>
                    </a>
                </td>
<?php } ?>
                <td class="dataTableContent text-center"><?php echo zen_datetime_short($orders->fields['date_purchased']); ?></td>
                <td class="dataTableContent text-right"><?php echo !empty($orders->fields['orders_status_name']) ? $orders->fields['orders_status_name'] : TEXT_INVALID_ORDER_STATUS; ?></td>
                <?php
                   $order_comments = zen_output_string_protected(zen_get_orders_comments($orders->fields['orders_id']));
                   if (!empty($order_comments)) {
                      echo '<td class="dataTableContent text-center" title="' . $order_comments . '">';
                      echo zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', '', 16, 16);
                      echo '</td>';
                   } else {
                     echo '<td class="dataTableContent text-center"></td>';
                   }
                ?>
<?php
  // -----
  // A watching observer can provide an associative array in the form:
  //
  // $extra_data = array(
  //     array(
  //       'align' => $alignment,    // One of 'center', 'right', or 'left' (optional)
  //       'text' => $value
  //     ),
  // );
  //
  // Observer note:  Be sure to check that the $p3/$extra_data value is specifically (bool)false before initializing, since
  // multiple observers might be injecting content!
  //
  $extra_data = false;
  $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_LIST_EXTRA_COLUMN_DATA', ($oInfo ?? []), $orders->fields, $extra_data);
  if (is_array($extra_data)) {
      foreach ($extra_data as $data_info) {
          $align = (isset($data_info['align'])) ? (' text-' . $data_info['align']) : '';
?>
                <td class="dataTableContent<?php echo $align; ?>"><?php echo $data_info['text']; ?></td>
<?php
      }
  }
?>

                <td class="dataTableContent noprint text-right actions dataTableButtonCell">
                  <div class="btn-group">
                    <?php
                    echo '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['oID', 'action']) . 'oID=' . $orders->fields['orders_id'] . '&action=edit', 'NONSSL') . '" class="btn btn-sm btn-default btn-edit" data-toggle="tooltip" title="' . ICON_EDIT . '">' .
                      zen_icon('pencil', hidden: true) .
                    '</a>' . $extra_action_icons;
                    ?>
                    </div>
                    <?php
                    if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) {
                      echo zen_icon('caret-right', '', '2x', true);
                    } else {
                      echo '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['oID']) . 'oID=' . $orders->fields['orders_id'], 'NONSSL') . '" data-toggle="tooltip" title="' . IMAGE_ICON_INFO . '" role="button">' . zen_icon('circle-info', '', '2x', true, false) . '</a>';
                    }
                    ?>&nbsp;</td>
                </tr>
                <?php
                $orders->MoveNext();
              }
              ?>
              </tbody>
            </table>
            <table class="table">
              <tr>
                  <td><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ORDERS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                  <td class="text-right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_ORDERS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'],
                          zen_get_all_get_params(['page', 'oID', 'action'])); ?></td>
              </tr>
              <?php
              if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
              ?>
                  <tr>
                      <td class="text-right" colspan="2">
                      <?php
                          echo '<a href="' . zen_href_link(FILENAME_ORDERS, '', 'NONSSL') . '" class="btn btn-default" role="button">' . IMAGE_RESET . '</a>';
                          if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
                              $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
                              echo '<br>' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
                          }
                      ?>
                      </td>
                  </tr>
              <?php
              }
              ?>
            </table>
          </div>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
              <?php
              $heading = [];
              $contents = [];

              switch ($action) {
                case 'delete':
                  $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_DELETE_ORDER . '</h4>'];

                  $contents = ['form' => zen_draw_form('orders', FILENAME_ORDERS, zen_get_all_get_params(['oID', 'action']) . '&action=deleteconfirm', 'post', 'class="form-horizontal"', true) . zen_draw_hidden_field('oID', $oInfo->orders_id)];
//      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br><br><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
                  $contents[] = ['text' => TEXT_INFO_DELETE_INTRO . '<br><br><strong>' . ENTRY_ORDER_ID . $oInfo->orders_id . '<br>' . $oInfo->order_total . '<br>' . $oInfo->customers_name . ($oInfo->customers_company !== '' ? '<br>' . zen_output_string_protected($oInfo->customers_company) : '') . '</strong>'];
                  $contents[] = ['text' => '<br><label>' . zen_draw_checkbox_field('restock', 'on') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY . '</label>'];
                  $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['oID', 'action']) . 'oID=' . $oInfo->orders_id, 'NONSSL') . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                  break;
                default:
                  if (isset($oInfo) && is_object($oInfo)) {
                    $heading[] = ['text' => '<h4>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . zen_datetime_short($oInfo->date_purchased) . '</h4>'];

                    $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['oID', 'action']) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '" class="btn btn-primary" role="button">' . IMAGE_DETAILS . '</a> <a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['oID', 'action']) . 'oID=' . $oInfo->orders_id . '&action=delete', 'NONSSL') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>'];
                    $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id) . '" target="_blank" class="btn btn-info" role="button">' . IMAGE_ORDERS_INVOICE . '</a> <a href="' . zen_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" target="_blank" class="btn btn-info" role="button">' . IMAGE_ORDERS_PACKINGSLIP . '</a>'];
                    $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_MENU_BUTTONS', $oInfo, $contents);

                    // each contents array is drawn in a div, so this form block must be a single array element.
                    $contents[] = ['text' =>
                        zen_draw_form('statusUpdateForm', FILENAME_ORDERS, zen_get_all_get_params(['action','language']) . 'action=update_order' . (!isset($_GET['oID']) ? '&oID=' . $oInfo->orders_id : '') . '&language=' . $oInfo->language_code, 'post', '', true) . // form action uses the order language to change the session language on the update. On initial page load (from another page), $_GET['oID'] is not set, hence clause in form action
                        '<fieldset style="border:solid thin slategray;padding:5px"><legend style="width:inherit;">&nbsp;' . IMAGE_UPDATE . '&nbsp;</legend>' .
                        ($oInfo->language_code !== $_SESSION['languages_code'] ? zen_draw_hidden_field('admin_language', $_SESSION['languages_code']) : '') . // if the order language is different to the current admin language, record the admin language, to restore it in the redirect after the status update email has been sent
                        zen_draw_label(IMAGE_SEND_EMAIL, 'notify', 'class="control-label"') .
                        zen_draw_checkbox_field('notify', '1', $notify_email, '', 'class="checkbox-inline" id="notify"') . "<br>\n" .
                        zen_draw_label(ENTRY_STATUS, 'statusUpdateSelect', 'class="control-label"') .
                        zen_draw_order_status_dropdown('statusUpdateSelect', $oInfo->orders_status, '', 'onChange="this.form.submit();" id="statusUpdateSelect" class="form-control"') . "\n" .
                        '</fieldset></form>' . "\n"];

                    $contents[] = ['text' => '<br>' . TEXT_DATE_ORDER_CREATED . ' ' . zen_date_short($oInfo->date_purchased)];
                    $contents[] = ['text' => '<br>' . '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $oInfo->customers_id, 'NONSSL') . '">' . $oInfo->customers_email_address . '</a>' ];
                    $contents[] = ['text' => TEXT_INFO_IP_ADDRESS . ' ' . $oInfo->ip_address];
                    if (zen_not_null($oInfo->last_modified)) {
                      $contents[] = ['text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . zen_date_short($oInfo->last_modified)];
                    }
                    $contents[] = ['text' => '<br>' . TEXT_INFO_PAYMENT_METHOD . ' ' . $oInfo->payment_method];
                    $contents[] = ['text' => '<br>' . ENTRY_SHIPPING . ' ' . $oInfo->shipping_method];

// check if order has open gv
                    $gv_check = $db->Execute("SELECT order_id, unique_id
                                              FROM " . TABLE_COUPON_GV_QUEUE . "
                                              WHERE order_id = " . (int)$oInfo->orders_id . "
                                              AND release_flag = 'N'
                                              LIMIT 1");
                    if ($gv_check->RecordCount() > 0) {
                      $goto_gv = '<a href="' . zen_href_link(FILENAME_GV_QUEUE, 'order=' . $oInfo->orders_id) . '" class="btn btn-primary" role="button">' . IMAGE_GIFT_QUEUE . '</a>';
                      $contents[] = ['text' => '<br>' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '', '3', 'style="width:100%"')];
                      $contents[] = ['align' => 'text-center', 'text' => $goto_gv];
                    }

                    // indicate if comments exist
                    $orders_history_query = $db->Execute(
                        "SELECT comments, updated_by
                           FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                          WHERE orders_id = " . (int)$oInfo->orders_id . "
                            AND comments != ''
                          ORDER BY date_added ASC
                          LIMIT 1"
                    );

                    if (!$orders_history_query->EOF) {
                      $contents[] = ['text' => '<br>' . TABLE_HEADING_COMMENTS];

                      // -----
                      // If the first comment was made by the customer, protect the output (no HTML); otherwise, HTML was entered
                      // by an admin or 'known' process so it's OK.
                      //
                      $protected = $orders_history_query->fields['updated_by'] === '';
                      $contents[] = ['text' => nl2br(zen_output_string($orders_history_query->fields['comments'], false, $protected))];
                    }

                    $contents[] = ['text' => '<br>' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '', '3', 'style="width:100%"')];
                    $order = new order($oInfo->orders_id);
                    $contents[] = ['text' => TABLE_HEADING_PRODUCTS . ': ' . count($order->products)];
                    for ($i = 0, $n=count($order->products); $i <$n; $i++) {
                      $contents[] = ['text' => $order->products[$i]['qty'] . '&nbsp;x&nbsp;' . $order->products[$i]['name']];

                      if (!empty($order->products[$i]['attributes'])) {
                        for ($j = 0, $nn=count($order->products[$i]['attributes']); $j < $nn; $j++) {
                          $contents[] = ['text' => '&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . nl2br(zen_output_string_protected($order->products[$i]['attributes'][$j]['value'])) . '</i>'];
                        }
                      }
                      if ($i > MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING && MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING != 0) {
                        $contents[] = ['text' => TEXT_MORE];
                        break;
                      }
                    }

                    if (count($order->products) > 0) {
                      $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['oID', 'action']) . 'oID=' . $oInfo->orders_id . '&action=edit', 'NONSSL') . '" class="btn btn-primary" role="button">' . IMAGE_DETAILS . '</a>'];
                    }
                  }
                  break;
              }
              $zco_notifier->notify('NOTIFY_ADMIN_ORDERS_MENU_BUTTONS_END', ($oInfo ?? []), $contents);

              if (!empty($heading) && !empty($contents)) {
                $box = new box();
                echo $box->infoBox($heading, $contents);
              }
              ?>
          </div>
        </div>
        <?php
      }
      ?>
      <!-- body_text_eof //-->

    </div>
    <!-- body_eof //-->

    <!--  enable on-page script tools -->
    <script>
        <?php
        $order_link = str_replace('&amp;', '&', zen_href_link(FILENAME_ORDERS, zen_get_all_get_params(['oID', 'action']) . "oID=[*]"));
        ?>
        jQuery(function () {
            const orderLink = '<?php echo $order_link; ?>';
            jQuery("tr.order-listing-row td").not('.dataTableButtonCell').on('click', (function() {
                window.location.href = orderLink.replace('[*]', jQuery(this).parent().attr('data-oid') + (jQuery(this).parent().attr('data-current') ? '&action=edit' : ''));
            }));
            jQuery('[data-toggle="popover"]').popover({html:true,sanitize: true});
        })
    </script>


    <!-- footer //-->
    <div class="footer-area">
      <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    </div>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');
