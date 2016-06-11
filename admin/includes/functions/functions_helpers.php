<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.6.0 $
 */

/**
   * helper function to access language::available_languages object
   * @return array
   */
  function zen_get_languages() {
    global $lng;

    if (!isset($lng)) {
      $lng = new language();
    }

    return $lng->get_available_languages();
  }

  /**
   * @param $language_id
   * @return array
   */
  function zen_get_languages_list() {
    $languages = zen_get_languages();
    $languages = array_reverse($languages);
    $languageList = [];
    $languageList[] = array('id'=>'', 'text' => TEXT_ALL);
    foreach($languages as $language) {
      $languageList[] = array('id'=>$language['id'], 'text' => $language['name']);
    }
    return $languageList;
  }


  /**
   * build a list of directories in a specified parent folder
   * (formatted in id/text pairs for SELECT boxes)
   *
   * @todo convert to a directory-iterator instead
   * @todo - this will be deprecated after converting remaining admin pages to LEAD format
   *
   * @return array (id/text pairs)
   */
  function zen_build_subdirectories_array($parent_folder = '', $default_text = 'Main Directory') {
    if ($parent_folder == '') $parent_folder = DIR_FS_CATALOG_IMAGES;
    $dir_info[] = array('id' => '', 'text' => $default_text);

    $dir = @dir($parent_folder);
    while ($file = $dir->read()) {
      if (is_dir($parent_folder . $file) && $file != "." && $file != "..") {
        $dir_info[] = array('id' => $file . '/', 'text' => $file);
      }
    }
    $dir->close();
    sort($dir_info);
    return $dir_info;
  }



////
// Sets timeout for the current script.
  function zen_set_time_limit($limit) {
    @set_time_limit($limit);
  }


  function zen_get_file_permissions($mode) {
// determine type
    if ( ($mode & 0xC000) == 0xC000) { // unix domain socket
      $type = 's';
    } elseif ( ($mode & 0x4000) == 0x4000) { // directory
      $type = 'd';
    } elseif ( ($mode & 0xA000) == 0xA000) { // symbolic link
      $type = 'l';
    } elseif ( ($mode & 0x8000) == 0x8000) { // regular file
      $type = '-';
    } elseif ( ($mode & 0x6000) == 0x6000) { //bBlock special file
      $type = 'b';
    } elseif ( ($mode & 0x2000) == 0x2000) { // character special file
      $type = 'c';
    } elseif ( ($mode & 0x1000) == 0x1000) { // named pipe
      $type = 'p';
    } else { // unknown
      $type = '?';
    }

// determine permissions
    $owner['read']    = ($mode & 00400) ? 'r' : '-';
    $owner['write']   = ($mode & 00200) ? 'w' : '-';
    $owner['execute'] = ($mode & 00100) ? 'x' : '-';
    $group['read']    = ($mode & 00040) ? 'r' : '-';
    $group['write']   = ($mode & 00020) ? 'w' : '-';
    $group['execute'] = ($mode & 00010) ? 'x' : '-';
    $world['read']    = ($mode & 00004) ? 'r' : '-';
    $world['write']   = ($mode & 00002) ? 'w' : '-';
    $world['execute'] = ($mode & 00001) ? 'x' : '-';

// adjust for SUID, SGID and sticky bit
    if ($mode & 0x800 ) $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x400 ) $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
    if ($mode & 0x200 ) $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';

    return $type .
           $owner['read'] . $owner['write'] . $owner['execute'] .
           $group['read'] . $group['write'] . $group['execute'] .
           $world['read'] . $world['write'] . $world['execute'];
  }

  function zen_count_days($start_date, $end_date, $lookup = 'm') {
    if ($lookup == 'd') {
    // Returns number of days
      $start_datetime = gmmktime (0, 0, 0, substr ($start_date, 5, 2), substr ($start_date, 8, 2), substr ($start_date, 0, 4));
      $end_datetime = gmmktime (0, 0, 0, substr ($end_date, 5, 2), substr ($end_date, 8, 2), substr ($end_date, 0, 4));
      $days = (($end_datetime - $start_datetime) / 86400) + 1;
      $d = $days % 7;
      $w = date("w", $start_datetime);
      $result = floor ($days / 7) * 5;
      $counter = $result + $d - (($d + $w) >= 7) - (($d + $w) >= 8) - ($w == 0);
    }
    if ($lookup == 'm') {
    // Returns whole-month-count between two dates
    // courtesy of websafe<at>partybitchez<dot>org
      $start_date_unixtimestamp = strtotime($start_date);
      $start_date_month = date("m", $start_date_unixtimestamp);
      $end_date_unixtimestamp = strtotime($end_date);
      $end_date_month = date("m", $end_date_unixtimestamp);
      $calculated_date_unixtimestamp = $start_date_unixtimestamp;
      $counter=0;
      while ($calculated_date_unixtimestamp < $end_date_unixtimestamp) {
        $counter++;
        $calculated_date_unixtimestamp = strtotime($start_date . " +{$counter} months");
      }
      if ( ($counter==1) && ($end_date_month==$start_date_month)) $counter=($counter-1);
    }
    return $counter;
  }


