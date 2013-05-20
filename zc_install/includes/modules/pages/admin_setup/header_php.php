<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */

  $adminDir = (isset($_POST['adminDir'])) ? zen_output_string_protected($_POST['adminDir']) : 'admin';
  $admin_password = zen_create_PADSS_password();
  $wordlist = file(DIR_FS_INSTALL . 'includes/wordlist.csv');
  $max = count($wordlist) - 1;
  $word1 = trim($wordlist[zen_pwd_rand(0,$max)]);
  $pos = zen_pwd_rand(0,4);
  $word1[$pos] = strtoupper($word1[$pos]);
  $word3 = trim($wordlist[zen_pwd_rand(0,$max)]);
  $pos = zen_pwd_rand(0,4);
  $word3[$pos] = strtoupper($word3[$pos]);
  $word2 = zen_create_random_value(3, 'chars');
  $adminNewDir =  $word1 . '-' . $word2 . '-' . $word3;
  $result = @rename(DIR_FS_ROOT . $adminDir, DIR_FS_ROOT . $adminNewDir);
  if (!$result) $adminNewDir = 'admin';

