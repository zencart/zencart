<?php

/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sat Oct 17 20:53:59 2015 -0400 Modified in v1.5.5 $
 */
// Sets the status of a page
function zen_set_ezpage_status($pages_id, $status, $status_field) {
  global $db;
  if ($status == '1') {
    zen_record_admin_activity('EZ-Page ID ' . (int)$pages_id . ' [' . $status_field . '] changed to 0', 'info');
    return $db->Execute("UPDATE " . TABLE_EZPAGES . "
                         SET " . zen_db_input($status_field) . " = 0
                         WHERE pages_id = " . (int)$pages_id);
  } elseif ($status == '0') {
    zen_record_admin_activity('EZ-Page ID ' . (int)$pages_id . ' [' . $status_field . '] changed to 1', 'info');
    return $db->Execute("UPDATE " . TABLE_EZPAGES . "
                         SET " . zen_db_input($status_field) . " = 1
                         WHERE pages_id = " . (int)$pages_id);
  } else {
    return -1;
  }
}

require('includes/application_top.php');

if (!isset($_SESSION['ez_sort_order'])) {
  $_SESSION['ez_sort_order'] = 0;
}
if (!isset($_GET['reset_ez_sort_order'])) {
  $reset_ez_sort_order = $_SESSION['ez_sort_order'];
}

if ($_GET['action'] == 'set_editor') {
  // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
  $action = '';
  zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN));
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (zen_not_null($action)) {
  switch ($action) {
    case 'set_ez_sort_order':
      $_SESSION['ez_sort_order'] = $_GET['reset_ez_sort_order'];
      $action = '';
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . ($_GET['ezID'] != '' ? '&ezID=' . $_GET['ezID'] : '')));
      break;
    case 'page_open_new_window':
      zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'page_open_new_window');
      $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
      break;
    case 'page_is_ssl':
      zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'page_is_ssl');
      $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
      break;
    case 'status_header':
      zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'status_header');
      $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
      break;
    case 'status_sidebox':
      zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'status_sidebox');
      $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
      break;
    case 'status_footer':
      zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'status_footer');
      $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
      break;
    case 'status_toc':
      zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'status_toc');
      $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
      break;
    case 'insert':
    case 'update':
      if (isset($_POST['pages_id'])) {
        $pages_id = zen_db_prepare_input($_POST['pages_id']);
      }
      $pages_title = zen_db_prepare_input($_POST['pages_title']);
      $page_open_new_window = (int)$_POST['page_open_new_window'];
      $page_is_ssl = (int)$_POST['page_is_ssl'];

      $pages_html_text = zen_db_prepare_input($_POST['pages_html_text']);
      $alt_url = zen_db_prepare_input($_POST['alt_url']);

      $alt_url_external = zen_db_prepare_input($_POST['alt_url_external']);

      $pages_header_sort_order = (int)$_POST['header_sort_order'];
      $pages_sidebox_sort_order = (int)$_POST['sidebox_sort_order'];
      $pages_footer_sort_order = (int)$_POST['footer_sort_order'];
      $pages_toc_sort_order = (int)$_POST['toc_sort_order'];

      $toc_chapter = (int)$_POST['toc_chapter'];

      $status_header = ($pages_header_sort_order == 0 ? 0 : (int)$_POST['status_header']);
      $status_sidebox = ($pages_sidebox_sort_order == 0 ? 0 : (int)$_POST['status_sidebox']);
      $status_footer = ($pages_footer_sort_order == 0 ? 0 : (int)$_POST['status_footer']);
      $status_toc = ($pages_toc_sort_order == 0 ? 0 : (int)$_POST['status_toc']);

      $page_error = false;
      if (empty($pages_title)) {
        $messageStack->add(ERROR_PAGE_TITLE_REQUIRED, 'error');
        $page_error = true;
      }
      if (empty($pages_html_text)) {
        
      }

      $zv_link_method_cnt = 0;
      if ($alt_url != '') {
        $zv_link_method_cnt++;
      }
      if ($alt_url_external != '') {
        $zv_link_method_cnt++;
      }
      if ($pages_html_text != '' and strlen(trim($pages_html_text)) > 6) {
        $zv_link_method_cnt++;
      }
      if ($zv_link_method_cnt > 1) {
        $messageStack->add(ERROR_MULTIPLE_HTML_URL, 'error');
        $page_error = true;
      }

      if ($page_error == false) {
        $sql_data_array = array(
          'pages_title' => $pages_title,
          'page_open_new_window' => $page_open_new_window,
          'page_is_ssl' => $page_is_ssl,
          'alt_url' => $alt_url,
          'alt_url_external' => $alt_url_external,
          'status_header' => $status_header,
          'status_sidebox' => $status_sidebox,
          'status_footer' => $status_footer,
          'status_toc' => $status_toc,
          'header_sort_order' => $pages_header_sort_order,
          'sidebox_sort_order' => $pages_sidebox_sort_order,
          'footer_sort_order' => $pages_footer_sort_order,
          'toc_sort_order' => $pages_toc_sort_order,
          'toc_chapter' => $toc_chapter,
          'pages_html_text' => $pages_html_text);

        if ($action == 'insert') {
          zen_db_perform(TABLE_EZPAGES, $sql_data_array);
          $pages_id = $db->insert_ID();
          $messageStack->add(SUCCESS_PAGE_INSERTED, 'success');
          zen_record_admin_activity('EZ-Page with ID ' . (int)$pages_id . ' added.', 'info');
        } elseif ($action == 'update') {
          zen_db_perform(TABLE_EZPAGES, $sql_data_array, 'update', "pages_id = '" . (int)$pages_id . "'");
          $messageStack->add(SUCCESS_PAGE_UPDATED, 'success');
          zen_record_admin_activity('EZ-Page with ID ' . (int)$pages_id . ' updated.', 'info');
        }

        zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'ezID=' . $pages_id));
      } else {
        if ($page_error == false) {
          $action = 'new';
        } else {
          $_GET['pages_id'] = $pages_id;
          $_GET['ezID'] = $pages_id;
          $_GET['action'] = 'new';
          $action = 'new';
          $ezID = $pages_id;
        }
      }
      break;
    case 'deleteconfirm':
      $pages_id = zen_db_prepare_input($_POST['ezID']);
      $db->Execute("delete from " . TABLE_EZPAGES . " where pages_id = '" . (int)$pages_id . "'");
      $messageStack->add(SUCCESS_PAGE_REMOVED, 'success');
      zen_record_admin_activity('EZ-Page with ID ' . (int)$pages_id . ' deleted.', 'notice');
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page']));
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
    <?php
    if ($editor_handler != '') {
      include ($editor_handler);
    }
    ?>
  </head>
  <body onLoad="init()">
      <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE . ' ' . ($_GET['ezID'] != '' ? TEXT_INFO_PAGES_ID . $_GET['ezID'] : TEXT_INFO_PAGES_ID_SELECT); ?></h1>
      <div class="row">
        <!-- body_text //-->
        <?php
        if ($action != 'new') {
// toggle switch for display sort order
          $ez_sort_order_array = array(
            array('id' => '0', 'text' => TEXT_SORT_CHAPTER_TOC_TITLE),
            array('id' => '1', 'text' => TEXT_SORT_HEADER_TITLE),
            array('id' => '2', 'text' => TEXT_SORT_SIDEBOX_TITLE),
            array('id' => '3', 'text' => TEXT_SORT_FOOTER_TITLE),
            array('id' => '4', 'text' => TEXT_SORT_PAGE_TITLE),
            array('id' => '5', 'text' => TEXT_SORT_PAGE_ID_TITLE)
          );
          ?>
          <div class="col-sm-6 text-right">
              <?php echo zen_draw_form('set_ez_sort_order_form', FILENAME_EZPAGES_ADMIN, '', 'get'); ?>
              <?php echo TEXT_SORT_CHAPTER_TOC_TITLE_INFO . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_ez_sort_order', $ez_sort_order_array, $reset_ez_sort_order, 'onChange="this.form.submit();"'); ?>
              <?php echo zen_hide_session_id(); ?>
              <?php echo ($_GET['page'] != '' ? zen_draw_hidden_field('page', $_GET['page']) : ''); ?>
              <?php echo zen_draw_hidden_field('action', 'set_ez_sort_order'); ?>
              <?php echo '</form>'; ?>
          </div>
          <div class="col-sm-6 text-right">
              <?php
// toggle switch for editor
              echo TEXT_EDITOR_INFO . zen_draw_form('set_editor_form', FILENAME_EZPAGES_ADMIN, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();"') .
              zen_hide_session_id() .
              zen_draw_hidden_field('action', 'set_editor') .
              '</form>';
            }
            ?>
        </div>
      </div>
      <?php
      if ($action == 'new') {
        $form_action = 'insert';

        $parameters = array(
          'pages_title' => '',
          'page_open_new_window' => '',
          'page_is_ssl' => '',
          'pages_html_text' => '',
          'alt_url' => '',
          'alt_url_external' => '',
          'header_sort_order' => '',
          'sidebox_sort_order' => '',
          'footer_sort_order' => '',
          'toc_sort_order' => '',
          'toc_chapter' => '',
          'status_header' => '',
          'status_sidebox' => '',
          'status_footer' => '',
          'status_toc' => '',
          'page_open_new_window' => '',
          'page_is_ssl' => ''
        );

        $ezInfo = new objectInfo($parameters);

        if (isset($_GET['ezID'])) {
          $form_action = 'update';

          $ezID = zen_db_prepare_input($_GET['ezID']);

          $page_query = "SELECT *
                         FROM " . TABLE_EZPAGES . "
                         WHERE pages_id = " . (int)$_GET['ezID'];
          $page = $db->Execute($page_query);
          $ezInfo->updateObjectInfo($page->fields);
        } elseif (zen_not_null($_POST)) {
          $ezInfo->updateObjectInfo($_POST);
        }

// set all status settings and switches
        if (!isset($ezInfo->status_header)) {
          $ezInfo->status_header = '1';
        }
        switch ($ezInfo->status_header) {
          case '0': $is_status_header = false;
            $not_status_header = true;
            break;
          case '1': $is_status_header = true;
            $not_status_header = false;
            break;
          default: $is_status_header = true;
            $not_status_header = false;
            break;
        }
        if (!isset($ezInfo->status_sidebox)) {
          $ezInfo->status_sidebox = '1';
        }
        switch ($ezInfo->status_sidebox) {
          case '0': $is_status_sidebox = false;
            $not_status_sidebox = true;
            break;
          case '1': $is_status_sidebox = true;
            $not_status_sidebox = false;
            break;
          default: $is_status_sidebox = true;
            $not_status_sidebox = false;
            break;
        }
        if (!isset($ezInfo->status_footer)) {
          $ezInfo->status_footer = '1';
        }
        switch ($ezInfo->status_footer) {
          case '0': $is_status_footer = false;
            $not_status_footer = true;
            break;
          case '1': $is_status_footer = true;
            $not_status_footer = false;
            break;
          default: $is_status_footer = true;
            $not_status_footer = false;
            break;
        }
        if (!isset($ezInfo->status_toc)) {
          $ezInfo->status_toc = '1';
        }
        switch ($ezInfo->status_toc) {
          case '0': $is_status_toc = false;
            $not_status_toc = true;
            break;
          case '1': $is_status_toc = true;
            $not_status_toc = false;
            break;
          default: $is_status_toc = true;
            $not_status_toc = false;
            break;
        }
        if (!isset($ezInfo->page_open_new_window)) {
          $ezInfo->not_page_open_new_window = '1';
        }
        switch ($ezInfo->page_open_new_window) {
          case '0': $is_page_open_new_window = false;
            $not_page_open_new_window = true;
            break;
          case '1': $is_page_open_new_window = true;
            $not_page_open_new_window = false;
            break;
          default: $is_page_open_new_window = false;
            $not_page_open_new_window = true;
            break;
        }
        if (!isset($ezInfo->page_is_ssl)) {
          $ezInfo->page_is_ssl = '1';
        }
        switch ($ezInfo->page_is_ssl) {
          case '0': $is_page_is_ssl = false;
            $not_page_is_ssl = true;
            break;
          case '1': $is_page_is_ssl = true;
            $not_page_is_ssl = false;
            break;
          default: $is_page_is_ssl = false;
            $not_page_is_ssl = true;
            break;
        }
        ?>
        <?php
        echo zen_draw_form('new_page', FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . zen_db_prepare_input($_GET['page']) . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data" class="form-horizontal"');
        if ($form_action == 'update') {
          echo zen_draw_hidden_field('pages_id', $ezID);
        }
        ?>

        <div class="form-group">
          <div class="col-sm-12"><?php echo (($form_action == 'insert') ? '<button type="sumit" class="btn btn-primary">' . IMAGE_INSERT . '</button>' : '<button type="sumit" class="btn btn-primary">' . IMAGE_UPDATE . '</button>') . ' <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['ezID']) ? 'ezID=' . $_GET['ezID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?></div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_PAGES_TITLE, 'pages_title', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9"><?php echo zen_draw_input_field('pages_title', htmlspecialchars($ezInfo->pages_title, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_EZPAGES, 'pages_title') . ' class="form-control"', true); ?></div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TABLE_HEADING_PAGE_OPEN_NEW_WINDOW, 'page_open_new_window', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9">
            <label class="radio-inline"><?php echo zen_draw_radio_field('page_open_new_window', '1', $is_page_open_new_window) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('page_open_new_window', '0', $not_page_open_new_window) . TEXT_NO; ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TABLE_HEADING_PAGE_IS_SSL, 'page_is_ssl', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9">
            <label class="radio-inline"><?php echo zen_draw_radio_field('page_is_ssl', '1', $is_page_is_ssl) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('page_is_ssl', '0', $not_page_is_ssl) . TEXT_NO; ?></label>
          </div>
        </div>
        <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>
        <div class="form-group">
          <div class="col-sm-3">
              <?php echo zen_draw_label(TABLE_HEADING_STATUS_HEADER, 'status_header', 'class="control-label"'); ?>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_header', '1', $is_status_header) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_header', '0', $not_status_header) . TEXT_NO; ?></label>
            <br>
            <?php echo zen_draw_label(TEXT_HEADER_SORT_ORDER, 'header_sort_order', 'class="control-label"'); ?>
            <?php echo zen_draw_input_field('header_sort_order', $ezInfo->header_sort_order, zen_set_field_length(TABLE_EZPAGES, 'header_sort_order') . ' class="form-control"', false); ?>
            <br>
          </div>
          <div class="col-sm-3">
              <?php echo zen_draw_label(TABLE_HEADING_STATUS_SIDEBOX, 'status_sidebox', 'class="control-label"'); ?>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_sidebox', '1', $is_status_sidebox) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_sidebox', '0', $not_status_sidebox) . TEXT_NO; ?></label>
            <br>
            <?php echo zen_draw_label(TEXT_SIDEBOX_SORT_ORDER, 'sidebox_sort_order', 'class="control-label"'); ?>
            <?php echo zen_draw_input_field('sidebox_sort_order', $ezInfo->sidebox_sort_order, zen_set_field_length(TABLE_EZPAGES, 'sidebox_sort_order') . ' class="form-control"', false); ?>
            <br>
          </div>
          <div class="col-sm-3">
              <?php echo zen_draw_label(TABLE_HEADING_STATUS_FOOTER, 'status_footer', 'class="control-label"'); ?>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_footer', '1', $is_status_footer) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_footer', '0', $not_status_footer) . TEXT_NO; ?></label>
            <br>
            <?php echo zen_draw_label(TEXT_FOOTER_SORT_ORDER, 'status_footer', 'class="control-label"'); ?>
            <?php echo zen_draw_input_field('footer_sort_order', $ezInfo->footer_sort_order, zen_set_field_length(TABLE_EZPAGES, 'footer_sort_order') . ' class="form-control"', false); ?>
            <br>
          </div>
          <div class="col-sm-3">
              <?php echo zen_draw_label(TABLE_HEADING_CHAPTER_PREV_NEXT, 'toc_chapter', 'class="control-label"'); ?>
              <?php echo zen_draw_input_field('toc_chapter', $ezInfo->toc_chapter, zen_set_field_length(TABLE_EZPAGES, 'toc_chapter', '6') . ' class="form-control"', false); ?>
            <br>
            <?php echo zen_draw_label(TABLE_HEADING_STATUS_TOC, 'status_toc', 'class="control-label"'); ?>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_toc', '1', $is_status_toc) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_toc', '0', $not_status_toc) . TEXT_NO; ?></label>
            <br>
            <?php echo zen_draw_label(TEXT_TOC_SORT_ORDER, 'toc_sort_order', 'class="control-label"'); ?>
            <?php echo zen_draw_input_field('toc_sort_order', $ezInfo->toc_sort_order, zen_set_field_length(TABLE_EZPAGES, 'toc_sort_order') . ' class="form-control"', false); ?>
            <br>
          </div>
          <ul>
            <li><?php echo TEXT_HEADER_SORT_ORDER_EXPLAIN; ?></li>
            <li><?php echo TEXT_SIDEBOX_ORDER_EXPLAIN; ?></li>
            <li><?php echo TEXT_FOOTER_ORDER_EXPLAIN; ?></li>
            <li><?php echo TEXT_TOC_SORT_ORDER_EXPLAIN; ?></li>
            <li><?php echo TEXT_CHAPTER_EXPLAIN; ?></li>
          </ul>
        </div>
        <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_PAGES_HTML_TEXT, 'pages_html_text', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9"><?php echo zen_draw_textarea_field('pages_html_text', 'soft', '100%', '25', htmlspecialchars($ezInfo->pages_html_text, ENT_COMPAT, CHARSET, TRUE), 'class="editorHook form-control"'); ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_ALT_URL, 'alt_url', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9"><?php echo zen_draw_input_field('alt_url', $ezInfo->alt_url, 'size="100" class="form-control"'); ?><br><?php echo TEXT_ALT_URL_EXPLAIN; ?></div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_ALT_URL_EXTERNAL, 'alt_url_external', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9"><?php echo zen_draw_input_field('alt_url_external', $ezInfo->alt_url_external, 'size="100" class="form-control"'); ?><br><?php echo TEXT_ALT_URL_EXTERNAL_EXPLAIN; ?></div>
        </div>
        <div class="form-group">
          <div class="col-sm-12"><?php echo (($form_action == 'insert') ? '<button type="sumit" class="btn btn-primary">' . IMAGE_INSERT . '</button>' : '<button type="sumit" class="btn btn-primary">' . IMAGE_UPDATE . '</button>') . ' <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['ezID']) ? 'ezID=' . $_GET['ezID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?></div>
        </div>
        <?php
      } else {
        ?>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ID; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAGES; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_PAGE_OPEN_NEW_WINDOW; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_PAGE_IS_SSL; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_STATUS_HEADER; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_STATUS_SIDEBOX; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_STATUS_FOOTER; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_CHAPTER; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_STATUS_TOC; ?></th>
                  <th class="dataTableHeadingContent text-center">&nbsp;</th>
                </tr>
              </thead>
              <tbody>

                <?php
// set display order
                switch (true) {
                  case ($_SESSION['ez_sort_order'] == 0):
                    $ez_order_by = " order by toc_chapter, toc_sort_order, pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 1):
                    $ez_order_by = " order by header_sort_order, pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 2):
                    $ez_order_by = " order by sidebox_sort_order, pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 3):
                    $ez_order_by = " order by footer_sort_order, pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 4):
                    $ez_order_by = " order by pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 5):
                    $ez_order_by = " order by  pages_id, pages_title";
                    break;
                  default:
                    $ez_order_by = " order by toc_chapter, toc_sort_order, pages_title";
                    break;
                }

                $pages_query_raw = "select * from " . TABLE_EZPAGES . $ez_order_by;

// Split Page
// reset page when page is unknown
                if (($_GET['page'] == '' or $_GET['page'] == '1') and $_GET['ezID'] != '') {
                  $check_page = $db->Execute($pages_query_raw);
                  $check_count = 1;
                  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_EZPAGE) {
                    foreach ($check_page as $item) {
                      if ($item['customers_id'] == $_GET['cID']) {
                        break;
                      }
                      $check_count++;
                    }
                    $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS_EZPAGE) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS_EZPAGE) != 0 ? .5 : 0)), 0);
                  } else {
                    $_GET['page'] = 1;
                  }
                }

                $pages_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_EZPAGE, $pages_query_raw, $pages_query_numrows);
                $pages = $db->Execute($pages_query_raw);

                foreach ($pages as $page) {
                  if ((!isset($_GET['ezID']) || (isset($_GET['ezID']) && ($_GET['ezID'] == $page['pages_id']))) && !isset($ezInfo) && (substr($action, 0, 3) != 'new')) {
                    $ezInfo_array = $page;
                    $ezInfo = new objectInfo($ezInfo_array);
                  }
                  $zv_link_method_cnt = 0;
                  if ($page['alt_url'] != '') {
                    $zv_link_method_cnt++;
                  }
                  if ($page['alt_url_external'] != '') {
                    $zv_link_method_cnt++;
                  }
                  if ($page['pages_html_text'] != '' and strlen(trim($page['pages_html_text'])) > 6) {
                    $zv_link_method_cnt++;
                  }
                  if (isset($ezInfo) && is_object($ezInfo) && ($page['pages_id'] == $ezInfo->pages_id)) {
                    echo '              <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href=\'' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $page['pages_id']) . '\'" role="button">' . "\n";
                  } else {
                    echo '              <tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $page['pages_id']) . '\'" role="button">' . "\n";
                  }
                  ?>
                <td class="dataTableContent text-right"><?php echo ($zv_link_method_cnt > 1 ? zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED_EZPAGES, 10, 10) : '') . '&nbsp;' . $page['pages_id']; ?></td>
                <td class="dataTableContent"><?php echo $page['pages_title']; ?></td>
                <td class="dataTableContent text-center"><?php echo ($page['page_open_new_window'] == 1 ? '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=page_open_new_window&current=' . $page['page_open_new_window'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=page_open_new_window&current=' . $page['page_open_new_window'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent text-center"><?php echo ($page['page_is_ssl'] == 1 ? '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=page_is_ssl&current=' . $page['page_is_ssl'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=page_is_ssl&current=' . $page['page_is_ssl'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent text-right"><?php echo $page['header_sort_order'] . '&nbsp;' . ($page['status_header'] == 1 ? '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_header&current=' . $page['status_header'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_header&current=' . $page['status_header'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent text-right"><?php echo $page['sidebox_sort_order'] . '&nbsp;' . ($page['status_sidebox'] == 1 ? '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_sidebox&current=' . $page['status_sidebox'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_sidebox&current=' . $page['status_sidebox'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent text-right"><?php echo $page['footer_sort_order'] . '&nbsp;' . ($page['status_footer'] == 1 ? '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_footer&current=' . $page['status_footer'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_footer&current=' . $page['status_footer'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent text-right"><?php echo $page['toc_chapter']; ?></td>
                <td class="dataTableContent text-right"><?php echo $page['toc_sort_order'] . '&nbsp;' . ($page['status_toc'] == 1 ? '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_toc&current=' . $page['status_toc'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_toc&current=' . $page['status_toc'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent text-center">&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($ezInfo) && is_object($ezInfo) && ($page['pages_id'] == $ezInfo->pages_id)) ? 'ezID=' . $page['pages_id'] . '&action=new' : '') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?>
                    <?php
                    if (isset($ezInfo) && is_object($ezInfo) && ($page['pages_id'] == $ezInfo->pages_id)) {
                      echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                    } else {
                      echo '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($page['pages_id']) ? 'ezID=' . $page['pages_id'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                    }
                    ?>
                </td>
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
                case 'delete':
                  $heading[] = array('text' => '<h4>' . $ezInfo->pages_title . '</h4>');

                  $contents = array('form' => zen_draw_form('pages', FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('ezID', $ezInfo->pages_id));
                  $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                  $contents[] = array('text' => '<br><b>' . $ezInfo->pages_title . '</b>');

                  $contents[] = array('align' => 'center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                  break;
                default:
                  if (is_object($ezInfo)) {
                    $heading[] = array('text' => '<h4>' . TEXT_PAGE_TITLE . '&nbsp;' . $ezInfo->pages_title . '&nbsp;|&nbsp;' . TEXT_CHAPTER . '&nbsp;' . $ezInfo->toc_chapter . '</h4>');

                    $zv_link_method_cnt = 0;
                    if ($ezInfo->alt_url != '') {
                      $zv_link_method_cnt++;
                    }
                    if ($ezInfo->alt_url_external != '') {
                      $zv_link_method_cnt++;
                    }
                    if ($ezInfo->pages_html_text != '' and strlen(trim($ezInfo->pages_html_text)) > 6) {
                      $zv_link_method_cnt++;
                    }

                    if ($zv_link_method_cnt > 1) {
                      $contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED_EZPAGES, 10, 10) . ' &nbsp;' . TEXT_WARNING_MULTIPLE_SETTINGS);
                    }

                    $contents[] = array('text' => TEXT_ALT_URL . (empty($ezInfo->alt_url) ? '&nbsp;' . TEXT_NONE : '<br>' . $ezInfo->alt_url));
                    $contents[] = array('text' => '<br>' . TEXT_ALT_URL_EXTERNAL . (empty($ezInfo->alt_url_external) ? '&nbsp;' . TEXT_NONE : '<br>' . $ezInfo->alt_url_external));
                    $contents[] = array('text' => '<br>' . TEXT_PAGES_HTML_TEXT . '<br>' . substr(strip_tags($ezInfo->pages_html_text), 0, 100));

                    $contents[] = array('align' => 'text-center', 'text' => '<br><a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $ezInfo->pages_id . '&action=new') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $ezInfo->pages_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a><br><br><br>');

                    if ($ezInfo->date_scheduled) {
                      $contents[] = array('text' => '<br>' . sprintf(TEXT_PAGES_SCHEDULED_AT_DATE, zen_date_short($ezInfo->date_scheduled)));
                    }

                    if ($ezInfo->expires_date) {
                      $contents[] = array('text' => '<br>' . sprintf(TEXT_PAGES_EXPIRES_AT_DATE, zen_date_short($ezInfo->expires_date)));
                    } elseif ($ezInfo->expires_impressions) {
                      $contents[] = array('text' => '<br>' . sprintf(TEXT_PAGES_EXPIRES_AT_IMPRESSIONS, $ezInfo->expires_impressions));
                    }

                    if ($ezInfo->date_status_change) {
                      $contents[] = array('text' => '<br>' . sprintf(TEXT_PAGES_STATUS_CHANGE, zen_date_short($ezInfo->date_status_change)));
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
              <td><?php echo $pages_split->display_count($pages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_EZPAGE, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PAGES); ?></td>
              <td class="text-right"><?php echo $pages_split->display_links($pages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_EZPAGE, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y', 'ezID'))); ?></td>
            </tr>
            <tr>
              <td class="text-right" colspan="2"><a href="<?php echo zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_PAGE; ?></a></td>
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
