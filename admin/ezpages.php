<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */
require 'includes/application_top.php';

if (!isset($_SESSION['ez_sort_order'])) {
  $_SESSION['ez_sort_order'] = 0;
}
$currentSortOrder = $_SESSION['ez_sort_order'];

$currentPage = (isset($_GET['page']) && $_GET['page'] != '' ? (int)$_GET['page'] : 0);

if (isset($_GET['action']) && $_GET['action'] == 'set_editor') {
  // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
  $action = '';
  zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN));
}

$languages = zen_get_languages();

$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (!empty($action)) {
  switch ($action) {
    case 'set_ez_sort_order':
      $currentSortOrder = $_SESSION['ez_sort_order'] = (int)$_GET['reset_ez_sort_order'];
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, zen_get_all_get_params(['action'])));
      break;
    case 'update_status':
      zen_set_ezpage_status((int)$_POST['ezID'], (int)$_POST['new_status'], $_POST['fieldName']);
      $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, zen_get_all_get_params(['action', 'ezID']) . 'ezID=' . $_POST['ezID']));
      break;
    case 'insert':
    case 'update':
      if (isset($_POST['pages_id'])) {
        $pages_id = (int)$_POST['pages_id'];
      }
      $page_open_new_window = (int)$_POST['page_open_new_window'];
      $page_is_ssl = (int)$_POST['page_is_ssl'];
      $status_visible = (int)$_POST['status_visible'];

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

      $pages_html_url_flag = false;
      $page_error = false;
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        if ($_POST['pages_html_text'][$languages[$i]['id']] != '' && strlen(trim($_POST['pages_html_text'][$languages[$i]['id']])) > 6) {
          $pages_html_url_flag = true;
        }
        if (empty($_POST['pages_title'][$languages[$i]['id']])) {
          $messageStack->add(ERROR_PAGE_TITLE_REQUIRED . ' (' . $languages[$i]['name'] . ')', 'error');
          $page_error = true;
        }
        if (empty($pages_html_text)) {

        }
      }

      $zv_link_method_cnt = 0;
      if ($alt_url != '') {
        $zv_link_method_cnt++;
      }
      if ($alt_url_external != '') {
        $zv_link_method_cnt++;
      }
      $pages_html_text_count = 0;
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        if (!empty($pages_html_text[$languages[$i]['id']]) && strlen(trim($pages_html_text[$languages[$i]['id']])) > 6) {
          $pages_html_text_count = $i + 1;
        }
      }
      if ($pages_html_text_count > 0) {
        $zv_link_method_cnt++;
      }
      if ($zv_link_method_cnt > 1) {
        $messageStack->add(ERROR_MULTIPLE_HTML_URL, 'error');
        $page_error = true;
      }

      // -----
      // Let a watching observer know that the EZ-Page's data is about to be recorded in the
      // database, giving it the opportunity to perform its checks on any additional data and
      // add any additional fields to be written to the 'ezpages' table.
      //
      // If the observer sets the $page_error (i.e. $p2) value to (bool)true, it is the observer's
      // responsibility to add a message for display to the current admin.
      //
      $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_UPDATE_BASE', $action, $page_error, $sql_data_array);

      if ($page_error == false) {
        $sql_data_array = [
          'page_open_new_window' => $page_open_new_window,
          'page_is_ssl' => $page_is_ssl,
          'alt_url' => $alt_url,
          'alt_url_external' => $alt_url_external,
          'status_header' => $status_header,
          'status_sidebox' => $status_sidebox,
          'status_footer' => $status_footer,
          'status_toc' => $status_toc,
          'status_visible' => $status_visible,
          'header_sort_order' => $pages_header_sort_order,
          'sidebox_sort_order' => $pages_sidebox_sort_order,
          'footer_sort_order' => $pages_footer_sort_order,
          'toc_sort_order' => $pages_toc_sort_order,
          'toc_chapter' => $toc_chapter,
        ];

        if ($action == 'insert') {
          zen_db_perform(TABLE_EZPAGES, $sql_data_array);
          $pages_id = $db->insert_ID();
          $pages_title_array = zen_db_prepare_input($_POST['pages_title']);
          $pages_html_text_array = zen_db_prepare_input($_POST['pages_html_text']);
          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $sql_data_array = [
              'pages_title' => $pages_title_array[$language_id],
              'pages_html_text' => $pages_html_text_array[$language_id],
              'languages_id' => (int)$language_id,
              'pages_id' => (int)$pages_id,
            ];

            $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_UPDATE_LANG_INSERT', array('pages_id' => (int)$pages_id, 'languages_id' => $language_id), $sql_data_array);

            zen_db_perform(TABLE_EZPAGES_CONTENT, $sql_data_array);
          }
          $messageStack->add(SUCCESS_PAGE_INSERTED, 'success');
          zen_record_admin_activity('EZ-Page with ID ' . (int)$pages_id . ' added.', 'info');
        } elseif ($action == 'update') {
          zen_db_perform(TABLE_EZPAGES, $sql_data_array, 'update', "pages_id = " . (int)$pages_id);
          $pages_title_array = zen_db_prepare_input($_POST['pages_title']);
          $pages_html_text_array = zen_db_prepare_input($_POST['pages_html_text']);
          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $sql_data_array = [
              'pages_title' => $pages_title_array[$language_id],
              'pages_html_text' => $pages_html_text_array[$language_id],
            ];

            $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_UPDATE_LANG_UPDATE', array('pages_id' => (int)$pages_id, 'languages_id' => $language_id), $sql_data_array);

            zen_db_perform(TABLE_EZPAGES_CONTENT, $sql_data_array, 'update', "pages_id = " . (int)$pages_id . " and languages_id = " . (int)$language_id);
          }
          $messageStack->add(SUCCESS_PAGE_UPDATED, 'success');
          zen_record_admin_activity('EZ-Page with ID ' . (int)$pages_id . ' updated.', 'info');
        }

        zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'ezID=' . $pages_id));
      } else {
        $_GET['pages_id'] = $pages_id;
        $_GET['ezID'] = $pages_id;
        $_GET['action'] = 'new';
        $action = 'new';
        $ezID = $pages_id;
      }
      break;
    case 'deleteconfirm':
      $pages_id = zen_db_prepare_input($_POST['ezID']);
      $db->Execute("DELETE FROM " . TABLE_EZPAGES . "
                    WHERE pages_id = " . (int)$pages_id);
      $db->Execute("DELETE FROM " . TABLE_EZPAGES_CONTENT . "
                    WHERE pages_id = " . (int)$pages_id);
      $messageStack->add(SUCCESS_PAGE_REMOVED, 'success');
      zen_record_admin_activity('EZ-Page with ID ' . (int)$pages_id . ' deleted.', 'notice');
      zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '')));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <?php
    if ($editor_handler != '') {
      include $editor_handler;
    }
    ?>
  </head>
  <body>
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE . ' ' . (!empty($_GET['ezID']) ? TEXT_INFO_PAGES_ID . $_GET['ezID'] : TEXT_INFO_PAGES_ID_SELECT); ?></h1>
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
            array('id' => '5', 'text' => TEXT_SORT_PAGE_ID_TITLE),
          );
          ?>
          <div class="col-sm-offset-4 col-sm-4">
            <?php echo zen_draw_form('set_ez_sort_order_form', FILENAME_EZPAGES_ADMIN, '', 'get', 'class="form-horizontal"'); ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_SORT_CHAPTER_TOC_TITLE_INFO, 'reset_ez_sort_order', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9">
                <?php echo zen_draw_pull_down_menu('reset_ez_sort_order', $ez_sort_order_array, $currentSortOrder, 'onChange="this.form.submit();" class="form-control" id="reset_ez_sort_order"'); ?>
              </div>
            </div>
            <?php echo zen_hide_session_id(); ?>
            <?php echo ($currentPage != 0 ? zen_draw_hidden_field('page', $currentPage) : ''); ?>
            <?php echo zen_draw_hidden_field('action', 'set_ez_sort_order'); ?>
            <?php echo '</form>'; ?>
          </div>
          <div class="col-sm-4">
            <?php
