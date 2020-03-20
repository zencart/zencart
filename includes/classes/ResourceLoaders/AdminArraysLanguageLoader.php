<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class AdminArraysLanguageLoader extends BaseLanguageLoader
{
    public function loadLanguageDefines()
    {
        $this->loadBaseLanguageFile();
        $this->loadLanguageForView();
        $this->loadLanguageExtraDefinitions();
    }

    public function makeConstants($defines)
    {
        foreach ($defines as $defineKey => $defineValue) {
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

    protected function loadArraysFromDirectory($rootPath, $lng, $extraPath)
    {
        $path = $rootPath . $lng . $extraPath;
        $fileList = $this->fileSystem->listFilesFromDirectory($path, '~^[lang\.].*\.php$~i');
        $this->processArrayFileList($path, $fileList);
    }

    protected function processArrayFileList($path, $fileList)
    {
        foreach ($fileList as $file) {
            $defines = $this->loadArrayDefineFile($path . '/' . $file);
            $this->addLanguageDefines($defines);
        }
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

    public function loadDefinesFromArrayFile($baseDirectory, $language, $languageFile, $languageSubDir = '')
    {
        $languageArrayFile = 'lang.' . $languageFile;
        $mainFile = $baseDirectory . $language . '/' . $languageSubDir . $languageArrayFile;
        $fallbackFile = $baseDirectory . $this->fallback . '/' . $languageSubDir . $languageArrayFile;
        $defineList = $this->loadDefinesWithFallback($mainFile, $fallbackFile);
        // @todo plugins
        return $defineList;
    }

    protected function loadDefinesWithFallback($mainFile, $fallbackFile)
    {
        $defineListFallback = [];
        if ($mainFile != $fallbackFile) {
            $defineListFallback = $this->loadArrayDefineFile($fallbackFile);
        }
        $defineListMain = $this->loadArrayDefineFile($mainFile);
        $defineList = array_merge($defineListFallback, $defineListMain);
        return $defineList;
    }

    protected function addLanguageDefines($defineList)
    {
        if (!is_array($defineList)) return;
        $newDefineList = array_merge($this->languageDefines, $defineList);
        $this->languageDefines = $newDefineList;
    }

    protected function loadArrayDefineFile($defineFile)
    {
        $defineList = [];
        if (is_file($defineFile)) {
            $this->mainLoader->addLanguageFilesLoaded('arrays', str_replace('lang.', '', $defineFile));
            $defineList = include_once($defineFile);
        }
        return $defineList;
    }
}