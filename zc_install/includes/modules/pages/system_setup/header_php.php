<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 18235 2010-11-23 22:41:05Z drbyte $
 * @TODO: If SSL is selected, switch into SSL mode to prove that it works.
 */

// check to see if we're upgrading
$is_upgrade = (int)$zc_install->getConfigKey('is_upgrade');

$redetect_requested = (isset($_POST['rediscover'])) ? $_POST['rediscover'] :'';
if (!empty($redetect_requested)) $_POST = '';
if (!empty($redetect_requested)) $is_upgrade = 0;

// init some vars:
$enable_ssl = '';
$enable_ssl_admin = '';

/*
 * read existing settings instead of trying to detect from first install
 */
if ($is_upgrade) {
   $http_server = zen_read_config_value('HTTP_SERVER', FALSE);
   $http_catalog = zen_read_config_value('DIR_WS_CATALOG', FALSE);
   $virtual_path = str_replace('http://','',$http_server) . $http_catalog;
   $virtual_https_server = str_replace('https://','',zen_read_config_value('HTTPS_SERVER', FALSE));
   $virtual_https_path = $virtual_https_server . zen_read_config_value('DIR_WS_HTTPS_CATALOG', FALSE);
   $enable_ssl = zen_read_config_value('ENABLE_SSL', FALSE);
   $enable_ssl_admin = zen_read_config_value('ENABLE_SSL_ADMIN', FALSE);
   $dir_fs_www_root = zen_read_config_value('DIR_FS_CATALOG', FALSE);
   $https_catalog = zen_read_config_value('DIR_WS_HTTPS_CATALOG', FALSE);

   $http_server = $zc_install->trimTrailingSlash($http_server);
   $http_catalog = $zc_install->trimTrailingSlash($http_catalog);
   $virtual_path = $zc_install->trimTrailingSlash($virtual_path);
   $virtual_https_server = $zc_install->trimTrailingSlash($virtual_https_server);
   $virtual_https_path = $zc_install->trimTrailingSlash($virtual_https_path);
   $dir_fs_www_root = $zc_install->trimTrailingSlash($dir_fs_www_root);
   $https_catalog = $zc_install->trimTrailingSlash($https_catalog);


} else { //fresh install, so do auto-detect of several settings
  $dir_fs_www_root = $zc_install->detectDocumentRoot();

  // Determine http path
  $virtual_path = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
  $virtual_path = substr($virtual_path, 0, strpos($virtual_path, '/zc_install'));

  // Determine the https directory.  This is a best-guess since we're not likely installing over SSL connection:
  $virtual_https_server = getenv('HTTP_HOST');
  $virtual_https_path = $virtual_path;

} //endif $is_upgradable

  // Yahoo hosting and others may use / for physical path ... so instead of leaving it blank, offer '/'
  if ($dir_fs_www_root == '') $dir_fs_www_root = '/';


  // Set form input values
  if (!isset($_POST['physical_path'])) $_POST['physical_path']=$dir_fs_www_root;
  if (!isset($_POST['virtual_http_path'])) $_POST['virtual_http_path']= 'http://' . $virtual_path;
  if (!isset($_POST['virtual_https_path'])) $_POST['virtual_https_path']='https://' . $virtual_https_path;
  if (!isset($_POST['virtual_https_server'])) $_POST['virtual_https_server']='https://' . $virtual_https_server;
  if (!isset($_POST['enable_ssl'])) $_POST['enable_ssl']=$enable_ssl;
  if (!isset($_POST['enable_ssl_admin'])) $_POST['enable_ssl_admin']=$enable_ssl_admin;

  if (isset($_POST['submit'])) {
    $zc_install->isEmpty($_POST['physical_path'], ERROR_TEXT_PHYSICAL_PATH_ISEMPTY, ERROR_CODE_PHYSICAL_PATH_ISEMPTY);
    $zc_install->fileExists($zc_install->trimTrailingSlash($_POST['physical_path']) . '/index.php', ERROR_TEXT_PHYSICAL_PATH_INCORRECT, ERROR_CODE_PHYSICAL_PATH_INCORRECT);
    $zc_install->isEmpty($_POST['virtual_http_path'], ERROR_TEXT_VIRTUAL_HTTP_ISEMPTY, ERROR_CODE_VIRTUAL_HTTP_ISEMPTY);
    if ($_POST['enable_ssl'] == 'true' || $_POST['enable_ssl_admin'] == 'true') {
      // @TODO: actually *test* the HTTPS URL if supplied, to determine whether it's actually valid or not.
      $zc_install->isEmpty($_POST['virtual_https_path'], ERROR_TEXT_VIRTUAL_HTTPS_ISEMPTY, ERROR_CODE_VIRTUAL_HTTPS_ISEMPTY);
      $zc_install->isEmpty($_POST['virtual_https_server'], ERROR_TEXT_VIRTUAL_HTTPS_SERVER_ISEMPTY, ERROR_CODE_VIRTUAL_HTTPS_SERVER_ISEMPTY);
    }

    if (!$zc_install->fatal_error) {
      $zc_install->setConfigKey('DIR_FS_CATALOG', $zc_install->trimTrailingSlash($_POST['physical_path']));
      $zc_install->setConfigKey('virtual_http_path', $zc_install->trimTrailingSlash($_POST['virtual_http_path']));
      $zc_install->setConfigKey('virtual_https_path', $zc_install->trimTrailingSlash($_POST['virtual_https_path']));
      $zc_install->setConfigKey('virtual_https_server', $zc_install->trimTrailingSlash($_POST['virtual_https_server']));
      $zc_install->setConfigKey('ENABLE_SSL', $_POST['enable_ssl']);
      $zc_install->setConfigKey('ENABLE_SSL_ADMIN', $_POST['enable_ssl_admin']);
      header('location: index.php?main_page=config_checkup&action=write' . zcInstallAddSID() );
    exit;
    }
  }

  // quick sanitization
  foreach($_POST as $key=>$val) {
    if(is_array($val)){
      foreach($val as $key2 => $val2){
        $_POST[$key][$key2] = htmlspecialchars($val2, ENT_COMPAT, CHARSET, FALSE);
      }
    } else {
      $_POST[$key] = htmlspecialchars($val, ENT_COMPAT, CHARSET, FALSE);
    }
  }

  setInputValue($_POST['physical_path'], 'PHYSICAL_PATH_VALUE', $dir_fs_www_root);
  setInputValue($_POST['virtual_http_path'], 'VIRTUAL_HTTP_PATH_VALUE', 'http://' . $virtual_path);
  setInputValue($_POST['virtual_https_path'], 'VIRTUAL_HTTPS_PATH_VALUE', 'https://' . $virtual_https_path);
  setInputValue($_POST['virtual_https_server'], 'VIRTUAL_HTTPS_SERVER_VALUE', 'https://' . $virtual_https_server);
  setRadioChecked($_POST['enable_ssl'], 'ENABLE_SSL', $enable_ssl);
  setRadioChecked($_POST['enable_ssl_admin'], 'ENABLE_SSL_ADMIN', $enable_ssl_admin);
