<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piloujp 2025 Sep 30 Modified in v2.2.0 $
 */
require 'includes/application_top.php';

$gID = (int)($_GET['gID'] ?? 1);
$_GET['gID'] = $gID;

$action = $_GET['action'] ?? '';

if (!empty($action)) {
    switch ($action) {
        case 'saveall':
            $counter = 0;
            // Handle radio fields (configuration[cfg_XX])
            if (is_array($_POST['configuration'] ?? false)) {
                foreach ($_POST['configuration'] as $key => $value) {
                    if (str_starts_with($key, 'cfg_')) {
                        $config_id = (int)substr($key, 4);
                        $configuration_value = zen_db_prepare_input($value);

                        // See if there are any configuration checks
                        $checks = $db->Execute("SELECT val_function FROM " . TABLE_CONFIGURATION . " WHERE configuration_id = " . $config_id, 1);
                        if (!$checks->EOF && $checks->fields['val_function'] != NULL) {
                            require_once 'includes/functions/configuration_checks.php';
                            if (!zen_validate_configuration_entry($configuration_value, $checks->fields['val_function'])) {
                                zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID']));
                            }
                        }

                        if (isset($_POST['orig_' . $key]) && $_POST['orig_' . $key] === $configuration_value) {
                            continue; // No change, skip update
                        }
                        $db->Execute(
                            "UPDATE " . TABLE_CONFIGURATION . "
                                SET configuration_value = '" . zen_db_input($configuration_value) . "',
                                    last_modified = now()
                              WHERE configuration_id = " . $config_id
                        );
                        $counter++;
                    }

                    $result = $db->Execute(
                        "SELECT configuration_key
                           FROM " . TABLE_CONFIGURATION . "
                          WHERE configuration_id = " . $config_id . "
                          LIMIT 1"
                    );
                    zen_record_admin_activity('Configuration setting changed for ' . $result->fields['configuration_key'] . ': ' . $configuration_value, 'warning');

                    // Send a notifier that a configuration change has been made
                    $zco_notifier->notify('NOTIFY_ADMIN_CONFIG_CHANGE', $result->fields['configuration_key']);
                }
            }

            // Handle text fields (cfg_XX)
            foreach ($_POST as $key => $value) {
                if (str_starts_with($key, 'cfg_') && !is_array($value)) {
                    $config_id = (int)substr($key, 4);
                    $configuration_value = zen_db_prepare_input($value);

                    // See if there are any configuration checks
                    $checks = $db->Execute("SELECT val_function FROM " . TABLE_CONFIGURATION . " WHERE configuration_id = " . $config_id, 1);
                    if (!$checks->EOF && $checks->fields['val_function'] != NULL) {
                        require_once 'includes/functions/configuration_checks.php';
                        if (!zen_validate_configuration_entry($configuration_value, $checks->fields['val_function'])) {
                            zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID']));
                        }
                    }

                    if (isset($_POST['orig_' . $key]) && $_POST['orig_' . $key] === $configuration_value) {
                        continue; // No change, skip update
                    }
                    $db->Execute(
                        "UPDATE " . TABLE_CONFIGURATION . "
                            SET configuration_value = '" . zen_db_input($configuration_value) . "',
                                last_modified = now()
                          WHERE configuration_id = " . $config_id
                    );
                    $counter++;

                    $result = $db->Execute(
                        "SELECT configuration_key
                           FROM " . TABLE_CONFIGURATION . "
                          WHERE configuration_id = " . $config_id . "
                          LIMIT 1"
                    );
                    zen_record_admin_activity('Configuration setting changed for ' . $result->fields['configuration_key'] . ': ' . $configuration_value, 'warning');

                    // Send a notifier that a configuration change has been made
                    $zco_notifier->notify('NOTIFY_ADMIN_CONFIG_CHANGE', $result->fields['configuration_key']);
                }
            }

            // set the WARN_BEFORE_DOWN_FOR_MAINTENANCE to false if DOWN_FOR_MAINTENANCE = true
            if (zen_get_configuration_key_value('WARN_BEFORE_DOWN_FOR_MAINTENANCE') === 'true' && zen_get_configuration_key_value('DOWN_FOR_MAINTENANCE') === 'true') {
                $db->Execute(
                    "UPDATE " . TABLE_CONFIGURATION . "
                        SET configuration_value = 'false',
                            last_modified = now()
                      WHERE configuration_key = 'WARN_BEFORE_DOWN_FOR_MAINTENANCE'
                      LIMIT 1"
                );
            }

            $messageStack->add_session(sprintf(TEXT_CONFIG_SAVED_SUCCESS, $counter), 'success');

            zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID']));
            break;

        default:
            break;
    }
}

$cfg_group = $db->Execute(
    "SELECT configuration_group_title
       FROM " . TABLE_CONFIGURATION_GROUP . "
      WHERE configuration_group_id = " . (int)$gID . "
      LIMIT 1"
);

