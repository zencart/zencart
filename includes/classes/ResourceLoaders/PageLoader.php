<?php
/**
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
namespace Zencart\PageLoader;

use Zencart\FileSystem\FileSystem as FileSystem;
use Zencart\ResourceLoaders\TemplateResolver;
use Zencart\Traits\Singleton;

/**
 * @since ZC v1.5.7
 */
class PageLoader
{
    use Singleton;

    private array $installedPlugins;
    private string $mainPage;
    private FileSystem $fileSystem;
    private ?TemplateResolver $templateResolver = null;
    private array $templateSearchDirectories = [];

    /**
     * @since ZC v1.5.8
     */
    public function init(
        array $installedPlugins,
        string $mainPage,
        FileSystem $fileSystem,
        ?TemplateResolver $templateResolver = null
    ): void
    {
        $this->installedPlugins = $installedPlugins;
        $this->mainPage = $mainPage;
        $this->fileSystem = $fileSystem;
        $this->templateResolver = $templateResolver;
    }

    /**
     * This method locates the 'base' module-page directory, either in the
     * storefront's /includes/modules/pages or in an encapsulated plugin's
     * /catalog/includes/modules/pages directory.
     *
     * @since ZC v1.5.7
     */
    public function findModulePageDirectory(string $context = 'catalog'): bool|string
    {
        if (is_dir(DIR_WS_MODULES . 'pages/' . $this->mainPage)) {
            return DIR_WS_MODULES . 'pages/' . $this->mainPage;
        }
        foreach ($this->installedPlugins as $plugin) {
            $rootDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/' . $context;
            $checkDir = $rootDir . '/includes/modules/pages/' . $this->mainPage;
            if (is_dir($checkDir)) {
                return $checkDir;
            }
        }
        return false;
    }

    // -----
    // This method locates **all** files matching a given pattern from the 'base'
    // module-page directory and any module-page directories found in zc_plugins.
    //
    /**
     * @since ZC v2.2.0
     */
    public function listModulePagesFiles(string $nameStartsWith, string $fileExtension = '.php', string $context = 'catalog'): array
    {
        $module_page_dir = DIR_WS_MODULES . 'pages/' . $this->mainPage;
        $fileRegx = '~^' . $nameStartsWith . '.*\\' . $fileExtension . '$~i';
        $fileList = $this->fileSystem->listFilesFromDirectoryAlphaSorted($module_page_dir, $fileRegx, true);
        foreach ($this->installedPlugins as $plugin) {
            $rootDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/' . $context;
            $checkDir = $rootDir . '/' . $module_page_dir;
            $fileList = array_merge($fileList, $this->fileSystem->listFilesFromDirectoryAlphaSorted($checkDir, $fileRegx, true));
        }
        return $fileList;
    }

    /**
     * @since ZC v1.5.7
     */
    public function getTemplatePart(string $pageDirectory, string $templatePart, string $fileExtension = '.php'): array
    {
        if ($this->isTemplatePath($pageDirectory)) {
            $directoryArray = [];
            foreach ($this->getTemplateSearchDirectoriesFromPath($pageDirectory) as $directory) {
                $directoryArray = $this->getTemplatePartFromDirectory(
                    $directoryArray,
                    $directory,
                    $templatePart,
                    $fileExtension
                );
            }
            $directoryArray = array_values(array_unique($directoryArray));
            sort($directoryArray);
            return $directoryArray;
        }

        $directoryArray = $this->getTemplatePartFromDirectory(
            [],
            $pageDirectory,
            $templatePart,
            $fileExtension
        );

        foreach ($this->installedPlugins as $plugin) {
            $checkDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/';
            $checkDir .= $pageDirectory;
            $directoryArray = $this->getTemplatePartFromDirectory(
                $directoryArray,
                $checkDir,
                $templatePart,
                $fileExtension
            );
        }
        sort($directoryArray);
        return $directoryArray;
    }

