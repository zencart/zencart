<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 16 Modified in v1.5.7 $
 */
require('includes/application_top.php');

// Check all existing boxes are in the main /sideboxes
$boxes_directory = DIR_FS_CATALOG_MODULES . 'sideboxes/';
$boxes_directory_template = DIR_FS_CATALOG_MODULES . 'sideboxes/' . $template_dir . '/';

$directory_array = array();
if ($dir = @dir($boxes_directory)) {
  while ($file = $dir->read()) {
    if (!is_dir($boxes_directory . $file)) {
      if (preg_match('~^[^\._].*\.php$~i', $file) > 0) {
        $directory_array[] = $file;
      }
    }
  }
  if (sizeof($directory_array)) {
    sort($directory_array);
  }
  $dir->close();
}

$dir_check = $directory_array;

// Check all existing boxes are in the current template /sideboxes/template_dir
if ($dir = @dir($boxes_directory_template)) {
  while ($file = $dir->read()) {
    if (!is_dir($boxes_directory_template . $file)) {
      if (!in_array($file, $dir_check, TRUE)) {
        if (preg_match('~^[^\._].*\.php$~i', $file) > 0) {
          $directory_array[] = $file;
        }
      }
    }
  }
  $dir->close();
}
sort($directory_array);

$warning_new_box = '';
$installed_boxes = array();
for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
  $file = $directory_array[$i];

