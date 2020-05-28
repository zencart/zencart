<?php

/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 18 Modified in v1.5.7 $
 */
require('includes/application_top.php');

if (!isset($_SESSION['ez_sort_order'])) {
  $_SESSION['ez_sort_order'] = 0;
}
if (!isset($_GET['reset_ez_sort_order'])) {
  $reset_ez_sort_order = $_SESSION['ez_sort_order'];
}

if (isset($_GET['action']) && $_GET['action'] == 'set_editor') {
  // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
  $action = '';
  zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN));
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$languages = zen_get_languages();
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
    case 'status_visible':
      zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'status_visible');
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
        $sql_data_array = array(
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
          'toc_chapter' => $toc_chapter);

        if ($action == 'insert') {
          zen_db_perform(TABLE_EZPAGES, $sql_data_array);
          $pages_id = $db->insert_ID();
          $pages_title_array = zen_db_prepare_input($_POST['pages_title']);
          $pages_html_text_array = zen_db_prepare_input($_POST['pages_html_text']);
          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $sql_data_array = array(
              'pages_title' => $pages_title_array[$language_id],
              'pages_html_text' => $pages_html_text_array[$language_id],
              'languages_id' => (int)$language_id,
              'pages_id' => (int)$pages_id);

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
            $sql_data_array = array(
              'pages_title' => $pages_title_array[$language_id],
              'pages_html_text' => $pages_html_text_array[$language_id]);

            $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_UPDATE_LANG_UPDATE', array('pages_id' => (int)$pages_id, 'languages_id' => $language_id), $sql_data_array);

            zen_db_perform(TABLE_EZPAGES_CONTENT, $sql_data_array, 'update', "pages_id = '" . (int)$pages_id . "' and languages_id = '" . $language_id . "'");
          }
          $messageStack->add(SUCCESS_PAGE_UPDATED, 'success');
          zen_record_admin_activity('EZ-Page with ID ' . (int)$pages_id . ' updated.', 'info');
        }

        zen_redirect(zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'ezID=' . $pages_id));
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
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
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
            array('id' => '5', 'text' => TEXT_SORT_PAGE_ID_TITLE)
          );
          ?>
          <div class="col-sm-6 text-right">
              <?php echo zen_draw_form('set_ez_sort_order_form', FILENAME_EZPAGES_ADMIN, '', 'get'); ?>
              <?php echo TEXT_SORT_CHAPTER_TOC_TITLE_INFO . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_ez_sort_order', $ez_sort_order_array, $reset_ez_sort_order, 'onChange="this.form.submit();"'); ?>
              <?php echo zen_hide_session_id(); ?>
              <?php echo (!empty($_GET['page']) ? zen_draw_hidden_field('page', $_GET['page']) : ''); ?>
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
          'pages_html_text' => '',
          'alt_url' => '',
          'alt_url_external' => '',
          'header_sort_order' => '',
          'sidebox_sort_order' => '',
          'footer_sort_order' => '',
          'toc_sort_order' => '',
          'toc_chapter' => '',
          'status_header' => '1',
          'status_sidebox' => '1',
          'status_footer' => '1',
          'status_toc' => '1',
          'page_open_new_window' => '0',
          'page_is_ssl' => '1'
        );

        $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_NEW', '', $parameters);

        $ezInfo = new objectInfo($parameters);

        if (isset($_GET['ezID'])) {
          $form_action = 'update';

          $ezID = zen_db_prepare_input($_GET['ezID']);

          $page_query = "SELECT e.*, ec.*
                         FROM " . TABLE_EZPAGES . " e,
                              " . TABLE_EZPAGES_CONTENT . " ec
                         WHERE e.pages_id = " . (int)$_GET['ezID'] . "
                         AND ec.pages_id = e.pages_id
                         AND ec.languages_id = " . (int)$_SESSION['languages_id'];
          $page = $db->Execute($page_query);
          $ezInfo->updateObjectInfo($page->fields);
        } elseif (zen_not_null($_POST)) {
          $ezInfo->updateObjectInfo($_POST);
        }

        echo zen_draw_form('new_page', FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . zen_db_prepare_input($_GET['page']) . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data" class="form-horizontal"');
        if ($form_action == 'update') {
          echo zen_draw_hidden_field('pages_id', $ezID);
        }
        ?>

        <div class="form-group">
          <div class="col-sm-12"><?php echo (($form_action == 'insert') ? '<button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button>' : '<button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button>') . ' <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['ezID']) ? 'ezID=' . $_GET['ezID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?></div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_PAGES_TITLE, 'pages_title', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php
              $pages_title = '';
              for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                if (isset($_GET['ezID']) && zen_not_null($_GET['ezID'])) {
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
                <?php echo zen_draw_input_field('pages_title[' . $languages[$i]['id'] . ']', htmlspecialchars($pages_title, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_EZPAGES_CONTENT, 'pages_title') . ' class="form-control" required', false); ?>
              </div>
              <br>
              <?php
            }
            ?>
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
        $extra_page_inputs = array();
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
            <br /><br /><?php echo TABLE_HEADING_PAGE_IS_VISIBLE_EXPLANATION; ?>
          </div>
        </div>
        <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>
        <div class="form-group">
          <div class="col-md-offset-1 col-md-10">
            <div class="col-sm-3">
                <?php echo zen_draw_label(TABLE_HEADING_STATUS_HEADER, 'status_header', 'class="control-label"'); ?>
              <label class="radio-inline"><?php echo zen_draw_radio_field('status_header', '1', ($ezInfo->status_header == 1)) . TEXT_YES; ?></label>
              <label class="radio-inline"><?php echo zen_draw_radio_field('status_header', '0', ($ezInfo->status_header == 0)) . TEXT_NO; ?></label>
              <br>
              <?php echo zen_draw_label(TEXT_HEADER_SORT_ORDER, 'header_sort_order', 'class="control-label"'); ?>
              <?php echo zen_draw_input_field('header_sort_order', $ezInfo->header_sort_order, zen_set_field_length(TABLE_EZPAGES, 'header_sort_order') . ' class="form-control"', false); ?>
              <br>
            </div>
            <div class="col-sm-3">
                <?php echo zen_draw_label(TABLE_HEADING_STATUS_SIDEBOX, 'status_sidebox', 'class="control-label"'); ?>
              <label class="radio-inline"><?php echo zen_draw_radio_field('status_sidebox', '1', ($ezInfo->status_sidebox == 1)) . TEXT_YES; ?></label>
              <label class="radio-inline"><?php echo zen_draw_radio_field('status_sidebox', '0', ($ezInfo->status_sidebox == 0)) . TEXT_NO; ?></label>
              <br>
              <?php echo zen_draw_label(TEXT_SIDEBOX_SORT_ORDER, 'sidebox_sort_order', 'class="control-label"'); ?>
              <?php echo zen_draw_input_field('sidebox_sort_order', $ezInfo->sidebox_sort_order, zen_set_field_length(TABLE_EZPAGES, 'sidebox_sort_order') . ' class="form-control"', false); ?>
              <br>
            </div>
            <div class="col-sm-3">
                <?php echo zen_draw_label(TABLE_HEADING_STATUS_FOOTER, 'status_footer', 'class="control-label"'); ?>
              <label class="radio-inline"><?php echo zen_draw_radio_field('status_footer', '1', ($ezInfo->status_footer == 1)) . TEXT_YES; ?></label>
              <label class="radio-inline"><?php echo zen_draw_radio_field('status_footer', '0', ($ezInfo->status_footer == 0)) . TEXT_NO; ?></label>
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
              <label class="radio-inline"><?php echo zen_draw_radio_field('status_toc', '1', ($ezInfo->status_toc == 1)) . TEXT_YES; ?></label>
              <label class="radio-inline"><?php echo zen_draw_radio_field('status_toc', '0', ($ezInfo->status_toc == 0)) . TEXT_NO; ?></label>
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
        </div>
        <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1'); ?></div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_PAGES_HTML_TEXT, 'pages_html_text', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
            <?php
            $pages_html_text = '';

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
              if (isset($_GET['ezID']) && zen_not_null($_GET['ezID'])) {
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
                <?php echo zen_draw_textarea_field('pages_html_text[' . $languages[$i]['id'] . ']', 'soft', '100%', '20', htmlspecialchars($pages_html_text, ENT_COMPAT, CHARSET, TRUE), 'class="editorHook form-control"'); ?>
              </div>
              <br>
              <?php
            }
            ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_ALT_URL, 'alt_url', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('alt_url', $ezInfo->alt_url, 'size="100" class="form-control"'); ?><br><?php echo TEXT_ALT_URL_EXPLAIN; ?></div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_ALT_URL_EXTERNAL, 'alt_url_external', 'class="col-sm-3 control-label"'); ?>
          <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_input_field('alt_url_external', $ezInfo->alt_url_external, 'size="100" class="form-control"'); ?>
            <span class="help-block"><?php echo TEXT_ALT_URL_EXTERNAL_EXPLAIN; ?></span>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-12"><?php echo (($form_action == 'insert') ? '<button type="submit" class="btn btn-primary">' . IMAGE_INSERT . '</button>' : '<button type="submit" class="btn btn-primary">' . IMAGE_UPDATE . '</button>') . ' <a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['ezID']) ? 'ezID=' . $_GET['ezID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?></div>
        </div>
        <?php echo '</form>'; ?>
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
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_VISIBLE; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_STATUS_TOC; ?></th>
                  <th class="dataTableHeadingContent text-center">&nbsp;</th>
                </tr>
              </thead>
              <tbody>

                <?php
