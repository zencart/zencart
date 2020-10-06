<?php
/**
 * Load extra user defined language files
 * see  {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jul 15 Modified in v1.5.7 $
 */
// must be called appropriately
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// set directories to check for language files
$lang_extra_defs_dir = DIR_WS_LANGUAGES . $_SESSION['language'] . '/extra_definitions/';
$lang_extra_defs_dir_template = DIR_WS_LANGUAGES . $_SESSION['language'] . '/extra_definitions/' . $template_dir . '/';

$file_array = array(); 
$folderlist = array($lang_extra_defs_dir_template, $lang_extra_defs_dir);

foreach ($folderlist as $folder) { 
  $this_folder = DIR_FS_CATALOG . $folder; 
  if ($dir = @dir($this_folder)) {
    while (false !== ($file = $dir->read())) {
      if (!is_dir($this_folder. $file)) {
        if (!array_key_exists($file, $file_array)) {
          if (preg_match('~^[^\._].*\.php$~i', $file) > 0) {
             $file_array[$file] = $folder . $file;
          }
        }
      }
    }
    $dir->close();
  }
}

if (sizeof($file_array)) {
    ksort($file_array);
}


foreach ($file_array as $file => $include_file) { 
  include($include_file);
}