    /**
     * Note: Changed from public to private for ZC 3.0.0.
     *
     * @since ZC v1.5.7
     */
    private function getTemplatePartFromDirectory(array $directoryArray, string $pageDirectory, string $templatePart, string $fileExtension): array
    {
        if ($dir = @dir($pageDirectory)) {
            while ($file = $dir->read()) {
                if (!is_dir($pageDirectory . $file)) {
                    if (str_ends_with($file, $fileExtension) && preg_match($templatePart, $file)) {
                        $directoryArray[] = $file;
                    }
                }
            }
            $dir->close();
        }
        return $directoryArray;
    }

    /**
     * Locates a specified file ($fileName) within a specified directory ($currentTemplateDir), which
     * is normally specified as TEMPLATE_DEFAULT).
     *
     * File location search order; first found is returned:
     *
     * 1. $currentTemplateDir / $currentPage, e.g. includes/templates/responsive_classic/popup_image/tpl_main_page.php
     * 2. zc_plugins default / $currentPage (first-found, alphanumerically sorted), e.g. zc_plugins/k/v2/catalog/includes/templates/default/popup_image/tpl_main_page.php
     * 3. template_default / $currentPage, e.g. includes/templates/template_default/popup_image/tpl_main_page.php
     * 4. $currentTemplateDir / $templateSubDir, e.g. includes/templates/responsive_classic/common/tpl_main_page.php
     * 5. zc_plugins default / $templateSubDir (first-found, alphanumerically sorted), e.g. zc_plugins/k/v2/catalog/includes/templates/default/common/tpl_main_page.php
     * 6. template_default / $templateSubDir, e.g. includes/templates/template_default/common/tpl_main_page.php
     *
     * For example, assuming that the selected template is 'responsive_classic':
     *
     * - getTemplateDirectory('tpl_main_page.php', DIR_WS_TEMPLATE, 'contact_us', 'common') returns
     *   - DIR_WS_TEMPLATE . 'common'
     * - getTemplateDirectory('tpl_main_page.php', DIR_WS_TEMPLATE, 'popup_shipping_estimator', 'common') returns
     *   - DIR_WS_TEMPLATES . 'template_default/popup_shipping_estimator'
     *
     * @since ZC v1.5.8
     */
    public function getTemplateDirectory(string $fileName, string $currentTemplateDir, string $currentPage, string $templateSubDir): string
    {
        $fileName = str_replace("/", '', $fileName);
        foreach ($this->getTemplateSearchDirectories($this->getCurrentTemplateKey($currentTemplateDir), $currentPage, $templateSubDir) as $directory) {
            if ($this->fileSystem->fileExistsInDirectory($directory, $fileName)) {
                return rtrim($directory, '/');
            }
        }
        return DIR_WS_TEMPLATES . 'template_default/' . trim($templateSubDir, '/');
    }

    /**
     * @since ZC v1.5.7
     */
    public function getTemplatePluginDir(string $fileName, string $templateDir, ?string $whichPlugin = ''): bool|string
    {
        foreach ($this->installedPlugins as $plugin) {
            if (!empty($whichPlugin) && $plugin['unique_key'] !== $whichPlugin) {
                continue;
            }

            foreach ($this->getPluginOverlayDirectories($plugin, $templateDir) as $checkDir) {
                if ($this->fileSystem->fileExistsInDirectory($checkDir, preg_replace('/\//', '', $fileName))) {
                    return $checkDir;
                }
            }
        }
        return false;
    }

    /**
     * @since ZC v1.5.7
     */
    public function getBodyCode(): string
    {
        // -----
        // Determine where, if anywhere, the current-page's main_template_vars.php
        // file resides.
        //
        // listModulePagesFiles returns all locations and the first-found file is used. That'll
        // be the file in /includes/modules/pages/{current_page} or (searching all active
        // plugins alphanumerically) the first-found in any zc_plugins.
        //
        $template_vars_locations = $this->listModulePagesFiles('main_template_vars');
        if (count($template_vars_locations) !== 0) {
            return $template_vars_locations[0];
        }
        return $this->getTemplateDirectory('tpl_' . preg_replace('/.php/', '', $this->mainPage) . '_default.php', DIR_WS_TEMPLATE, $this->mainPage, 'templates') . '/tpl_' . $this->mainPage . '_default.php';
    }

