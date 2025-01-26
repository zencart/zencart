<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piloujp 2024 Sep 25 Modified in v2.1.0-beta1 $
 */
use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\ModuleFinder;

require 'includes/application_top.php';

if (file_exists(DIR_FS_CATALOG . 'includes/classes/dbencdata.php')) {
    require_once DIR_FS_CATALOG . 'includes/classes/dbencdata.php';
}

$set = $_GET['set'] ?? $_POST['set'] ?? '';

$is_ssl_protected = strpos(HTTP_SERVER, 'https') === 0;

switch ($set) {
    case 'shipping':
        $module_type = 'shipping';
        $module_key = 'MODULE_SHIPPING_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_SHIPPING);
        $shipping_errors = '';
        if (zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') === 'NONE' || zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') === '') {
            $shipping_errors .= '<br>' . ERROR_SHIPPING_ORIGIN_ZIP;
        }
        if (zen_get_configuration_key_value('ORDER_WEIGHT_ZERO_STATUS') === '1' && (!defined('MODULE_SHIPPING_FREESHIPPER_STATUS') || MODULE_SHIPPING_FREESHIPPER_STATUS !== 'True')) {
            $shipping_errors .= '<br>' . ERROR_ORDER_WEIGHT_ZERO_STATUS;
        }
        if ($shipping_errors !== '') {
            $messageStack->add(ERROR_SHIPPING_CONFIGURATION . $shipping_errors, 'caution');
        }
        break;
    case 'ordertotal':
        $module_type = 'order_total';
        $module_key = 'MODULE_ORDER_TOTAL_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_ORDER_TOTAL);
        break;
    case 'payment':
    default:
        $module_type = 'payment';
        $module_key = 'MODULE_PAYMENT_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_PAYMENT);
        break;
}

$moduleFinder = new ModuleFinder($module_type, new Filesystem());
$modules_found = $moduleFinder->findFromFilesystem($installedPlugins);

$nModule = $_GET['module'] ?? null;
$notificationType = $module_type . (($nModule) ? '-' . $nModule : '') ;

$notifications = new AdminNotifications();
$availableNotifications = $notifications->getNotifications($notificationType, $_SESSION['admin_id']);

