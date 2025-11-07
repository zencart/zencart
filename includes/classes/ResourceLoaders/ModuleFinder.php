<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
namespace Zencart\ResourceLoaders;


use Zencart\FileSystem\FileSystem;

/**
 * @since ZC v2.1.0
 */
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
    /**
     * @since ZC v2.1.0
     */
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
