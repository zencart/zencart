<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Sep 08 Modified in v1.5.7a $
 */
require('includes/application_top.php');

function zen_display_files() {
  global $check_directory, $found, $configuration_key_lookup;
    for ($i = 0, $n = sizeof($check_directory); $i < $n; $i++) {

    $dir_check = $check_directory[$i];

    if ($dir = @dir($dir_check)) {
      while ($file = $dir->read()) {
        if (!is_dir($dir_check . $file)) {
          if (preg_match('~^[^\._].*\.php$~i', $file) > 0) {
            $directory_array[] = $file;
          }
        }
      }
        if (sizeof($directory_array)) {
        sort($directory_array);
      }
      $dir->close();
    }
  }
  return $directory_array;
}

$action = (isset($_GET['action']) ? $_GET['action'] : '');
if (isset($_GET['filename'])) {
  $_GET['filename'] = str_replace('../', '!HA' . 'CK' . 'ER_A' . 'LERT!', $_GET['filename']);
}

$za_who = isset($_GET['za_lookup']) ? $_GET['za_lookup'] : '';

if ($action == 'new_page') {
  $page = $_GET['define_it'];

  $check_directory = array();
  $check_directory[] = DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/';
  $directory_files = zen_display_files();

  $za_lookup = array();
    for ($i = 0, $n = sizeof($directory_files); $i < $n; $i++) {
    $za_lookup[] = array('id' => $i, 'text' => $directory_files[$i]);
  }

// This will cause it to look for 'define_conditions.php'
  $_GET['filename'] = $za_lookup[$page]['text'];
  $_GET['box_name'] = BOX_TOOLS_DEFINE_CONDITIONS;
}

