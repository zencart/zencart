<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: Ian Wilson  Sun Jul 22 20:06:40 2012 +0100 Modified in v1.5.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// check if a default currency is set
  if (!defined('DEFAULT_CURRENCY')) {
    $messageStack->add(ERROR_NO_DEFAULT_CURRENCY_DEFINED, 'error');
  }

// check if a default language is set
  if (!defined('DEFAULT_LANGUAGE') || DEFAULT_LANGUAGE=='') {
    $messageStack->add(ERROR_NO_DEFAULT_LANGUAGE_DEFINED, 'error');
  }

  if (function_exists('ini_get') && ((bool)ini_get('file_uploads') == false) ) {
    $messageStack->add(WARNING_FILE_UPLOADS_DISABLED, 'warning');
  }

// set demo message
  if (zen_get_configuration_key_value('ADMIN_DEMO')=='1') {
    if (zen_admin_demo()) {
      $messageStack->add(ADMIN_DEMO_ACTIVE, 'warning');
    } else {
      $messageStack->add(ADMIN_DEMO_ACTIVE_EXCLUSION, 'warning');
    }
  }

  // check if email subsystem has been disabled
  if (SEND_EMAILS != 'true') {
    $messageStack->add(WARNING_EMAIL_SYSTEM_DISABLED, 'error');
  }

  // this will let the admin know that the website is DOWN FOR MAINTENANCE to the public
  if (DOWN_FOR_MAINTENANCE == 'true') {
    $messageStack->add(WARNING_ADMIN_DOWN_FOR_MAINTENANCE,'caution');
  }

// include the password crypto functions
  require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'password_funcs.php');

// default admin settings
  $admin_security = false;

  $demo_check = $db->Execute("select * from " . TABLE_ADMIN . " where admin_name='demo' or admin_name='Admin'");
  if (!$demo_check->EOF) {

    $cnt_admin= 0;
    while (!$demo_check->EOF) {
      $checking = $demo_check->fields['admin_pass'];
      if (($demo_check->fields['admin_name'] =='Admin' and zen_validate_password('admin', $checking))) {
        $admin_security = true;
        $cnt_admin++;
      }
      if (($demo_check->fields['admin_name'] =='demo' and zen_validate_password('demoonly', $checking))) {
        $admin_security = true;
        $cnt_admin++;
      }

      $demo_check->MoveNext();
    }

    if ($admin_security == true) {
      $messageStack->add(ERROR_ADMIN_SECURITY_WARNING, 'caution');
    }
  }

  // log cleanup for zc_install "info logs". This still leaves behind any zcInstallDEBUG or zcInstallException log files
  if ($za_dir = @dir(DIR_FS_LOGS)) {
    while ($zv_file = $za_dir->read()) {
      if (preg_match('/^zcInstallLog.*\.log$/', $zv_file)) {
        unlink(DIR_FS_LOGS . '/' . $zv_file);
      }
    }
    $za_dir->close();
    unset($za_dir);
  }
