<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piloujp 2024 Aug 29 Modified in v2.1.0-alpha2 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (!empty($action)) {
  switch ($action) {
    case 'save':
      $cID = zen_db_prepare_input($_GET['cID']);

      $configuration_value = zen_db_prepare_input($_POST['configuration_value']);
        // See if there are any configuration checks
        $checks = $db->Execute("SELECT val_function FROM " . TABLE_CONFIGURATION . " WHERE configuration_id = " . (int)$cID);
        if (!$checks->EOF && $checks->fields['val_function'] != NULL) {
           require_once('includes/functions/configuration_checks.php');
           if (!zen_validate_configuration_entry($configuration_value, $checks->fields['val_function'])) {
              zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$_GET['cID'] . '&action=edit'));
           }
        }

      $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_value = '" . zen_db_input($configuration_value) . "',
                        last_modified = now()
                    WHERE configuration_id = " . (int)$cID);

      $result = $db->Execute("SELECT configuration_key
                              FROM " . TABLE_CONFIGURATION . "
                              WHERE configuration_id = " . (int)$cID . "
                              LIMIT 1");
      zen_record_admin_activity('Configuration setting changed for ' . $result->fields['configuration_key'] . ': ' . $configuration_value, 'warning');

      // Send a notifier that a configuration change has been made
      $zco_notifier->notify('NOTIFY_ADMIN_CONFIG_CHANGE', $result->fields['configuration_key']);

      // set the WARN_BEFORE_DOWN_FOR_MAINTENANCE to false if DOWN_FOR_MAINTENANCE = true
      if ((WARN_BEFORE_DOWN_FOR_MAINTENANCE == 'true') && (DOWN_FOR_MAINTENANCE == 'true')) {
        $db->Execute("UPDATE " . TABLE_CONFIGURATION . "
                      SET configuration_value = 'false',
                          last_modified = now()
                      WHERE configuration_key = 'WARN_BEFORE_DOWN_FOR_MAINTENANCE'");
      }

      zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$cID));
      break;
  }
}