$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (!empty($action)) {
    $admname = '{' . preg_replace('/[^\w]/', '*', zen_get_admin_name()) . '[' . (int)$_SESSION['admin_id'] . ']}';
    switch ($action) {
        case 'save':
            $class = basename($_GET['module']);
            if (!$is_ssl_protected && in_array($class, ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck'])) {
                break;
            }
            foreach($_POST['configuration'] as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                    $value = preg_replace('/, --none--/', '', $value);
                }
                if (function_exists('dbenc_encrypt') && function_exists('dbenc_is_encrypted_value_key') && dbenc_is_encrypted_value_key($key)) {
                    $value = dbenc_encrypt($value);
                }

                // See if there are any configuration checks
                $checks = $db->Execute("SELECT configuration_title, val_function FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = '" . $key . "'");
                if (!$checks->EOF && $checks->fields['val_function'] !== null) {
                    require_once 'includes/functions/configuration_checks.php';
                    if (!zen_validate_configuration_entry($value, $checks->fields['val_function'], $checks->fields['configuration_title'])) {
                        zen_redirect(zen_href_link(FILENAME_MODULES, 'set=' . $_GET['set'] . '&module=' . $_GET['module'] . '&action=edit'));
                    }
                }
                $db->Execute(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_value = '" . zen_db_input($value) . "'
                      WHERE configuration_key = '" . zen_db_input($key) . "'
                      LIMIT 1");
            }
            $msg = sprintf(
                TEXT_EMAIL_MESSAGE_ADMIN_SETTINGS_CHANGED,
                preg_replace('/[^\w]/', '*', (!empty($_GET['module']) ? $_GET['module'] : (!empty($_GET['set']) ? $_GET['set'] : 'UNKNOWN'))),
                $admname
            );
            zen_record_admin_activity($msg, 'warning');
            zen_mail(
                STORE_NAME,
                STORE_OWNER_EMAIL_ADDRESS,
                TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED,
                $msg,
                STORE_NAME,
                EMAIL_FROM,
                ['EMAIL_MESSAGE_HTML' => nl2br($msg, false)],
                'admin_settings_changed'
            );
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=' . $set . (!empty($_GET['module']) ? '&module=' . $_GET['module'] : ''), 'SSL'));
            break;

        case 'install':
            $result = 'failed';
            $file_extension = pathinfo($PHP_SELF, PATHINFO_EXTENSION);
            $class = basename($_POST['module']);
            $class_file = $class . '.' . $file_extension;
            if (!$is_ssl_protected && in_array($class, ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck'])) {
                break;
            }

            if (in_array($class_file, array_keys($modules_found))) {
                if ($languageLoader->loadModuleLanguageFile($class_file, $module_type)) {
                    require DIR_FS_CATALOG . $modules_found[$class_file] . $class_file;
                    $module = new $class();
                    $msg = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MODULE_INSTALLED, preg_replace('/[^\w]/', '*', $_POST['module']), $admname);
                    zen_record_admin_activity($msg, 'warning');
                    zen_mail(
                        STORE_NAME,
                        STORE_OWNER_EMAIL_ADDRESS,
                        TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED,
                        $msg,
                        STORE_NAME,
                        EMAIL_FROM,
                        ['EMAIL_MESSAGE_HTML' => nl2br($msg, false)],
                        'admin_settings_changed'
                    );
                    $result = $module->install();
                }
            }
            if ($result !== 'failed') {
                zen_redirect(zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class . '&action=edit', 'SSL'));
            }
            break;

        case 'removeconfirm':
            $file_extension = pathinfo($PHP_SELF, PATHINFO_EXTENSION);
            $class = basename($_POST['module']);
            $class_file = $class . '.' . $file_extension;
            if (in_array($class_file, array_keys($modules_found))) {
                if ($languageLoader->loadModuleLanguageFile($class_file, $module_type)) {
                    require DIR_FS_CATALOG . $modules_found[$class_file] . $class_file;
                    $module = new $class();
                    $msg = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MODULE_REMOVED, preg_replace('/[^\w]/', '*', $_POST['module']), $admname);
                    zen_record_admin_activity($msg, 'warning');
                    zen_mail(
                        STORE_NAME,
                        STORE_OWNER_EMAIL_ADDRESS,
                        TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED,
                        $msg,
                        STORE_NAME,
                        EMAIL_FROM,
                        ['EMAIL_MESSAGE_HTML' => nl2br($msg, false)],
                        'admin_settings_changed'
                    );
                    $result = $module->remove();
                }
            }
            zen_redirect(zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class, 'SSL'));
            break;
    }
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <!-- body_text //-->
      <h1><?= HEADING_TITLE ?></h1>
      <div class="row">
        <?php require_once DIR_WS_MODULES . 'notificationsDisplay.php'; ?>
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?= TABLE_HEADING_MODULES ?></th>
                <th class="dataTableHeadingContent">&nbsp;</th>
                <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_SORT_ORDER ?></th>
<?php
if ($set == 'payment') {
?>
                <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_ORDERS_STATUS ?></th>
<?php
}
?>
                <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_ACTION ?>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