////
// Retreive server information
  function zen_get_system_information() {
    global $db;

    // determine database size stats
    $indsize = 0;
    $datsize = 0;
    $result = $db->Execute("SHOW TABLE STATUS" . (DB_PREFIX == '' ? '' : " LIKE '" . str_replace('_', '\_', DB_PREFIX) . "%'"));
    while (!$result->EOF) {
      $datsize += $result->fields['Data_length'];
      $indsize += $result->fields['Index_length'];
      $result->MoveNext();
    }

    $strictmysql = false;
    $mysql_mode = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'sql\_mode'");
    if (!$result->EOF) {
      $mysql_mode = $result->fields['Value'];
      if (strstr($result->fields['Value'], 'strict_')) $strictmysql = true;
    }
    $mysql_slow_query_log_status = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'slow\_query\_log'");
    if (!$result->EOF) {
      $mysql_slow_query_log_status = $result->fields['Value'];
    }
    $mysql_slow_query_log_file = '';
    $result = $db->Execute("SHOW VARIABLES LIKE 'slow\_query\_log\_file'");
    if (!$result->EOF) {
      $mysql_slow_query_log_file = $result->fields['Value'];
    }
    $result = $db->Execute("select now() as datetime");
    $mysql_date = $result->fields['datetime'];

    $errnum = 0;
    $system = $host = $kernel = $output = '';
    list($system, $host, $kernel) = array('', $_SERVER['SERVER_NAME'], php_uname());
    $uptime = (DISPLAY_SERVER_UPTIME == 'true') ? 'Unsupported' : 'Disabled/Unavailable';

    // check to see if "exec()" is disabled in PHP -- if not, get additional info via command line
    $php_disabled_functions = '';
    $exec_disabled = false;
    $php_disabled_functions = @ini_get("disable_functions");
    if ($php_disabled_functions != '') {
      if (in_array('exec', preg_split('/,/', str_replace(' ', '', $php_disabled_functions)))) {
        $exec_disabled = true;
      }
    }
    if (!$exec_disabled) {
      @exec('uname -a 2>&1', $output, $errnum);
      if ($errnum == 0 && sizeof($output)) list($system, $host, $kernel) = preg_split('/[\s,]+/', $output[0], 5);
      $output = '';
      if (DISPLAY_SERVER_UPTIME == 'true') {
        @exec('uptime 2>&1', $output, $errnum);
        if ($errnum == 0) {
          $uptime = $output[0];
        }
      }
    }

    return array('date' => zen_datetime_short(date('Y-m-d H:i:s')),
                 'system' => $system,
                 'kernel' => $kernel,
                 'host' => $host,
                 'ip' => gethostbyname($host),
                 'uptime' => $uptime,
                 'http_server' => $_SERVER['SERVER_SOFTWARE'],
                 'php' => PHP_VERSION,
                 'zend' => (function_exists('zend_version') ? zend_version() : ''),
                 'db_server' => DB_SERVER,
                 'db_ip' => gethostbyname(DB_SERVER),
                 'db_version' => 'MySQL ' . $db->get_server_info(),
                 'db_date' => zen_datetime_short($mysql_date),
                 'php_memlimit' => @ini_get('memory_limit'),
                 'php_file_uploads' => strtolower(@ini_get('file_uploads')),
                 'php_uploadmaxsize' => @ini_get('upload_max_filesize'),
                 'php_postmaxsize' => @ini_get('post_max_size'),
                 'database_size' => $datsize,
                 'index_size' => $indsize,
                 'mysql_strict_mode' => $strictmysql,
                 'mysql_mode' => $mysql_mode,
                 'mysql_slow_query_log_status' => $mysql_slow_query_log_status,
                 'mysql_slow_query_log_file' => $mysql_slow_query_log_file,
                 );
  }

/**
 * Perform an array multisort, based on 1 or 2 columns being passed
 * (defaults to sorting by first column ascendingly then second column ascendingly unless otherwise specified)
 *
 * @param $data        multidimensional array to be sorted
 * @param $columnName1 string representing the named column to sort by as first criteria
 * @param $order1      either SORT_ASC or SORT_DESC (default SORT_ASC)
 * @param $columnName2 string representing named column as second criteria
 * @param $order2      either SORT_ASC or SORT_DESC (default SORT_ASC)
 * @return array   Original array sorted as specified
 */
