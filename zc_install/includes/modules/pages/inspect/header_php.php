<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Fri Sep 7 13:41:39 2012 -0400 Modified in v1.5.1 $
 *
 * @TODO - http://dev.mysql.com/doc/refman/5.0/en/user-resources.html
 */

  $advanced_mode = (isset($_GET['adv'])) ? true : false;

  $zen_cart_previous_version_installed = false;
  $zen_cart_database_connect_OK = false;
  $zen_cart_allow_database_upgrade = false;
  $zdb_sql_cache = '';
  $configWriteOverride = (isset($_GET['overrideconfig'])) ? true : false;
  if (file_exists('../includes/configure.php')) {
    // read the existing configure.php file(s) to get values and guess whether it looks like a valid prior install
    if (zen_read_config_value('HTTP_SERVER', FALSE)      == 'http://localhost') $zen_cart_previous_version_installed = 'maybe';
    if (zen_read_config_value('DIR_FS_CATALOG', FALSE)   == '/var/www/html/') $zen_cart_previous_version_installed = 'maybe';
    if (zen_read_config_value('HTTP_SERVER', FALSE)      != '' ) $zen_cart_previous_version_installed = true;
    if (zen_read_config_value('DIR_WS_CLASSES', FALSE)   != '' ) $zen_cart_previous_version_installed = true;
    if (zen_read_config_value('DIR_FS_CATALOG', FALSE)   != '' ) $zen_cart_previous_version_installed = true;
    if (strpos(zen_read_config_value('DIR_FS_SQL_CACHE', FALSE),'/path/to/')>0) $zen_cart_previous_version_installed = false;
    if (zen_read_config_value('DB_DATABASE', FALSE)      == '' ) $zen_cart_previous_version_installed = false;

    //read the configure.php file and look for hints that it's just a copy of dist-configure.php
    $lines = file('../includes/configure.php');
    foreach ($lines as $line) {
      if (substr_count($line,'dist-configure.php') > 0) $zen_cart_previous_version_installed = false;
    } //end foreach

    $zdb_type     = zen_read_config_value('DB_TYPE', FALSE);
    $zdb_prefix   = zen_read_config_value('DB_PREFIX', FALSE);
    $zdb_coll     = zen_read_config_value('DB_CHARSET', FALSE);
    if ($zdb_coll != 'latin1') $zdb_coll = 'utf8';
    $zdb_server   = zen_read_config_value('DB_SERVER', FALSE);
    $zdb_user     = zen_read_config_value('DB_SERVER_USERNAME', FALSE);
    $zdb_pwd      = zen_read_config_value('DB_SERVER_PASSWORD', FALSE);
    $zdb_name     = zen_read_config_value('DB_DATABASE', FALSE);
    $zdb_sql_cache= zen_read_config_value('DIR_FS_SQL_CACHE', FALSE);
    if (strpos($zdb_sql_cache,'/path/to/')>0) $zdb_sql_cache=''; // /path/to/ comes from dist-configure.php. Invalid, thus make null.

    if (ZC_UPG_DEBUG==true) {
      echo 'db-type=' . $zdb_type . '<br>';
      echo 'db-prefix=' . $zdb_prefix . '<br>';
      echo 'db-host=' . $zdb_server . '<br>';
      echo 'db-name=' . $zdb_name . '<br>';
      echo 'db-user=' . $zdb_user . '<br>';
      echo 'cache_folder=' . $zdb_sql_cache . '<br>';
    }

    define('DIR_FS_CATALOG', '../');
    define('DB_TYPE', 'mysql');
    define('SQL_CACHE_METHOD', 'none');
    if ($zdb_type!='' && $zdb_name !='') {
      // now check database connectivity
      require('../includes/' . 'classes/db/' . $zdb_type . '/query_factory.php');

      $zc_install->functionExists($zdb_type, '', '');
      $zc_install->dbConnect($zdb_type, $zdb_server, $zdb_name, $zdb_user, $zdb_pwd, '', '');
      if ($zc_install->error == false) $zen_cart_database_connect_OK = true;
      if ($zc_install->error == true) {
        $zen_cart_previous_version_installed = false;
        if (ZC_UPG_DEBUG==true) echo 'db-connection failed using the credentials supplied';
        if (ZC_UPG_DEBUG==true) $zc_install->logDetails('db-connection failed using the credentials supplied');
      }

      //reset error-check class after connection attempt
      $zc_install->error = false;
      $zc_install->fatal_error = false;
      $zc_install->error_list = array();
    } //endif check for db_type and db_name defined

    if ($zen_cart_database_connect_OK) { #1
      //open database connection to run queries against it
      $db_test = new queryFactory;
      $db_test->Connect($zdb_server, $zdb_user, $zdb_pwd, $zdb_name) or $zen_cart_database_connect_OK = false;

      if ($zen_cart_database_connect_OK) { //#2  This check is done again just in case connect fails on previous line
        //set database table prefix
        define('DB_PREFIX',$zdb_prefix);
        // Now check the database for what version it's at, if found
        require('includes/classes/class.installer_version_manager.php');
        $dbinfo = new versionManager;

        // Check to see whether we should offer the option to upgrade "database only", rather than rebuild configure.php files too.
        // For v1.2.1, the only check we need is whether we're at v1.2.0 already or not.
        // Future versions may require more extensive checking if the core configure.php files change.
        // NOTE: This flag is also used to determine whether or not we prevent moving to next screen if the configure.php files are not writable
        if ($dbinfo->found_version >= '1.2.0') {
          $zen_cart_allow_database_upgrade=true;
        }

      } //endif $zen_cart_database_connect_OK #2
    } //endif $zen_cart_database_connect_OK
  } else {
    $zen_cart_previous_version_installed = false;
    if (ZC_UPG_DEBUG==true) echo 'NOTE: Did not find existing configure.php file. Assuming fresh install.';
    if (ZC_UPG_DEBUG==true) $zc_install->logDetails('NOTE: Did not find existing configure.php file. Assuming fresh install.');
  } //endif exists configure.php

