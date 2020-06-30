<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class CatalogArraysLanguageLoader extends ArraysLanguageLoader
{
    public function loadInitialLanguageDefines($mainLoader)
    {
        $this->mainLoader = $mainLoader;
    }

    protected function loadLanguageForView()
    {
        $defineList = $this->loadDefinesFromArrayFile(DIR_WS_LANGUAGES, $_SESSION['language'], $this->currentPage);
        $this->addLanguageDefines($defineList);
    }

    protected function loadLanguageExtraDefinitions()
    {
        $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $_SESSION['language'], '/extra_definitions');
    }

    protected function loadBaseLanguageFile()
    {
        $mainFile = DIR_WS_LANGUAGES . 'lang.' . $_SESSION['language'] . '.php';
        $fallbackFile = DIR_WS_LANGUAGES . 'lang.' . $this->fallback . '.php';
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        $this->addLanguageDefines($defineList);
        $defineList = $this->loadDefinesFromArrayFile(DIR_WS_LANGUAGES, $_SESSION['language'], 'gv_name.php');
        $this->addLanguageDefines($defineList);
        $defineList = $this->loadDefinesFromArrayFile(DIR_WS_LANGUAGES, $_SESSION['language'], FILENAME_EMAIL_EXTRAS);
        $this->addLanguageDefines($defineList);
        $defineList = $this->loadDefinesFromTemplateLanguage(
            DIR_WS_LANGUAGES, $this->templateDir, $_SESSION['language'], FILENAME_OTHER_IMAGES_NAMES);
        $this->addLanguageDefines($defineList);
    }

    public function loadDefinesFromTemplateLanguage($rootPath, $templateDir, $lang, $fileName)
    {
        // load order is
        // always load from catalog language directory
        // then load from template language file if it exists
        // then load from plugins @todo
        $defineList = $this->loadDefinesFromArrayFile(DIR_FS_CATALOG . $rootPath, $lang, $fileName);
        $this->addLanguageDefines($defineList);
        if (file_exists(DIR_FS_CATALOG . $rootPath . $_SESSION[$lang] . '/' . $templateDir . '/' . $fileName)) {
            $defineList = $this->loadDefinesFromArrayFile(
                DIR_FS_CATALOG . $rootPath, $lang, $fileName, $templateDir . '/');
            $this->addLanguageDefines($defineList);
        }
    }

}
