<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 19 Modified in v2.1.0-alpha2 $
 */
use Zencart\FileSystem\FileSystem;
use Zencart\ResourceLoaders\SideboxFinder;
use App\Models\LayoutBox;

require 'includes/application_top.php';

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
if ($selected_template !== $template_dir) {
    $messageStack->add(TEXT_CAUTION_EDITING_NOT_LIVE_TEMPLATE, 'error');
}

$include_single_column_settings = $available_templates[$selected_template]['uses_single_column_layout_settings'];
$uses_mobile_sidebox_settings = $available_templates[$selected_template]['uses_mobile_sidebox_settings'];

$sideboxFinder = new SideboxFinder(new Filesystem());
$sideboxes = $sideboxFinder->findFromFilesystem($installedPlugins, $selected_template);

$model = new LayoutBox();
$new_boxes = [];
foreach ($sideboxes as $sideboxFile => $plugin) {
    $result = $model
        ->where('layout_template', $selected_template)
        ->where('layout_box_name', $sideboxFile)
        ->first();
    if ($result) {
        continue;
    }
    $insertValues = [
        'layout_template' => $selected_template,
        'layout_box_name' => $sideboxFile,
        'layout_box_status' => 0,
        'layout_box_location' => 0,
        'layout_box_sort_order' => 3000,
        'layout_box_sort_order_single' => 3000,
        'layout_box_status_single' => 0,
        'plugin_details' => $plugin,
    ];
    $new_boxes[$model->query()->insertGetId($insertValues)] = $sideboxFile;
}

$action = $_GET['action'] ?? '';
switch ($action) {
    case 'save':
        if (!isset($_POST['left_active'], $_POST['right_active'], $_POST['inactive_lr'])) {
            zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER));
        }

        $layout_update = [];
        $left_active = explode(',', trim(str_replace(' ', '', $_POST['left_active']), ','));
        $sort_order = 0;
        foreach ($left_active as $next_box_id) {
            $layout_update[(int)$next_box_id] = [
                'layout_box_status' => 1,
                'layout_box_location' => 0,
                'layout_box_sort_order' => $sort_order,
            ];
            $sort_order += 20;
        }

        $right_active = explode(',', trim(str_replace(' ', '', $_POST['right_active']), ','));
        foreach ($right_active as $next_box_id) {
            $layout_update[(int)$next_box_id] = [
                'layout_box_status' => 1,
                'layout_box_location' => 1,
                'layout_box_sort_order' => $sort_order,
            ];
            $sort_order += 20;
        }

        $inactive_lr = explode(',', trim(str_replace(' ', '', $_POST['inactive_lr']), ','));
        foreach ($inactive_lr as $next_box_id) {
            $layout_update[(int)$next_box_id] = [
                'layout_box_status' => 0,
                'layout_box_location' => 0,
                'layout_box_sort_order' => 3000,
            ];
        }

        if ($include_single_column_settings === true) {
            if (!isset($_POST['single_active'], $_POST['inactive_single'])) {
                zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER));
            }

            $single_active = explode(',', trim(str_replace(' ', '', $_POST['single_active']), ','));
            $sort_order = 0;
            foreach ($single_active as $next_box_id) {
                if (!isset($layout_update[(int)$next_box_id])) {
                    $layout_update[(int)$next_box_id] = [
                        'layout_box_status_single' => 1,
                        'layout_box_sort_order_single' => $sort_order,
                    ];
                } else {
                    $layout_update[(int)$next_box_id] += [
                        'layout_box_status_single' => 1,
                        'layout_box_sort_order_single' => $sort_order,
                    ];
                }
                $sort_order += 20;
            }

            $inactive_single = explode(',', trim(str_replace(' ', '', $_POST['inactive_single']), ','));
            foreach ($inactive_single as $next_box_id) {
                if (!isset($layout_update[(int)$next_box_id])) {
                    $layout_update[(int)$next_box_id] = [
                        'layout_box_status_single' => 0,
                        'layout_box_sort_order_single' => 3000,
                    ];
                } else {
                    $layout_update[(int)$next_box_id] += [
                        'layout_box_status_single' => 0,
                        'layout_box_sort_order_single' => 3000,
                    ];
                }
            }
        }

        foreach ($layout_update as $box_id => $values) {
            $model->where('layout_id', $box_id)->update($values);
        }

        $messageStack->add_session(SUCCESS_BOX_UPDATED, 'success');
        zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER));
        break;

    case 'deleteconfirm':
        if (isset($_POST['delete_boxes'], $_POST['delete_boxes_names'])) {
            $boxes_to_remove = explode(',', $_POST['delete_boxes']);
            $boxes_names = explode(',', str_replace(' ', '', zen_db_prepare_input($_POST['delete_boxes_names'])));
            if (count($boxes_to_remove) === count($boxes_names)) {
                foreach ($boxes_to_remove as $index => $box_id) {
                   $model
                    ->where('layout_id', (int)$box_id)
                    ->where('layout_box_name', $boxes_names[$index])
                    ->delete();
                }
                $messageStack->add_session(SUCCESS_BOX_DELETED . zen_output_string_protected($_POST['delete_boxes_names']), 'success');
            }
        }
        zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER));
        break;

    case 'reset_defaults':
        // ensure this is a POST and that the required parameters have been supplied
        if (($_POST['action'] ?? '') !== 'reset_defaults' || !isset($_POST['tfrom'], $_POST['tto'])) {
            break;
        }

        // check for fake resets
        if (($_POST['tfrom'] !== '0' && !isset($available_templates[$_POST['tfrom']])) || !isset($available_templates[$_POST['tto']])) {
            $messageStack->add(TEXT_ERROR_INVALID_RESET_SUBMISSION, 'error');
            break;
        }

        if ($_POST['tfrom'] === '0') {
            $tfrom = 'default_template_settings';
        } else {
            $tfrom = strip_tags($_POST['tfrom']);
        }
        $tto = strip_tags($_POST['tto']);

        $reset_boxes = $model->where('layout_template', $tfrom)->get();
        foreach ($reset_boxes as $reset_box) {
            // This DOES include the single-column values, regardless of $include_single_column_settings value
            $updateValues = [
                'layout_box_status' => $reset_box['layout_box_status'],
                'layout_box_location' => $reset_box['layout_box_location'],
                'layout_box_sort_order' => $reset_box['layout_box_sort_order'],
                'layout_box_sort_order_single' => $reset_box['layout_box_sort_order_single'],
                'layout_box_status_single' => $reset_box['layout_box_status_single'],
            ];
            $model
                ->where('layout_box_name', $reset_box['layout_box_name'])
                ->where('layout_template', $tto)
                ->update($updateValues);
        }
        $messageStack->add_session(sprintf(SUCCESS_BOX_RESET, $tto, $tfrom), 'success');
        zen_redirect(zen_href_link(FILENAME_LAYOUT_CONTROLLER));
        break;

    default:
        break;
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
    <div id="lbc-container" class="container-fluid">
