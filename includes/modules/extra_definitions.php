<?php
/**
 * Load extra user defined language files
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Aug 23 Modified in v2.0.0-alpha1 $
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
    foreach (zen_get_files_in_directory($this_folder) as $file) {
        if (!array_key_exists(basename($file), $file_array)) {
            $file_array[basename($file)] = $file;
        }
    }
}

if (!empty($file_array)) {
    ksort($file_array);
}

foreach ($file_array as $file => $include_file) {
  include($include_file);
}
