<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 03 Modified in v2.1.0-beta1 $
 */

namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class AdminFilesLanguageLoader extends FilesLanguageLoader
{
    public function loadInitialLanguageDefines($mainLoader)
    {
        $this->mainLoader = $mainLoader;
        $this->loadLanguageExtraDefinitions();
        $this->loadLanguageForView();
        $this->loadBaseLanguageFile();
    }

    protected function loadLanguageForView()
    {
        $this->loadFileDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $this->currentPage);
    }

    protected function loadLanguageExtraDefinitions()
    {
        $dirPath = DIR_WS_LANGUAGES . $_SESSION['language'] . '/extra_definitions';
        $fileList = $this->fileSystem->listFilesFromDirectoryAlphaSorted($dirPath, '~^(?!lang\.).*\.php$~i');
        foreach ($fileList as $file) {
            $this->loadFileDefineFile($dirPath . '/' . $file);
        }
    }

    protected function loadBaseLanguageFile()
    {
        $this->loadFileDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');
        $this->loadFileDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_EMAIL_EXTRAS);
        $this->loadFileDefineFile(
            zen_get_file_directory(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/', FILENAME_OTHER_IMAGES_NAMES)
        );
    }
}