// set display order
                switch (true) {
                  case ($_SESSION['ez_sort_order'] == 0):
                    $ez_order_by = " ORDER BY e.toc_chapter, e.toc_sort_order, ec.pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 1):
                    $ez_order_by = " ORDER BY e.header_sort_order, ec.pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 2):
                    $ez_order_by = " ORDER BY e.sidebox_sort_order, ec.pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 3):
                    $ez_order_by = " ORDER BY e.footer_sort_order, ec.pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 4):
                    $ez_order_by = " ORDER BY ec.pages_title";
                    break;
                  case ($_SESSION['ez_sort_order'] == 5):
                    $ez_order_by = " ORDER BY e.pages_id, ec.pages_title";
                    break;
                  default:
                    $ez_order_by = " ORDER BY e.toc_chapter, e.toc_sort_order, ec.pages_title";
                    break;
                }

                $pages_query_raw = "SELECT e.*, ec.*
                                    FROM " . TABLE_EZPAGES . " e,
                                         " . TABLE_EZPAGES_CONTENT . " ec
                                    WHERE e.pages_id = ec.pages_id
                                    AND ec.languages_id = " . (int)$_SESSION['languages_id'] .
                                    $ez_order_by;

// Split Page
// reset page when page is unknown
                if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['ezID'])) {
                  $check_page = $db->Execute($pages_query_raw);
                  $check_count = 1;
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
                    <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?php echo zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $page['pages_id']); ?>'" role="button">
                        <?php
                      } else {
                        ?>
                    <tr class="dataTableRow" onclick="document.location.href='<?php echo zen_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $page['pages_id']); ?>'" role="button">
                        <?php
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
                    <td class="dataTableContent text-center"><?php echo ($page['status_visible'] == 1 ? '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_visible&current=' . $page['status_visible'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_visible&current=' . $page['status_visible'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                    <td class="dataTableContent text-right"><?php echo $page['toc_sort_order'] . '&nbsp;' . ($page['status_toc'] == 1 ? '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_toc&current=' . $page['status_toc'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_toc&current=' . $page['status_toc'] . '&ezID=' . $page['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
<?php
                    // -----
                    // Give a watching observer the chance to insert another icon/link to the standard list of 'action' icons.
                    //
                    $extra_action_icons = '';
                    $zco_notifier->notify('NOTIFY_ADMIN_EZPAGES_EXTRA_ACTION_ICONS', $page, $extra_action_icons);
?>
                    <td class="dataTableContent text-center"><?php echo '<a href="' . zen_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($ezInfo) && is_object($ezInfo) && ($page['pages_id'] == $ezInfo->pages_id)) ? 'ezID=' . $page['pages_id'] . '&action=new' : '') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>' . $extra_action_icons; ?>
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
                    if ($ezInfo->pages_html_text != '' && strlen(trim($ezInfo->pages_html_text)) > 6) {
                      $zv_link_method_cnt++;
                    }

                    if ($zv_link_method_cnt > 1) {
                      $contents[] = array('text' => zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED_EZPAGES, 10, 10) . ' &nbsp;' . TEXT_WARNING_MULTIPLE_SETTINGS);
                    }

                    $contents[] = array('text' => TEXT_ALT_URL . (empty($ezInfo->alt_url) ? '&nbsp;' . TEXT_NONE : '<br>' . $ezInfo->alt_url));
                    $contents[] = array('text' => '<br>' . TEXT_ALT_URL_EXTERNAL . (empty($ezInfo->alt_url_external) ? '&nbsp;' . TEXT_NONE : '<br>' . $ezInfo->alt_url_external));
                    $ez_content = strip_tags($ezInfo->pages_html_text);
                    $ez_sub_content = zen_trunc_string($ez_content, MAX_PREVIEW); 
                    $contents[] = array('text' => '<br>' . TEXT_PAGES_HTML_TEXT . '<br>' . $ez_sub_content);

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
