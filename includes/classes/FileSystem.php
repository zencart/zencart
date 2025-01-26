<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 21 Modified in v2.1.0-beta1 $
 */
namespace Zencart\FileSystem;

use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;

class FileSystem extends IlluminateFilesystem
{
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

    public function listFilesFromDirectory(string $rootDir, string $fileRegx = '~^[^\._].*\.php$~i'): array
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
                $fileList[] = basename($rootDir . '/' . $file);
            }
        }
        $dir->close();
        return $fileList;
    }

    public function listFilesFromDirectoryAlphaSorted(string $rootDir, string $fileRegx = '~^[^\._].*\.php$~i'): array
    {
        $fileList = $this->listFilesFromDirectory($rootDir, $fileRegx);
        sort($fileList);
        return $fileList;
    }

    public function loadFilesFromPluginsDirectory(array $installedPlugins, string $rootDir, string $fileRegx = '~^[^\._].*\.php$~i'): void
    {
        foreach ($installedPlugins as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'];
            $pluginDir = $pluginDir . '/' . $rootDir;
            $this->loadFilesFromDirectory($pluginDir, $fileRegx);
        }
    }

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

    public function setFileExtension(string $file, string $extension = 'php'): string
    {
        if (preg_match('~\.' . $extension . '~i', $file)) {
            return $file;
        }
        return $file . '.php';
    }

    public function hasTemplateLanguageOverride(string $templateDir, string $rootPath, string $language, string $file, string $extraPath = ''): bool
    {
        $file = $this->setFileExtension($file);
        $fullPath = $rootPath . $language . $extraPath . '/' . $templateDir . '/' . $file;
        if (!file_exists($fullPath)) {
            return false;
        }
        return true;
    }

    public function getExtraPathForTemplateOverrrideOrOriginal(string $templateDir, string $rootPath, string $language, string $file, string $extraPath = ''): string
    {
        if (!$this->hasTemplateLanguageOverride($templateDir, $rootPath, $language, $file, $extraPath)) {
            return $extraPath;
        }
        $extraPath = $extraPath . '/' . $templateDir;
        return $extraPath;
    }

    protected function realpath(string $path): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return str_replace('\\', '/', realpath($path));
        }
        return realpath($path);
    }
}
