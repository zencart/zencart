<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Jul 7 2014 Modified in v1.5.4 $
 */
/**
 * System check for valid SESSION_WRITE_DIRECTORY value
 * (This value is server-dependent, and when a store is moved to another server this value must be updated for admin sessions to work correctly.
 *  The following uses the DIR_FS_SQL_CACHE from the admin/includes/configure.php (or the /local/ one if it exists) if it points to a valid folder,
 *  else it uses the /cache/ directory located in the catalog area.)
 */

if (!file_exists(SESSION_WRITE_DIRECTORY) || !is_writable(SESSION_WRITE_DIRECTORY)) {
  zen_record_admin_activity('Session directory folder not found. Will attempt to re-detect and update configuration. Old value: ' . SESSION_WRITE_DIRECTORY, 'notice');

  $possible_dir[] = DIR_FS_SQL_CACHE;
  $possible_dir[] = DIR_FS_CATALOG . 'cache';
  $possible_dir[] = realpath(__DIR__ . '/../') . '/cache';

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

  $sql = "update " . TABLE_CONFIGURATION . " set configuration_value = '" . $db->prepare_input(trim($selected_dir)) . "' where configuration_key = 'SESSION_WRITE_DIRECTORY'";
  $db->Execute($sql);
  zen_record_admin_activity('Updated SESSION_WRITE_DIRECTORY configuration setting to ' . $selected_dir, 'notice');

  if (!file_exists($selected_dir) || !is_writable($selected_dir)) {
    die('ALERT: Your cache directory does not exist or is not writable: ' . $selected_dir . ' ... This must be fixed before the page can load correctly.');
  }

  zen_redirect(zen_href_link(FILENAME_DEFAULT));
  exit(1);
}
