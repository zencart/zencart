<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */

  require (DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');

  $isUpgrade = FALSE;
  $adminLink = $catalogLink = '#';
  $adminServer = isset($_POST['http_server_admin']) ? $_POST['http_server_admin'] : '';
  $catalogHttpServer = isset($_POST['http_server_catalog']) ? $_POST['http_server_catalog'] : '';
  $dir_ws_http_catalog = isset($_POST['dir_ws_http_catalog']) ? $_POST['dir_ws_http_catalog'] : '';
  $adminDir = isset($_POST['admin_directory']) ? $_POST['admin_directory'] : '';
  if (!isset($_POST['admin_directory']) || !file_exists(DIR_FS_ROOT . $_POST['admin_directory'])) {
    $systemChecker = new systemChecker($adminDir);
    $adminDirectoryList = systemChecker::getAdminDirectoryList();
// die('admin list:<pre>'.print_r($adminDirectoryList, TRUE));
    if (count($adminDirectoryList) == 1) $adminDir = $adminDirectoryList[0];
    list($adminDir, $documentRoot, $adminServer, $catalogHttpServer, $catalogHttpUrl, $catalogHttpsServer, $catalogHttpsUrl, $dir_ws_http_catalog, $dir_ws_https_catalog) = getDetectedURIs($adminDir);
  }
  $adminLink = zen_output_string_protected($adminServer) . zen_output_string_protected($dir_ws_http_catalog) . zen_output_string_protected($adminDir);
  $catalogLink = zen_output_string_protected($catalogHttpServer) . zen_output_string_protected($dir_ws_http_catalog);

  if (isset($_POST['upgrade_mode']) && $_POST['upgrade_mode'] == 'yes')
  {
    $isUpgrade = TRUE;
  }
  // only do the next step if there was real POST data, else bad info may be written to database
  else if (isset($_POST['http_server_admin']) && $_POST['http_server_admin'] != '')
  {
    $isUpgrade = FALSE;
    $options = $_POST;
    $dbInstaller = new zcDatabaseInstaller($options);
    $result = $dbInstaller->getConnection();
    $extendedOptions = array();
    $error = $dbInstaller->doCompletion($options);
  }
