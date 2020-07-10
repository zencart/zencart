<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Tue Feb 16 15:03:47 2016 -0500 Modified in v1.5.5 $
 */

  @unlink(DEBUG_LOG_FOLDER . '/progress.json');
  require (DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');
  $changedDir = (bool)$_POST['changedDir'];
  $adminDir = $_POST['adminDir'];
  $adminNewDir = $_POST['adminNewDir'];
  if (defined('DEVELOPER_MODE') && DEVELOPER_MODE === true)
  {
    $admin_password = 'developer1';
  } else {
    $admin_password = zen_create_PADSS_password();
  }
  if (isset($_POST['upgrade_mode']) && $_POST['upgrade_mode'] == 'yes')
  {
    $isUpgrade = TRUE;
  } else if (isset($_POST['http_server_catalog']))
  {
    $isUpgrade = FALSE;
    require (DIR_FS_INSTALL . 'includes/classes/class.zcConfigureFileWriter.php');
    $result = new zcConfigureFileWriter($_POST);

    $errors = $result->errors;
  }
