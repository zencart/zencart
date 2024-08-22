<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 */
namespace Zencart\ResourceLoaders;


use Zencart\FileSystem\FileSystem;

class ModuleFinder
{
    private FileSystem $filesystem;

    private string $moduleDir;

    public function __construct(string $moduleType, FileSystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->moduleDir = "$moduleType/";
    }

    // -----
    // Locate all modules of the type specified during the class construction,
    // noting that any duplication in zc_plugins **overrides** any base module!
    //
    public function findFromFilesystem(array $installedPlugins): array
    {
        $modules = [];

        $baseDir = DIR_WS_MODULES . $this->moduleDir;
        $files = $this->filesystem->listFilesFromDirectoryAlphaSorted(DIR_FS_CATALOG . $baseDir);
        foreach ($files as $file) {
            $modules[$file] = $baseDir;
        }

        foreach ($installedPlugins as $plugin) {
            $pluginDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/modules/' . $this->moduleDir;
            $files = $this->filesystem->listFilesFromDirectoryAlphaSorted(DIR_FS_CATALOG . $pluginDir);
            foreach ($files as $file) {
                $modules[$file] = $pluginDir;
            }
        }
        return $modules;
    }
}
