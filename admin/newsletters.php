<?php
/**
 * @package admin
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2019 May 08 Modified in v1.5.6b $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (zen_not_null($action)) {

  switch ($action) {
    case 'set_editor':
      // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
      $action = '';
      zen_redirect(zen_href_link(FILENAME_NEWSLETTERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'nID=' . $newsletter_id));
      break;
    case 'insert':
    case 'update':
      if (isset($_POST['newsletter_id'])) {
        $newsletter_id = zen_db_prepare_input($_POST['newsletter_id']);
      }
      $newsletter_module = zen_db_prepare_input($_POST['module']);
      $title = zen_db_prepare_input($_POST['title']);
      $content = zen_db_prepare_input($_POST['content']);
      $content_html = zen_db_prepare_input($_POST['message_html']);

      $newsletter_error = false;
      if (empty($title)) {
        $messageStack->add(ERROR_NEWSLETTER_TITLE, 'error');
        $newsletter_error = true;
      }

      if (empty($newsletter_module)) {
        $messageStack->add(ERROR_NEWSLETTER_MODULE, 'error');
        $newsletter_error = true;
      }

      if ($newsletter_error == false) {
        $sql_data_array = array(
          'title' => $title,
          'content' => $content,
          'content_html' => $content_html,
          'module' => $newsletter_module);

        if ($action == 'insert') {
          $sql_data_array['date_added'] = 'now()';
          $sql_data_array['status'] = '0';

          zen_db_perform(TABLE_NEWSLETTERS, $sql_data_array);
          $newsletter_id = zen_db_insert_id();
        } elseif ($action == 'update') {
          zen_db_perform(TABLE_NEWSLETTERS, $sql_data_array, 'update', "newsletters_id = " . (int)$newsletter_id);
        }

        zen_redirect(zen_href_link(FILENAME_NEWSLETTERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'nID=' . $newsletter_id));
      } else {
        $action = 'new';
      }
      break;
    case 'deleteconfirm':
      $newsletter_id = zen_db_prepare_input($_POST['nID']);

      $db->Execute("DELETE FROM " . TABLE_NEWSLETTERS . " WHERE newsletters_id = " . (int)$newsletter_id);

      zen_redirect(zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page']));
      break;
    case 'delete':
    case 'new':
      if (!isset($_GET['nID'])) {
        break;
      }
    case 'send':
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
    <script>
      var form = "";
      var submitted = false;
      var error = false;
      var error_message = "";

      function check_select(field_name, field_default, message) {
          if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
              var field_value = form.elements[field_name].value;

              if (field_value == field_default) {
                  error_message = error_message + "* " + message + "\n";
                  error = true;
              }
          }
      }

      function check_message(msg) {
          if (form.elements['content'] && form.elements['message_html']) {
              var field_value1 = form.elements['content'].value;
              var field_value2 = form.elements['message_html'].value;

              if ((field_value1 == '' || field_value1.length < 3) && (field_value2 == '' || field_value2.length < 3)) {
                  error_message = error_message + "* " + msg + "\n";
                  error = true;
              }
          }
      }

      function check_form(form_name) {
          if (submitted == true) {
              alert("<?php echo JS_ERROR_SUBMITTED; ?>");
              return false;
          }
          error = false;
          form = form_name;
          error_message = "<?php echo JS_ERROR; ?>";

          //  check_message("<?php echo ENTRY_NOTHING_TO_SEND; ?>");
          check_select('audience_selected', '', "<?php echo ERROR_PLEASE_SELECT_AUDIENCE; ?>");
          if (error == true) {
              alert(error_message);
              return false;
          } else {
              submitted = true;
              return true;
          }
      }
    </script>
    <?php if ($editor_handler != '') include ($editor_handler); ?>
  </head>
  <body onLoad="init()">
    <div id="spiffycalendar" class="text"></div>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->

      <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
      <div class="row">
        <div class="col-sm-offset-6 col-sm-6">
            <?php if ($action == 'new') { ?>
            <!-- // toggle switch for editor -->
            <?php echo zen_draw_form('set_editor_form', FILENAME_NEWSLETTERS, '', 'get', 'class="form-horizontal"'); ?>
            <?php echo zen_hide_session_id(); ?>
            <?php echo zen_draw_hidden_field('action', 'set_editor'); ?>
            <?php echo zen_draw_label(TEXT_EDITOR_INFO, 'reset_editor', 'class="control-label col-sm-6"'); ?>
            <div class="col-sm-6">
                <?php echo zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();" class="form-control"'); ?>
            </div>
            <?php echo '</form>'; ?>
          <?php } ?>
        </div>
      </div>
      <!-- body_text //-->

      <?php
      if ($action == 'new') {
        $form_action = 'insert';

        $parameters = array(
          'title' => '',
          'content' => '',
          'content_html' => '',
          'module' => '');

        $nInfo = new objectInfo($parameters);

        if (isset($_GET['nID'])) {
          $form_action = 'update';

          $nID = zen_db_prepare_input($_GET['nID']);


          $newsletter = $db->Execute("SELECT title, content, content_html, module
                                      FROM " . TABLE_NEWSLETTERS . "
                                      WHERE newsletters_id = " . (int)$nID);

          $nInfo->updateObjectInfo($newsletter->fields);
        } elseif ($_POST) {
          $nInfo->updateObjectInfo($_POST);
        }

        $directory_array = array();
        if ($dir = dir(DIR_WS_MODULES . 'newsletters/')) {
          while ($file = $dir->read()) {
            if (!is_dir(DIR_WS_MODULES . 'newsletters/' . $file)) {
              if (preg_match('~^[^\._].*\.php$~i', $file) > 0) {
                $directory_array[] = $file;
              }
            }
          }
          sort($directory_array);
          $dir->close();
        }

        for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
          $modules_array[] = array(
            'id' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')),
            'text' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')));
        }
        ?>
        <?php
        echo zen_draw_form('newsletter', FILENAME_NEWSLETTERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'action=' . $form_action, 'post', 'onsubmit="return check_form(newsletter);" class="form-horizontal"');
        if ($form_action == 'update') {
          echo zen_draw_hidden_field('newsletter_id', $nID);
        }
        ?>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_NEWSLETTER_MODULE, 'modules', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_pull_down_menu('module', $modules_array, $nInfo->module, 'class="form-control"'); ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_NEWSLETTER_TITLE, 'title', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('title', htmlspecialchars($nInfo->title, ENT_COMPAT, CHARSET, TRUE), 'size="50" class="form-control"', true); ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_NEWSLETTER_CONTENT_HTML, 'message_html', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_textarea_field('message_html', 'soft', '100%', '30', htmlspecialchars($nInfo->content_html, ENT_COMPAT, CHARSET, TRUE), 'id="message_html" class="editorHook form-control"'); ?>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_NEWSLETTER_CONTENT, 'content', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_textarea_field('content', 'soft', '100%', '20', htmlspecialchars($nInfo->content, ENT_COMPAT, CHARSET, TRUE), 'class="noEditor form-control"'); ?>
          </div>
        </div>
        <div class="main row text-right">
          <button type="submit" class="btn btn-primary"><?php echo (($form_action == 'insert') ? IMAGE_SAVE : IMAGE_UPDATE); ?></button>&nbsp;<a href="<?php echo zen_href_link(FILENAME_NEWSLETTERS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['nID']) ? 'nID=' . $_GET['nID'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
        </div>
        <?php
      } elseif ($action == 'preview') {
        $nID = zen_db_prepare_input($_GET['nID']);

        $newsletter = $db->Execute("SELECT title, content, content_html, module
                                    FROM " . TABLE_NEWSLETTERS . "
                                    WHERE newsletters_id = " . (int)$nID);

        $nInfo = new objectInfo($newsletter->fields);
        ?>
        <div class="row text-right">
          <a href="<?php echo zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a>
        </div>
        <div class="row"><?php echo zen_draw_separator(); ?></div>
        <div class="row">
          <div class="col-sm-3"><?php echo zen_draw_label(strip_tags(TEXT_NEWSLETTER_CONTENT_HTML), '', 'class="control-label"'); ?></div>
          <div class="col-sm-9 col-md-6"><?php echo nl2br($nInfo->content_html); ?></div>
        </div>
        <div class="row">
          <div class="col-sm-3"><?php echo zen_draw_label(strip_tags(TEXT_NEWSLETTER_CONTENT), '', 'class="control-label"'); ?></div>
          <div class="col-sm-9 col-md-6"><tt><?php echo nl2br($nInfo->content); ?></tt></div>
        </div>
        <div class="row"><?php echo zen_draw_separator(); ?></div>
        <div class="row text-right">
          <a href="<?php echo zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a>
        </div>
        <?php
      } elseif ($action == 'send') {
        $nID = zen_db_prepare_input($_GET['nID']);

        $newsletter = $db->Execute("SELECT title, content, content_html, module
                                    FROM " . TABLE_NEWSLETTERS . "
                                    WHERE newsletters_id = " . (int)$nID);

        $nInfo = new objectInfo($newsletter->fields);

        include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
        include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
        $module_name = $nInfo->module;
        $module = new $module_name($nInfo->title, $nInfo->content, $nInfo->content_html);
        ?>
        <div class="row">
            <?php
            if ($module->show_choose_audience) {
              echo $module->choose_audience();
            } else {
              echo $module->confirm();
            }
            ?>
        </div>
        <?php
      } elseif ($action == 'confirm') { // show count of customers to receive messages, and preview of contents.
        $nID = zen_db_prepare_input($_GET['nID']);

        $newsletter = $db->Execute("SELECT title, content, content_html, module
                                    FROM " . TABLE_NEWSLETTERS . "
                                    WHERE newsletters_id = " . (int)$nID);

        $nInfo = new objectInfo($newsletter->fields);

        include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
        include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
        $module_name = $nInfo->module;
        $module = new $module_name($nInfo->title, $nInfo->content, $nInfo->content_html);
        ?>
        <?php echo $module->confirm(); ?>
        <?php
      } elseif ($action == 'confirm_send') { // confirmed, now go ahead and send the messages
        $nID = zen_db_prepare_input($_GET['nID']);

        $newsletter = $db->Execute("SELECT newsletters_id, title, content, content_html, module
                                    FROM " . TABLE_NEWSLETTERS . "
                                    WHERE newsletters_id = " . (int)$nID);

        $nInfo = new objectInfo($newsletter->fields);

        include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
        include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
        $module_name = $nInfo->module;
        $module = new $module_name($nInfo->title, $nInfo->content, $nInfo->content_html, $_POST['audience_selected']);
        ?>
        <div class="row">
          <div class="col-sm-12"><strong><?php echo TEXT_PLEASE_WAIT; ?></strong></div>
          <?php
          zen_set_time_limit(600);
          flush();
          $i = $module->send($nInfo->newsletters_id);
          ?>
        </div>
        <div class="row">
          <h1><span class="text-danger"><?php echo TEXT_FINISHED_SENDING_EMAILS; ?></span></h1>
        </div>
        <div class="row">
          <span class="text-danger"><?php echo sprintf(TEXT_AFTER_EMAIL_INSTRUCTIONS, $i); ?></span>
        </div>
        <div class="row text-center">
          <a href="<?php echo zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a>
        </div>
        <?php
      } else {
        ?>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-striped table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_NEWSLETTERS; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_SIZE; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_MODULE; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_SENT; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
              </thead>
              <tbody>
                  <?php
                  $newsletters_query_raw = "SELECT newsletters_id, title, length(content) AS content_length,
                                                   length(content_html) AS content_html_length, module, date_added, date_sent, status
                                            FROM " . TABLE_NEWSLETTERS . "
                                            ORDER BY date_added desc";
                  $newsletters_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $newsletters_query_raw, $newsletters_query_numrows);
                  $newsletters = $db->Execute($newsletters_query_raw);
                  foreach ($newsletters as $newsletter) {
                    if ((!isset($_GET['nID']) || (isset($_GET['nID']) && ($_GET['nID'] == $newsletter['newsletters_id']))) && !isset($nInfo) && (substr($action, 0, 3) != 'new')) {
                      $nInfo = new objectInfo($newsletter);
                    }

                    if (isset($nInfo) && is_object($nInfo) && ($newsletter['newsletters_id'] == $nInfo->newsletters_id)) {
                      ?>
                    <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview'); ?>'">
                      <?php } else { ?>
                    <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $newsletter['newsletters_id']); ?>'">
                      <?php } ?>
                    <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $newsletter['newsletters_id'] . '&action=preview') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $newsletter['title']; ?></td>
                    <td class="dataTableContent" align="right"><?php echo number_format($newsletter['content_length'] + $newsletter['content_html_length']) . ' bytes'; ?></td>
                    <td class="dataTableContent" align="right"><?php echo $newsletter['module']; ?></td>
                    <td class="dataTableContent" align="center"><?php
                        if ($newsletter['status'] == '1') {
                          echo zen_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK);
                        } else {
                          echo zen_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS);
                        }
                        ?></td>
                    <td class="dataTableContent" align="right"><?php
                        if (isset($nInfo) && is_object($nInfo) && ($newsletter['newsletters_id'] == $nInfo->newsletters_id)) {
                          echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', '');
                        } else {
                          echo '<a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $newsletter['newsletters_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                        }
                        ?>
                    </td>
                  </tr>
                  <?php } ?>
              </tbody>
            </table>
          </div>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
              <?php
              $heading = array();
              $contents = array();

              switch ($action) {
                case 'delete':
                  $heading[] = array('text' => '<h4>' . $nInfo->title . '</h4>');

                  $contents = array('form' => zen_draw_form('newsletters', FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('nID', $nInfo->newsletters_id));
                  $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                  $contents[] = array('text' => '<br><strong>' . $nInfo->title . '</strong>');
                  $contents[] = array('align' => 'center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                  break;
                default:
                  if (!empty($nInfo) && is_object($nInfo)) {
                    $heading[] = array('text' => '<h4>' . $nInfo->title . '</h4>');

                    $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=new') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a> <a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '" class="btn btn-primary" role="button">' . IMAGE_PREVIEW . '</a> <a href="' . zen_href_link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=send') . '" class="btn btn-primary" role="button">' . IMAGE_SEND . '</a>');

                    $contents[] = array('text' => '<br>' . TEXT_NEWSLETTER_DATE_ADDED . ' ' . zen_date_short($nInfo->date_added));
                    if ($nInfo->status == '1') {
                      $contents[] = array('text' => TEXT_NEWSLETTER_DATE_SENT . ' ' . zen_date_short($nInfo->date_sent));
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
              <td><?php echo $newsletters_split->display_count($newsletters_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_NEWSLETTERS); ?></td>
              <td class="text-right"><?php echo $newsletters_split->display_links($newsletters_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
            </tr>
            <tr>
              <td class="text-right" colspan="2"><a href="<?php echo zen_href_link(FILENAME_NEWSLETTERS, 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_NEWSLETTER; ?></a></td>
            </tr>
          </table>
        </div>
        <?php
      }
      ?>
      <!-- body_text_eof //-->

      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->

  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
