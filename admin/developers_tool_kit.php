<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: proseLA 2023 Oct 24 Modified in v2.0.0-alpha1 $
 */
require('includes/application_top.php');

$default_context_lines = 0;
$output = '';
$outCount = '';

if (!empty($_POST) && !isset($_POST['context_lines'])) {
  $_POST['context_lines'] = abs((int)$default_context_lines);
}

if (!empty($_GET['configuration_key_lookup']))  {
  $_POST['configuration_key'] = strtoupper($_GET['configuration_key_lookup']);
  $_POST['zv_files'] = 1;
  $_POST['zv_filestype'] = !empty($_POST['zv_filestype']) ? $_POST['zv_filestype'] : 0;
  $_POST['case_sensitive'] = !empty($_POST['case_sensitive']) ? $_POST['case_sensitive'] : 0;
}
$configuration_key_lookup = $_POST['configuration_key'] ?? '';
$default_context_lines = (int)($_POST['context_lines'] ?? $default_context_lines);
$case_sensitive = !empty($_POST['case_sensitive']);
$include_plugins = !empty($_POST['include_plugins']);
$include_laravel = !empty($_POST['include_laravel']);
$q_const = $q_func = $q_class = $q_tpl = $q_all = '';

function getDirList($dirName, $filetypes = 1) {
  global $directory_array, $sub_dir_files;

  $dirName = str_replace('//', '/', $dirName);

  $excluded = [];
  $excluded[] = DIR_FS_CATALOG . 'includes/classes/vendors';
  $excluded[] = DIR_FS_CATALOG . 'zc_install';
  if (in_array(rtrim($dirName, '/'), $excluded)) {
      return $sub_dir_files;
  }

  // add directory name to the sub_dir_files list;
  $sub_dir_files[] = $dirName;
  $d = @dir($dirName);
  if ($d) {
    while ($entry = $d->read()) {
      if ($entry != "." && $entry != "..") {
        if (is_dir($dirName . "/" . $entry)) {
          getDirList($dirName . "/" . $entry);
        }
      }
    }
    $d->close();
    unset($d);
  }

  return $sub_dir_files;
}

function zen_display_files($include_root = false, $filetypesincluded = 1) {
  global $check_directory, $found, $configuration_key_lookup, $outCount, $output;
  global $db;
  $max_context_lines_before = $max_context_lines_after = abs((int)$_POST['context_lines']);

  $directory_array = array();
  for ($i = 0, $n = count($check_directory); $i < $n; $i++) {

    $dir_check = $check_directory[$i];

    switch ($filetypesincluded) {
      case(1):
        $file_extensions = array('.php');
        break;
      case(2):
        $file_extensions = array('.php', '.css');
        break;
      case(3):
        $file_extensions = array('.css');
        break;
      case(4):
        $file_extensions = array('.html', '.txt');
        break;
      case(5):
        $file_extensions = array('.js');
        break;
      case(6):
        $file_extensions = array('.*');
        break;
      default:
        $file_extensions = array('.php', '.css');
        break;
    }

    if ($dir = @dir($dir_check)) {
      while ($file = $dir->read()) {
        if (!is_dir($dir_check . $file)) {
          foreach ($file_extensions as $extension) {
            if (preg_match('/\\' . $extension . '$/', $file) > 0) {
              $directory_array[] = $dir_check . $file;
            }
          }
        }
      }
      if (count($directory_array)) {
        sort($directory_array);
      }
      $dir->close();
      unset($dir);
    }
  }

  if ($include_root == true) {
    $original_array = $directory_array;
    $root_array = array();
// if not html/txt
    if ($filetypesincluded != 3 && $filetypesincluded != 4 && $filetypesincluded != 5) {
      $root_array[] = DIR_FS_CATALOG . 'ajax.php';
      $root_array[] = DIR_FS_CATALOG . 'index.php';
      $root_array[] = DIR_FS_CATALOG . 'ipn_main_handler.php';
      $root_array[] = DIR_FS_CATALOG . 'page_not_found.php';
      $root_array[] = DIR_FS_CATALOG . 'square_handler.php';
    }

    $root_array[] = DIR_FS_CATALOG . FILENAME_DATABASE_TEMPORARILY_DOWN;
    $new_array = array_merge($root_array, $original_array);
    $directory_array = $new_array;
  }

// show path and filename
  if (strtoupper($configuration_key_lookup) == $configuration_key_lookup) {

//      $configuration_key_lookup = str_replace(array('"', "'"), '', $configuration_key_lookup);
    // if appears to be a constant ask about configuration table
    $check_database = true;
    $sql = "SELECT *
            FROM " . TABLE_CONFIGURATION . "
            WHERE configuration_key = :zcconfigkey:";
    $sql = $db->BindVars($sql, ':zcconfigkey:', strtoupper($configuration_key_lookup), 'string');
    $check_configure = $db->Execute($sql);
    if ($check_configure->RecordCount() < 1) {
      $sql = "SELECT *
              FROM " . TABLE_PRODUCT_TYPE_LAYOUT . "
              WHERE configuration_key = :zcconfigkey:";
      $sql = $db->BindVars($sql, ':zcconfigkey:', strtoupper($configuration_key_lookup), 'string');
      $check_configure = $db->Execute($sql);
    }
    if ($check_configure->RecordCount() >= 1) {
      $links = '<strong><span class="alert">' . TEXT_SEARCH_DATABASE_TABLES . '</span></strong> ' . '<a href="' . zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT, 'action=' . 'locate_configuration' . '&configuration_key_lookup=' . zen_output_string_protected($configuration_key_lookup)) . '">' . zen_output_string_protected($configuration_key_lookup) . '</a><br><br>';
    } else {
      // do nothing
    }
  } else {
    // don't ask about configuration table
  }
  $outCount = '<table class="table">' . "\n";
  if (!empty($check_database) && $check_configure->RecordCount() >= 1) {
    // only ask if found
    $outCount .= '<tr><td>' . $links . '</td></tr>';
  }
  $outCount .= '<tr class="infoBoxContent"><td class="dataTableHeadingContent">' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . TEXT_INFO_SEARCHING . count($directory_array) . TEXT_INFO_FILES_FOR . zen_output_string_protected($configuration_key_lookup) . '</td></tr></table>' . "\n\n";

