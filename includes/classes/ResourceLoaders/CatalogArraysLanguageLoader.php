<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 30 Modified in v2.1.0-alpha2 $
 */
namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class CatalogArraysLanguageLoader extends ArraysLanguageLoader
{
    public function loadInitialLanguageDefines($mainLoader): void
    {
        $this->mainLoader = $mainLoader;
        $this->loadMainLanguageFiles();
        $this->loadLanguageExtraDefinitions();
    }

    public function loadLanguageForView(): void
    {
        $languages = [
            $_SESSION['language'],
        ];
        if ($_SESSION['language'] !== $this->fallback) {
            $languages[] = $this->fallback;
        }

        $this->loadCurrentPageBaseFile($languages);

        foreach ($languages as $next_lang) {
            $baseDir = DIR_WS_LANGUAGES . $next_lang;

            $this->loadCurrentPageExtraFilesFromDir($baseDir . '/' . $this->templateDir);

            foreach ($this->pluginList as $plugin) {
                $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/languages/' . $next_lang;

                $this->loadCurrentPageExtraFilesFromDir($pluginDir . '/default');
                $this->loadCurrentPageExtraFilesFromDir($pluginDir);
            }

            $this->loadCurrentPageExtraFilesFromDir($baseDir);
        }
    }

    protected function loadCurrentPageBaseFile(array $languages): void
    {
        $filename = 'lang.' . $this->currentPage . '.php';
        foreach ($languages as $next_lang) {
            $baseDir = DIR_WS_LANGUAGES . $next_lang . '/';

            $defines = $this->loadArrayDefineFile($baseDir . $this->templateDir . '/' . $filename);
            $this->makeConstants($defines);

            foreach ($this->pluginList as $plugin) {
                $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/languages/' . $next_lang;

                $defines = $this->loadArrayDefineFile($pluginDir . '/default/' . $filename);
                $this->makeConstants($defines);

                $defines = $this->loadArrayDefineFile($pluginDir . '/' . $filename);
                $this->makeConstants($defines);
            }

            $defines = $this->loadArrayDefineFile($baseDir . $filename);
            $this->makeConstants($defines);
        }
    }

    protected function loadCurrentPageExtraFilesFromDir(string $directory): void
    {
        $files_regex = '~^' . 'lang.' . $this->currentPage  . '(.+)\.php$~i';

        $files = $this->fileSystem->listFilesFromDirectoryAlphaSorted($directory, $files_regex);
        foreach ($files as $file) {
            $defines = $this->loadArrayDefineFile($directory . '/' . $file);
            $this->makeConstants($defines);
        }
    }

    protected function loadLanguageExtraDefinitions(): void
    {
        $defineList = $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $_SESSION['language'], '/extra_definitions');
        $this->addLanguageDefines($defineList);

        $defineList = $this->pluginLoadArraysFromDirectory($_SESSION['language'], '/extra_definitions', 'catalog');
        $this->addLanguageDefines($defineList);

        $defineList = $this->pluginLoadArraysFromDirectory($_SESSION['language'], '/extra_definitions/default', 'catalog');
        $this->addLanguageDefines($defineList);
        
        $defineList = $this->loadArraysFromDirectory(DIR_WS_LANGUAGES, $_SESSION['language'], '/extra_definitions/' . $this->templateDir);
        $this->addLanguageDefines($defineList);
    }

    protected function loadMainLanguageFiles(): void
    {
        $extraFiles = [FILENAME_EMAIL_EXTRAS, FILENAME_HEADER, FILENAME_BUTTON_NAMES, FILENAME_ICON_NAMES, FILENAME_OTHER_IMAGES_NAMES, FILENAME_CREDIT_CARDS, FILENAME_WHOS_ONLINE, FILENAME_META_TAGS];
        $mainFile = DIR_WS_LANGUAGES . 'lang.' . $_SESSION['language'] . '.php';
        $fallbackFile = DIR_WS_LANGUAGES . 'lang.' . $this->fallback . '.php';
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        $this->addLanguageDefines($defineList);

        $mainFile = DIR_WS_LANGUAGES . $this->templateDir . '/lang.' . $_SESSION['language'] . '.php';
        $fallbackFile = DIR_WS_LANGUAGES . 'lang.' . $_SESSION['language'] . '.php';
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        $this->addLanguageDefines($defineList);

        foreach ($extraFiles as $file) {
            $file = basename($file, '.php') . '.php';
            $this->loadExtraLanguageFiles(DIR_WS_LANGUAGES, $_SESSION['language'], $file);
        }
    }
}
