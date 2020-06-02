<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class CatalogFilesLanguageLoader extends BaseLanguageLoader
{
    public function loadLanguageDefines($mainLoader)
    {
        $this->mainLoader = $mainLoader;
        $this->loadMainLanguageFile();
        $this->loadLanguageExtraDefinitions();
    }

    protected function loadMainLanguageFile()
    {
        $this->loadFileDefineFile(zen_get_file_directory(DIR_WS_LANGUAGES, $_SESSION['language'] . '.php'));
        $this->loadFileDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');
    }

    protected function LoadLanguageExtraDefinitions()
    {
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
            $this->loadFileDefineFile($include_file);
        }

    }

    public function loadFileDefineFile($defineFile)
    {
        if (!is_file($defineFile)) {
            return;
        }
        if ($this->mainLoader->isFileAlreadyLoaded($defineFile)) {
            return;
        }
        $this->mainLoader->addLanguageFilesLoaded('legacy', $defineFile);
        include_once($defineFile);
    }
}