// check all files located
  $file_cnt = 0;
  $cnt_found = 0;
  $case_sensitive = !empty($_POST['case_sensitive']);
  $include_plugins = !empty($_POST['include_plugins']);
  $include_laravel = !empty($_POST['include_laravel']);
  for ($i = 0, $n = count($directory_array); $i < $n; $i++) {
    // build file content of matching lines
    $file_cnt++;
    $file = $directory_array[$i];
    // clean path name
    $file = preg_replace('~/+~', '/', $file);

    $show_file = '';
    if (file_exists($file)) {
      $show_file .= "\n" . '<table class="table results"><tr><td class="main">' . "\n";
      $show_file .= '<tr class="infoBoxContent"><td class="dataTableHeadingContent">';
      $show_file .= '<strong>' . $file . '</strong>';
      $show_file .= '</td></tr>';
      $show_file .= '<tr><td class="main">';

      // put file into an array to be scanned
      $lines = file($file);
      $found_line = false;
      // loop through the array, show line and line numbers
      $cnt_lines = 0;
      foreach ($lines as $line_num => $line) {
        $padding_length = strlen((string)count($lines));
        $cnt_lines++;

        // determine correct search pattern rule
        // uses '#' as regex delimiter
        $search_pattern = preg_quote($configuration_key_lookup, '#');
        if (isset($_GET['action']) && $_GET['action'] == 'locate_all_files' && !empty($_GET['m'])) {
          // escape the delimiter character:
          $search_pattern = str_replace('#', '\#', $configuration_key_lookup);
        }

        // do actual search
        $search_found = preg_match('#' . $search_pattern . '#' . (!$case_sensitive ? 'i' : ''), $line);

        if ($search_found === false) {
          return false;
        }

        // use to debug for UTF-8 NO BOM on files: test search on a, e, s change if below to true
        if (false && htmlspecialchars($line, ENT_QUOTES, CHARSET) == '') {
          $output = '<br>SOMETHING BROKE in: ' . $file . '<br>on: ' . $line_num . ' - ' . $line . '<br>';
          $search_found = false;
        }

        if ($search_found) {
          $found_line = true;
          $found = true;
          $cnt_found++;
          $line_numpos = $line_num + 1;

          for ($j = min($max_context_lines_before, $line_num); $j > 0; $j--) {
            $show_file .= '<br>Line #<span class="dtk-linenum">' . number_pad_with_spaces($line_numpos - $j, $padding_length) . '</span> : ';
            $show_file .= '<span class="dtk-contextline">';
            $show_file .= cleanup_dtk_output_text($lines[($line_num - $j)]);
            $show_file .= '</span>';
          }

          $show_file .= '<br>Line #<span class="dtk-linenum">' . number_pad_with_spaces($line_numpos, $padding_length) . '</span> : ';

          if ($max_context_lines_before > 0)
            $show_file .= '<strong>';
          $show_file .= '<span class="dtk-foundline' . ($max_context_lines_before > 0 ? '-multi' : '') . '">';
          $show_file .= cleanup_dtk_output_text($line, $search_pattern, $case_sensitive);
          $show_file .= '</span>';
          if ($max_context_lines_before > 0)
            $show_file .= '</strong>';

          for ($j = 1, $m = min($max_context_lines_after, count($lines) - $line_numpos); $j < $m + 1; $j++) {
            $show_file .= '<br>Line #<span class="dtk-linenum">' . number_pad_with_spaces($line_numpos + $j, $padding_length) . '</span> : ';
            $show_file .= '<span class="dtk-contextline">';
            $show_file .= cleanup_dtk_output_text($lines[($line_num + $j)]);
            $show_file .= '</span>';
          }
          $show_file .= "<br>\n";
        } else {
          if ($cnt_lines >= 5) {
//            $show_file .= ' .';
            $cnt_lines = 0;
          }
        }
      }
    }
    $show_file .= '</td></tr></table>' . "\n";

    // if there was a match, show lines
    if ($found_line == true) {
      $output .= $show_file . '<div class="row"></div>';
    } // show file
  }
  $output .= '<table class="table"><tr class="infoBoxContent"><td class="dataTableHeadingContent">' . TEXT_INFO_MATCHES_FOUND . $cnt_found . ' --- ' . TEXT_INFO_SEARCHING . count($directory_array) . TEXT_INFO_FILES_FOR . zen_output_string_protected($configuration_key_lookup) . '</td></tr></table>';
  $output .= '<table class="table"><tr><td>' . zen_draw_separator('pixel_black.gif', '100%', '2') . '</td></tr><tr><td>&nbsp;</td></tr></table>' . "\n";
  return true;
}

// zen_display_files

/**
 * Strip out dangerous content, run htmlspecialchars, and insert highlighting of "found" text
 *
 * @param string $input
 * @param string $highlight
 * @param boolean $case_sensitive
 * @return string
 */
function cleanup_dtk_output_text($input = '', $highlight = '', $case_sensitive = false) {
  if ($input == '')
    return $input;
  //prevent db pwd from being displayed, for sake of security
  $input = (substr_count($input, "'DB_SERVER_PASSWORD'")) ? '***HIDDEN***' : $input;

  // mark the selected text, for highlighting
  if ($highlight != '') {
    $input = preg_replace('#(' . $highlight . ')#' . (!$case_sensitive ? 'i' : ''), '~~!~~!~~\1~!!~!!~', $input);
  }
  // sanitize output
  $input = htmlspecialchars($input, ENT_QUOTES, CHARSET);

  // keep original "spaces" (doesn't account for tabs)
  $input = str_replace(' ', '&nbsp;', $input);

  // highlight the selected text
  if ($highlight != '') {
    $input = str_replace('~~!~~!~~', '<span class="dtk-highlite">', $input);
    $input = str_replace('~!!~!!~', '</span>', $input);
  }

  return $input;
}

/**
 * Left-pad input "number" string with spaces
 *
 * @param string $number The number string to pad
 * @param int $n The number of padding characters to accommodate
 * @return string
 */
function number_pad_with_spaces($number, $n = 0) {
  return str_replace(' ', '&nbsp;', str_pad((int)$number, $n, ' ', STR_PAD_LEFT));
}

/* ==================================================================== */

$action = $_GET['action'] ?? '';
// don't do any 'action' if clicked on the Check for Updates button
    if (isset($_GET['vcheck']) && $_GET['vcheck'] == 'yes') {
        $action = '';
    }

$found = true;

$search = $_POST['search'] ?? '';
$flags = (isset($_GET['v']) ? '&v=' : '') . (isset($_GET['s']) ? '&s=' . preg_replace('/[^a-z]/', '', $_GET['s']) : '');