function zen_sort_array($data, $columnName1 = '', $order1 = SORT_ASC, $columnName2 = '', $order2 = SORT_ASC)
{
  // simple validations
  $keys = array_keys($data);
  if ($columnName1 == '') {
    $columnName1 = $keys[0];
  }
  if (!in_array($order1, array(SORT_ASC, SORT_DESC))) $order1=SORT_ASC;
  if ($columnName2 == '') {
    $columnName2 = $keys[1];
  }
  if (!in_array($order2, array(SORT_ASC, SORT_DESC))) $order2=SORT_ASC;

  // prepare sub-arrays for aiding in sorting
  foreach($data as $key=>$val)
  {
    $sort1[] = $val[$columnName1];
    $sort2[] = $val[$columnName2];
  }
  // do actual sort based on specified fields.
  array_multisort($sort1, $order1, $sort2, $order2, $data);
  return $data;
}


  function zen_call_function($function, $parameter, $object = '') {
    if ($object == '') {
      return call_user_func($function, $parameter);
    } else {
      return call_user_func(array($object, $function), $parameter);
    }
  }

/**
 * Lookup session-specific language file, and load
 *
 * @param string $file filename of language file
 */
function zen_load_language_file($file) {
    if (file_exists(DIR_WS_LANGUAGES . $_SESSION ['language'] . '/' . $file)) {
        include (DIR_WS_LANGUAGES . $_SESSION ['language'] . '/' . $file);
        return;
    }
    if (file_exists(DIR_WS_LANGUAGES . 'english' . '/' . $file)) {
        include (DIR_WS_LANGUAGES . 'english' . '/' . $file);
    }
}

/**
 * Deletes a file/directory from the server (assuming PHP has permission to do so)
 */
  function zen_remove_file($source) {
    global $messageStack, $zen_remove_error;

    if (isset($zen_remove_error)) $zen_remove_error = false;

    if (is_dir($source)) {
      $dir = dir($source);
      while ($file = $dir->read()) {
        if ( ($file != '.') && ($file != '..') ) {
          if (is_writeable($source . '/' . $file)) {
            zen_remove_file($source . '/' . $file);
          } else {
            $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source . '/' . $file), 'error');
            $zen_remove_error = true;
          }
        }
      }
      $dir->close();

      if (is_writeable($source)) {
        rmdir($source);
        zen_record_admin_activity('Removed directory from server: [' . $source . ']', 'notice');
      } else {
        $messageStack->add(sprintf(ERROR_DIRECTORY_NOT_REMOVEABLE, $source), 'error');
        $zen_remove_error = true;
      }
    } else {
      if (is_writeable($source)) {
        unlink($source);
        zen_record_admin_activity('Deleted file from server: [' . $source . ']', 'notice');
      } else {
        $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source), 'error');
        $zen_remove_error = true;
      }
    }
  }


/**
 * Obtain a list of .log/.xml files from the /logs/ folder
 *
 * If $maxToList == 'count' then it returns the total number of files found
 * If an integer is passed, then an array of files is returned, including paths, filenames, and datetime details
 *
 * @param $maxToList mixed (integer or 'count')
 * @return array or integer
 *
 * inspired by log checking suggestion from Steve Sherratt (torvista)
 */
function get_logs_data($maxToList = 'count') {
  if (!defined('DIR_FS_LOGS')) define('DIR_FS_LOGS', DIR_FS_CATALOG . 'logs');
  $logs = array();
  $file = array();
  $i = 0;
  foreach(array(DIR_FS_LOGS) as $purgeFolder) {
    $sourcePurgeFolder = $purgeFolder; 
    $purgeFolder = rtrim($purgeFolder, '/');
    if (!file_exists($purgeFolder) || !is_dir($purgeFolder)) continue;

    $dir = dir($purgeFolder);
    while ($logfile = $dir->read()) {
      if (substr($logfile, 0, 1) == '.') continue;
      if (!preg_match('/.*(\.log|\.xml)$/', $logfile)) continue; // xml allows for usps debug

      if ($maxToList != 'count') {
        $filename = $purgeFolder . '/' . $logfile;
        $logs[$i]['path'] = $purgeFolder . "/";
        $logs[$i]['filename'] = $logfile;
        $logs[$i]['filesize'] = @filesize($filename);
        $logs[$i]['unixtime'] = @filemtime($filename);
        $logs[$i]['datetime'] = strftime(DATE_TIME_FORMAT, $logs[$i]['unixtime']);
      }
      $i++;
      if ($maxToList != 'count' && $i >= $maxToList) break;
    }
    $dir->close();
    unset($dir);
  }

  if ($maxToList == 'count') return $i;

  $logs = zen_sort_array($logs, 'unixtime', SORT_DESC);
  return $logs;
}


