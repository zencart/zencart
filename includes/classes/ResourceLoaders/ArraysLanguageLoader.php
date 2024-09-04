<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 28 Modified in v2.1.0-alpha2 $
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
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/' . $context . '/includes/languages/';
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
        $defineListMain = $this->loadDefinesFromArrayFile($rootPath, $language, $fileName, $extraPath);
        $extraPath .= '/' . $this->templateDir;
        $defineListTemplate = $this->loadDefinesFromArrayFile($rootPath, $language, $fileName, $extraPath);
        $defineList = array_merge($defineListMain, $defineListTemplate);
        $this->makeConstants($defineList);
    }

    public function loadModuleLanguageFile(string $language, string $fileName, string $module_type): array
    {
        $defineList = $this->loadModuleDefinesFromArrayFile($language, $fileName, $module_type);

        $defineListPlugins = $this->pluginLoadDefinesFromArrayFile($language, $fileName, 'catalog', '/modules/' . $module_type);
        $defineList = array_merge($defineList, $defineListPlugins);

        $defineListTemplate = $this->loadModuleDefinesFromArrayFile($language, $fileName, $module_type, $this->templateDir . '/');
        $defineList = array_merge($defineList, $defineListTemplate);

        $this->makeConstants($defineList);

        return $defineList;
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
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'];
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
}
