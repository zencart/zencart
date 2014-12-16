<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: layout_controller.php drbyte  Modified in v1.5.4 $
 */

  require('includes/application_top.php');

// Check all existing boxes are in the main /sideboxes
  $boxes_directory = DIR_FS_CATALOG_MODULES . 'sideboxes/';

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir($boxes_directory)) {
    while ($file = $dir->read()) {
      if (!is_dir($boxes_directory . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          if ($file != 'empty.txt') {
            $directory_array[] = $file;
          }
        }
      }
    }
    if (sizeof($directory_array)) {
      sort($directory_array);
    }
    $dir->close();
  }

// Check all exisiting boxes are in the current template /sideboxes/template_dir
  $dir_check= $directory_array;
  $boxes_directory = DIR_FS_CATALOG_MODULES . 'sideboxes/' . $template_dir . '/';

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));

  if ($dir = @dir($boxes_directory)) {
    while ($file = $dir->read()) {
      if (!is_dir($boxes_directory . $file)) {
          if (in_array($file, $dir_check, TRUE)) {
            // skip name exists
          } else {
            if ($file != 'empty.txt') {
              $directory_array[] = $file;
            }
          }
      }
    }
    sort($directory_array);
    $dir->close();
  }

  $warning_new_box='';
  $installed_boxes = array();
  for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
    $file = $directory_array[$i];

// Verify Definitions
    $definitions = $db->Execute("select layout_box_name from " . TABLE_LAYOUT_BOXES . " where layout_box_name='" . zen_db_input($file) . "' and layout_template='" . zen_db_input($template_dir) . "'");
    if ($definitions->EOF) {
      if (!strstr($file, 'ezpages_bar')) {
        $warning_new_box .= $file . ' ';
      } else {
        // skip ezpage sideboxes
//        $warning_new_box .= $file . ' - HIDDEN ';
      }
      $db->Execute("insert into " . TABLE_LAYOUT_BOXES . "
                  (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single)
                  values ('" . zen_db_input($template_dir) . "', '" . zen_db_input($file) . "', 0, 0, 0, 0, 0)");
    }
  }