    /**
     * Returns an array with potential template-related directories to
     * be searched for a file.
     *
     * File locations' returned in this array/precedence order:
     *
     * 1. $templateKey's directory / $currentPage, e.g. includes/templates/responsive_classic/popup_image/
     * 2. zc_plugins default / $currentPage (first-found, alphanumerically sorted), e.g. zc_plugins/k/v2/catalog/includes/templates/default/popup_image/
     * 3. template_default / $currentPage, e.g. includes/templates/template_default/popup_image/
     * 4. $templateKey's directory / $templateSubDir, e.g. includes/templates/responsive_classic/common/
     * 5. zc_plugins default / $templateSubDir (first-found, alphanumerically sorted), e.g. zc_plugins/k/v2/catalog/includes/templates/default/common/
     * 6. template_default / $templateSubDir, e.g. includes/templates/template_default/common/
     *
     * @since ZC v3.0.0
     */
    private function getTemplateSearchDirectories(string $templateKey, string $currentPage, string $templateSubDir): array
    {
        // -----
        // If there was a previous request for the same information, return the
        // cached array.
        //
        if (isset($this->templateSearchDirectories[$templateKey][$currentPage][$templateSubDir])) {
            return $this->templateSearchDirectories[$templateKey][$currentPage][$templateSubDir];
        }

        $directories = [];
        $inheritanceChain = $this->getNonDefaultInheritanceChain($templateKey);

        foreach ($inheritanceChain as $chainTemplateKey) {
            $directories = array_merge(
                $directories,
                $this->getTemplateSubDirectory($chainTemplateKey, $currentPage),
                $this->getOverlayDirectoriesForTarget($chainTemplateKey, $currentPage)
            );
        }
        $directories = array_merge(
            $directories,
            $this->getDefaultTemplateSubDirectories($currentPage)
        );

        foreach ($inheritanceChain as $chainTemplateKey) {
            $directories = array_merge(
                $directories,
                $this->getTemplateSubDirectory($chainTemplateKey, $templateSubDir),
                $this->getOverlayDirectoriesForTarget($chainTemplateKey, $templateSubDir)
            );
        }
        $directories = array_merge(
            $directories,
            $this->getDefaultTemplateSubDirectories($templateSubDir)
        );

        $directories = array_values(array_unique(array_filter($directories)));
        $this->templateSearchDirectories[$templateKey][$currentPage][$templateSubDir] = $directories;

        return $directories;
    }

    /**
     * @since ZC v3.0.0
     */
    private function getTemplateSearchDirectoriesFromPath(string $pageDirectory): array
    {
        $normalized = $this->normalizeDirectory($pageDirectory);
        if (!preg_match('~includes/templates/([^/]+)/(.+)$~', $normalized, $matches)) {
            return [$pageDirectory];
        }

        $templateKey = $matches[1];
        $templateSubDir = trim($matches[2], '/');
        return $this->getTemplateSearchDirectories($templateKey, $this->mainPage, $templateSubDir);
    }

    /**
     * @since ZC v3.0.0
     */
    private function getTemplateSubDirectory(string $templateKey, string $subDirectory): array
    {
        $record = $this->getTemplateResolver()->getTemplateRecord($templateKey);
        if ($record === null) {
            return [];
        }

        $templateRoot = $this->getRelativeCatalogPath($record['template_path']);
        if ($templateRoot === null) {
            return [];
        }

        return [
            $templateRoot . '/' . trim($subDirectory, '/') . '/',
        ];
    }