<?php
$installed_modules = [];
$temp_for_sort = [];
$module_directory = DIR_FS_CATALOG . DIR_WS_MODULES . $module_type;
foreach ($modules_found as $module_name => $module_file_dir) {
    if (!$languageLoader->loadModuleLanguageFile($module_name, $module_type)) {
        echo ERROR_MODULE_FILE_NOT_FOUND . DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $module_name . '<br>';
        continue;
    }

    require DIR_FS_CATALOG . $module_file_dir . $module_name;
    $class = pathinfo($module_name, PATHINFO_FILENAME);
    if (!class_exists($class)) {
        continue;
    }

    $module = new $class();
    // check if module passes the "check()" test (ie: enabled and valid, determined by each module individually)
    if ($module->check() > 0) {
        // determine sort orders (using up to 6 digits, then filename) and add to list of installed modules
        $temp_for_sort[$module_name] = str_pad((int)$module->sort_order, 6, '0', STR_PAD_LEFT) . $module_name;
        asort($temp_for_sort);
        $installed_modules = array_flip($temp_for_sort);
    }
    if ((!isset($_GET['module']) || $_GET['module'] === $class) && !isset($mInfo)) {
        $module_info = [
            'code' => $module->code,
            'title' => $module->title,
            'description' => $module->description,
            'status' => $module->check(),
        ];

        $keys_extra = [];
        foreach ($module->keys() as $next_key) {
            $key_value = $db->Execute(
                "SELECT configuration_title AS `title`, configuration_value AS `value`,
                        configuration_description AS `description`, use_function, set_function
                   FROM " . TABLE_CONFIGURATION . "
                  WHERE configuration_key = '" . zen_db_input($next_key) . "'
                  LIMIT 1");
            if (!$key_value->EOF) {
                $keys_extra[$next_key] = $key_value->fields;
            }
        }
        $module_info['keys'] = $keys_extra;
        if (method_exists($module, 'get_configuration_errors')) {
            $module_info['configuration_errors'] = $module->get_configuration_errors();
        }

        $mInfo = new objectInfo($module_info);
    }

    if (isset($mInfo) && is_object($mInfo) && $class === $mInfo->code) { // a module row is selected
        if ($module->check() > 0) { // a module row is selected, module is installed, infoBox is showing module parameters
            if (isset($_GET['action']) && $_GET['action'] === 'edit') { // a module row is selected, module is installed, infoBox is showing module Edit parameters
?>
              <tr id="defaultSelected" class="dataTableRowSelected">
<?php
            } else { // a module row is selected, module is installed, infoBox is only showing module parameters
?>
              <tr id="defaultSelected" class="dataTableRowSelected" style="cursor:pointer" onclick="document.location.href='<?= zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class . '&action=edit', 'SSL') ?>'">
<?php
            }
        } else { // a module row is selected, module is NOT installed
?>
              <tr id="defaultSelected" class="dataTableRowSelected">
<?php
        }
    } else { // module row is not selected: click to show install option or module parameters
?>
              <tr class="dataTableRow" style="cursor:pointer" onclick="document.location.href='<?= zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class, 'SSL') ?>'">
<?php
    }
?>
                  <td class="dataTableContent"><?= $module->title ?></td>
                  <td class="dataTableContent"><?= $module->code ?></td>
                  <td class="dataTableContent text-right">
<?php
    if (is_numeric($module->sort_order)) {
        echo $module->sort_order;
    }

    // show current status
    if ($set === 'payment' || $set === 'shipping') {
        echo ((!empty($module->enabled) && is_numeric($module->sort_order)) ? zen_icon('status-green') : ((empty($module->enabled) && is_numeric($module->sort_order)) ? zen_icon('status-yellow') : zen_icon('status-red')));
    } else {
        echo (is_numeric($module->sort_order) ? zen_icon('status-green') : zen_icon('status-red'));
    }
?>
                  </td>
<?php
    if ($set === 'payment') {
        if (!isset($module->order_status)) {
            $module->order_status = 0;
        }

        $orders_status_name = $db->Execute(
            "SELECT orders_status_id, orders_status_name
               FROM " . TABLE_ORDERS_STATUS . "
              WHERE orders_status_id = " . (int)$module->order_status . "
                AND language_id = " . (int)$_SESSION['languages_id']
        );
?>
                  <td class="dataTableContent text-center">
                    <?= (is_numeric($module->sort_order) ? (empty($orders_status_name->fields['orders_status_id']) ? TEXT_DEFAULT : $orders_status_name->fields['orders_status_name']) : '') ?>
                  </td>
<?php
    }
?>
                  <td class="dataTableContent text-right">
<?php
    if (isset($mInfo) && is_object($mInfo) && $class === $mInfo->code) {
        echo zen_icon('caret-right', '', '2x', true);
        $_GET['module'] = $_GET['module'] ?? $mInfo->code;
    } else {
        echo
            '<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class, 'SSL') . '" data-toggle="tooltip" title="' . IMAGE_ICON_INFO . '" role="button">' .
                zen_icon('circle-info', '', '2x', true, false) .
            '</a>';
    }
?>
                    &nbsp;
                  </td>
              </tr>
<?php
}

ksort($installed_modules);
$installed_modules_list = zen_db_input(implode(';', $installed_modules));
$check = $db->Execute(
    "SELECT configuration_value
       FROM " . TABLE_CONFIGURATION . "
      WHERE configuration_key = '" . zen_db_input($module_key) . "'
      LIMIT 1"
);

if (!$check->EOF) {
    if ($check->fields['configuration_value'] !== implode(';', $installed_modules)) {
        $db->Execute(
            "UPDATE " . TABLE_CONFIGURATION . "
                SET configuration_value = '" . $installed_modules_list . "',
                    last_modified = now()
              WHERE configuration_key = '" . zen_db_input($module_key) . "'
              LIMIT 1"
        );
    }
} else {
  $db->Execute(
    "INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
     VALUES
        ('Installed Modules', '" . zen_db_input($module_key) . "', '" . $installed_modules_list . "', 'This is automatically updated. No need to edit.', 6, 0, now())");
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
        $contents[] = ['text' => zen_draw_hidden_field('set', $_GET['set'] ?? '')];
        $contents[] = ['text' => zen_draw_hidden_field('module', $_GET['module'] ?? '')];
        $contents[] = ['text' => '<h5>' . TEXT_DELETE_INTRO . '</h5>'];

        $contents[] = [
            'align' => 'text-center',
            'text' =>
                '<button type="submit" class="btn btn-danger" id="removeButton">' .
                    IMAGE_MODULE_REMOVE .
                '</button>&nbsp;' .
                '<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : ''), 'SSL') . '" class="btn btn-default" role="button" id="cancelButton">' .
                    IMAGE_CANCEL .
                '</a>'
        ];
        break;

    case 'edit':
        if (!$is_ssl_protected && in_array($_GET['module'], ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck'])) {
            break;
        }
        $keys = '';
        foreach ($mInfo->keys as $key => $value) {
            $displayKey = '';
            if (ADMIN_CONFIGURATION_KEY_ON === '1') {
                $displayKey = 'Key: ' . $key . '<br>';
            }
            $keys .= '<b>' . $displayKey . zen_lookup_admin_menu_language_override('configuration_key_title', $key, $value['title']) . '</b><br>' . zen_lookup_admin_menu_language_override('configuration_key_description', $key, $value['description']) . '<br>';
            if ($value['set_function']) {
                eval('$keys .= ' . $value['set_function'] . '"' . zen_output_string($value['value'], ['"' => '&quot;', '`' => 'null;return;exit;']) . '", "' . $key . '");');
            } else {
                $keys .= zen_draw_input_field('configuration[' . $key . ']', htmlspecialchars($value['value'], ENT_COMPAT, CHARSET, true), 'class="form-control"');
            }
            $keys .= '<br><br>';
        }
        $keys = substr($keys, 0, strrpos($keys, '<br><br>'));
        $heading[] = ['text' => '<h4>' . $mInfo->title . '</h4>'];
        $contents = [
            'form' => zen_draw_form('modules', FILENAME_MODULES, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : '') . '&action=save', 'post', 'class="form-horizontal"', true)
        ];
        if (ADMIN_CONFIGURATION_KEY_ON === '1') {
            $contents[] = ['text' => '<strong>Module code: ' . $mInfo->code . '</strong><br>'];
        }
        $contents[] = ['text' => $keys];
        $contents[] = [
            'align' => 'text-center',
            'text' =>
                '<button type="submit" class="btn btn-danger" id="saveButton">' .
                    IMAGE_UPDATE .
                '</button>&nbsp;' .
                '<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : ''), 'SSL') . '" class="btn btn-default" role="button" id="cancelButton">' .
                    IMAGE_CANCEL .
                '</a>'
        ];
        break;

    default:
        $heading[] = ['text' => '<h4>' . $mInfo->title . '</h4>'];

        $help_button = [];
        $file_extension = pathinfo($PHP_SELF, PATHINFO_EXTENSION);
        $class = pathinfo($_GET['module'], PATHINFO_FILENAME);
        if (file_exists($module_directory . $class . $file_extension)) {
            if ($languageLoader->loadModuleDefinesFromFile('/modules/', $_SESSION['language'],  $module_type, $class . $file_extension)) {
                include_once $module_directory . $class . $file_extension;
                $module = new $class();
                if (method_exists($module, 'help')) {
                    $help_text = $module->help();
                    if (isset($help_text['link'])) {
                        $help_button = ['align' => 'text-center', 'text' => '<a href="' . $help_text['link'] . '" target="_blank" rel="noreferrer noopener">' . '<button type="submit" class="btn btn-primary " id="helpButton">' . IMAGE_MODULE_HELP. '</button></a>'];
                    } elseif (isset($help_text['body'])) {
                        $help_title = $module->title;
                        $help_button = ['align' => 'text-center', 'text' => '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#helpModal">' . IMAGE_MODULE_HELP . '</button>'];
                    }
                }
            }
        }

        if ($mInfo->status == '1') {
            $keys = '';
            foreach ($mInfo->keys as $key => $value) {
                $displayKey = '';
                if (ADMIN_CONFIGURATION_KEY_ON === '1') {
                    $displayKey = 'Key: ' . $key . '<br>';
                }
                $keys .= '<b>'. $displayKey . zen_lookup_admin_menu_language_override('configuration_key_title', $key, $value['title']) . '</b><br>';
                if ($value['use_function']) {
                    $use_function = $value['use_function'];
                    if (strpos($use_function, '->') !== false) {
                        $class_method = explode('->', $use_function);
                        if (!class_exists($class_method[0])) {
                            include_once DIR_WS_CLASSES . $class_method[0] . '.php';
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

            if (ADMIN_CONFIGURATION_KEY_ON === '1') {
                $contents[] = ['text' => '<strong>Module code: ' . $mInfo->code . '</strong><br>'];
            }
            $keys = substr($keys, 0, strrpos($keys, '<br><br>'));
            if (!$is_ssl_protected && in_array($mInfo->code, ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck'])) {
                $contents[] = ['align' => 'text-center', 'text' => TEXT_WARNING_SSL_EDIT];
            } else {
                $contents[] = [
                    'align' => 'text-center', 'text' =>
                        '<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . (isset($_GET['module']) ? '&module=' . $_GET['module'] : '') . '&action=edit', 'SSL') . '" class="btn btn-primary" role="button" id="editButton">' .
                            IMAGE_EDIT .
                        '</a>'
                ];
            }

            $contents[] = [
                'align' => 'text-center',
                'text' =>
                    '<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=remove', 'SSL') . '" class="btn btn-warning" role="button" id="removeButton">' .
                        '<i class="fa-solid fa-minus"></i> ' .
                        IMAGE_MODULE_REMOVE .
                    '</a>'
            ];
            if (!empty($help_button)) {
                $contents[] = $help_button;
            }
            $contents[] = ['text' => '<br>' . $mInfo->description];

            if (!empty($mInfo->configuration_errors)) {
                $contents[] = ['text' => $mInfo->configuration_errors . '<br>'];  // warnings, etc.
            }
            $contents[] = ['text' => '<br>' . $keys];
        } else {
            if (!$is_ssl_protected && in_array($mInfo->code, ['paypaldp', 'authorizenet_aim', 'authorizenet_echeck'])) {
                $contents[] = ['align' => 'text-center', 'text' => TEXT_WARNING_SSL_INSTALL];
            } else {
                $contents[] = ['align' => 'text-center', 'text' => zen_draw_form('install_module', FILENAME_MODULES, 'set=' . $set . '&action=install') . zen_draw_hidden_field('module', $mInfo->code) . '<button type="submit" id="installButton" class="btn btn-primary"><i class="fa-solid fa-plus"></i> ' . IMAGE_MODULE_INSTALL . '</button></form>'];
            }
            if (!empty($help_button)) {
                $contents[] = $help_button;
            }
            $contents[] = ['text' => '<br>' . $mInfo->description];
        }
        break;
}

if (!empty($heading) && !empty($contents)) {
    $box = new box();
    echo $box->infoBox($heading, $contents);
}
?>
        </div>
      </div>
      <div class="row"><?= TEXT_MODULE_DIRECTORY . ' ' . $module_directory ?></div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->

<?php
if (!empty($help_text['body'])) {
?>
<div id="helpModal" class="modal fade">
      <div class="modal-dialog">
           <div class="modal-content">
                <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal">&times;</button>
                     <h4 class="modal-title"><?= $help_title . ' ' . IMAGE_MODULE_HELP ?></h4>
                </div>
                <div class="modal-body">
                    <?= $help_text['body'] ?>
                </div>
                <div class="modal-footer">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
           </div>
      </div>
</div>
<?php
}
?>
  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
