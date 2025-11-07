<?php
/**
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 29 Modified in v2.2.0 $
 */
namespace Zencart\LanguageLoader;

/**
 * @since ZC v1.5.7
 */
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

    /**
     * @since ZC v1.5.8
     */
    public function loadInitialLanguageDefines(): void
    {
        $this->arrayLoader->loadInitialLanguageDefines($this);
        $this->fileLoader->loadInitialLanguageDefines($this);
    }

    /**
     * @since ZC v1.5.8
     */
    public function finalizeLanguageDefines(): void
    {
        $this->arrayLoader->makeConstants($this->arrayLoader->getLanguageDefines());
    }

    /**
     * @since ZC v1.5.8
     */
    public function getLanguageFilesLoaded(): array
    {
        return $this->languageFilesLoaded;
    }

    /**
     * @since ZC v1.5.8
     */
    public function addLanguageFilesLoaded(string $type, string $defineFile): void
    {
        $this->languageFilesLoaded[$type][] = $defineFile;
    }

    /**
     * @since ZC v1.5.8
     */
    public function loadDefinesFromFile(string $baseDirectory, string $language, string $languageFile): bool
    {
        $this->arrayLoader->loadDefinesFromArrayFile($baseDirectory, $language, $languageFile);
        $this->fileLoader->loadFileDefineFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . $baseDirectory . '/' . $languageFile);
        return true;
    }

    /**
     * @since ZC v1.5.8
     */
    public function loadModuleDefinesFromFile(string $baseDirectory, string $language, string $module_type, string $languageFile): bool
    {
        $defs = $this->arrayLoader->loadModuleDefinesFromArrayFile(DIR_FS_CATALOG . 'includes/languages/', $language, $module_type, $languageFile);

        $this->arrayLoader->makeConstants($defs);
        if ($module_type !== '') {
            $module_type .= '/';
        }
        $this->fileLoader->loadFileDefineFile(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . $baseDirectory . $module_type . $languageFile);
        return true;
    }

    /**
     * @since ZC v2.1.0
     */
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
     * @since ZC v1.5.8
     */
    public function setCurrentPage(string $currentPage): void
    {
        $this->arrayLoader->currentPage = $currentPage;
        $this->fileLoader->currentPage = $currentPage;
    }

    /**
     * @since ZC v1.5.7
     */
    public function loadLanguageForView(): void
    {
        $this->arrayLoader->loadLanguageForView();
        $this->fileLoader->loadLanguageForView();
    }

    /**
     * @since ZC v1.5.8
     */
    public function loadExtraLanguageFiles(string $rootPath, string $language, string $fileName, string $extraPath = ''): void
    {
        $this->arrayLoader->loadExtraLanguageFiles($rootPath, $language, $fileName, $extraPath);
        $this->fileLoader->loadExtraLanguageFiles($rootPath, $language, $fileName, $extraPath);
    }

    /**
     * @since ZC v1.5.8
     */
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

    /**
     * @since ZC v2.1.0
     */
    public function loadModuleLanguageFile(string $fileName, string $moduleType): bool
    {
        $this->arrayLoader->loadModuleLanguageFile($fileName, $moduleType);
        $this->fileLoader->loadModuleLanguageFile($fileName, $moduleType);

        $language_files_loaded = array_merge($this->languageFilesLoaded['arrays'], $this->languageFilesLoaded['legacy']);

        if ($moduleType !== '') {
            $moduleType .= '/';
        }
        $match_string = '~modules/' . $moduleType . '(lang\.)?' . $fileName . '$~';
        $match_string_template = '~modules/' . $moduleType . $this->arrayLoader->getTemplateDir() . '/(lang\.)?' . $fileName . '$~';
        foreach ($language_files_loaded as $next_file) {
            if (preg_match($match_string, $next_file) || preg_match($match_string_template, $next_file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @since ZC v1.5.8
     */
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