// Verify Definitions
  $definitions = $db->Execute("SELECT layout_box_name
                               FROM " . TABLE_LAYOUT_BOXES . "
                               WHERE layout_box_name = '" . zen_db_input($file) . "'
                               AND layout_template = '" . zen_db_input($template_dir) . "'");
  if ($definitions->EOF) {
    if (!strstr($file, 'ezpages_bar')) {
      $warning_new_box .= $file . ' ';
    } else {
      // skip ezpage sideboxes
//        $warning_new_box .= $file . ' - HIDDEN ';
    }
    $db->Execute("INSERT INTO " . TABLE_LAYOUT_BOXES . " (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single)
                  VALUES ('" . zen_db_input($template_dir) . "', '" . zen_db_input($file) . "', 0, 0, 0, 0, 0)");
  }
}

$cur_page = 'page=' . (isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : '1'); // page=1 used to prevent concatenation issues.
if (!isset($_GET['action'])) $_GET['action'] = '';

////////////////////////////////////
if (!empty($_GET['action'])) {
  switch ($_GET['action']) {
    case 'save':
      $box_id = zen_db_prepare_input($_GET['cID']);
      // $layout_box_name = zen_db_prepare_input($_POST['layout_box_name']);
      $layout_box_status = zen_db_prepare_input($_POST['layout_box_status']);
      $layout_box_location = zen_db_prepare_input($_POST['layout_box_location']);
      $layout_box_sort_order = zen_db_prepare_input($_POST['layout_box_sort_order']);
      $layout_box_sort_order_single = zen_db_prepare_input($_POST['layout_box_sort_order_single']);
      $layout_box_status_single = zen_db_prepare_input($_POST['layout_box_status_single']);

      $db->Execute("UPDATE " . TABLE_LAYOUT_BOXES . "
                    SET layout_box_status = " . (int)$layout_box_status . ",
                        layout_box_location = " . (int)$layout_box_location . ",
                        layout_box_sort_order = " . (int)$layout_box_sort_order . ",
                        layout_box_sort_order_single = " . (int)$layout_box_sort_order_single . ",
                        layout_box_status_single = " . (int)$layout_box_status_single . "
                    WHERE layout_id = " . (int)$box_id);

      $messageStack->add_session(SUCCESS_BOX_UPDATED . $_GET['layout_box_name'], 'success');
      zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $box_id));
      break;
    case 'deleteconfirm':
      $box_id = zen_db_prepare_input($_POST['cID']);

      $db->Execute("DELETE FROM " . TABLE_LAYOUT_BOXES . "
                    WHERE layout_id = " . (int)$box_id);

      $messageStack->add_session(SUCCESS_BOX_DELETED . $_GET['layout_box_name'], 'success');
      zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page));
      break;
    case 'reset_defaults':
      if ($_POST['action'] == 'reset_defaults') {
        $reset_boxes = $db->Execute("SELECT *
                                     FROM " . TABLE_LAYOUT_BOXES . "
                                     WHERE layout_template = 'default_template_settings'");
        foreach ($reset_boxes as $reset_box) {
          $db->Execute("UPDATE " . TABLE_LAYOUT_BOXES . "
                        SET layout_box_status = " . (int)$reset_box['layout_box_status'] . ",
                            layout_box_location = " . (int)$reset_box['layout_box_location'] . ",
                            layout_box_sort_order = " . (int)$reset_box['layout_box_sort_order'] . ",
                            layout_box_sort_order_single = " . (int)$reset_box['layout_box_sort_order_single'] . ",
                            layout_box_status_single = " . (int)$reset_box['layout_box_status_single'] . "
                        WHERE layout_box_name = '" . zen_db_input($reset_box['layout_box_name']) . "'
                        AND layout_template='" . zen_db_input($template_dir) . "'");
        }
        $messageStack->add_session(SUCCESS_BOX_RESET . $template_dir, 'success');
        zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER));
      }
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
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
        <?php
        if ($warning_new_box) {
          ?>
        <div class="row messageStackError"><?php echo TEXT_WARNING_NEW_BOXES_FOUND . $warning_new_box; ?></div>
        <?php
      }
      ?>
      <h1><?php echo HEADING_TITLE . ' ' . $template_dir; ?></h1>
      <div class="row"><strong><?php echo TABLE_HEADING_BOXES_PATH; ?></strong><?php echo DIR_FS_CATALOG_MODULES . ' ... '; ?></div>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_LAYOUT_BOX_NAME; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_LAYOUT_BOX_STATUS; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_LAYOUT_BOX_LOCATION; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_LAYOUT_BOX_SORT_ORDER; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_LAYOUT_BOX_SORT_ORDER_SINGLE; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_LAYOUT_BOX_STATUS_SINGLE; ?></th>
                <th colspan="2" class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>

              <?php
              $column_controller = $db->Execute("SELECT layout_id, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single
                                                 FROM " . TABLE_LAYOUT_BOXES . "
                                                 WHERE (layout_template = '" . zen_db_input($template_dir) . "'
                                                   AND layout_box_name NOT LIKE '%ezpages_bar%')
                                                 ORDER BY  layout_box_location, layout_box_sort_order");
              while (!$column_controller->EOF) {
//    if (((!$_GET['cID']) || ($_GET['cID'] == $column_controller->fields['layout_id'])) && (!$bInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
                if ((empty($_GET['cID']) || ($_GET['cID'] == $column_controller->fields['layout_id'])) && empty($bInfo) && (empty($action) || substr($action, 0, 3) != 'new')) {
                  $bInfo = new objectInfo($column_controller->fields);
                }

//  if ( (is_object($bInfo)) && ($column_controller->fields['layout_id'] == $bInfo->layout_id) ) {
                if (isset($bInfo) && is_object($bInfo) && ($column_controller->fields['layout_id'] == $bInfo->layout_id)) {
                  echo '              <tr class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&action=edit') . '\'" role="button">' . "\n";
                } else {
                  echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $column_controller->fields['layout_id']) . '\'" role="button">' . "\n";
                }
                ?>
              <td class="dataTableContent"><?php echo (file_exists($boxes_directory_template . $column_controller->fields['layout_box_name']) ? '<span class="alert">' . str_replace(DIR_FS_CATALOG_MODULES, '', $boxes_directory_template) . '</span>' . $column_controller->fields['layout_box_name'] : str_replace(DIR_FS_CATALOG_MODULES, '', $boxes_directory) . $column_controller->fields['layout_box_name']); ?></td>
              <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) || file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? 'dataTableContent' : 'messageStackError' ); ?>" align="center"><?php echo ($column_controller->fields['layout_box_status'] == '1' ? TEXT_ON : '<span class="alert">' . TEXT_OFF . '</span>'); ?></td>
              <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) || file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? 'dataTableContent' : 'messageStackError' ); ?>" align="center"><?php echo ($column_controller->fields['layout_box_location'] == '0' ? TEXT_LEFT : TEXT_RIGHT); ?></td>
              <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) || file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? 'dataTableContent' : 'messageStackError' ); ?>" align="center"><?php echo $column_controller->fields['layout_box_sort_order']; ?></td>
              <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) || file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? 'dataTableContent' : 'messageStackError' ); ?>" align="center"><?php echo $column_controller->fields['layout_box_sort_order_single']; ?></td>
              <td class="<?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) || file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? 'dataTableContent' : 'messageStackError' ); ?>" align="center"><?php echo ($column_controller->fields['layout_box_status_single'] == '1' ? TEXT_ON : '<span class="alert">' . TEXT_OFF . '</span>'); ?></td>

              <td class="dataTableContent text-right"><?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) || file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? TEXT_GOOD_BOX : TEXT_BAD_BOX); ?><?php echo '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $column_controller->fields['layout_id'] . '&action=edit') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', IMAGE_EDIT) . '</a>'; ?></td>

              <td class="dataTableContent text-right"><?php echo ( (file_exists($boxes_directory . $column_controller->fields['layout_box_name']) || file_exists($boxes_directory_template . $column_controller->fields['layout_box_name'])) ? TEXT_GOOD_BOX : TEXT_BAD_BOX); ?><?php
                  if (isset($bInfo) && is_object($bInfo) && ($column_controller->fields['layout_id'] == $bInfo->layout_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $column_controller->fields['layout_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                  }
                  ?>&nbsp;</td>
              </tr>

              <?php
              $last_box_column = $column_controller->fields['layout_box_location'];
              $column_controller->MoveNext();
              if (($column_controller->fields['layout_box_location'] != $last_box_column) && ! $column_controller->EOF) {
                ?>
                <tr valign="top">
                  <td colspan="8" height="20" align="center" valign="middle"><?php echo zen_draw_separator('pixel_black.gif', '90%', '3'); ?></td>
                </tr>
                <?php
              }
            }
            ?>
          </tbody>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = array();
            $contents = array();

            switch ($bInfo->layout_box_status) {
              case '0': $layout_box_status_status_on = false;
                $layout_box_status_status_off = true;
                break;
              case '1':
              default: $layout_box_status_status_on = true;
                $layout_box_status_status_off = false;
            }
            switch ($bInfo->layout_box_status_single) {
              case '0': $layout_box_status_single_on = false;
                $layout_box_status_single_off = true;
                break;
              case '1':
              default: $layout_box_status_single_on = true;
                $layout_box_status_single_off = false;
            }

            switch ($_GET['action']) {
              case 'edit':
                switch ($bInfo->layout_box_status) {
                  case '0': $in_status = false;
                    $out_status = true;
                    break;
                  case '1': $in_status = true;
                    $out_status = false;
                    break;
                  default: $in_status = true;
                    $out_status = false;
                }
                switch ($bInfo->layout_box_location) {
                  case '0': $left_status = true;
                    $right_status = false;
                    break;
                  case '1': $left_status = false;
                    $right_status = true;
                    break;
                  default: $left_status = false;
                    $right_status = true;
                }
                switch ($bInfo->layout_box_status_single) {
                  case '0': $in_status_single = false;
                    $out_status_single = true;
                    break;
                  case '1': $in_status_single = true;
                    $out_status_single = false;
                    break;
                  default: $in_status_single = true;
                    $out_status_single = false;
                }

                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_EDIT_BOX . '</h4>');

                $contents = array('form' => zen_draw_form('column_controller', FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&action=save' . '&layout_box_name=' . $bInfo->layout_box_name, 'post', 'class="form-horizontal"'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_NAME . ' ' . $bInfo->layout_box_name);
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LAYOUT_BOX_STATUS, 'layout_box_status' , 'class="control-label"') . '<div class="radio"><label>' . zen_draw_radio_field('layout_box_status', '1', $in_status) . TEXT_ON . '</label></div><div class="radio"><label class="radio">' . zen_draw_radio_field('layout_box_status', '0', $out_status) . TEXT_OFF . '</label></div>');
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LAYOUT_BOX_LOCATION, 'layout_box_location' , 'class="control-label"') . '<div class="radio"><label>' . zen_draw_radio_field('layout_box_location', '0', $left_status) . TEXT_LEFT . '</label></div><div class="radio"><label>' . zen_draw_radio_field('layout_box_location', '1', $right_status) . TEXT_RIGHT . '</label></div>');
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LAYOUT_BOX_SORT_ORDER, 'layout_box_sort_order' , 'class="control-label"') . zen_draw_input_field('layout_box_sort_order', $bInfo->layout_box_sort_order, 'size="4" class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LAYOUT_BOX_SORT_ORDER_SINGLE, 'layout_box_sort_order_single' , 'class="control-label"') . zen_draw_input_field('layout_box_sort_order_single', $bInfo->layout_box_sort_order_single, 'size="4" class="form-control"'));
                $contents[] = array('text' => '<br>' . zen_draw_label(TEXT_INFO_LAYOUT_BOX_STATUS_SINGLE, 'layout_box_status_single' , 'class="control-label"') . '<div class="radio"><label>' . zen_draw_radio_field('layout_box_status_single', '1', $in_status_single) . TEXT_ON . '</label></div><div class="radio"><label>' . zen_draw_radio_field('layout_box_status_single', '0', $out_status_single) . TEXT_OFF . '</label></div>');
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&layout_box_name=' . $bInfo->layout_box_name) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_BOX . '</h4>');

                $contents = array('form' => zen_draw_form('column_controller', FILENAME_LAYOUT_CONTROLLER, $cur_page . '&action=deleteconfirm' . '&layout_box_name=' . $bInfo->layout_box_name) . zen_draw_hidden_field('cID', $bInfo->layout_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $bInfo->layout_box_name . '</b>');
                $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (is_object($bInfo)) {
                  $heading[] = array('text' => '<h4>' . TEXT_INFO_LAYOUT_BOX . $bInfo->layout_box_name . '</h4>');
                  $contents[] = array('text' => '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>');
                  $contents[] = array('text' => '<strong>' . TEXT_INFO_BOX_DETAILS . '</strong>');
                  $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_NAME . ' ' . $bInfo->layout_box_name);
                  $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_STATUS . ' ' . ($bInfo->layout_box_status == '1' ? TEXT_ON : TEXT_OFF));
                  $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_LOCATION . ' ' . ($bInfo->layout_box_location == '0' ? TEXT_LEFT : TEXT_RIGHT));
                  $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_SORT_ORDER . ' ' . $bInfo->layout_box_sort_order);
                  $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_SORT_ORDER_SINGLE . ' ' . $bInfo->layout_box_sort_order_single);
                  $contents[] = array('text' => TEXT_INFO_LAYOUT_BOX_STATUS_SINGLE . ' ' . ($bInfo->layout_box_status_single == '1' ? TEXT_ON : TEXT_OFF));

                  if (!(file_exists($boxes_directory . $bInfo->layout_box_name) || file_exists($boxes_directory_template . $bInfo->layout_box_name))) {
                    $contents[] = array('text' => '<br><strong>' . TEXT_INFO_DELETE_MISSING_LAYOUT_BOX . '<br>' . $template_dir . '</strong>');
                    $contents[] = array('text' => TEXT_INFO_DELETE_MISSING_LAYOUT_BOX_NOTE . '<strong>' . $bInfo->layout_box_name . '</strong>');
                    $contents[] = array('text' => '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&action=delete' . '&layout_box_name=' . $bInfo->layout_box_name) . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  }
                }
                break;
            }

            if ((zen_not_null($heading)) && (zen_not_null($contents))) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
        </div>

        <!-- end of display -->
      </div>
      <div class="row">
        <table class="table">
          <tr>
            <td>
                <?php echo '<br>' . TEXT_INFO_RESET_TEMPLATE_SORT_ORDER . '<strong>' . $template_dir . '</strong>'; ?>
            </td>
          </tr>
          <tr>
            <td class="text-center">
                <?php echo TEXT_INFO_RESET_TEMPLATE_SORT_ORDER_NOTE; ?>
            </td>
          </tr>
          <tr>
            <td class="text-center">
              <?php echo zen_draw_form('reset_defaults', FILENAME_LAYOUT_CONTROLLER, 'action=reset_defaults'); ?>
              <?php echo zen_draw_hidden_field('action', 'reset_defaults'); ?>
              <?php echo '<button type="submit" class="btn btn-warning">' . IMAGE_RESET . '</button>'; ?>
              <?php echo '</form>'; ?>
            </td>
          </tr>
        </table>
      </div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
