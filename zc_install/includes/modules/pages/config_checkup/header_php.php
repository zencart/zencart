<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 18818 2011-05-31 20:19:34Z drbyte $
 */

$write_config_files_only = ((isset($_POST['submit']) && $_POST['submit']==ONLY_UPDATE_CONFIG_FILES) || (isset($_POST['configfile']) && zen_not_null($_POST['configfile'])) || (isset($_GET['configfile']) && zen_not_null($_GET['configfile'])) || ZC_UPG_DEBUG3 != false) ? true : false;

$is_upgrade = (int)$zc_install->getConfigKey('is_upgrade');
$result = false;
$action = (isset($_GET['action'])) ? $_GET['action'] : '';

/**
 * recheck to see if written files are valid
 */
if ($action == 'recheck') {
  $result = $zc_install->validateConfigFiles($http_server);
}
/**
 * write files and check to see if they're valid
 */
if ($result == false || $action == 'write') {
  $result = $zc_install->writeConfigFiles();
}

// if config files wrote okay, carry on to next step
if ($result == true) {
  $zc_install->resetConfigKeys();
  $zc_install->resetConfigInfo();
  header('location: index.php?main_page=store_setup' /*. zcInstallAddSID()*/ );
  exit;
} else {
  $flag_check_config_keys = true;
}

// otherwise, proceed with displaying file contents for manual setup -- (copy/paste from template output)