////////////////////////////////////
  if ($_GET['action']) {
    switch ($_GET['action']) {
      case 'insert':
        $layout_box_name = zen_db_prepare_input($_POST['layout_box_name']);
        $layout_box_status = zen_db_prepare_input($_POST['layout_box_status']);
        $layout_box_location = zen_db_prepare_input($_POST['layout_box_location']);
        $layout_box_sort_order = zen_db_prepare_input($_POST['layout_box_sort_order']);
        $layout_box_sort_order_single = zen_db_prepare_input($_POST['layout_box_sort_order_single']);
        $layout_box_status_single = zen_db_prepare_input($_POST['layout_box_status_single']);

        $db->Execute("insert into " . TABLE_LAYOUT_BOXES . "
                    (layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single)
                    values ('" . zen_db_input($layout_box_name) . "',
                            '" . zen_db_input($layout_box_status) . "',
                            '" . zen_db_input($layout_box_location) . "',
                            '" . zen_db_input($layout_box_sort_order) . "',
                            '" . zen_db_input($layout_box_sort_order_single) . "',
                            '" . zen_db_input($layout_box_status_single) . "')");

        $messageStack->add_session(SUCCESS_BOX_ADDED . $_GET['layout_box_name'], 'success');
        zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER));
        break;
      case 'save':
        $box_id = zen_db_prepare_input($_GET['cID']);
        // $layout_box_name = zen_db_prepare_input($_POST['layout_box_name']);
        $layout_box_status = zen_db_prepare_input($_POST['layout_box_status']);
        $layout_box_location = zen_db_prepare_input($_POST['layout_box_location']);
        $layout_box_sort_order = zen_db_prepare_input($_POST['layout_box_sort_order']);
        $layout_box_sort_order_single = zen_db_prepare_input($_POST['layout_box_sort_order_single']);
        $layout_box_status_single = zen_db_prepare_input($_POST['layout_box_status_single']);

        $db->Execute("update " . TABLE_LAYOUT_BOXES . " set layout_box_status = '" . zen_db_input($layout_box_status) . "', layout_box_location = '" . zen_db_input($layout_box_location) . "', layout_box_sort_order = '" . zen_db_input($layout_box_sort_order) . "', layout_box_sort_order_single = '" . zen_db_input($layout_box_sort_order_single) . "', layout_box_status_single = '" . zen_db_input($layout_box_status_single) . "' where layout_id = '" . zen_db_input($box_id) . "'");

        $messageStack->add_session(SUCCESS_BOX_UPDATED . $_GET['layout_box_name'], 'success');
        zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $box_id));
        break;
      case 'deleteconfirm':
        $box_id = zen_db_prepare_input($_POST['cID']);

        $db->Execute("delete from " . TABLE_LAYOUT_BOXES . " where layout_id = '" . zen_db_input($box_id) . "'");

        $messageStack->add_session(SUCCESS_BOX_DELETED . $_GET['layout_box_name'], 'success');
        zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page']));
        break;
      case 'reset_defaults':
        if ($_POST['action'] == 'reset_defaults') {
          $reset_boxes = $db->Execute("select * from " . TABLE_LAYOUT_BOXES . " where layout_template= 'default_template_settings'");
          while (!$reset_boxes->EOF) {
            $db->Execute("update " . TABLE_LAYOUT_BOXES . " set layout_box_status= '" . $reset_boxes->fields['layout_box_status'] . "', layout_box_location= '" . $reset_boxes->fields['layout_box_location'] . "', layout_box_sort_order='" . $reset_boxes->fields['layout_box_sort_order'] . "', layout_box_sort_order_single='" . $reset_boxes->fields['layout_box_sort_order_single'] . "', layout_box_status_single='" . $reset_boxes->fields['layout_box_status_single'] . "' where layout_box_name='" . $reset_boxes->fields['layout_box_name'] . "' and layout_template='" . zen_db_input($template_dir) . "'");
            $reset_boxes->MoveNext();
          }
          $messageStack->add_session(SUCCESS_BOX_RESET . $template_dir, 'success');
          zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER));
        }
        break;
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
<body onload="init()">
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
<?php
if ($warning_new_box) {
?>
        <tr class="messageStackError">
          <td colspan="2" class="messageStackError">
<?php echo 'WARNING: New boxes found: ' . $warning_new_box; ?>
          </td>
        </tr>
<?php
}
?>
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE . ' ' . $template_dir; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>

      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" align="left"><strong>Boxes Path: </strong><?php echo DIR_FS_CATALOG_MODULES . ' ... ' . '<br />&nbsp;'; ?></td>
              </tr>
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="left" width="200"><?php echo TABLE_HEADING_LAYOUT_BOX_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAYOUT_BOX_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAYOUT_BOX_LOCATION; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAYOUT_BOX_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAYOUT_BOX_SORT_ORDER_SINGLE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAYOUT_BOX_STATUS_SINGLE; ?></td>
                <td colspan="2" class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>

