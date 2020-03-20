<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class AdminFilesLanguageLoader extends BaseLanguageLoader
{
    public function loadLanguageDefines()
    {
        $this->loadLanguageForView();
        $this->loadLanguageExtraDefinitions();
        $this->loadBaseLanguageFile();
    }

    protected function loadLanguageForView()
    {
        $this->loadFileDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $this->currentPage);
    }

    protected function loadLanguageExtraDefinitions()
    {
        $dirPath = DIR_WS_LANGUAGES . $_SESSION['language'] . '/extra_definitions';
        $fileList = $this->fileSystem->listFilesFromDirectory($dirPath);
        foreach ($fileList as $file) {
            $this->loadFileDefineFile($dirPath . '/' . $file);
        }
    }

    protected function loadBaseLanguageFile()
    {
        $this->loadFileDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');
        $this->loadFileDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . "/" . FILENAME_EMAIL_EXTRAS);
        $this->loadFileDefineFile(
            zen_get_file_directory(
                DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/', FILENAME_OTHER_IMAGES_NAMES));
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