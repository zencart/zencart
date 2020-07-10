<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 12 Modified in v1.5.7 $
 */
/**
 * System check for valid SESSION_WRITE_DIRECTORY value
 * (This value is server-dependent, and when a store is moved to another server this value must be updated for admin sessions to work correctly.
 *  The following uses the DIR_FS_SQL_CACHE from the admin/includes/configure.php (or the /local/ one if it exists) if it points to a valid folder,
 *  else it uses the /cache/ directory located in the catalog area.)
 */

if (!file_exists(SESSION_WRITE_DIRECTORY) || !is_writable(SESSION_WRITE_DIRECTORY)) {
  zen_record_admin_activity('Session directory folder not found. Will attempt to re-detect and update configuration. Old value: ' . SESSION_WRITE_DIRECTORY, 'notice');
  define('DIR_FS_ROOT', realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/../') . '/');

  $possible_dir = array();
  $possible_dir[] = DIR_FS_SQL_CACHE;
  $possible_dir[] = DIR_FS_CATALOG . 'cache';
  $possible_dir[] = DIR_FS_ROOT . 'cache';

  $selected_dir = DIR_FS_CATALOG . 'cache';

  foreach($possible_dir as $dir) {
    if (!file_exists($dir)) {
      unset($dir);
      continue;
    }
    if (!is_writable($dir)) {
      unset($dir);
      continue;
    }
    $selected_dir = $dir;
  }
  if ($selected_dir == '') $selected_dir = DIR_FS_CATALOG . 'cache';

  $sql = "SELECT configuration_key FROM " . TABLE_CONFIGURATION . "  WHERE configuration_key = 'SESSION_WRITE_DIRECTORY'";
  $conf_count = $db->Execute($sql);

  if (empty($conf_count->RecordCount())) {
    $sql = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Session Directory', 'SESSION_WRITE_DIRECTORY', '/tmp', 'This should point to the folder specified in your DIR_FS_SQL_CACHE setting in your configure.php files.', '15', '1', now())";
    $db->Execute($sql);
  }
  $sql = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '" . $db->prepare_input(trim($selected_dir)) . "' WHERE configuration_key = 'SESSION_WRITE_DIRECTORY'";
  $db->Execute($sql);
  zen_record_admin_activity('Updated SESSION_WRITE_DIRECTORY configuration setting to ' . $selected_dir, 'notice');

  if (!file_exists($selected_dir) || !is_writable($selected_dir)) {
    die('ALERT: Your cache directory does not exist or is not writable: ' . $selected_dir . ' ... This must be fixed before the page can load correctly.');
  }

  zen_redirect(zen_href_link(FILENAME_DEFAULT));
  exit(1);
}
