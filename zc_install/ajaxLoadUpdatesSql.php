<?php
/**
 * ajaxLoadUpdatesSql.php
 * @package Installer
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', realpath(__DIR__ . '/') . '/');
define('DIR_FS_ROOT', realpath(__DIR__ . '/../') . '/');

require(DIR_FS_INSTALL . 'includes/application_top.php');

$error = FALSE;
$errorList = array();
$db_type = 'mysql';
$updateList = array('1.5.0'=>array('required'=>'1.3.9'),'1.5.1'=>array('required'=>'1.5.0'),'1.5.2'=>array('required'=>'1.5.1'),'1.5.3'=>array('required'=>'1.5.2'),'1.6.0'=>array('required'=>'1.5.3'));

$systemChecker = new systemChecker();
$dbVersion = $systemChecker->findCurrentDbVersion();

$updateVersion = str_replace('version-', '', $_POST['version']);
$updateVersion = str_replace('_', '.', $updateVersion);
$versionInfo = $updateList[$updateVersion];

if ($versionInfo['required'] != $dbVersion)
{
  $error = TRUE;
  //@TODO - language string to lang file
  $errorList[] = "Could not update to version " . $updateVersion . " Version " . $versionInfo['required'] . 'update required';
}
if (!$error)
{
  require_once(DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');
  $file = DIR_FS_INSTALL . 'sql/updates/' . $db_type . '_upgrade_zencart_' . str_replace('.', '', $updateVersion) . '.sql';
  $options = $systemChecker->getDbConfigOptions();
  $dbInstaller = new zcDatabaseInstaller($options);
  $result = $dbInstaller->getConnection();
  $errorUpg = $dbInstaller->parseSqlFile($file);
}
echo json_encode(array('error'=>$error, 'version'=>$_POST['version'], 'errorList'=>$errorList));
