<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 May 17 Modified in v1.5.7 $
 */
require('includes/application_top.php');
if (file_exists(DIR_FS_CATALOG . 'includes/classes/dbencdata.php')) {
  require_once(DIR_FS_CATALOG . 'includes/classes/dbencdata.php');
}

$set = (isset($_GET['set']) ? $_GET['set'] : (isset($_POST['set']) ? $_POST['set'] : ''));

$is_ssl_protected = (substr(HTTP_SERVER, 0, 5) == 'https') ? TRUE : FALSE;

if (zen_not_null($set)) {
  switch ($set) {
    case 'shipping':
      $module_type = 'shipping';
      $module_directory = DIR_FS_CATALOG_MODULES . 'shipping/';
      $module_key = 'MODULE_SHIPPING_INSTALLED';
      define('HEADING_TITLE', HEADING_TITLE_MODULES_SHIPPING);
      $shipping_errors = '';
      if (zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == 'NONE' || zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == '') {
        $shipping_errors .= '<br>' . ERROR_SHIPPING_ORIGIN_ZIP;
      }
      if (zen_get_configuration_key_value('ORDER_WEIGHT_ZERO_STATUS') == '1' && (!defined('MODULE_SHIPPING_FREESHIPPER_STATUS') || MODULE_SHIPPING_FREESHIPPER_STATUS != 'True')) {
        $shipping_errors .= '<br>' . ERROR_ORDER_WEIGHT_ZERO_STATUS;
      }
      if (defined('MODULE_SHIPPING_USPS_STATUS') && ( MODULE_SHIPPING_USPS_USERID == 'NONE' || MODULE_SHIPPING_USPS_SERVER == 'test')) {
        $shipping_errors .= '<br>' . ERROR_USPS_STATUS;
      }
      if ($shipping_errors != '') {
        $messageStack->add(ERROR_SHIPPING_CONFIGURATION . $shipping_errors, 'caution');
      }
      break;
    case 'ordertotal':
      $module_type = 'order_total';
      $module_directory = DIR_FS_CATALOG_MODULES . 'order_total/';
      $module_key = 'MODULE_ORDER_TOTAL_INSTALLED';
      define('HEADING_TITLE', HEADING_TITLE_MODULES_ORDER_TOTAL);
      break;
    case 'payment':
    default:
      $module_type = 'payment';
      $module_directory = DIR_FS_CATALOG_MODULES . 'payment/';
      $module_key = 'MODULE_PAYMENT_INSTALLED';
      define('HEADING_TITLE', HEADING_TITLE_MODULES_PAYMENT);
      break;
  }
}

$nModule = isset($_GET['module']) ? $_GET['module'] : null;
$notificationType = $module_type . (($nModule) ? '-' . $nModule : '') ;

$notifications = new AdminNotifications();
$availableNotifications = $notifications->getNotifications($notificationType, $_SESSION['admin_id']);

