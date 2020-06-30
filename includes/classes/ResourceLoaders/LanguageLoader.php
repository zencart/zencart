<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 20 New in v1.5.7 $
 */

namespace Zencart\LanguageLoader;

class LanguageLoader
{

    public function __construct($arraysLoader, $filesLoader)
    {
        $this->languageFilesLoaded = ['arrays' => [], 'legacy' => []];
        $this->arrayLoader = $arraysLoader;
        $this->fileLoader = $filesLoader;
        $this->languageFilesLoaded = ['arrays' => [], 'legacy' => []];
    }

    public function loadInitialLanguageDefines()
    {
        $this->arrayLoader->loadInitialLanguageDefines($this);
        $this->fileLoader->loadInitialLanguageDefines($this);
    }

    public function finalizeLanguageDefines()
    {
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
        $this->fileLoader->loadFileDefineFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . $baseDirectory . '/' . $languageFile);
    }

    public function loadLanguageForView()
    {
        $this->arrayLoader->loadLanguageForView();
        $this->fileLoader->loadLanguageForView();
    }

    public function loadExtraLanguageFiles($rootPath, $language, $fileName, $extraPath = '')
    {
        $defineList = $this->arrayLoader->loadDefinesFromArrayFile($rootPath, $language, $fileName, $extraPath);
        $this->arrayLoader->makeConstants($defineList);
        $this->fileLoader->loadFileDefineFile($rootPath . $language . $extraPath . '/' . $fileName);
        // @todo plugins & late extra definitions
    }

    public function hasLanguageFile($rootPath, $language, $fileName, $extraPath = '')
    {
        if (file_exists($rootPath . $language . $extraPath . '/' . $fileName)) {
            return true;
        }
        if (file_exists($rootPath . $language . $extraPath . '/lang.' . $fileName)) {
            return true;
        }
    }

    public function isFileAlreadyLoaded($defineFile)
    {
        $fileInfo = pathinfo($defineFile);
        $searchFile = 'lang.' . $fileInfo['basename'];
        $searchFile = $fileInfo['dirname'] . '/' . $searchFile;
        if (in_array($searchFile, $this->languageFilesLoaded['arrays'])) {
            return true;
        }
        if (in_array($searchFile, $this->languageFilesLoaded['legacy'])) {
            return true;
        }
    }
}