//  if ($check_count > 1) $zen_cart_version_already_installed = false; // if more than one test failed, it must be a fresh install

  if ($zen_cart_previous_version_installed == true && $zen_cart_database_connect_OK == true) {
    $is_upgradable = true;

    if ($dbinfo->zdb_configuration_table_found) {
      $zdb_version_message = sprintf(LABEL_PREVIOUS_VERSION_NUMBER, $dbinfo->found_version);
    } else {
      $zdb_version_message = LABEL_PREVIOUS_VERSION_NUMBER_UNKNOWN;
    }
  }



///////////////////////////////////
// Run System Pre-Flight Check:
///////////////////////////////////
  $status_check = array();
  $status_check2 = array();
  $dir_fs_www_root = $zc_install->detectDocumentRoot();
  //Structure is this:
  //$status_check[] = array('Importance' => '', 'Title' => '', 'Status' => '', 'Class' => '', 'HelpURL' =>'', 'HelpLabel'=>'');

  // Check for new ZC version available
  $new_version = $zc_install->checkIsZCVersionCurrent();
  if ($new_version != TEXT_VERSION_CHECK_CURRENT || $advanced_mode) {
    $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_ZC_VERSION_CHECK, 'Status' => $new_version, 'Class' => ($new_version != TEXT_VERSION_CHECK_CURRENT ? 'WARN' : 'NA'), 'HelpURL' =>'', 'HelpLabel'=>'');
  }

  //WebServer OS as reported by env check
  $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_WEBSERVER, 'Status' => getenv("SERVER_SOFTWARE"), 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');

  //General info
  $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_HTTP_HOST, 'Status' => $_SERVER['HTTP_HOST'], 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');
  $path_trans = @$_SERVER['PATH_TRANSLATED'];
  $path_trans_display = $path_trans;
  if (empty($path_trans)) {
    $path_trans_display = $_SERVER['SCRIPT_FILENAME'] . '(SCRIPT_FILENAME)';
    $path_trans = $_SERVER['SCRIPT_FILENAME'];
  }
  $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_PATH_TRANLSATED, 'Status' => $path_trans_display, 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');

  $real_path = realpath(dirname(basename($PHP_SELF)).'/..');
  $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_REALPATH, 'Status' => $real_path, 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');

  //get list of disabled functions
  $disabled_funcs = ini_get("disable_functions");
  if (!strstr($disabled_funcs,'disk_free_space')) {
    // get free space on disk
    $disk_freespaceGB=round(@disk_free_space($path_trans)/1024/1024/1024,2);
    $disk_freespaceMB=round(@disk_free_space($path_trans)/1024/1024,2);
    if ($disk_freespaceGB >0) $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_DISK_FREE_SPACE, 'Status' => $disk_freespaceGB . ' GB' . (($disk_freespaceGB==0) ? ' (can be ignored)' : ''), 'Class' => ($disk_freespaceMB<1000 && $disk_freespaceGB != 0)?'FAIL':'NA', 'HelpURL' =>'', 'HelpLabel'=>'');
  }

  // Operating System as reported by PHP:
  $status_check[] = array('Importance' => 'Optional', 'Title' => LABEL_PHP_OS, 'Status' => PHP_OS, 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');

  //PHP mode (module, cgi, etc)
  $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_PHP_API_MODE, 'Status' => @php_sapi_name(), 'Class' => (/*@strstr(php_sapi_name(),'cgi') ? 'WARN' :*/ 'NA'), 'HelpURL' =>ERROR_CODE_PHP_AS_CGI, 'HelpLabel'=>ERROR_TEXT_PHP_AS_CGI);

  //Set Time Limit setting
  $set_time_limit = ini_get("max_execution_time");
  $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_SET_TIME_LIMIT, 'Status' => $set_time_limit, 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');

  //get list of disabled functions
  if (!zen_not_null($disabled_funcs)) $disabled_funcs = ini_get("disable_functions");
  if (zen_not_null($disabled_funcs)) $status_check[] = array('Importance' => 'Recommended', 'Title' => LABEL_DISABLED_FUNCTIONS, 'Status' => $disabled_funcs, 'Class' => (@substr_count($disabled_funcs,'set_time_limit') ? 'WARN' : 'NA'), 'HelpURL' =>ERROR_CODE_DISABLE_FUNCTIONS, ERROR_TEXT_DISABLE_FUNCTIONS);

  if (version_compare(PHP_VERSION, 5.4, '<')) {
    // Check Register Globals
    $register_globals = ini_get("register_globals");
    if ($register_globals == '' || $register_globals =='0' || strtoupper($register_globals) =='OFF') {
      $register_globals = OFF; // Having register globals "off" is more secure
      $this_class='OK';
    } else {
      $register_globals = "<span class='errors'>".ON.'</span>';
      $this_class='WARN';
    }
    $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_REGISTER_GLOBALS, 'Status' => $register_globals, 'Class' => $this_class, 'HelpURL' =>ERROR_CODE_REGISTER_GLOBALS_ON, 'HelpLabel'=>ERROR_TEXT_REGISTER_GLOBALS_ON);
  }
  //Check MySQL version
  $mysql_support = (function_exists( 'mysql_connect' )) ? ON : OFF;
  $mysql_version = (function_exists('mysql_get_server_info')) ? @mysql_get_server_info() : UNKNOWN;
  $mysql_version = ($mysql_version == '') ? UNKNOWN : $mysql_version ;
  //if (is_object($db_test)) $mysql_qry=$db_test->get_server_info();
  $mysql_ver_class = ($mysql_version<'4.1.0') ? 'FAIL' : 'OK';
  $mysql_ver_class = ($mysql_version == UNKNOWN || $mysql_version > '5.6') ? 'WARN' : $mysql_ver_class;

  $status_check[] = array('Importance' => 'Critical', 'Title' => LABEL_MYSQL_AVAILABLE, 'Status' => $mysql_support, 'Class' => ($mysql_support==ON) ? 'OK' : 'FAIL', 'HelpURL' =>ERROR_CODE_DB_NOTSUPPORTED, 'HelpLabel'=>ERROR_TEXT_DB_NOTSUPPORTED);
  if ($mysql_version != UNKNOWN || ($mysql_version == UNKNOWN && $advanced_mode)) $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_MYSQL_VER, 'Status' => $mysql_version, 'Class' => $mysql_ver_class, 'HelpURL' =>($mysql_version > '5.6' ? ERROR_CODE_DB_MYSQL5 : ERROR_CODE_DB_VER_UNKNOWN), 'HelpLabel'=>($mysql_version > '5.6' ? ERROR_TEXT_DB_MYSQL5 : ERROR_TEXT_DB_VER_UNKNOWN) );

  //DB Privileges
