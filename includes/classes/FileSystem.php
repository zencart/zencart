<?php
/**
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
namespace Zencart\FileSystem;

/**
 * @since ZC v1.5.7
 */
class FileSystem
{
    /**
     * @since ZC v1.5.7
     */
    public function loadFilesFromDirectory(string $rootDir, string $fileRegx = '~^[^\._].*\.php$~i'): void
    {
        if (!is_dir($rootDir)) {
            return;
        }
        if (!$dir = @dir($rootDir)) {
            return;
        }
        while ($file = $dir->read()) {
            if (preg_match($fileRegx, $file) > 0) {
                require_once($rootDir . '/' . $file);
            }
        }
        $dir->close();
    }

    /**
     * @since ZC v1.5.7
     */
    public function listFilesFromDirectory(string $rootDir, string $fileRegx = '~^[^\._].*\.php$~i', bool $keepDir = false): array
    {
        if (!is_dir($rootDir)) {
            return [];
        }
        if (!$dir = @dir($rootDir)) {
            return [];
        }
        $fileList = [];
        while ($file = $dir->read()) {
            if (preg_match($fileRegx, $file) > 0) {
                $fileName = $rootDir . '/' . $file;
                if ($keepDir === false) {
                    $fileName = basename($fileName);
                }
                $fileList[] = $fileName;
            }
        }
        $dir->close();
        return $fileList;
    }

    /**
     * @since ZC v1.5.8
     */
    public function listFilesFromDirectoryAlphaSorted(string $rootDir, string $fileRegx = '~^[^\._].*\.php$~i', bool $keepDir = false): array
    {
        $fileList = $this->listFilesFromDirectory($rootDir, $fileRegx, $keepDir);
        sort($fileList);
        return $fileList;
    }

    /**
     * @since ZC v1.5.7
     */
    public function loadFilesFromPluginsDirectory(array $installedPlugins, string $rootDir, string $fileRegx = '~^[^\._].*\.php$~i'): void
    {
        foreach ($installedPlugins as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'];
            $pluginDir = $pluginDir . '/' . $rootDir;
            $this->loadFilesFromDirectory($pluginDir, $fileRegx);
        }
    }

    /**
     * @since ZC v1.5.7
     */
    public function findPluginAdminPage(array $installedPlugins, string $page)
    {
        $found = null;
        foreach ($installedPlugins as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'];
            $adminFile = $pluginDir . '/admin/' . $page . '.php';
            $adminFile = $this->realpath($adminFile);
            $realPath = $this->realpath($adminFile);
            if ($realPath === false || strpos($realPath, $pluginDir) !== 0) {
                continue; // Skip this file if it's not under the intended directory
            }
            if (!file_exists($realPath)) {
                continue;
            }
            $found = $realPath;
        }
        return $found;
    }

    /**
     * @since ZC v1.5.7
     */
    public function isAdminDir(string $filePath): bool
    {
        if (!defined('DIR_FS_ADMIN')) {
            return false;
        }
        $test = str_replace(DIR_FS_ADMIN, '', $filePath);
        if ($test != $filePath) {
            return false;
        }
        return true;
    }

    /**
     * @since ZC v1.5.7
     */
    public function isCatalogDir(string $filePath): bool
    {
        if ($this->isAdminDir($filePath)) {
            return false;
        }
        if (!defined('DIR_FS_CATALOG')) {
            return false;
        }
        $test = str_replace(DIR_FS_CATALOG, '', $filePath);
        if ($test !== $filePath) {
            return false;
        }
        return true;

    }

    /**
     * @since ZC v1.5.7
     */
    public function getRelativeDir(string $filePath): string
    {
        if ($this->isAdminDir($filePath)) {
            return str_replace(DIR_FS_ADMIN, '', $filePath);
        }
        if ($this->isCatalogDir($filePath)) {
            return str_replace(DIR_FS_CATALOG, '', $filePath);
        }
        return $filePath;
    }

    /**
     * @since ZC v1.5.7
     */
    public function getDirectorySize(string $path, $decimals = 2, bool $addSuffix = true): string
    {
        $bytes = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $bytes += $file->getSize();
        }
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        $suffix = 'bloody huge!';
        if (isset($size[$factor])) {
            $suffix = $size[$factor];
        }
        return sprintf("%.{$decimals}f ", $bytes / pow(1024, $factor)) . $suffix;
    }

    /**
     * @since ZC v1.5.7
     */
    public function fileExistsInDirectory(string $fileDir, string $filePattern): bool
    {
        $found = false;
        $filePattern = '/' . str_replace("/", "\/", $filePattern) . '$/';
        if (!is_dir($fileDir)) {
            return false;
        }
        if ($mydir = @dir($fileDir)) {
            while ($file = $mydir->read()) {
                if (preg_match($filePattern, $file)) {
                    $found = true;
                    break;
                }
            }
            $mydir->close();
        }
        return $found;
    }

    /**
     * @since ZC v1.5.8
     */
    public function setFileExtension(string $file, string $extension = 'php'): string
    {
        if (preg_match('~\.' . $extension . '~i', $file)) {
            return $file;
        }
        return $file . '.php';
    }

    /**
     * @since ZC v1.5.8
     */
    public function hasTemplateLanguageOverride(string $templateDir, string $rootPath, string $language, string $file, string $extraPath = ''): bool
    {
        $file = $this->setFileExtension($file);
        $fullPath = $rootPath . $language . $extraPath . '/' . $templateDir . '/' . $file;
        if (!file_exists($fullPath)) {
            return false;
        }
        return true;
    }

    /**
     * @since ZC v1.5.8
     */
    public function getExtraPathForTemplateOverrrideOrOriginal(string $templateDir, string $rootPath, string $language, string $file, string $extraPath = ''): string
    {
        if (!$this->hasTemplateLanguageOverride($templateDir, $rootPath, $language, $file, $extraPath)) {
            return $extraPath;
        }
        $extraPath = $extraPath . '/' . $templateDir;
        return $extraPath;
    }

    /**
     * @since ZC v2.0.0
     */
    protected function realpath(string $path): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return str_replace('\\', '/', realpath($path));
        }
        return realpath($path);
    }

    /**
     * @since ZC v2.2.0
     */
    public function deleteDirectory(string $directory): bool
    {
        if (!is_dir($directory)) {
            return false;
        }

        $items = scandir($directory);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
                continue;
            }

            @unlink($path);
        }

        return @rmdir($directory);
    }
}