    /**
     * @since ZC v3.0.0
     */
    private function getDefaultTemplateSubDirectories(string $subDirectory): array
    {
        $directories = [];
        foreach ($this->installedPlugins as $plugin) {
            foreach ($this->getPluginOverlayDirectories($plugin, $subDirectory, ['default']) as $directory) {
                $directories[] = $directory;
            }
        }

        $default_dir = DIR_WS_TEMPLATES . 'template_default/' . trim($subDirectory, '/') . '/';
        if (is_dir($default_dir)) {
            $directories[] = $default_dir;
        }

        return $directories;
    }

    /**
     * @since ZC v3.0.0
     */
    private function getOverlayDirectoriesForTarget(string $targetTemplate, string $templateSubDir): array
    {
        $directories = [];
        foreach ($this->installedPlugins as $plugin) {
            foreach ($this->getPluginOverlayDirectories($plugin, $templateSubDir, [$targetTemplate]) as $directory) {
                $directories[] = $directory;
            }
        }

        return $directories;
    }

    /**
     * @since ZC v3.0.0
     */
    private function getPluginOverlayDirectories(array $plugin, string $templateSubDir, ?array $targets = null): array
    {
        $templatesRoot = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/templates/';
        if (!is_dir(DIR_FS_CATALOG . $templatesRoot)) {
            return [];
        }

        $availableTargets = $targets ?? $this->getPluginTemplateTargets($templatesRoot);
        $directories = [];
        foreach ($availableTargets as $target) {
            $directory = $templatesRoot . trim($target, '/') . '/' . trim($templateSubDir, '/') . '/';
            if (is_dir(DIR_FS_CATALOG . $directory)) {
                $directories[] = $directory;
            }
        }

        return $directories;
    }

    /**
     * @since ZC v3.0.0
     */
    private function getPluginTemplateTargets(string $templatesRoot): array
    {
        $targets = [];
        $directory = new \DirectoryIterator(DIR_FS_CATALOG . $templatesRoot);
        foreach ($directory as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }

            $targets[] = $fileInfo->getFilename();
        }

        return $targets;
    }

    /**
     * @since ZC v3.0.0
     */
    private function getTemplateResolver(): TemplateResolver
    {
        if ($this->templateResolver === null) {
            $this->templateResolver = new TemplateResolver();
        }

        return $this->templateResolver;
    }

    /**
     * @since ZC v3.0.0
     */
    private function getCurrentTemplateKey(string $currentTemplateDir): string
    {
        $normalized = trim($this->normalizeDirectory($currentTemplateDir), '/');
        if ($normalized === '' || $normalized === 'template_default') {
            return 'template_default';
        }

        if (preg_match('~includes/templates/([^/]+)$~', $normalized, $matches)) {
            return $matches[1];
        }

        return basename($normalized);
    }

    /**
     * @since ZC v3.0.0
     */
    private function getNonDefaultInheritanceChain(string $templateKey): array
    {
        $chain = $this->getTemplateResolver()->getTemplateInheritanceChain($templateKey);
        return array_values(array_filter($chain, static fn(string $item): bool => $item !== 'template_default'));
    }

    /**
     * @since ZC v3.0.0
     */
    private function getRelativeCatalogPath(string $path): ?string
    {
        $normalizedCatalogRoot = rtrim(str_replace('\\', '/', DIR_FS_CATALOG), '/');
        $normalizedPath = $this->normalizeDirectory($path);
        if (!str_starts_with($normalizedPath, $normalizedCatalogRoot . '/')) {
            return null;
        }

        return substr($normalizedPath, strlen($normalizedCatalogRoot) + 1);
    }

    /**
     * @since ZC v3.0.0
     */
    private function isTemplatePath(string $path): bool
    {
        return str_contains($this->normalizeDirectory($path), 'includes/templates/');
    }

    /**
     * @since ZC v3.0.0
     */
    private function normalizeDirectory(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
