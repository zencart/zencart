<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jun 12 Modified in v2.1.0-alpha1 $
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
        $this->loadExtraLanguageFiles(DIR_WS_LANGUAGES, $_SESSION['language'], $this->currentPage . '.php');
        // Pick up additional plugin files which are substring matches
        // Example: lang.create_account_register.php on create_account page.
        $directory = DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $this->templateDir;
        $tfiles = $this->fileSystem->listFilesFromDirectoryAlphaSorted($directory, '~^' . "lang." . $this->currentPage  . '(.+)\.php$~i');

        $directory = DIR_WS_LANGUAGES . $_SESSION['language'];
        $files = $this->fileSystem->listFilesFromDirectoryAlphaSorted($directory, '~^' . "lang." . $this->currentPage . '(.+)\.php$~i');
        $files = array_merge($files, $tfiles);

        foreach ($files as $file) {
           $file = substr($file, 5);
           $this->loadExtraLanguageFiles(DIR_WS_LANGUAGES, $_SESSION['language'], $file);
        }
        foreach ($this->pluginList as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/languages/';
            $this->loadExtraLanguageFiles($pluginDir, $_SESSION['language'], $this->currentPage . '.php');
            $this->loadExtraLanguageFiles($pluginDir, $_SESSION['language'], $this->currentPage . '.php', '/default');
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
            $file = basename($file, '.php') . ".php";
            $this->loadExtraLanguageFiles(DIR_WS_LANGUAGES, $_SESSION['language'], $file);
        }
    }
}
