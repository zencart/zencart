<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jul 17 Modified in v1.5.7 $
 */

if (!file_exists(DIR_FS_INSTALL . 'includes/custom_settings.php')) {
  echo 'Error: could not find the zc_install/includes/custom_settings.php file.' . "\n\n";
  exit(1);
}
require (DIR_FS_INSTALL . 'custom_settings.php');
if (!isset($zc_settings) || !is_array($zc_settings)) {
  echo 'Error: $zc_settings array not found in custom_settings.php';
  exit(1);
}
$isUpgrade = FALSE;

$otherConfigErrors = FALSE;
$hasUpgradeErrors = FALSE;
$selectedAdminDir = file_exists(DIR_FS_ROOT . $zc_settings['adminDir']) ? $zc_settings['adminDir'] : 'admin';
$systemChecker = new systemChecker($selectedAdminDir);
$dbVersion = $systemChecker->findCurrentDbVersion();
$currentDbVersion = EXPECTED_DATABASE_VERSION_MAJOR . '.' . EXPECTED_DATABASE_VERSION_MINOR;
$isCurrentDb = ($dbVersion == $currentDbVersion) ? TRUE : FALSE;
$hasSaneConfigFile = $systemChecker->hasSaneConfigFile();
$hasTables = $systemChecker->hasTables();
$hasUpdatedConfigFile = $systemChecker->hasUpdatedConfigFile();
$errorList = $systemChecker->runTests();
list($hasFatalErrors, $listFatalErrors) = $systemChecker->getErrorList();
list($hasWarnErrors, $listWarnErrors) = $systemChecker->getErrorList('WARN');
if (isset($listFatalErrors[0]['methods']))
{
  $res = key($listFatalErrors[0]['methods']);
  if ($res == 'CheckWriteableAdminFile') $otherConfigErrors = TRUE;
}
$adminDirectoryList = systemChecker::getAdminDirectoryList();
$hasMultipleAdmins = FALSE;
$selectedAdminDir = file_exists(DIR_FS_ROOT . $zc_settings['adminDir']) ? $zc_settings['adminDir'] : 'admin';
if (count($adminDirectoryList) > 1)
{
  $hasMultipleAdmins = TRUE;
  echo 'Multiple Admin Folders Found:';
  foreach ($adminDirectoryList as $directory)
  {
    echo $directory;
    if ($directory == $zc_settings['adminDir']) echo ' (selected)';
    echo "\n";
  }
} else
{
  $selectedAdminDir = $adminDirectoryList[0];
  echo 'Selected Admin Folder: ' . $selectedAdminDir . "\n";
}
// do auto-detections
list($adminDir, $documentRoot, $adminServer, $catalogHttpServer, $catalogHttpUrl, $catalogHttpsServer, $catalogHttpsUrl, $dir_ws_http_catalog, $dir_ws_https_catalog) = getDetectedURIs();
$db_type = 'mysql';
$db_charset = 'utf8mb4';
$db_prefix = '';
$sql_cache_method = 'none'; // 'file', 'database'
$db_host = isset($zc_settings['db_host']) ? $zc_settings['db_host'] : 'localhost';
$db_name = isset($zc_settings['db_name']) ? $zc_settings['db_name'] : 'zencart';
$db_user = isset($zc_settings['db_user']) ? $zc_settings['db_user'] : '';
$db_password= isset($zc_settings['db_password']) ? $zc_settings['db_password'] : '';

require (DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');

$admin_password = zen_create_PADSS_password();

if (isset($_POST['http_server_catalog']))
{
  require (DIR_FS_INSTALL . 'includes/classes/class.zcConfigureFileWriter.php');
  $result = new zcConfigureFileWriter($_POST);
}




require (DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');
if ($isUpgrade == FALSE) {
  $options = $_POST;
  $dbInstaller = new zcDatabaseInstaller($options);
  $result = $dbInstaller->getConnection();
  $extendedOptions = array();
  $dbInstaller->doCompletion($options);
}
