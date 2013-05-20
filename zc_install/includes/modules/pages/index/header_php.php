<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */

$otherConfigErrors = FALSE;
$hasUpgradeErrors = FALSE;
$systemChecker = new systemChecker();
$dbVersion = $systemChecker->findCurrentDbVersion();
$currentDbVersion = EXPECTED_DATABASE_VERSION_MAJOR . '.' . EXPECTED_DATABASE_VERSION_MINOR;
$isCurrentDb = ($dbVersion == $currentDbVersion) ? TRUE : FALSE;
$hasSaneConfigFile = $systemChecker->hasSaneConfigFile();
$hasUpdatedConfigFile = $systemChecker->hasUpdatedConfigFile();
if ($hasSaneConfigFile)
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
$adminDirectoryList = systemChecker::getAdminDirectoryList();
$hasMultipleAdmins = FALSE;
if (count($adminDirectoryList) > 1)
{
  $hasMultipleAdmins = TRUE;
}
$formAction = 'system_setup';
if (!$hasFatalErrors && $hasSaneConfigFile && !$hasUpgradeErrors && !$isCurrentDb) 
{
  $formAction = 'database_upgrade';
} 
$adminOptionList = array();
foreach ($adminDirectoryList as $directory)
{
  $adminOptionList[] = array('id' => $directory, 'text' => $directory);
}