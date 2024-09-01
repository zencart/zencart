<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 18 Modified in v2.1.0-alpha2 $
 */
namespace Zencart\ResourceLoaders;

class SideboxFinder
{
    private $filesystem;

    public function __construct($filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function findFromFilesystem(array $installedPlugins, string $templateDir): array
    {
        $sideboxes = [];
        foreach ($installedPlugins as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/modules/sideboxes/';
            $files = $this->filesystem->listFilesFromDirectoryAlphaSorted($pluginDir);
            foreach ($files as $file) {
                $sideboxes[$file] = $plugin['unique_key'] . '/' . $plugin['version'];
            }
        }
        $mainDir = DIR_FS_CATALOG_MODULES . 'sideboxes/';
        $mainDirTpl = DIR_FS_CATALOG_MODULES . 'sideboxes/' . $templateDir . '/';
        $files = $this->filesystem->listFilesFromDirectoryAlphaSorted($mainDir);
        foreach ($files as $file) {
            $sideboxes[$file] = '';
        }
        $files = $this->filesystem->listFilesFromDirectoryAlphaSorted($mainDirTpl);
        foreach ($files as $file) {
            $sideboxes[$file] = '';
        }
        return $sideboxes;
    }

    public function sideboxPath($sideboxInfo, string $templateDir, bool $withFullPath = false): bool|string
    {
        if (!empty($sideboxInfo['plugin_details'])) {
            $path = $this->sideboxPathInPlugin($sideboxInfo);
            $path = ($withFullPath) ? DIR_FS_CATALOG . 'zc_plugins/' . $path . '/catalog/includes/modules/sideboxes/': $path;
            return $path;
        }
        $baseDir = DIR_FS_CATALOG . DIR_WS_MODULES . 'sideboxes/';
        $rootPath = ($withFullPath) ? DIR_FS_CATALOG . DIR_WS_MODULES : '';
        if (file_exists($baseDir . $templateDir . '/' . $sideboxInfo['layout_box_name'])) {
            return $rootPath . 'sideboxes/' . $templateDir . '/';
        }
        if (file_exists($baseDir . $sideboxInfo['layout_box_name'])) {
            return $rootPath . 'sideboxes/';
        }
        return false;
    }

    public function sideboxPathInPlugin($sideboxInfo): bool|string
    {
        $baseDir = DIR_FS_CATALOG . 'zc_plugins/' . $sideboxInfo['plugin_details'] . '/'  . 'catalog/includes/modules/sideboxes/';
        if (file_exists($baseDir . $sideboxInfo['layout_box_name'])) {
            return $sideboxInfo['plugin_details'];
        }
        return false;
    }
}
