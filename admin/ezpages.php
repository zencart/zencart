<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Jun 30 2014 Modified in v1.5.4 $
 */

// Sets the status of a page
  function zen_set_ezpage_status($pages_id, $status, $status_field) {
  global $db;
    if ($status == '1') {
      zen_record_admin_activity('EZ-Page ID ' . (int)$pages_id . ' [' . $status_field . '] changed to 0', 'info');
      $zco_notifier->notify('ADMIN_EZPAGES_STATUS_CHANGE', (int)$pages_id, $status_field, 0);
      return $db->Execute("update " . TABLE_EZPAGES . " set " . zen_db_input($status_field) . " = '0'  where pages_id = '" . (int)$pages_id . "'");
    } elseif ($status == '0') {
      zen_record_admin_activity('EZ-Page ID ' . (int)$pages_id . ' [' . $status_field . '] changed to 1', 'info');
      $zco_notifier->notify('ADMIN_EZPAGES_STATUS_CHANGE', (int)$pages_id, $status_field, 1);
      return $db->Execute("update " . TABLE_EZPAGES . " set " . zen_db_input($status_field) . " = '1'  where pages_id = '" . (int)$pages_id . "'");
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
    $action='';
    zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN));
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (zen_not_null($action)) {
    switch ($action) {
      case 'set_ez_sort_order':
        $_SESSION['ez_sort_order'] = $_GET['reset_ez_sort_order'];
        $action='';
        zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . ($_GET['ezID'] != '' ? '&ezID=' . $_GET['ezID'] : '')));
        break;
      case 'page_open_new_window':
        zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'page_open_new_window');
        $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
        zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
        break;
      case 'page_is_ssl':
        zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'page_is_ssl');
        $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
        zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
        break;
      case 'status_header':
        zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'status_header');
        $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
        zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
        break;
      case 'status_sidebox':
        zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'status_sidebox');
        $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
        zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
        break;
      case 'status_footer':
        zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'status_footer');
        $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
        zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
        break;
      case 'status_toc':
        zen_set_ezpage_status(zen_db_prepare_input($_GET['ezID']), zen_db_prepare_input($_GET['current']), 'status_toc');
        $messageStack->add(SUCCESS_PAGE_STATUS_UPDATED, 'success');
        zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']));
        break;
      case 'insert':
      case 'update':
        if (isset($_POST['pages_id'])) $pages_id = zen_db_prepare_input($_POST['pages_id']);
        $pages_title = zen_db_prepare_input($_POST['pages_title']);
        $page_open_new_window = (int)$_POST['page_open_new_window'];
        $page_is_ssl  = (int)$_POST['page_is_ssl'];

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
        if ($alt_url !='') {
          $zv_link_method_cnt++;
        }
        if ($alt_url_external !='') {
          $zv_link_method_cnt++;
        }
        if ($pages_html_text !='' and strlen(trim($pages_html_text)) > 6) {
          $zv_link_method_cnt++;
        }
        if ($zv_link_method_cnt > 1) {
          $messageStack->add(ERROR_MULTIPLE_HTML_URL, 'error');
          $page_error = true;
        }

        if ($page_error == false) {
          $sql_data_array = array('pages_title' => $pages_title,
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
            $zco_notifier->notify('ADMIN_EZPAGES_PAGE_ADDED', (int)$pages_id);
          } elseif ($action == 'update') {
            zen_db_perform(TABLE_EZPAGES, $sql_data_array, 'update', "pages_id = '" . (int)$pages_id . "'");
            $messageStack->add(SUCCESS_PAGE_UPDATED, 'success');
            zen_record_admin_activity('EZ-Page with ID ' . (int)$pages_id . ' updated.', 'info');
            $zco_notifier->notify('ADMIN_EZPAGES_PAGE_UPDATED', (int)$pages_id);
          }

          zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'ezID=' . $pages_id));
        } else {
          if ($page_error == false) {
            $action = 'new';
          } else {
            $_GET['pages_id'] = $pages_id;
            $_GET['ezID'] = $pages_id;
            $_GET['action'] = 'new';
            $action = 'new';
            $ezID = $pages_id;
            $page = $_GET['page'];
          }
        }
        break;
      case 'deleteconfirm':
        $pages_id = zen_db_prepare_input($_POST['ezID']);
        $db->Execute("delete from " . TABLE_EZPAGES . " where pages_id = '" . (int)$pages_id . "'");
        $messageStack->add(SUCCESS_PAGE_REMOVED, 'success');
        zen_record_admin_activity('EZ-Page with ID ' . (int)$pages_id . ' deleted.', 'notice');
        $zco_notifier->notify('ADMIN_EZPAGES_PAGE_DELETED', (int)$pages_id);
        zen_redirect(zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page']));
        break;
    }
  }
