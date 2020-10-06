<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 18 Modified in v1.5.7 $
 */

namespace Zencart\FileSystem;

use Zencart\Traits\Singleton;

class FileSystem
{
    use Singleton;

    protected $installedPlugins;

    public function loadFilesFromDirectory($rootDir, $fileRegx)
    {
        if (!$dir = @dir($rootDir)) return;
        while ($file = $dir->read()) {
            if (preg_match($fileRegx, $file) > 0) {
                require_once($rootDir . '/' . $file);
            }
        }
        $dir->close();
    }

    public function listFilesFromDirectory($rootDir, $fileRegx)
    {
        if (!$dir = @dir($rootDir)) return [];
        $fileList = [];
        while ($file = $dir->read()) {
            if (preg_match($fileRegx, $file) > 0) {
                $fileList[] = basename($rootDir . '/' . $file);
            }
        }
        $dir->close();
        return $fileList;
    }

    public function loadFilesFromPluginsDirectory($installedPlugins, $rootDir, $fileRegx)
    {
        foreach ($installedPlugins as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'];
            $pluginDir = $pluginDir . '/' . $rootDir;
            $this->loadFilesFromDirectory($pluginDir, $fileRegx);
        }
    }

    public function findPluginAdminPage($installedPlugins, $page)
    {
        $found = null;
        foreach ($installedPlugins as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'];
            $adminFile = $pluginDir . '/admin/' . $page . '.php';
            if (!file_exists($adminFile)) {
                continue;
            }
            $found = $adminFile;
        }
        return $found;
    }

    public function isAdminDir($filePath)
    {
        if (!defined('DIR_FS_ADMIN')) return false;
        $test = str_replace(DIR_FS_ADMIN, '', $filePath);
        if ($test != $filePath) return false;
        return true;
    }

    public function isCatalogDir($filePath)
    {
        if ($this->isAdminDir($filePath)) return false;
        if (!defined('DIR_FS_CATALOG')) return false;
        $test = str_replace(DIR_FS_CATALOG, '', $filePath);
        if ($test != $filePath) return false;
        return true;

    }

    public function getRelativeDir($filePath)
    {
        if ($this->isAdminDir($filePath)) return str_replace(DIR_FS_ADMIN, '', $filePath);
        if ($this->isCatalogDir($filePath)) return str_replace(DIR_FS_CATALOG, '', $filePath);
        return $filePath;
    }

    public function getDirectorySize($path, $decimals = 2, $addSuffix = true)
    {
        $bytes = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $bytes += $file->getSize();
        }
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        $suffix = 'bloody huge!';
        if (isset($size[$factor])) $suffix = $size[$factor];
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $suffix;
    }

    public function fileExistsInDirectory($fileDir, $filePattern)
    {
        $found = false;
        $filePattern = '/' . str_replace("/", "\/", $filePattern) . '$/';
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

    public function getPluginRelativeDirectory($pluginKey)
    {
        if (!isset($this->installedPlugins[$pluginKey])) {
            return null;
        }
        $version = $this->installedPlugins[$pluginKey]['version'];
        $relativePath = '/zc_plugins/' . $pluginKey . '/' . $version . '/';
        return $relativePath;
    }


    public function getPluginAbsoluteDirectory($pluginKey)
    {
        if (!isset($this->installedPlugins[$pluginKey])) {
            return null;
        }
        $version = $this->installedPlugins[$pluginKey]['version'];
        $absolutePath = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version . '/';
        return $absolutePath;
    }

    public function setInstalledPlugins($installedPlugins)
    {
        $this->installedPlugins = $installedPlugins;
    }
}
