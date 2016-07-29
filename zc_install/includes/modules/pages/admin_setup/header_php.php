<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Tue Feb 16 15:03:47 2016 -0500 Modified in v1.5.5 $
 */

  @unlink(DEBUG_LOG_FOLDER . '/progress.json');
  require (DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');
  require(DIR_FS_INSTALL . DIR_WS_INSTALL_TEMPLATE . 'common/developer_mode_helper.php');
  $adminConfigSettings = $_POST;
  $changedDir = (bool)$adminConfigSettings['changedDir'];
  $adminDir = $adminConfigSettings['adminDir'];
  $adminNewDir = $adminConfigSettings['adminNewDir'];
  
  if (DEVELOPER_MODE === true)
  {
    $adminConfigSettings['developer_mode'] = 'true';
    $admin_password = 'developer1';
  } else {
    $adminConfigSettings['developer_mode'] = 'false';
    $admin_password = zen_create_PADSS_password();
  }
  if (isset($adminConfigSettings['upgrade_mode']) && $adminConfigSettings['upgrade_mode'] == 'yes')
  {
    $isUpgrade = TRUE;
  } else if (isset($adminConfigSettings['http_server_catalog']))
  {
    $isUpgrade = FALSE;
    require (DIR_FS_INSTALL . 'includes/classes/class.zcConfigureFileWriter.php');
    $result = new zcConfigureFileWriter($adminConfigSettings);

    $errors = $result->errors;
  }
  