<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jun 14 Modified in v2.1.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
//  @todo icwtodo Development debug code
// do not remove for now
if (defined('DEV_SHOW_APPLICATION_BOTTOM_DEBUG') && DEV_SHOW_APPLICATION_BOTTOM_DEBUG == true) {
    $langLoaded = $languageLoader->getLanguageFilesLoaded();
    echo '$langLoaded =<br>' . nl2br(var_export($langLoaded, true), false);

    $files = get_included_files();
    $langFiles = [];
    $pattern = DIR_WS_LANGUAGES;
    foreach ($files as $file) {
        $shortFile = str_replace(["\\", DIR_FS_CATALOG], ['/', ''], $file);
        if (in_array($shortFile, $langLoaded['legacy']) || in_array($file, $langLoaded['legacy'])) {
            continue;
        }
        if (in_array($shortFile, $langLoaded['arrays']) || in_array($file, $langLoaded['arrays'])) {
            continue;
        }
        if (strpos($shortFile, $pattern) === 0) {
            $langFiles[] = $file;
        }
    }
    echo '<br>Other $langFiles:<br>' . nl2br(var_export($langFiles, true), false);
}
// close session (store variables)
session_write_close();
