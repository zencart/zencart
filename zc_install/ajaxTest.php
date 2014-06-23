<?php
/**
 * ajaxTest.php
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
$updateVersion = 'xxxx';
$versionInfo['required'] = 'yyyy';
//if ($_POST['version'] == 'version-1_5_1') $error = TRUE;
$errorList = array();
$errorList[] = "Could not update to version " . $updateVersion . " Version " . $versionInfo['required'] . 'update required';
sleep(1);
echo json_encode(array('error'=>$error, 'version'=>$_POST['version'], 'errorList'=>$errorList));
