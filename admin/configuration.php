<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Modified in v1.6.0 $
 */

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'save':
        $cID = zen_db_prepare_input($_GET['cID']);
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$cID));
        }

        $configuration_value = zen_db_prepare_input($_POST['configuration_value']);
        // See if there are any configuration checks
        $checks = $db->Execute("SELECT val_function FROM " . TABLE_CONFIGURATION . " WHERE configuration_id = '" . (int)$cID . "'");
        if (!$checks->EOF && $checks->fields['val_function'] != NULL) {
           require_once('includes/functions/configuration_checks.php');
           zen_validate_configuration_entry($configuration_value, $checks->fields['val_function']);
        }

        $db->Execute("update " . TABLE_CONFIGURATION . "
                      set configuration_value = '" . zen_db_input($configuration_value) . "',
                          last_modified = now() where configuration_id = '" . (int)$cID . "'");

        $result = $db->Execute("select configuration_key from " . TABLE_CONFIGURATION . " where configuration_id=" . (int)$cID . " LIMIT 1");
        zen_record_admin_activity('Configuration setting changed for ' . $result->fields['configuration_key'] . ': ' . $configuration_value, 'warning');
        $zco_notifier->notify('ADMIN_CONFIGURATION_SETTING_CHANGE', $result->fields['configuration_key'], $configuration_value);

        // set the WARN_BEFORE_DOWN_FOR_MAINTENANCE to false if DOWN_FOR_MAINTENANCE = true
        if ( (WARN_BEFORE_DOWN_FOR_MAINTENANCE == 'true') && (DOWN_FOR_MAINTENANCE == 'true') ) {
        $db->Execute("update " . TABLE_CONFIGURATION . "
                      set configuration_value = 'false', last_modified = '" . NOW . "'
                      where configuration_key = 'WARN_BEFORE_DOWN_FOR_MAINTENANCE'");
            }

        zen_redirect(zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$cID));
        break;
    }
  }

  $gID = (isset($_GET['gID'])) ? $_GET['gID'] : 1;
  $_GET['gID'] = $gID;
  $cfg_group = $db->Execute("select language_key as constant_name
                             from " . TABLE_ADMIN_PAGES . "
                             where page_params = 'gID=" . (int)$gID . "'");

if ($gID == 7) {
        $shipping_errors = '';
        if (zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == 'NONE' or zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == '') {
          $shipping_errors .= '<br />' . ERROR_SHIPPING_ORIGIN_ZIP;
        }
        if (zen_get_configuration_key_value('ORDER_WEIGHT_ZERO_STATUS') == '1' and !defined('MODULE_SHIPPING_FREESHIPPER_STATUS')) {
          $shipping_errors .= '<br />' . ERROR_ORDER_WEIGHT_ZERO_STATUS;
        }
        if (defined('MODULE_SHIPPING_USPS_STATUS') and (MODULE_SHIPPING_USPS_USERID=='NONE' or MODULE_SHIPPING_USPS_SERVER == 'test')) {
          $shipping_errors .= '<br />' . ERROR_USPS_STATUS;
        }
        if ($shipping_errors != '') {
          $messageStack->add(ERROR_SHIPPING_CONFIGURATION . $shipping_errors, 'caution');
        }
}
require_once('includes/template/common/tplHtmlHeadLegacy.php');
require_once('includes/template/common/tplHtmlHead.php');

?>
</head>
<body  class="skin-blue-light">
<!-- header //-->
<?php require('includes/template/common/tplHeader.php'); ?>
<!-- header_eof //-->

<!-- body //-->
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
                <span class="pageHeading"><?php echo constant($cfg_group->fields['constant_name']); ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">

                <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                        <td class="dataTableHeadingContent"
                            width="55%"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></td>
                        <td class="dataTableHeadingContent"
                            align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $result = $db->Execute("select configuration_id, configuration_title, configuration_value, configuration_key,
                                        use_function from " . TABLE_CONFIGURATION . "
                                        where configuration_group_id = '" . (int)$gID . "'
                                        order by sort_order");
  foreach ($result as $config_entry) {
    if (zen_not_null($config_entry['use_function'])) {
      $use_function = $config_entry['use_function'];
      if (preg_match('/->/', $use_function)) {
        $class_method = explode('->', $use_function);
        if (!is_object(${$class_method[0]})) {
          include(DIR_WS_CLASSES . $class_method[0] . '.php');
          ${$class_method[0]} = new $class_method[0]();
        }
        $cfgValue = zen_call_function($class_method[1], $config_entry['configuration_value'], ${$class_method[0]});
      } else {
        $cfgValue = zen_call_function($use_function, $config_entry['configuration_value']);
      }
    } else {
      $cfgValue = $config_entry['configuration_value'];
    }

    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $config_entry['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cfg_extra = $db->Execute("select configuration_key, configuration_description, date_added,
                                        last_modified, use_function, set_function
                                 from " . TABLE_CONFIGURATION . "
                                 where configuration_id = '" . (int)$config_entry['configuration_id'] . "'");
      $cInfo_array = array_merge($config_entry, $cfg_extra->fields);
      $cInfo = new objectInfo($cInfo_array);
    }

    if ( (isset($cInfo) && is_object($cInfo)) && ($config_entry['configuration_id'] == $cInfo->configuration_id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $config_entry['configuration_id'] . '&action=edit') . '\'">' . "\n";
    }
?>
<?php
   // multilanguage support:
   // For example, in admin/includes/languages/spanish/configuration.php
   // define('CFGTITLE_STORE_NAME', 'Nombre de la Tienda');
   // define('CFGDESC_STORE_NAME', 'El nombre de mi tienda');
    if (defined('CFGTITLE_' . $config_entry['configuration_key'])) {
      $config_entry['configuration_title'] = constant('CFGTITLE_' . $config_entry['configuration_key']);
    }
    if (defined('CFGDESC_' . $config_entry['configuration_key'])) {
      $config_entry['configuration_description'] = constant('CFGDESC_' . $config_entry['configuration_key']);
    }
?>
                <td class="dataTableContent"><?php echo $config_entry['configuration_title']; ?></td>
                <td class="dataTableContent"><?php echo htmlspecialchars($cfgValue, ENT_COMPAT, CHARSET, TRUE); ?></td>
                        <td class="dataTableContent"
                            align="right"><?php if ((isset($cInfo) && is_object($cInfo)) && ($config_entry['configuration_id'] == $cInfo->configuration_id)) {
                                echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                            } else {
                                echo '<a href="' . zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $config_entry['configuration_id']) . '" name="link_' . $config_entry['configuration_key'] . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                            } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
                </table>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
<?php
  $heading = array();
  $contents = array();

    // Translation for contents
    if (defined('CFGTITLE_' . $cInfo->configuration_key)) {
      $cInfo->configuration_title = constant('CFGTITLE_' . $cInfo->configuration_key);
    }
    if (defined('CFGDESC_' . $cInfo->configuration_key)) {
      $cInfo->configuration_description = constant('CFGDESC_' . $cInfo->configuration_key);
    }

  switch ($action) {
    case 'edit':
      $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');

      if ($cInfo->set_function) {
        eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value, ENT_COMPAT, CHARSET, TRUE) . '");');
      } else {
                            $value_field = zen_draw_input_field('configuration_value', htmlspecialchars($cInfo->configuration_value, ENT_COMPAT, CHARSET, TRUE), 'size="60" class="cfgInput" autofocus');
      }

      $contents = array('form' => zen_draw_form('configuration', FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=save'));
      if (ADMIN_CONFIGURATION_KEY_ON == 1) {
        $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br />');
      }
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br><b>' . $cInfo->configuration_title . '</b><br>' . $cInfo->configuration_description . '<br>' . $value_field);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE, 'name="submit' . $cInfo->configuration_key . '"') . '&nbsp;<a href="' . zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');
        if (ADMIN_CONFIGURATION_KEY_ON == 1) {
          $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br />');
        }

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_admin_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
        $contents[] = array('text' => '<br>' . $cInfo->configuration_description);
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($cInfo->date_added));
        if (zen_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($cInfo->last_modified));
      }
      break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    $box = new box;
    echo $box->infoBox($heading, $contents);
  }
?>
            </div>
        </div>
    </div>

<!-- body_eof //-->

<!-- footer //-->
<?php require('includes/template/common/tplFooter.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