<?php
$template_array_from = [
    ['id' => 0, 'text' => TEXT_ORIGINAL_DEFAULTS],
];
$template_array_to = [];
foreach ($available_templates as $key => $value) {
    if (isset($value['missing'])) {
        continue;
    }
    $row = ['id' => $key, 'text' => $value['name'] . ($key === $template_dir ? TEXT_THIS_IS_PRIMARY_TEMPLATE : '')];
    $template_array_from[] = $row;
    $template_array_to[] = $row;
}
?>
        <h1><?= HEADING_TITLE . ' ' . $selected_template ?></h1>
        <div class="row my-1">
            <div class="col-md-6 col-lg-8 <?= ($selected_template !== $template_dir) ? 'alert alert-danger' : '' ?>">
                <div>
                    <strong><?= TABLE_HEADING_BOXES_PATH ?></strong><?= DIR_FS_CATALOG_MODULES . ' ... ' ?>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 alert alert-warning my-0 text-center">
                <?= zen_draw_form('templateselect', FILENAME_LAYOUT_CONTROLLER, '', 'post', 'class="form-inline"') .
                    zen_draw_label(TEXT_CURRENTLY_VIEWING, 'template_select', 'class="control-label"') . "\n" .
                    zen_draw_pull_down_menu('t', $template_array_to, $selected_template, 'class="form-control" id="template_select"') ?>
                    <button type="submit" class="btn btn-primary"><?= IMAGE_SELECT ?></button>
                <?= '</form>' ?>
            </div>
        </div>
        <hr class="border-dark">
<?php
if (count($new_boxes) !== 0) {
?>
        <div class="alert alert-warning"><?= TEXT_WARNING_NEW_BOXES_FOUND . implode(', ', array_values($new_boxes)) ?></div>
<?php
}

$layoutBoxes = $model
    ->where('layout_template', $selected_template)
    ->where('layout_box_name', 'not like', '%ezpages_bar')
    ->where('layout_box_name', 'not like', '%\_header.php')
    ->where('layout_box_name', 'not like', '%\_footer.php')
    ->orderBy('layout_box_sort_order')
    ->orderBy('layout_box_sort_order_single')
    ->orderBy('layout_box_name')
    ->get();
$left_active = [];
$right_active = [];
$left_right_inactive = [];
$mobile_active = [];
$mobile_inactive = [];
$missing = [];
foreach ($layoutBoxes as $layoutBox) {
    $boxDirectory = $sideboxFinder->sideboxPath($layoutBox, $selected_template);
    if ($boxDirectory === false) {
        $missing[$layoutBox['layout_box_name']] = $layoutBox['layout_id'];
        continue;
    }
    $currentBox = $boxDirectory . $layoutBox['layout_box_name'];
    if (empty($layoutBox['layout_box_status'])) {
        $left_right_inactive[$currentBox] = $layoutBox['layout_id'];
    } elseif (empty($layoutBox['layout_box_location'])) {
        $left_active[$currentBox] = $layoutBox['layout_id'];
    } else {
        $right_active[$currentBox] = $layoutBox['layout_id'];
    }
    if (empty($layoutBox['layout_box_status_single'])) {
        $mobile_inactive[$currentBox] = $layoutBox['layout_id'];
    } else {
        $mobile_active[$currentBox] = $layoutBox['layout_id'];
    }
}

if ($include_single_column_settings === true) {
    $layoutBoxes = $model
        ->where('layout_template', $selected_template)
        ->where('layout_box_name', 'like', '%\_header.php')
        ->orderBy('layout_box_sort_order_single')
        ->orderBy('layout_box_name')
        ->get();
    $header_active = [];
    $header_inactive = [];
    foreach ($layoutBoxes as $layoutBox) {
        $boxDirectory = $sideboxFinder->sideboxPath($layoutBox, $selected_template);
        if ($boxDirectory === false) {
            $missing[$layoutBox['layout_box_name']] = $layoutBox['layout_id'];
            continue;
        }
        $currentBox = $boxDirectory . $layoutBox['layout_box_name'];
        if (empty($layoutBox['layout_box_status_single'])) {
            $header_inactive[$currentBox] = $layoutBox['layout_id'];
        } else {
            $header_active[$currentBox] = $layoutBox['layout_id'];
        }
    }
    $header_boxes_present = (count($layoutBoxes) !== 0);

    $layoutBoxes = $model
        ->where('layout_template', $selected_template)
        ->where('layout_box_name', 'like', '%\_footer.php')
        ->orderBy('layout_box_sort_order_single')
        ->orderBy('layout_box_name')
        ->get();
    $footer_active = [];
    $footer_inactive = [];
    foreach ($layoutBoxes as $layoutBox) {
        $boxDirectory = $sideboxFinder->sideboxPath($layoutBox, $selected_template);
        if ($boxDirectory === false) {
            $missing[$layoutBox['layout_box_name']] = $layoutBox['layout_id'];
            continue;
        }
        $currentBox = $boxDirectory . $layoutBox['layout_box_name'];
        if (empty($layoutBox['layout_box_status_single'])) {
            $footer_inactive[$currentBox] = $layoutBox['layout_id'];
        } else {
            $footer_active[$currentBox] = $layoutBox['layout_id'];
        }
    }
    $footer_boxes_present = (count($layoutBoxes) !== 0);
}
?>
        <div class="row">
            <div class="col">
                <button class="btn btn-info" data-toggle="collapse" data-target="#instructions">
                    <?= BUTTON_SHOW_NOTES ?>
                </button>
                <button class="btn btn-info d-none" data-toggle="collapse" data-target="#instructions">
                    <?= BUTTON_HIDE_NOTES ?>
                </button>
                <div id="instructions" class="collapse pt-2">
                    <p><?= TEXT_INSTRUCTIONS ?></p>
                    <p><strong><?= TEXT_NOTES ?></strong></p>
                    <ol>