require('includes/admin_html_head.php');
?>
<?php if ($editor_handler != '') include ($editor_handler); ?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
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
            <td class="pageHeading"><?php echo HEADING_TITLE . ' ' . ($ezID != '' ? TEXT_INFO_PAGES_ID . $ezID : TEXT_INFO_PAGES_ID_SELECT); ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="main">
<?php
      if ($action != 'new') {
// toggle switch for display sort order
        $ez_sort_order_array = array(array('id' => '0', 'text' => TEXT_SORT_CHAPTER_TOC_TITLE),
                              array('id' => '1', 'text' => TEXT_SORT_HEADER_TITLE),
                              array('id' => '2', 'text' => TEXT_SORT_SIDEBOX_TITLE),
                              array('id' => '3', 'text' => TEXT_SORT_FOOTER_TITLE),
                              array('id' => '4', 'text' => TEXT_SORT_PAGE_TITLE),
                              array('id' => '5', 'text' => TEXT_SORT_PAGE_ID_TITLE)
                              );
        echo TEXT_SORT_CHAPTER_TOC_TITLE_INFO . zen_draw_form('set_ez_sort_order_form', FILENAME_EZPAGES_ADMIN, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_ez_sort_order', $ez_sort_order_array, $reset_ez_sort_order, 'onChange="this.form.submit();"') . zen_hide_session_id() .
        ($_GET['page'] != '' ? zen_draw_hidden_field('page', $_GET['page']) : '') .
        zen_draw_hidden_field('action', 'set_ez_sort_order') .
        '</form>';
?>
            </td>
            <td class="main">
<?php
// toggle switch for editor
        echo TEXT_EDITOR_INFO . zen_draw_form('set_editor_form', FILENAME_EZPAGES_ADMIN, '', 'get') . '&nbsp;&nbsp;' . zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();"') .
        zen_hide_session_id() .
        zen_draw_hidden_field('action', 'set_editor') .
        '</form>';
      }
?>
          </td>
          </tr>
        </table></td>
      </tr>
<?php
  if ($action == 'new') {
    $form_action = 'insert';

    $parameters = array('pages_title' => '',
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
                        'status_toc' => ''
                        );

    $ezInfo = new objectInfo($parameters);

    if (isset($_GET['ezID'])) {
      $form_action = 'update';

      $ezID = zen_db_prepare_input($_GET['ezID']);

      $page_query = "select * from " . TABLE_EZPAGES . " where pages_id = '" . (int)$_GET['ezID'] . "'";
      $page = $db->Execute($page_query);
      $ezInfo->updateObjectInfo($page->fields);
    } elseif (zen_not_null($_POST)) {
      $ezInfo->updateObjectInfo($_POST);
    }

// set all status settings and switches
    if (!isset($ezInfo->status_header)) $ezInfo->status_header = '1';
    switch ($ezInfo->status_header) {
      case '0': $is_status_header = false; $not_status_header = true; break;
      case '1': $is_status_header = true; $not_status_header = false; break;
      default: $is_status_header = true; $not_status_header = false; break;
    }
    if (!isset($ezInfo->status_sidebox)) $ezInfo->status_sidebox = '1';
    switch ($ezInfo->status_sidebox) {
      case '0': $is_status_sidebox = false; $not_status_sidebox = true; break;
      case '1': $is_status_sidebox = true; $not_status_sidebox = false; break;
      default: $is_status_sidebox = true; $not_status_sidebox = false; break;
    }
    if (!isset($ezInfo->status_footer)) $ezInfo->status_footer = '1';
    switch ($ezInfo->status_footer) {
      case '0': $is_status_footer = false; $not_status_footer = true; break;
      case '1': $is_status_footer = true; $not_status_footer = false; break;
      default: $is_status_footer = true; $not_status_footer = false; break;
    }
    if (!isset($ezInfo->status_toc)) $ezInfo->status_toc = '1';
    switch ($ezInfo->status_toc) {
      case '0': $is_status_toc = false; $not_status_toc = true; break;
      case '1': $is_status_toc = true; $not_status_toc = false; break;
      default: $is_status_toc = true; $not_status_toc = false; break;
    }
    if (!isset($ezInfo->page_open_new_window)) $ezInfo->not_page_open_new_window = '1';
    switch ($ezInfo->page_open_new_window) {
      case '0': $is_page_open_new_window = false; $not_page_open_new_window = true; break;
      case '1': $is_page_open_new_window = true; $not_page_open_new_window = false; break;
      default: $is_page_open_new_window = false; $not_page_open_new_window = true; break;
    }
    if (!isset($ezInfo->page_is_ssl)) $ezInfo->page_is_ssl = '1';
    switch ($ezInfo->page_is_ssl) {
      case '0': $is_page_is_ssl = false; $not_page_is_ssl = true; break;
      case '1': $is_page_is_ssl = true; $not_page_is_ssl = false; break;
      default: $is_page_is_ssl = false; $not_page_is_ssl = true; break;
    }
?>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
<?php
    echo zen_draw_form('new_page', FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . zen_db_prepare_input($_GET['page']) . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"');
    if ($form_action == 'update') echo zen_draw_hidden_field('pages_id', $ezID);
 ?>

      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="2" class="main" align="left" valign="top" nowrap><?php echo (($form_action == 'insert') ? zen_image_submit('button_insert.gif', IMAGE_INSERT) : zen_image_submit('button_update.gif', IMAGE_UPDATE)). '&nbsp;&nbsp;<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['ezID']) ? 'ezID=' . $_GET['ezID'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_PAGES_TITLE; ?></td>
            <td class="main"><?php echo zen_draw_input_field('pages_title', htmlspecialchars($ezInfo->pages_title, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_EZPAGES, 'pages_title'), true); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>

          <tr>
            <td colspan="2"><table border="0" cellspacing="4" cellpadding="4">
              <tr>
                <td class="main" align="left" valign="top"><strong>
                <?php echo TABLE_HEADING_PAGE_OPEN_NEW_WINDOW; ?></strong><br />
                  <?php echo zen_draw_radio_field('page_open_new_window', '1', $is_page_open_new_window) . '&nbsp;' . TEXT_YES . '<br />' . zen_draw_radio_field('page_open_new_window', '0', $not_page_open_new_window) . '&nbsp;' . TEXT_NO; ?>
                </td>
                <td class="main" align="left" valign="top"><strong>
                <?php echo TABLE_HEADING_PAGE_IS_SSL; ?></strong><br />
                  <?php echo zen_draw_radio_field('page_is_ssl', '1', $is_page_is_ssl) . '&nbsp;' . TEXT_YES . '<br />' . zen_draw_radio_field('page_is_ssl', '0', $not_page_is_ssl) . '&nbsp;' . TEXT_NO; ?>
                </td>
              </tr>
            </table></td>
          </tr>

          <tr>
            <td colspan="2"><table border="0" cellspacing="4" cellpadding="4">
              <tr>
                <td class="main" align="left" valign="top"><strong>
                <?php echo TABLE_HEADING_STATUS_HEADER; ?></strong><br />
                  <?php echo zen_draw_radio_field('status_header', '1', $is_status_header) . '&nbsp;' . TEXT_YES . '<br />' . zen_draw_radio_field('status_header', '0', $not_status_header) . '&nbsp;' . TEXT_NO; ?>
                </td>
                <td class="main" align="center" valign="bottom">
                <?php echo TEXT_HEADER_SORT_ORDER; ?><br />
                  <?php echo zen_draw_input_field('header_sort_order', $ezInfo->header_sort_order, zen_set_field_length(TABLE_EZPAGES, 'header_sort_order'), false); ?>
                </td>
                <td align="center">&nbsp;<?php echo zen_draw_separator('pixel_black.gif', '2', '50'); ?>&nbsp;</td>

                <td class="main" align="left" valign="top"><strong>
                <?php echo TABLE_HEADING_STATUS_SIDEBOX; ?></strong><br />
                  <?php echo zen_draw_radio_field('status_sidebox', '1', $is_status_sidebox) . '&nbsp;' . TEXT_YES . '<br />' . zen_draw_radio_field('status_sidebox', '0', $not_status_sidebox) . '&nbsp;' . TEXT_NO; ?>
                </td>
                <td class="main" align="center" valign="bottom">
                <?php echo TEXT_SIDEBOX_SORT_ORDER; ?><br />
                  <?php echo zen_draw_input_field('sidebox_sort_order', $ezInfo->sidebox_sort_order, zen_set_field_length(TABLE_EZPAGES, 'sidebox_sort_order'), false); ?>
                </td>
                <td align="center">&nbsp;<?php echo zen_draw_separator('pixel_black.gif', '2', '50'); ?>&nbsp;</td>

                <td class="main" align="left" valign="top"><strong>
                <?php echo TABLE_HEADING_STATUS_FOOTER; ?></strong><br />
                  <?php echo zen_draw_radio_field('status_footer', '1', $is_status_footer) . '&nbsp;' . TEXT_YES . '<br />' . zen_draw_radio_field('status_footer', '0', $not_status_footer) . '&nbsp;' . TEXT_NO; ?>
                </td>
                <td class="main" align="center" valign="bottom">
                  <?php echo TEXT_FOOTER_SORT_ORDER; ?><br />
                  <?php echo zen_draw_input_field('footer_sort_order', $ezInfo->footer_sort_order, zen_set_field_length(TABLE_EZPAGES, 'footer_sort_order'), false); ?>
                </td>
                <td align="center">&nbsp;<?php echo zen_draw_separator('pixel_black.gif', '2', '50'); ?>&nbsp;</td>

                <td class="main" align="left" valign="top"><strong>
                <?php echo TABLE_HEADING_CHAPTER_PREV_NEXT; ?></strong>
                <?php echo zen_draw_input_field('toc_chapter', $ezInfo->toc_chapter, zen_set_field_length(TABLE_EZPAGES, 'toc_chapter', '6'), false); ?>
                </td>

                <td class="main" align="left" valign="top"><strong>
                <?php echo TABLE_HEADING_STATUS_TOC; ?></strong><br />
                  <?php echo zen_draw_radio_field('status_toc', '1', $is_status_toc) . '&nbsp;' . TEXT_YES . '<br />' . zen_draw_radio_field('status_toc', '0', $not_status_toc) . '&nbsp;' . TEXT_NO; ?>
                </td>
                <td class="main" align="center" valign="bottom">
                  <?php echo TEXT_TOC_SORT_ORDER; ?><br />
                  <?php echo zen_draw_input_field('toc_sort_order', $ezInfo->toc_sort_order, zen_set_field_length(TABLE_EZPAGES, 'toc_sort_order'), false); ?>
                </td>

              </tr>
            </table></td>
          </tr>
              <tr>
                <td class="main" colspan="2">
                  <?php echo TEXT_HEADER_SORT_ORDER_EXPLAIN . '<br />'; ?>
                  <?php echo TEXT_SIDEBOX_ORDER_EXPLAIN . '<br />'; ?>
                  <?php echo TEXT_FOOTER_ORDER_EXPLAIN . '<br />'; ?>
                  <?php echo TEXT_TOC_SORT_ORDER_EXPLAIN . '<br />'; ?>
                  <?php echo TEXT_CHAPTER_EXPLAIN; ?>
                </td>
              </tr>


          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td valign="top" class="main"><?php echo TEXT_PAGES_HTML_TEXT; ?></td>
            <td class="main"><?php echo zen_draw_textarea_field('pages_html_text', 'soft', '100%', '40', htmlspecialchars($ezInfo->pages_html_text, ENT_COMPAT, CHARSET, TRUE), 'class="editorHook"');?></td>
          </tr>

          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>

          <tr>
            <td class="main" valign="top"><?php echo TEXT_ALT_URL; ?></td>
            <td class="main" valign="top"><?php echo zen_draw_input_field('alt_url', $ezInfo->alt_url, 'size="100"');
                      echo '<br />' . TEXT_ALT_URL_EXPLAIN;
                ?></td>
          </tr>

          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>

          <tr>
            <td class="main" valign="top"><?php echo TEXT_ALT_URL_EXTERNAL; ?></td>
            <td class="main" valign="top"><?php echo zen_draw_input_field('alt_url_external', $ezInfo->alt_url_external, 'size="100"');
                      echo '<br />' . TEXT_ALT_URL_EXTERNAL_EXPLAIN;
                ?></td>
          </tr>

          <tr>
            <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>

        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="2" class="main" align="left" valign="top" nowrap><?php echo (($form_action == 'insert') ? zen_image_submit('button_insert.gif', IMAGE_INSERT) : zen_image_submit('button_update.gif', IMAGE_UPDATE)). '&nbsp;&nbsp;<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['ezID']) ? 'ezID=' . $_GET['ezID'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
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
              <tr class="dataTableHeadingRow" width="100%">
                <td class="dataTableHeadingContent" width="75px" align="center"><?php echo TABLE_HEADING_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PAGES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PAGE_OPEN_NEW_WINDOW; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PAGE_IS_SSL; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS_HEADER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS_SIDEBOX; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS_FOOTER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CHAPTER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS_TOC; ?></td>
                <td class="dataTableHeadingContent" align="center">&nbsp;</td>
              </tr>

<?php
// set display order
  switch(true) {
    case ($_SESSION['ez_sort_order'] == 0):
      $ez_order_by =  " order by toc_chapter, toc_sort_order, pages_title";
      break;
    case ($_SESSION['ez_sort_order'] == 1):
      $ez_order_by =  " order by header_sort_order, pages_title";
      break;
    case ($_SESSION['ez_sort_order'] == 2):
      $ez_order_by =  " order by sidebox_sort_order, pages_title";
      break;
    case ($_SESSION['ez_sort_order'] == 3):
      $ez_order_by =  " order by footer_sort_order, pages_title";
      break;
    case ($_SESSION['ez_sort_order'] == 4):
      $ez_order_by =  " order by pages_title";
      break;
    case ($_SESSION['ez_sort_order'] == 5):
      $ez_order_by =  " order by  pages_id, pages_title";
      break;
    default:
      $ez_order_by =  " order by toc_chapter, toc_sort_order, pages_title";
      break;
  }

    $pages_query_raw = "select * from " . TABLE_EZPAGES . $ez_order_by;

// Split Page
// reset page when page is unknown
if (($_GET['page'] == '' or $_GET['page'] == '1') and $_GET['ezID'] != '') {
  $check_page = $db->Execute($pages_query_raw);
  $check_count=1;
  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_EZPAGE) {
    while (!$check_page->EOF) {
      if ($check_page->fields['customers_id'] == $_GET['cID']) {
        break;
      }
      $check_count++;
      $check_page->MoveNext();
    }
    $_GET['page'] = round((($check_count/MAX_DISPLAY_SEARCH_RESULTS_EZPAGE)+(fmod_round($check_count,MAX_DISPLAY_SEARCH_RESULTS_EZPAGE) !=0 ? .5 : 0)),0);
  } else {
    $_GET['page'] = 1;
  }
}

    $pages_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_EZPAGE, $pages_query_raw, $pages_query_numrows);
    $pages = $db->Execute($pages_query_raw);

while (!$pages->EOF) {
     if ((!isset($_GET['ezID']) || (isset($_GET['ezID']) && ($_GET['ezID'] == $pages->fields['pages_id']))) && !isset($ezInfo) && (substr($action, 0, 3) != 'new')) {
        $ezInfo_array = $pages->fields;
        $ezInfo = new objectInfo($ezInfo_array);
      }
    $zv_link_method_cnt = 0;
    if ($pages->fields['alt_url'] !='') {
      $zv_link_method_cnt++;
    }
    if ($pages->fields['alt_url_external'] !='') {
      $zv_link_method_cnt++;
    }
    if ($pages->fields['pages_html_text'] !='' and strlen(trim($pages->fields['pages_html_text'])) > 6) {
      $zv_link_method_cnt++;
    }
      if (isset($ezInfo) && is_object($ezInfo) && ($pages->fields['pages_id'] == $ezInfo->pages_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $pages->fields['pages_id']) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $pages->fields['pages_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent" width="75px" align="right"><?php echo ($zv_link_method_cnt > 1 ? zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED_EZPAGES, 10, 10) : '') . '&nbsp;' . $pages->fields['pages_id']; ?></td>
                <td class="dataTableContent"><?php echo '&nbsp;' . $pages->fields['pages_title']; ?></td>
                <td class="dataTableContent" align="center"><?php echo ($pages->fields['page_open_new_window'] == 1 ? '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=page_open_new_window&current=' . $pages->fields['page_open_new_window'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=page_open_new_window&current=' . $pages->fields['page_open_new_window'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent" align="center"><?php echo ($pages->fields['page_is_ssl'] == 1 ? '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=page_is_ssl&current=' . $pages->fields['page_is_ssl'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=page_is_ssl&current=' . $pages->fields['page_is_ssl'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent" align="right"><?php echo $pages->fields['header_sort_order'] . '&nbsp;' . ($pages->fields['status_header'] == 1 ? '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_header&current=' . $pages->fields['status_header'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_header&current=' . $pages->fields['status_header'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent" align="right"><?php echo $pages->fields['sidebox_sort_order'] . '&nbsp;' . ($pages->fields['status_sidebox'] == 1 ? '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_sidebox&current=' . $pages->fields['status_sidebox'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_sidebox&current=' . $pages->fields['status_sidebox'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent" align="right"><?php echo $pages->fields['footer_sort_order'] . '&nbsp;' . ($pages->fields['status_footer'] == 1 ? '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_footer&current=' . $pages->fields['status_footer'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_footer&current=' . $pages->fields['status_footer'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent" align="right"><?php echo $pages->fields['toc_chapter']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $pages->fields['toc_sort_order'] . '&nbsp;' . ($pages->fields['status_toc'] == 1 ? '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_toc&current=' . $pages->fields['status_toc'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=status_toc&current=' . $pages->fields['status_toc'] . '&ezID=' . $pages->fields['pages_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
                <td class="dataTableContent" align="center">&nbsp;&nbsp;<?php echo '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN,
(isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($ezInfo) && is_object($ezInfo) && ($pages->fields['pages_id'] == $ezInfo->pages_id)) ? 'ezID=' . $pages->fields['pages_id'] . '&action=new' : '') . '">' . zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT) . '</a>'; ?><?php if (isset($ezInfo) && is_object($ezInfo) && ($pages->fields['pages_id'] == $ezInfo->pages_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($pages->fields['pages_id']) ? 'ezID=' . $pages->fields['pages_id'] : '')) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?></td>
              </tr>
<?php

 $pages->MoveNext();
    }
?>
                  <tr>

                    <td class="smallText" valign="top" colspan="2"><?php echo $pages_split->display_count($pages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_EZPAGE, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PAGES); ?></td>
                    <td class="smallText" align="right" colspan="8"><?php echo $pages_split->display_links($pages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_EZPAGE, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y', 'ezID'))); ?></td>
                  </tr>

              <tr>
                <td colspan="10"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'action=new') . '">' . zen_image_button('button_new_file.gif', IMAGE_NEW_PAGE) . '</a>'; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<b>' . $ezInfo->pages_title . '</b>');

      $contents = array('form' => zen_draw_form('pages', FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('ezID', $ezInfo->pages_id));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $ezInfo->pages_title . '</b>');

      $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $_GET['ezID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($ezInfo)) {
        $heading[] = array('text' => '<b>' . TEXT_PAGE_TITLE . '&nbsp;' . $ezInfo->pages_title . '&nbsp;|&nbsp;' . TEXT_CHAPTER . '&nbsp;' . $ezInfo->toc_chapter . '</b>');

        $zv_link_method_cnt = 0;
        if ($ezInfo->alt_url !='') {
          $zv_link_method_cnt++;
        }
        if ($ezInfo->alt_url_external !='') {
          $zv_link_method_cnt++;
        }
        if ($ezInfo->pages_html_text !='' and strlen(trim($ezInfo->pages_html_text)) > 6) {
          $zv_link_method_cnt++;
        }

        if ($zv_link_method_cnt > 1) {
          $contents[] = array('align' => 'left', 'text' => zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED_EZPAGES, 10, 10) . ' &nbsp;' . TEXT_WARNING_MULTIPLE_SETTINGS);
        }

        $contents[] = array('align' => 'left', 'text' => TEXT_ALT_URL . (empty($ezInfo->alt_url) ? '&nbsp;' . TEXT_NONE : '<br />' . $ezInfo->alt_url));
        $contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_ALT_URL_EXTERNAL . (empty($ezInfo->alt_url_external) ? '&nbsp;' . TEXT_NONE : '<br />' . $ezInfo->alt_url_external));
        $contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_PAGES_HTML_TEXT . '<br />' . substr(strip_tags($ezInfo->pages_html_text),0,100));

        $contents[] = array('align' => 'left', 'text' => '<br /><a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $ezInfo->pages_id . '&action=new') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_admin_href_link(FILENAME_EZPAGES_ADMIN, 'page=' . $_GET['page'] . '&ezID=' . $ezInfo->pages_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a><br /><br /><br />');

        if ($ezInfo->date_scheduled) $contents[] = array('text' => '<br />' . sprintf(TEXT_PAGES_SCHEDULED_AT_DATE, zen_date_short($ezInfo->date_scheduled)));

        if ($ezInfo->expires_date) {
          $contents[] = array('text' => '<br />' . sprintf(TEXT_PAGES_EXPIRES_AT_DATE, zen_date_short($ezInfo->expires_date)));
        } elseif ($ezInfo->expires_impressions) {
          $contents[] = array('text' => '<br />' . sprintf(TEXT_PAGES_EXPIRES_AT_IMPRESSIONS, $ezInfo->expires_impressions));
        }

        if ($ezInfo->date_status_change) $contents[] = array('text' => '<br />' . sprintf(TEXT_PAGES_STATUS_CHANGE, zen_date_short($ezInfo->date_status_change)));
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
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