<?php
  $boxes_directory = DIR_FS_CATALOG_MODULES . 'sideboxes' . '/';
  $boxes_directory_template = DIR_FS_CATALOG_MODULES . 'sideboxes/' . $template_dir . '/';

  $column_controller = $db->Execute("select layout_id, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single from " . TABLE_LAYOUT_BOXES . " where (layout_template='" . zen_db_input($template_dir) . "' and layout_box_name NOT LIKE '%ezpages_bar%') order by  layout_box_location, layout_box_sort_order");
  while (!$column_controller->EOF) {
//    if (((!$_GET['cID']) || (@$_GET['cID'] == $column_controller->fields['layout_id'])) && (!$bInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
  if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $column_controller->fields['layout_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
      $bInfo = new objectInfo($column_controller->fields);
    }

//  if ( (is_object($bInfo)) && ($column_controller->fields['layout_id'] == $bInfo->layout_id) ) {
    if (isset($bInfo) && is_object($bInfo) && ($column_controller->fields['layout_id'] == $bInfo->layout_id)) {
      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $bInfo->layout_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $column_controller->fields['layout_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent" width="100"><?php echo (file_exists($boxes_directory_template . $column_controller->fields['layout_box_name']) ? '<span class="alert">' . str_replace(DIR_FS_CATALOG_MODULES, '', $boxes_directory_template) . '</span>' . $column_controller->fields['layout_box_name'] : str_replace(DIR_FS_CATALOG_MODULES, '', $boxes_directory) . $column_controller->fields['layout_box_name']); ?></td>
                <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) or file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? dataTableContent : messageStackError ); ?>" align="center"><?php echo ($column_controller->fields['layout_box_status']=='1' ? TEXT_ON : '<span class="alert">' . TEXT_OFF .'</span>'); ?></td>
                <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) or file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? dataTableContent : messageStackError ); ?>" align="center"><?php echo ($column_controller->fields['layout_box_location']=='0' ? TEXT_LEFT : TEXT_RIGHT); ?></td>
                <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) or file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? dataTableContent : messageStackError ); ?>" align="center"><?php echo $column_controller->fields['layout_box_sort_order']; ?></td>
                <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) or file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? dataTableContent : messageStackError ); ?>" align="center"><?php echo $column_controller->fields['layout_box_sort_order_single']; ?></td>
                <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) or file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? dataTableContent : messageStackError ); ?>" align="center"><?php echo ($column_controller->fields['layout_box_status_single']=='1' ? TEXT_ON : '<span class="alert">' . TEXT_OFF . '</span>'); ?></td>

                <td class="dataTableContent" align="right"><?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) or file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? TEXT_GOOD_BOX : TEXT_BAD_BOX) ; ?><?php echo '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $column_controller->fields['layout_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', IMAGE_EDIT) . '</a>'; ?></td>

                <td class="dataTableContent" align="right"><?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) or file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? TEXT_GOOD_BOX : TEXT_BAD_BOX) ; ?><?php if ( (is_object($bInfo)) && ($column_controller->fields['layout_id'] == $bInfo->layout_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $column_controller->fields['layout_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>

<?php
    $last_box_column = $column_controller->fields['layout_box_location'];
    $column_controller->MoveNext();
    if (($column_controller->fields['layout_box_location'] != $last_box_column) and !$column_controller->EOF) {
?>
              <tr valign="top">
                <td colspan="7" height="20" align="center" valign="middle"><?php echo zen_draw_separator('pixel_black.gif', '90%', '3'); ?></td>
              </tr>
<?php
    }
  }
?>

              <tr valign="top">
                <td valign="top"><?php echo zen_draw_separator('pixel_trans.gif', '75%', '10'); ?></td>
              </tr>

            </table></td>
<?php
  $heading = array();
  $contents = array();

    switch ($bInfo->layout_box_status) {
      case '0': $layout_box_status_status_on = false; $layout_box_status_status_off = true; break;
      case '1':
      default: $layout_box_status_status_on = true; $layout_box_status_status_off = false;
    }
    switch ($bInfo->layout_box_status_single) {
      case '0': $layout_box_status_single_on = false; $layout_box_status_single_off = true; break;
      case '1':
      default: $layout_box_status_single_on = true; $layout_box_status_single_off = false;
    }

  switch ($_GET['action']) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_BOX . '</b>');

      $contents = array('form' => zen_draw_form('column_controller', FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_NAME . '<br />' . zen_draw_input_field('layout_box_name'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_STATUS . '<br />' . zen_draw_input_field('layout_box_status'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_LOCATION . '<br />' . zen_draw_input_field('layout_box_location'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_SORT_ORDER . '<br />' . zen_draw_input_field('layout_box_sort_order'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_SORT_ORDER_SINGLE . '<br />' . zen_draw_input_field('layout_box_sort_order_single'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_STATUS_SINGLE . '<br />' . zen_draw_input_field('layout_box_status_single'));

      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      switch ($bInfo->layout_box_status) {
        case '0': $in_status = false; $out_status = true; break;
        case '1': $in_status = true; $out_status = false; break;
        default: $in_status = true; $out_status = false;
      }
      switch ($bInfo->layout_box_location) {
        case '0': $left_status = true; $right_status = false; break;
        case '1': $left_status = false; $right_status = true; break;
        default: $left_status = false; $right_status = true;
      }
      switch ($bInfo->layout_box_status_single) {
        case '0': $in_status_single = false; $out_status_single = true; break;
        case '1': $in_status_single = true; $out_status_single = false; break;
        default: $in_status_single = true; $out_status_single = false;
      }

      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_BOX . '</b>');

      $contents = array('form' => zen_draw_form('column_controller', FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $bInfo->layout_id . '&action=save' . '&layout_box_name=' . $bInfo->layout_box_name));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_NAME . ' ' . $bInfo->layout_box_name);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_STATUS . '<br />' . zen_draw_radio_field('layout_box_status', '1', $in_status) . TEXT_ON . zen_draw_radio_field('layout_box_status', '0', $out_status) . TEXT_OFF);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_LOCATION . '<br />' . zen_draw_radio_field('layout_box_location', '0', $left_status) . TEXT_LEFT . zen_draw_radio_field('layout_box_location', '1', $right_status) . TEXT_RIGHT);
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_SORT_ORDER . '<br />' . zen_draw_input_field('layout_box_sort_order', $bInfo->layout_box_sort_order,'size="4"'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_SORT_ORDER_SINGLE . '<br />' . zen_draw_input_field('layout_box_sort_order_single', $bInfo->layout_box_sort_order_single,'size="4"'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_LAYOUT_BOX_STATUS_SINGLE . '<br />' . zen_draw_radio_field('layout_box_status_single', '1', $in_status_single) . TEXT_ON . zen_draw_radio_field('layout_box_status_single', '0', $out_status_single) . TEXT_OFF);
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $bInfo->layout_id . '&layout_box_name=' . $bInfo->layout_box_name) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_BOX . '</b>');

      $contents = array('form' => zen_draw_form('column_controller', FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&action=deleteconfirm' . '&layout_box_name=' . $bInfo->layout_box_name) . zen_draw_hidden_field('cID', $bInfo->layout_id));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $bInfo->layout_box_name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $bInfo->layout_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($bInfo)) {
        $heading[] = array('text' => '<strong>' . TEXT_INFO_LAYOUT_BOX . $bInfo->layout_box_name . '</strong>');
        $contents[] = array('align' => 'left', 'text' => '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $bInfo->layout_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
        $contents[] = array('text' => '<strong>' . TEXT_INFO_BOX_DETAILS . '</strong>');
        $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_NAME . ' ' . $bInfo->layout_box_name);
        $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_STATUS . ' ' .  ($bInfo->layout_box_status=='1' ? TEXT_ON : TEXT_OFF) );
        $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_LOCATION . ' ' . ($bInfo->layout_box_location=='0' ? TEXT_LEFT : TEXT_RIGHT) );
        $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_SORT_ORDER . ' ' . $bInfo->layout_box_sort_order);
        $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_SORT_ORDER_SINGLE . ' ' . $bInfo->layout_box_sort_order_single);
        $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_STATUS_SINGLE . ' ' .  ($bInfo->layout_box_status_single=='1' ? TEXT_ON : TEXT_OFF) );

        if (!(file_exists($boxes_directory . $bInfo->layout_box_name) or file_exists($boxes_directory_template . $bInfo->layout_box_name))) {
          $contents[] = array('align' => 'left', 'text' => '<br /><strong>' . TEXT_INFO_DELETE_MISSING_LAYOUT_BOX . '<br />' . $template_dir . '</strong>');
          $contents[] = array('align' => 'left', 'text' => TEXT_INFO_DELETE_MISSING_LAYOUT_BOX_NOTE . '<strong>' . $bInfo->layout_box_name . '</strong>');
          $contents[] = array('align' => 'left', 'text' => '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, 'page=' . $_GET['page'] . '&cID=' . $bInfo->layout_id . '&action=delete' . '&layout_box_name=' . $bInfo->layout_box_name) . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        }
      }
      break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo "\n" . '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
  </tr>
  <tr>
    <td><table align="center">
      <tr>
        <td class="main" align="left">
          <?php echo '<br />' . TEXT_INFO_RESET_TEMPLATE_SORT_ORDER . '<strong>' . $template_dir . '</strong>'; ?>
        </td>
      </tr>
      <tr>
        <td class="main" align="center">
          <?php echo TEXT_INFO_RESET_TEMPLATE_SORT_ORDER_NOTE; ?>
        </td>
      </tr>
      <tr>
        <td class="main" align="center">
            <?php echo zen_draw_form('reset_defaults', FILENAME_LAYOUT_CONTROLLER, 'action=reset_defaults'); ?>
            <?php echo zen_draw_hidden_field('action', 'reset_defaults'); ?>
            <?php echo zen_image_submit('button_reset.gif', IMAGE_RESET) ?>
            </form>
        </td>
      </tr>
    </table></td>
  </tr>
  <tr valign="top">
    <td valign="top"><?php echo zen_draw_separator('pixel_trans.gif', '1', '100'); ?></td>
  </tr>

<!-- end of display -->

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
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
