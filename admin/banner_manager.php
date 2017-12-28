<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sun Oct 18 03:43:51 2015 -0400 Modified in v1.5.5 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (isset($_GET['flagbanners_on_ssl'])) {
  $_GET['flagbanners_on_ssl'] = (int)$_GET['flagbanners_on_ssl'];
}
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

$banner_extension = zen_banner_image_extension();

if (zen_not_null($action)) {
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

    case 'setbanners_on_ssl':
      if (($_GET['flagbanners_on_ssl'] == '0') || ($_GET['flagbanners_on_ssl'] == '1')) {
        $db->Execute("UPDATE " . TABLE_BANNERS . "
                      SET banners_on_ssl = " . (int)$_GET['flagbanners_on_ssl'] . "
                      WHERE banners_id = " . (int)$_GET['bID']);

        $messageStack->add_session(SUCCESS_BANNER_ON_SSL_UPDATED, 'success');
      } else {
        $messageStack->add_session(ERROR_UNKNOWN_BANNER_ON_SSL, 'error');
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
        $banners_id = zen_db_prepare_input($_POST['banners_id']);
      }
      $banners_title = zen_db_prepare_input($_POST['banners_title']);
      $banners_url = zen_db_prepare_input($_POST['banners_url']);
      $new_banners_group = zen_db_prepare_input($_POST['new_banners_group']);
      $banners_group = (empty($new_banners_group)) ? zen_db_prepare_input($_POST['banners_group']) : $new_banners_group;
      $banners_html_text = zen_db_prepare_input($_POST['banners_html_text']);
      $banners_image_local = zen_db_prepare_input($_POST['banners_image_local']);
      $banners_image_target = zen_db_prepare_input($_POST['banners_image_target']);
      $db_image_location = '';
      $expires_date = zen_db_prepare_input($_POST['expires_date']) == '' ? 'null' : zen_date_raw($_POST['expires_date']);
      $expires_impressions = zen_db_prepare_input($_POST['expires_impressions']);
      $date_scheduled = zen_db_prepare_input($_POST['date_scheduled']) == '' ? 'null' : zen_date_raw($_POST['date_scheduled']);
      $status = zen_db_prepare_input($_POST['status']);
      $banners_open_new_windows = zen_db_prepare_input($_POST['banners_open_new_windows']);
      $banners_on_ssl = zen_db_prepare_input($_POST['banners_on_ssl']);
      $banners_sort_order = zen_db_prepare_input($_POST['banners_sort_order']);

      $banner_error = false;
      if (empty($banners_title)) {
        $messageStack->add(ERROR_BANNER_TITLE_REQUIRED, 'error');
        $banner_error = true;
      }

      if (empty($banners_group)) {
        $messageStack->add(ERROR_BANNER_GROUP_REQUIRED, 'error');
        $banner_error = true;
      }

      if (empty($banners_html_text)) {
        if (empty($banners_image_local)) {
          $banners_image = new upload('banners_image');
          $banners_image->set_extensions(array('jpg', 'jpeg', 'gif', 'png', 'webp', 'flv', 'webm', 'ogg'));
          $banners_image->set_destination(DIR_FS_CATALOG_IMAGES . $banners_image_target);
          if (($banners_image->parse() == false) || ($banners_image->save() == false)) {
            $messageStack->add(ERROR_BANNER_IMAGE_REQUIRED, 'error');
            $banner_error = true;
          }
        }
      }

      if ($banner_error == false) {
        $db_image_location = (zen_not_null($banners_image_local)) ? $banners_image_local : $banners_image_target . $banners_image->filename;
        $sql_data_array = array(
          'banners_title' => $banners_title,
          'banners_url' => $banners_url,
          'banners_image' => $db_image_location,
          'banners_group' => $banners_group,
          'banners_html_text' => $banners_html_text,
          'status' => $status,
          'banners_open_new_windows' => $banners_open_new_windows,
          'banners_on_ssl' => $banners_on_ssl,
          'banners_sort_order' => (int)$banners_sort_order);

        if ($action == 'add') {
          $insert_sql_data = array(
            'date_added' => 'now()',
            'status' => '1');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_BANNERS, $sql_data_array);

          $banners_id = zen_db_insert_id();

          $messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
        } elseif ($action == 'upd') {
          zen_db_perform(TABLE_BANNERS, $sql_data_array, 'update', "banners_id = '" . (int)$banners_id . "'");

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

      if (function_exists('imagecreate') && zen_not_null($banner_extension)) {
        if (is_file(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
          if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
            unlink(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension);
          }
        }

        if (is_file(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
          if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
            unlink(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension);
          }
        }

        if (is_file(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
          if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
            unlink(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension);
          }
        }

        if (is_file(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
          if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
            unlink(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension);
          }
        }
      }

      $messageStack->add_session(SUCCESS_BANNER_REMOVED, 'success');

      zen_redirect(zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']));
      break;
  }
}

// check if the graphs directory exists
$dir_ok = false;
if (function_exists('imagecreate') && zen_not_null($banner_extension)) {
  if (is_dir(DIR_WS_IMAGES . 'graphs')) {
    if (is_writeable(DIR_WS_IMAGES . 'graphs')) {
      $dir_ok = true;
    } else {
      $messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
    }
  } else {
    $messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
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
      function popupImageWindow(url) {
          window.open(url, 'popupImageWindow', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
      }
    </script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }

      // -->
    </script>
    <?php if ($editor_handler != '') include ($editor_handler); ?>
  </head>
  <body onload="init()">
    <div id="spiffycalendar" class="text"></div>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <!-- body_text //-->
      <?php if ($action == '') { ?>
        <div class="row">
          <table class="table-condensed">
            <tr>
              <td class="text-center"><?php echo TEXT_LEGEND; ?></td>
              <td class="text-center"><?php echo TABLE_HEADING_STATUS . '<br>' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF); ?></td>
              <td class="text-center"><?php echo TEXT_LEGEND_BANNER_OPEN_NEW_WINDOWS . '<br>' . zen_image(DIR_WS_IMAGES . 'icon_orange_on.gif', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_ON) . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_orange_off.gif', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_OFF); ?></td>
              <td class="text-center"><?php echo TEXT_LEGEND_BANNER_ON_SSL . '<br>' . zen_image(DIR_WS_IMAGES . 'icon_blue_on.gif', IMAGE_ICON_BANNER_ON_SSL_ON) . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_blue_off.gif', IMAGE_ICON_BANNER_ON_SSL_OFF); ?></td>
            </tr>
          </table>
        </div>
      <?php } // legend ?>
      <?php
      if ($action == 'new') {
        $form_action = 'add';

        $parameters = array(
          'expires_date' => '',
          'date_scheduled' => '',
          'banners_title' => '',
          'banners_url' => '',
          'banners_group' => '',
          'banners_image' => '',
          'banners_html_text' => '',
          'expires_impressions' => '',
          'banners_open_new_windows' => '',
          'banners_on_ssl' => '');

        $bInfo = new objectInfo($parameters);

        if (isset($_GET['bID'])) {
          $form_action = 'upd';

          $bID = zen_db_prepare_input($_GET['bID']);

          $banner = $db->Execute("select banners_title, banners_url, banners_image, banners_group,
                                     banners_html_text, status,
                                     date_format(date_scheduled, '%Y/%m/%d') as date_scheduled,
                                     date_format(expires_date, '%Y/%m/%d') as expires_date,
                                     expires_impressions, date_status_change, banners_open_new_windows, banners_on_ssl, banners_sort_order
                                     from " . TABLE_BANNERS . "
                                     where banners_id = '" . (int)$bID . "'");

          $bInfo->updateObjectInfo($banner->fields);
        } elseif (zen_not_null($_POST)) {
          $bInfo->updateObjectInfo($_POST);
        }

        if (!isset($bInfo->status)) {
          $bInfo->status = '1';
        }
        switch ($bInfo->status) {
          case '0': $is_status = false;
            $not_status = true;
            break;
          case '1': $is_status = true;
            $not_status = false;
            break;
          default: $is_status = true;
            $not_status = false;
            break;
        }
        if (!isset($bInfo->banners_open_new_windows)) {
          $bInfo->banners_open_new_windows = '1';
        }
        switch ($bInfo->banners_open_new_windows) {
          case '0': $is_banners_open_new_windows = false;
            $not_banners_open_new_windows = true;
            break;
          case '1': $is_banners_open_new_windows = true;
            $not_banners_open_new_windows = false;
            break;
          default: $is_banners_open_new_windows = true;
            $not_banners_open_new_windows = false;
            break;
        }
        if (!isset($bInfo->banners_on_ssl)) {
          $bInfo->banners_on_ssl = '1';
        }
        switch ($bInfo->banners_on_ssl) {
          case '0': $is_banners_on_ssl = false;
            $not_banners_on_ssl = true;
            break;
          case '1': $is_banners_on_ssl = true;
            $not_banners_on_ssl = false;
            break;
          default: $is_banners_on_ssl = true;
            $not_banners_on_ssl = false;
            break;
        }

        $groups_array = array();
        $groups = $db->Execute("select distinct banners_group
                            from " . TABLE_BANNERS . "
                            order by banners_group");
        while (!$groups->EOF) {
          $groups_array[] = array('id' => $groups->fields['banners_group'], 'text' => $groups->fields['banners_group']);
          $groups->MoveNext();
        }
        ?>
        <link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
        <script src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
        <script>
      var dateExpires = new ctlSpiffyCalendarBox("dateExpires", "new_banner", "expires_date", "btnDate1", "<?php echo zen_date_short($bInfo->expires_date); ?>", scBTNMODE_CUSTOMBLUE);
      var dateScheduled = new ctlSpiffyCalendarBox("dateScheduled", "new_banner", "date_scheduled", "btnDate2", "<?php echo zen_date_short($bInfo->date_scheduled); ?> ", scBTNMODE_CUSTOMBLUE);
        </script>

        <div class="row">
            <?php
            echo zen_draw_form('new_banner', FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data" class="form-horizontal"');
            if ($form_action == 'upd') {
              echo zen_draw_hidden_field('banners_id', $bID);
            }
            ?>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_STATUS, 'status', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
              <label class="radio-inline"><?php echo zen_draw_radio_field('status', '1', $is_status) . '&nbsp;' . TEXT_BANNERS_ACTIVE; ?></label>
              <label class="radio-inline"><?php echo zen_draw_radio_field('status', '0', $not_status) . '&nbsp;' . TEXT_BANNERS_NOT_ACTIVE; ?></label><br><br>
              <p><?php echo TEXT_INFO_BANNER_STATUS; ?></p>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_OPEN_NEW_WINDOWS, 'banners_open_new_windows', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
              <label class="radio-inline"><?php echo zen_draw_radio_field('banners_open_new_windows', '1', $is_banners_open_new_windows) . '&nbsp;' . TEXT_YES; ?></label>
              <label class="radio-inline"><?php echo zen_draw_radio_field('banners_open_new_windows', '0', $not_banners_open_new_windows) . '&nbsp;' . TEXT_NO; ?></label><br><br>
              <p><?php echo TEXT_INFO_BANNER_OPEN_NEW_WINDOWS; ?></p>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_ON_SSL, 'banners_on_ssl', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
              <label class="radio-inline"><?php echo zen_draw_radio_field('banners_on_ssl', '1', $is_banners_on_ssl) . '&nbsp;' . TEXT_YES; ?></label>
              <label class="radio-inline"><?php echo zen_draw_radio_field('banners_on_ssl', '0', $not_banners_on_ssl) . '&nbsp;' . TEXT_NO; ?></label><br><br>
              <p><?php echo TEXT_INFO_BANNER_ON_SSL; ?></p>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_TITLE, 'banners_title', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
                <?php echo zen_draw_input_field('banners_title', htmlspecialchars($bInfo->banners_title, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_BANNERS, 'banners_title') . ' class="form-control"', true); ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_URL, 'banners_url', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
                <?php echo zen_draw_input_field('banners_url', $bInfo->banners_url, zen_set_field_length(TABLE_BANNERS, 'banners_url') . ' class="form-control"'); ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_GROUP, 'banners_group', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
                <?php echo zen_draw_pull_down_menu('banners_group', $groups_array, $bInfo->banners_group, 'class="form-control"') . '<br><p>' . TEXT_BANNERS_NEW_GROUP . '</p>' . zen_draw_input_field('new_banners_group', '', 'class="form-control"', ((sizeof($groups_array) > 0) ? false : true)); ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_IMAGE, 'banners_image', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
                <?php echo zen_draw_file_field('banners_image') . '<br><p>' . TEXT_BANNERS_IMAGE_LOCAL . '</p><p>' . DIR_FS_CATALOG_IMAGES . '</p>' . zen_draw_input_field('banners_image_local', (isset($bInfo->banners_image) ? $bInfo->banners_image : ''), zen_set_field_length(TABLE_BANNERS, 'banners_image') . ' class="form-control"'); ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_IMAGE_TARGET, 'banners_image_target', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
                <?php echo DIR_FS_CATALOG_IMAGES . zen_draw_input_field('banners_image_target', '', 'class="form-control'); ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo TEXT_BANNER_IMAGE_TARGET_INFO; ?>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_HTML_TEXT, 'banners_html_text', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
                <?php echo '<br><p>' . TEXT_BANNERS_HTML_TEXT_INFO . '</p>' . zen_draw_textarea_field('banners_html_text', 'soft', '80', '10', htmlspecialchars($bInfo->banners_html_text, ENT_COMPAT, CHARSET, TRUE), 'class="editorHook form-control"'); ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_ALL_SORT_ORDER, 'banners_sort_order', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
                <?php echo TEXT_BANNERS_ALL_SORT_ORDER_INFO . '<br>' . zen_draw_input_field('banners_sort_order', $bInfo->banners_sort_order, zen_set_field_length(TABLE_BANNERS, 'banners_sort_order') . ' class="form-control"', false); ?>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_SCHEDULED_AT, 'date_scheduled', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
              <script>dateScheduled.writeControl();dateScheduled.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script>
            </div>
          </div>
          <div class="form-group">
              <?php echo zen_draw_label(TEXT_BANNERS_EXPIRES_ON, 'expires_date', 'class="col-sm-3 control-label"'); ?>
            <div class="col-sm-9">
              <script>dateExpires.writeControl();dateExpires.dateFormat = "<?php echo DATE_FORMAT_SPIFFYCAL; ?>";</script>
              <?php echo TEXT_BANNERS_OR_AT . '<br><br>' . zen_draw_input_field('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7" class="form-control"') . ' ' . zen_draw_label(TEXT_BANNERS_IMPRESSIONS, 'expires_impressions', 'class="control-label"'); ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 text-right">
              <button type="submit" class="btn btn-primary"><?php echo (($form_action == 'add') ? IMAGE_INSERT : IMAGE_UPDATE); ?></button> <a href="<?php echo zen_href_link(FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['bID']) ? 'bID=' . $_GET['bID'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12">
                <?php echo TEXT_BANNERS_BANNER_NOTE . '<br>' . TEXT_BANNERS_INSERT_NOTE . '<br>' . TEXT_BANNERS_EXPIRCY_NOTE . '<br>' . TEXT_BANNERS_SCHEDULE_NOTE; ?>
            </div>
          </div>
          <?php echo '</form>'; ?>
        </div>
        <?php
      } else {
        ?>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_BANNERS; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_GROUPS; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_STATISTICS; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_BANNER_OPEN_NEW_WINDOWS; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_BANNER_ON_SSL; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_BANNER_SORT_ORDER; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
              </thead>
              <tbody>
                  <?php
                  $banners_query_raw = "select banners_id, banners_title, banners_image, banners_group, status,
                                             expires_date, expires_impressions, date_status_change, date_scheduled,
                                             date_added, banners_open_new_windows, banners_on_ssl, banners_sort_order
                                      from " . TABLE_BANNERS . "
                                      order by banners_title, banners_group";
// Split Page
// reset page when page is unknown
                  if (($_GET['page'] == '' or $_GET['page'] == '1') and $_GET['bID'] != '') {
                    $check_page = $db->Execute($banners_query_raw);
                    $check_count = 1;
                    if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
                      foreach ($check_page as $item) {
                        if ($item['banners_id'] == $_GET['bID']) {
                          break;
                        }
                        $check_count++;
                      }
                      $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)), 0);
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

                    if ((!isset($_GET['bID']) || (isset($_GET['bID']) && ($_GET['bID'] == $banner['banners_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
                      $bInfo_array = array_merge($banner, $info->fields);
                      $bInfo = new objectInfo($bInfo_array);
                    }

                    $banners_shown = ($info->fields['banners_shown'] != '') ? $info->fields['banners_shown'] : '0';
                    $banners_clicked = ($info->fields['banners_clicked'] != '') ? $info->fields['banners_clicked'] : '0';

                    if (isset($bInfo) && is_object($bInfo) && ($banner['banners_id'] == $bInfo->banners_id)) {
                      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=new') . '\'" role="button">' . "\n";
                    } else {
                      echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id']) . '\'" role="button">' . "\n";
                    }
                    ?>
                <td class="dataTableContent"><a href="javascript:popupImageWindow('<?php echo FILENAME_POPUP_IMAGE; ?>.php?banner=<?php echo $banner['banners_id']; ?>')"><?php echo zen_image(DIR_WS_IMAGES . 'icon_popup.gif', 'View Banner'); ?></a>&nbsp;<?php echo $banner['banners_title']; ?></td>
                <td class="dataTableContent text-right"><?php echo $banner['banners_group']; ?></td>
                <td class="dataTableContent text-right"><?php echo $banners_shown . ' / ' . $banners_clicked; ?></td>
                <td class="dataTableContent text-center">
                    <?php
                    if ($banner['status'] == '1') {
                      echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setflag&flag=0') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>';
                    } else {
                      echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setflag&flag=1') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>';
                    }
                    ?>
                </td>
                <td class="dataTableContent text-center">
                    <?php
                    if ($banner['banners_open_new_windows'] == '1') {
                      ?>
                    <a href="<?php echo zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setbanners_open_new_windows&flagbanners_open_new_windows=0'); ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_orange_on.gif', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_ON); ?></a>
                    <?php
                  } else {
                    ?>
                    <a href="<?php echo zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setbanners_open_new_windows&flagbanners_open_new_windows=1'); ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_orange_off.gif', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_OFF); ?></a>
                    <?php
                  }
                  ?>
                </td>
                <td class="dataTableContent text-center">
                    <?php
                    if ($banner['banners_on_ssl'] == '1') {
                      ?>
                    <a href="<?php echo zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setbanners_on_ssl&flagbanners_on_ssl=0'); ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_blue_on.gif', IMAGE_ICON_BANNER_ON_SSL_ON); ?></a>
                    <?php
                  } else {
                    ?>
                    <a href="<?php echo zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id'] . '&action=setbanners_on_ssl&flagbanners_on_ssl=1'); ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_blue_off.gif', IMAGE_ICON_BANNER_ON_SSL_OFF); ?></a>
                    <?php
                  }
                  ?>
                </td>
                <td class="dataTableContent text-right"><?php echo $banner['banners_sort_order']; ?></td>

                <td class="dataTableContent text-right">
                  <a href="<?php echo zen_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id']); ?>"><?php echo zen_image(DIR_WS_ICONS . 'statistics.gif', ICON_STATISTICS); ?></a>&nbsp;
                  <?php
                  if (isset($bInfo) && is_object($bInfo) && ($banner['banners_id'] == $bInfo->banners_id)) {
                    echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                  } else {
                    echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banner['banners_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
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
              switch ($action) {
                case 'delete': // deprecated
                case 'del':
                  $heading[] = array('text' => '<h4>' . $bInfo->banners_title . '</h4>');

                  $contents = array('form' => zen_draw_form('banners', FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('bID', $bInfo->banners_id));
                  $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                  $contents[] = array('text' => '<br><b>' . $bInfo->banners_title . '</b>');
                  if ($bInfo->banners_image) {
                    $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE);
                  }
                  $contents[] = array('align' => 'center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                  break;
                default:
                  if (is_object($bInfo)) {
                    $heading[] = array('text' => '<h4>' . $bInfo->banners_title . '</h4>');

                    $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=new') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=del') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                    $contents[] = array('text' => '<br>' . TEXT_BANNERS_DATE_ADDED . ' ' . zen_date_short($bInfo->date_added));
                    $contents[] = array('text-center', 'text' => '<br>' . '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id) . '" class="btn btn-default" role="button">' . IMAGE_UPDATE . '</a>');

                    if ((function_exists('imagecreate')) && ($dir_ok) && ($banner_extension)) {
                      $banner_id = $bInfo->banners_id;
                      $days = '3';
                      include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
                      $contents[] = array('align' => 'text-center', 'text' => '<br>' . zen_image(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner_id . '.' . $banner_extension));
                    } else {
                      include(DIR_WS_FUNCTIONS . 'html_graphs.php');
                      $contents[] = array('align' => 'text-center', 'text' => '<br>' . zen_banner_graph_infoBox($bInfo->banners_id, '3'));
                    }

                    $contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'graph_hbar_blue.gif', 'Blue', '5', '5') . ' ' . TEXT_BANNERS_BANNER_VIEWS . '<br>' . zen_image(DIR_WS_IMAGES . 'graph_hbar_red.gif', 'Red', '5', '5') . ' ' . TEXT_BANNERS_BANNER_CLICKS);

                    if ($bInfo->date_scheduled) {
                      $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, zen_date_short($bInfo->date_scheduled)));
                    }

                    if ($bInfo->expires_date) {
                      $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, zen_date_short($bInfo->expires_date)));
                    } elseif ($bInfo->expires_impressions) {
                      $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions));
                    }

                    if ($bInfo->date_status_change) {
                      $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_STATUS_CHANGE, zen_date_short($bInfo->date_status_change)));
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
        </div>
        <div class="row">
          <table class="table">
            <tr>
              <td><?php echo $banners_split->display_count($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_BANNERS); ?></td>
              <td class="text-right"><?php echo $banners_split->display_links($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
            </tr>
            <tr>
              <td class="text-right" colspan="2"><a href="<?php echo zen_href_link(FILENAME_BANNER_MANAGER, 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_BANNER; ?></a></td>
            </tr>
          </table>
        </div>
        <?php
      }
      ?>

      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
