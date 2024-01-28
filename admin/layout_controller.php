<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */

use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\SideboxFinder;
use App\Models\LayoutBox;

require('includes/application_top.php');

$available_templates = zen_get_catalog_template_directories(true);
if (isset($available_templates['template_default'])) {
    $available_templates['template_default']['name'] = 'template_default';
}

$selected_template = $template_dir;

// check if a different template has been selected for viewing
if (!empty($_SESSION['layout_editor_selected_template']) && isset($available_templates[$_SESSION['layout_editor_selected_template']])) {
    $selected_template = strip_tags($_SESSION['layout_editor_selected_template']);
}
if (!empty($_POST['t']) && isset($available_templates[$_POST['t']])) {
    $selected_template = strip_tags($_POST['t']);
    $_SESSION['layout_editor_selected_template'] = $selected_template;
}
if ($selected_template != $template_dir) {
    $messageStack->add(TEXT_CAUTION_EDITING_NOT_LIVE_TEMPLATE, 'error');
}

$include_single_column_settings = false;
if (!empty($available_templates[$selected_template]['uses_single_column_layout_settings'])) {
    $include_single_column_settings = (bool)$available_templates[$selected_template]['uses_single_column_layout_settings'];
}

//
$sideboxFinder = new SideboxFinder(new Filesystem);
$sideboxes = $sideboxFinder->findFromFilesystem($installedPlugins, $selected_template);

$model = new LayoutBox;
$insertValues = [];
$warning_new_box = ''; // icwtodo @todo

foreach ($sideboxes as $sideboxFile => $plugin) {
    $result = $model->where('layout_template', $selected_template)->where('layout_box_name', $sideboxFile)->first();
    if ($result) continue;
    $insertValues = [
        'layout_template' => $selected_template,
        'layout_box_name' => $sideboxFile,
        'layout_box_status' => 0,
        'layout_box_location' => 0,
        'layout_box_sort_order' => 0,
        'layout_box_sort_order_single' => 0,
        'layout_box_status_single' => 0,
        'plugin_details' => $plugin,
    ];
    $model->query()->insert($insertValues);
}

//$model->where(['infs' => 0])->delete();
$cur_page = 'page=' . (isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : '1'); // page=1 to prevent concatenation issues.
if (!isset($_GET['action'])) $_GET['action'] = '';

