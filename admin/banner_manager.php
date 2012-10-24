<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: banner_manager.php 19330 2011-08-07 06:32:56Z drbyte $
 */

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (isset($_GET['flagbanners_on_ssl'])) $_GET['flagbanners_on_ssl'] = (int)$_GET['flagbanners_on_ssl'];
  if (isset($_GET['bID'])) $_GET['bID'] = (int)$_GET['bID'];
  if (isset($_GET['flag'])) $_GET['flag'] = (int)$_GET['flag'];
  if (isset($_GET['page'])) $_GET['page'] = (int)$_GET['page'];
  if (isset($_GET['flagbanners_open_new_windows'])) $_GET['flagbanners_open_new_windows'] = (int)$_GET['flagbanners_open_new_windows'];

  $banner_extension = zen_banner_image_extension();

  if (zen_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          zen_set_banner_status($_GET['bID'], $_GET['flag']);

          $messageStack->add_session(SUCCESS_BANNER_STATUS_UPDATED, 'success');
        } else {
          $messageStack->add_session(ERROR_UNKNOWN_STATUS_FLAG, 'error');
        }

        zen_redirect(zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']));
        break;

      case 'setbanners_on_ssl':
        if ( ($_GET['flagbanners_on_ssl'] == '0') || ($_GET['flagbanners_on_ssl'] == '1') ) {
          $db->Execute("update " . TABLE_BANNERS . " set banners_on_ssl='" . $_GET['flagbanners_on_ssl'] . "' where banners_id='" . $_GET['bID'] . "'");

          $messageStack->add_session(SUCCESS_BANNER_ON_SSL_UPDATED, 'success');
        } else {
          $messageStack->add_session(ERROR_UNKNOWN_BANNER_ON_SSL, 'error');
        }

        zen_redirect(zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']));
        break;
      case 'setbanners_open_new_windows':
        if ( ($_GET['flagbanners_open_new_windows'] == '0') || ($_GET['flagbanners_open_new_windows'] == '1') ) {
          $db->Execute("update " . TABLE_BANNERS . " set banners_open_new_windows='" . $_GET['flagbanners_open_new_windows'] . "' where banners_id='" . $_GET['bID'] . "'");

          $messageStack->add_session(SUCCESS_BANNER_OPEN_NEW_WINDOW_UPDATED, 'success');
        } else {
          $messageStack->add_session(ERROR_UNKNOWN_BANNER_OPEN_NEW_WINDOW, 'error');
        }

        zen_redirect(zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']));
        break;
      case 'insert':
      case 'update':
        if (isset($_POST['banners_id'])) $banners_id = zen_db_prepare_input($_POST['banners_id']);
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
            $banners_image->set_destination(DIR_FS_CATALOG_IMAGES . $banners_image_target);
            if ( ($banners_image->parse() == false) || ($banners_image->save() == false) ) {
              $messageStack->add(ERROR_BANNER_IMAGE_REQUIRED, 'error');
              $banner_error = true;
            }
          }
        }

        if ($banner_error == false) {
          $db_image_location = (zen_not_null($banners_image_local)) ? $banners_image_local : $banners_image_target . $banners_image->filename;
          $sql_data_array = array('banners_title' => $banners_title,
                                  'banners_url' => $banners_url,
                                  'banners_image' => $db_image_location,
                                  'banners_group' => $banners_group,
                                  'banners_html_text' => $banners_html_text,
                                  'status' => $status,
                                  'banners_open_new_windows' => $banners_open_new_windows,
                                  'banners_on_ssl' => $banners_on_ssl,
                                  'banners_sort_order' => (int)$banners_sort_order);

          if ($action == 'insert') {
            $insert_sql_data = array('date_added' => 'now()',
                                     'status' => '1');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            zen_db_perform(TABLE_BANNERS, $sql_data_array);

            $banners_id = zen_db_insert_id();

            $messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
          } elseif ($action == 'update') {
            zen_db_perform(TABLE_BANNERS, $sql_data_array, 'update', "banners_id = '" . (int)$banners_id . "'");

            $messageStack->add_session(SUCCESS_BANNER_UPDATED, 'success');
          }

// NOTE: status will be reset by the /functions/banner.php
// build new update sql for date_scheduled, expires_date and expires_impressions

          $sql = "UPDATE " . TABLE_BANNERS . "
                  SET
                    date_scheduled = :scheduledDate,
                    expires_date = DATE_ADD(:expiresDate, INTERVAL '23:59:59' HOUR_SECOND),
                    expires_impressions = " . ($expires_impressions == 0 ? "null" : ":expiresImpressions") . "
                    WHERE banners_id = :bannersID";
          if ($expires_impressions > 0) {
            $sql = $db->bindVars($sql, ':expiresImpressions', $expires_impressions, 'integer');
          }
          if ($date_scheduled != '') {
            $sql = $db->bindVars($sql, ':scheduledDate', $date_scheduled, 'date');
          }
          if ($expires_date != '') {
            $sql = $db->bindVars($sql, ':expiresDate', $expires_date, 'date');
          }
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
          $banner = $db->Execute("select banners_image
                                 from " . TABLE_BANNERS . "
                                 where banners_id = '" . (int)$banners_id . "'");

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

        $db->Execute("delete from " . TABLE_BANNERS . "
                      where banners_id = '" . (int)$banners_id . "'");
        $db->Execute("delete from " . TABLE_BANNERS_HISTORY . "
                      where banners_id = '" . (int)$banners_id . "'");

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
require('includes/admin_html_head.php');
?>
<script language="javascript"><!--
function popupImageWindow(url) {
  window.open(url,'popupImageWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
<body>
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
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
<?php if ($action=='') { ?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td class="smallText" align="center" width="100"><?php echo TEXT_LEGEND; ?></td>
            <td class="smallText" align="center" width="100"><?php echo TEXT_LEGEND_STATUS_OFF . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON); ?></td>
            <td class="smallText" align="center" width="100"><?php echo TEXT_LEGEND_BANNER_ON_SSL . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_blue_on.gif', IMAGE_ICON_BANNER_ON_SSL_ON) . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_blue_off.gif', IMAGE_ICON_BANNER_ON_SSL_OFF); ?></td>
            <td class="smallText" align="center" width="100"><?php echo TEXT_LEGEND_BANNER_OPEN_NEW_WINDOWS . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_orange_on.gif', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_ON) . '&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_orange_off.gif', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_OFF); ?></td>
          </tr>
        </table></td>
      </tr>
<?php } // legend ?>
<?php
  if ($action == 'new') {
    $form_action = 'insert';

    $parameters = array('expires_date' => '',
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
      $form_action = 'update';

      $bID = zen_db_prepare_input($_GET['bID']);

      $banner = $db->Execute("select banners_title, banners_url, banners_image, banners_group,
                                     banners_html_text, status,
                                     date_format(date_scheduled, '%Y/%m/%d') as date_scheduled,
                                     date_format(expires_date, '%Y/%m/%d') as expires_date,
                                     expires_impressions, date_status_change, banners_open_new_windows, banners_on_ssl, banners_sort_order
                                     from " . TABLE_BANNERS . "
                                     where banners_id = '" . (int)$bID . "'");

      $bInfo->objectInfo($banner->fields);
    } elseif (zen_not_null($_POST)) {
      $bInfo->objectInfo($_POST);
    }

    if (!isset($bInfo->status)) $bInfo->status = '1';
    switch ($bInfo->status) {
      case '0': $is_status = false; $not_status = true; break;
      case '1': $is_status = true; $not_status = false; break;
      default: $is_status = true; $not_status = false; break;
    }
    if (!isset($bInfo->banners_open_new_windows)) $bInfo->banners_open_new_windows = '1';
    switch ($bInfo->banners_open_new_windows) {
      case '0': $is_banners_open_new_windows = false; $not_banners_open_new_windows = true; break;
      case '1': $is_banners_open_new_windows = true; $not_banners_open_new_windows = false; break;
      default: $is_banners_open_new_windows = true; $not_banners_open_new_windows = false; break;
    }
    if (!isset($bInfo->banners_on_ssl)) $bInfo->banners_on_ssl = '1';
    switch ($bInfo->banners_on_ssl) {
      case '0': $is_banners_on_ssl = false; $not_banners_on_ssl = true; break;
      case '1': $is_banners_on_ssl = true; $not_banners_on_ssl = false; break;
      default: $is_banners_on_ssl = true; $not_banners_on_ssl = false; break;
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
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr><?php echo zen_draw_form('new_banner', FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"'); if ($form_action == 'update') echo zen_draw_hidden_field('banners_id', $bID); ?>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_STATUS; ?></td>
            <td class="main"><?php echo zen_draw_radio_field('status', '1', $is_status) . '&nbsp;' . TEXT_BANNERS_ACTIVE . '&nbsp;' . zen_draw_radio_field('status', '0', $not_status) . '&nbsp;' . TEXT_BANNERS_NOT_ACTIVE . '<br />' . TEXT_INFO_BANNER_STATUS; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_OPEN_NEW_WINDOWS; ?></td>
            <td class="main"><?php echo zen_draw_radio_field('banners_open_new_windows', '1', $is_banners_open_new_windows) . '&nbsp;' . TEXT_YES . '&nbsp;' . zen_draw_radio_field('banners_open_new_windows', '0', $not_banners_open_new_windows) . '&nbsp;' . TEXT_NO . '<br />' . TEXT_INFO_BANNER_OPEN_NEW_WINDOWS; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_ON_SSL; ?></td>
            <td class="main"><?php echo zen_draw_radio_field('banners_on_ssl', '1', $is_banners_on_ssl) . '&nbsp;' . TEXT_YES . '&nbsp;' . zen_draw_radio_field('banners_on_ssl', '0', $not_banners_on_ssl) . '&nbsp;' . TEXT_NO . '<br />' . TEXT_INFO_BANNER_ON_SSL; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_TITLE; ?></td>
            <td class="main"><?php echo zen_draw_input_field('banners_title', htmlspecialchars($bInfo->banners_title, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_BANNERS, 'banners_title'), true); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_URL; ?></td>
            <td class="main"><?php echo zen_draw_input_field('banners_url', $bInfo->banners_url, zen_set_field_length(TABLE_BANNERS, 'banners_url')); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_BANNERS_GROUP; ?></td>
            <td class="main"><?php echo zen_draw_pull_down_menu('banners_group', $groups_array, $bInfo->banners_group) . TEXT_BANNERS_NEW_GROUP . '<br>' . zen_draw_input_field('new_banners_group', '', '', ((sizeof($groups_array) > 0) ? false : true)); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_BANNERS_IMAGE; ?></td>
            <td class="main"><?php echo zen_draw_file_field('banners_image') . ' ' . TEXT_BANNERS_IMAGE_LOCAL . '<br>' . DIR_FS_CATALOG_IMAGES . zen_draw_input_field('banners_image_local', (isset($bInfo->banners_image) ? $bInfo->banners_image : ''), zen_set_field_length(TABLE_BANNERS, 'banners_image')); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_IMAGE_TARGET; ?></td>
            <td class="main"><?php echo DIR_FS_CATALOG_IMAGES . zen_draw_input_field('banners_image_target'); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><?php echo TEXT_BANNER_IMAGE_TARGET_INFO; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td valign="top" class="main"><?php echo TEXT_BANNERS_HTML_TEXT; ?></td>
            <td class="main"><?php echo TEXT_BANNERS_HTML_TEXT_INFO . '<br />' . zen_draw_textarea_field('banners_html_text', 'soft', '60', '5', htmlspecialchars($bInfo->banners_html_text, ENT_COMPAT, CHARSET, TRUE)); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_ALL_SORT_ORDER; ?></td>
            <td class="main"><?php echo TEXT_BANNERS_ALL_SORT_ORDER_INFO . '<br />' . zen_draw_input_field('banners_sort_order', $bInfo->banners_sort_order, zen_set_field_length(TABLE_BANNERS, 'banners_sort_order'), false); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_SCHEDULED_AT; ?></td>
            <td valign="top" class="main"><?php echo zen_draw_input_field('date_scheduled', zen_date_short($bInfo->date_scheduled), 'class="datepicker"');  ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td valign="top" class="main"><?php echo TEXT_BANNERS_EXPIRES_ON; ?></td>
            <td class="main"><?php echo zen_draw_input_field('expires_date', zen_date_short($bInfo->expires_date), 'class="datepicker"');  ?><?php echo TEXT_BANNERS_OR_AT . '<br>' . zen_draw_input_field('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_BANNER_NOTE . '<br>' . TEXT_BANNERS_INSERT_NOTE . '<br>' . TEXT_BANNERS_EXPIRCY_NOTE . '<br>' . TEXT_BANNERS_SCHEDULE_NOTE; ?></td>
            <td class="main" align="right" valign="top" nowrap><?php echo (($form_action == 'insert') ? zen_image_submit('button_insert.gif', IMAGE_INSERT) : zen_image_submit('button_update.gif', IMAGE_UPDATE)). '&nbsp;&nbsp;<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['bID']) ? 'bID=' . $_GET['bID'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_BANNERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_GROUPS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATISTICS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_BANNER_OPEN_NEW_WINDOWS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_BANNER_ON_SSL; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_BANNER_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
// Split Page
// reset page when page is unknown
if (($_GET['page'] == '' or $_GET['page'] == '1') and $_GET['bID'] != '') {
  $banners_query_raw = "select banners_id, banners_title, banners_image, banners_group, status, expires_date, expires_impressions, date_status_change, date_scheduled, date_added, banners_open_new_windows, banners_on_ssl, banners_sort_order from " . TABLE_BANNERS . " order by banners_title, banners_group";
  $check_page = $db->Execute($banners_query_raw);
  $check_count=1;
  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
    while (!$check_page->EOF) {
      if ($check_page->fields['banners_id'] == $_GET['bID']) {
        break;
      }
      $check_count++;
      $check_page->MoveNext();
    }
    $_GET['page'] = round((($check_count/MAX_DISPLAY_SEARCH_RESULTS)+(fmod_round($check_count,MAX_DISPLAY_SEARCH_RESULTS) !=0 ? .5 : 0)),0);
  } else {
    $_GET['page'] = 1;
  }
}

    $banners_query_raw = "select banners_id, banners_title, banners_image, banners_group, status, expires_date, expires_impressions, date_status_change, date_scheduled, date_added, banners_open_new_windows, banners_on_ssl, banners_sort_order from " . TABLE_BANNERS . " order by banners_title, banners_group";
    $banners_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $banners_query_raw, $banners_query_numrows);
    $banners = $db->Execute($banners_query_raw);
    while (!$banners->EOF) {
      $info = $db->Execute("select sum(banners_shown) as banners_shown,
                                   sum(banners_clicked) as banners_clicked
                            from " . TABLE_BANNERS_HISTORY . "
                            where banners_id = '" . (int)$banners->fields['banners_id'] . "'");

      if ((!isset($_GET['bID']) || (isset($_GET['bID']) && ($_GET['bID'] == $banners->fields['banners_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
        $bInfo_array = array_merge($banners->fields, $info->fields);
        $bInfo = new objectInfo($bInfo_array);
      }

      $banners_shown = ($info->fields['banners_shown'] != '') ? $info->fields['banners_shown'] : '0';
      $banners_clicked = ($info->fields['banners_clicked'] != '') ? $info->fields['banners_clicked'] : '0';

      if (isset($bInfo) && is_object($bInfo) && ($banners->fields['banners_id'] == $bInfo->banners_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=new') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners->fields['banners_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="javascript:popupImageWindow(\'' . FILENAME_POPUP_IMAGE . '.php' . '?banner=' . $banners->fields['banners_id']  . '\')">' . zen_image(DIR_WS_IMAGES . 'icon_popup.gif', 'View Banner') . '</a>&nbsp;' . $banners->fields['banners_title']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $banners->fields['banners_group']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $banners_shown . ' / ' . $banners_clicked; ?></td>
                <td class="dataTableContent" align="center">
<?php
      if ($banners->fields['status'] == '1') {
        echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners->fields['banners_id'] . '&action=setflag&flag=0') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>';
      } else {
        echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners->fields['banners_id'] . '&action=setflag&flag=1') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>';
      }
?>
                </td>
                <td class="dataTableContent" align="center">
<?php
      if ($banners->fields['banners_open_new_windows'] == '1') {
        echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners->fields['banners_id'] . '&action=setbanners_open_new_windows&flagbanners_open_new_windows=0') . '">' . zen_image(DIR_WS_IMAGES . 'icon_orange_on.gif', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_ON) . '</a>';
      } else {
        echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners->fields['banners_id'] . '&action=setbanners_open_new_windows&flagbanners_open_new_windows=1') . '">' . zen_image(DIR_WS_IMAGES . 'icon_orange_off.gif', IMAGE_ICON_BANNER_OPEN_NEW_WINDOWS_OFF) . '</a>';
      }
?>
                </td>
                <td class="dataTableContent" align="center">
<?php
      if ($banners->fields['banners_on_ssl'] == '1') {
        echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners->fields['banners_id'] . '&action=setbanners_on_ssl&flagbanners_on_ssl=0') . '">' . zen_image(DIR_WS_IMAGES . 'icon_blue_on.gif', IMAGE_ICON_BANNER_ON_SSL_ON) . '</a>';
      } else {
        echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners->fields['banners_id'] . '&action=setbanners_on_ssl&flagbanners_on_ssl=1') . '">' . zen_image(DIR_WS_IMAGES . 'icon_blue_off.gif', IMAGE_ICON_BANNER_ON_SSL_OFF) . '</a>';
      }
?>
                </td>
                <td class="dataTableContent" align="right"><?php echo $banners->fields['banners_sort_order']; ?></td>

                <td class="dataTableContent" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $banners->fields['banners_id']) . '">' . zen_image(DIR_WS_ICONS . 'statistics.gif', ICON_STATISTICS) . '</a>&nbsp;'; if (isset($bInfo) && is_object($bInfo) && ($banners->fields['banners_id'] == $bInfo->banners_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners->fields['banners_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $banners->MoveNext();
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $banners_split->display_count($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_BANNERS); ?></td>
                    <td class="smallText" align="right"><?php echo $banners_split->display_links($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'action=new') . '">' . zen_image_button('button_new_banner.gif', IMAGE_NEW_BANNER) . '</a>'; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<b>' . $bInfo->banners_title . '</b>');

      $contents = array('form' => zen_draw_form('banners', FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('bID', $bInfo->banners_id));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $bInfo->banners_title . '</b>');
      if ($bInfo->banners_image) $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($bInfo)) {
        $heading[] = array('text' => '<b>' . $bInfo->banners_title . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=new') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_BANNERS_DATE_ADDED . ' ' . zen_date_short($bInfo->date_added));
        $contents[] = array('center', 'text' => '<br />' . '<a href="' . zen_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id) . '">' . zen_image_button('button_update.gif', IMAGE_UPDATE) . '</a>' );

        if ( (function_exists('imagecreate')) && ($dir_ok) && ($banner_extension) ) {
          $banner_id = $bInfo->banners_id;
          $days = '3';
          include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
          $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner_id . '.' . $banner_extension));
        } else {
          include(DIR_WS_FUNCTIONS . 'html_graphs.php');
          $contents[] = array('align' => 'center', 'text' => '<br>' . zen_banner_graph_infoBox($bInfo->banners_id, '3'));
        }

        $contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'graph_hbar_blue.gif', 'Blue', '5', '5') . ' ' . TEXT_BANNERS_BANNER_VIEWS . '<br>' . zen_image(DIR_WS_IMAGES . 'graph_hbar_red.gif', 'Red', '5', '5') . ' ' . TEXT_BANNERS_BANNER_CLICKS);

        if ($bInfo->date_scheduled) $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, zen_date_short($bInfo->date_scheduled)));

        if ($bInfo->expires_date) {
          $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, zen_date_short($bInfo->expires_date)));
        } elseif ($bInfo->expires_impressions) {
          $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions));
        }

        if ($bInfo->date_status_change) $contents[] = array('text' => '<br>' . sprintf(TEXT_BANNERS_STATUS_CHANGE, zen_date_short($bInfo->date_status_change)));
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
<?php
  }
?>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
    <script>
    $(function() {
        $( ".datepicker" ).datepicker({
         dateFormat: '<?php echo DATE_FORMAT_DATEPICKER_ADMIN; ?>',
         changeMonth: true,
         changeYear: true
        });
    });
    </script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>