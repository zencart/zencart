<?php
/**
 * installer Class.
 * This class is used during the installation and upgrade processes
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Thu Apr 5 15:25:02 2012 +0000 Modified in v1.5.1 $
 */


  class installer {
    var $php_version, $user_agent;
    var $configKeys = array();
    var $configFiles = array();
    var $configInfo = array();
    var $error, $fatal_error, $error_array;


    function installer() {
      $this->php_version = PHP_VERSION;
      $this->santitize_inputs();
      $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
      $this->configKeys = (isset($_SESSION['installerConfigKeys'])) ? $_SESSION['installerConfigKeys'] : array();
      if (isset($_POST['zcinst'])) $this->readConfigKeysFromPost();
      $this->configFiles = array();
    }

    function test_admin_configure($zp_error_text, $zp_error_code, $zp_fatal = false) {
      if (!file_exists('../admin/includes/configure.php')) {
        @chmod('../admin/includes', 0777);
        @touch('../admin/includes/configure.php');
        @chmod('../admin/includes', 0755);
        if (!file_exists('../admin/includes/configure.php')) {
          $this->setError($zp_error_text, $zp_error_code, $zp_fatal);
          return false;
        }
      } else {
        return true;
      }
    }


    function test_admin_configure_write($zp_error_text, $zp_error_code, $zp_fatal = true) {
      $fp = @fopen('../admin/includes/configure.php', 'a');
      if (!is_writeable('../admin/includes/configure.php') || (!$fp) ) {
        $this->setError($zp_error_text, $zp_error_code, $zp_fatal);
        $this->admin_config_writable=false;
      } else {
        $this->admin_config_writable=true;
      }
      if ($fp) @fclose($fp);
    }

    function test_store_configure_write($zp_error_text, $zp_error_code, $zp_fatal = true) {
      $fp = @fopen('../includes/configure.php', 'a');
      if (!is_writeable('../includes/configure.php') || (!$fp) ) {
        $this->setError($zp_error_text, $zp_error_code, $zp_fatal);
        $this->store_config_writable=false;
      } else {
        $this->store_config_writable=true;
      }
      if ($fp) @fclose($fp);
    }

    function test_store_configure($zp_error_text, $zp_error_code, $zp_fatal = true) {
      if (!file_exists('../includes/configure.php')) {
        @chmod('../includes', 0777);
        @touch('../includes/configure.php');
        @chmod('../includes', 0755);
        if (!file_exists('../includes/configure.php')) {
          $this->setError($zp_error_text, $zp_error_code, $zp_fatal);
          return false;
        }
      } else {
        return true;
      }
    }

    function test_php_version ($zp_test, $test_version, $zp_error_text='', $zp_error_code='', $zp_fatal=false) {
      if (isset($_GET['ignorephpver']) && $_GET['ignorephpver']=='1') return false;
      $string = explode('.',substr($this->php_version,0,6));
      foreach ($string as $key=>$value) {
        $string[$key] = str_pad((int)$value, 2, '0', STR_PAD_LEFT);
      }
      $myver_string = implode('',$string);

      $string = explode('.',$test_version);
      foreach ($string as $key=>$value) {
        $string[$key] = str_pad($value, 2, '0', STR_PAD_LEFT);
      }
      $test_version = implode('',$string);

      $zp_error_text = $this->php_version . ' ' . $zp_error_text;
//echo '<br />$myver='.$myver_string . '  $test_ver = ' . $test_version . ' &nbsp;&nbsp;&nbsp;TEST: ' . $zp_test . '&nbsp;&nbsp;error-text: ' . $zp_error_text;

      switch ($zp_test) {
        case '=':
          if ($myver_string == $test_version) {
            $this->setError($zp_error_text, $zp_error_code, $zp_fatal);
            return true;
          }
          break;
        case '<':
          if ($myver_string < $test_version) {
            $this->setError($zp_error_text, $zp_error_code, $zp_fatal);
            return true;
          }
          break;
      }
      return false;
    }

    function isEmpty($zp_test, $zp_error_text, $zp_error_code) {
      if ($zp_test == '' || $zp_test=='http://' || $zp_test=='https://' ) {
        $this->setError($zp_error_text, $zp_error_code, true);
      }
      return $zp_test;
    }

    function checkPrefix($zp_test, $zp_error_text, $zp_error_code) {
      if ($zp_test == '') return true;
      if (!preg_match( '#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $zp_test) || strlen($zp_test) > 16) {
        $this->setError('Your db prefix of "'.$zp_test.'" is a potential problem. ' . $zp_error_text, $zp_error_code, true);
      }
    }

    function fileExists($zp_file, $zp_error_text, $zp_error_code) {
      if (!file_exists($zp_file)) {
        $this->setError($zp_error_text, $zp_error_code, true);
      }
    }

    function isDir($zp_file, $zp_error_text, $zp_error_code) {
      if (!is_dir($zp_file)) {
        $this->setError($zp_error_text, $zp_error_code, true);
      }
    }

    function isWriteable($zp_file, $zp_error_text='', $zp_error_code='') {
      $retVal = true;
      if (is_dir($zp_file)) $zp_file .= '/test_writable.txt';
      $fp = @fopen($zp_file, 'a');
      if (!is_writeable($zp_file) || (!$fp) ) {
        if ($zp_error_code !='') $this->setError($zp_error_text, $zp_error_code, true);
        $retVal = false;
      }
      @fclose($fp);
      if (file_exists($zp_file) && !strstr($zp_file, 'configure.php')) @unlink($zp_file);
      return $retVal;
    }

    function functionExists($zp_type, $zp_error_text, $zp_error_code) {
      if ($zp_type == 'mysql') {
        $function = 'mysql_connect';
      }
      if (!function_exists($function)) {
        $this->setError($zp_error_text, $zp_error_code, true);
      }
    }

    function dbConnect($zp_type, $zp_host, $zp_database, $zp_username, $zp_pass, $zp_error_text, $zp_error_code, $zp_error_text2=ERROR_TEXT_DB_NOTEXIST, $zp_error_code2=ERROR_CODE_DB_NOTEXIST) {
      if ($this->error == false) {
        if ($zp_type == 'mysql') {
          $link = @mysql_connect($zp_host, $zp_username, $zp_pass);
          if ($link == false ) {
            $this->setError($zp_error_text.'<br />'.@mysql_error(), $zp_error_code, true);
          } else {
            if (!@mysql_select_db($zp_database, $link)) {
              $this->setError($zp_error_text2.'<br />'.@mysql_error(), $zp_error_code2, true);
            } else {
              @mysql_close($link);
            }
          }
        }
      }
    }

    function dbCreate($zp_create, $zp_type, $zp_name, $zp_error_text, $zp_error_code) {
      if ($zp_create == 'true' && $this->error == false) {
        if ($zp_type == 'mysql' && (@mysql_query('CREATE DATABASE ' . $zp_name) == false)) {
          $this->setError($zp_error_text, $zp_error_code, true);
        }
      }
    }

    function dbExists($zp_create, $zp_type, $zp_host, $zp_username, $zp_pass, $zp_name, $zp_error_text, $zp_error_code) {
      //    echo $zp_create;
      if ($zp_create != 'true' && $this->error == false) {
        if ($zp_type == 'mysql') {
          $link = @mysql_connect($zp_host, $zp_username, $zp_pass);
          if (@mysql_select_db($zp_name, $link) == false) {
            $this->setError($zp_error_text.'<br />'.@mysql_error(), $zp_error_code, true);
          }
          @mysql_close($link);
        }
      }
    }

    function isEmail($zp_param, $zp_error_text, $zp_error_code) {
      if (zen_validate_email($zp_param) == false) {
        $this->setError($zp_error_text, $zp_error_code, true);
      }
    }

    function isEqual($zp_param1, $zp_param2, $zp_error_text, $zp_error_code) {
      if ($zp_param1 != $zp_param2) {
        $this->setError($zp_error_text, $zp_error_code, true);
      }
    }

    function setError($zp_error_text, $zp_error_code, $zp_fatal = false) {
      $zp_error_text = strip_tags($zp_error_text, '<br>');
      $this->error = true;
      $this->fatal_error = $zp_fatal;
      $this->error_array[] = array('text'=>$zp_error_text, 'code'=>$zp_error_code);
      $this->throwException(($zp_fatal ? 'FATAL: ' : '') . str_replace('<br />', ' - ', $zp_error_text));
      $this->logDetails(($zp_fatal ? 'FATAL: ' : '') . str_replace('<br />', ' - ', $zp_error_text));
    }


  /**
   * Test CURL communications
   *
   * returns string
   */
    function test_curl($mode='NONSSL', $proxy = false, $proxyAddress = '') {
      if (!function_exists('curl_init') || !function_exists('curl_exec') || stristr(ini_get('disable_functions'), 'curl_exec') || stristr(ini_get('disable_functions'), 'curl_init') ) {
        $this->setError(ERROR_TEXT_CURL_NOT_COMPILED, ERROR_CODE_CURL_SUPPORT, false);
        return ERROR_TEXT_CURL_NOT_COMPILED;
      }
      $url = ($mode == 'NONSSL') ? "http://www.zen-cart.com/testcurl.php" : "https://www.zen-cart.com/testcurl.php";
      $data = "installertest=checking";
      if ($proxy) return false;

      // Send CURL communication
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 11);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
      if ($proxy) {
        curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, true);
        @curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt ($ch, CURLOPT_PROXY, $proxyAddress);
      }

      $result = curl_exec($ch);
      $errtext = curl_error($ch);
      $errnum = curl_errno($ch);
      $commInfo = @curl_getinfo($ch);
      curl_close ($ch);

      if (isset($_GET['debug'])) echo $mode . ($proxy ? ' (proxy)': '') . ' CURL RESULTS: ' . $errnum . ' => ' . $errtext . (trim($result) != '' ? ' [' . $result . ']' : '') . '<pre>' . print_r($commInfo, true) . '</pre><br /><br />';

      if ($errnum != 0 || trim($result) != 'PASS') {
        $response = $errnum . ' => ' . $errtext . (trim($result) != '' ? ' [' . $result . ']' : '');
        $this->setError(($mode == 'NONSSL' ? ERROR_TEXT_CURL_PROBLEM_GENERAL : ERROR_TEXT_CURL_SSL_PROBLEM) . ' ' . $response, ERROR_CODE_CURL_SUPPORT, false);
        return ($mode == 'NONSSL' ? ERROR_TEXT_CURL_PROBLEM_GENERAL : ERROR_TEXT_CURL_SSL_PROBLEM) . ' ' . $response;
      }
      return OKAY;  // yes, this is an intentional constant
    }

    function trimTrailingSlash($string) {
      return rtrim($string, '/');
    }

    function resetConfigInfo() {
      $this->configInfo = array();
      $_SESSION['installerConfigInfo'] = $this->configInfo;
    }

    function setConfigInfo($key, $val) {
      if ($val == 'unset_this') {
        unset($this->configInfo[$key]);
      } else {
        $this->configInfo[$key] = $val;
      }
      $_SESSION['installerConfigInfo'] = $this->configInfo;
    }

    function getConfigInfo($key = '*', $printable = false) {
      if ($key == '*') {
        return ($printable) ? print_r($this->configInfo, true) : $this->configInfo;
      } else {
        return (isset($this->configInfo[$key])) ? $this->configInfo[$key] : '';
      }
    }

    function resetConfigKeys() {
      $this->configKeys = array();
      $_SESSION['installerConfigKeys'] = $this->configKeys;
    }

    function setConfigKey($key, $val) {
      if ($val == 'unset_this') {
        unset($this->configKeys[$key]);
      } else {
        if ($key == 'DB_SERVER_PASSWORD') $val = $this->obfuscate($val);
        $this->configKeys[$key] = $val;
      }
      $_SESSION['installerConfigKeys'] = $this->configKeys;
    }

    function setConfigKeyMulti($key_array) {
      foreach($key_array as $key=>$val) {
        $this->configKeys[$key] = $val;
        if ($val == 'unset_this') unset($this->configKeys[$key]);
      }
      $_SESSION['installerConfigKeys'] = $this->configKeys;
    }

    function getConfigKey($key = '*', $printable = false) {
      if ($key == '*') {
        return ($printable) ? print_r($this->configKeys, true) : $this->configKeys;
      } else if ($key == '-') {
        $cleanKeys = $this->configKeys;
        if (isset($cleanKeys['DB_SERVER_PASSWORD'])) $cleanKeys['DB_SERVER_PASSWORD'] = '***private***';
        return ($printable) ? print_r($cleanKeys, true) : $cleanKeys;
      } else {
        $retVal = (isset($this->configKeys[$key])) ? $this->configKeys[$key] : '';
        if ($key == 'DB_SERVER_PASSWORD') $retVal = $this->obfuscate($retVal, 'out');
        return $retVal;
      }
    }

    function getConfigKeysAsPost() {
      $string = '';
      foreach($this->configKeys as $key => $value) {
        $string .= '<input type="hidden" name="zcinst[' . $key . ']" value="' . $value . '" />' . "\n";
      }
      return $string;
    }

    function readConfigKeysFromPost() {
      $postArray = $_POST['zcinst'];
      foreach($postArray as $key => $value) {
        if ($key == 'DB_SERVER_PASSWORD') $value = $this->obfuscate($value, 'out');
        $this->setConfigKey($key, $value);
      }
      return $this->configKeys;
    }

    function obfuscate($var, $mode='in') {
      if ($mode == 'in') return base64_encode(base64_encode($var));
      if ($mode == 'out') return base64_decode(base64_decode($var));
      return $var;
    }

    function throwException($details, $moreinfo = '', $location = '', $fname = '') {
      global $current_page;
      if ($_SESSION['logfilename'] == '') $_SESSION['logfilename'] = ($fname == '') ? date('M-d-Y_h-i-s-') . zen_create_random_value(6) : $fname;
      $location = ($location == '') ? $current_page : $location;
      if ($fp = @fopen(DEBUG_LOG_FOLDER . '/zcInstallExceptionDetails_' . $_SESSION['logfilename'] . '.log', 'a')) {
        fwrite($fp, '---------------' . "\n" . date('M d Y G:i') . ' -- ' . $location . "\n" . $details . "\n\n");
        fclose($fp);
      }
    }

    function logDetails($details, $location = '', $fname = '') {
      global $current_page;
      if ($_SESSION['logfilename'] == '') $_SESSION['logfilename'] = ($fname == '') ? date('M-d-Y_h-i-s-') . zen_create_random_value(6) : $fname;
      $location = ($location == '') ? $current_page : $location;
      if ($fp = @fopen(DEBUG_LOG_FOLDER . '/zcInstallLog_' . $_SESSION['logfilename'] . '.log', 'a')) {
        fwrite($fp, '---------------' . "\n" . date('M d Y G:i') . ' -- ' . $location . "\n" . $details . "\n\n");
        fclose($fp);
      }
    }

    // Determine Document Root
    function detectDocumentRoot() {
      $dir_fs_www_root = realpath(dirname(basename(__FILE__)) . "/..");
      if ($dir_fs_www_root == '') $dir_fs_www_root = '/';
      $dir_fs_www_root = str_replace(array('\\','//'), '/', $dir_fs_www_root);
      return $dir_fs_www_root;
    }

    // OLD METHOD ... should be removed
    function detectDocumentRoot_OLD() {
      // old method:
      $realPath = realpath(dirname(basename(__FILE__)));
      $script_filename = (isset($_SERVER['PATH_TRANSLATED'])) ? $_SERVER['PATH_TRANSLATED'] : (isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : $realPath);
      $script_filename = str_replace(array('\\','//'), '/', $script_filename);

      // split into an array in order to read its parts
      $dir_fs_www_root_array = explode('/', dirname($script_filename));

      // re-assemble with all except the last part
      $dir_fs_www_root_tmp = array();
      for ($i=0, $n=sizeof($dir_fs_www_root_array)-1; $i<$n; $i++) {
        $dir_fs_www_root_tmp[] = $dir_fs_www_root_array[$i];
      }
      $dir_fs_www_root = implode('/', $dir_fs_www_root_tmp);

      // if blank, at least give it a single slash /
      if ($dir_fs_www_root == '') $dir_fs_www_root = '/';
      return $dir_fs_www_root;
    }

    function writeConfigFiles() {
      $virtual_http_path = parse_url($this->getConfigKey('virtual_http_path'));
      $http_server = $virtual_http_path['scheme'] . '://' . $virtual_http_path['host'];
      $http_catalog = (isset($virtual_http_path['path'])) ? $virtual_http_path['path'] : '';
      if (isset($virtual_http_path['port']) && !empty($virtual_http_path['port'])) {
        $http_server .= ':' . $virtual_http_path['port'];
      }
      if (substr($http_catalog, -1) != '/') {
        $http_catalog .= '/';
      }
      $sql_cache_dir = (int)$this->getConfigKey('DIR_FS_SQL_CACHE');
      $cache_type = $this->getConfigKey('SQL_CACHE_METHOD');
      $https_server = $this->getConfigKey('virtual_https_server');
      $https_catalog = $this->getConfigKey('virtual_https_path');
      //if the https:// entries were left blank, use non-SSL versions instead of blank
      if ($https_server == '' || trim($https_server) == '' || $https_server == 'https://' || $https_server == '://') $https_server = $http_server;
      if (trim($https_catalog) == '') $https_catalog = $http_catalog;
      $https_catalog_path = preg_replace('/' . preg_quote($https_server, '/') . '/', '', $https_catalog) . '/';
      $https_catalog = $https_catalog_path;

      //now let's write the files
      // Catalog version first:
      require('includes/store_configure.php');
      $config_file_contents_catalog = $file_contents;
      $fp = @fopen($this->getConfigKey('DIR_FS_CATALOG') . '/includes/configure.php', 'w');
      if ($fp) {
        fputs($fp, $file_contents);
        fclose($fp);
        @chmod($this->getConfigKey('DIR_FS_CATALOG') . '/includes/configure.php', 0444);
      }
      $http_srvr_admin = $http_server;
      $http_catalog_admin = $http_catalog;
      // if SSL is enabled for admin, put the SSL address into the HTTP_SERVER field (and set corresponding DIR_WS_ADMIN param too)
      if ($this->getConfigKey('ENABLE_SSL_ADMIN') == 'true') {
        $http_srvr_admin = $https_server;
        $http_catalog_admin = $https_catalog;
      }
      // now Admin version:
      require('includes/admin_configure.php');
      $config_file_contents_admin = $file_contents;
      $fp = @fopen($this->getConfigKey('DIR_FS_CATALOG') . '/admin/includes/configure.php', 'w');
      if ($fp) {
        fputs($fp, $file_contents);
        fclose($fp);
//        @chmod($this->getConfigKey('DIR_FS_CATALOG') . '/admin/includes/configure.php', 0444);
      }

      $this->configFiles = array('catalog' => $config_file_contents_catalog, 'admin' => $config_file_contents_admin);
      return $this->validateConfigFiles($http_server);
    }

    function validateConfigFiles($http_server) {
      // test whether the files were written successfully
      $ztst_http_server = zen_read_config_value('HTTP_SERVER');
      $ztst_db_server = zen_read_config_value('DB_SERVER');
      $ztst_sqlcachedir = zen_read_config_value('DIR_FS_SQL_CACHE');
      if ($ztst_http_server != $http_server || $ztst_db_server != $this->getConfigKey('DB_SERVER') || $ztst_sqlcachedir != $this->getConfigKey('DIR_FS_SQL_CACHE') || $this->getConfigKey('DB_SERVER') == '') {
        $this->setError(ERROR_TEXT_COULD_NOT_WRITE_CONFIGURE_FILES, ERROR_CODE_COULD_NOT_WRITE_CONFIGURE_FILES, true);
        $this->throwException('Failed writing configure.php file: Found in config file: [' . $ztst_http_server . '], expecting [' . $http_server . ']');
        $this->throwException('Failed writing configure.php file: Found in config file: [' . $ztst_db_server . '], expecting [' . $this->getConfigKey('DB_SERVER') . ']');
        $this->throwException('Failed writing configure.php file: Found in config file: [' . $ztst_sqlcachedir . '], expecting [' . $this->getConfigKey('DIR_FS_SQL_CACHE') . ']');
        $retVal = false;
      } else {
        $retVal = true;
      }
      return $retVal;
    }

    function validateDatabaseSetup($data) {
      if ($data['db_type'] != 'mysql') $data['db_prefix'] = '';  // if not using mysql, don't support prefixes because we don't trap for them
      if ($data['cache_type'] == 'file') {  //if caching to file, check folder
        $this->isEmpty($data['sql_cache_dir'],  ERROR_TEXT_CACHE_DIR_ISEMPTY, ERROR_CODE_CACHE_DIR_ISEMPTY);
        $this->isDir($data['sql_cache_dir'],  ERROR_TEXT_CACHE_DIR_ISDIR, ERROR_CODE_CACHE_DIR_ISDIR);
        $this->isWriteable($data['sql_cache_dir'],  ERROR_TEXT_CACHE_DIR_ISWRITEABLE, ERROR_CODE_CACHE_DIR_ISWRITEABLE);
      }
      //$this->checkPrefix($data['db_prefix'], ERROR_TEXT_DB_PREFIX_NODOTS, ERROR_CODE_DB_PREFIX_NODOTS);
      $data['db_prefix'] == preg_replace('/[^0-9a-zA-Z_]/', '_', trim($data['db_prefix']));
      $this->isEmpty($data['db_host'], ERROR_TEXT_DB_HOST_ISEMPTY, ERROR_CODE_DB_HOST_ISEMPTY);
      $this->isEmpty($data['db_username'], ERROR_TEXT_DB_USERNAME_ISEMPTY, ERROR_CODE_DB_USERNAME_ISEMPTY);
      $this->isEmpty($data['db_name'], ERROR_TEXT_DB_NAME_ISEMPTY, ERROR_CODE_DB_NAME_ISEMPTY);
      $this->fileExists('sql/' . $data['db_type'] . '_zencart.sql', ERROR_TEXT_DB_SQL_NOTEXIST, ERROR_CODE_DB_SQL_NOTEXIST);
      $this->functionExists($data['db_type'], ERROR_TEXT_DB_NOTSUPPORTED, ERROR_CODE_DB_NOTSUPPORTED);
      $this->dbConnect($data['db_type'], $data['db_host'], $data['db_name'], $data['db_username'], $data['db_pass'], ERROR_TEXT_DB_CONNECTION_FAILED, ERROR_CODE_DB_CONNECTION_FAILED,ERROR_TEXT_DB_NOTEXIST, ERROR_CODE_DB_NOTEXIST);
      $this->dbExists(false, $data['db_type'], $data['db_host'], $data['db_username'], $data['db_pass'], $data['db_name'], ERROR_TEXT_DB_NOTEXIST, ERROR_CODE_DB_NOTEXIST);
      if ($data['db_coll'] != 'utf8') $data['db_coll'] = 'latin1';
      $this->setConfigKey('DB_TYPE', $data['db_type']);
      $this->setConfigKey('DB_PREFIX', $data['db_prefix']);
      $this->setConfigKey('DB_CHARSET', $data['db_coll']);
      $this->setConfigKey('DB_SERVER', $data['db_host']);
      $this->setConfigKey('DB_SERVER_USERNAME', $data['db_username']);
      $this->setConfigKey('DB_SERVER_PASSWORD', $data['db_pass']);
      $this->setConfigKey('DB_DATABASE', $data['db_name']);
      $this->setConfigKey('SQL_CACHE_METHOD', $data['cache_type']);
      $this->setConfigKey('DIR_FS_SQL_CACHE', $this->trimTrailingSlash($data['sql_cache_dir']));
    }

    function dbActivate() {
      if (isset($this->db)) return;
      if ($this->getConfigKey('DB_TYPE') == '') $this->setConfigKey('DB_TYPE', zen_read_config_value('DB_TYPE', FALSE));
      if ($this->getConfigKey('DB_CHARSET') == '') $this->setConfigKey('DB_CHARSET', zen_read_config_value('DB_CHARSET', FALSE));
      if ($this->getConfigKey('DB_CHARSET') != 'latin1') $this->setConfigKey('DB_CHARSET', 'utf8');
      if (!defined('DB_CHARSET') && $this->getConfigKey('DB_CHARSET') != '') define('DB_CHARSET', $this->getConfigKey('DB_CHARSET'));
      if ($this->getConfigKey('DB_PREFIX') == '') $this->setConfigKey('DB_PREFIX', zen_read_config_value('DB_PREFIX', FALSE));
      if ($this->getConfigKey('DB_SERVER') == '') $this->setConfigKey('DB_SERVER', zen_read_config_value('DB_SERVER', FALSE));
      if ($this->getConfigKey('DB_SERVER_USERNAME') == '') $this->setConfigKey('DB_SERVER_USERNAME', zen_read_config_value('DB_SERVER_USERNAME', FALSE));
      if ($this->getConfigKey('DB_SERVER_PASSWORD') == '') $this->setConfigKey('DB_SERVER_PASSWORD', zen_read_config_value('DB_SERVER_PASSWORD', FALSE));
      if ($this->getConfigKey('DB_DATABASE') == '') $this->setConfigKey('DB_DATABASE', zen_read_config_value('DB_DATABASE', FALSE));
      include_once('../includes/classes/db/' . $this->getConfigKey('DB_TYPE') . '/query_factory.php');
      $this->db = new queryFactory;
      $this->db->Connect($this->getConfigKey('DB_SERVER'), $this->getConfigKey('DB_SERVER_USERNAME'), $this->getConfigKey('DB_SERVER_PASSWORD'), $this->getConfigKey('DB_DATABASE'), true);
    }

    function dbLoadProcedure() {
      $this->dbActivate(); // can likely remove this line for v1.4
      global $db;
      $db = $this->db;
      // process the actual sql insertions
      executeSql('sql/' . $this->getConfigKey('DB_TYPE') . '_zencart.sql', $this->getConfigKey('DB_DATABASE'), $this->getConfigKey('DB_PREFIX'));
      executeSql('sql/' . $this->getConfigKey('DB_TYPE') . '_' . $this->getConfigKey('DB_CHARSET') . '.sql', $this->getConfigKey('DB_DATABASE'), $this->getConfigKey('DB_PREFIX'));

      //update the cache folder setting:
      $this->dbAfterLoadActions();

      if (file_exists('includes/local/developers_' . $this->getConfigKey('DB_TYPE') . '.sql')) {
        executeSql('includes/local/developers_' . $this->getConfigKey('DB_TYPE') . '.sql', $this->getConfigKey('DB_DATABASE'), $this->getConfigKey('DB_PREFIX'));
      }

      // process any plugin SQL scripts
      $this->dbHandleSQLPlugins();

      // Close the database connection
      $this->db->Close();
    }

      /**
       * Support for SQL Plugins in installer
       */
    function dbHandleSQLPlugins() {
      $directory_array = array();
      $sqlpluginsdir = 'sql/plugins/';
      if ($dir = @dir($sqlpluginsdir)) {
        while ($file = $dir->read()) {
          if (!is_dir($sqlpluginsdir . $file)) {
            if (ZC_UPG_DEBUG3) echo '<br />checking file: ' . $sqlpluginsdir . $file;
            if (preg_match('/^' . $this->getConfigKey('DB_TYPE') . '.*\.sql$/', $file) > 0) {
              $directory_array[] = $file;
            }
          }
        }
        if (sizeof($directory_array)) {
          sort($directory_array);
        }
        $dir->close();
      }
      for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
        $file = $directory_array[$i];
        if (file_exists($sqlpluginsdir . $file)) {
          echo '<br />Processing Plugin: ' . $sqlpluginsdir . $file . '<br />';
          $this->logDetails('Processing SQL Plugin: ' . $sqlpluginsdir . $file);
          executeSql($sqlpluginsdir . $file, $this->getConfigKey('DB_DATABASE'), $this->getConfigKey('DB_PREFIX'));
        }
      }

    }

    function dbAfterLoadActions() {
      $this->dbActivate(); // can likely remove this line for v1.4
      //update the cache folder setting:
      $sql = "update ". $this->getConfigKey('DB_PREFIX') ."configuration set configuration_value='". $this->getConfigKey('DIR_FS_SQL_CACHE') ."' where configuration_key = 'SESSION_WRITE_DIRECTORY'";
      $this->db->Execute($sql);
      //update the logging_folder setting:
      $sql = "update ". $this->getConfigKey('DB_PREFIX') ."configuration set configuration_value='". $this->getConfigKey('DIR_FS_SQL_CACHE') ."/page_parse_time.log' where configuration_key = 'STORE_PAGE_PARSE_TIME_LOG'";
      $this->db->Execute($sql);
      //update the phpbb setting:
//      $sql = "update ". $this->getConfigKey('DB_PREFIX') ."configuration set configuration_value='". $this->getConfigKey('PHPBB_ENABLE') ."' where configuration_key = 'PHPBB_LINKS_ENABLED'";
//      $this->db->Execute($sql);
    }

    function dbDemoDataInstall() {
      $this->dbActivate(); // can likely remove this line for v1.4
      global $db;
      $db = $this->db;
      executeSql('demo/' . DB_TYPE . '_demo.sql', DB_DATABASE, DB_PREFIX);
    }

    function validateStoreSetup($data) {
      $this->configInfo['store_name'] = $this->isEmpty(zen_db_prepare_input($data['store_name']), ERROR_TEXT_STORE_NAME_ISEMPTY, ERROR_CODE_STORE_NAME_ISEMPTY);
      $this->configInfo['store_owner'] = $this->isEmpty(zen_db_prepare_input($data['store_owner']), ERROR_TEXT_STORE_OWNER_ISEMPTY, ERROR_CODE_STORE_OWNER_ISEMPTY);
      $this->configInfo['store_owner_email'] = $this->isEmpty(zen_db_prepare_input($data['store_owner_email']), ERROR_TEXT_STORE_OWNER_EMAIL_ISEMPTY, ERROR_CODE_STORE_OWNER_EMAIL_ISEMPTY);
      $this->configInfo['store_owner_email'] = $this->isEmpty(zen_db_prepare_input($data['store_owner_email']), ERROR_TEXT_STORE_OWNER_EMAIL_NOTEMAIL, ERROR_CODE_STORE_OWNER_EMAIL_NOTEMAIL);
      $this->configInfo['store_address'] = $this->isEmpty(zen_db_prepare_input($data['store_address']), ERROR_TEXT_STORE_ADDRESS_ISEMPTY, ERROR_CODE_STORE_ADDRESS_ISEMPTY);
      $this->configInfo['store_country'] = zen_db_prepare_input($data['store_country']);
      $selectedStoreZone = zen_db_prepare_input($data['store_zone']);
      if ($selectedStoreZone == '-1' && $selectedStoreZone != '0') $data['store_zone'] = '';
      $this->configInfo['store_zone'] = $this->isEmpty(zen_db_prepare_input($data['store_zone']), ERROR_TEXT_STORE_ZONE_NEEDS_SELECTION, ERROR_CODE_STORE_ZONE_NEEDS_SELECTION);
      if ($this->configInfo['store_zone'] == '0') $this->configInfo['store_zone'] = '';
      $this->configInfo['store_default_language'] = zen_db_prepare_input($data['store_default_language']);
      $this->configInfo['store_default_currency'] = zen_db_prepare_input($data['store_default_currency']);
    }

    function dbStoreSetup() {
      $this->dbActivate(); // can likely remove this line for v1.4
      $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $this->db->prepare_input($this->configInfo['store_name']) . "' where configuration_key = 'STORE_NAME'";
      $this->db->Execute($sql);
      $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $this->db->prepare_input($this->configInfo['store_owner']) . "' where configuration_key = 'STORE_OWNER'";
      $this->db->Execute($sql);
      $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $this->db->prepare_input($this->configInfo['store_owner_email']) . "' where configuration_key in
               ('STORE_OWNER_EMAIL_ADDRESS', 'EMAIL_FROM', 'SEND_EXTRA_ORDER_EMAILS_TO', 'SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO', 'SEND_EXTRA_LOW_STOCK_EMAILS_TO', 'SEND_EXTRA_GV_CUSTOMER_EMAILS_TO', 'SEND_EXTRA_GV_ADMIN_EMAILS_TO', 'SEND_EXTRA_DISCOUNT_COUPON_ADMIN_EMAILS_TO', 'SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO', 'SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO', 'MODULE_PAYMENT_CC_EMAIL')";
      $this->db->Execute($sql);
      $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $this->db->prepare_input($this->configInfo['store_country']) . "' where configuration_key in ('STORE_COUNTRY', 'SHIPPING_ORIGIN_COUNTRY')";
      $this->db->Execute($sql);
      $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $this->db->prepare_input($this->configInfo['store_zone']) . "' where configuration_key = 'STORE_ZONE'";
      $this->db->Execute($sql);
      $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $this->db->prepare_input($this->configInfo['store_address']) . "' where configuration_key = 'STORE_NAME_ADDRESS'";
      $this->db->Execute($sql);
      $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $this->db->prepare_input($this->configInfo['store_default_language']) . "' where configuration_key = 'DEFAULT_LANGUAGE'";
      $this->db->Execute($sql);
      $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $this->db->prepare_input($this->configInfo['store_default_currency']) . "' where configuration_key = 'DEFAULT_CURRENCY'";
      $this->db->Execute($sql);
      $sql = "update " . DB_PREFIX . "currencies set value = 1 where code = '" . $this->db->prepare_input($this->configInfo['store_default_currency']) . "'";
      $this->db->Execute($sql);
    }

    function updateAdminIpList() {
      if (isset($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR']) > 4) {
        $checkip = $_SERVER['REMOTE_ADDR'];
        $this->dbActivate();
        $sql = "select configuration_value from " . DB_PREFIX . "configuration where configuration_key = 'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE'";
        $result = $this->db->Execute($sql);
        if (!strstr($result->fields['configuration_value'], $checkip)) {
          $newip = $result->fields['configuration_value'] . ',' . $checkip;
          $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $this->db->prepare_input($newip) . "' where configuration_key = 'EXCLUDE_ADMIN_IP_FOR_MAINTENANCE'";
          $this->db->Execute($sql);
        }
      }
    }

    function validateAdminSetup($data) {
      $this->dbActivate();
      if (!isset($this->configInfo['check_for_updates'])) $this->configInfo['check_for_updates'] = (isset($data['check_for_updates']) && $data['check_for_updates']== '1' ) ? 1 : 0;
      $this->configInfo['admin_username'] = zen_db_prepare_input($data['admin_username']);
      $this->configInfo['admin_email'] = zen_db_prepare_input($data['admin_email']);
      $this->configInfo['admin_pass'] = zen_db_prepare_input($data['admin_pass']);
      $this->isEmpty($this->configInfo['admin_username'], ERROR_TEXT_ADMIN_USERNAME_ISEMPTY, ERROR_CODE_ADMIN_USERNAME_ISEMPTY);
      $this->isEmpty($this->configInfo['admin_email'], ERROR_TEXT_ADMIN_EMAIL_ISEMPTY, ERROR_CODE_ADMIN_EMAIL_ISEMPTY);
      $this->isEmail($this->configInfo['admin_email'], ERROR_TEXT_ADMIN_EMAIL_NOTEMAIL, ERROR_CODE_ADMIN_EMAIL_NOTEMAIL);
      $this->isEmpty($this->configInfo['admin_pass'], ERROR_TEXT_ADMIN_PASS_ISEMPTY, ERROR_CODE_ADMIN_PASS_ISEMPTY);

      // passwords must contain at least 1 letter and 1 number and be of required minimum length
      if (!preg_match('/^(?=.*[a-zA-Z]+.*)(?=.*[\d]+.*)[\d\w[:punct:]]{7,}$/', $this->configInfo['admin_pass'])) {
        $this->setError(ERROR_TEXT_ADMIN_PASS_INSECURE, ERROR_CODE_ADMIN_PASS_INSECURE, true);
      }
    }


    function dbAdminSetup() {
      $this->dbActivate();
      $sql = "update " . DB_PREFIX . "admin set admin_name = '" . $this->configInfo['admin_username'] . "', admin_email = '" . $this->configInfo['admin_email'] . "', admin_pass = '" . zen_encrypt_password($this->configInfo['admin_pass']) . "', pwd_last_change_date = 0, reset_token = '" . (time() + (72 * 60 * 60)) . '}' . zen_encrypt_password($this->configInfo['admin_pass']) . "' where admin_id = 1";
      $this->db->Execute($sql) or die("Error in query: $sql".$this->db->ErrorMsg());

      // enable/disable automatic version-checking
      $sql = "update " . DB_PREFIX . "configuration set configuration_value = '".($this->configInfo['check_for_updates'] ? 'true' : 'false' ) ."' where configuration_key = 'SHOW_VERSION_UPDATE_IN_HEADER'";
      $this->db->Execute($sql) or die("Error in query: $sql".$this->db->ErrorMsg());

      $this->db->Close();
    }


    function verifyAdminCredentials($admin_name, $admin_pass, $prefix = '^^^') {
      // security check
      if ($admin_name == '' || $admin_name == 'demo' || $admin_pass == '') {
        $this->setError(ERROR_TEXT_ADMIN_PWD_REQUIRED, ERROR_CODE_ADMIN_PWD_REQUIRED, true);
      } else {
        if ($prefix == '^^^') $prefix = DB_PREFIX;
        $admin_name = zen_db_prepare_input($admin_name);
        $admin_pass = zen_db_prepare_input($admin_pass);
//@TODO: deal with super-user requirement and expired-passwords?
        $sql = "select admin_id, admin_name, admin_pass from " . $prefix . "admin where admin_name = '" . $admin_name . "'";
        //open database connection to run queries against it
        $this->dbActivate();
        $this->db->Close();
        unset($this->db);
        $this->dbActivate();
        $result = $this->db->Execute($sql);
        if ($result->EOF || $admin_name != $result->fields['admin_name'] || !zen_validate_password($admin_pass, $result->fields['admin_pass'])) {
          $this->setError(ERROR_TEXT_ADMIN_PWD_REQUIRED, ERROR_CODE_ADMIN_PWD_REQUIRED, true);
        } else {
          $this->candidateSuperuser = $result->fields['admin_id'];
        }
        $this->db->Close();
      }
    }

    function addSuperUser($prefix = '^^^') {
      if ($prefix == '^^^') $prefix = DB_PREFIX;
      $this->dbActivate();
      $this->db->Close();
      unset($this->db);
      $this->dbActivate();
      $sql = "UPDATE " . $prefix . "admin SET admin_profile = 1 WHERE admin_id = " . $this->candidateSuperuser;
      $this->db->Execute($sql) or die("Error in query: $sql".$this->db->ErrorMsg());
      $this->db->Close();
    }

    function doPrefixRename($newprefix, $db_prefix_rename_from) {
      $this->test_admin_configure(ERROR_TEXT_ADMIN_CONFIGURE,ERROR_CODE_ADMIN_CONFIGURE, true);
      $this->test_store_configure(ERROR_TEXT_STORE_CONFIGURE,ERROR_CODE_STORE_CONFIGURE);
      $this->test_admin_configure_write(ERROR_TEXT_ADMIN_CONFIGURE_WRITE,ERROR_CODE_ADMIN_CONFIGURE_WRITE);
      $this->test_store_configure_write(ERROR_TEXT_STORE_CONFIGURE_WRITE,ERROR_CODE_STORE_CONFIGURE_WRITE);
      $this->functionExists(DB_TYPE, ERROR_TEXT_DB_NOTSUPPORTED, ERROR_CODE_DB_NOTSUPPORTED);
      $this->dbConnect(DB_TYPE, DB_SERVER, DB_DATABASE, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, ERROR_TEXT_DB_CONNECTION_FAILED, ERROR_CODE_DB_CONNECTION_FAILED,ERROR_TEXT_DB_NOTEXIST, ERROR_CODE_DB_NOTEXIST);

      // security check
      if ((!isset($_POST['adminid']) && !isset($_POST['adminpwd'])) || $_POST['adminid']=='' || $_POST['adminid']=='demo') {
        $this->setError(ERROR_TEXT_ADMIN_PWD_REQUIRED, ERROR_CODE_ADMIN_PWD_REQUIRED, true);
      } else {
        $this->verifyAdminCredentials($_POST['adminid'], $_POST['adminpwd'], $db_prefix_rename_from);
      }
      // end admin verification

      if (ZC_UPG_DEBUG2==true) echo 'Processing prefix updates...<br />';
      if ($this->error == false && $nothing_to_process==false) {

        $this->dbActivate();
        $this->db->Close();
        unset($this->db);
        $this->dbActivate();
        $tables = $this->db->Execute("SHOW TABLES"); // get a list of tables to compare against
        $tables_list = array();
        while (!$tables->EOF) {
          $tables_list[] = $tables->fields['Tables_in_' . DB_DATABASE];
          $tables->MoveNext();
        } //end while


        //read the "database_tables.php" files, and loop through the table names
        foreach($database_tablenames_array as $filename) {
          if (!file_exists($filename)) continue;
          $lines = file($filename);
          foreach ($lines as $line) {
            $line = trim($line);
            if (substr($line,0,1) != '<' && substr($line,0,2) != '?'.'>' && substr($line,0,2) != '//' && $line != '') {
              //           echo 'line='.$line.'<br>';
              $def_string=array();
              $def_string=explode("'",$line);
              //define('TABLE_CONSTANT',DB_PREFIX.'tablename');
              //[1]=TABLE_CONSTANT
              //[2]=,DB_PREFIX.
              //[3]=tablename
              //[4]=);
              //[5]=
              //echo '[1]->'.$def_string[1].'<br>';
              //echo '[2]->'.$def_string[2].'<br>';
              //echo '[3]->'.$def_string[3].'<br>';
              //echo '[4]->'.$def_string[4].'<br>';
              //echo '[5]->'.$def_string[5].'<br>';
              if (strtoupper($def_string[1]) != 'DB_PREFIX' // the define of DB_PREFIX is not a tablename
              && str_replace('PHPBB','',strtoupper($def_string[1]) ) == strtoupper($def_string[1])  // this is not a phpbb table
              && str_replace(' ','',$def_string[2]) == ',DB_PREFIX.') { // this is a Zen Cart-related table (vs phpbb)
                $tablename_read = $def_string[3];
                foreach($tables_list as $existing_table) {
                  if ($tablename_read == str_replace($db_prefix_rename_from,'',$existing_table)) {
                    //echo $tablename_read.'<br>';
                    $sql_command = 'alter table '. $db_prefix_rename_from . $tablename_read . ' rename ' . $newprefix.$tablename_read;
                    //echo $sql_command .'<br>';
                    $this->db->Execute($sql_command);
                    $tables_updated++;
                    $tablename_read = '';
                    $sql_command = '';
                  }//endif $tablename_read == existing
                }//end foreach $tables_list
              } //endif is "DEFINE"?
            } // endif substring not < or ? or // etc
          } //end foreach $lines
        }//end foreach $database_tablenames array

        $this->db->Close();
      } // end if zc_install-error

      //echo $tables_updated;
      if ($tables_updated <50) $this->setError(ERROR_TEXT_TABLE_RENAME_INCOMPLETE, ERROR_CODE_TABLE_RENAME_INCOMPLETE, false);

      if ($tables_updated >50) {
        //update the configure.php files with the new prefix.
        $configure_files_updated = 0;
        foreach($configure_files_array as $filename) {
          $lines = file($filename);
          $full_file = '';
          foreach ($lines as $line) {
            $def_string=explode("'",$line);
            if (strtoupper($def_string[1]) == 'DB_PREFIX') {
              // check to see if prefix found matches what we've been processing... for safety to be sure we have the right line
              $old_prefix_from_file = $def_string[3];
              if ($old_prefix_from_file == DB_PREFIX || $old_prefix_from_file == $db_prefix_rename_from) {
                $line = '  define(\'DB_PREFIX\', \'' . $newprefix. '\');' . "\n";
                $configure_files_updated++;
              }
            } // endif DEFINE DB_PREFIX found;
            $full_file .= $line;
          } //end foreach $lines
          $fp = fopen($filename, 'w');
          fputs($fp, $full_file);
          fclose($fp);
          @chmod($filename, 0644);
        } //end foreach array to update configure.php files
        if ($configure_files_updated <2) $this->setError(ERROR_TEXT_TABLE_RENAME_CONFIGUREPHP_FAILED, ERROR_CODE_TABLE_RENAME_CONFIGUREPHP_FAILED, false);
      } //endif $tables_updated count sufficient
    }

    function santitize_inputs() {
      if (isset($_GET['main_page'])) $_GET['main_page'] = preg_replace('/[^a-zA-Z_]/', '', $_GET['main_page']);
      if (isset($_GET['language'])) $_GET['language'] = preg_replace('/[^a-zA-Z_]/', '', $_GET['language']);
      if (isset($_GET['debug'])) $_GET['debug'] = preg_replace('/[^0-9]/', '', $_GET['debug']);
      if (isset($_GET['debug2'])) $_GET['debug2'] = preg_replace('/[^0-9]/', '', $_GET['debug2']);
      if (isset($_GET['debug3'])) $_GET['debug3'] = preg_replace('/[^0-9]/', '', $_GET['debug3']);
      if (isset($_GET['configfile'])) $_GET['configfile'] = preg_replace('/[^0-9]/', '', $_GET['configfile']);
      if (isset($_GET['nogrants'])) $_GET['nogrants'] = preg_replace('/[^0-9]/', '', $_GET['nogrants']);

      /**
       * process all $_GET terms
       */
      $strictReplace = '[<>\']';
      $unStrictReplace = '[<>]';
      if (isset($_GET) && count($_GET) > 0) {
        foreach($_GET as $key=>$value){
          if(is_array($value)){
            foreach($value as $key2 => $val2){
              if ($key2 == 'keyword') {
                $_GET[$key][$key2] = preg_replace('/'.$unStrictReplace.'/', '', $val2);
              } else {
                $_GET[$key][$key2] = preg_replace('/'.$strictReplace.'/', '', $val2);
              }
              unset($GLOBALS[$key]);
            }
          } else {
            if ($key == 'keyword') {
              $_GET[$key] = preg_replace('/'.$unStrictReplace.'/', '', $value);
            } else {
              $_GET[$key] = preg_replace('/'.$strictReplace.'/', '', $value);
            }
            unset($GLOBALS[$key]);
          }
        }
      }
      /**
       * process all $_POST terms
       */
      if (isset($_POST) && count($_POST) > 0) {
        foreach($_POST as $key=>$value){
          if(is_array($value)){
            foreach($value as $key2 => $val2){
              unset($GLOBALS[$key]);
            }
          } else {
            unset($GLOBALS[$key]);
          }
        }
      }
      /**
       * process all $_COOKIE terms
       */
      if (isset($_COOKIE) && count($_COOKIE) > 0) {
        foreach($_COOKIE as $key=>$value){
          if(is_array($value)){
            foreach($value as $key2 => $val2){
              unset($GLOBALS[$key]);
            }
          } else {
            unset($GLOBALS[$key]);
          }
        }
      }
      /**
       * process all $_SESSION terms
       */
      if (isset($_SESSION) && count($_SESSION) > 0) {
        foreach($_SESSION as $key=>$value){
          if(is_array($value)){
            foreach($value as $key2 => $val2){
              unset($GLOBALS[$key]);
            }
          } else {
            unset($GLOBALS[$key]);
          }
        }
      }
      /**
       * sanitize $_SERVER vars
       */
      $_SERVER['REMOTE_ADDR'] = preg_replace('/[^0-9.%:]/', '', $_SERVER['REMOTE_ADDR']);
    }


  } // end class

