<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * System check for valid SESSION_WRITE_DIRECTORY value
 * (This value is server-dependent, and when a store is moved to another server this value must be updated for admin sessions to work correctly.
 *  The following uses the DIR_FS_SQL_CACHE from the admin/includes/configure.php (or the /local/ one if it exists) if it points to a valid folder,
 *  else it uses the /cache/ directory located in the catalog area.)
 */

if (!file_exists(SESSION_WRITE_DIRECTORY) || !is_writable(SESSION_WRITE_DIRECTORY)) {
  define('DIR_FS_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

  $possible_dir[] = DIR_FS_SQL_CACHE;
  $possible_dir[] = DIR_FS_ROOT . 'cache';

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
  if ($selected_dir == '') $selected_dir = DIR_FS_ROOT . 'cache';

  $sql = "update " . DB_PREFIX . "configuration set configuration_value = '" . $db->prepare_input(trim($selected_dir)) . "' where configuration_key = 'SESSION_WRITE_DIRECTORY'";
  $db->Execute($sql);
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
  exit(1);
}
