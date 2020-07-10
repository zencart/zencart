<?php
/**
 * application_bottom.php
 * Common actions carried out at the end of each page invocation.
 *
 * @package initSystem
 * @copyright Copyright 2003-2010 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: application_bottom.php 17088 2010-07-31 05:08:33Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

//  @todo icwtodo Development debug code
// do not remove for now
if (defined('DEV_SHOW_APPLICATION_BOTTOM_DEBUG') && DEV_SHOW_APPLICATION_BOTTOM_DEBUG == true) {
    $langLoaded = $languageLoader->getLanguageFilesLoaded();
    dump($langLoaded);
    $files = get_included_files();
    $langFiles = [];
    $pattern = '~^' . DIR_FS_CATALOG . DIR_WS_LANGUAGES . '~';
    foreach ($files as $file) {
        $shortFile = str_replace(DIR_FS_CATALOG, '', $file);
        if (in_array($shortFile, $langLoaded['legacy']) || in_array($file, $langLoaded['legacy'])) {
            continue;
        }
        if (in_array($shortFile, $langLoaded['arrays']) || in_array($file, $langLoaded['arrays'])) {
            continue;
        }
        if (preg_match($pattern, $file)) {
            error_log('legacy language file loaded ' . $file, 3, DIR_FS_LOGS . 'language_loading.log');
            $langFiles[] = $file;
        }
    }
    dump($langFiles);
//dump($_SESSION);
}
// close session (store variables)
session_write_close();

