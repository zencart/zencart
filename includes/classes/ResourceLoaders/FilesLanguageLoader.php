<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 06 Modified in v2.1.0-beta1 $
 */
namespace Zencart\LanguageLoader;

use Zencart\FileSystem\FileSystem;

class FilesLanguageLoader extends BaseLanguageLoader
{
    protected $mainLoader;

    public function loadExtraLanguageFiles(string $rootPath, string $language, string $fileName, string $extraPath = ''): void
    {
        if ($this->mainLoader->hasLanguageFile($rootPath, $language, $fileName, $extraPath .  '/' . $this->templateDir)) {
            $this->loadFileDefineFile($rootPath . $language . $extraPath . '/' . $this->templateDir . '/' . $fileName);
        } else {
            $this->loadFileDefineFile($rootPath . $language . $extraPath . '/' . $fileName);
        }
    }

    public function loadModuleLanguageFile(string $fileName, string $module_type): bool
    {
        $rootPath = DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'];
        $extraPath = '/modules/' . $module_type . '/';

        if ($this->loadFileDefineFile($rootPath . $extraPath . $this->templateDir . '/' . $fileName) === true) {
            return true;
        }

        return $this->loadFileDefineFile($rootPath . $extraPath . $fileName);
    }

    protected function loadFileDefineFile(string $defineFile): bool
    {
        $pathInfo = pathinfo(($defineFile));
        if (preg_match('~^lang\.~i', $pathInfo['basename'])) {
            return false;
        }
        if (!is_file($defineFile)) {
            return false;
        }
        if ($this->mainLoader->isFileAlreadyLoaded($defineFile)) {
            return false;
        }
        $this->mainLoader->addLanguageFilesLoaded('legacy', $defineFile);
        include_once $defineFile;
        return true;
    }
}