switch ($action) {
  case ('search_config_keys'):
    // credits Benjamin Bellamy, torvista
    $search_type = (isset($_GET['t']) && $_GET['t'] == 'all') ? 'all' : 'keyword';
      if ($search_type == 'all') {
          $search = '';
      }
    // The request that returns the configuration keys:
    // Product-Type info is limited to products_type=1 (general)
    $sql = "(SELECT configuration_id, configuration_key, c.configuration_group_id AS configuration_group_id, configuration_group_title,
                    configuration_title, configuration_description, (CASE WHEN use_function = 'zen_cfg_password_display' THEN '********' ELSE configuration_value END) AS configuration_value, 'conf' AS src
             FROM " . TABLE_CONFIGURATION . " c,
                  " . TABLE_CONFIGURATION_GROUP . " g
             WHERE c.configuration_group_id = g.configuration_group_id
             :cfgAndClause: " . (!isset($_GET['v']) ? ' AND g.visible=1 ' : '') . "
             ORDER BY configuration_title, configuration_group_id)
         UNION
        (SELECT configuration_id, configuration_key, p.product_type_id AS configuration_group_id, type_name AS configuration_group_title,
                configuration_title, configuration_description, configuration_value, 'type' AS src
         FROM " . TABLE_PRODUCT_TYPE_LAYOUT . " p,
              " . TABLE_PRODUCT_TYPES . " t
         WHERE p.product_type_id = t.type_id
        :typeRestriction:
        :ptypeAndClause:
        ORDER BY configuration_title, configuration_group_id)";
    $searchClause = $cfgKeySearch = '';
    // add search criteria
    if (zen_not_null($search) && $search_type != 'all') {
      $searchClause = "AND (configuration_title LIKE '%:search:%' OR configuration_description LIKE '%:search:%' :cfgKeySearch:)";
      // support configuration_key constants
      $cfgKeySearch = " OR configuration_key like '%:zcconfigkey:%' ";
    }
    $cfgAndClause = $searchClause;
    $ptypeAndClause = $searchClause;
    $sql = $db->bindVars($sql, ':cfgAndClause:', $cfgAndClause, 'passthru');
    $sql = $db->bindVars($sql, ':ptypeAndClause:', $ptypeAndClause, 'passthru');
    $sql = $db->bindVars($sql, ':typeRestriction:', ' and t.type_id=1 ', 'passthru');
    $sql = $db->BindVars($sql, ':cfgKeySearch:', $cfgKeySearch, 'passthru');
    $sql = $db->BindVars($sql, ':zcconfigkey:', str_replace('_', '\_', strtoupper($search)), 'noquotestring');
    $sql = $db->bindVars($sql, ':search:', $search, 'noquotestring');
    if (isset($_GET['s']) && $_GET['s'] == 'k')
      $sql .= ' ORDER BY configuration_key';
    // if nothing submitted to search for, force no results
    if ($search_type != 'all' && $search == '') {
      $sql = "SELECT * FROM " . TABLE_CONFIGURATION . " WHERE 2 = 3";
    }
    $keySearchResults = $db->Execute($sql);
    if ($keySearchResults->RecordCount() == 0) {
        if (empty($search)) {
            $messageStack->add(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
        } else {
            $messageStack->add(ERROR_CONFIGURATION_KEY_NOT_FOUND, 'caution');
        }
    }
    break;

  case ('locate_configuration'):
    if ($configuration_key_lookup == '') {
      $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
      zen_redirect(zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT));
    }
    $found = false;
    $zv_files_group = $_POST['zv_files'];
    $q_const = zen_output_string_protected($_POST['configuration_key']);

    $sql = "SELECT *, (CASE WHEN use_function = 'zen_cfg_password_display' THEN '********' ELSE configuration_value END) AS configuration_value
            FROM " . TABLE_CONFIGURATION . "
            WHERE configuration_key = :zcconfigkey:";
    $sql = $db->BindVars($sql, ':zcconfigkey:', $_POST['configuration_key'], 'string');
    $check_configure = $db->Execute($sql);
    if ($check_configure->RecordCount() < 1) {
      $sql = "SELECT * FROM " . TABLE_PRODUCT_TYPE_LAYOUT . " WHERE configuration_key = :zcconfigkey:";
      $sql = $db->BindVars($sql, ':zcconfigkey:', $_POST['configuration_key'], 'string');
      $check_configure = $db->Execute($sql);
      if ($check_configure->RecordCount() < 1) {
        // build filenames to search
        switch ($zv_files_group) {
          case (0): // none
            $check_directory = array();
            $filename_listing = '';
            break;
          case (1): // all english.php files
            $check_directory = array();
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES;
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/';
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $template_dir . '/' . $_SESSION['language'] . '/';
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/' . $template_dir . '/';
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/extra_definitions/';
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/extra_definitions/' . $template_dir . '/';
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/';
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/shipping/';
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/order_total/';
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/product_types/';
            $check_directory[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES;
            $check_directory[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES . $_SESSION['language'] . '/';
            $check_directory[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/newsletters/';
            break;
          case (2): // all catalog /language/*.php
            $check_directory = array();
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES;
            break;
          case (3): // all catalog /language/english/*.php
            $check_directory = array();
            $check_directory[] = DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/';
            break;
          case (4): // all admin /language/*.php
            $check_directory = array();
            $check_directory[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES;
            break;
          case (5): // all admin /language/english/*.php
            // set directories and files names
            $check_directory = array();
            $check_directory[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES . $_SESSION['language'] . '/';
            break;
        } // eof: switch
        // Check for new databases and filename in extra_datafiles directory

        zen_display_files();
      } else {
        $show_products_type_layout = true;
        $show_configuration_info = true;
        $found = true;
      }
    } else {
      $show_products_type_layout = false;
      $show_configuration_info = true;
      $found = true;
    }

    break;

  case ('locate_function'):
    if ($configuration_key_lookup == '') {
      $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
      zen_redirect(zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT));
    }
    $found = false;
    $zv_files_group = $_POST['zv_files'];
    $q_func = zen_output_string_protected($_POST['configuration_key']);

    // build filenames to search
    switch ($zv_files_group) {
      case (0): // none
        $filename_listing = '';
        $check_directory = array();
        break;
      case (1): // all admin/catalog function files
        $check_directory = array();
        $check_directory[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS;
        $check_directory[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'extra_functions/';
        $check_directory[] = DIR_FS_ADMIN . DIR_WS_FUNCTIONS;
        $check_directory[] = DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'extra_functions/';
        break;
      case (2): // all catalog function files
        $check_directory = array();
        $check_directory[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS;
        $check_directory[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'extra_functions/';
        break;
      case (3): // all admin function files
        $check_directory = array();
        $check_directory[] = DIR_FS_ADMIN . DIR_WS_FUNCTIONS;
        $check_directory[] = DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'extra_functions/';
        break;
    } // eof: switch
    // @TODO: Check for new databases and filename in extra_datafiles directory

    zen_display_files();

    break;

  case ('locate_class'):
    if ($configuration_key_lookup == '') {
      $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
      zen_redirect(zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT));
    }
    $found = false;
    $zv_files_group = $_POST['zv_files'];
    $q_class = zen_output_string_protected($_POST['configuration_key']);

    // build filenames to search
    switch ($zv_files_group) {
      case (0): // none
        $filename_listing = '';
        $check_directory = array();
        break;
      case (1): // all admin/catalog classes files
        $check_directory = array();
        $filename_listing = '';

        $sub_dir_files = array();
        getDirList(DIR_FS_CATALOG . DIR_WS_CLASSES, 1);
        for ($i = 0, $n = count($sub_dir_files); $i < $n; $i++) {
          $check_directory[] = $sub_dir_files[$i] . '/';
        }
        $check_directory[] = DIR_FS_ADMIN . DIR_WS_CLASSES;
        break;
      case (2): // all catalog classes files
        $check_directory = array();
        $sub_dir_files = array();
        getDirList(DIR_FS_CATALOG . DIR_WS_CLASSES, 1);
        for ($i = 0, $n = count($sub_dir_files); $i < $n; $i++) {
          $check_directory[] = $sub_dir_files[$i] . '/';
        }
        break;
      case (3): // all admin class files
        $check_directory = array();
        $check_directory[] = DIR_FS_ADMIN . DIR_WS_CLASSES;
        break;
    } // eof: switch
    // @TODO: Check for new databases and filename in extra_datafiles directory

    zen_display_files();

    break;

  case ('locate_template'):
    if ($configuration_key_lookup == '') {
      $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
      zen_redirect(zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT));
    }
    $found = false;
    $zv_files_group = $_POST['zv_files'];
    $q_tpl = zen_output_string_protected($_POST['configuration_key']);

    // build filenames to search
    switch ($zv_files_group) {
      case (0): // none
        $check_directory = array();
        $filename_listing = '';
        break;
      case (1): // all template files
        $check_directory = array();
        $check_directory[] = DIR_FS_CATALOG_TEMPLATES . 'template_default/templates' . '/';
        $check_directory[] = DIR_FS_CATALOG_TEMPLATES . 'template_default/sideboxes' . '/';
        $check_directory[] = DIR_FS_CATALOG_MODULES;
        $check_directory[] = DIR_FS_CATALOG_MODULES . 'sideboxes/';

        $check_directory[] = DIR_FS_CATALOG_TEMPLATES . $template_dir . '/templates' . '/';
        $check_directory[] = DIR_FS_CATALOG_TEMPLATES . $template_dir . '/sideboxes' . '/';

        $check_directory[] = DIR_FS_CATALOG_MODULES . $template_dir . '/';
        $check_directory[] = DIR_FS_CATALOG_MODULES . 'sideboxes/' . $template_dir . '/';

        $sub_dir_files = array();
        getDirList(DIR_FS_CATALOG_MODULES . 'pages');
        $check_dir = $sub_dir_files;
        for ($i = 0, $n = count($check_dir); $i < $n; $i++) {
          $check_directory[] = $check_dir[$i] . '/';
        }

        break;
      case (2): // all /templates files
        $check_directory = array();
        $check_directory[] = DIR_FS_CATALOG_TEMPLATES . 'template_default/templates' . '/';
        $check_directory[] = DIR_FS_CATALOG_TEMPLATES . $template_dir . '/templates' . '/';
        break;
      case (3): // all sideboxes files
        $check_directory = array();
        $check_directory[] = DIR_FS_CATALOG_TEMPLATES . 'template_default/sideboxes' . '/';
        $check_directory[] = DIR_FS_CATALOG_MODULES . 'sideboxes/';
        $check_directory[] = DIR_FS_CATALOG_MODULES . 'sideboxes/' . $template_dir . '/';
        $check_directory[] = DIR_FS_CATALOG_TEMPLATES . $template_dir . '/sideboxes' . '/';
        break;
      case (4): // all /pages files
        $check_directory = array();
        //$check_directory[] = DIR_FS_CATALOG_MODULES . 'pages/';
        $sub_dir_files = array();
        getDirList(DIR_FS_CATALOG_MODULES . 'pages');

        $check_dir = array_merge($check_directory, $sub_dir_files);
        for ($i = 0, $n = count($check_dir); $i < $n; $i++) {
          $check_directory[] = $check_dir[$i] . '/';
        }

        break;
    } // eof: switch
    // Check for new databases and filename in extra_datafiles directory

    zen_display_files();

    break;


/// all files
  case ('locate_all_files'):
    $zv_check_root = false;
    if ($configuration_key_lookup == '') {
      $messageStack->add_session(ERROR_CONFIGURATION_KEY_NOT_ENTERED, 'caution');
      zen_redirect(zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT));
    }
    $found = false;
    $zv_files_group = $_POST['zv_files'];
    $zv_filestype_group = $_POST['zv_filestype'];
    $q_all = zen_output_string_protected($_POST['configuration_key']);
//echo 'settings: ' . '$zv_files_group: ' . $zv_files_group . '$zv_filestype_group: ' . $zv_filestype_group . '<br>';
//echo 'Who am I template ' . $template_dir . ' sess lang ' . $_SESSION['language'];
    switch ($zv_files_group) {
      case (0): // none
        $check_directory = array();
        $filename_listing = '';
        break;
      case (1): // all
        $zv_check_root = true;
        $filename_listing = '';

        $check_directory = array();

// get includes
        $sub_dir_files = array();
        getDirList(DIR_FS_CATALOG . DIR_WS_INCLUDES, $zv_filestype_group);
        $sub_dir_files_catalog = $sub_dir_files;

// get email
        $sub_dir_files = array();
        getDirList(DIR_FS_EMAIL_TEMPLATES, $zv_filestype_group);
        $sub_dir_files_email = $sub_dir_files;

// get admin
        $sub_dir_files = array();
        getDirList(DIR_FS_ADMIN, $zv_filestype_group);
        $sub_dir_files_admin = $sub_dir_files;

// get zc_plugins
        $sub_dir_files = array();
        if ($include_plugins) {
          getDirList(DIR_FS_CATALOG . '/zc_plugins', $zv_filestype_group);
        }
        $sub_dir_files_plugins = $sub_dir_files;

// get laravel
        $sub_dir_files = array();
        if ($include_plugins) {
          getDirList(DIR_FS_CATALOG . '/laravel', $zv_filestype_group);
        }
        $sub_dir_files_laravel = $sub_dir_files;

        $check_dir = array_merge($sub_dir_files_catalog, $sub_dir_files_email, $sub_dir_files_admin, $sub_dir_files_plugins, $sub_dir_files_laravel);
        for ($i = 0, $n = count($check_dir); $i < $n; $i++) {
          $check_directory[] = $check_dir[$i] . '/';
        }
        break;

      case (2): // all catalog
        $zv_check_root = true;
        $filename_listing = '';

        $check_directory = array();

        $sub_dir_files = array();
        getDirList(DIR_FS_CATALOG . DIR_WS_INCLUDES, $zv_filestype_group);
        $sub_dir_files_catalog = $sub_dir_files;

// get email
        $sub_dir_files = array();
        getDirList(DIR_FS_EMAIL_TEMPLATES, $zv_filestype_group);
        $sub_dir_files_email = $sub_dir_files;

        $check_dir = array_merge($sub_dir_files_catalog, $sub_dir_files_email);
        for ($i = 0, $n = count($check_dir); $i < $n; $i++) {
          $zv_add_dir = str_replace('//', '/', $check_dir[$i] . '/');
          if (strstr($zv_add_dir, DIR_WS_ADMIN) == '') {
            $check_directory[] = $zv_add_dir;
          }
        }
        break;

      case (3): // all admin
        $zv_check_root = false;
        $filename_listing = '';

        $check_directory = array();

        $sub_dir_files = array();
        getDirList(DIR_FS_ADMIN, $zv_filestype_group);
        $sub_dir_files_admin = $sub_dir_files;

// get zc_plugins
        $sub_dir_files = array();
        if ($include_plugins) {
          getDirList(DIR_FS_CATALOG . '/zc_plugins', $zv_filestype_group);
        }
        $sub_dir_files_plugins = $sub_dir_files;

// get laravel
        $sub_dir_files = array();
        if ($include_plugins) {
          getDirList(DIR_FS_CATALOG . '/laravel', $zv_filestype_group);
        }
        $sub_dir_files_laravel = $sub_dir_files;

        $check_dir = array_merge($sub_dir_files_admin, $sub_dir_files_plugins, $sub_dir_files_laravel);
        for ($i = 0, $n = count($check_dir); $i < $n; $i++) {
          $check_directory[] = $check_dir[$i] . '/';
        }
        break;

      case (4): // all plugins
        $zv_check_root = false;
        $filename_listing = '';

        $check_directory = array();

        $sub_dir_files = array();
        getDirList(DIR_FS_CATALOG . '/zc_plugins', $zv_filestype_group);
        $check_dir = $sub_dir_files;
        for ($i = 0, $n = count($check_dir); $i < $n; $i++) {
          $check_directory[] = $check_dir[$i] . '/';
        }
        break;

      case (5): // laravel
        $zv_check_root = false;
        $filename_listing = '';

        $check_directory = array();

        $sub_dir_files = array();
        getDirList(DIR_FS_CATALOG . '/laravel', $zv_filestype_group);
        $check_dir = $sub_dir_files;
        for ($i = 0, $n = count($check_dir); $i < $n; $i++) {
          $check_directory[] = $check_dir[$i] . '/';
        }
        break;
    }

    $result = zen_display_files($zv_check_root, $zv_filestype_group);
    if ($result === false) {
      $messageStack->add(TEXT_ERROR_REGEX_FAIL, 'caution');
    }

    break;
} // eof: action
// if no matches in either databases or selected language directory give an error
if ($found == false) {
  $messageStack->add(ERROR_CONFIGURATION_KEY_NOT_FOUND . ' ' . zen_output_string_protected($configuration_key_lookup), 'caution');
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
  <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <?php echo $outCount;?>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <?php
    echo $output;
    ?>
    <div class="container-fluid">
      <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
      <!-- body //-->

      <!-- body_text //-->
      <?php
      if (!empty($show_configuration_info)) {
        $show_configuration_info = false;
        ?>
        <table class="constantlink">
          <tr class="infoBoxContent">
            <td colspan="2" class="pageHeading text-center"><?php echo TABLE_CONFIGURATION_TABLE; ?></td>
          </tr>
          <tr>
            <td class="infoBoxHeading"><?php echo TABLE_TITLE_KEY; ?></td>
            <td class="dataTableHeadingContentWhois"><?php echo $check_configure->fields['configuration_key']; ?></td>
          </tr>
          <tr>
            <td class="infoBoxHeading"><?php echo TABLE_TITLE_TITLE; ?></td>
            <td class="dataTableHeadingContentWhois"><?php echo $check_configure->fields['configuration_title']; ?></td>
          </tr>
          <tr>
            <td class="infoBoxHeading"><?php echo TABLE_TITLE_DESCRIPTION; ?></td>
            <td class="dataTableHeadingContentWhois"><?php echo $check_configure->fields['configuration_description']; ?></td>
          </tr>
          <?php
          if ($show_products_type_layout == true) {
            $check_configure_group = $db->Execute("SELECT * FROM " . TABLE_PRODUCT_TYPES . " WHERE type_id='" . (int)$check_configure->fields['product_type_id'] . "'");
          } else {
            $check_configure_group = $db->Execute("SELECT * FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id='" . (int)$check_configure->fields['configuration_group_id'] . "'");
          }
          ?>

          <?php
          if ($show_products_type_layout == true) {
            ?>
            <tr>
              <td class="infoBoxHeading"><?php echo TABLE_TITLE_GROUP; ?></td>
              <td class="dataTableHeadingContentWhois"><?php echo 'Product Type Layout'; ?></td>
            </tr>
          <?php } else { ?>
            <tr>
              <td class="infoBoxHeading"><?php echo TABLE_TITLE_VALUE; ?></td>
              <td class="dataTableHeadingContentWhois"><?php echo $check_configure->fields['configuration_value']; ?></td>
            </tr>
            <tr>
              <td class="infoBoxHeading"><?php echo TABLE_TITLE_GROUP; ?></td>
              <td class="dataTableHeadingContentWhois">
                  <?php
                  $id_note = '';
                  if (isset($check_configure_group->fields['visible']) && $check_configure_group->fields['visible'] == '0') {
                    $id_note = TEXT_INFO_CONFIGURATION_HIDDEN;
                  }
                  echo 'ID#' . $check_configure_group->fields['configuration_group_id'] . ' ' . $check_configure_group->fields['configuration_group_title'] . $id_note;
                  ?>
              </td>
            </tr>
          <?php } ?>
          <tr>
              <td>&nbsp;</td>
            <td class="main text-center">
                <?php
                if ($show_products_type_layout == false and ( $check_configure->fields['configuration_id'] != 0 and $check_configure_group->fields['visible'] != 0)) {
                  echo '<a href="' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $check_configure_group->fields['configuration_group_id'] . '&cID=' . $check_configure->fields['configuration_id']) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>';
                } else {
                  $page = '';
                  if (strstr($check_configure->fields['configuration_key'], 'MODULE_SHIPPING'))
                    $page .= 'shipping';
                  if (strstr($check_configure->fields['configuration_key'], 'MODULE_PAYMENT'))
                    $page .= 'payment';
                  if (strstr($check_configure->fields['configuration_key'], 'MODULE_ORDER_TOTAL'))
                    $page .= 'ordertotal';

                  if ($show_products_type_layout == true) {
                    echo '<a href="' . zen_href_link(FILENAME_PRODUCT_TYPES) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>';
                  } else {
                    if ($page != '') {
                      echo '<a href="' . zen_href_link(FILENAME_MODULES, 'set=' . $page) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>';
                    } else {
                      echo TEXT_INFO_NO_EDIT_AVAILABLE . '<br>';
                    }
                  }
                }
                ?>
                <?php echo '<a href="' . zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'; ?>
            </td>
          </tr>
          <tr class="infoBoxContent">
            <td colspan="2" class="pageHeading text-center">
                <?php
                $links = '<br><strong><span class="alert">' . TEXT_SEARCH_ALL_FILES . '</span></strong> ' . '<a href="' . zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT, 'action=' . 'locate_all_files' . '&configuration_key_lookup=' . zen_output_string_protected($configuration_key_lookup) . '&zv_files=1') . '">' . zen_output_string_protected($configuration_key_lookup) . '</a><br>';
                echo $links;
                ?>
            </td>
          </tr>
        </table>

        <?php
      } else {
        ?>

        <?php
// disabled and here for an example
        if (false) {
          ?>
          <!-- bof: update all products price sorter -->
          <table >
            <tr>
              <td class="main text-left" valign="top"><?php echo TEXT_INFO_PRODUCTS_PRICE_SORTER_UPDATE; ?></td>
              <td class="main text-right" valign="middle"><?php echo '<a href="' . zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT, 'action=update_all_products_price_sorter') . '" class="btn btn-primary" role="button">' . IMAGE_UPDATE . '</a>'; ?></td>
            </tr>
          </table>
          <!-- eof: update all products price sorter -->
        <?php } ?>

        <!-- bof: Locate a configuration constant -->
        <div class="row">
          <div class="col-sm-12"><?php echo TEXT_CONFIGURATION_CONSTANT; ?></div>
        </div>

        <?php echo zen_draw_form('locate_configure', FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_configuration', 'post', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_CONFIGURATION_KEY, 'locConfig', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_input_field('configuration_key', $q_const, ' id="locConfig" size="40" class="form-control" placeholder="' . TEXT_SEARCH_KEY_PLACEHOLDER . '"'); ?>
          </div>
        </div>
        <div class="form-group">
            <?php
            $za_lookup = array(
              array('id' => '0', 'text' => TEXT_LOOKUP_NONE),
              array('id' => '1', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_LANGUAGE),
              array('id' => '2', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG),
              array('id' => '3', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_CATALOG_TEMPLATE),
              array('id' => '4', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN),
              array('id' => '5', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_ADMIN_LANGUAGE)
            );
//                                              array('id' => '6', 'text' => TEXT_LANGUAGE_LOOKUP_CURRENT_ALL)
            ?>
            <?php echo zen_draw_label(TEXT_LANGUAGE_LOOKUPS, 'zv_files_lc', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_pull_down_menu('zv_files', $za_lookup, (isset($action) && $action == 'locate_configuration' ? (int)$_POST['zv_files'] : '0'), 'id="zv_files_lc" class="form-control"'); ?>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-12"><?php echo TEXT_INFO_CONFIGURATION_UPDATE; ?></div>
        </div>
        <div class="form-group text-right">
          <div class="col-sm-12">
            <button type="submit" class="btn btn-primary"><?php echo TEXT_BUTTON_SEARCH_ALT; ?></button>
          </div>
        </div>
        <?php echo '</form>'; ?>
        <!-- eof: Locate a configuration constant -->
        <div class="row"><?php echo zen_draw_separator(); ?></div>
        <!-- bof: search configuration keys -->
        <div class="row">
          <div class="col-sm-12"><?php echo SEARCH_CFG_KEYS_HEADING_TITLE; ?></div>
        </div>
        <?php echo zen_draw_form('search_keys', FILENAME_DEVELOPERS_TOOL_KIT, 'action=search_config_keys' . $flags, 'post', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <?php echo zen_draw_label(SEARCH_CFG_KEYS_SEARCH_BOX_TEXT, 'search', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('search', zen_output_string_protected($search), 'id="search" size="40" class="form-control" placeholder="' . SEARCH_CFG_KEYS_FORM_PLACEHOLDER . '"'); ?></div>
        </div>
        <div class="form-group">
          <div class="col-sm-12 text-right">
            <button type="submit" class="btn btn-primary"><?php echo SEARCH_CFG_KEYS_FORM_BUTTON_SEARCH_SORTED_BY_GROUP; ?></button>
            <input type="button" value="<?php echo SEARCH_CFG_KEYS_FORM_BUTTON_SEARCH_SORTED_BY_KEY; ?>" onClick="document.search_keys.action = '<?php echo zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT, 'action=search_config_keys&s=k' . $flags) ?>'; document.search_keys.submit();" title="<?php echo SEARCH_CFG_KEYS_FORM_BUTTON_SEARCH_SORTED_BY_KEY; ?>" class="btn btn-primary">
            <input type="button" value="<?php echo SEARCH_CFG_KEYS_FORM_BUTTON_VIEW_ALL; ?>" onClick="document.search_keys.action = '<?php echo zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT, 'action=search_config_keys&t=all' . $flags) ?>'; document.search_keys.submit();" title="<?php echo SEARCH_CFG_KEYS_FORM_BUTTON_VIEW_ALL; ?>" class="btn btn-primary">
            <button title="<?php echo TEXT_RESET_BUTTON_ALT; ?>" onClick="document.search_keys.action = '<?php echo zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT); ?>'; document.search_keys.search = ''; document.search_keys.submit();" class="btn btn-primary"><?php echo SEARCH_CFG_KEYS_FORM_BUTTON_RESET; ?></button>
          </div>
        </div>
        <?php echo '</form>'; ?>
        <?php if ($action == 'search_config_keys') { ?>
          <div class="row">
            <div class="col-sm-12"><?php echo ($keySearchResults->RecordCount() > 0) ? $keySearchResults->RecordCount() . ' ' . SEARCH_CFG_KEYS_FOUND_KEYS : SEARCH_CFG_KEYS_NOT_FOUND_KEYS; ?></div>
          </div>
        <?php } ?>
        <?php
        if ($action == 'search_config_keys' && $keySearchResults->RecordCount() > 0) {
          $last_group = $keySearchResults->fields['configuration_group_id'];
          $groupChanged = FALSE;
          ?>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo SEARCH_CFG_KEYS_TABLE_SECTION; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo SEARCH_CFG_KEYS_TABLE_GROUP; ?></th>
                  <th class="dataTableHeadingContent"><?php echo SEARCH_CFG_KEYS_TABLE_TITLE; ?></th>
                  <th class="dataTableHeadingContent"><?php echo SEARCH_CFG_KEYS_TABLE_DESCRIPTION; ?></th>
                  <th class="dataTableHeadingContent"><?php echo SEARCH_CFG_KEYS_TABLE_KEY_NAME; ?></th>
                  <th class="dataTableHeadingContent"><?php echo SEARCH_CFG_KEYS_TABLE_VALUE; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo SEARCH_CFG_KEYS_TABLE_EDIT; ?></th>
                </tr>
              </thead>
              <tbody>
                  <?php
                  foreach ($keySearchResults as $keySearchResult) {
                    if ($keySearchResult['src'] == 'type') {
                      $section = 'Product Types';
                      $editlink = zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $keySearchResult['configuration_group_id'] . '&cID=' . $keySearchResult['configuration_id'] . '&action=layout_edit');
                      $viewlink = zen_href_link(FILENAME_PRODUCT_TYPES, 'ptID=' . $keySearchResult['configuration_group_id'] . '&cID=' . $keySearchResult['configuration_id'] . '&action=layout');
                    } else if ($keySearchResult['src'] == 'conf') {
                      $section = 'Configuration';
                      $editlink = zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $keySearchResult['configuration_group_id'] . '&cID=' . $keySearchResult['configuration_id'] . '&action=edit');
                      $viewlink = zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $keySearchResult['configuration_group_id'] . '&cID=' . $keySearchResult['configuration_id']);
                    } else {
                      $editlink = '';
                      $viewlink = '';
                    }
                    if (!strpos($flags, 's=k') && $last_group != $keySearchResult['configuration_group_id']) {
                      $groupChanged = TRUE;
                    }
                    $last_group = $keySearchResult['configuration_group_id'];
                    $tdClass = 'dataTableContent' . ($groupChanged ? ' dataTableGroupChange' : '');
                    ?>
                  <tr class="dataTableRow">
                    <td class="<?php echo $tdClass; ?>"><?php echo $section; ?></td>
                    <td class="<?php echo $tdClass; ?> text-center"><?php echo $keySearchResult['configuration_group_title']; ?></td>
                    <td class="<?php echo $tdClass; ?>"><?php echo $keySearchResult['configuration_title']; ?></td>

                    <td class="<?php echo $tdClass; ?>"><?php echo $keySearchResult['configuration_description']; ?> &nbsp;</td>
                    <td class="<?php echo $tdClass; ?>"><?php echo $keySearchResult['configuration_key']; ?></td>
                    <td class="<?php echo $tdClass; ?>"><?php echo htmlspecialchars($keySearchResult['configuration_value']); /* implode("<br>\n", preg_split("/[\s,.]+/", $configuration->fields['configuration_value'])) */ ?></td>
                    <td class="<?php echo $tdClass; ?> text-center" onclick="document.location.href = '<?php echo $viewlink; ?>'"><a href="<?php echo $editlink; ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT; ?></a></td>
                  </tr>
                  <?php
                  $groupChanged = FALSE;
                }
                ?>
              </tbody>
            </table>
          </div>
          <?php
        }
        ?>

        <!-- eof: search configuration keys -->
        <div class="row"><?php echo zen_draw_separator(); ?></div>
        <!-- bof: Locate a function -->

        <div class="row">
          <div class="col-sm-12"><?php echo TEXT_FUNCTION_CONSTANT; ?></div>
        </div>
        <?php echo zen_draw_form('locate_function', FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_function', 'post', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_CONFIGURATION_KEY, 'configuration_key_lf', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('configuration_key', $q_func, 'id="configuration_key_lf" class="form-control" size="40" placeholder="' . TEXT_SEARCH_PHRASE_PLACEHOLDER . '"'); ?></div>
        </div>
        <div class="form-group">
            <?php
            $za_lookup = array(
              array('id' => '1', 'text' => TEXT_FUNCTION_LOOKUP_CURRENT),
              array('id' => '2', 'text' => TEXT_FUNCTION_LOOKUP_CURRENT_CATALOG),
              array('id' => '3', 'text' => TEXT_FUNCTION_LOOKUP_CURRENT_ADMIN)
            );
            ?>
            <?php echo zen_draw_label(TEXT_FUNCTION_LOOKUPS, 'zv_files_lf', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('zv_files', $za_lookup, (isset($action) && $action == 'locate_function' ? (int)$_POST['zv_files'] : '1'), 'id="zv_files_lf" class="form-control"'); ?></div>
        </div>
        <div class="form-group">
          <div class="col-sm-12 text-right"><button type="submit" class="btn btn-primary"><?php echo TEXT_BUTTON_SEARCH; ?></button></div>
        </div>
        <?php echo '</form>'; ?>

        <!-- eof: Locate a function -->
        <div class="row"><?php echo zen_draw_separator(); ?></div>
        <!-- bof: Locate a class -->

        <div class="row">
          <div class="col-sm-12"><?php echo TEXT_CLASS_CONSTANT; ?></div>
        </div>
        <?php echo zen_draw_form('locate_class', FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_class', 'post', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_CONFIGURATION_KEY, 'configuration_key_lc', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('configuration_key', $q_class, 'id="configuration_key_lc" class="form-control" size="40" placeholder="' . TEXT_SEARCH_PHRASE_PLACEHOLDER . '"'); ?></div>
        </div>
        <div class="form-group">
            <?php
            $za_lookup = array(
              array('id' => '1', 'text' => TEXT_CLASS_LOOKUP_CURRENT),
              array('id' => '2', 'text' => TEXT_CLASS_LOOKUP_CURRENT_CATALOG),
              array('id' => '3', 'text' => TEXT_CLASS_LOOKUP_CURRENT_ADMIN)
            );
            ?>
            <?php echo zen_draw_label(TEXT_CLASS_LOOKUPS, 'zv_files_lcl', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('zv_files', $za_lookup, (isset($action) && $action == 'locate_class' ? (int)$_POST['zv_files'] : '1'), 'id="zv_files_lcl" class="form-control"'); ?></div>
        </div>
        <div class="form-group">
          <div class="col-sm-12 text-right"><button type="submit" class="btn btn-primary"><?php echo TEXT_BUTTON_SEARCH; ?></button></div>
        </div>
        <?php echo '</form>'; ?>

        <!-- eof: Locate a class -->
        <div class="row"><?php echo zen_draw_separator(); ?></div>
        <!-- bof: Locate template files -->

        <div class="row">
          <div class="col-sm-12"><?php echo TEXT_TEMPLATE_CONSTANT; ?></div>
        </div>
        <?php echo zen_draw_form('locate_template', FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_template', 'post', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_CONFIGURATION_KEY, 'configuration_key_ltf', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('configuration_key', $q_tpl, 'id="configuration_key_ltf" class="form-control" size="40" placeholder="' . TEXT_SEARCH_PHRASE_PLACEHOLDER . '"'); ?></div>
        </div>
        <div class="form-group">
            <?php
            $za_lookup = array(
              array('id' => '1', 'text' => TEXT_TEMPLATE_LOOKUP_CURRENT),
              array('id' => '2', 'text' => TEXT_TEMPLATE_LOOKUP_CURRENT_TEMPLATES),
              array('id' => '3', 'text' => TEXT_TEMPLATE_LOOKUP_CURRENT_SIDEBOXES),
              array('id' => '4', 'text' => TEXT_TEMPLATE_LOOKUP_CURRENT_PAGES)
            );
            ?>
            <?php echo zen_draw_label(TEXT_TEMPLATE_LOOKUPS, 'zv_files_ltf', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('zv_files', $za_lookup, (isset($action) && $action == 'locate_template' ? (int)$_POST['zv_files'] : '1'), 'id="zv_files_ltf" class="form-control"'); ?></div>
        </div>
        <div class="form-group">
          <div class="col-sm-12 text-right"><button type="submit" class="btn btn-primary"><?php echo TEXT_BUTTON_SEARCH; ?></button></div>
        </div>
        <?php echo '</form>'; ?>

        <!-- eof: Locate template Files -->
        <div class="row"><?php echo zen_draw_separator(); ?></div>
        <!-- bof: Locate all files -->

        <div class="row">
          <div class="col-sm-12"><?php echo TEXT_ALL_FILES_CONSTANT; ?></div>
        </div>
        <?php echo zen_draw_form('locate_all_files', FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_all_files', 'post', 'class="form-horizontal"'); ?>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_SEARCH_LOOKUP_PLACEHOLDER, 'configuration_key_af', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('configuration_key', $q_all, 'id="configuration_key_af" class="form-control" size="40" placeholder="' . TEXT_SEARCH_LOOKUP_PLACEHOLDER . '"'); ?></div>
        </div>
        <div class="form-group">
            <?php
            $za_lookup = array(
              array('id' => '1', 'text' => TEXT_ALL_FILES_LOOKUP_CURRENT),
              array('id' => '2', 'text' => TEXT_ALL_FILES_LOOKUP_CURRENT_CATALOG),
              array('id' => '3', 'text' => TEXT_ALL_FILES_LOOKUP_CURRENT_ADMIN),
              array('id' => '4', 'text' => TEXT_ALL_FILES_LOOKUP_CURRENT_PLUGINS),
              array('id' => '5', 'text' => TEXT_ALL_FILES_LOOKUP_CURRENT_LARAVEL),
            );
            ?>
            <?php echo zen_draw_label(TEXT_ALL_FILES_LOOKUPS, 'zv_files_af', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('zv_files', $za_lookup, (isset($action) && $action == 'locate_all_files' ? (int)$_POST['zv_files'] : '1'), 'id="zv_files_af" class="form-control"'); ?></div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_INCLUDE_PLUGINS, 'include_zc_plugins', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
            <div class="checkbox">
              <label for="include_zc_plugins"><?php echo zen_draw_checkbox_field('include_plugins', true, $include_plugins, '', 'id="include_zc_plugins" aria-label="include_zc_plugins"'); ?></label>
            </div>
          </div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_INCLUDE_LARAVEL, 'include_laravel', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
            <div class="checkbox">
              <label for="include_laravel"><?php echo zen_draw_checkbox_field('include_laravel', true, $include_laravel, '', 'id="include_laravel" aria-label="include_laravel"'); ?></label>
            </div>
          </div>
        </div>
        <div class="form-group">
            <?php
            $za_lookup_filetype = array(
              array('id' => '1', 'text' => TEXT_ALL_FILES_LOOKUP_PHP),
              array('id' => '2', 'text' => TEXT_ALL_FILES_LOOKUP_PHPCSS),
              array('id' => '3', 'text' => TEXT_ALL_FILES_LOOKUP_CSS),
              array('id' => '4', 'text' => TEXT_ALL_FILES_LOOKUP_HTMLTXT),
              array('id' => '5', 'text' => TEXT_ALL_FILES_LOOKUP_JS),
              array('id' => '6', 'text' => TEXT_ALL_FILES_LOOKUP_ALL_TYPES),
            );
            ?>
            <?php echo zen_draw_label(TEXT_ALL_FILESTYPE_LOOKUPS, 'zv_filestype', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_pull_down_menu('zv_filestype', $za_lookup_filetype, (!empty($_POST['zv_filestype']) ? $_POST['zv_filestype'] : '1'), 'id="zv_filestype" class="form-control"'); ?></div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_CONTEXT_LINES, 'context_lines', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6"><?php echo zen_draw_input_field('context_lines', strval((int)$default_context_lines), 'id="context_lines" class="form-control" size="1"'); ?></div>
        </div>
        <div class="form-group">
            <?php echo zen_draw_label(TEXT_CASE_SENSITIVE, 'locate-cs', 'class="control-label col-sm-3"'); ?>
          <div class="col-sm-9 col-md-6">
            <div class="checkbox">
              <label><?php echo zen_draw_checkbox_field('case_sensitive', true, $case_sensitive, '', 'id="locate-cs"'); ?></label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-12 text-right">
            <button type="submit" class="btn btn-primary"><?php echo TEXT_BUTTON_SEARCH; ?></button>
            <input type="button" class="btn btn-primary" value="<?php echo TEXT_BUTTON_REGEX_SEARCH; ?>" onClick="document.locate_all_files.action = '<?php echo zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT, 'action=locate_all_files&m=r') ?>'; document.locate_all_files.submit();" title="<?php echo TEXT_BUTTON_REGEX_SEARCH_ALT; ?>">
            <button class="btn btn-primary" title="<?php echo TEXT_RESET_BUTTON_ALT; ?>" onClick="document.locate_all_files.action = '<?php echo zen_href_link(FILENAME_DEVELOPERS_TOOL_KIT); ?>'; document.locate_all_files.submit();"><?php echo SEARCH_CFG_KEYS_FORM_BUTTON_RESET; ?></button>
          </div>
        </div>
        <?php echo '</form>'; ?>
        <!-- eof: Locate all files -->
      <?php } ?>
      <div class="row"><?php echo zen_draw_separator(); ?></div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
