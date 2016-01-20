<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Modified in v1.5.5 $
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
          zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$cID));
        }
        $configuration_value = zen_db_prepare_input($_POST['configuration_value']);

        $db->Execute("update " . TABLE_CONFIGURATION . "
                      set configuration_value = '" . zen_db_input($configuration_value) . "',
                          last_modified = now() where configuration_id = '" . (int)$cID . "'");

        $result = $db->Execute("select configuration_key from " . TABLE_CONFIGURATION . " where configuration_id=" . (int)$cID . " LIMIT 1");
        zen_record_admin_activity('Configuration setting changed for ' . $result->fields['configuration_key'] . ': ' . $configuration_value, 'warning');

        // set the WARN_BEFORE_DOWN_FOR_MAINTENANCE to false if DOWN_FOR_MAINTENANCE = true
        if ( (WARN_BEFORE_DOWN_FOR_MAINTENANCE == 'true') && (DOWN_FOR_MAINTENANCE == 'true') ) {
        $db->Execute("update " . TABLE_CONFIGURATION . "
                      set configuration_value = 'false', last_modified = '" . NOW . "'
                      where configuration_key = 'WARN_BEFORE_DOWN_FOR_MAINTENANCE'"); }

        zen_redirect(zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . (int)$cID));
        break;
    }
  }

  $gID = (isset($_GET['gID'])) ? $_GET['gID'] : 1;
  $_GET['gID'] = $gID;
  $cfg_group = $db->Execute("select configuration_group_title
                             from " . TABLE_CONFIGURATION_GROUP . "
                             where configuration_group_id = '" . (int)$gID . "'");

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
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onLoad="init()">
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
            <td class="pageHeading"><?php echo $cfg_group->fields['configuration_group_title']; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" width="55%"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $configuration = $db->Execute("select configuration_id, configuration_title, configuration_value, configuration_key,
                                        use_function from " . TABLE_CONFIGURATION . "
                                        where configuration_group_id = '" . (int)$gID . "'
                                        order by sort_order");
  while (!$configuration->EOF) {
    if (zen_not_null($configuration->fields['use_function'])) {
      $use_function = $configuration->fields['use_function'];
      if (preg_match('/->/', $use_function)) {
        $class_method = explode('->', $use_function);
        if (!is_object(${$class_method[0]})) {
          include(DIR_WS_CLASSES . $class_method[0] . '.php');
          ${$class_method[0]} = new $class_method[0]();
        }
        $cfgValue = zen_call_function($class_method[1], $configuration->fields['configuration_value'], ${$class_method[0]});
      } else {
        $cfgValue = zen_call_function($use_function, $configuration->fields['configuration_value']);
      }
    } else {
      $cfgValue = $configuration->fields['configuration_value'];
    }

    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $configuration->fields['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $cfg_extra = $db->Execute("select configuration_key, configuration_description, date_added,
                                        last_modified, use_function, set_function
                                 from " . TABLE_CONFIGURATION . "
                                 where configuration_id = '" . (int)$configuration->fields['configuration_id'] . "'");
      $cInfo_array = array_merge($configuration->fields, $cfg_extra->fields);
      $cInfo = new objectInfo($cInfo_array);
    }

    if ( (isset($cInfo) && is_object($cInfo)) && ($configuration->fields['configuration_id'] == $cInfo->configuration_id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $configuration->fields['configuration_id'] . '&action=edit') . '\'">' . "\n";
    }
?>
<?php
   // multilanguage support: 
   // For example, in admin/includes/languages/spanish/configuration.php
   // define('CFGTITLE_STORE_NAME', 'Nombre de la Tienda');
   // define('CFGDESC_STORE_NAME', 'El nombre de mi tienda');
    if (defined('CFGTITLE_' . $configuration->fields['configuration_key'])) {
      $configuration->fields['configuration_title'] = constant('CFGTITLE_' . $configuration->fields['configuration_key']);
    }
    if (defined('CFGDESC_' . $configuration->fields['configuration_key'])) {
      $configuration->fields['configuration_description'] = constant('CFGDESC_' . $configuration->fields['configuration_key']); 
    }
?>
                <td class="dataTableContent"><?php echo $configuration->fields['configuration_title']; ?></td>
                <td class="dataTableContent"><?php echo htmlspecialchars($cfgValue, ENT_COMPAT, CHARSET, TRUE); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($cInfo) && is_object($cInfo)) && ($configuration->fields['configuration_id'] == $cInfo->configuration_id) ) { echo ADMIN_ROW_ICON_RIGHT_ARROW; } else { echo '<a href="' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $configuration->fields['configuration_id']) . '" name="link_' . $configuration->fields['configuration_key'] . '">'.ADMIN_ROW_ICON_INFO.'</span></a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $configuration->MoveNext();
  }
?>
            </table></td>
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
        $value_field = zen_draw_input_field('configuration_value', htmlspecialchars($cInfo->configuration_value, ENT_COMPAT, CHARSET, TRUE), 'size="60"');
      }

      $contents = array('form' => zen_draw_form('configuration', FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=save'));
      if (ADMIN_CONFIGURATION_KEY_ON == 1) {
        $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br />');
      }
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br><b>' . $cInfo->configuration_title . '</b><br>' . $cInfo->configuration_description . '<br>' . $value_field);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE, 'name="submit' . $cInfo->configuration_key . '"') . '&nbsp;<a href="' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');
        if (ADMIN_CONFIGURATION_KEY_ON == 1) {
          $contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br />');
        }

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
        $contents[] = array('text' => '<br>' . $cInfo->configuration_description);
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($cInfo->date_added));
        if (zen_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($cInfo->last_modified));
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
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>