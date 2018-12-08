<?php
/**
 * ajaxAdminSetup.php
 * @package Installer
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Tue Sep 11 15:53:41 2018 -0400 Modified in v1.5.6 $
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', __DIR__ . '/');
define('DIR_FS_ROOT', realpath(__DIR__ . '/../') . '/');

require(DIR_FS_INSTALL . 'includes/application_top.php');

$adminDir = $_POST['adminDir'];
$wordlist = file(DIR_FS_INSTALL . 'includes/wordlist.csv');
$max = count($wordlist) - 1;
$word1 = trim($wordlist[zen_pwd_rand(0,$max)]);
$pos = zen_pwd_rand(0,4);
$word1[$pos] = strtoupper($word1[$pos]);
$word3 = trim($wordlist[zen_pwd_rand(0,$max)]);
$pos = zen_pwd_rand(0,4);
$word3[$pos] = strtoupper($word3[$pos]);
$word2 = zen_create_random_value(3, 'chars');
$adminNewDir = $adminDir;
$result = FALSE;
if ($adminDir == 'admin' && (!defined('DEVELOPER_MODE') || DEVELOPER_MODE == false))
{
  $adminNewDir =  $word1 . '-' . $word2 . '-' . $word3;
  $result = @rename(DIR_FS_ROOT . $adminDir, DIR_FS_ROOT . $adminNewDir);
  if ($result === false) {
    $adminNewDir = $adminDir;
  } else {
    $adminDir = $adminNewDir;
  }
}
echo json_encode(array('changedDir'=>(int)$result, 'adminNewDir'=>$adminNewDir, 'adminDir'=>$adminDir));