$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (zen_not_null($action)) {
  $admname = '{' . preg_replace('/[^\w]/', '*', zen_get_admin_name()) . '[' . (int)$_SESSION['admin_id'] . ']}';
  switch ($action) {
    case 'save':
      $class = basename($_GET['module']);
      if (!$is_ssl_protected && in_array($class, ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck'])) {
        break;
      }
      foreach($_POST['configuration'] as $key => $value) {
        if (is_array($value)) {
          $value = implode(", ", $value);
          $value = preg_replace("/, --none--/", "", $value);
        }
        if (function_exists('dbenc_encrypt') && function_exists('dbenc_is_encrypted_value_key') && dbenc_is_encrypted_value_key($key)) {
          $value = dbenc_encrypt($value);
        }

        // See if there are any configuration checks
        $checks = $db->Execute("SELECT configuration_title, val_function FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . $key . "'");
        if (!$checks->EOF && $checks->fields['val_function'] != NULL) {
           require_once('includes/functions/configuration_checks.php');
           if (!zen_validate_configuration_entry($value, $checks->fields['val_function'], $checks->fields['configuration_title'])) {
             zen_redirect(zen_href_link(FILENAME_MODULES, 'set=' . $_GET['set'] . '&module=' . $_GET['module'] . '&action=edit'));
           }
        }
        $db->Execute("update " . TABLE_CONFIGURATION . "
                        set configuration_value = '" . zen_db_input($value) . "'
                        where configuration_key = '" . zen_db_input($key) . "'");
      }
      $msg = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_SETTINGS_CHANGED, preg_replace('/[^\w]/', '*', (!empty($_GET['module']) ? $_GET['module'] : (!empty($_GET['set']) ? $_GET['set'] : 'UNKNOWN'))), $admname);
      zen_record_admin_activity($msg, 'warning');
      zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED, $msg, STORE_NAME, EMAIL_FROM, ['EMAIL_MESSAGE_HTML' => $msg], 'admin_settings_changed');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=' . $set . (!empty($_GET['module']) ? '&module=' . $_GET['module'] : ''), 'SSL'));
      break;
    case 'install':
      $result = 'failed';
      $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
      $class = basename($_POST['module']);
      if (!$is_ssl_protected && in_array($class, ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck'])) {
          break;
      }
      if (file_exists($module_directory . $class . $file_extension)) {
          if (file_exists(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $class . $file_extension)) {
            include DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $class . $file_extension;
            include $module_directory . $class . $file_extension;
            $module = new $class();
            $msg = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MODULE_INSTALLED, preg_replace('/[^\w]/', '*', $_POST['module']), $admname);
            zen_record_admin_activity($msg, 'warning');
            zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED, $msg, STORE_NAME, EMAIL_FROM, ['EMAIL_MESSAGE_HTML' => $msg], 'admin_settings_changed');
            $result = $module->install();
          }
      }
      if ($result != 'failed') {
        zen_redirect(zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class . '&action=edit', 'SSL'));
      }
      break;
    case 'removeconfirm':
      $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
      $class = basename($_POST['module']);
      if (file_exists($module_directory . $class . $file_extension)) {
          if (file_exists(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $class . $file_extension)) {
              include DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $class . $file_extension;
              include $module_directory . $class . $file_extension;
              $module = new $class();
              $msg    = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MODULE_REMOVED, preg_replace('/[^\w]/', '*', $_POST['module']), $admname);
              zen_record_admin_activity($msg, 'warning');
              zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED, $msg, STORE_NAME, EMAIL_FROM, ['EMAIL_MESSAGE_HTML'=>$msg], 'admin_settings_changed');
              $result = $module->remove();
          }
      }
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class, 'SSL'));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
  </head>
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <!-- body_text //-->
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
          <?php require_once(DIR_WS_MODULES . 'notificationsDisplay.php');?>
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULES; ?></th>
                <th class="dataTableHeadingContent">&nbsp;</th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
                <?php
                if ($set == 'payment') {
                  ?>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ORDERS_STATUS; ?></th>
                <?php } ?>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
                <?php
                $directory_array = [];
                if ($dir = @dir($module_directory)) {
                  while ($file = $dir->read()) {
                    if (!is_dir($module_directory . $file)) {
                      if (preg_match('~^[^\._].*\.php$~i', $file) > 0) {
                        $directory_array[] = $file;
                      }
                    }
                  }
                  sort($directory_array);
                  $dir->close();
                }

                $installed_modules = $temp_for_sort = [];
                for ($i = 0, $n = count($directory_array); $i < $n; $i++) {
                  $file = $directory_array[$i];
                  if (file_exists(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file)) {
                    include(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file);
                    include($module_directory . $file);
                    $class = substr($file, 0, strrpos($file, '.'));
                    if (class_exists($class)) {
                      $module = new $class();
                      // check if module passes the "check()" test (ie: enabled and valid, determined by each module individually)
                      if ($module->check() > 0) {
                        // determine sort orders (using up to 6 digits, then filename) and add to list of installed modules
                        $temp_for_sort[$file] = str_pad((int)$module->sort_order, 6, "0", STR_PAD_LEFT) . $file;
                        asort($temp_for_sort);
                        $installed_modules = array_flip($temp_for_sort);
                      }
                      if ((!isset($_GET['module']) || (isset($_GET['module']) && ($_GET['module'] == $class))) && !isset($mInfo)) {
                        $module_info = [
                          'code' => $module->code,
                          'title' => $module->title,
                          'description' => $module->description,
                          'status' => $module->check()];
                        $module_keys = $module->keys();
                        $keys_extra = [];
                        for ($j = 0, $k = count($module_keys); $j < $k; $j++) {
                          $key_value = $db->Execute("SELECT configuration_title, configuration_value, configuration_key, configuration_description, use_function, set_function
                                                     FROM " . TABLE_CONFIGURATION . "
                                                     WHERE configuration_key = '" . zen_db_input($module_keys[$j]) . "'");
                          if (!$key_value->EOF) {
                            $keys_extra[$module_keys[$j]]['title'] = $key_value->fields['configuration_title'];
                            $keys_extra[$module_keys[$j]]['value'] = $key_value->fields['configuration_value'];
                            $keys_extra[$module_keys[$j]]['description'] = $key_value->fields['configuration_description'];
                            $keys_extra[$module_keys[$j]]['use_function'] = $key_value->fields['use_function'];
                            $keys_extra[$module_keys[$j]]['set_function'] = $key_value->fields['set_function'];
                          }
                        }
                        $module_info['keys'] = $keys_extra;
                        $mInfo = new objectInfo($module_info);
                      }
                        if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code)) { // a module row is selected
                            if ($module->check() > 0) { // a module row is selected, module is installed, infoBox is showing module parameters
                                if (isset($_GET['action']) && $_GET['action'] === 'edit') { // a module row is selected, module is installed, infoBox is showing module Edit parameters ?>
                                    <tr id="defaultSelected" class="dataTableRowSelected">
                                <?php } else { // a module row is selected, module is installed, infoBox is only showing module parameters ?>
                                    <tr id="defaultSelected" class="dataTableRowSelected" style="cursor:pointer" onclick="document.location.href='<?php echo zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class . '&action=edit', 'SSL'); ?>'">
                                <?php }
                            } else { // a module row is selected, module is NOT installed ?>
                                <tr id="defaultSelected" class="dataTableRowSelected">
                            <?php }
                        } else { // module row is not selected: click to show install option or module parameters ?>
                            <tr class="dataTableRow" style="cursor:pointer" onclick="document.location.href='<?php echo zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class, 'SSL'); ?>'">
                        <?php } ?>
                  <td class="dataTableContent"><?php echo $module->title; ?></td>
                  <td class="dataTableContent"><?php echo(strstr($module->code, 'paypal') ? 'PayPal' : $module->code); ?></td>
                  <td class="dataTableContent text-right">
                      <?php if (is_numeric($module->sort_order)) {
                          echo $module->sort_order;
                      } ?>
                      <?php
                      // show current status
                      if ($set == 'payment' || $set == 'shipping') {
                        echo ((!empty($module->enabled) && is_numeric($module->sort_order)) ? zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') : ((empty($module->enabled) && is_numeric($module->sort_order)) ? zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') : zen_image(DIR_WS_IMAGES . 'icon_status_red.gif')));
                      } else {
                        echo (is_numeric($module->sort_order) ? zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') : zen_image(DIR_WS_IMAGES . 'icon_status_red.gif'));
                      }
                      ?>
                  </td>
                  <?php
                  if ($set == 'payment') {
                    if (!isset($module->order_status)) {
                        $module->order_status = 0;
                    }
                    
                    $orders_status_name = $db->Execute("SELECT orders_status_id, orders_status_name
                                                        FROM " . TABLE_ORDERS_STATUS . "
                                                        WHERE orders_status_id = " . (int)$module->order_status . "
                                                        AND language_id = " . (int)$_SESSION['languages_id']);
                    ?>
                    <td class="dataTableContent text-left">&nbsp;&nbsp;&nbsp;<?php echo(is_numeric($module->sort_order) ? (empty($orders_status_name->fields['orders_status_id']) ? TEXT_DEFAULT : $orders_status_name->fields['orders_status_name']) : ''); ?>&nbsp;&nbsp;&nbsp;</td>
                  <?php } ?>
                  <td class="dataTableContent text-right">
                      <?php
                      if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code)) {
                        echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif');
                      } else {
                        echo '<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class, 'SSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                      }
                      ?>&nbsp;</td>
                  </tr>
                <?php }
              } else {
                echo ERROR_MODULE_FILE_NOT_FOUND . DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file . '<br>';
              }
            }
            ksort($installed_modules);
            $check = $db->Execute("SELECT configuration_value
                                   FROM " . TABLE_CONFIGURATION . "
                                   WHERE configuration_key = '" . zen_db_input($module_key) . "'");

            if ($check->RecordCount() > 0) {
              if ($check->fields['configuration_value'] != implode(';', $installed_modules)) {
                $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                              SET configuration_value = '" . zen_db_input(implode(';', $installed_modules)) . "',
                                  last_modified = now()
                              WHERE configuration_key = '" . zen_db_input($module_key) . "'");
              }
            } else {
              $db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
                            VALUES ('Installed Modules', '" . zen_db_input($module_key) . "', '" . zen_db_input(implode(';', $installed_modules)) . "', 'This is automatically updated. No need to edit.', '6', '0', now())");
            }
            ?>
            </tbody>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">

          <?php
          $heading = [];
          $contents = [];
          switch ($action) {
            case 'remove':
              $heading[] = ['text' => '<h4>' . $mInfo->title . '</h4>'];

              $contents = ['form' => zen_draw_form('module_delete', FILENAME_MODULES, '&action=removeconfirm' . (isset($_GET['set']) ? '&set=' . $_GET['set'] : ''))];
              $contents[] = ['text' => zen_draw_hidden_field('set', (isset($_GET['set']) ? $_GET['set'] : ''))];
              $contents[] = ['text' => zen_draw_hidden_field('module', (isset($_GET['module']) ? $_GET['module'] : ''))];
              $contents[] = ['text' => '<h5>' . TEXT_DELETE_INTRO . '</h5>'];

              $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-danger" id="removeButton">' . IMAGE_MODULE_REMOVE . '</button>&nbsp;<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : ''), 'SSL') . '" class="btn btn-default" role="button" id="cancelButton">' . IMAGE_CANCEL . '</a>'];
              break;
            case 'edit':
              if (!$is_ssl_protected && in_array($_GET['module'], ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck'])) {
                break;
              }
              $keys = '';
              foreach($mInfo->keys as $key => $value) {
                $keys .= '<b>' . $value['title'] . '</b><br>' . $value['description'] . '<br>';
                if ($value['set_function']) {
                  eval('$keys .= ' . $value['set_function'] . "'" . $value['value'] . "', '" . $key . "');");
                } else {
                  $keys .= zen_draw_input_field('configuration[' . $key . ']', htmlspecialchars($value['value'], ENT_COMPAT, CHARSET, TRUE), 'class="form-control"');
                }
                $keys .= '<br><br>';
              }
              $keys = substr($keys, 0, strrpos($keys, '<br><br>'));
              $heading[] = ['text' => '<h4>' . $mInfo->title . '</h4>'];
              $contents = ['form' => zen_draw_form('modules', FILENAME_MODULES, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : '') . '&action=save', 'post', 'class="form-horizontal"', true)];
              if (ADMIN_CONFIGURATION_KEY_ON == 1) {
                $contents[] = ['text' => '<strong>Key: ' . $mInfo->code . '</strong><br>'];
              }
              $contents[] = ['text' => $keys];
              $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-danger" id="saveButton">' . IMAGE_UPDATE . '</button>&nbsp;<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : ''), 'SSL') . '" class="btn btn-default" role="button" id="cancelButton">' . IMAGE_CANCEL . '</a>'];
              break;
            default:
              $heading[] = ['text' => '<h4>' . $mInfo->title . '</h4>'];

              if ($mInfo->status == '1') {
                $keys = '';
                foreach($mInfo->keys as $value) {
                  $keys .= '<b>' . $value['title'] . '</b><br>';
                  if ($value['use_function']) {
                    $use_function = $value['use_function'];
                    if (preg_match('/->/', $use_function)) {
                      $class_method = explode('->', $use_function);
                      if (!class_exists($class_method[0])) {
                        include_once(DIR_WS_CLASSES . $class_method[0] . '.php');
                      }
                      if (!is_object(${$class_method[0]})) {
                        ${$class_method[0]} = new $class_method[0]();
                      }
                      $keys .= zen_call_function($class_method[1], $value['value'], ${$class_method[0]});
                    } else {
                      $keys .= zen_call_function($use_function, $value['value']);
                    }
                  } else {
                    $keys .= $value['value'];
                  }
                  $keys .= '<br><br>';
                }

                if (ADMIN_CONFIGURATION_KEY_ON == 1) {
                  $contents[] = ['text' => '<strong>Key: ' . $mInfo->code . '</strong><br>'];
                }
                $keys = substr($keys, 0, strrpos($keys, '<br><br>'));
                if (!(!$is_ssl_protected && in_array($mInfo->code, ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck']))) {
                  $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . (isset($_GET['module']) ? '&module=' . $_GET['module'] : '') . '&action=edit', 'SSL') . '" class="btn btn-primary" role="button" id="editButton">' . IMAGE_EDIT . '</a>'];
                } else {
                  $contents[] = ['align' => 'text-center', 'text' => TEXT_WARNING_SSL_EDIT];
                }
                $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=remove', 'SSL') . '" class="btn btn-warning" role="button" id="removeButton"><i class="fa fa-minus"></i> ' . IMAGE_MODULE_REMOVE . '</a>'];
                $contents[] = ['text' => '<br>' . $mInfo->description];
                $contents[] = ['text' => '<br>' . $keys];
              } else {
                if (!(!$is_ssl_protected && in_array($mInfo->code, ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck']))) {
                  $contents[] = ['align' => 'text-center', 'text' => zen_draw_form('install_module', FILENAME_MODULES, 'set=' . $set . '&action=install') . zen_draw_hidden_field('module', $mInfo->code) . '<button type="submit" id="installButton" class="btn btn-primary"><i class="fa fa-plus"></i> ' . IMAGE_MODULE_INSTALL . '</button></form>'];
                } else {
                  $contents[] = ['align' => 'text-center', 'text' => TEXT_WARNING_SSL_INSTALL];
                }
                $contents[] = ['text' => '<br>' . $mInfo->description];
              }
              break;
          }
          ?>
          <?php
          if ((zen_not_null($heading)) && (zen_not_null($contents))) {
            $box = new box();
            echo $box->infoBox($heading, $contents);
          }
          ?>
        </div>
      </div>
      <div class="row"><?php echo TEXT_MODULE_DIRECTORY . ' ' . $module_directory; ?></div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
