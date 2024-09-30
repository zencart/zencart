<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 03 Modified in v2.1.0-beta1 $
 */
namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class CatalogFilesLanguageLoader extends FilesLanguageLoader
{
    public function loadInitialLanguageDefines($mainLoader)
    {
        $this->mainLoader = $mainLoader;
        $this->loadLanguageExtraDefinitions();
        $this->loadMainLanguageFiles();
    }

    public function loadLanguageForView(): void
    {
        $directory = DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $this->templateDir;
        if (defined('NO_LANGUAGE_SUBSTRING_MATCH') && in_array($this->currentPage, NO_LANGUAGE_SUBSTRING_MATCH)) {
            $files_to_match = $this->currentPage;
        } else {
            $files_to_match = $this->currentPage . '(.*)';
        }
        $files = $this->fileSystem->listFilesFromDirectoryAlphaSorted($directory, '~^' . $files_to_match  . '\.php$~i');
        foreach ($files as $file) {
            $this->loadFileDefineFile($directory . '/' . $file);
        }

        $directory = DIR_WS_LANGUAGES . $_SESSION['language'];
        $files = $this->fileSystem->listFilesFromDirectoryAlphaSorted($directory, '~^' . $files_to_match  . '\.php$~i');
        foreach ($files as $file) {
            $this->loadFileDefineFile($directory . '/' . $file);
        }
    }

    protected function loadMainLanguageFiles(): void
    {
        $extraFiles = [
            FILENAME_EMAIL_EXTRAS,
            FILENAME_HEADER,
            FILENAME_BUTTON_NAMES,
            FILENAME_ICON_NAMES,
            FILENAME_OTHER_IMAGES_NAMES,
            FILENAME_CREDIT_CARDS,
            FILENAME_WHOS_ONLINE,
            FILENAME_META_TAGS,
        ];

        $this->loadFileDefineFile(DIR_WS_LANGUAGES . $this->templateDir . '/' . $_SESSION['language'] . '.php');
        $this->loadFileDefineFile(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');
        foreach ($extraFiles as $file) {
            $file = basename($file, '.php') . '.php';
            $this->loadExtraLanguageFiles(DIR_WS_LANGUAGES, $_SESSION['language'], $file);
        }
    }

    protected function LoadLanguageExtraDefinitions(): void
    {
        $extraDefsDir = DIR_WS_LANGUAGES . $_SESSION['language'] . '/extra_definitions';
        $extraDefsDirTpl = $extraDefsDir . '/' . $this->templateDir;
        $extraDefs = $this->fileSystem->listFilesFromDirectoryAlphaSorted($extraDefsDir);
        $extraDefsTpl = $this->fileSystem->listFilesFromDirectoryAlphaSorted($extraDefsDirTpl);

        $folderList = [
            $extraDefsDir => $extraDefs,
            $extraDefsDirTpl => $extraDefsTpl,
        ];

        $foundList = [];
        foreach ($folderList as $folder => $entries) {
            foreach ($entries as $entry) {
                $foundList[$entry] = $folder;
            }
        }

        foreach ($foundList as $file => $directory) {
            $this->loadFileDefineFile($directory . '/' . $file);
        }
    }
}