// toggle switch for editor
            echo zen_draw_form('set_editor_form', FILENAME_EZPAGES_ADMIN, '', 'get', 'class="form-horizontal"');
            ?>
            <div class="form-group">
              <?php echo zen_draw_label(TEXT_EDITOR_INFO, 'reset_editor', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9">
                <?php echo zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();" class="form-control" id="reset_editor"'); ?>
              </div>
            </div>
            <?php echo zen_hide_session_id(); ?>
            <?php echo zen_draw_hidden_field('action', 'set_editor'); ?>
            <?php echo '</form>'; ?>
          <?php } ?>
        </div>
      </div>
      <?php
      if ($action == 'new') {
        $form_action = 'insert';

        $parameters = [
          'pages_title' => '',
          'pages_html_text' => '',
          'alt_url' => '',
          'alt_url_external' => '',
          'header_sort_order' => '',
          'sidebox_sort_order' => '',
          'footer_sort_order' => '',
          'toc_sort_order' => '',
          'toc_chapter' => '',
          'status_header' => 1,
          'status_sidebox' => 1,
          'status_footer' => 1,
          'status_toc' => 1,
          'page_open_new_window' => 0,
          'page_is_ssl' => 1,
        ];

        $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_NEW', '', $parameters);

        $ezInfo = new objectInfo($parameters);

        if (isset($_GET['ezID'])) {
          $form_action = 'update';

          $ezID = (int)$_GET['ezID'];

          $page_query = "SELECT e.*, ec.pages_title, ec.pages_html_text
                         FROM " . TABLE_EZPAGES . " e
                         INNER JOIN " . TABLE_EZPAGES_CONTENT . " ec ON (e.pages_id=ec.pages_id AND ec.languages_id = " . (int)$_SESSION['languages_id'] . ")
                         WHERE e.pages_id = " . (int)$_GET['ezID'];
          $page = $db->Execute($page_query);
          $ezInfo->updateObjectInfo($page->fields);
        } elseif (!empty($_POST)) {
          $ezInfo->updateObjectInfo($_POST);
        }

        echo zen_draw_form('new_page', FILENAME_EZPAGES_ADMIN, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data" class="form-horizontal"');
        if ($form_action == 'update') {
          echo zen_draw_hidden_field('pages_id', $ezID);
        }
        ?>

        <div class="form-group">
          <div class="col-sm-12">
            <?php echo (($form_action == 'insert') ? '<button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button>' : '<button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button>') . ' <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['ezID']) ? 'ezID=' . $_GET['ezID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-3">
            <p class="control-label"><?php echo TEXT_PAGES_TITLE; ?></p>
          </div>
          <div class="col-sm-9 col-md-6">
            <?php
            $pages_title = '';
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
              if (!empty($_GET['ezID'])) {
                $title_query_sql = "SELECT pages_title
                                    FROM " . TABLE_EZPAGES_CONTENT . "
                                    WHERE pages_id = " . (int)$_GET['ezID'] . "
                                    AND languages_id = " . (int)$languages[$i]['id'];
                $title_query = $db->Execute($title_query_sql);
                $pages_title = $title_query->fields['pages_title'];
              } else {
                $pages_title = '';
              }
              ?>
              <div class="input-group">
                <span class="input-group-addon"><?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?></span>
                <?php echo zen_draw_input_field('pages_title[' . $languages[$i]['id'] . ']', htmlspecialchars($pages_title, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_EZPAGES_CONTENT, 'pages_title') . ' class="form-control" id="pages_title[' . $languages[$i]['id'] . ']" required', false); ?>
                <span class="input-group-addon alert-danger">*</span>
              </div>
              <br>
            <?php } ?>
          </div>
        </div>
        <?php
        // -----
        // Give an observer the chance to supply some additional ezpages-related inputs.  Each
        // entry in the $extra_page_inputs returned is expected to contain:
        //
        // array(
        //    'label' => array(
        //        'text' => 'The label text',   (required)
        //        'field_name' => 'The name of the field associated with the label', (required)
        //        'addl_class' => {Any additional class to be applied to the label} (optional)
        //        'parms' => {Any additional parameters for the label, e.g. 'style="font-weight: 700;"} (optional)
        //    ),
        //    'input' => 'The HTML to be inserted' (required)
        // )
        //
        $extra_page_inputs = [];
        $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_FORM_FIELDS', $ezInfo, $extra_page_inputs);
        if (!empty($extra_page_inputs)) {
          foreach ($extra_page_inputs as $extra_input) {
            $addl_class = (isset($extra_input['label']['addl_class'])) ? (' ' . $extra_input['label']['addl_class']) : '';
            $parms = (isset($extra_input['label']['parms'])) ? (' ' . $extra_input['label']['parms']) : '';
            ?>
            <div class="form-group">
              <?php echo zen_draw_label($extra_input['label']['text'], $extra_input['label']['field_name'], 'class="col-sm-3 control-label' . $addl_class . '"' . $parms); ?>
              <div class="col-sm-9 col-md-6"><?php echo $extra_input['input']; ?></div>
            </div>
            <?php
          }
        }
        ?>
        <div class="form-group">
          <?php echo zen_draw_label(TABLE_HEADING_PAGE_OPEN_NEW_WINDOW, 'page_open_new_window', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
            <label class="radio-inline"><?php echo zen_draw_radio_field('page_open_new_window', '1', ($ezInfo->page_open_new_window == 1)) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('page_open_new_window', '0', ($ezInfo->page_open_new_window == 0)) . TEXT_NO; ?></label>
          </div>
        </div>
        <div class="form-group">
          <?php echo zen_draw_label(TABLE_HEADING_PAGE_IS_SSL, 'page_is_ssl', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
            <label class="radio-inline"><?php echo zen_draw_radio_field('page_is_ssl', '1', ($ezInfo->page_is_ssl == 1)) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('page_is_ssl', '0', ($ezInfo->page_is_ssl == 0)) . TEXT_NO; ?></label>
          </div>
        </div>
        <div class="form-group">
          <?php echo zen_draw_label(TABLE_HEADING_PAGE_IS_VISIBLE, 'page_is_visible', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_visible', '1', ($ezInfo->status_visible == 1)) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_visible', '0', ($ezInfo->status_visible == 0)) . TEXT_NO; ?></label>
            <br><br><?php echo TABLE_HEADING_PAGE_IS_VISIBLE_EXPLANATION; ?>
          </div>
        </div>
        <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>
        <div class="form-group">
          <div class="col-sm-3">
            <p class="control-label"><?php echo TABLE_HEADING_STATUS_HEADER; ?></p>
          </div>
          <div class="col-sm-9 col-md-6">
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_header', '1', ($ezInfo->status_header == 1)) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_header', '0', ($ezInfo->status_header == 0)) . TEXT_NO; ?></label>
          </div>
        </div>
        <div class="form-group">
          <?php echo zen_draw_label(TEXT_HEADER_SORT_ORDER, 'header_sort_order', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-2">
            <?php echo zen_draw_input_field('header_sort_order', $ezInfo->header_sort_order, zen_set_field_length(TABLE_EZPAGES, 'header_sort_order') . ' class="form-control" id="header_sort_order"'); ?>
          </div>
        </div>
        <hr>
        <div class="form-group">
          <div class="col-sm-3">
            <p class="control-label"><?php echo zen_draw_label(TABLE_HEADING_STATUS_SIDEBOX, 'status_sidebox', 'class="control-label"'); ?></p>
          </div>
          <div class="col-sm-9 col-md-6">
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_sidebox', '1', ($ezInfo->status_sidebox == 1)) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_sidebox', '0', ($ezInfo->status_sidebox == 0)) . TEXT_NO; ?></label>
          </div>
        </div>
        <div class="form-group">
          <?php echo zen_draw_label(TEXT_SIDEBOX_SORT_ORDER, 'sidebox_sort_order', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-2">
            <?php echo zen_draw_input_field('sidebox_sort_order', $ezInfo->sidebox_sort_order, zen_set_field_length(TABLE_EZPAGES, 'sidebox_sort_order') . ' class="form-control" id="sidebox_sort_order"'); ?>
          </div>
        </div>
        <hr>
        <div class="form-group">
          <div class="col-sm-3">
            <p class="control-label"><?php echo TABLE_HEADING_STATUS_FOOTER; ?></p>
          </div>
          <div class="col-sm-9 col-md-6">
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_footer', '1', ($ezInfo->status_footer == 1)) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_footer', '0', ($ezInfo->status_footer == 0)) . TEXT_NO; ?></label>
          </div>
        </div>
        <div class="form-group">
          <?php echo zen_draw_label(TEXT_FOOTER_SORT_ORDER, 'status_footer', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-2">
            <?php echo zen_draw_input_field('footer_sort_order', $ezInfo->footer_sort_order, zen_set_field_length(TABLE_EZPAGES, 'footer_sort_order') . ' class="form-control" id="footer_sort_order"'); ?>
          </div>
        </div>
        <hr>
        <div class="form-group">
          <?php echo zen_draw_label(TABLE_HEADING_CHAPTER_PREV_NEXT, 'toc_chapter', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-2">
            <?php echo zen_draw_input_field('toc_chapter', $ezInfo->toc_chapter, zen_set_field_length(TABLE_EZPAGES, 'toc_chapter', '6') . ' class="form-control" id="toc_chapter"'); ?>
          </div>
        </div>
        <hr>
        <div class="form-group">
          <div class="col-sm-3">
            <p class="control-label"><?php echo TABLE_HEADING_STATUS_TOC; ?></p>
          </div>
          <div class="col-sm-9 col-md-6">
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_toc', '1', ($ezInfo->status_toc == 1)) . TEXT_YES; ?></label>
            <label class="radio-inline"><?php echo zen_draw_radio_field('status_toc', '0', ($ezInfo->status_toc == 0)) . TEXT_NO; ?></label>
          </div>
        </div>
        <div class="form-group">
          <?php echo zen_draw_label(TEXT_TOC_SORT_ORDER, 'toc_sort_order', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-2">
            <?php echo zen_draw_input_field('toc_sort_order', $ezInfo->toc_sort_order, zen_set_field_length(TABLE_EZPAGES, 'toc_sort_order') . ' class="form-control" id="toc_sort_order"'); ?>
          </div>
        </div>
        <div class="col-sm-12">
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
          <div class="col-sm-3">
            <p class="control-label"><?php echo TEXT_PAGES_HTML_TEXT; ?></p>
          </div>
          <div class="col-sm-9 col-md-6">
            <?php
            $pages_html_text = '';

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
              if (!empty($_GET['ezID'])) {
                $text_query_sql = "SELECT pages_html_text
                                   FROM " . TABLE_EZPAGES_CONTENT . "
                                   WHERE pages_id = " . (int)$_GET['ezID'] . "
                                   AND languages_id = " . (int)$languages[$i]['id'];
                $text_query = $db->Execute($text_query_sql);
                $pages_html_text = $text_query->fields['pages_html_text'];
              } else {
                $pages_html_text = '';
              }
              ?>
              <div class="input-group">
                <span class="input-group-addon"><?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?></span>
                <?php echo zen_draw_textarea_field('pages_html_text[' . $languages[$i]['id'] . ']', 'soft', '', '20', htmlspecialchars($pages_html_text, ENT_COMPAT, CHARSET, TRUE), 'class="editorHook form-control"'); ?>
              </div>
              <br>
              <?php
            }
            ?>
          </div>
        </div>
        <div class="form-group">
          <?php echo zen_draw_label(TEXT_ALT_URL, 'alt_url', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('alt_url', $ezInfo->alt_url, 'size="100" class="form-control" id="alt_url"'); ?>
            <span class="help-block"><?php echo TEXT_ALT_URL_EXPLAIN; ?></span>
          </div>
        </div>
        <div class="form-group">
          <?php echo zen_draw_label(TEXT_ALT_URL_EXTERNAL, 'alt_url_external', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_input_field('alt_url_external', $ezInfo->alt_url_external, 'size="100" class="form-control" id="alt_url_external"'); ?>
            <span class="help-block"><?php echo TEXT_ALT_URL_EXTERNAL_EXPLAIN; ?></span>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-12"><?php echo (($form_action == 'insert') ? '<button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button>' : '<button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button>') . ' <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($_GET['ezID']) ? 'ezID=' . $_GET['ezID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?></div>
        </div>
        <?php echo '</form>'; ?>
      <?php } else { ?>
<?php
        // Additional notification, allowing admin-observers to include additional legend icons
        $extra_legends = '';
        $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_MENU_LEGEND', [], $extra_legends);
?>

        <div class="row"><?php echo TEXT_LEGEND . ' ' . zen_icon('status-red', TEXT_WARNING_MULTIPLE_SETTINGS) . ' ' . TEXT_WARNING_MULTIPLE_SETTINGS .  $extra_legends; ?></div>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover" role="listbox">
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
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_VISIBLE; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_STATUS_TOC; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
              </thead>
              <tbody>

                <?php
// set display order
                switch ($currentSortOrder) {
                  case (0):
                    $ez_order_by = " ORDER BY e.toc_chapter, e.toc_sort_order, ec.pages_title";
                    break;
                  case (1):
                    $ez_order_by = " ORDER BY e.header_sort_order, ec.pages_title";
                    break;
                  case (2):
                    $ez_order_by = " ORDER BY e.sidebox_sort_order, ec.pages_title";
                    break;
                  case (3):
                    $ez_order_by = " ORDER BY e.footer_sort_order, ec.pages_title";
                    break;
                  case (4):
                    $ez_order_by = " ORDER BY ec.pages_title";
                    break;
                  case (5):
                    $ez_order_by = " ORDER BY e.pages_id, ec.pages_title";
                    break;
                  default:
                    $ez_order_by = " ORDER BY e.toc_chapter, e.toc_sort_order, ec.pages_title";
                    break;
                }

                $pages_query_raw = "SELECT e.*, ec.pages_title, ec.pages_html_text
                                    FROM " . TABLE_EZPAGES . " e
                                    INNER JOIN " . TABLE_EZPAGES_CONTENT . " ec ON (e.pages_id=ec.pages_id AND ec.languages_id = " . (int)$_SESSION['languages_id'] . ")
                                    " . $ez_order_by;

// Split Page
// reset page when page is unknown
                if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['ezID'])) {
                  $check_page = $db->Execute($pages_query_raw);
                  $check_count = 0;
                  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_EZPAGE) {
                    foreach ($check_page as $item) {
                      if ($item['pages_id'] == $_GET['ezID']) {
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
                  if ($page['pages_html_text'] != '' && strlen(trim($page['pages_html_text'])) > 6) {
                    $zv_link_method_cnt++;
                  }
                  if (isset($ezInfo) && is_object($ezInfo) && ($page['pages_id'] == $ezInfo->pages_id)) {
                    ?>
                    <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_EZPAGES_ADMIN, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'ezID=' . $page['pages_id']); ?>'" role="option" aria-selected="true">
                    <?php } else { ?>
                    <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_EZPAGES_ADMIN, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'ezID=' . $page['pages_id']); ?>'" role="option" aria-selected="false">
                    <?php } ?>
                    <td class="dataTableContent text-right"><?php echo ($zv_link_method_cnt > 1 ? zen_icon('status-red', IMAGE_ICON_STATUS_RED_EZPAGES) : '') . '&nbsp;' . $page['pages_id']; ?></td>
                    <td class="dataTableContent"><?php echo $page['pages_title']; ?></td>
                    <td class="dataTableContent text-center">
                      <?php echo zen_draw_form('page_open_new_window', FILENAME_EZPAGES_ADMIN, 'action=update_status'); ?>
                      <button type="submit" class="btn btn-status">
                        <?php if ($page['page_open_new_window'] == '1') { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo IMAGE_ICON_STATUS_ON; ?>"></i>
                        <?php } else { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>"></i>
                        <?php } ?>
                      </button>
                      <?php
                      echo zen_draw_hidden_field('ezID', $page['pages_id']);
                      echo zen_draw_hidden_field('new_status', ($page['page_open_new_window'] == '1' ? '0' : '1'));
                      echo zen_draw_hidden_field('fieldName', 'page_open_new_window');
                      echo '</form>';
                      ?></td>
                    <td class="dataTableContent text-center">
                      <?php echo zen_draw_form('page_is_ssl', FILENAME_EZPAGES_ADMIN, 'action=update_status'); ?>
                      <button type="submit" class="btn btn-status">
                        <?php if ($page['page_is_ssl'] == '1') { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo IMAGE_ICON_STATUS_ON; ?>"></i>
                        <?php } else { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>"></i>
                        <?php } ?>
                      </button>
                      <?php
                      echo zen_draw_hidden_field('ezID', $page['pages_id']);
                      echo zen_draw_hidden_field('new_status', ($page['page_is_ssl'] == '1' ? '0' : '1'));
                      echo zen_draw_hidden_field('fieldName', 'page_is_ssl');
                      echo '</form>';
                      ?></td>
                    <td class="dataTableContent text-right">
                      <?php echo $page['header_sort_order'] . '&nbsp;'; ?>
                      <?php echo zen_draw_form('header_status', FILENAME_EZPAGES_ADMIN, 'action=update_status'); ?>
                      <button type="submit" class="btn btn-status">
                        <?php if ($page['status_header'] == '1') { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo IMAGE_ICON_STATUS_ON; ?>"></i>
                        <?php } else { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>"></i>
                        <?php } ?>
                      </button>
                      <?php
                      echo zen_draw_hidden_field('ezID', $page['pages_id']);
                      echo zen_draw_hidden_field('new_status', ($page['status_header'] == '1' ? '0' : '1'));
                      echo zen_draw_hidden_field('fieldName', 'status_header');
                      echo '</form>';
                      ?></td>
                    <td class="dataTableContent text-right">
                      <?php echo $page['sidebox_sort_order'] . '&nbsp;'; ?>
                      <?php echo zen_draw_form('sidebox_status', FILENAME_EZPAGES_ADMIN, 'action=update_status'); ?>
                      <button type="submit" class="btn btn-status">
                        <?php if ($page['status_sidebox'] == '1') { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo IMAGE_ICON_STATUS_ON; ?>"></i>
                        <?php } else { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>"></i>
                        <?php } ?>
                      </button>
                      <?php
                      echo zen_draw_hidden_field('ezID', $page['pages_id']);
                      echo zen_draw_hidden_field('new_status', ($page['status_sidebox'] == '1' ? '0' : '1'));
                      echo zen_draw_hidden_field('fieldName', 'status_sidebox');
                      echo '</form>';
                      ?></td>
                    <td class="dataTableContent text-right">
                      <?php echo $page['footer_sort_order'] . '&nbsp;'; ?>
                      <?php echo zen_draw_form('footer_status', FILENAME_EZPAGES_ADMIN, 'action=update_status'); ?>
                      <button type="submit" class="btn btn-status">
                        <?php if ($page['status_footer'] == '1') { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo IMAGE_ICON_STATUS_ON; ?>"></i>
                        <?php } else { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>"></i>
                        <?php } ?>
                      </button>
                      <?php
                      echo zen_draw_hidden_field('ezID', $page['pages_id']);
                      echo zen_draw_hidden_field('new_status', ($page['status_footer'] == '1' ? '0' : '1'));
                      echo zen_draw_hidden_field('fieldName', 'status_footer');
                      echo '</form>';
                      ?>
                    </td>
                    <td class="dataTableContent text-right"><?php echo $page['toc_chapter']; ?></td>
                    <td class="dataTableContent text-center">
                      <?php echo zen_draw_form('status_visible', FILENAME_EZPAGES_ADMIN, 'action=update_status'); ?>
                      <button type="submit" class="btn btn-status">
                        <?php if ($page['status_visible'] == '1') { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo IMAGE_ICON_STATUS_ON; ?>"></i>
                        <?php } else { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>"></i>
                        <?php } ?>
                      </button>
                      <?php
                      echo zen_draw_hidden_field('ezID', $page['pages_id']);
                      echo zen_draw_hidden_field('new_status', ($page['status_visible'] == '1' ? '0' : '1'));
                      echo zen_draw_hidden_field('fieldName', 'status_visible');
                      echo '</form>';
                      ?>
                    </td>
                    <td class="dataTableContent text-right">
                      <?php echo $page['toc_sort_order'] . '&nbsp;'; ?>
                      <?php echo zen_draw_form('status_toc', FILENAME_EZPAGES_ADMIN, 'action=update_status'); ?>
                      <button type="submit" class="btn btn-status">
                        <?php if ($page['status_toc'] == '1') { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo IMAGE_ICON_STATUS_ON; ?>"></i>
                        <?php } else { ?>
                          <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>"></i>
                        <?php } ?>
                      </button>
                      <?php
                      echo zen_draw_hidden_field('ezID', $page['pages_id']);
                      echo zen_draw_hidden_field('new_status', ($page['status_toc'] == '1' ? '0' : '1'));
                      echo zen_draw_hidden_field('fieldName', 'status_toc');
                      echo '</form>';
                      ?>
                    </td>
                    <?php
                    // -----
                    // Give a watching observer the chance to insert another icon/link to the standard list of 'action' icons.
                    //
                    $extra_action_icons = '';
                    $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_EXTRA_ACTION_ICONS', $page, $extra_action_icons);
                    ?>
                    <td class="dataTableContent text-right actions">
                      <div class="btn-group">
                        <a href="<?php echo zen_href_link(FILENAME_EZPAGES_ADMIN, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'ezID=' . $page['pages_id'] . '&action=new'); ?>" title="<?php echo ICON_EDIT; ?>" class="btn btn-sm btn-default btn-edit" role="button">
                          <?php echo zen_icon('pencil', '', 'lg', hidden: true) ?>
                        </a>
                        <?php echo $extra_action_icons; ?>
                        <?php if (isset($ezInfo) && is_object($ezInfo) && ($page['pages_id'] == $ezInfo->pages_id)) {
                          echo zen_icon('caret-right', '', '2x', true);
                        } else { ?>
                          <a href="<?php echo zen_href_link(FILENAME_EZPAGES_ADMIN, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . (isset($page['pages_id']) ? 'ezID=' . $page['pages_id'] : '')); ?>" title="<?php echo IMAGE_ICON_INFO ?>" role="button">
                            <?php echo zen_icon('circle-info', '', '2x', true) ?>
                          </a>
                        <?php } ?>
                      </div>
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
            $heading = [];
            $contents = [];
            switch ($action) {
              case 'delete':
                $heading[] = array('text' => '<h4>' . $ezInfo->pages_title . '</h4>');

                $contents = array('form' => zen_draw_form('pages', FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('ezID', $ezInfo->pages_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br><b>' . $ezInfo->pages_title . '</b>');

                $contents[] = array('align' => 'center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (!empty($ezInfo) && is_object($ezInfo)) {
                  $heading[] = array('text' => '<h4>' . TEXT_PAGE_TITLE . '&nbsp;' . $ezInfo->pages_title . '&nbsp;|&nbsp;' . TEXT_CHAPTER . '&nbsp;' . $ezInfo->toc_chapter . '</h4>');

                  $zv_link_method_cnt = 0;
                  if ($ezInfo->alt_url != '') {
                    $zv_link_method_cnt++;
                  }
                  if ($ezInfo->alt_url_external != '') {
                    $zv_link_method_cnt++;
                  }
                  if ($ezInfo->pages_html_text != '' && strlen(trim($ezInfo->pages_html_text)) > 6) {
                    $zv_link_method_cnt++;
                  }

                  if ($zv_link_method_cnt > 1) {
                    $contents[] = array('text' => zen_icon('status-red', IMAGE_ICON_STATUS_RED_EZPAGES) . ' &nbsp;' . '<b>' . TEXT_WARNING_MULTIPLE_SETTINGS . '</b>');
                  }

                  $contents[] = array('text' => TEXT_ALT_URL . (empty($ezInfo->alt_url) ? '&nbsp;' . TEXT_NONE : '<br>' . $ezInfo->alt_url));
                  $contents[] = array('text' => TEXT_ALT_URL_EXTERNAL . (empty($ezInfo->alt_url_external) ? '&nbsp;' . TEXT_NONE : '<br>' . $ezInfo->alt_url_external));
                  $ez_content = strip_tags($ezInfo->pages_html_text);
                  $ez_sub_content = zen_trunc_string($ez_content, MAX_PREVIEW);
                  $contents[] = array('text' => TEXT_PAGES_HTML_TEXT . '<br>' . $ez_sub_content);

                  $contents[] = array('align' => 'text-center', 'text' => '<br><a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $ezInfo->pages_id . '&action=new') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $ezInfo->pages_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');

                  if ($ezInfo->date_status_change) {
                    $contents[] = array('text' => '<br>' . sprintf(TEXT_PAGES_STATUS_CHANGE, zen_date_short($ezInfo->date_status_change)));
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
