<?php
/**
 * ajaxValidateAdminCredentials.php
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', realpath(dirname(__FILE__) . '/') . '/');
define('DIR_FS_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

require(DIR_FS_INSTALL . 'includes/application_top.php');

$error = FALSE;
$adminUser = $_POST['admin_user'];
$adminPassword = $_POST['admin_password'];
$systemChecker = new systemChecker();
$result = $systemChecker->validateAdminCredentials($adminUser, $adminPassword);
if ($result === FALSE || $result === TRUE)
{	
  $error = !$result;
  $adminCandidate = '';
} else 
{
	$error = FALSE;
	$adminCandidate = $result;
}
echo json_encode(array('error'=>$error, 'adminCandidate'=>$adminCandidate));