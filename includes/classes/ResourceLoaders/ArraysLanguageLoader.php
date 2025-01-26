<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 07 Modified in v2.1.0-beta1 $
 */
namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class ArraysLanguageLoader extends BaseLanguageLoader
{
    protected $mainLoader;

    public function makeConstants($defines): bool
    {
        if (!is_array($defines)) {
            return false;
        }

        $constants_made = false;
        foreach ($defines as $defineKey => $defineValue) {
            if (defined($defineKey)) {
                $constants_made = true;
                continue;
            }
            preg_match_all('/%{2}([^%]+)%{2}/', $defineValue, $matches, PREG_PATTERN_ORDER);
            if (count($matches[1])) {
                foreach ($matches[1] as $index => $match) {
                    if (isset($defines[$match])) {
                        $defineValue = str_replace($matches[0][$index], $defines[$match], $defineValue);
                    }
                }
            }

            define($defineKey, $defineValue);
            $constants_made = true;
        }
        return $constants_made;
    }

    public function getLanguageDefines(): array
    {
        return $this->languageDefines;
    }

    protected function loadArraysFromDirectory(string $rootPath, string $language, string $extraPath): array
    {
        $path = $rootPath . $language . $extraPath;
        $fileList = $this->fileSystem->listFilesFromDirectory($path, '~^lang\.(.*)\.php$~i');
        $defineList = $this->processArrayFileList($path, $fileList);
        return $defineList;
    }

    protected function pluginLoadArraysFromDirectory(string $language, string $extraPath, string $context = 'admin'): array
    {
        $defineList = [];
        foreach ($this->pluginList as $plugin) {
            $pluginDir = $this->zcPluginsDir . $plugin['unique_key'] . '/' . $plugin['version'] . '/' . $context . '/includes/languages/';
            $defines = $this->loadArraysFromDirectory($pluginDir, $language, $extraPath);
            $defineList = array_merge($defineList, $defines);
        }
        return $defineList;
    }

    protected function processArrayFileList(string $path, array $fileList): array
    {
        $defineList = [];
        foreach ($fileList as $file) {
            $defines = $this->loadArrayDefineFile($path . '/' . $file);
            $defineList = array_merge($defineList, $defines);
        }
        return $defineList;
    }

    public function loadExtraLanguageFiles(string $rootPath, string $language, string $fileName, string $extraPath = ''): void
    {
        // -----
        // Any $extraPath specified, if not an empty string, must start with a '/' and not end with one.
        //
        $extraPath = trim($extraPath, '/');
        if ($extraPath !== '') {
            $extraPath = '/' . $extraPath;
        }

        $defineListMain = $this->loadDefinesFromArrayFile($rootPath, $language, $fileName, $extraPath);

        $extraPath .= '/' . $this->templateDir;
        $defineListTemplate = $this->loadDefinesFromArrayFile($rootPath, $language, $fileName, $extraPath);

        $defineList = array_merge($defineListMain, $defineListTemplate);
        $this->makeConstants($defineList);
    }

    public function loadModuleLanguageFile(string $fileName, string $module_type): bool
    {
        // -----
        // First, gather the 'base' 'english' language file for the given order_total/payment/shipping module. If
        // the current session's language is **other than** 'english', the file for that language (if present)
        // overwrites any of the 'english' language constants.
        //
        $defineList = $this->loadModuleDefinesFromArrayFile($this->fallback, $fileName, $module_type);
        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = array_merge($defineList, $this->loadModuleDefinesFromArrayFile($_SESSION['language'], $fileName, $module_type));
        }

        // -----
        // Next, gather any 'english' language file from all zc_plugin's 'base' modules' directory; if the
        // current session's language is **other than** 'english', see if any file for that language is
        // provided by any enabled plugin.
        //
        // Any language definitions found in the plugins' files overwrite any previously-loaded ones.
        //
        $defineListPlugins = $this->pluginLoadDefinesFromArrayFile($this->fallback, $fileName, 'catalog', '/modules/' . $module_type);
        $defineList = array_merge($defineList, $defineListPlugins);
        if ($_SESSION['language'] !== $this->fallback) {
            $defineListPlugins = $this->pluginLoadDefinesFromArrayFile($_SESSION['language'], $fileName, 'catalog', '/modules/' . $module_type);
            $defineList = array_merge($defineList, $defineListPlugins);
        }

        // -----
        // Next, gather any 'english' language file from all zc_plugin's 'default' modules' directory; if the
        // current session's language is **other than** 'english', see if any file for that language is
        // provided by any enabled plugin.
        //
        // Any language definitions found in the plugins' files overwrite any previously-loaded ones.
        //
        $defineListPlugins = $this->pluginLoadDefinesFromArrayFile($this->fallback, $fileName, 'catalog', '/modules/' . $module_type . '/default');
        $defineList = array_merge($defineList, $defineListPlugins);
        if ($_SESSION['language'] !== $this->fallback) {
            $defineListPlugins = $this->pluginLoadDefinesFromArrayFile($_SESSION['language'], $fileName, 'catalog', '/modules/' . $module_type . '/default');
            $defineList = array_merge($defineList, $defineListPlugins);
        }

        // -----
        // Finally, gather any template-override definitions **for the current session language**. Any language
        // definitions found here overwrite any previously-loaded ones.
        //
        $defineListTemplate = $this->loadModuleDefinesFromArrayFile($_SESSION['language'], $fileName, $module_type, $this->templateDir . '/');
        $defineList = array_merge($defineList, $defineListTemplate);

        // -----
        // Create the language constants from the definitions found and return an indication of whether/not
        // constants were made (or pre-existing).
        //
        return $this->makeConstants($defineList);
    }

    protected function loadDefinesFromArrayFile(string $rootPath, string $language, string $fileName, string $extraPath = ''): array
    {
        $arrayFileName = 'lang.' . $fileName;
        $mainFile = $rootPath . $language . $extraPath. '/' . $arrayFileName;
        $fallbackFile = $rootPath . $language . '/' . $arrayFileName;
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        return $defineList;
    }

    protected function loadModuleDefinesFromArrayFile(string $language, string $fileName, string $module_type, string $templateDir = ''): array
    {
        $rootPath = DIR_FS_CATALOG . DIR_WS_LANGUAGES;
        $arrayFileName = 'lang.' . $fileName;

        $mainFile = $rootPath . $language . '/modules/' . $module_type . '/' . $templateDir . $arrayFileName;
        $fallbackFile = $rootPath . $this->fallback . '/modules/' . $module_type . '/' . $templateDir . $arrayFileName;
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        return $defineList;
    }

    protected function pluginLoadDefinesFromArrayFile(string $language, string $fileName, string $context = 'admin', string $extraPath = ''): array
    {
        $defineList = [];
        foreach ($this->pluginList as $plugin) {
            $pluginDir = $this->zcPluginsDir . $plugin['unique_key'] . '/' . $plugin['version'];
            $pluginDir .=  '/' . $context . '/includes/languages/';
            $pluginDefineList = $this->loadDefinesFromArrayFile($pluginDir, $language, $fileName, $extraPath);
            $defineList = array_merge($defineList, $pluginDefineList);
        }
        return $defineList;
    }

    protected function loadDefinesWithFallback(string $mainFile, string $fallbackFile): array
    {
        $defineListFallback = [];
        if ($mainFile !== $fallbackFile) {
            $defineListFallback = $this->loadArrayDefineFile($fallbackFile);
        }
        $defineListMain = $this->loadArrayDefineFile($mainFile);
        $defineList = array_merge($defineListFallback, $defineListMain);
        return $defineList;
    }

    protected function addLanguageDefines($defineList): void
    {
        if (!is_array($defineList)) {
            return;
        }
        $newDefineList = array_merge($this->languageDefines, $defineList);
        $this->languageDefines = $newDefineList;
    }

    protected function loadArrayDefineFile(string $definesFile): array
    {
        if ($this->mainLoader->isFileAlreadyLoaded($definesFile) === true || !is_file($definesFile)) {
            return [];
        }

        $this->mainLoader->addLanguageFilesLoaded('arrays', $definesFile);
        // file should return a variable 
        $definesList = require $definesFile;
        return $definesList; 
    }

    // -----
    // Loads the specified file from the specified directory, with language fall-back.
    //
    // First the file from the 'fallback' (i.e. 'english') language subdirectory is loaded and
    // its definitions added to the to-be-generated constants' list.
    //
    // Next, if the current session language is different than the 'fallback', load the file from
    // the session-specified directory and add its definitions to the to-be-generated constants' list,
    // overwriting any previous definitions.
    //
    protected function loadDefinesFromDirFileWithFallback(string $directory, string $filename): void
    {
        $defineList = $this->loadDefinesFromArrayFile($directory, $this->fallback, $filename);
        $this->addLanguageDefines($defineList);

        if ($_SESSION['language'] !== $this->fallback) {
            $defineList = $this->loadDefinesFromArrayFile($directory, $_SESSION['language'], $filename);
            $this->addLanguageDefines($defineList);
        }
    }

    // -----
    // Load (and make associated constants) for a given **storefront** language file.  Used
    // primarily by admin plugins that have common admin/storefront constant definitions.
    //
    // Note: The $extraDir, if non-blank, must start with a '/' and not end with one!
    //
    public function makeCatalogArrayConstants(string $fileName, string $extraDir = ''): void
    {
        if (str_starts_with($fileName, 'lang.') === false) {
            $fileName = 'lang.' . $fileName;
        }

        $rootDir = DIR_FS_CATALOG . DIR_WS_LANGUAGES;

        $mainFile = $rootDir . $_SESSION['language'] . $extraDir . '/' . $fileName;
        $fallbackFile = $rootDir . $this->fallback . $extraDir . '/' . $fileName;

        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);

        foreach ($this->pluginList as $plugin) {
            $pluginDir = $this->zcPluginsDir . $plugin['unique_key'] . '/' . $plugin['version'];
            $pluginDir .=  '/catalog/includes/languages/';

            $mainFile = $pluginDir . $_SESSION['language'] . $extraDir . '/' . $fileName;
            $fallbackFile = $pluginDir . $this->fallback . $extraDir . '/' . $fileName;

            $pluginDefineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
            $defineList = array_merge($defineList, $pluginDefineList);
        }

        $templateFile = $rootDir . $_SESSION['language'] . $extraDir . '/' . $this->templateDir . '/' . $fileName;
        $defineList = array_merge($defineList, $this->loadArrayDefineFile($templateFile));

        $this->makeConstants($defineList);
    }
}