if (false) { // DISABLED THIS CODEBLOCK FOR NOW....
  if ($zen_cart_database_connect_OK) {
    $zdb_privs_list = zen_check_database_privs('','',true);
    $privs_array = explode('|||',$zdb_privs_list);
    $db_priv_ok = $privs_array[0];
    $zdb_privs =  $privs_array[1];
    if (ZC_UPG_DEBUG==true) echo 'privs_list_to_parse='.$db_priv_ok.'|||'.$zdb_privs;
    //  $granted_db = str_replace('`','',substr($zdb_privs,strpos($zdb_privs,' ON ')+4) );
    //  $db_priv_ok = ($granted_db == '*.*' || $granted_db==DB_DATABASE.'.*' || $granted_db==DB_DATABASE.'.'.$table) ? true : false;
    //  $zdb_privs = substr($zdb_privs,0,strpos($zdb_privs,' ON ')); //remove the "ON..." portion
    $zdb_privs_class='FAIL';
    $privs_matched=0;
    if (substr_count($zdb_privs,'ALL PRIVILEGES')>0) $zdb_privs_class='OK';
    foreach(array('SELECT','INSERT','UPDATE','DELETE','CREATE','ALTER','INDEX','DROP') as $value) {
      if (in_array($value,explode(', ',$zdb_privs))) {
        $privs_matched++;
        $privs_found_text .= $value .', ';
      }
    }
    if ($privs_matched==8 && $db_priv_ok) $zdb_privs_class='OK';
    if (substr_count($zdb_privs,'USAGE') >0) $zdb_privs_class='NA';
    if (!zen_not_null($zdb_privs)) {
      $privs_found_text = UNKNOWN;
      $zdb_privs_class='NA';
    }
    if ($privs_found_text=='') $privs_found_text = $zdb_privs;
    if ($zdb_privs == 'Not Checked') {
      $privs_found_text = $zdb_privs;
      $zdb_privs_class='NA';
    }
    $status_check[] = array('Importance' => 'Critical', 'Title' => LABEL_DB_PRIVS, 'Status' => str_replace(',  ',' ',$privs_found_text.' '), 'Class' => $zdb_privs_class, 'HelpURL' =>ERROR_CODE_DB_PRIVS, 'HelpLabel'=>ERROR_TEXT_DB_PRIVS);
  }
}

  //PHP Version Check
  $err_text = '';
  $err_code = '';
  $php_ver = '';

  if ($zc_install->test_php_version('<', "5.2.3", ERROR_TEXT_PHP_OLD_VERSION, ERROR_CODE_PHP_OLD_VERSION, ($zen_cart_allow_database_upgrade == false) )) {
    if ($zen_cart_allow_database_upgrade == false) {
      $php_ver = '<span class="errors">'.$zc_install->php_version.' {*** '. MUST_UPGRADE . ' ***}</span>';
      $this_class = 'FAIL';
    } else {
      $php_ver = '<span class="errors">'.$zc_install->php_version.' {*** '. SHOULD_UPGRADE . ' ***}</span>';
      $this_class = 'WARN';
    }
  } else {
    $php_ver = $zc_install->php_version;
    $this_class = 'OK';
  }
  
  if (version_compare(PHP_VERSION, 5.5, '>=')) {
    $php_ver = $zc_install->php_version;
    $this_class = 'WARN';
    $err_text = 'This ZC version is not yet tested with this version of PHP.';
    $err_code = '';
  }
  $status_check[] = array('Importance' => 'Critical', 'Title' => LABEL_PHP_VER, 'Status' => $php_ver, 'Class' => $this_class, 'HelpURL' =>$err_code, 'HelpLabel'=>$err_text);

  // SAFE MODE check
  if (version_compare(PHP_VERSION, 5.4, '<')) {
    $safe_mode = (ini_get("safe_mode")) ? "<span class='errors'>" . ON . '</span>' : OFF;
    $status_check[] = array('Importance' => 'Critical', 'Title' => LABEL_SAFE_MODE, 'Status' => $safe_mode, 'Class' => ($safe_mode==OFF) ? 'OK' : 'FAIL', 'HelpURL' =>ERROR_CODE_SAFE_MODE_ON, 'HelpLabel'=>ERROR_TEXT_SAFE_MODE_ON);
  }

  //PHP support for Sessions check
  $php_ext_sessions = (@extension_loaded('session')) ? ON : OFF;
  $status_check[] = array('Importance' => 'Critical', 'Title' => LABEL_PHP_EXT_SESSIONS, 'Status' => $php_ext_sessions, 'Class' => ($php_ext_sessions==ON) ? 'OK' : 'FAIL', 'HelpURL' =>ERROR_CODE_PHP_SESSION_SUPPORT, 'HelpLabel'=>ERROR_TEXT_PHP_SESSION_SUPPORT);

  //session.auto_start check
  $php_session_auto = (ini_get('session.auto_start')) ? ON : OFF;
  $status_check[] = array('Importance' => 'Critical', 'Title' => LABEL_PHP_SESSION_AUTOSTART, 'Status' => $php_session_auto, 'Class' => ($php_session_auto==ON) ? 'FAIL' : 'OK', 'HelpURL' =>ERROR_CODE_PHP_SESSION_AUTOSTART, 'HelpLabel'=>ERROR_TEXT_PHP_SESSION_AUTOSTART);

  //session.trans_sid check
  $php_session_trans_sid = (ini_get('session.use_trans_sid')) ? ON : OFF;
  $status_check[] = array('Importance' => 'Critical', 'Title' => LABEL_PHP_SESSION_TRANS_SID, 'Status' => $php_session_trans_sid, 'Class' => ($php_session_trans_sid==ON) ? 'FAIL' : 'OK', 'HelpURL' =>ERROR_CODE_PHP_SESSION_TRANS_SID, 'HelpLabel'=>ERROR_TEXT_PHP_SESSION_TRANS_SID);

  // Check for 'tmp' folder for file-based caching. This checks numerous places, and tests actual writing of a file to those folders.
  $session_save_path = (@ini_get('session.save_path')) ? ini_get('session.save_path') : UNKNOWN;
  $session_save_path_writable = (@is_writable( $session_save_path )) ? WRITABLE : UNWRITABLE ;
  $status_check2[3] = array('Importance' => 'Optional', 'Title' => LABEL_PHP_EXT_SAVE_PATH, 'Status' => $session_save_path . ($session_save_path != UNKNOWN ? '&nbsp;&nbsp;-->' . $session_save_path_writable : ''), 'Class' => ($session_save_path_writable ==WRITABLE || $session_save_path == UNKNOWN) ? 'OK' : 'WARN', 'HelpURL' =>ERROR_CODE_SESSION_SAVE_PATH, 'HelpLabel'=>ERROR_TEXT_SESSION_SAVE_PATH);

  //check various options for cache storage:
  //foreach (array(@ini_get("session.save_path"), '/tmp', '/var/lib/php/session', $dir_fs_www_root . '/tmp', $dir_fs_www_root . '/cache', 'c:/php/tmp', 'c:/php/sessiondata', 'c:/windows/temp', 'c:/temp') as $cache_test) {
  $suggested_cache = '';
  foreach (array($dir_fs_www_root . '/cache') as $cache_test) {
    if (is_dir($cache_test) && @is_writable($cache_test) ) {  // does it exist?  Is is writable?
      $filename = $cache_test . '/zentest.tst';
      $fp = @fopen($filename,"w");  // if this fails, then the file is not really writable
      @fwrite($fp,'cache test');
      @fclose($fp);
      $fp = @fopen($filename,"rb");  // read it back to be sure it's ok
      $contents = @fread($fp, filesize($filename));
      @fclose($fp);
      @unlink($filename);
      if ($contents == 'cache test') {
        $suggested_cache=$cache_test;  // if contents were read ok, then path is OK
        break;
      }
    }
  }
  $sugg_cache_class = 'OK'; //default
  $sugg_cache_code = '';
  $sugg_cache_text = '';
  if ($suggested_cache == '') {
    $suggested_cache = $dir_fs_www_root . '/cache';  //suggest to use catalog path if no alternative was found usable
    $sugg_cache_class = 'WARN';
    $sugg_cache_code = ERROR_CODE_CACHE_CUSTOM_NEEDED;
    $sugg_cache_text = '<br />'.ERROR_TEXT_CACHE_CUSTOM_NEEDED; // the <br> tag is for line-wrap for a long message displayed
  } elseif (!is_dir($suggested_cache)) {
    $sugg_cache_code = ERROR_CODE_CACHE_DIR_ISDIR;
    $sugg_cache_text = ERROR_TEXT_CACHE_DIR_ISDIR;
    $sugg_cache_class = 'WARN';
  } elseif (!@is_writable($suggested_cache)) {
    $sugg_cache_code = ERROR_CODE_CACHE_DIR_ISWRITABLE;
    $sugg_cache_text = ERROR_TEXT_CACHE_DIR_ISWRITABLE;
    $sugg_cache_class = 'WARN';
  }//endif $suggested_cache
  $zc_install->setConfigKey('DIR_FS_SQL_CACHE', $suggested_cache);

  $zdb_sql_cache_writable = (@is_writable($zdb_sql_cache)) ? WRITABLE : UNWRITABLE;
  if ($zdb_sql_cache != '') $status_check[] = array('Importance' => 'Recommended', 'Title' => LABEL_CURRENT_CACHE_PATH, 'Status' => $zdb_sql_cache . '&nbsp;&nbsp;-->' . $zdb_sql_cache_writable , 'Class' => ($zdb_sql_cache_writable ==WRITABLE) ? 'OK' : 'WARN', 'HelpURL' =>ERROR_CODE_CACHE_DIR_ISWRITEABLE, 'HelpLabel'=>ERROR_TEXT_CACHE_DIR_ISWRITEABLE);
  $status_check[] = array('Importance' => 'Recommended', 'Title' => LABEL_SUGGESTED_CACHE_PATH, 'Status' => $suggested_cache, 'Class' => $sugg_cache_class, 'HelpURL' =>$sugg_cache_code, 'HelpLabel'=>$sugg_cache_text);

