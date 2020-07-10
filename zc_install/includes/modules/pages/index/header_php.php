<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Feb 04 Modified in v1.5.7 $
 */

$otherConfigErrors = FALSE;
$hasUpgradeErrors = FALSE;
$selectedAdminDir = '';

$adminDirectoryList = systemChecker::getAdminDirectoryList();
if (empty($adminDirectoryList)) {
    // This should never happen, and zc_install does NOT require it to be named "admin", however the message here says
    // to rename it to "admin" for simplicity of giving instructions and directing the reader to go fix the missing dir problem.
    die('ERROR: unable to locate your admin directory. For simplicity, please be sure it exists and rename it to "admin" before proceeding.');
}
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
    if (!isset($_POST['btnsubmit']) || $_POST['btnsubmit'] != TEXT_REFRESH) {
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
$hasTables = $systemChecker->hasTables();
$hasUpdatedConfigFile = $systemChecker->hasUpdatedConfigFile();


if ($hasTables && $hasSaneConfigFile && $hasUpdatedConfigFile)
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
