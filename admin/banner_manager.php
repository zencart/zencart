<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 04 Modified in v2.1.0-beta1 $
 */
require 'includes/application_top.php';
require 'includes/functions/functions_banner_graphs.php';

$action = ($_GET['action'] ?? '');
if (isset($_GET['bID'])) {
  $_GET['bID'] = (int)$_GET['bID'];
}
if (isset($_GET['flag'])) {
  $_GET['flag'] = (int)$_GET['flag'];
}
if (isset($_GET['page'])) {
  $_GET['page'] = (int)$_GET['page'];
}
if (isset($_GET['flagbanners_open_new_windows'])) {
  $_GET['flagbanners_open_new_windows'] = (int)$_GET['flagbanners_open_new_windows'];
}

if (!empty($action)) {
  switch ($action) {
    case 'setflag':
      if (($_GET['flag'] == '0') || ($_GET['flag'] == '1')) {
        zen_set_banner_status($_GET['bID'], $_GET['flag']);

        $messageStack->add_session(SUCCESS_BANNER_STATUS_UPDATED, 'success');
      } else {
        $messageStack->add_session(ERROR_UNKNOWN_STATUS_FLAG, 'error');
      }

      zen_redirect(zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']));
      break;

    case 'setbanners_open_new_windows':
      if (($_GET['flagbanners_open_new_windows'] == '0') || ($_GET['flagbanners_open_new_windows'] == '1')) {
        $db->Execute("UPDATE " . TABLE_BANNERS . "
                      SET banners_open_new_windows = " . (int)$_GET['flagbanners_open_new_windows'] . "
                      WHERE banners_id = " . (int)$_GET['bID']);

        $messageStack->add_session(SUCCESS_BANNER_OPEN_NEW_WINDOW_UPDATED, 'success');
      } else {
        $messageStack->add_session(ERROR_UNKNOWN_BANNER_OPEN_NEW_WINDOW, 'error');
      }

      zen_redirect(zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']));
      break;
    case 'insert': // deprecated
    case 'update': // deprecated
    case 'add':
    case 'upd':
      if (isset($_POST['banners_id'])) {
        $banners_id = (int)$_POST['banners_id'];
      }
      $banners_title = zen_db_prepare_input($_POST['banners_title']);
      $banners_url = zen_db_prepare_input($_POST['banners_url']);
      $new_banners_group = zen_db_prepare_input($_POST['new_banners_group']);
      $banners_group = (empty($new_banners_group)) ? zen_db_prepare_input($_POST['banners_group']) : $new_banners_group;
      $banners_html_text = zen_db_prepare_input($_POST['banners_html_text']);
      $banners_image_local = zen_db_prepare_input($_POST['banners_image_local']);
      $banners_image_target = zen_db_prepare_input($_POST['banners_image_target']);
      $db_image_location = '';

      $banner_error = false;

      $expires_date_raw = zen_db_prepare_input($_POST['expires_date']);
      if ($expires_date_raw === '') {
          $expires_date = 'null';
      } else {
          if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($expires_date_raw)) {
            $local_fmt = zen_datepicker_format_fordate();
            $dt = DateTime::createFromFormat($local_fmt, $expires_date_raw);
            $expires_date_raw = 'null';
            if (!empty($dt)) {
              $expires_date_raw = $dt->format('Y-m-d');
            }
          }
          if (zcDate::validateDate($expires_date_raw) === true) {
            $expires_date = $expires_date_raw;
          } else {
            $banner_error = true;
            $messageStack->add(ERROR_INVALID_EXPIRES_DATE, 'error');
          }
      }

      $expires_impressions = (int)$_POST['expires_impressions'];

      $date_scheduled_raw = zen_db_prepare_input($_POST['date_scheduled']);
      if ($date_scheduled_raw === '') {
          $date_scheduled = 'null';
      } else {
          if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($date_scheduled_raw)) {
            $local_fmt = zen_datepicker_format_fordate();
            $dt = DateTime::createFromFormat($local_fmt, $date_scheduled_raw);
            $date_scheduled_raw = 'null';
            if (!empty($dt)) {
              $date_scheduled_raw = $dt->format('Y-m-d');
            }
          }
          if (zcDate::validateDate($date_scheduled_raw) === true) {
            $date_scheduled = $date_scheduled_raw;
          } else {
            $banner_error = true;
            $messageStack->add(ERROR_INVALID_SCHEDULED_DATE, 'error');
          }
      }

      $status = (int)$_POST['status'];
      $banners_open_new_windows = (int)$_POST['banners_open_new_windows'];
      $banners_sort_order = (int)$_POST['banners_sort_order'];

      if (empty($banners_title)) {
        $messageStack->add(ERROR_BANNER_TITLE_REQUIRED, 'error');
        $banner_error = true;
      }

      if (empty($banners_group)) {
        $messageStack->add(ERROR_BANNER_GROUP_REQUIRED, 'error');
        $banner_error = true;
      }

      // If an image has been uploaded, parse it far enough to validate it, but not yet save it
      $banners_image = new upload('banners_image');
      $banners_image->set_extensions(['jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg']);
      $banners_image->set_destination(DIR_FS_CATALOG_IMAGES . $banners_image_target);
      $has_uploaded_image = $banners_image->parse();

      // if we can't save the uploaded image, and no local image is supplied, then if the banner has no HTML content, throw error
      if (empty($banners_image_local) || $has_uploaded_image) {
          if (!($uploaded_image = $banners_image->save())) {
              if (empty($banners_html_text)) {
                  $messageStack->add(ERROR_BANNER_IMAGE_REQUIRED, 'error');
                  $banner_error = true;
              }
          }
      }

      // use local (or user-typed) image filename first
      $db_image_location = $banners_image_local;
      // override with uploaded file, if validated
      if (!empty($uploaded_image)) {
          $db_image_location = $banners_image_target . $banners_image->filename;
      }

      if (!$banner_error) {
        $db_image_location = zen_limit_image_filename($db_image_location, TABLE_BANNERS, 'banners_image');
        $banners_url = zen_limit_image_filename($banners_url, TABLE_BANNERS, 'banners_url');
        $sql_data_array = [
          'banners_title' => $banners_title,
          'banners_url' => $banners_url,
          'banners_image' => $db_image_location,
          'banners_group' => $banners_group,
          'banners_html_text' => $banners_html_text,
          'status' => $status,
          'banners_open_new_windows' => $banners_open_new_windows,
          'banners_sort_order' => $banners_sort_order,
        ];

        if ($action == 'add') {
          $insert_sql_data = [
            'date_added' => 'now()',
            'status' => '1',
          ];

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_BANNERS, $sql_data_array);

          $banners_id = zen_db_insert_id();

          $messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
        } elseif ($action == 'upd') {
          zen_db_perform(TABLE_BANNERS, $sql_data_array, 'update', "banners_id = " . (int)$banners_id);

          $messageStack->add_session(SUCCESS_BANNER_UPDATED, 'success');
        }

// NOTE: status will be reset by the /functions/banner.php
// build new update sql for date_scheduled, expires_date and expires_impressions

        $sql = "UPDATE " . TABLE_BANNERS . "
                SET date_scheduled = DATE_ADD(:scheduledDate, INTERVAL '00:00:00' HOUR_SECOND),
                    expires_date = DATE_ADD(:expiresDate, INTERVAL '23:59:59' HOUR_SECOND),
                    expires_impressions = " . ($expires_impressions == 0 ? "null" : ":expiresImpressions") . "
                WHERE banners_id = :bannersID";
        $sql = $db->bindVars($sql, ':expiresImpressions', $expires_impressions, 'integer');
        $sql = $db->bindVars($sql, ':scheduledDate', $date_scheduled, 'date');
        $sql = $db->bindVars($sql, ':expiresDate', $expires_date, 'date');
        $sql = $db->bindVars($sql, ':bannersID', $banners_id, 'integer');
        $db->Execute($sql);

        zen_redirect(zen_href_link(FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'bID=' . $banners_id));
      } else {
        $action = 'new';
      }
      break;
    case 'deleteconfirm':
      $banners_id = zen_db_prepare_input($_POST['bID']);

      if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
        $banner = $db->Execute("SELECT banners_image
                                FROM " . TABLE_BANNERS . "
                                WHERE banners_id = " . (int)$banners_id);

        if (is_file(DIR_FS_CATALOG_IMAGES . $banner->fields['banners_image'])) {
          if (is_writeable(DIR_FS_CATALOG_IMAGES . $banner->fields['banners_image'])) {
            unlink(DIR_FS_CATALOG_IMAGES . $banner->fields['banners_image']);
          } else {
            $messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
          }
        } else {
          $messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
        }
      }

      $db->Execute("DELETE FROM " . TABLE_BANNERS . "
                    WHERE banners_id = " . (int)$banners_id);
      $db->Execute("DELETE FROM " . TABLE_BANNERS_HISTORY . "
                    WHERE banners_id = " . (int)$banners_id);

      $messageStack->add_session(SUCCESS_BANNER_REMOVED, 'success');

      zen_redirect(zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']));
      break;
  }
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <link rel="stylesheet" href="includes/css/banner_tools.css">
    <script>
      function popupImageWindow(url) {
        window.open(url, 'popupImageWindow', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
      }
    </script>
    <?php if ($editor_handler != '') include ($editor_handler); ?>
  </head>
  <body>
    <div id="spiffycalendar" class="text"></div>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!--[if lte IE 8]><script src="includes/javascript/flot/excanvas.min.js"></script><![endif]-->
    <script src="includes/javascript/flot/jquery.flot.min.js"></script>
    <script src="includes/javascript/flot/jquery.flot.orderbars.js"></script>

    <!-- body //-->
    <div class="container-fluid">
      <h1><?= HEADING_TITLE ?></h1>
      <!-- body_text //-->
      <?php if ($action == '') { ?>
        <div class="row">
          <table class="table-condensed">
            <tr>
              <td class="text-right"><?= TEXT_LEGEND ?></td>
              <td class="text-center"><?= TABLE_HEADING_STATUS ?></td>
              <td class="text-center"><?= TEXT_LEGEND_BANNER_OPEN_NEW_WINDOWS ?></td>
            </tr>
            <tr>
              <td class="text-right"></td>
              <td class="text-center">
                <?= zen_icon('enabled', IMAGE_ICON_STATUS_ON, '2x', hidden: true) ?>
                &nbsp;
                <?= zen_icon('disabled', IMAGE_ICON_STATUS_OFF, '2x', hidden: true) ?>
              </td>
              <td class="text-center actions"><div class="btn-group">
                <?= zen_icon('new-window', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_ON, '2x', hidden: true) ?>
                &nbsp;
                <?= zen_icon('new-window-off', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_OFF, hidden: true) ?>
              </div></td>
            </tr>
          </table>
        </div>
      <?php } // legend ?>
      <?php
      if ($action == 'new') {
        $form_action = 'add';

        $parameters = [
          'expires_date' => '',
          'date_scheduled' => '',
          'banners_title' => '',
          'banners_url' => '',
          'banners_group' => '',
          'banners_image' => '',
          'banners_html_text' => '',
          'expires_impressions' => '',
          'banners_open_new_windows' => 1,
          'status' => 1,
        ];

        $bInfo = new objectInfo($parameters);

        if (isset($_GET['bID'])) {
          $form_action = 'upd';

          $bID = zen_db_prepare_input($_GET['bID']);

          $banner = $db->Execute("SELECT banners_title, banners_url, banners_image, banners_group,
                                         banners_html_text, status,
                                         date_format(date_scheduled, '" . zen_datepicker_format_forsql() . "') AS date_scheduled,
                                         date_format(expires_date, '" . zen_datepicker_format_forsql() . "') AS expires_date,
                                         expires_impressions, date_status_change, banners_open_new_windows, banners_sort_order
                                  FROM " . TABLE_BANNERS . "
                                  WHERE banners_id = " . (int)$bID);

          $bInfo->updateObjectInfo($banner->fields);
        } elseif (!empty($_POST)) {
          $bInfo->updateObjectInfo($_POST);
        }

        $groups_array = [];
        $groups = $db->Execute("SELECT DISTINCT banners_group
                                FROM " . TABLE_BANNERS . "
                                ORDER BY banners_group");
        foreach ($groups as $group) {
          $groups_array[] = [
            'id' => $group['banners_group'],
            'text' => $group['banners_group'],
          ];
        }
        ?>
        <div class="row">
          <?php
          echo zen_draw_form('new_banner', FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data" class="form-horizontal"');
          if ($form_action == 'upd') {
            echo zen_draw_hidden_field('banners_id', $bID);
          }
          ?>
          <div class="form-group">
            <div class="col-sm-3">
              <p class="control-label"><?= TEXT_BANNERS_STATUS ?></p>
            </div>
            <div class="col-sm-9 col-md-6">
              <label class="radio-inline"><?= zen_draw_radio_field('status', '1', $bInfo->status == 1) . TEXT_BANNERS_ACTIVE ?></label>
              <label class="radio-inline"><?= zen_draw_radio_field('status', '0', $bInfo->status == 0) . TEXT_BANNERS_NOT_ACTIVE ?></label>
              <span class="help-block"><?= TEXT_INFO_BANNER_STATUS ?></span>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <p class="control-label"><?= TEXT_BANNERS_OPEN_NEW_WINDOWS ?></p>
            </div>
            <div class="col-sm-9 col-md-6">
              <label class="radio-inline"><?= zen_draw_radio_field('banners_open_new_windows', '1', $bInfo->banners_open_new_windows == 1) . TEXT_YES ?></label>
              <label class="radio-inline"><?= zen_draw_radio_field('banners_open_new_windows', '0', $bInfo->banners_open_new_windows == 0) . TEXT_NO ?></label><br>
              <span class="help-block"><?= TEXT_INFO_BANNER_OPEN_NEW_WINDOWS ?></span>
            </div>
          </div>
          <div class="form-group">
            <?= zen_draw_label(TEXT_BANNERS_TITLE, 'banners_title', 'class="col-sm-3 control-label"') ?>
            <div class="col-sm-9 col-md-6">
              <?= zen_draw_input_field('banners_title', htmlspecialchars($bInfo->banners_title, ENT_COMPAT, CHARSET), zen_set_field_length(TABLE_BANNERS, 'banners_title') . ' class="form-control" id="banners_title"', true) ?>
            </div>
          </div>
          <div class="form-group">
            <?= zen_draw_label(TEXT_BANNERS_URL, 'banners_url', 'class="col-sm-3 control-label"') ?>
            <div class="col-sm-9 col-md-6">
              <?= zen_draw_input_field('banners_url', $bInfo->banners_url, zen_set_field_length(TABLE_BANNERS, 'banners_url') . ' class="form-control" id="banners_url"') ?>
            </div>
          </div>
          <div class="form-group">
            <?= zen_draw_label(TEXT_BANNERS_GROUP, 'banners_group', 'class="col-sm-3 control-label"') ?>
            <div class="col-sm-4 col-md-3">
              <?= zen_draw_pull_down_menu('banners_group', $groups_array, $bInfo->banners_group, 'class="form-control" id="banners_group"') ?>
              <p><?= TEXT_BANNERS_NEW_GROUP ?></p><?= zen_draw_input_field('new_banners_group', '', 'class="form-control" id="new_banners_group"', count($groups_array) === 0) ?>
            </div>
          </div>

          <div style="border: 1px solid grey; padding: 10px">
            <div class="form-group row mt-2">
                <div class="col-sm-offset-3 col-sm-9"><?= TEXT_BANNERS_IMAGE_LOCAL ?></div>
                <?= zen_draw_label(TEXT_BANNERS_CURRENT_IMAGE, 'banners_image_local', 'class="col-sm-3 control-label"') ?>
                <div class="col-sm-9 col-md-6">
                    <div class="input-group">
                        <span class="input-group-addon"><?= DIR_FS_CATALOG_IMAGES ?></span>
                        <?= zen_draw_input_field('banners_image_local', ($bInfo->banners_image ?? ''), zen_set_field_length(TABLE_BANNERS, 'banners_image') . 'id="banners_image_local" class="form-control"') ?>
                    </div>
                </div>
            </div>
            <div class="form-group row mt-2">
                <?= zen_draw_label(TEXT_BANNERS_IMAGE, 'banners_image', 'class="col-sm-3 control-label"') ?>
                <div class="col-sm-9 col-md-6">
                    <?= zen_draw_file_field('banners_image', '', 'class="form-control" id="banners_image"') ?>
                </div>
            </div>
            <div class="form-group row mt-2">
                <?= zen_draw_label(TEXT_BANNERS_IMAGE_TARGET, 'banners_image_target', 'class="col-sm-3 control-label"') ?>
                <div class="col-sm-9 col-md-6">
                    <div class="input-group"><span class="input-group-addon"><?= DIR_FS_CATALOG_IMAGES ?></span>
                        <?= zen_draw_input_field('banners_image_target', 'banners/', 'class="form-control" id="banners_image_target"') ?>
                    </div>
                    <div>
                        <?= TEXT_BANNER_IMAGE_TARGET_INFO ?>
                    </div>
                </div>
            </div>
          </div>

          <div class="form-group mt-4">
            <?= zen_draw_label(TEXT_BANNERS_HTML_TEXT, 'banners_html_text', 'class="col-sm-3 control-label"') ?>
            <div class="col-sm-9 col-md-6">
              <?= '<p>' . TEXT_BANNERS_HTML_TEXT_INFO . '</p>' . zen_draw_textarea_field('banners_html_text', 'soft', '80', '10', htmlspecialchars($bInfo->banners_html_text, ENT_COMPAT, CHARSET), 'class="editorHook form-control" id="banners_html_text"') ?>
            </div>
          </div>
          <div class="form-group">
            <?= zen_draw_label(TEXT_BANNERS_ALL_SORT_ORDER, 'banners_sort_order', 'class="col-sm-3 control-label"') ?>
            <div class="col-sm-9 col-md-6">
              <?= TEXT_BANNERS_ALL_SORT_ORDER_INFO . '<br>' . zen_draw_input_field('banners_sort_order', $bInfo->banners_sort_order, zen_set_field_length(TABLE_BANNERS, 'banners_sort_order') . ' class="form-control" id="banners_sort_order"') ?>
            </div>
          </div>
          <div class="form-group">
            <?= zen_draw_label(TEXT_BANNERS_SCHEDULED_AT, 'date_scheduled', 'class="col-sm-3 control-label"') ?>
            <div class="col-sm-9 col-md-6">
              <div class="date input-group" id="datepicker_date_scheduled">
                <span class="input-group-addon datepicker_icon">
                  <?= zen_icon('calendar-days', size: 'lg') ?>
                </span>
                <?= zen_draw_input_field('date_scheduled', $bInfo->date_scheduled, 'class="form-control" id="date_scheduled" autocomplete="off"') ?>
              </div>
              <span class="help-block errorText">(<?= zen_datepicker_format_full() ?>) <span class="date-check-error"><?= ERROR_INVALID_SCHEDULED_DATE ?></span></span>
            </div>
          </div>
          <div class="form-group">
            <?= zen_draw_label(TEXT_BANNERS_EXPIRES_ON, 'expires_date', 'class="col-sm-3 control-label"') ?>
            <div class="col-sm-9 col-md-6">
              <div class="date input-group" id="datepicker_expires_date">
                <span class="input-group-addon datepicker_icon">
                  <?= zen_icon('calendar-days', size: 'lg') ?>
                </span>
                <?= zen_draw_input_field('expires_date', $bInfo->expires_date, 'class="form-control" id="expires_date" autocomplete="off"') ?>
              </div>
              <span class="help-block errorText">(<?= zen_datepicker_format_full() ?>) <span class="date-check-error"><?= ERROR_INVALID_EXPIRES_DATE ?></span></span>
              <?= TEXT_BANNERS_OR_AT ?>
            </div>
          </div>
          <?php require DIR_WS_INCLUDES . 'javascript/dateChecker.php'; ?>
          <div class="form-group">
            <?= zen_draw_label(TEXT_BANNERS_IMPRESSIONS, 'expires_impressions', 'class="control-label col-sm-3"') ?>
            <div class="col-sm-9 col-md-6">
              <?= zen_draw_input_field('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7" class="form-control" id="expires_impressions"') ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 text-right">
              <button type="submit" class="btn btn-primary"><?= (($form_action == 'add') ? IMAGE_INSERT : IMAGE_UPDATE) ?></button> <a href="<?= zen_href_link(FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['bID']) ? 'bID=' . (int)$_GET['bID'] : '')) ?>" class="btn btn-default" role="button"><?= IMAGE_CANCEL ?></a>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9 col-md-6">
              <?= TEXT_BANNERS_BANNER_NOTE . '<br>' . TEXT_BANNERS_INSERT_NOTE . '<br>' . TEXT_BANNERS_EXPIRY_NOTE . '<br>' . TEXT_BANNERS_SCHEDULE_NOTE ?>
            </div>
          </div>
          <?= '</form>' ?>
        </div>
        <?php
      } else {
        ?>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover table-striped">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?= TABLE_HEADING_BANNERS ?></th>
                  <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_GROUPS ?></th>
                  <th class="dataTableHeadingContent"><?= TABLE_HEADING_POSITIONS ?></th>
                  <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_STATISTICS ?></th>
                  <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_STATUS ?></th>
                  <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_BANNER_OPEN_NEW_WINDOWS ?></th>
                  <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_BANNER_SORT_ORDER ?></th>
                  <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_ACTION ?></th>
                </tr>
              </thead>
              <tbody>
                <?php
                $banners_query_raw = 'SELECT banners_id, banners_title, banners_image, banners_group, status,
                                             expires_date, expires_impressions, date_status_change, date_scheduled,
                                             date_added, banners_open_new_windows, banners_sort_order
                                      FROM ' . TABLE_BANNERS . '
                                      ORDER BY banners_group, banners_title';
// Split Page
// reset page when page is unknown
                if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['bID'])) {
                  $check_page = $db->Execute($banners_query_raw);
                  $check_count = 0;
                  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
                    foreach ($check_page as $item) {
                      if ($item['banners_id'] == (int)$_GET['bID']) {
                        break;
                      }
                      $check_count++;
                    }
                    $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)));
                  } else {
                    $_GET['page'] = 1;
                  }
                }
                $banners_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $banners_query_raw, $banners_query_numrows);
                $banners = $db->Execute($banners_query_raw);
                foreach ($banners as $banner) {
                  $info = $db->Execute("SELECT SUM(banners_shown) AS banners_shown,
                                               SUM(banners_clicked) AS banners_clicked
                                        FROM " . TABLE_BANNERS_HISTORY . "
                                        WHERE banners_id = " . (int)$banner['banners_id']);

                  if ((empty($_GET['bID']) || $_GET['bID'] == $banner['banners_id']) && empty($bInfo) && substr($action, 0, 3) != 'new') {
                    $bInfo_array = array_merge($banner, $info->fields);
                    $bInfo = new objectInfo($bInfo_array);
                  }

                  $banners_shown = ($info->fields['banners_shown'] != '') ? $info->fields['banners_shown'] : '0';
                  $banners_clicked = ($info->fields['banners_clicked'] != '') ? $info->fields['banners_clicked'] : '0';

                  if (isset($bInfo) && is_object($bInfo) && ($banner['banners_id'] == $bInfo->banners_id)) {
                    ?>
                    <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?= zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=new') ?>'">
                      <?php
                    } else {
                      ?>
                    <tr class="dataTableRow" onclick="document.location.href = '<?= zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id']) ?>'">
                      <?php
                    }
                    ?>
                    <td class="dataTableContent">
                      <a href="javascript:popupImageWindow('<?= zen_href_link(FILENAME_POPUP_IMAGE, 'banner=' . $banner['banners_id']) ?>')" title="View Banner"><i class="fa-regular fa-window-restore fa-lg txt-black" aria-hidden="true"></i></a>&nbsp;<?= $banner['banners_title'] ?></td>
                    <td class="dataTableContent text-right"><?= $banner['banners_group'] ?></td>
                    <td class="dataTableContent">
                        <?php
                        $banner_positions = $db->Execute(
                            'SELECT configuration_key, configuration_title, configuration_value
                                                 FROM ' . TABLE_CONFIGURATION . '
                                                 WHERE configuration_key LIKE "SHOW_BANNERS_GROUP_SET%"
                                                 AND INSTR(configuration_value, "' . $banner['banners_group'] . '")'
                        );
                        // a banner group may be used in multiple positions: get each position
                        $positions = [];
                        foreach ($banner_positions as $banner_position) {
                            // remove text prior to the hyphen in the configuraiton_title to leave the position (e.g. "Banner Display Group - Side Box banner_box_all"  "Banner Display Groups - Footer Position 3")
                            // allows for optional spaces around hyphens
                            $position_texts = preg_split('/\s?-\s?/', $banner_position['configuration_title']);
                            $positions[] = $position_texts !== false ? $position_texts[1] : '';
                        }
                        echo '<div class="text-nowrap">' . implode('<br>', $positions) . '</div>';
                        ?>
                    </td>
                    <td class="dataTableContent text-center"><?= $banners_shown . ' / ' . $banners_clicked ?></td>
                    <td class="dataTableContent text-center">
                      <?php if ($banner['status'] == '1') { ?>
                        <a href="<?= zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setflag&flag=0') ?>" data-toggle="tooltip" title="<?= IMAGE_ICON_STATUS_ON ?>"><?= zen_icon('enabled', '', '2x', false, true) ?></a>
                      <?php } else { ?>
                        <a href="<?= zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setflag&flag=1') ?>" data-toggle="tooltip" title="<?= IMAGE_ICON_STATUS_OFF ?>"><?= zen_icon('disabled', '', '2x', false, true) ?></a>
                      <?php } ?>
                    </td>
                    <td class="dataTableContent text-center">
                      <?php if ($banner['banners_open_new_windows'] == '1') { ?>
                        <a href="<?= zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setbanners_open_new_windows&flagbanners_open_new_windows=0') ?>" data-toggle="tooltip" title="<?= IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_ON ?>">
                          <?= zen_icon('new-window', '', '2x', false, true) ?>
                        </a>
                      <?php } else { ?>
                        <a href="<?= zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setbanners_open_new_windows&flagbanners_open_new_windows=1') ?>">
                          <?= zen_icon('new-window-off', '', '2x', false, true) ?>
                        </a>
                      <?php } ?>
                    </td>
                    <td class="dataTableContent text-right"><?= $banner['banners_sort_order'] ?></td>
                    <td class="dataTableContent text-right">
                      <a href="<?= zen_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id']) ?>"><?= zen_icon('line-chart', '', 'lg', false, true) ?></a>
                      <?php if (isset($bInfo) && is_object($bInfo) && ($banner['banners_id'] == $bInfo->banners_id)) {
                        echo zen_icon('caret-right', '', '2x', true);
                      } else { ?>
                        <a href="<?= zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id']) ?>">
                          <?= zen_icon('circle-info', '', '2x', true) ?>
                        </a>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = [];
            $contents = [];
            switch ($action) {
              case 'delete': // deprecated
              case 'del':
                $heading[] = ['text' => '<h4>' . $bInfo->banners_title . '</h4>'];

                $contents = ['form' => zen_draw_form('banners', FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('bID', $bInfo->banners_id)];
                $contents[] = ['text' => TEXT_INFO_DELETE_INTRO];
                $contents[] = ['text' => '<b>' . $bInfo->banners_title . '</b>'];
                if ($bInfo->banners_image) {
                  $contents[] = ['text' => '<div class="checkbox-inline"><label>' . zen_draw_checkbox_field('delete_image', 'on', true) . TEXT_INFO_DELETE_IMAGE . '</label></div>'];
                }
                $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                break;
              default:
                if (is_object($bInfo)) {
                  $heading[] = ['text' => '<h4>' . $bInfo->banners_title . '</h4>'];

                  $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=new') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=del') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>'];
                  $contents[] = ['text' => TEXT_BANNERS_DATE_ADDED . ' ' . zen_date_short($bInfo->date_added)];
                  $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id) . '" class="btn btn-default" role="button">' . IMAGE_UPDATE . '</a>'];

                  $banner_id = $bInfo->banners_id;
                  $days = 3;
                  $stats = zen_get_banner_data_recent($banner_id, $days);
                  $data = [
                    [
                      'label' => TEXT_BANNERS_BANNER_VIEWS,
                      'data' => $stats[0],
                      'bars' => ['order' => 1]
                    ],
                    [
                      'label' => TEXT_BANNERS_BANNER_CLICKS,
                      'data' => $stats[1],
                      'bars' => ['order' => 2]
                    ]
                  ];
                  $settings = [
                    'series' => [
                      'bars' => [
                        'show' => 'true',
                        'barWidth' => 0.4,
                        'align' => 'center'
                      ],
                      'lines, points' => [
                        'show' => 'false'
                      ],
                    ],
                    'xaxis' => [
                      'tickDecimals' => 0,
                      'ticks' => sizeof($stats[0]),
                      'tickLength' => 0
                    ],
                    'yaxis' => [
                      'tickLength' => 0
                    ],
                    'colors' => [
                      'blue',
                      'red'
                    ],
                  ];
                  $opts = json_encode($settings);
                  $contents[] = [
                    'align' => 'center',
                    'text' => '<br>' .
                    '<div id="banner-infobox" style="width:200px;height:220px;"></div>' .
                    '<div class="flot-x-axis">' .
                    '<div class="flot-tick-label">' . TEXT_BANNERS_LAST_3_DAYS . '</div>' .
                    '</div>' .
                    '<script>' .
                    'var data = ' . json_encode($data) . ' ;' .
                    'var options = ' . $opts . ' ;' .
                    'var plot = $("#banner-infobox").plot(data, options).data("plot");' .
                    '</script>'
                  ];

                  if ($bInfo->date_scheduled) {
                    $contents[] = ['text' => sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, zen_date_short($bInfo->date_scheduled))];
                  }

                  if ($bInfo->expires_date) {
                    $contents[] = ['text' => sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, zen_date_short($bInfo->expires_date))];
                  } elseif ($bInfo->expires_impressions) {
                    $contents[] = ['text' => sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions)];
                  }

                  if ($bInfo->date_status_change) {
                    $contents[] = ['text' => sprintf(TEXT_BANNERS_STATUS_CHANGE, zen_date_short($bInfo->date_status_change))];
                  }
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
        <div class="row">
          <table class="table">
            <tr>
              <td><?= $banners_split->display_count($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_BANNERS) ?></td>
              <td class="text-right"><?= $banners_split->display_links($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']) ?></td>
            </tr>
            <tr>
              <td class="text-right" colspan="2"><a href="<?= zen_href_link(FILENAME_BANNER_MANAGER, 'action=new') ?>" class="btn btn-primary" role="button"><?= IMAGE_NEW_BANNER ?></a></td>
            </tr>
          </table>
        </div>
      <?php } ?>

      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
    <!-- script for datepicker -->
    <script>
      $(function () {
        $('input[name="date_scheduled"]').datepicker({
            minDate: 0
        });
        $('input[name="expires_date"]').datepicker({
            minDate: 1
        });
      })
    </script>
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