if ($cfg_group->EOF) {
    $cfg_group->fields['configuration_group_title'] = '';
} else {
    // multilanguage support:
    // For example, in admin/includes/languages/spanish/lang.configuration.php
    // define('CFG_GRP_TITLE_MY_STORE', 'Mi Tienda');
    $cfg_group->fields['configuration_group_title'] = zen_lookup_admin_menu_language_override(
        'configuration_group_title',
        $cfg_group->fields['configuration_group_title'],
        $cfg_group->fields['configuration_group_title']
    );
}

if ($gID === 7) {
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
} elseif ($gID === 6) {
    if (!zen_is_superuser()) {
        zen_redirect(zen_href_link(FILENAME_DENIED, '', 'SSL'));
    }
} elseif ($gID === 5) {
    if (zen_get_configuration_key_value('CUSTOMERS_ACTIVATION_REQUIRED') === 'true') {
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '3' WHERE configuration_key = 'CUSTOMERS_APPROVAL_AUTHORIZATION'", 1);
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'customers_authorization' WHERE configuration_key = 'CUSTOMERS_AUTHORIZATION_FILENAME'", 1);
    }
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <style>
        .row-hover:hover {
            background-color: #f8f9fa; /* Bootstrap's .table-hover color */
        }
        .save-button {
            position: fixed;
            bottom: 15px;
            right: 15px;
        }
    	.form-horizontal hr {
			margin: 0;
			border: 0;
			border-top: 1px solid #bfbfbf;
} 
@media (max-width: 767px) {        
        .form-control {
            margin-bottom: .5rem; 
        }
}
    </style>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<!-- body //-->
<div class="container-fluid">
    <h1><?= $cfg_group->fields['configuration_group_title'] ?></h1>
<?php
$query =
    "SELECT configuration_id, configuration_title, configuration_description, configuration_value, configuration_key, use_function, set_function
       FROM " . TABLE_CONFIGURATION . "
      WHERE configuration_group_id = " . (int)$gID;
$default_sort = true;
if (defined('CONFIGURATION_MENU_ENTRIES_TO_SORT_BY_NAME') && !empty(CONFIGURATION_MENU_ENTRIES_TO_SORT_BY_NAME)) {
    $sorted_menus = explode(',', CONFIGURATION_MENU_ENTRIES_TO_SORT_BY_NAME);
    if (in_array($gID, $sorted_menus)) {
        $default_sort = false;
    }
}
if ($default_sort) {
    $query .= " ORDER BY sort_order";
} else {
    $query .= " ORDER BY configuration_title";
}
$configuration = $db->Execute($query);
echo zen_draw_form('configuration', FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&action=saveall', 'post', 'class="form-horizontal"');
?>
    <div class="row font-weight-bold bg-primary py-3">
        <div class="col-md-3"><?= TABLE_HEADING_CONFIGURATION_TITLE ?></div>
        <div class="col-md-3"><?= TABLE_HEADING_CONFIGURATION_VALUE ?></div>
        <div class="col-md-6"></div>
    </div>
<?php
foreach ($configuration as $item) {
    $fieldName = 'cfg_' . $item['configuration_id'];
    $cfgValue = htmlspecialchars($item['configuration_value'], ENT_COMPAT, CHARSET, true);

    if (defined('CFGTITLE_' . $item['configuration_key'])) {
        $item['configuration_title'] = constant('CFGTITLE_' . $item['configuration_key']);
    }
    if (defined('CFGDESC_' . $item['configuration_key'])) {
        $item['configuration_description'] = constant('CFGDESC_' . $item['configuration_key']);
    }

?>
    <div class="row row-hover align-items-center py-2">
        <div class="col-md-3">
            <?php
            echo '<strong>' . $item['configuration_title'] . '</strong>';
            if (ADMIN_CONFIGURATION_KEY_ON == 1) {
                echo '<br>Key: ' . $item['configuration_key'];
            }
            ?>
        </div>
        <div class="col-md-3">
            <?php
            if (!empty($item['set_function'])) {
                $set_function = $item['set_function'] . '\'' . $cfgValue . '\', \'' . $fieldName . '\')';
                eval('$inputField = ' . $set_function . ';');
                echo $inputField;
            } else {
                echo '<input type="text" name="' . $fieldName . '" value="' . htmlspecialchars($cfgValue, ENT_COMPAT, CHARSET, true) . '" class="form-control">';
            }
            echo '<input type="hidden" name="orig_' . $fieldName . '" value="' . htmlspecialchars($cfgValue, ENT_COMPAT, CHARSET, true) . '">';
            ?>
        </div>
        <div class="col-md-6 bg-info p-3"><?= $item['configuration_description'] ?></div>
    </div>
    <hr>
<?php
}
?>

    <div class="save-button">
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?= BUTTON_SAVE_ALL ?></button>
    </div>
    <?= '</form>' ?>
</div>

<!-- body_eof //-->

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