if (version_compare(PHP_VERSION, 5.4, '<')) {
  //PHP MagicQuotesRuntime
  $status_check[] = array('Importance' => 'Recommended', 'Title' => LABEL_PHP_MAG_QT_RUN, 'Status' => $php_magic_quotes_runtime , 'Class' => ($php_magic_quotes_runtime=='OFF')?'OK':'FAIL', 'HelpURL' =>ERROR_CODE_MAGIC_QUOTES_RUNTIME, 'HelpLabel'=>ERROR_TEXT_MAGIC_QUOTES_RUNTIME);
  //PHP MagicQuotesSybase
  $status_check[] = array('Importance' => 'Recommended', 'Title' => LABEL_PHP_MAG_QT_SYBASE, 'Status' => $php_magic_quotes_sybase , 'Class' => ($php_magic_quotes_sybase=='OFF')?'OK':'FAIL', 'HelpURL' =>ERROR_CODE_MAGIC_QUOTES_SYBASE, 'HelpLabel'=>ERROR_TEXT_MAGIC_QUOTES_SYBASE);
}
  //PHP GD support check
  $php_ext_gd =       (@extension_loaded('gd'))      ? ON : OFF;
  $status_check[] = array('Importance' => 'Optional', 'Title' => LABEL_PHP_EXT_GD, 'Status' => $php_ext_gd , 'Class' => ($php_ext_gd==ON)?'OK':'WARN', 'HelpURL' =>ERROR_CODE_GD_SUPPORT, 'HelpLabel'=>ERROR_TEXT_GD_SUPPORT);
  if ($php_ext_gd == ON) {
    $gd_info = (function_exists('gd_info')) ? @gd_info() : array('GD Version' => 'UNKNOWN');
  $gd_ver = 'GD ' . $gd_info['GD Version'];
  $status_check[] = array('Importance' => 'Optional', 'Title' => LABEL_GD_VER, 'Status' => $gd_ver , 'Class' => ($php_ext_gd==ON && strstr($gd_ver,'2.') )?'OK':'WARN', 'HelpURL' =>ERROR_CODE_GD_SUPPORT, 'HelpLabel'=>ERROR_TEXT_GD_SUPPORT);
  }

 //check for zLib Compression Support
  $php_ext_zlib =     (@extension_loaded('zlib'))    ? ON : OFF;
  $status_check[] = array('Importance' => '', 'Title' => LABEL_PHP_EXT_ZLIB, 'Status' => $php_ext_zlib, 'Class' => ($php_ext_zlib==ON)?'OK':'WARN', 'HelpURL' =>'', 'HelpLabel'=>'');

  //Check for OpenSSL support (only relevant for Apache)
  $php_ext_openssl =  (@extension_loaded('openssl')) ? ON : OFF;
  if ($php_ext_openssl == ON) $status_check[] = array('Importance' => 'Optional', 'Title' => LABEL_PHP_EXT_OPENSSL, 'Status' => $php_ext_openssl, 'Class' => ($php_ext_openssl==ON)?'OK':'WARN', 'HelpURL' =>ERROR_CODE_OPENSSL_WARN, 'HelpLabel'=>ERROR_TEXT_OPENSSL_WARN);

  //Check for CURL support (ie: for payment/shipping gateways)
  //$php_ext_curl =     (function_exists('curl_init'))    ? ON : OFF;
  $php_ext_curl =     (@extension_loaded('curl'))    ? ON : OFF;
  $status_check[] = array('Importance' => 'Optional', 'Title' => LABEL_PHP_EXT_CURL, 'Status' => $php_ext_curl, 'Class' => ($php_ext_curl==ON)? 'OK' : 'WARN', 'HelpURL' =>ERROR_CODE_CURL_SUPPORT, 'HelpLabel'=>ERROR_TEXT_CURL_SUPPORT);

  // check for actual CURL operation
  $curl_nonssl_test = $zc_install->test_curl('NONSSL');
  $curl_ssl_test = $zc_install->test_curl('SSL');
  $status_check[] = array('Importance' => 'Optional', 'Title' => LABEL_CURL_NONSSL, 'Status' => $curl_nonssl_test, 'Class' => ($curl_nonssl_test == OKAY) ? 'OK' : 'WARN', 'HelpURL' =>ERROR_CODE_CURL_SUPPORT, 'HelpLabel'=>ERROR_TEXT_CURL_SUPPORT);
  $status_check[] = array('Importance' => 'Optional', 'Title' => LABEL_CURL_SSL, 'Status' => $curl_ssl_test, 'Class' => ($curl_ssl_test == OKAY) ? 'OK' : 'WARN', 'HelpURL' =>ERROR_CODE_CURL_SSL_PROBLEM, 'HelpLabel'=>ERROR_TEXT_CURL_SSL_PROBLEM);

  //Check for upload support built in to PHP
  $php_uploads =      (@ini_get('file_uploads')) ? ON : OFF;
  $status_check[] = array('Importance' => 'Optional', 'Title' => LABEL_PHP_UPLOAD_STATUS, 'Status' => $php_uploads . sprintf('&nbsp;&nbsp;  upload_max_filesize=%s;&nbsp;&nbsp;  post_max_size=%s',@ini_get('upload_max_filesize'), @ini_get('post_max_size')) , 'Class' => ($php_uploads==ON)?'OK':'WARN', 'HelpURL' =>ERROR_CODE_UPLOADS_DISABLED, 'HelpLabel'=>ERROR_TEXT_UPLOADS_DISABLED);

  //Upload TMP dir setting
  $upload_tmp_dir = ini_get("upload_tmp_dir");
  $status_check[] = array('Importance' => 'Info', 'Title' => LABEL_UPLOAD_TMP_DIR, 'Status' => $upload_tmp_dir, 'Class' => 'OK', 'HelpURL' =>'', 'HelpLabel'=>'');

  //htaccess check
  $testPath = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
  $testPath = 'http://' . substr($testPath, 0, strpos($testPath, '/zc_install')) . '/includes/filenames.php';
  if (function_exists('curl_init'))
  {
    $resultCurl = get_web_page($testPath);
  } else
  {
    $resultCurl = 'UNKNOWN';
  }
  $htaccessSupport = (isset($resultCurl['http_code']) && ($resultCurl['http_code'] == '403' || $resultCurl['http_code'] == '404')) ? ON : (($resultCurl == 'UNKNOWN') ? LABEL_COULD_NOT_TEST_HTACCESS : OFF);
  $status_check[] = array('Importance' => 'Optional', 'Title' => LABEL_HTACCESS_SUPPORT, 'Status' => $htaccessSupport, 'Class' => ($htaccessSupport==ON)?'OK':'WARN', 'HelpURL' =>'', 'HelpLabel'=>'');

  //Check for XML Support
  $xml_support = function_exists('xml_parser_create') ? ON : OFF;
  $status_check2[] = array('Importance' => 'Optional', 'Title' => LABEL_XML_SUPPORT, 'Status' => $xml_support, 'Class' => ($xml_support==ON)?'OK':'WARN', 'HelpURL' =>'', 'HelpLabel'=>'');

  // PHP output buffering (GZip) (PHP configuration)
  $php_buffer = (@ini_get("output_buffering"))   ? ON : OFF;
  $status_check2[] = array('Importance' => 'Optional', 'Title' => LABEL_GZIP, 'Status' => $php_buffer, 'Class' => ($php_buffer==ON)?'OK':'WARN', 'HelpURL' =>'', 'HelpLabel'=>'');

  //Check PostgreSQL availability
