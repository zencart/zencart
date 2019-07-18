<?php
/**
 * Load in any specialized developer and/or unit-testing scripts
 *
 * @package initSystem
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Apr 11 Modified in v1.5.6b $
 */
// must be called appropriately
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// set directories to check for extra scripts
$fsDir = DIR_FS_CATALOG . 'not_for_release/testFramework/extra_scripts/';
$wsDir = 'not_for_release/testFramework/extra_scripts/';

// Check for new functions in extra_scripts directory
$directory_array = array();

if ($dir = @dir($fsDir)) {
  while ($file = $dir->read()) {
    if (!is_dir($fsDir . $file)) {
      if (preg_match('~^[^\._].*\.php$~i', $file) > 0) {
        $directory_array[] = $file;
      }
    }
  }
  if (sizeof($directory_array)) {
    sort($directory_array);
  }
  $dir->close();
}

$file_cnt=0;
for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
  $file_cnt++;
  $file = $directory_array[$i];

  if (file_exists($wsDir . $file)) {
    include($wsDir . $file);
  }
}