<?php
if ($include_single_column_settings === true) {
    $template_specific_boxes = '';
    if ($header_boxes_present === true) {
        $template_specific_boxes = TEXT_MOVE_HEADER_COLUMN;
    }
    if ($footer_boxes_present === true) {
        $template_specific_boxes .= ', ' . TEXT_MOVE_FOOTER_COLUMN;
    }
    if ($uses_mobile_sidebox_settings === true) {
        $template_specific_boxes .= ', ' . TEXT_MOVE_MOBILE_COLUMN;
    }
    $template_specific_boxes = ltrim($template_specific_boxes, ', ');
    if ($template_specific_boxes !== '') {
?>
                        <li class="py-1"><?= sprintf(TEXT_NOTE1_OPT, '<b>' . ucwords($template_specific_boxes) . '</b>', '<samp>' . $selected_template . '</samp>') ?></li>
<?php
    }
}
?>
                        <li class="py-1"><?= TEXT_NOTE1 ?></li>
                        <li class="py-1"><?= TEXT_NOTE2 ?></li>
                        <li class="py-1"><?= TEXT_NOTE3 ?></li>
                        <li class="py-1"><?= TEXT_NOTE4 ?></li>
                        <li class="py-1"><?= TEXT_NOTE5 ?></li>
                    </ol>
                </div>
            </div>
        </div>
<?php
if (count($missing) !== 0) {
?>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="panel panel-danger">
                    <div class="panel-heading text-center"><?= TEXT_HEADING_MISSING_BOXES ?></div>
                    <div class="panel-body pb-0">
                        <ul id="lbc-missing" class="list-group mb-0">
<?php
    foreach ($missing as $next_box => $next_box_id) {
?>
                            <li class="list-group-item list-group-item-danger my-1">
                                <div class="row">
                                    <div class="col-sm-9 pl-0 lbc-item">
                                        <?= $next_box ?>
                                    </div>
                                    <div class="col-sm-3 pr-0 text-right">
                                        <?= zen_draw_checkbox_field($next_box, '1', false, '', 'data-id="' . $next_box_id . '"') ?>
                                    </div>
                                </div>
                            </li>
<?php
    }
?>
                        </ul>
                        <div class="row text-center py-2">
                            <button id="remove-missing" class="btn btn-danger"><?= BUTTON_REMOVE_SELECTED ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
        <div id="remove-modal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <?= zen_draw_form('confirm-form', FILENAME_LAYOUT_CONTROLLER, 'action=deleteconfirm') ?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title text-center"><?= TEXT_INFO_HEADING_DELETE_BOX ?></h4>
                    </div>
                    <div class="modal-body">
                        <p id="missing-none-selected"><?= TEXT_NO_BOXES_TO_REMOVE ?></p>
                        <div id="missing-confirm" class="d-none">
                            <?= zen_draw_hidden_field('delete_boxes', 'placeholder', 'id="remove-boxes"') ?>
                            <?= zen_draw_hidden_field('delete_boxes_names', 'placeholder', 'id="remove-boxes-names"') ?>
                            <p>
                                <?= TEXT_INFO_DELETE_MISSING_LAYOUT_BOX_NOTE ?>
                                <span id="boxes-to-remove">&nbsp;</span>
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer text-center">
                        <button id="remove-button" type="submit" class="btn btn-danger d-none"><?= BUTTON_REMOVE_BOXES ?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?= BUTTON_CLOSE ?></button>
                    </div>
                    <?= '</form>' ?>
                </div>
            </div>
        </div>
<?php
}

// -----
// Determine whether the header/footer/mobile column will be displayed.
//
$show_single_column = $include_single_column_settings && ($uses_mobile_sidebox_settings || $header_boxes_present || $footer_boxes_present);
?>
        <div class="text-center py-2">
            <button class="btn btn-primary btn-save d-none"><?= BUTTON_SAVE_CHANGES ?></button>
        </div>
        <div id="lbc-main" class="row">
            <div id="lbc-lr" class="col-md-<?= ($show_single_column === true) ? '8' : '12' ?>">
                <div class="panel panel-info dataTableRow">
                    <div class="panel-heading text-center"><?= TEXT_HEADING_MAIN_PAGE_BOXES ?></div>
                    <div class="panel-body pb-1">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-success">
                                    <div class="panel-heading text-center">
<?php
if (COLUMN_LEFT_STATUS === '0') {
?>
                                        <a href="javascript:void(0);" data-toggle="popover" title="<?= TEXT_COLUMN_DISABLED ?>" data-content="<?= TEXT_DISABLED_MESSAGE ?>" data-trigger="focus">
                                            <i class="fa-solid fa-2x fa-circle-exclamation text-danger"></i>
                                        </a>
<?php
}
?>
                                        <?= TEXT_HEADING_ACTIVE_LEFT ?>
                                    </div>
                                    <div class="panel-body">
                                        <ul id="left-box" class="list-group lbc-box-lr mb-0">
<?php
foreach ($left_active as $next_box => $next_box_id) {
    $move_up_title = sprintf(TEXT_MOVE_BOX_UP, $next_box, TEXT_MOVE_MAIN_PAGE_COLUMN);
    $move_down_title = sprintf(TEXT_MOVE_BOX_DOWN, $next_box, TEXT_MOVE_MAIN_PAGE_COLUMN);
    $move_unused_title = sprintf(TEXT_MOVE_BOX_UNUSED, $next_box, TEXT_MOVE_MAIN_PAGE_COLUMN);
?>
                                            <li class="list-group-item my-1 lbc-item" data-id="<?= $next_box_id ?>">
                                                <div class="row">
                                                    <div class="col-sm-9 pl-0 pt-2">
                                                        <?= $next_box ?>
                                                    </div>
                                                    <div class="col-sm-3 pr-0 d-flex justify-content-around">
                                                        <i class="fa-solid fa-2x fa-xmark px-1" title="<?= $move_unused_title ?>"></i>
                                                        <i class="fa-solid fa-2x fa-angle-down px-1" title="<?= $move_down_title ?>"></i>
                                                        <i class="fa-solid fa-2x fa-angle-up px-1" title="<?= $move_up_title ?>"></i>
                                                    </div>
                                                </div>
                                            </li>
<?php
}
?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-success">
                                    <div class="panel-heading text-center">
