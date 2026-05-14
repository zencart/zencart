<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
namespace Zencart\ResourceLoaders;

/**
 * @since ZC v3.0.0
 */
class HtmlIncludesFinder
{
    private $filesystem;
    private array $installedPlugins;
    private string $language;
    private string $fallback = 'english';
    private string $templateDir;

    private static array $files;

    public function __construct($filesystem, array $installedPlugins, string $language, string $templateDir)
    {
        $this->filesystem = $filesystem;
        $this->installedPlugins = $installedPlugins;
        $this->language = $language;
        $this->templateDir = $templateDir;
    }

    /**
     * @since ZC v3.0.0
     */
    public function setFallback(string $fallback): void
    {
        $this->fallback = $fallback;
    }

    /**
     * @since ZC v3.0.0
     */
    public function findAll(): array
    {
        if (isset(self::$files)) {
            return self::$files;
        }

        // -----
        // Note: File directories are searched in reverse order of precedence, since
        // a file's "name" is used as the $files' array's key so that the last file
        // of a matching name is the directory that is used!
        //
        // 1. Fallback (e.g. english) directories' /html_includes/ base.
        // 2. Current language directories' /html_includes/ base.
        // 3. Fallback (e.g. english) directories' /html_includes/{templateDir}/
        // 4. Current language directories' /html_includes/{templateDir}/
        //
        $file_search_order = [];
        if ($this->fallback !== $this->language) {
            $file_search_order = $this->addToSearch($file_search_order, $this->fallback . '/html_includes/');
        }
        $file_search_order = $this->addToSearch($file_search_order, $this->language . '/html_includes/');

        if ($this->fallback !== $this->language) {
            $file_search_order = $this->addToSearch($file_search_order, $this->fallback . '/html_includes/' . $this->templateDir . '/');
        }
        $file_search_order = $this->addToSearch($file_search_order, $this->language . '/html_includes/' . $this->templateDir . '/');

        $files = [];
        foreach ($file_search_order as $next_dir) {
            $dir_files = $this->filesystem->listFilesFromDirectoryAlphaSorted($next_dir);
            foreach ($dir_files as $filename) {
                $files[$filename] = $next_dir;
            }
        }

        self::$files = $files;
        return $files;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function addToSearch(array $search_array, string $html_includes_dir): array
    {
        $search_array[] = DIR_FS_CATALOG . DIR_WS_LANGUAGES . $html_includes_dir;
        foreach ($this->installedPlugins as $plugin) {
            $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/languages/';
            $search_array[] = $pluginDir . $html_includes_dir;
        }
        return $search_array;
    }

    /**
     * @since ZC v3.0.0
     */
    public function find(string $filename, bool $withFullPath = true): bool|string
    {
        if (!isset(self::$files)) {
            $this->findAll();
        }

        if (!in_array($filename, array_keys(self::$files))) {
            return false;
        }

        $found_file = self::$files[$filename] . $filename;
        if ($withFullPath === true) {
            return $found_file;
        }

        return str_replace(DIR_FS_CATALOG, '', $found_file);
    }
}