// define template specific file name defines
$file = zen_get_file_directory(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/html_includes/', isset($_GET['filename']) ? $_GET['filename'] : '', 'false');
?>
<?php
if (empty($_GET['action'])) {
  $_GET['action'] = '';
}
switch ($_GET['action']) {
  case 'set_editor':
    // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
    $action = '';
    zen_redirect(zen_href_link(FILENAME_DEFINE_PAGES_EDITOR));
    break;
  case 'save':
    if (($_GET['lngdir']) && ($_GET['filename'])) {
      if (file_exists($file)) {
        if (file_exists('bak' . $file)) {
          @unlink('bak' . $file);
        }
        @rename($file, 'bak' . $file);
        $new_file = fopen($file, 'w');
        $file_contents = stripslashes($_POST['file_contents']);
        fwrite($new_file, $file_contents, strlen($file_contents));
        fclose($new_file);
      }
      zen_record_admin_activity('Define-Page-Editor was used to save changes to file ' . $file, 'info');
      zen_redirect(zen_href_link(FILENAME_DEFINE_PAGES_EDITOR));
    }
    break;
}

if (empty($_SESSION['language'])) {
  $_SESSION['language'] = $language;
}

$languages_array = array();
$languages = zen_get_languages();
$lng_exists = false;
for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
  if ($languages[$i]['directory'] == $_SESSION['language'])
    $lng_exists = true;

  $languages_array[] = array('id' => $languages[$i]['directory'],
    'text' => $languages[$i]['name']);
}
if (!$lng_exists) {
  $_SESSION['language'] = $language;
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
    <?php if ($editor_handler != '') include ($editor_handler); ?>
  </head>
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE . '&nbsp;' . $_SESSION['language']; ?></h1>
      <div class="row">
        <div class="col-sm-4 col-md-4">
            <?php
            $check_directory = array();
            $check_directory[] = DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/html_includes/';
            $directory_files = zen_display_files();

            $za_lookup = array();
            $za_lookup[] = array('id' => -1, 'text' => TEXT_INFO_SELECT_FILE);

            for ($i = 0, $n = sizeof($directory_files); $i < $n; $i++) {
              $za_lookup[] = array('id' => $i, 'text' => $directory_files[$i]);
            }

            echo zen_draw_form('new_page', FILENAME_DEFINE_PAGES_EDITOR, '', 'get');
            echo zen_draw_pull_down_menu('define_it', $za_lookup, '-1', 'onChange="this.form.submit();" class="form-control"');
            echo zen_hide_session_id();
            echo zen_draw_hidden_field('action', 'new_page');
            echo '</form>';
            ?>
        </div>
        <div class="col-sm-5 col-md-6">&nbsp;</div>
        <div class="col-sm-3 col-md-2">
            <?php
// toggle switch for editor
            echo zen_draw_form('set_editor_form', FILENAME_DEFINE_PAGES_EDITOR, '', 'get', 'class="form-horizontal"');
            echo zen_draw_label(TEXT_EDITOR_INFO, 'reset_editor', 'class="control-label"');
            echo zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onChange="this.form.submit();" class="form-control"');
            echo zen_draw_hidden_field('action', 'set_editor');
            echo zen_hide_session_id();
            echo '</form>';
            ?>
        </div>
      </div>
      <?php
// show editor
      if (isset($_GET['filename'])) {
        ?>
        <?php
        if ($_SESSION['language'] && $_GET['filename']) {
          if (file_exists($file)) {
            $file_contents = file_get_contents($file);

            $file_writeable = true;
            if (!is_writeable($file)) {
              $file_writeable = false;
              $messageStack->reset();
              $messageStack->add(sprintf(ERROR_FILE_NOT_WRITEABLE, $file), 'error');
              echo $messageStack->output();
            }
            ?>
            <div class="row"><strong><?php echo TEXT_INFO_CAUTION . '<br /><br />' . TEXT_INFO_EDITING . '<br />' . $file . '<br />'; ?></strong></div>
            <div class="row">
                <?php echo zen_draw_form('language', FILENAME_DEFINE_PAGES_EDITOR, 'lngdir=' . $_SESSION['language'] . '&filename=' . $_GET['filename'] . '&action=save'); ?>
              <div class="col-sm-6"><?php echo zen_draw_textarea_field('file_contents', 'soft', '100%', '30', htmlspecialchars($file_contents, ENT_COMPAT, CHARSET, TRUE), (($file_writeable) ? '' : 'readonly') . ' class="editorHook form-control"'); ?>
              </div>
              <div class="col-sm-6">&nbsp;</div>
              <div class="col-sm-12"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
              <div class="col-sm-6 text-right">
                  <?php
                  if ($file_writeable) {
                    ?>
                  <button type="submit" class="btn btn-primary"><?php echo IMAGE_SAVE; ?></button> <a href="<?php echo zen_href_link(FILENAME_DEFINE_PAGES_EDITOR, 'define_it=' . $_GET['define_it'] . '&action=new_page'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_RESET; ?></a> <a href="<?php echo zen_href_link(FILENAME_DEFINE_PAGES_EDITOR . '.php'); ?>" class="btn btn-default"><?php echo IMAGE_CANCEL; ?></a>
                  <?php
                } else {
                  ?>
                  <a href="<?php echo zen_href_link(FILENAME_DEFINE_PAGES_EDITOR, 'lngdir=' . $_SESSION['language']); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a>
                  <?php
                }
                ?>
              </div>
              <div class="col-sm-6">&nbsp;</div>
              <?php echo '</form>'; ?>
            </div>
            <?php
          } else {
            ?>
            <div class="row"><strong><?php echo sprintf(TEXT_FILE_DOES_NOT_EXIST, $file); ?></strong></div>
            <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
            <div class="row"><a href="<?php echo zen_href_link($_GET['filename'], 'lngdir=' . $_SESSION['language']); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a></div>
            <?php
          }
        } else {
          $filename = $_SESSION['language'] . '.php';
          ?>
          <div class="row">
            <table class="table">
              <tr>
                <td><a href="<?php echo zen_href_link($_GET['filename'], 'lngdir=' . $_SESSION['language'] . '&filename=' . $filename); ?>"><strong><?php echo $filename; ?></strong></a></td>
                      <?php
                      $dir = dir(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language']);
                      $left = false;
                      if ($dir) {
                        while ($file = $dir->read()) {
                          if (preg_match('~^[^\._].*\.php$~i', $file) > 0) {
                            echo '                <td class="smallText"><a href="' . zen_href_link($_GET['filename'], 'lngdir=' . $_SESSION['language'] . '&filename=' . $file) . '">' . $file . '</a></td>' . "\n";
                            if (!$left) {
                              echo '              </tr>' . "\n" .
                              '              <tr>' . "\n";
                            }
                            $left = !$left;
                          }
                        }
                        $dir->close();
                      }
                      ?>
              </tr>
            </table>
          </div>
          <?php
        }
        ?>
      <?php } // filename   ?>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
