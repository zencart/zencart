<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Modified in v1.5.6 $
 */

$otherConfigErrors = FALSE;
$hasUpgradeErrors = FALSE;
$selectedAdminDir = '';
$adminDirectoryList = systemChecker::getAdminDirectoryList();
$selectedAdminDir = $adminDirectoryList[0];
$hasMultipleAdmins = FALSE;
if (count($adminDirectoryList) > 1)
{
    $hasMultipleAdmins = TRUE;
}
if (isset($_POST['adminDir']))
{
  $selectedAdminDir = zen_output_string_protected($_POST['adminDir']);
}
$systemChecker = new systemChecker($selectedAdminDir);
if (isset($_POST['updateConfigure'])) {
    require_once (DIR_FS_INSTALL . 'includes/classes/class.zcConfigureFileReader.php');
    require_once (DIR_FS_INSTALL . 'includes/classes/class.zcConfigureFileWriter.php');
    if ($_POST['btnsubmit'] != TEXT_REFRESH) {
        $configFile = DIR_FS_ROOT . 'includes/configure.php';
        $configFileLocal = DIR_FS_ROOT . 'includes/local/configure.php';
        if (file_exists($configFileLocal)) $configFile = $configFileLocal;
        $storeConfigureFileReader = new zcConfigureFileReader($configFile);

        $admConfigFile = DIR_FS_ROOT . $selectedAdminDir . '/includes/configure.php';
        $admConfigFileLocal = DIR_FS_ROOT . $selectedAdminDir . '/includes/local/configure.php';
        if (file_exists($admConfigFileLocal)) $admConfigFile = $admConfigFileLocal;
        $adminConfigureFileReader = new zcConfigureFileReader($admConfigFile);

        $configureInputs = $storeConfigureFileReader->getStoreInputsFromLegacy();
        $configureInputs['enable_ssl_admin'] = trim($adminConfigureFileReader->getRawDefine('ENABLE_SSL_ADMIN'), "'");
        $configureInputs['http_server_admin'] = trim($adminConfigureFileReader->getRawDefine( ($configureInputs['enable_ssl_admin'] == 'true' ? 'HTTPS_SERVER' : 'HTTP_SERVER') ), "'");
        $configureInputs['adminDir'] = $selectedAdminDir;
        $storeConfigureFileWriter = new zcConfigureFileWriter($configureInputs);
    }
}
$dbVersion = $systemChecker->findCurrentDbVersion();
$currentDbVersion = EXPECTED_DATABASE_VERSION_MAJOR . '.' . EXPECTED_DATABASE_VERSION_MINOR;
$isCurrentDb = ($dbVersion == $currentDbVersion) ? TRUE : FALSE;
$hasSaneConfigFile = $systemChecker->hasSaneConfigFile();
$hasUpdatedConfigFile = $systemChecker->hasUpdatedConfigFile();


if ($hasSaneConfigFile && $hasUpdatedConfigFile)
{
  $systemChecker->addRunLevel('upgradeDb');
}
$errorList = $systemChecker->runTests();
list($hasFatalErrors, $listFatalErrors) = $systemChecker->getErrorList();
list($hasWarnErrors, $listWarnErrors) = $systemChecker->getErrorList('WARN');
list($hasLocalAlerts, $listLocalAlerts) = $systemChecker->getErrorList('ALERT');
if (isset($listFatalErrors[0]['methods']))
{
  $res = key($listFatalErrors[0]['methods']);
  if ($res == 'CheckWriteableAdminFile') $otherConfigErrors = TRUE;
}
if (count($listFatalErrors) == 1)
{
  if ($listFatalErrors[0]['runLevel'] == 'upgradeDb')
  {
    $hasUpgradeErrors = TRUE;
  }
}
$formAction = 'system_setup';
if (!$hasFatalErrors && $hasSaneConfigFile && !$hasUpgradeErrors && !$isCurrentDb)
{
  $formAction = 'database_upgrade';
}
if (!$hasUpdatedConfigFile)
{
    $formAction = 'index';
}

$adminOptionList = array();
foreach ($adminDirectoryList as $directory)
{
  $adminOptionList[] = array('id' => $directory, 'text' => $directory);
}
