<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */

  require (DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');

  if (isset($_POST['upgrade_mode']) && $_POST['upgrade_mode'] == 'yes')
  {
    $isUpgrade = TRUE;
  } else
  {
    $isUpgrade = FALSE;
    $adminLink = zen_output_string_protected($_POST['http_server_admin']) . zen_output_string_protected($_POST['dir_ws_http_catalog']) . zen_output_string_protected($_POST['admin_directory']);
    $catalogLink = zen_output_string_protected($_POST['http_server_catalog']) . zen_output_string_protected($_POST['dir_ws_http_catalog']);
    $options = $_POST;
    $dbInstaller = new zcDatabaseInstaller($options);
    $result = $dbInstaller->getConnection();
    $extendedOptions = array();
    $error = $dbInstaller->doCompletion($options);
  }
