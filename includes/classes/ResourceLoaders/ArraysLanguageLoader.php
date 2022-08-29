<?php
/**
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2022 Aug 23 Modified in v1.5.8-alpha2 $
 */

namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class ArraysLanguageLoader extends BaseLanguageLoader
{
    protected $mainLoader;
    
    public function makeConstants($defines)
    {
        if (!is_array($defines)) return; 

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
        }
    }

    public function getLanguageDefines()
    {
        return $this->languageDefines;
    }


    protected function loadArraysFromDirectory($rootPath, $language, $extraPath)
    {
        $path = $rootPath . $language . $extraPath;
        $fileList = $this->fileSystem->listFilesFromDirectory($path, '~^lang\.(.*)\.php$~i');
        $defineList = $this->processArrayFileList($path, $fileList);
        return $defineList;
    }

    protected function pluginLoadArraysFromDirectory($language, $extraPath, $context = 'admin')
    {
        $defineList = [];
        foreach ($this->pluginList as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/' . $context . '/includes/languages/';
            $defines = $this->loadArraysFromDirectory($pluginDir, $language, $extraPath);
            $defineList = array_merge($defineList, $defines);
        }
        return $defineList;
    }

    protected function processArrayFileList($path, $fileList)
    {
        $defineList = [];
        foreach ($fileList as $file) {
            $defines = $this->loadArrayDefineFile($path . '/' . $file);
            $defineList = array_merge($defineList, $defines);
        }
        return $defineList;
    }

    public function loadExtraLanguageFiles($rootPath, $language, $fileName, $extraPath = '')
    {
        $defineListMain = $this->loadDefinesFromArrayFile($rootPath, $language, $fileName, $extraPath);
        $extraPath .= '/' . $this->templateDir;
        $defineListTemplate = $this->loadDefinesFromArrayFile($rootPath, $language, $fileName, $extraPath);
        $defineList = array_merge($defineListMain, $defineListTemplate);
        $this->makeConstants($defineList);
    }

    public function loadDefinesFromArrayFile($rootPath, $language, $fileName, $extraPath = '')
    {
        $arrayFileName = 'lang.' . $fileName;
        $mainFile = $rootPath . $language . $extraPath. '/' . $arrayFileName;
        $fallbackFile = $rootPath . $language . '/' . $arrayFileName;
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        return $defineList;
    }

    public function loadModuleDefinesFromArrayFile($rootPath, $language, $module_type, $fileName, $extraPath = '')
    {
        $arrayFileName = 'lang.' . $fileName;
        $extraBlock = ''; 
        if (!empty($extraPath)) { 
           $extraBlock = $extraPath. '/'; 
        }
        $mainFile = $rootPath . $language . '/modules/' . $module_type . '/' . $extraBlock . $arrayFileName;
        $fallbackFile = $mainFile; // for now no fallback
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        return $defineList;
    }

    public function pluginLoadDefinesFromArrayFile($language, $fileName, $context = 'admin', $extraPath = '')
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

    protected function loadDefinesWithFallback($mainFile, $fallbackFile)
    {
        $defineListFallback = [];
        if ($mainFile !== $fallbackFile) {
            $defineListFallback = $this->loadArrayDefineFile($fallbackFile);
        }
        $defineListMain = $this->loadArrayDefineFile($mainFile);
        $defineList = array_merge($defineListFallback, $defineListMain);
        return $defineList;
    }

    protected function addLanguageDefines($defineList)
    {
        if (!is_array($defineList)) {
            return;
        }
        $newDefineList = array_merge($this->languageDefines, $defineList);
        $this->languageDefines = $newDefineList;
    }

    protected function loadArrayDefineFile($definesFile)
    {
        $definesList = [];
        if ($this->mainLoader->isFileAlreadyLoaded($definesFile) === true || !is_file($definesFile)) {
            return $definesList;
        }
        $this->mainLoader->addLanguageFilesLoaded('arrays', $definesFile);
        // file should return a variable 
        $definesList = require $definesFile;
        return $definesList; 
    }
}
