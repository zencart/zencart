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
    private TemplateResolver $templateResolver;

    private static array $files = [];

    public function __construct($filesystem, array $installedPlugins, string $language, string $templateDir)
    {
        $this->filesystem = $filesystem;
        $this->installedPlugins = $installedPlugins;
        $this->language = $language;
        $this->templateDir = $templateDir;
        $this->templateResolver = new TemplateResolver(null, null, null, $installedPlugins);
    }

    /**
     * @since ZC v3.0.0
     */
    public function setFallback(string $fallback): void
    {
        if ($this->fallback === $fallback) {
            return;
        }
        $this->fallback = $fallback;
        unset(self::$files[$this->getCacheKey()]);
    }

    /**
     * @since ZC v3.0.0
     */
    public function findAll(): array
    {
        $cacheKey = $this->getCacheKey();
        if (isset(self::$files[$cacheKey])) {
            return self::$files[$cacheKey];
        }

        // -----
        // Files are found using this search order. For each of the directories
        // searched, the returned array is ordered by "base" includes/languages directory
        // first, followed by plugin directories (alphabetically sorted by plugin key).
        //
        // 1. Current language directories' /html_includes/{template inheritance chain}/
        // 2. Current language directories' /html_includes/ base.
        // 3. Fallback (e.g. english) directories' /html_includes/{template inheritance chain}/
        // 4. Fallback (e.g. english) directories' /html_includes/ base.
        //
        $file_search_order = [];
        foreach ($this->getTemplateInheritanceChain() as $templateKey) {
            $file_search_order = $this->addToSearch($file_search_order, $this->language . '/html_includes/' . $templateKey . '/');
        }
        $file_search_order = $this->addToSearch($file_search_order, $this->language . '/html_includes/');
        if ($this->fallback !== $this->language) {
            foreach ($this->getTemplateInheritanceChain() as $templateKey) {
                $file_search_order = $this->addToSearch($file_search_order, $this->fallback . '/html_includes/' . $templateKey . '/');
            }
            $file_search_order = $this->addToSearch($file_search_order, $this->fallback . '/html_includes/');
        }

        $files = [];
        foreach ($file_search_order as $next_dir) {
            $dir_files = $this->filesystem->listFilesFromDirectoryAlphaSorted($next_dir);
            foreach ($dir_files as $filename) {
                $files[$filename] ??= $next_dir;  //- First file found is used
            }
        }

        self::$files[$cacheKey] = $files;
        return self::$files[$cacheKey];
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
        $files = $this->findAll();

        if (!array_key_exists($filename, $files)) {
            return false;
        }

        $found_file = $files[$filename] . $filename;
        if ($withFullPath === true) {
            return $found_file;
        }

        return str_replace(DIR_FS_CATALOG, '', $found_file);
    }

    /**
     * @since ZC v3.0.0
     */
    protected function getTemplateInheritanceChain(): array
    {
        $chain = $this->templateResolver->getTemplateInheritanceChain($this->templateDir);
        if ($chain === []) {
            return [$this->templateDir];
        }

        return array_values(array_unique($chain));
    }

    /**
     * @since ZC v3.0.0
     */
    protected function getCacheKey(): string
    {
        $plugins = array_map(
            static fn(array $plugin): string => ($plugin['unique_key'] ?? '') . ':' . ($plugin['version'] ?? ''),
            $this->installedPlugins
        );

        return implode('|', [
            $this->language,
            $this->fallback,
            $this->templateDir,
            implode(',', $plugins),
        ]);
    }
}
