<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header_php.php 6981 2007-09-12 18:26:56Z drbyte $
 */

  $zc_install->resetConfigKeys();

  if (isset($_POST['submit'])) {
    if (isset($_POST['license_consent']) && $_POST['license_consent'] == 'agree') {
      header('location: index.php?main_page=inspect' . zcInstallAddSID() );
      exit;
    }
    if (isset($_POST['license_consent']) && $_POST['license_consent'] == 'disagree') {
      header('location: index.php' . zcInstallAddSID() );
      exit;
    }
  }
?>