<?php
if (COLUMN_RIGHT_STATUS === '0') {
?>
                                        <a href="javascript:void(0);" data-toggle="popover" title="<?= TEXT_COLUMN_DISABLED ?>" data-content="<?= TEXT_DISABLED_MESSAGE ?>" data-trigger="focus">
                                            <i class="fa-solid fa-2x fa-circle-exclamation text-danger"></i>
                                        </a>
<?php
}
?>
                                        <?= TEXT_HEADING_ACTIVE_RIGHT ?>
                                    </div>
                                    <div class="panel-body">
                                        <ul id="right-box" class="list-group lbc-box-lr mb-0">
<?php
foreach ($right_active as $next_box => $next_box_id) {
    $move_up_title = sprintf(TEXT_MOVE_BOX_UP, $next_box, TEXT_MOVE_MAIN_PAGE_COLUMN);
    $move_down_title = sprintf(TEXT_MOVE_BOX_DOWN, $next_box, TEXT_MOVE_MAIN_PAGE_COLUMN);
    $move_unused_title = sprintf(TEXT_MOVE_BOX_UNUSED, $next_box, TEXT_MOVE_MAIN_PAGE_COLUMN);
?>
                                            <li class="list-group-item my-1 lbc-item" data-id="<?= $next_box_id ?>">
                                                <div class="row">
                                                    <div class="col-sm-9 pl-0 pt-2">
                                                        <?= $next_box ?>
                                                    </div>
                                                    <div class="col-sm-3 pr-0 d-flex justify-content-around">
                                                        <i class="fa-solid fa-2x fa-xmark px-1" title="<?= $move_unused_title ?>"></i>
                                                        <i class="fa-solid fa-2x fa-angle-down px-1" title="<?= $move_down_title ?>"></i>
                                                        <i class="fa-solid fa-2x fa-angle-up px-1" title="<?= $move_up_title ?>"></i>
                                                    </div>
                                                </div>
                                            </li>
<?php
}
?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6">
                                <div class="panel panel-warning">
                                    <div class="panel-heading text-center"><?= TEXT_HEADING_INACTIVE_LEFT_RIGHT ?></div>
                                    <div class="panel-body">
                                        <ul id="unused" class="list-group lbc-box-lr mb-0">
<?php
ksort($left_right_inactive);
foreach ($left_right_inactive as $next_box => $next_box_id) {
    $move_up_title = sprintf(TEXT_MOVE_BOX_UP, $next_box, TEXT_MOVE_MAIN_PAGE_COLUMN);
    $move_down_title = sprintf(TEXT_MOVE_BOX_DOWN, $next_box, TEXT_MOVE_MAIN_PAGE_COLUMN);
    $move_unused_title = sprintf(TEXT_MOVE_BOX_UNUSED, $next_box, TEXT_MOVE_MAIN_PAGE_COLUMN);
?>
                                            <li class="list-group-item my-1 lbc-item" data-id="<?= $next_box_id ?>">
                                                <div class="row">
                                                    <div class="col-sm-9 pl-0 pt-2">
                                                        <?= $next_box ?>
                                                    </div>
                                                    <div class="col-sm-3 pr-0 d-flex justify-content-around">
                                                        <i class="fa-solid fa-2x fa-xmark px-1" title="<?= $move_unused_title ?>"></i>
                                                        <i class="fa-solid fa-2x fa-angle-down px-1" title="<?= $move_down_title ?>"></i>
                                                        <i class="fa-solid fa-2x fa-angle-up px-1" title="<?= $move_up_title ?>"></i>
                                                    </div>
                                                </div>
                                            </li>
<?php
}
?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                    </div>
                </div>
            </div>
