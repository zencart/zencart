<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

namespace Zencart\LanguageLoader;

class AdminLanguageLoader
{

    public function __construct($pluginList, $currentPage, $templateDir, $fallback = 'english')
    {
//        $this->pluginList = $pluginList;
        $this->languageFilesLoaded = ['arrays' => [], 'legacy' => []];
        $this->arrayLoader = new AdminArraysLanguageLoader(
            $this, $pluginList, $currentPage, $templateDir, $fallback = 'english');
        $this->fileLoader = new AdminFilesLanguageLoader(
            $this, $pluginList, $currentPage, $templateDir, $fallback = 'english');
        $this->languageFilesLoaded = ['arrays' => [], 'legacy' => []];
    }

    public function loadLanguageDefines()
    {
        $this->arrayLoader->loadLanguageDefines();
        $this->fileLoader->loadLanguageDefines();
        $this->arrayLoader->makeConstants($this->arrayLoader->getLanguageDefines());
    }

    public function getLanguageFilesLoaded()
    {
        return $this->languageFilesLoaded;
    }

    public function addLanguageFilesLoaded($type, $defineFile)
    {
        $this->languageFilesLoaded[$type][] = $defineFile;
    }

    public function loadDefinesFromFile($baseDirectory, $language, $languageFile)
    {
        $this->arrayLoader->loadDefinesFromArrayFile($baseDirectory, $language, $languageFile);
        $this->fileLoader->loadFileDefineFile($baseDirectory . $language . '/' . $languageFile);
    }

    public function isFileAlreadyLoaded($defineFile)
    {
        if (in_array($defineFile, $this->languageFilesLoaded['arrays'])) {
            return true;
        }
        if (in_array($defineFile, $this->languageFilesLoaded['legacy'])) {
            return true;
        }
    }


}