// turn off display of Postgres status until we support it again
  // $pg_support = (function_exists( 'pg_connect' )) ? ON : OFF;
  //$status_check2[] = array('Importance' => 'Optional', 'Title' => LABEL_POSTGRES_AVAILABLE, 'Status' => $pg_support, 'Class' => ($pg_support==ON) ? 'OK' : 'WARN', 'HelpURL' =>ERROR_CODE_DB_NOTSUPPORTED, 'HelpLabel'=>ERROR_TEXT_DB_NOTSUPPORTED);


  //OpenBaseDir setting
  // read restrictions, and check whether the working folder falls within the list of restricted areas
  $this_class = 'OK';
  if ($open_basedir = ini_get('open_basedir')) {
    $found_basedir = false;
    // if anything is found for open_basedir, set to warning:
    if ($open_basedir)   $this_class = 'WARN';
    // expand based on : symbol, or ; for windows
    $basedir_check_array = explode(':',$open_basedir);
    if (!is_array($basedir_check_array)) $basedir_check_array = explode(';',$open_basedir);
    // now loop thru paths in the open_basedir settings to find a match to our site. If not found, issue warning.
    if (is_array($basedir_check_array) && $dir_fs_www_root !='') {
      foreach($basedir_check_array as $basedir_check) {
//        echo 'www-root: ' . $dir_fs_www_root . '<br /> basedir: ' . $basedir_check . '<br /><br />';
        if ($basedir_check !='' && strstr($dir_fs_www_root, $basedir_check)) {
          //echo 'FOUND<br /><br />';
          $found_basedir=true;
        }
      }
    }
    if (!$found_basedir) $this_class = 'FAIL';
  }

  $status_check2[] = array('Importance' => 'Recommended', 'Title' => LABEL_OPEN_BASEDIR, 'Status' => $open_basedir, 'Class' => $this_class, 'HelpURL' =>'', 'HelpLabel'=>'Could have problems uploading files or doing backups');

  //Sendmail-From setting (PHP configuration)
  $sendmail_from = @ini_get("sendmail_from");
  $status_check2[] = array('Importance' => 'Info', 'Title' => LABEL_SENDMAIL_FROM, 'Status' => $sendmail_from, 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');

  //Sendmail Path setting (PHP configuration)
  $sendmail_path = @ini_get("sendmail_path");
  $status_check2[] = array('Importance' => 'Info', 'Title' => LABEL_SENDMAIL_PATH, 'Status' => $sendmail_path, 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');

  //SMTP (vs sendmail) setting (PHP configuration)
  $smtp = @ini_get("SMTP");
  $status_check2[] = array('Importance' => 'Info', 'Title' => LABEL_SMTP_MAIL, 'Status' => $smtp, 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');


  //include_path (PHP configuration)
  $includePath = @ini_get("include_path");
  $status_check2[] = array('Importance' => 'Info', 'Title' => LABEL_INCLUDE_PATH, 'Status' => $includePath, 'Class' => 'NA', 'HelpURL' =>'', 'HelpLabel'=>'');



// reverse the order for slightly more pleasant display
  $status_check2 = array_reverse($status_check2);

// error-condition-detection
  $php_extensions = get_loaded_extensions();
  $admin_config_exists = $zc_install->test_admin_configure(ERROR_TEXT_ADMIN_CONFIGURE,ERROR_CODE_ADMIN_CONFIGURE, ($zen_cart_previous_version_installed && $zen_cart_database_connect_OK ? false : true));
  $store_config_exists = $zc_install->test_store_configure(ERROR_TEXT_STORE_CONFIGURE,ERROR_CODE_STORE_CONFIGURE);
  if ($php_ext_sessions=="OFF") {$zc_install->setError(ERROR_TEXT_PHP_SESSION_SUPPORT, ERROR_CODE_PHP_SESSION_SUPPORT, true);}

  // don't restrict ability to proceed with installation if upgrading the database w/o touching configure.php files is a suitable option
  $zen_cart_allow_database_upgrade_button_disable = $zc_install->fatal_error;

  // do we override the fatal error caused by configure.php files not being writable?  Automatic for Windows hosts, due to the complexities of permissions on Windoze
  if ($configWriteOverride || stristr(PHP_OS, 'WinNT') || stristr(getenv("SERVER_SOFTWARE"), 'Win32') || stristr($real_path, 'wwwroot') || stristr($real_path, 'inetpub') || stristr($real_path, ':')) {
    $configWriteOverride = true;
  }
  // now check for writability of the configure.php files (after capturing fatal_error status above).
  if ($admin_config_exists) $admin_config_writable = $zc_install->test_admin_configure_write(ERROR_TEXT_ADMIN_CONFIGURE_WRITE,ERROR_CODE_ADMIN_CONFIGURE_WRITE, !$configWriteOverride);
  if ($store_config_exists) $store_config_writable = $zc_install->test_store_configure_write(ERROR_TEXT_STORE_CONFIGURE_WRITE,ERROR_CODE_STORE_CONFIGURE_WRITE, !$configWriteOverride);

  foreach (array('includes/configure.php', 'admin/includes/configure.php') as $file) {
    $this_writable='';
    $this_exists='';
    if (file_exists('../' . $file)) {
      $this_exists='';
      if ($zc_install->isWriteable('../' . $file)) {
        $this_class = 'OK';
        $this_writable=WRITABLE;
      } else {
        $this_class = 'FAIL';
        $this_writable=UNWRITABLE;
      }
    } else {
      $this_exists=NOT_EXIST;
      $this_class='FAIL';
    }
    $file_status[]=array('file'=>$file, 'exists'=>$this_exists, 'writable'=>$this_writable, 'class'=> $this_class);
  }




  //check folders status
  foreach (array('cache'=>'777 read/write/execute',
                 'images'=>'777 read/write/execute (INCLUDE SUBDIRECTORIES TOO)',
                 'includes/languages/english/html_includes'=>'777 read/write (INCLUDE SUBDIRECTORIES TOO)',
                 'logs'=>'777 read/write/execute',
                 'media'=>'777 read/write/execute',
                 'pub'=>'777 read/write/execute',
                 'admin/backups'=>'777 read/write',
                 'admin/images/graphs'=>'777 read/write/execute'
                 ) as $folder=>$chmod) {
    $folder_status[]=array('folder'=>$folder, 'writable'=>(@is_writable('../'.$folder)) ? OK : UNWRITABLE, 'class'=> (@is_writable('../'.$folder)) ? 'OK' : 'WARN', 'chmod'=>$chmod);
  }


// disable Install/Upgrade buttons if fatal error discovered
  $button_status = ($zc_install->fatal_error && !isset($_GET['ignorefatal'])) ? 'disabled="disabled"' : '';
  $button_status_upg = ($zen_cart_allow_database_upgrade_button_disable && !isset($_GET['ignorefatal'])) ? 'disabled="disabled"' : '';


// record system inspection results
  $data = "\n------------------------------\n";
  foreach ($status_check as $val) {
    $data .= $val['Class'] . ': ' . $val['Title'] . ' => ' . $val['Status'] . "\n"; //	$val['HelpLabel']
  }
  foreach ($status_check2 as $val) {
    $data .= $val['Class'] . ': ' . $val['Title'] . ' => ' . $val['Status'] . "\n"; //	$val['HelpLabel']
  }
  foreach ($file_status as $val) {
    $data .= $val['class'] . ': ' . $val['file'] . ' => ' . $val['exists'] . ' ' . $val['writable'] . "\n";
  }
  foreach ($folder_status as $val) {
    $data .= $val['class'] . ': ' . $val['folder'] . ' => ' . $val['writable'] . ' ' . $val['chmod'] . "\n";
  }
  $data .= 'PHP Extensions compiled: ';
  foreach($php_extensions as $module) { $data .= $module . ', '; }
  $data = substr($data, 0, -2); // remove trailing comma
  $data .= "\n------------------------------\n";


  // PROCESS TEMPLATE BUTTONS, IF CLICKED
  if (isset($_POST['submit'])) {
    if (!$zc_install->fatal_error) {
      $zc_install->setConfigKey('is_upgrade', 0);
      header('location: index.php?main_page=database_setup' . zcInstallAddSID() );
      exit;
    }
  } else
  if (isset($_POST['upgrade'])) {
    if (!$zc_install->fatal_error) {
      $zc_install->setConfigKey('is_upgrade', 1);
      header('location: index.php?main_page=database_setup' . zcInstallAddSID() );
      exit;
    }
  } else
  if (isset($_POST['db_upgrade'])) {
    if (!$zen_cart_allow_database_upgrade_button_disable) {
      $zc_install->setConfigKey('is_upgrade', 1);
      header('location: index.php?main_page=database_upgrade' . zcInstallAddSID() );
      exit;
    }
  } else
  if (isset($_POST['refresh'])) {
    $zc_install->logDetails('System Inspection Results: ' . str_replace(array('<br />', '<br>', '&nbsp;'), '', $data));
    header('location: index.php?main_page=inspect' . zcInstallAddSID() );
    exit;
  } else {
    $zc_install->logDetails('System Inspection Results: ' . str_replace(array('<br />', '<br>', '&nbsp;'), '', $data));
  }
function get_web_page( $url )
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}