<?php
if ($show_single_column === true) {
    if ($header_boxes_present === true) {
?>
            <div class="col-md-4">
                <div class="panel panel-info dataTableRow">
                    <div class="panel-heading text-center panel-collapse" data-toggle="collapse" data-target="#header-panel">
                        <?= TEXT_HEADING_HEADER_BOXES ?>
                        <br>
                        <button class="btn btn-info btn-sm lbc-show d-none">
                            <?= BUTTON_SHOW ?>
                        </button>
                        <button class="btn btn-info btn-sm lbc-hide">
                            <?= BUTTON_HIDE ?>
                        </button>
                    </div>
                    <div id="header-panel" class="panel-body collapse in pb-0">
                        <div class="panel panel-success">
                            <div class="panel-heading text-center"><?= TEXT_HEADING_ACTIVE_BOXES ?></div>
                            <div class="panel-body">
                                <ul id="header-box" class="list-group lbc-box-h mb-0">
<?php
        foreach ($header_active as $next_box => $next_box_id) {
            $move_up_title = sprintf(TEXT_MOVE_BOX_UP, $next_box, TEXT_MOVE_HEADER_COLUMN);
            $move_down_title = sprintf(TEXT_MOVE_BOX_DOWN, $next_box, TEXT_MOVE_HEADER_COLUMN);
            $move_unused_title = sprintf(TEXT_MOVE_BOX_UNUSED, $next_box, TEXT_MOVE_HEADER_COLUMN);
?>
                                    <li class="list-group-item my-1 lbc-item" data-id="<?= $next_box_id ?>">
                                        <div class="row">
                                            <div class="col-sm-9 pl-0 pt-2">
                                                <?= $next_box ?>
                                            </div>
                                            <div class="col-sm-3 pr-0 d-flex justify-content-around">
                                                <i class="fa-solid fa-2x fa-xmark px-1" title="<?= $move_unused_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-down px-1" title="<?= $move_down_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-up px-1" title="<?= $move_up_title ?>"></i>
                                            </div>
                                        </div>
                                    </li>
<?php
        }
?>
                                </ul>
                            </div>
                        </div>
                        <div class="panel panel-warning">
                            <div class="panel-heading text-center"><?= TEXT_HEADING_INACTIVE_BOXES ?></div>
                            <div class="panel-body">
                                <ul id="header-unused" class="list-group lbc-box-h mb-0">
<?php
        ksort($header_inactive);
        foreach ($header_inactive as $next_box => $next_box_id) {
            $move_up_title = sprintf(TEXT_MOVE_BOX_UP, $next_box, TEXT_MOVE_HEADER_COLUMN);
            $move_down_title = sprintf(TEXT_MOVE_BOX_DOWN, $next_box, TEXT_MOVE_HEADER_COLUMN);
            $move_unused_title = sprintf(TEXT_MOVE_BOX_UNUSED, $next_box, TEXT_MOVE_HEADER_COLUMN);
?>
                                    <li class="list-group-item my-1 lbc-item" data-id="<?= $next_box_id ?>">
                                        <div class="row">
                                            <div class="col-sm-9 pl-0 pt-2">
                                                <?= $next_box ?>
                                            </div>
                                            <div class="col-sm-3 pr-0 d-flex justify-content-around">
                                                <i class="fa-solid fa-2x fa-xmark px-1" title="<?= $move_unused_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-down px-1" title="<?= $move_down_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-up px-1" title="<?= $move_up_title ?>"></i>
                                            </div>
                                        </div>
                                    </li>
<?php
        }
?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
    }

    if ($footer_boxes_present === true) {
?>
            <div class="col-md-4">
                <div class="panel panel-info dataTableRow">
                    <div class="panel-heading text-center panel-collapse" data-toggle="collapse" data-target="#footer-panel">
                        <?= TEXT_HEADING_FOOTER_BOXES ?>
                        <br>
                        <button class="btn btn-info btn-sm lbc-show d-none">
                            <?= BUTTON_SHOW ?>
                        </button>
                        <button class="btn btn-info btn-sm lbc-hide">
                            <?= BUTTON_HIDE ?>
                        </button>
                    </div>
                    <div id="footer-panel" class="panel-body collapse in pb-0">
                        <div class="panel panel-success">
                            <div class="panel-heading text-center"><?= TEXT_HEADING_ACTIVE_BOXES ?></div>
                            <div class="panel-body">
                                <ul id="footer-box" class="list-group lbc-box-f mb-0">
<?php
        foreach ($footer_active as $next_box => $next_box_id) {
            $move_up_title = sprintf(TEXT_MOVE_BOX_UP, $next_box, TEXT_MOVE_FOOTER_COLUMN);
            $move_down_title = sprintf(TEXT_MOVE_BOX_DOWN, $next_box, TEXT_MOVE_FOOTER_COLUMN);
            $move_unused_title = sprintf(TEXT_MOVE_BOX_UNUSED, $next_box, TEXT_MOVE_FOOTER_COLUMN);
?>
                                    <li class="list-group-item my-1 lbc-item" data-id="<?= $next_box_id ?>">
                                        <div class="row">
                                            <div class="col-sm-9 pl-0 pt-2">
                                                <?= $next_box ?>
                                            </div>
                                            <div class="col-sm-3 pr-0 d-flex justify-content-around">
                                                <i class="fa-solid fa-2x fa-xmark px-1" title="<?= $move_unused_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-down px-1" title="<?= $move_down_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-up px-1" title="<?= $move_up_title ?>"></i>
                                            </div>
                                        </div>
                                    </li>
<?php
        }
?>
                                </ul>
                            </div>
                        </div>
                        <div class="panel panel-warning">
                            <div class="panel-heading text-center"><?= TEXT_HEADING_INACTIVE_BOXES ?></div>
                            <div class="panel-body">
                                <ul id="footer-unused" class="list-group lbc-box-f mb-0">
<?php
        ksort($footer_inactive);
        foreach ($footer_inactive as $next_box => $next_box_id) {
            $move_up_title = sprintf(TEXT_MOVE_BOX_UP, $next_box, TEXT_MOVE_FOOTER_COLUMN);
            $move_down_title = sprintf(TEXT_MOVE_BOX_DOWN, $next_box, TEXT_MOVE_FOOTER_COLUMN);
            $move_unused_title = sprintf(TEXT_MOVE_BOX_UNUSED, $next_box, TEXT_MOVE_FOOTER_COLUMN);
?>
                                    <li class="list-group-item my-1 lbc-item" data-id="<?= $next_box_id ?>">
                                        <div class="row">
                                            <div class="col-sm-9 pl-0 pt-2">
                                                <?= $next_box ?>
                                            </div>
                                            <div class="col-sm-3 pr-0 d-flex justify-content-around">
                                                <i class="fa-solid fa-2x fa-xmark px-1" title="<?= $move_unused_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-down px-1" title="<?= $move_down_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-up px-1" title="<?= $move_up_title ?>"></i>
                                            </div>
                                        </div>
                                    </li>
<?php
        }
?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
    }

    if ($uses_mobile_sidebox_settings === true) {
?>
            <div class="col-md-4">
                <div class="panel panel-info dataTableRow">
                    <div class="panel-heading text-center panel-collapse" data-toggle="collapse" data-target="#mobile-panel">
                        <?= TEXT_HEADING_MOBILE_BOXES ?>
                        <br>
                        <button class="btn btn-info btn-sm lbc-show d-none">
                            <?= BUTTON_SHOW ?>
                        </button>
                        <button class="btn btn-info btn-sm lbc-hide">
                            <?= BUTTON_HIDE ?>
                        </button>
                    </div>
                    <div id="mobile-panel" class="panel-body collapse in pb-0">
                        <div class="panel panel-success">
                            <div class="panel-heading text-center"><?= TEXT_HEADING_ACTIVE_BOXES ?></div>
                            <div class="panel-body">
                                <ul id="mobile-box" class="list-group lbc-box-m mb-0">
<?php
        foreach ($mobile_active as $next_box => $next_box_id) {
            $move_up_title = sprintf(TEXT_MOVE_BOX_UP, $next_box, TEXT_MOVE_MOBILE_COLUMN);
            $move_down_title = sprintf(TEXT_MOVE_BOX_DOWN, $next_box, TEXT_MOVE_MOBILE_COLUMN);
            $move_unused_title = sprintf(TEXT_MOVE_BOX_UNUSED, $next_box, TEXT_MOVE_MOBILE_COLUMN);
?>
                                    <li class="list-group-item my-1 lbc-item" data-id="<?= $next_box_id ?>">
                                        <div class="row">
                                            <div class="col-sm-9 pl-0 pt-2">
                                                <?= $next_box ?>
                                            </div>
                                            <div class="col-sm-3 pr-0 d-flex justify-content-around">
                                                <i class="fa-solid fa-2x fa-xmark px-1" title="<?= $move_unused_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-down px-1" title="<?= $move_down_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-up px-1" title="<?= $move_up_title ?>"></i>
                                            </div>
                                        </div>
                                    </li>
<?php
        }
?>
                                </ul>
                            </div>
                        </div>
                        <div class="panel panel-warning">
                            <div class="panel-heading text-center"><?= TEXT_HEADING_INACTIVE_BOXES ?></div>
                            <div class="panel-body">
                                <ul id="mobile-unused" class="list-group lbc-box-m mb-0">
<?php
        ksort($mobile_inactive);
        foreach ($mobile_inactive as $next_box => $next_box_id) {
            $move_up_title = sprintf(TEXT_MOVE_BOX_UP, $next_box, TEXT_MOVE_MOBILE_COLUMN);
            $move_down_title = sprintf(TEXT_MOVE_BOX_DOWN, $next_box, TEXT_MOVE_MOBILE_COLUMN);
            $move_unused_title = sprintf(TEXT_MOVE_BOX_UNUSED, $next_box, TEXT_MOVE_MOBILE_COLUMN);
?>
                                    <li class="list-group-item my-1 lbc-item" data-id="<?= $next_box_id ?>">
                                        <div class="row">
                                            <div class="col-sm-9 pl-0 pt-2">
                                                <?= $next_box ?>
                                            </div>
                                            <div class="col-sm-3 pr-0 d-flex justify-content-around">
                                                <i class="fa-solid fa-2x fa-xmark px-1" title="<?= $move_unused_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-down px-1" title="<?= $move_down_title ?>"></i>
                                                <i class="fa-solid fa-2x fa-angle-up px-1" title="<?= $move_up_title ?>"></i>
                                            </div>
                                        </div>
                                    </li>
<?php
        }
?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
    }
}
?>
        </div>
        <div class="text-center py-2">
            <button class="btn btn-primary btn-save d-none"><?= BUTTON_SAVE_CHANGES ?></button>
        </div>
        
        <?= zen_draw_form('saveForm', FILENAME_LAYOUT_CONTROLLER, 'action=save', 'post') .
            zen_draw_hidden_field('left_active', 'placeholder', 'id="left-active"') .
            zen_draw_hidden_field('right_active', 'placeholder', 'id="right-active"') .
            zen_draw_hidden_field('inactive_lr', 'placeholder', 'id="inactive-lr"') .
            zen_draw_hidden_field('single_active', 'placeholder', 'id="single-active"') .
            zen_draw_hidden_field('inactive_single', 'placeholder', 'id="inactive-single"') ?>
        <?= '</form>' ?>

        <!-- resets -->
        <hr class="border-dark">
        <div id="reset-settings" class="row">
            <div class="col-sm-2"></div>
            <div class="col-sm-8">
                <div class="panel panel-warning text-center">
                    <div class="panel-heading h2 my-0"><?= TEXT_RESET_SETTINGS ?></div>
                    <div class="panel-body">
                        <p><?= TEXT_INFO_RESET_TEMPLATE_SORT_ORDER ?></p>
                        <p><?= TEXT_INFO_RESET_TEMPLATE_SORT_ORDER_NOTE ?></p>

                        <?= zen_draw_form('templatecopysettings', FILENAME_LAYOUT_CONTROLLER, 'action=reset_defaults', 'post', 'class="form-inline"') .
                            zen_draw_hidden_field('action', 'reset_defaults') .
                            zen_draw_label(TEXT_SETTINGS_COPY_FROM, 'template_select_from', 'class="control-label"') . "\n" .
                            zen_draw_pull_down_menu('tfrom', $template_array_from, $selected_template, 'class="form-control" id="template_select_from"') . "\n" .

                            zen_draw_label(TEXT_SETTINGS_COPY_TO, 'template_select_to', 'class="control-label"') . "\n" .
                            zen_draw_pull_down_menu('tto', $template_array_to, $selected_template, 'class="form-control" id="template_select_to"') . "\n" ?>
                            <button type="submit" class="btn btn-warning"><?= IMAGE_RESET ?></button>
                        <?= '</form>' ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-2"></div>
        </div>

    <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <script>