$gID = (isset($_GET['gID'])) ? $_GET['gID'] : 1;
$_GET['gID'] = $gID;
$cfg_group = $db->Execute("SELECT configuration_group_title
                           FROM " . TABLE_CONFIGURATION_GROUP . "
                           WHERE configuration_group_id = " . (int)$gID);

if ($cfg_group->RecordCount() == 0) {
    $cfg_group->fields['configuration_group_title'] = '';
} else {
    // multilanguage support:
    // For example, in admin/includes/languages/spanish/lang.configuration.php
    // define('CFG_GRP_TITLE_MY_STORE', 'Mi Tienda');
    $cfg_group->fields['configuration_group_title'] = zen_lookup_admin_menu_language_override('configuration_group_title', $cfg_group->fields['configuration_group_title'], $cfg_group->fields['configuration_group_title']);
}

if ($gID == 7) {
  $shipping_errors = '';
  if (zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == 'NONE' or zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == '') {
    $shipping_errors .= '<br>' . ERROR_SHIPPING_ORIGIN_ZIP;
  }
  if (zen_get_configuration_key_value('ORDER_WEIGHT_ZERO_STATUS') == '1' && (!defined('MODULE_SHIPPING_FREESHIPPER_STATUS') || MODULE_SHIPPING_FREESHIPPER_STATUS != 'True')) {
    $shipping_errors .= '<br>' . ERROR_ORDER_WEIGHT_ZERO_STATUS;
  }
  if ($shipping_errors != '') {
    $messageStack->add(ERROR_SHIPPING_CONFIGURATION . $shipping_errors, 'caution');
  }
} else if ($gID == 6) {
  if (!zen_is_superuser()) {
     zen_redirect(zen_href_link(FILENAME_DENIED, '', 'SSL'));
  }
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
          <h1><?php echo $cfg_group->fields['configuration_group_title']; ?></h1>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">

          <table class="table table-hover" role="listbox">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT configuration_id, configuration_title, configuration_value, configuration_key, use_function
                                               FROM " . TABLE_CONFIGURATION . "
                                               WHERE configuration_group_id = " . (int)$gID;

                $default_sort = true;
                if (defined('CONFIGURATION_MENU_ENTRIES_TO_SORT_BY_NAME') && !empty(CONFIGURATION_MENU_ENTRIES_TO_SORT_BY_NAME)) {
                   $sorted_menus = explode(",", CONFIGURATION_MENU_ENTRIES_TO_SORT_BY_NAME);
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
                foreach ($configuration as $item) {
                  if (!empty($item['use_function'])) {
                    $use_function = $item['use_function'];
                    if (preg_match('/->/', $use_function)) {
                      $class_method = explode('->', $use_function);
                      if (!(isset(${$class_method[0]}) && is_object(${$class_method[0]}))) {
                        include(DIR_WS_CLASSES . $class_method[0] . '.php');
                        ${$class_method[0]} = new $class_method[0]();
                      }
                      $cfgValue = zen_call_function($class_method[1], $item['configuration_value'], ${$class_method[0]});
                    } else {
                      $cfgValue = zen_call_function($use_function, $item['configuration_value']);
                    }
                  } else {
                    $cfgValue = $item['configuration_value'];
                  }

                  if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $item['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                    $cfg_extra = $db->Execute("SELECT configuration_key, configuration_description, date_added, last_modified, use_function, set_function
                                               FROM " . TABLE_CONFIGURATION . "
                                               WHERE configuration_id = " . (int)$item['configuration_id']);
                    $cInfo_array = array_merge($item, $cfg_extra->fields);
                    $cInfo = new objectInfo($cInfo_array);
                  }

                  if ((isset($cInfo) && is_object($cInfo)) && ($item['configuration_id'] == $cInfo->configuration_id)) {
                    echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'" role="option" aria-selected="true">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $item['configuration_id'] . '&action=edit') . '\'" role="option" aria-selected="false">' . "\n";
                  }
                  ?>
                  <?php
                  // multilanguage support:
                  // For example, in admin/includes/languages/spanish/lang.configuration.php
                  // define('CFGTITLE_STORE_NAME', 'Nombre de la Tienda');
                  // define('CFGDESC_STORE_NAME', 'El nombre de mi tienda');
                  if (defined('CFGTITLE_' . $item['configuration_key'])) {
                    $item['configuration_title'] = constant('CFGTITLE_' . $item['configuration_key']);
                  }
                  if (defined('CFGDESC_' . $item['configuration_key'])) {
                    $item['configuration_description'] = constant('CFGDESC_' . $item['configuration_key']);
                  }
                  ?>
              <td class="dataTableContent"><?php echo $item['configuration_title']; ?></td>
              <td class="dataTableContent"><?php
                   $setting = htmlspecialchars($cfgValue, ENT_COMPAT, CHARSET, TRUE);
                   if (strlen($setting) > 40) {

                      echo htmlspecialchars(substr($cfgValue,0,35), ENT_COMPAT, CHARSET, TRUE) . "...";
                   } else {
                      echo $setting;
                   }
              ?></td>
              <td class="dataTableContent text-right">
                  <?php
                  if ((isset($cInfo) && is_object($cInfo)) && ($item['configuration_id'] == $cInfo->configuration_id)) {
                    echo zen_icon('caret-right', '', '2x', true);
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $item['configuration_id']) . '" id="link_' . $item['configuration_key'] . '">' . zen_icon('circle-info', IMAGE_ICON_INFO, '2x', true, false) . '</a>';
                  }
                  ?>&nbsp;</td>
              </tr>
              <?php
            }
            ?>
            </tbody>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
          <?php
          $heading = array();
          $contents = array();

          // Translation for contents
          if (isset($cInfo) && isset($cInfo->configuration_key) && defined('CFGTITLE_' . $cInfo->configuration_key)) {
            $cInfo->configuration_title = constant('CFGTITLE_' . $cInfo->configuration_key);
          }
          if (isset($cInfo) && isset($cInfo->configuration_key) && defined('CFGDESC_' . $cInfo->configuration_key)) {
            $cInfo->configuration_description = constant('CFGDESC_' . $cInfo->configuration_key);
          }

          switch ($action) {
            case 'edit':
              $heading[] = array('text' => '<h4>' . $cInfo->configuration_title . '</h4>');

              if ($cInfo->set_function) {
                eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value, ENT_COMPAT, CHARSET, TRUE) . '");');
              } else {
                $value_field = zen_draw_input_field('configuration_value', htmlspecialchars($cInfo->configuration_value, ENT_COMPAT, CHARSET, TRUE), 'size="60" class="cfgInput form-control" autofocus');
              }

              $contents = array('form' => zen_draw_form('configuration', FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=save', 'post', 'class="form-horizontal"'));
              if (ADMIN_CONFIGURATION_KEY_ON == 1) {
                $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br>');
              }
              $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
              $contents[] = array('text' => '<br><strong>' . $cInfo->configuration_title . '</strong><br>' . $cInfo->configuration_description . '<br>' . $value_field);
              $contents[] = array('align' => 'text-center', 'text' => '<br>' . '<button type="submit" name="submit' . $cInfo->configuration_key . '" class="btn btn-primary"><i class="fa-solid fa-check"></i> ' . IMAGE_UPDATE . '</button>' . '&nbsp;<a href="' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id) . '" class="btn btn-default" role="button"><i class="fa-solid fa-xmark"></i> ' . IMAGE_CANCEL . '</a>');
              break;
            default:
              if (isset($cInfo) && is_object($cInfo)) {
                $heading[] = array('text' => '<h4>' . $cInfo->configuration_title . '</h4>');
                if (ADMIN_CONFIGURATION_KEY_ON == 1) {
                  $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br>');
                }

                $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '" class="btn btn-primary" role="button"> ' . IMAGE_EDIT . '</a>');
                $contents[] = array('text' => '<br>' . $cInfo->configuration_description);
                $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($cInfo->date_added));
                if (zen_not_null($cInfo->last_modified))
                  $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($cInfo->last_modified));
              }
              break;
          }

          if (!empty($heading) && !empty($contents)) {
            $box = new box;
            echo $box->infoBox($heading, $contents);
          }
          ?>
        </div>
      </div>
    </div>

    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
