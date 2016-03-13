<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sun Mar 13 2016  Modified in v1.5.5 $
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
    // Note: Can't use a language constant here, because that would require one to be defined :)
    $messageStack->add('ERROR: No default language defined.', 'error');
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

/**
 * set which precautions should be checked
 */
/**
 * should a message be displayed if install directory exists
 */
define('WARN_INSTALL_EXISTENCE', 'true');
/**
 * should a message be displayed if  config directory is writeable
 */
define('WARN_CONFIG_WRITEABLE', 'true');
/**
 * should a message be displayed if sql cache directory not writeable
 */
define('WARN_SQL_CACHE_DIRECTORY_NOT_WRITEABLE', 'true');
/**
 * should a message be displayed if session.autostart is on in php.ini
 */
define('WARN_SESSION_AUTO_START', 'true');
/**
 * should a message be displayed if download directory not readable
 */
define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');
/**
 * should a message be displayed if system detects version problem with the database
 */
define('WARN_DATABASE_VERSION_PROBLEM','true');
// check if the installer directory exists, and warn of its existence
if (WARN_INSTALL_EXISTENCE == 'true') {
  $check_path = realpath(DIR_FS_CATALOG . '/zc_install');
  if (is_dir($check_path)) {
    $messageStack->add(sprintf(WARNING_INSTALL_DIRECTORY_EXISTS, ($check_path == '' ? '..../zc_install' : $check_path)), 'warning');
  }
}

// check if the configure.php file is writeable
if (WARN_CONFIG_WRITEABLE == 'true') {
  $check_path = realpath(DIR_FS_CATALOG . '/includes/configure.php');
  if (file_exists($check_path) && is__writeable($check_path)) {
    $messageStack->add(sprintf(WARNING_CONFIG_FILE_WRITEABLE, ($check_path == '' ? '..../includes/configure.php' : $check_path)), 'warning');
  }
}

// check if the sql cache folder is writeable
if (WARN_SQL_CACHE_DIRECTORY_NOT_WRITEABLE == 'true' && strtolower(SQL_CACHE_METHOD) == 'file') {
  if (!is_dir(DIR_FS_SQL_CACHE)) {
    $messageStack->add(WARNING_SQL_CACHE_DIRECTORY_NON_EXISTENT, 'warning');
  } elseif (!is_writeable(DIR_FS_SQL_CACHE)) {
    $messageStack->add(WARNING_SQL_CACHE_DIRECTORY_NOT_WRITEABLE, 'warning');
  }
}

// check session.auto_start is disabled
if (function_exists('ini_get') && WARN_SESSION_AUTO_START == 'true') {
  if (ini_get('session.auto_start') == '1') {
    $messageStack->add(WARNING_SESSION_AUTO_START, 'warning');
  }
}

// to warn if the "downloads" folder is not readable (ie: not found, etc)
if ( WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true' && DOWNLOAD_ENABLED == 'true') {
  if (!is_dir(DIR_FS_DOWNLOAD)) {
    $messageStack->add(WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT, 'warning');
  }
}

// check database version against source code
$zv_db_patch_ok = true; // we start with true
if (WARN_DATABASE_VERSION_PROBLEM != 'false') {
  $result = $db->Execute("SELECT project_version_major, project_version_minor FROM " . TABLE_PROJECT_VERSION . " WHERE project_version_key = 'Zen-Cart Database'");
  $zv_db_patch_level_found = $result->fields['project_version_major']. '.' . $result->fields['project_version_minor'];
  $zv_db_patch_level_expected = EXPECTED_DATABASE_VERSION_MAJOR . '.' . EXPECTED_DATABASE_VERSION_MINOR;
  if ($zv_db_patch_level_expected=='.' || ($zv_db_patch_level_found < $zv_db_patch_level_expected) ) {
    $zv_db_patch_ok = false;
    $messageStack->add(WARNING_DATABASE_VERSION_OUT_OF_DATE, 'warning');
  }
}

// check for insecure default passwords, and present warning if found
// include the password crypto functions
  require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'password_funcs.php');
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

// Check that shipping/payment modules have been defined
  if (zen_get_configuration_key_value('MODULE_PAYMENT_INSTALLED') == '') {
    $messageStack->add(ERROR_PAYMENT_MODULES_NOT_DEFINED, 'caution');
  }
  if (zen_get_configuration_key_value('MODULE_SHIPPING_INSTALLED') == '') {
    $messageStack->add(ERROR_SHIPPING_MODULES_NOT_DEFINED, 'caution');
  }

// if welcome email coupon is set and <= 21 days warn shop owner
    if (NEW_SIGNUP_DISCOUNT_COUPON > 0) {
      $zc_welcome_check = $db->Execute("SELECT coupon_expire_date from " . TABLE_COUPONS . " WHERE coupon_id=" . (int)NEW_SIGNUP_DISCOUNT_COUPON);
      $zc_current_date = date('Y-m-d');
      $zc_days_to_expire = zen_date_diff($zc_current_date, $zc_welcome_check->fields['coupon_expire_date']);
      if ($zc_days_to_expire <= 21) {
        $zc_caution_warning = ($zc_days_to_expire <= 5 ? 'warning' : 'caution');
        $messageStack->add(sprintf(WARNING_WELCOME_DISCOUNT_COUPON_EXPIRES_IN, $zc_days_to_expire), $zc_caution_warning);
      }
    }

// Alerts for EZ-Pages
  if (EZPAGES_STATUS_HEADER == '2' and strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) {
    $messageStack->add(TEXT_EZPAGES_STATUS_HEADER_ADMIN, 'caution');
  }
  if (EZPAGES_STATUS_FOOTER == '2' and strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) {
    $messageStack->add(TEXT_EZPAGES_STATUS_FOOTER_ADMIN, 'caution');
  }
  if (EZPAGES_STATUS_SIDEBOX == '2' and strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) {
    $messageStack->add(TEXT_EZPAGES_STATUS_SIDEBOX_ADMIN, 'caution');
  }

// Editor alerts
  if (HTML_EDITOR_PREFERENCE != 'NONE' && !is_dir(DIR_FS_CATALOG . 'editors')) {
    $messageStack->add(ERROR_EDITORS_FOLDER_NOT_FOUND, 'caution');
  }

// check activity log size
  if (basename($PHP_SELF) == FILENAME_DEFAULT . '.php') {
    $show_admin_activity_log_link = false;

    $chk_admin_log = $db->Execute("select count(log_id) as counter from " . TABLE_ADMIN_ACTIVITY_LOG);
    if ($chk_admin_log->fields['counter'] > 0) {
      if ($chk_admin_log->fields['counter'] > 50000) {
        $show_admin_activity_log_link = true;
        $_SESSION['reset_admin_activity_log'] = true;
        $messageStack->add(WARNING_ADMIN_ACTIVITY_LOG_RECORDS . $chk_admin_log->fields['counter'], 'caution');
      }

      $chk_admin_log = $db->Execute("select min(access_date) as access_date from " . TABLE_ADMIN_ACTIVITY_LOG . " where access_date < DATE_SUB(CURDATE(),INTERVAL 60 DAY)");
      if (!empty($chk_admin_log->fields['access_date'])) {
        $show_admin_activity_log_link = true;
        $_SESSION['reset_admin_activity_log'] = true;
        $messageStack->add(WARNING_ADMIN_ACTIVITY_LOG_DATE . date('m-d-Y', strtotime($chk_admin_log->fields['access_date'])), 'caution');
      }
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
