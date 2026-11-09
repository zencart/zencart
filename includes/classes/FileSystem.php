<?php

declare(strict_types=1);
/**
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 26 Modified in v2.2.1 $
 */

namespace Zencart\FileSystem;

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

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
                require_once $rootDir . '/' . $file;
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
            $resolvedPluginDir = $this->realpath($pluginDir);
            if ($resolvedPluginDir === null) {
                continue;
            }
            $adminFile = $pluginDir . '/admin/' . $page . '.php';
            $realPath = $this->realpath($adminFile);
            if ($realPath === null || strpos($realPath, $resolvedPluginDir) !== 0) {
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
    public function getDirectorySize(string $path, $decimals = 2, bool $addSuffix = true): string
    {
        $bytes = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $bytes += $file->getSize();
        }
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen((string)$bytes) - 1) / 3);
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
    protected function realpath(string $path): ?string
    {
        $resolvedPath = realpath($path);
        if ($resolvedPath === false) {
            return null;
        }

        if (strtoupper(substr(\PHP_OS, 0, 3)) === 'WIN') {
            return str_replace('\\', '/', $resolvedPath);
        }

        return $resolvedPath;
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

            $path = $directory . \DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
                continue;
            }

            @unlink($path);
        }

        return @rmdir($directory);
    }

    /**
     * @since ZC v3.0.0
     */
    public function createDirectoryRecursive(string $rootDirectory, string $subDirectory): bool
    {
        $rootDirectory = rtrim($rootDirectory, '/\\') . '/';
        if (empty($subDirectory) || str_contains($subDirectory, '..') || !is_dir($rootDirectory)) {
            return false;
        }

        $subDirectory = trim($subDirectory, '/\\') . '/';
        if (is_dir($rootDirectory . $subDirectory)) {
            return true;
        }

        return mkdir($rootDirectory . $subDirectory, 0755, recursive: true);
    }

    /**
     * Gathers and returns an array of all subdirectories found 'under' the supplied
     * $rootDirectory. If $returnRelativeDir is false, each subdirectory is prefixed by
     * the supplied $rootDirectory; otherwise, only the subdirectory 'chain' is returned.
     *
     * Note: Returned subdirectories do not end with a directory-separator!
     *
     * @since ZC v3.0.0
     */
    public function getSubdirectoriesRecursive(string $rootDirectory, bool $returnRelativeDir): array
    {
        if ($rootDirectory === '' || !is_dir($rootDirectory)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootDirectory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $subdirectories = [];
        $real_root_directory = $this->realpath($rootDirectory);
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                $subdirectory = $this->realpath($fileInfo->getRealPath());
                if ($subdirectory === null) {
                    continue;
                }

                if ($returnRelativeDir === true) {
                    $subdirectory = str_replace($real_root_directory, '', $subdirectory);
                }
                $subdirectories[] = rtrim($subdirectory, '/\/');
            }
        }

        return $subdirectories;
    }

    /**
     * Validates the supplied string for use as the name of a subdirectory.
     *
     * The string must begin with an alphabetic character and contain only alphanumeric
     * characters, underscores or dashes.
     *
     * @since ZC v3.0.0
     */
    public function validateSubDirectoryName(string $subDirectoryName): bool
    {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_\-]*$/', $subDirectoryName) === 1;
    }
}
