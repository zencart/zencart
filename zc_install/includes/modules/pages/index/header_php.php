<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Sat Dec 5 18:49:20 2015 +0000 Modified in v1.5.5 $
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
        $storeConfigureFileReader = new zcConfigureFileReader(DIR_FS_ROOT .'includes/configure.php');
        $storeConfigureFileReader = new zcConfigureFileReader(DIR_FS_ROOT . $selectedAdminDir . '/includes/configure.php');
        $configureInputs = $storeConfigureFileReader->getStoreInputsFromLegacy();
        $configureInputs['http_server_admin'] = trim($storeConfigureFileReader->getRawDefine('ADMIN_HTTP_SERVER'), "'");
        $configureInputs['adminDir'] = $selectedAdminDir;
        $storeConfigureFileWriter = new zcConfigureFileWriter($configureInputs);
    }
}
$dbVersion = $systemChecker->findCurrentDbVersion();
$currentDbVersion = EXPECTED_DATABASE_VERSION_MAJOR . '.' . EXPECTED_DATABASE_VERSION_MINOR;
$isCurrentDb = ($dbVersion == $currentDbVersion) ? TRUE : FALSE;
$hasSaneConfigFile = $systemChecker->hasSaneConfigFile();
$hasUpdatedConfigFile = $systemChecker->hasUpdatedConfigFile();
// echo var_dump($dbVersion);
// echo var_dump($isCurrentDb);
// echo var_dump($hasSaneConfigFile);
// echo var_dump($hasUpdatedConfigFile);

if ($hasSaneConfigFile && $hasUpdatedConfigFile)
{
  $systemChecker->addRunLevel('upgradeDb');
}
$errorList = $systemChecker->runTests();
list($hasFatalErrors, $listFatalErrors) = $systemChecker->getErrorList();
list($hasWarnErrors, $listWarnErrors) = $systemChecker->getErrorList('WARN');
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