////////////////////////////////////
if (!empty($_GET['action'])) {
  switch ($_GET['action']) {
    case 'save':
      $box_id = zen_db_prepare_input($_GET['cID']);

      $updateValues = [
          'layout_box_status' => (int)$_POST['layout_box_status'],
          'layout_box_location' => (int)$_POST['layout_box_location'],
          'layout_box_sort_order' => (int)$_POST['layout_box_sort_order'],
      ];
      if ($include_single_column_settings) {
          $updateValues += [
          'layout_box_sort_order_single' => (int)$_POST['layout_box_sort_order_single'],
          'layout_box_status_single' => (int)$_POST['layout_box_status_single'],
       ];
      }
      $model->where('layout_id', $box_id)->update($updateValues);
      $messageStack->add_session(SUCCESS_BOX_UPDATED . $_GET['layout_box_name'], 'success');
      zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $box_id));
      break;

    case 'deleteconfirm':
      $box_id = zen_db_prepare_input($_POST['cID']);
      $model->where('layout_id', $box_id)->delete();
      $messageStack->add_session(SUCCESS_BOX_DELETED . $_GET['layout_box_name'], 'success');
      zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page));
      break;

    case 'reset_defaults':
        // ensure this is a POST
        if ($_POST['action'] != 'reset_defaults') {
          break;
        }
        // check for fake resets
        if (!isset($available_templates[$_POST['tfrom']]) || !isset($available_templates[$_POST['tto']])) {
            $messageStack->add(TEXT_ERROR_INVALID_RESET_SUBMISSION, 'error');
        }
        if ($_POST['tfrom'] === '0') {
            $tfrom = 'default_template_settings';
        } else {
            $tfrom = strip_tags($_POST['tfrom']);
        }
        $reset_boxes = $model->where('layout_template', $tfrom)->get();
        $tto = strip_tags($_POST['tto']);

        foreach ($reset_boxes as $reset_box) {
            // This DOES include the single-column values, regardless of $include_single_column_settings value
            $updateValues = [
                'layout_box_status' => $reset_box['layout_box_status'],
                'layout_box_location' => $reset_box['layout_box_location'],
                'layout_box_sort_order' => $reset_box['layout_box_sort_order'],
                'layout_box_sort_order_single' => $reset_box['layout_box_sort_order_single'],
                'layout_box_status_single' => $reset_box['layout_box_status_single'],
            ];
            $model->where('layout_box_name', $reset_box['layout_box_name'])
                ->where('layout_template', $tto)
                ->update($updateValues);
        }
        $messageStack->add_session(sprintf(SUCCESS_BOX_RESET, $tto, $tfrom), 'success');
        zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER));
        break;
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
        <?php
        if ($warning_new_box) {
          ?>
        <div class="row messageStackError"><?php echo TEXT_WARNING_NEW_BOXES_FOUND . $warning_new_box; ?></div>
        <?php
      }
      ?>
        <div class="row">
            <div class="col-sm-5 alert alert-warning pull-right">
                <?php
                echo zen_draw_form('templateselect', FILENAME_LAYOUT_CONTROLLER, zen_get_all_get_params(['page']), 'post', 'class="form-inline"');
                echo zen_draw_label(TEXT_CURRENTLY_VIEWING, 'template_select', 'class="control-label"') . ' ' . PHP_EOL;
                $template_array = [];
                foreach($available_templates as $key => $value) {
                    if (isset($value['missing'])) continue;
                    $template_array[] = ['id' => $key, 'text' => $value['name']. ($key === $template_dir ? TEXT_THIS_IS_PRIMARY_TEMPLATE : '')];
                }
                echo zen_draw_pull_down_menu('t', $template_array, $selected_template, 'class="form-control" id="template_select"');
                ?>
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_SELECT; ?></button>
                <?php echo '</form>'; ?>
            </div>
            <div class="col-sm-6<?php echo ($selected_template != $template_dir) ? ' alert alert-danger' : ''; ?>">
                <h1><?php echo HEADING_TITLE . ' ' . $selected_template; ?></h1>
                <div class=""><strong><?php echo TABLE_HEADING_BOXES_PATH; ?></strong><?php echo DIR_FS_CATALOG_MODULES . ' ... '; ?></div>
            </div>
        </div>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_LAYOUT_BOX_NAME; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo ($include_single_column_settings ? TABLE_HEADING_LAYOUT_BOX_STATUS : TABLE_HEADING_STATUS); ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_LAYOUT_BOX_LOCATION; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo ($include_single_column_settings ? TABLE_HEADING_LAYOUT_BOX_SORT_ORDER : TABLE_HEADING_SORT_ORDER); ?></th>
                <?php if ($include_single_column_settings) { ?>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_LAYOUT_BOX_SORT_ORDER_SINGLE; ?></th>
                <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_LAYOUT_BOX_STATUS_SINGLE; ?></th>
                <?php } ?>
                <th colspan="2" class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ACTION; ?></th>
              </tr>
            </thead>
            <tbody>

              <?php
              $layoutBoxes = $model->where('layout_template', $selected_template)
                  ->where('layout_box_name', 'not like', '%ezpages_bar')
                  ->orderBy('layout_box_location')
                  ->orderBy('layout_box_sort_order')
                  ->get();
              foreach ($layoutBoxes as $index => $layoutBox) {
                if ((empty($_GET['cID']) || ($_GET['cID'] == $layoutBox['layout_id']))
                    && empty($bInfo)
                    && (empty($action) || substr($action, 0, 3) != 'new'))
                {
                  $bInfo = $layoutBoxes[$index];
                }

                // sync UI to highlight the same line in the table if switching between templates
                if ((!isset($bInfo) || !is_object($bInfo)) && !empty($_GET['cID'])) {
                    $lookupMatchFromOtherTemplate = $model->where('layout_id', (int)$_GET['cID'])->first();
                    if (!empty($lookupMatchFromOtherTemplate)) {
                        $bInfo = $layoutBoxes->where('layout_box_name', $lookupMatchFromOtherTemplate['layout_box_name'])->first();
                    }
                }

                $boxDirectory = $sideboxFinder->sideboxPath($layoutBox, $selected_template);

                $border_divider_style = '';
                $next = $index + 1;
                if (isset($layoutBoxes[$next]) && $layoutBoxes[$index]->layout_box_location != $layoutBoxes[$next]->layout_box_location) {
                    $border_divider_style = ' style="border-bottom: 3px solid black;"';
                }
                if (isset($bInfo) && is_object($bInfo) && $layoutBox['layout_id'] == $bInfo->layout_id) {
                  echo '              <tr class="' . ($boxDirectory == '' ? 'danger' : 'success'). ' dataTableRowSelected"' . $border_divider_style . ' onclick="document.location.href=\'' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&action=edit') . '\'">' . "\n";
                } else {
                  echo '              <tr class="dataTableRow' . ($boxDirectory == '' ? ' danger' : ''). '"' . $border_divider_style . ' onclick="document.location.href=\'' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $layoutBox['layout_id']) . '\'">' . "\n";
                }
                ?>

              <td class="dataTableContent<?php if ($boxDirectory == '') echo ' font-weight-bold danger'; ?>"><?php echo $boxDirectory . $layoutBox['layout_box_name']; ?></td>
              <td class="<?php echo ($boxDirectory != '') ? 'dataTableContent' : 'messageStackError'; ?> text-center"><?php echo ($layoutBox['layout_box_status'] == '1' ? TEXT_ON : '<span class="alert">' . TEXT_OFF . '</span>'); ?></td>
              <td class="<?php echo ($boxDirectory != '') ? 'dataTableContent' : 'messageStackError'; ?> text-center"><?php echo ($layoutBox['layout_box_location'] == '0' ? TEXT_LEFT : TEXT_RIGHT); ?></td>
              <td class="<?php echo ($boxDirectory != '') ? 'dataTableContent' : 'messageStackError'; ?> text-center"><?php echo $layoutBox['layout_box_sort_order']; ?></td>
                  <?php if ($include_single_column_settings) { ?>
              <td class="<?php echo ($boxDirectory != '') ? 'dataTableContent' : 'messageStackError'; ?> text-center"><?php echo $layoutBox['layout_box_sort_order_single']; ?></td>
              <td class="<?php echo ($boxDirectory != '') ? 'dataTableContent' : 'messageStackError'; ?> text-center"><?php echo ($layoutBox['layout_box_status_single'] == '1' ? TEXT_ON : '<span class="alert">' . TEXT_OFF . '</span>'); ?></td>
                  <?php } ?>

              <td class="dataTableContent text-right"><?php echo ($boxDirectory != '') ? TEXT_GOOD_BOX : TEXT_BAD_BOX; ?><?php echo '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $layoutBox['layout_id'] . '&action=edit') . '" class="btn btn-sm" role="button"><i class="fa-solid fa-pencil"></i></a>'; ?></td>

              <td class="dataTableContent text-right"><?php echo ($boxDirectory != '') ? TEXT_GOOD_BOX : TEXT_BAD_BOX; ?><?php
                  if (isset($bInfo) && is_object($bInfo) && ($layoutBox['layout_id'] == $bInfo->layout_id)) {
                    echo zen_icon('caret-right', '', '2x', true);
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $layoutBox['layout_id']) . '" class="btn btn-sm" role="button">' . zen_icon('circle-info', '', '2x', true, false) . '</a>';
                  }
                  ?>
              </td>
              <?php echo '</tr>'; ?>
          <?php
            }
          ?>
          </tbody>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = [];
            $contents = [];

            switch ($bInfo->layout_box_status) {
              case '0':
                $layout_box_status_status_on = false;
                $layout_box_status_status_off = true;
                break;
              case '1':
              default:
                $layout_box_status_status_on = true;
                $layout_box_status_status_off = false;
            }
            switch ($bInfo->layout_box_status_single) {
              case '0':
                $layout_box_status_single_on = false;
                $layout_box_status_single_off = true;
                break;
              case '1':
              default:
                $layout_box_status_single_on = true;
                $layout_box_status_single_off = false;
            }

            switch ($_GET['action']) {
              case 'edit':
                switch ($bInfo->layout_box_status) {
                  case '0':
                    $in_status = false;
                    $out_status = true;
                    break;
                  case '1':
                    $in_status = true;
                    $out_status = false;
                    break;
                  default:
                    $in_status = true;
                    $out_status = false;
                }
                switch ($bInfo->layout_box_location) {
                  case '0':
                    $left_status = true;
                    $right_status = false;
                    break;
                  case '1':
                    $left_status = false;
                    $right_status = true;
                    break;
                  default:
                    $left_status = false;
                    $right_status = true;
                }
                switch ($bInfo->layout_box_status_single) {
                  case '0':
                    $in_status_single = false;
                    $out_status_single = true;
                    break;
                  case '1':
                    $in_status_single = true;
                    $out_status_single = false;
                    break;
                  default:
                    $in_status_single = true;
                    $out_status_single = false;
                }

                $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_EDIT_BOX . '</h4>'];

                $contents = ['form' => zen_draw_form('column_controller', FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&action=save' . '&layout_box_name=' . $bInfo->layout_box_name, 'post', 'class="form-horizontal"')];
                $contents[] = ['text' => TEXT_INFO_EDIT_INTRO];
                $contents[] = ['text' => TEXT_INFO_LAYOUT_BOX_NAME . ' ' . $bInfo->layout_box_name];
                $contents[] = ['text' => '<b>' . TEXT_INFO_LAYOUT_BOX_STATUS . '</b><div class="radio"><label>' . zen_draw_radio_field('layout_box_status', '1', $in_status) . TEXT_ON . '</label></div><div class="radio"><label>' . zen_draw_radio_field('layout_box_status', '0', $out_status) . TEXT_OFF . '</label></div>'];
                $contents[] = ['text' => '<b>' . TEXT_INFO_LAYOUT_BOX_LOCATION . '</b><div class="radio"><label>' . zen_draw_radio_field('layout_box_location', '0', $left_status) . TEXT_LEFT . '</label></div><div class="radio"><label>' . zen_draw_radio_field('layout_box_location', '1', $right_status) . TEXT_RIGHT . '</label></div>'];
                $contents[] = ['text' => zen_draw_label(TEXT_INFO_LAYOUT_BOX_SORT_ORDER, 'layout_box_sort_order' , 'class="control-label"') . zen_draw_input_field('layout_box_sort_order', $bInfo->layout_box_sort_order, 'size="4" class="form-control" id="layout_box_sort_order"')];
                if ($include_single_column_settings) {
                  $contents[] = ['text' => zen_draw_label(TEXT_INFO_LAYOUT_BOX_SORT_ORDER_SINGLE, 'layout_box_sort_order_single', 'class="control-label"') . zen_draw_input_field('layout_box_sort_order_single', $bInfo->layout_box_sort_order_single, 'size="4" class="form-control" id="layout_box_sort_order_single"')];
                  $contents[] = ['text' => '<b>' . TEXT_INFO_LAYOUT_BOX_STATUS_SINGLE . '</b><div class="radio"><label>' . zen_draw_radio_field('layout_box_status_single', '1', $in_status_single) . TEXT_ON . '</label></div><div class="radio"><label>' . zen_draw_radio_field('layout_box_status_single', '0', $out_status_single) . TEXT_OFF . '</label></div>'];
                }
                $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button> <a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&layout_box_name=' . $bInfo->layout_box_name) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                break;
              case 'delete':
                $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_DELETE_BOX . '</h4>'];

                $contents = ['form' => zen_draw_form('column_controller', FILENAME_LAYOUT_CONTROLLER, $cur_page . '&action=deleteconfirm' . '&layout_box_name=' . $bInfo->layout_box_name) . zen_draw_hidden_field('cID', $bInfo->layout_id)];
                $contents[] = ['text' => TEXT_INFO_DELETE_INTRO];
                $contents[] = ['text' => '<b>' . $bInfo->layout_box_name . '</b>'];
                $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                break;
              default:
                if (is_object($bInfo)) {
                  $heading[] = ['text' => '<h4>' . TEXT_INFO_LAYOUT_BOX . $bInfo->layout_box_name . '</h4>'];
                  $contents[] = ['text' => '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>'];
                  $contents[] = ['text' => '<strong>' . TEXT_INFO_BOX_DETAILS . '</strong>'];
                  $contents[] = ['text' => TEXT_INFO_LAYOUT_BOX_NAME . ' ' . $bInfo->layout_box_name];
                  $contents[] = ['text' => TEXT_INFO_LAYOUT_BOX_STATUS . ' ' . ($bInfo->layout_box_status == '1' ? TEXT_ON : TEXT_OFF)];
                  $contents[] = ['text' => TEXT_INFO_LAYOUT_BOX_LOCATION . ' ' . ($bInfo->layout_box_location == '0' ? TEXT_LEFT : TEXT_RIGHT)];
                  $contents[] = ['text' => TEXT_INFO_LAYOUT_BOX_SORT_ORDER . ' ' . $bInfo->layout_box_sort_order];
                  if ($include_single_column_settings) {
                      $contents[] = ['text' => TEXT_INFO_LAYOUT_BOX_SORT_ORDER_SINGLE . ' ' . $bInfo->layout_box_sort_order_single];
                      $contents[] = ['text' => TEXT_INFO_LAYOUT_BOX_STATUS_SINGLE . ' ' . ($bInfo->layout_box_status_single == '1' ? TEXT_ON : TEXT_OFF)];
                  }

                  if ($sideboxFinder->sideboxPath($bInfo, $selected_template) == '') {
                    $contents[] = ['text' => '<strong>' . TEXT_INFO_DELETE_MISSING_LAYOUT_BOX . '<br>' . $selected_template . '</strong>'];
                    $contents[] = ['text' => TEXT_INFO_DELETE_MISSING_LAYOUT_BOX_NOTE . '<strong>' . $bInfo->layout_box_name . '</strong>'];
                    $contents[] = ['text' => '<a href="' . zen_href_link(FILENAME_LAYOUT_CONTROLLER, $cur_page . '&cID=' . $bInfo->layout_id . '&action=delete' . '&layout_box_name=' . $bInfo->layout_box_name) . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>'];
                  }
                }
                break;
            }

            if (!empty($heading) && !empty($contents)) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
        </div>

        <!-- end of display -->
      </div>

        <!-- resets -->
      <div class="row">
          <div class="col-sm-8 alert alert-warning text-center">
              <h2><?php echo TEXT_RESET_SETTINGS; ?></h2>
              <p><?php echo TEXT_INFO_RESET_TEMPLATE_SORT_ORDER; ?></p>

              <p><?php echo TEXT_INFO_RESET_TEMPLATE_SORT_ORDER_NOTE; ?></p>

              <?php
              $template_array_from = [];
              $template_array_from[] = ['id' => 0, 'text' => TEXT_ORIGINAL_DEFAULTS];
              $template_array_to = [];
              foreach($available_templates as $key => $value) {
                  if (isset($value['missing'])) continue;
                  $row = ['id' => $key, 'text' => $value['name']. ($key === $template_dir ? TEXT_THIS_IS_PRIMARY_TEMPLATE : '')];
                  $template_array_from[] = $row;
                  $template_array_to[] = $row;
              }

              echo zen_draw_form('templatecopysettings', FILENAME_LAYOUT_CONTROLLER, zen_get_all_get_params(['page', 'action']) . '&action=reset_defaults', 'post', 'class="form-inline"');
              echo zen_draw_hidden_field('action', 'reset_defaults');

              echo zen_draw_label(TEXT_SETTINGS_COPY_FROM, 'template_select_from', 'class="control-label"') . ' ' . PHP_EOL;
              echo zen_draw_pull_down_menu('tfrom', $template_array_from, $selected_template, 'class="form-control" id="template_select_from"') . ' ' . PHP_EOL;

              echo zen_draw_label(TEXT_SETTINGS_COPY_TO, 'template_select_to', 'class="control-label"') . ' ' . PHP_EOL;
              echo zen_draw_pull_down_menu('tto', $template_array_to, $selected_template, 'class="form-control" id="template_select_to"') . ' ' . PHP_EOL;
              ?>

              <button type="submit" class="btn btn-warning"><?php echo IMAGE_RESET; ?></button>
              <?php echo '</form>'; ?>
          </div>
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