// -----
// Add an event listener for beforeunload.  If changes have been made,
// the browser will display a "stay/leave" confirmation ... unless the
// admin timeout is active.
//
window.addEventListener('beforeunload', function (e) {
    if ($('.btn-save').is(':visible')) {
        if ($.jTimeout().getSecondsTillExpiration() > 0) {
            e.preventDefault();
        }
    }
});

$(function() {
    // -----
    // Activate the popover(s).
    //
    $('[data-toggle="popover"]').popover();

    // -----
    // Handle Bootstrap collapsable events to display the Hide/Show buttons.
    //
    $('.collapse').on('show.bs.collapse', function() {
        if ($(this).attr('id') === 'instructions') {
            $(this).prev().prev().hide();   //- Hide show
            $(this).prev().show();
        } else {
            $(this).prev().children('.lbc-show').hide();
            $(this).prev().children('.lbc-hide').show();
        }
    });
    $('.collapse').on('hide.bs.collapse', function() {
        console.log('hide: '+$(this).attr('id'));
        if ($(this).attr('id') === 'instructions') {
            $(this).prev().prev().show();
            $(this).prev().hide();
        } else {
            $(this).prev().children('.lbc-show').show();
            $(this).prev().children('.lbc-hide').hide();
        }
    });

    // -----
    // Multi-use function to "hide" (while keeping the space) the up-/down-angles
    // since movement is up/down within a respective box-group. For example, the
    // very last entry in the "Inactive" box-groups cannot be moved down, so that
    // down-angle is hidden.
    //
    function set_invisible() {
        $('#lbc-main ul > li i.fa-angle-up, #lbc-main ul > li i.fa-angle-down').removeClass('invisible');
        $('#left-box > li:first-child i.fa-angle-up').addClass('invisible');
        $('#unused > li:last-child i.fa-angle-down').addClass('invisible');
        $('#header-box > li:first-child i.fa-angle-up').addClass('invisible');
        $('#header-unused > li:last-child i.fa-angle-down').addClass('invisible');
        $('#footer-box > li:first-child i.fa-angle-up').addClass('invisible');
        $('#footer-unused > li:last-child i.fa-angle-down').addClass('invisible');
        $('#mobile-box > li:first-child i.fa-angle-up').addClass('invisible');
        $('#mobile-unused > li:last-child i.fa-angle-down').addClass('invisible');
        $('i.fa-xmark').removeClass('invisible');
        $('#unused i.fa-xmark, #header-unused i.fa-xmark, #footer-unused i.fa-xmark, #mobile-unused i.fa-xmark').addClass('invisible');
    }
    set_invisible();

    // -----
    // This jQuery extension is used on up-angle clicks, moving a sidebox
    // selection 'up' in its respective box-group.
    //
    $.fn.moveUp = function() {
        console.log('Moving up: '+$(this).closest('ul').attr('id'));
        if ($(this).prev().length !== 0) {
            $(this).insertBefore($(this).prev()).markMoved();
        } else if ($(this).closest('ul').attr('id') === 'right-box') {
            $('#left-box').append($(this));
            $('#left-box > li:last-child').markMoved();
        } else if ($(this).closest('ul').attr('id') === 'unused') {
            $('#right-box').append($(this));
            $('#right-box > li:last-child').markMoved();
        } else if ($(this).closest('ul').attr('id') === 'header-unused') {
            $('#header-box').append($(this));
            $('#header-box > li:last-child').markMoved();
        } else if ($(this).closest('ul').attr('id') === 'footer-unused') {
            $('#footer-box').append($(this));
            $('#footer-box > li:last-child').markMoved();
        } else if ($(this).closest('ul').attr('id') === 'mobile-unused') {
            $('#mobile-box').append($(this));
            $('#mobile-box > li:last-child').markMoved();
        }
        return this;
    };

    // -----
    // This jQuery extension is used on down-angle clicks, moving a sidebox
    // selection 'down' in its respective box-group.
    //
    $.fn.moveDown = function() {
        if ($(this).next().length !== 0) {
            $(this).insertAfter($(this).next()).markMoved();
        } else if ($(this).closest('ul').attr('id') === 'left-box') {
            $('#right-box').prepend($(this));
            $('#right-box > li:first-child').markMoved();
        } else if ($(this).closest('ul').attr('id') === 'right-box') {
            $('#unused').prepend($(this));
            $('#unused > li:first-child').markMoved(true);
        } else if ($(this).closest('ul').attr('id') === 'header-box') {
            $('#header-unused').prepend($(this));
            $('#header-unused > li:first-child').markMoved(true);
        } else if ($(this).closest('ul').attr('id') === 'footer-box') {
            $('#footer-unused').prepend($(this));
            $('#footer-unused > li:first-child').markMoved(true);
        } else if ($(this).closest('ul').attr('id') === 'mobile-box') {
            $('#mobile-unused').prepend($(this));
            $('#mobile-unused > li:first-child').markMoved(true);
        }
        return this;
    };

    // -----
    // This jQuery extension is used when a sidebox is moved and, if the
    // movement is not within an unused box-group, sets the class for
    // the visual display on the sidebox item and activates the "Save"
    // button.
    //
    $.fn.markMoved = function(force = false) {
        if (force === true || ($(this).closest('ul').attr('id').endsWith('unused') === false)) {
            $(this).addClass('list-group-item-warning');
            $('.btn-save').show();
        }
        return this;
    };

    // -----
    // On-click handlers for the up-/down-angle icons.
    //
    $('#lbc-main i.fa-angle-up').on('click', function(e){
        $(this).closest('li').moveUp();
        set_invisible();
    });
    $('#lbc-main i.fa-angle-down').on('click', function(e){
        $(this).closest('li').moveDown();
        set_invisible();
    });

    // -----
    // On-click handler for the X icon (move to unused).
    //
    $('#lbc-main i.fa-xmark').on('click', function(e){
        let sideBox = $(this).closest('li');
        if (sideBox.closest('ul').attr('id') === 'header-box') {
            $('#header-unused').prepend(sideBox);
            $('#header-unused > li:first-child').markMoved(true);
        } else if (sideBox.closest('ul').attr('id') === 'footer-box') {
            $('#footer-unused').prepend(sideBox);
            $('#footer-unused > li:first-child').markMoved(true);
        } else if (sideBox.closest('ul').attr('id') === 'mobile-box') {
            $('#mobile-unused').prepend(sideBox);
            $('#mobile-unused > li:first-child').markMoved(true);
        } else {
            $('#unused').prepend(sideBox);
            $('#unused > li:first-child').markMoved(true);
        }
        set_invisible();
    });

    // -----
    // Handling for the two box-groups' jQuery UI sortables.
    //
    var start_pos = 0;
    var start_box = '';

    $('#left-box, #right-box, #unused').sortable({
        connectWith: '.lbc-box-lr',

        // -----
        // Selection is moved outside of a droppable/sortable container.
        // Change the cursor to indicate that the selection can't
        // be dropped there.
        //
        out: function(e, ui) {
            document.body.style.cursor = 'not-allowed';
        },
        // -----
        // Selection is hovering on a droppable/sortable container.
        // Change the cursor to indicate that the selection is moveable.
        //
        over: function(e, ui) {
            document.body.style.cursor = 'move';
        },
        // -----
        // When a drag/sort action starts, capture the relative position and name of
        // the associated item; used when the action stops.
        //
        start: function(e, ui) {
            start_pos = [].slice.call(ui.item[0].parentNode.children).indexOf(ui.item[0]);
            start_box = $(ui.item[0]).closest('ul').attr('id');
        },
        // -----
        // Issued at the end of a drag/sort action. The sidebox is marked as 'moved'
        // if its relative position in its current box-location has changed or if
        // it's been moved to a different box.
        //
        stop: function(e, ui) {
            document.body.style.cursor = 'default';
            let stop_pos = [].slice.call(ui.item[0].parentNode.children).indexOf(ui.item[0]);
            if (stop_pos !== start_pos || $(ui.item[0]).closest('ul').attr('id') !== start_box) {
                $(ui.item).markMoved($(ui.item[0]).closest('ul').attr('id') !== start_box);
            }
            set_invisible();
        },
    });

    $('#header-box, #header-unused').sortable({
        connectWith: '.lbc-box-h',

        // -----
        // Selection is moved outside of a droppable/sortable container.
        // Change the cursor to indicate that the selection can't
        // be dropped there.
        //
        out: function(e, ui) {
            document.body.style.cursor = 'not-allowed';
        },
        // -----
        // Selection is hovering on a droppable/sortable container.
        // Change the cursor to indicate that the selection is moveable.
        //
        over: function(e, ui) {
            document.body.style.cursor = 'move';
        },
        // -----
        // When a drag/sort action starts, capture the relative position and name of
        // the associated item; used when the action stops.
        //
        start: function(e, ui) {
            start_pos = [].slice.call(ui.item[0].parentNode.children).indexOf(ui.item[0]);
            start_box = $(ui.item[0]).closest('ul').attr('id');
        },
        // -----
        // Issued at the end of a drag/sort action. The sidebox is marked as 'moved'
        // if its relative position in its current box-location has changed or if
        // it's been moved to a different box.
        //
        stop: function(e, ui) {
            document.body.style.cursor = 'default';
            let stop_pos = [].slice.call(ui.item[0].parentNode.children).indexOf(ui.item[0]);
            if (stop_pos !== start_pos || $(ui.item[0]).closest('ul').attr('id') !== start_box) {
                $(ui.item).markMoved($(ui.item[0]).closest('ul').attr('id') !== start_box);
            }
            set_invisible();
        },
    });

    $('#footer-box, #footer-unused').sortable({
        connectWith: '.lbc-box-f',

        // -----
        // Selection is moved outside of a droppable/sortable container.
        // Change the cursor to indicate that the selection can't
        // be dropped there.
        //
        out: function(e, ui) {
            document.body.style.cursor = 'not-allowed';
        },
        // -----
        // Selection is hovering on a droppable/sortable container.
        // Change the cursor to indicate that the selection is moveable.
        //
        over: function(e, ui) {
            document.body.style.cursor = 'move';
        },
        // -----
        // When a drag/sort action starts, capture the relative position and name of
        // the associated item; used when the action stops.
        //
        start: function(e, ui) {
            start_pos = [].slice.call(ui.item[0].parentNode.children).indexOf(ui.item[0]);
            start_box = $(ui.item[0]).closest('ul').attr('id');
        },
        // -----
        // Issued at the end of a drag/sort action. The sidebox is marked as 'moved'
        // if its relative position in its current box-location has changed or if
        // it's been moved to a different box.
        //
        stop: function(e, ui) {
            document.body.style.cursor = 'default';
            let stop_pos = [].slice.call(ui.item[0].parentNode.children).indexOf(ui.item[0]);
            if (stop_pos !== start_pos || $(ui.item[0]).closest('ul').attr('id') !== start_box) {
                $(ui.item).markMoved($(ui.item[0]).closest('ul').attr('id') !== start_box);
            }
            set_invisible();
        },
    });

    $('#mobile-box, #mobile-unused').sortable({
        connectWith: '.lbc-box-m',

        // -----
        // Selection is moved outside of a droppable/sortable container.
        // Change the cursor to indicate that the selection can't
        // be dropped there.
        //
        out: function(e, ui) {
            document.body.style.cursor = 'not-allowed';
        },
        // -----
        // Selection is hovering on a droppable/sortable container.
        // Change the cursor to indicate that the selection is moveable.
        //
        over: function(e, ui) {
            document.body.style.cursor = 'move';
        },
        // -----
        // When a drag/sort action starts, capture the relative position and name of
        // the associated item; used when the action stops.
        //
        start: function(e, ui) {
            start_pos = [].slice.call(ui.item[0].parentNode.children).indexOf(ui.item[0]);
            start_box = $(ui.item[0]).closest('ul').attr('id');
        },
        // -----
        // Issued at the end of a drag/sort action. The sidebox is marked as 'moved'
        // if its relative position in its current box-location has changed or if
        // it's been moved to a different box.
        //
        stop: function(e, ui) {
            document.body.style.cursor = 'default';
            let stop_pos = [].slice.call(ui.item[0].parentNode.children).indexOf(ui.item[0]);
            if (stop_pos !== start_pos || $(ui.item[0]).closest('ul').attr('id') !== start_box) {
                $(ui.item).markMoved($(ui.item[0]).closest('ul').attr('id') !== start_box);
            }
            set_invisible();
        },
    });

    // -----
    // On-click handler for the "Save Settings" action. This builds up
    // comma-separated 'location_id' values for the various locations into
    // the hidden variables that are submitted via the form's submit.
    //
    $('.btn-save').on('click', function(){
        let theValue = '';
        $('#left-box .lbc-item').each(function() {
            theValue += ','+$(this).data('id');
        });
        document.getElementById('left-active').value = theValue;

        theValue = '';
        $('#right-box .lbc-item').each(function() {
            theValue += ','+$(this).data('id');
        });
        document.getElementById('right-active').value = theValue;

        theValue = '';
        $('#unused .lbc-item').each(function() {
            theValue += ','+$(this).data('id');
        });
        document.getElementById('inactive-lr').value = theValue;

        theValue = '';
        $('#header-box .lbc-item').each(function() {
            theValue += ','+$(this).data('id');
        });
        $('#footer-box .lbc-item').each(function() {
            theValue += ','+$(this).data('id');
        });
        $('#mobile-box .lbc-item').each(function() {
            theValue += ','+$(this).data('id');
        });
        document.getElementById('single-active').value = theValue;

        theValue = '';
        $('#header-unused .lbc-item').each(function() {
            theValue += ','+$(this).data('id');
        });
        $('#footer-unused .lbc-item').each(function() {
            theValue += ','+$(this).data('id');
        });
        $('#mobile-unused .lbc-item').each(function() {
            theValue += ','+$(this).data('id');
        });
        document.getElementById('inactive-single').value = theValue;

        $('.btn-save').hide();
        document.saveForm.submit();
    });

    // -----
    // On-click handler for any sideboxes that have "gone missing", gathering
    // those checked for database removal and formatting the information
    // for the modal display.
    //
    $('#remove-missing').on('click', function() {
        $('#boxes-to-remove').text('');
        document.getElementById('remove-boxes').value = '';
        $('#lbc-missing input:checked').each(function() {
            $('#boxes-to-remove').text($('#boxes-to-remove').text()+$(this).attr('name')+', ');
            document.getElementById('remove-boxes').value += $(this).data('id')+',';
        });

        $('#missing-confirm, #remove-button').hide();
        $('#missing-none-selected').show();
        if (document.getElementById('remove-boxes').value !== '') {
            let theValue = document.getElementById('remove-boxes').value;
            document.getElementById('remove-boxes').value = theValue.substring(0, theValue.length - 1);

            theValue = $('#boxes-to-remove').text();
            document.getElementById('remove-boxes-names').value = theValue.substring(0, theValue.length - 2);
            $('#boxes-to-remove').text(document.getElementById('remove-boxes-names').value);

            $('#missing-confirm, #remove-button').show();
            $('#missing-none-selected').hide();
        }

        $('#remove-modal').modal();
    });
});
    </script>
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
