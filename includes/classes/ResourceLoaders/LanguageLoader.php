<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 07 Modified in v2.1.0-beta1 $
 */
namespace Zencart\LanguageLoader;

class LanguageLoader
{
    private array $languageFilesLoaded;
    private $arrayLoader;
    private $fileLoader;

    public function __construct($arraysLoader, $filesLoader)
    {
        $this->languageFilesLoaded = ['arrays' => [], 'legacy' => []];
        $this->arrayLoader = $arraysLoader;
        $this->fileLoader = $filesLoader;
        $this->languageFilesLoaded = ['arrays' => [], 'legacy' => []];
    }

    public function loadInitialLanguageDefines(): void
    {
        $this->arrayLoader->loadInitialLanguageDefines($this);
        $this->fileLoader->loadInitialLanguageDefines($this);
    }

    public function finalizeLanguageDefines(): void
    {
        $this->arrayLoader->makeConstants($this->arrayLoader->getLanguageDefines());
    }

    public function getLanguageFilesLoaded(): array
    {
        return $this->languageFilesLoaded;
    }

    public function addLanguageFilesLoaded(string $type, string $defineFile): void
    {
        $this->languageFilesLoaded[$type][] = $defineFile;
    }

    public function loadDefinesFromFile(string $baseDirectory, string $language, string $languageFile): bool
    {
        $this->arrayLoader->loadDefinesFromArrayFile($baseDirectory, $language, $languageFile);
        $this->fileLoader->loadFileDefineFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . $baseDirectory . '/' . $languageFile);
        return true; 
    }

    public function loadModuleDefinesFromFile(string $baseDirectory, string $language, string $module_type, string $languageFile): bool
    {
        $defs = $this->arrayLoader->loadModuleDefinesFromArrayFile(DIR_FS_CATALOG . 'includes/languages/', $language, $module_type, $languageFile);

        $this->arrayLoader->makeConstants($defs);
        $this->fileLoader->loadFileDefineFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . $baseDirectory . $module_type . '/' . $languageFile);
        return true; 
    }

    public function makeCatalogArrayConstants(string $fileName, string $extraDir = ''): void
    {
        $this->arrayLoader->makeCatalogArrayConstants($fileName, $extraDir);
    }

    /**
     * Used on the catalog-side to set the current page for the language-load, since it's not necessarily
     * available during the autoload process (e.g. for AJAX handlers).
     *
     * @param string $currentPage
     * @return void
     */
    public function setCurrentPage(string $currentPage): void
    {
        $this->arrayLoader->currentPage = $currentPage;
        $this->fileLoader->currentPage = $currentPage;
    }

    public function loadLanguageForView(): void
    {
        $this->arrayLoader->loadLanguageForView();
        $this->fileLoader->loadLanguageForView();
    }

    public function loadExtraLanguageFiles(string $rootPath, string $language, string $fileName, string $extraPath = ''): void
    {
        $this->arrayLoader->loadExtraLanguageFiles($rootPath, $language, $fileName, $extraPath);
        $this->fileLoader->loadExtraLanguageFiles($rootPath, $language, $fileName, $extraPath);
    }

    public function hasLanguageFile(string $rootPath, string $language, string $fileName, string $extraPath = ''): bool
    {
        if (is_file($rootPath . $language . $extraPath . '/' . $fileName)) {
            return true;
        }
        if (is_file($rootPath . $language . $extraPath . '/lang.' . $fileName)) {
            return true;
        }
        return false;
    }

    public function loadModuleLanguageFile(string $fileName, string $moduleType): bool
    {
        $array_constants_created = $this->arrayLoader->loadModuleLanguageFile($fileName, $moduleType);
        $legacy_file_loaded = $this->fileLoader->loadModuleLanguageFile($fileName, $moduleType);

        return ($legacy_file_loaded === true || $array_constants_created === true);
    }

    public function isFileAlreadyLoaded(string $defineFile): bool
    {
        $fileInfo = pathinfo($defineFile);
        $searchFile = $fileInfo['basename'];
        if (strpos($searchFile, 'lang.') !== 0) {
            $searchFile = 'lang.' . $searchFile;
        }
        $searchFile = $fileInfo['dirname'] . '/' . $searchFile;
        if (in_array($searchFile, $this->languageFilesLoaded['arrays'])) {
            return true;
        }
        if (in_array($defineFile, $this->languageFilesLoaded['legacy'])) {
            return true;
        }
        return false;
    }
}
