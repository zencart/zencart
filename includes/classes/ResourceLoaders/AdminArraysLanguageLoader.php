<?php
/**
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jun 02 New in v1.5.8-alpha $
 */
namespace Zencart\LanguageLoader;

class AdminArraysLanguageLoader extends ArraysLanguageLoader
{
    public function loadInitialLanguageDefines($mainLoader): void
    {
        $this->mainLoader = $mainLoader;
        $this->loadBaseLanguageFiles();
        $this->loadLanguageForView();
        $this->loadLanguageExtraDefinitions();
    }

    protected function loadLanguageForView(): void
    {
        $defineList = $this->loadDefinesFromArrayFile(DIR_WS_LANGUAGES, $this->fallback, $this->currentPage);
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->loadDefinesFromArrayFile(DIR_WS_LANGUAGES, $_SESSION['language'], $this->currentPage);
            $this->addLanguageDefines($defineList);
        }

        $defineList = $this->pluginLoadDefinesFromArrayFile($this->fallback, $this->currentPage, 'admin', '');
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->pluginLoadDefinesFromArrayFile($_SESSION['language'], $this->currentPage, 'admin', '');
            $this->addLanguageDefines($defineList);
        }
    }

    protected function loadLanguageExtraDefinitions(): void
    {
        $defineList = $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $this->fallback, '/extra_definitions');
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $_SESSION['language'], '/extra_definitions');
            $this->addLanguageDefines($defineList);
        }

        $defineList = $this->pluginLoadArraysFromDirectory($this->fallback, '/extra_definitions');
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->pluginLoadArraysFromDirectory($_SESSION['language'], '/extra_definitions');
            $this->addLanguageDefines($defineList);
        }
    }

    protected function loadBaseLanguageFiles()
    {
        $mainFile = DIR_WS_LANGUAGES . 'lang.' . $_SESSION['language'] . '.php';
        $fallbackFile = DIR_WS_LANGUAGES . 'lang.' . $this->fallback . '.php';
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        $this->addLanguageDefines($defineList);

        $defineList = $this->loadDefinesFromArrayFile(DIR_WS_LANGUAGES, $this->fallback, 'gv_name.php');
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->loadDefinesFromArrayFile(DIR_WS_LANGUAGES, $_SESSION['language'], 'gv_name.php');
            $this->addLanguageDefines($defineList);
        }

        $defineList = $this->loadDefinesFromArrayFile(DIR_WS_LANGUAGES, $this->fallback, FILENAME_EMAIL_EXTRAS);
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->loadDefinesFromArrayFile(DIR_WS_LANGUAGES, $_SESSION['language'], FILENAME_EMAIL_EXTRAS);
            $this->addLanguageDefines($defineList);
        }

        $defineList = $this->loadDefinesFromArrayFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES, $this->fallback, FILENAME_OTHER_IMAGES_NAMES);
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->loadDefinesFromArrayFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES, $_SESSION['language'], FILENAME_OTHER_IMAGES_NAMES);
            $this->addLanguageDefines($defineList);
        }

        if ($this->fileSystem->hasTemplateLanguageOverride($this->templateDir, DIR_FS_CATALOG . DIR_WS_LANGUAGES, $this->fallback, FILENAME_OTHER_IMAGES_NAMES)) {
            $defineList = $this->loadDefinesFromArrayFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES, $this->fallback, FILENAME_OTHER_IMAGES_NAMES, $this->templateDir . '/');
            $this->addLanguageDefines($defineList);
        }

        if ($_SESSION['language'] !== $this->fallback) {
            if ($this->fileSystem->hasTemplateLanguageOverride($this->templateDir, DIR_FS_CATALOG . DIR_WS_LANGUAGES, $_SESSION['language'], FILENAME_OTHER_IMAGES_NAMES)) {
                $defineList = $this->loadDefinesFromArrayFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES, $_SESSION['language'], FILENAME_OTHER_IMAGES_NAMES, $this->templateDir . '/');
                $this->addLanguageDefines($defineList);
            }
        }
    }
}
