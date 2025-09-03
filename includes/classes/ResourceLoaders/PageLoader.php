<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 24 Modified in v2.1.0-beta1 $
 */
namespace Zencart\PageLoader;

use Zencart\FileSystem\FileSystem as FileSystem;
use Zencart\Traits\Singleton;

class PageLoader
{
    use Singleton;

    private array $installedPlugins;
    private string $mainPage;
    private FileSystem $fileSystem;

    public function init(array $installedPlugins, string $mainPage, FileSystem $fileSystem): void
    {
        $this->installedPlugins = $installedPlugins;
        $this->mainPage = $mainPage;
        $this->fileSystem = $fileSystem;
    }

    // -----
    // This method locates the 'base' module-page directory, either in the
    // storefront's /includes/modules/pages or in an encapsulated plugin's
    // /catalog/includes/modules/pages directory.
    //
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

    public function getTemplatePart(string $pageDirectory, string $templatePart, string $fileExtension = '.php'): array
    {
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

    public function getTemplatePartFromDirectory(array $directoryArray, string $pageDirectory, string $templatePart, string $fileExtension): array
    {
        if ($dir = @dir($pageDirectory)) {
            while ($file = $dir->read()) {
                if (!is_dir($pageDirectory . $file)) {
                    if (substr($file, strrpos($file, '.')) === $fileExtension && preg_match($templatePart, $file)) {
                        $directoryArray[] = $file;
                    }
                }
            }
            $dir->close();
        }
        return $directoryArray;
    }

    function getTemplateDirectory(string $templateCode, string $currentTemplate, string $currentPage, string $templateDir): string
    {
        if ($currentTemplate === 'template_default') {
            $currentTemplate = DIR_WS_TEMPLATES . $currentTemplate . '/';
        }

        $path = DIR_WS_TEMPLATES . 'template_default/' . $templateDir;

        if ($this->fileSystem->fileExistsInDirectory($currentTemplate . $currentPage, $templateCode)) {
            return $currentTemplate . $currentPage . '/';
        }
        if ($this->fileSystem->fileExistsInDirectory(DIR_WS_TEMPLATES . 'template_default/' . $currentPage, preg_replace('/\//', '', $templateCode))) {
            return DIR_WS_TEMPLATES . 'template_default/' . $currentPage;
        }
        if ($this->fileSystem->fileExistsInDirectory($currentTemplate . $templateDir, preg_replace('/\//', '', $templateCode))) {
            return $currentTemplate . $templateDir;
        }
        if ($tplPluginDir = $this->getTemplatePluginDir($templateCode, $templateDir)) {
            return $tplPluginDir;
        }
        return $path;
    }

    public function getTemplatePluginDir(string $templateCode, string $templateDir, ?string $whichPlugin = ''): bool|string
    {
        foreach ($this->installedPlugins as $plugin) {
            if (!empty($whichPlugin) && $plugin['unique_key'] !== $whichPlugin) {
                continue;
            }

            $checkDir = 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/catalog/includes/templates/default/' . $templateDir . '/';
            if ($this->fileSystem->fileExistsInDirectory($checkDir, preg_replace('/\//', '', $templateCode))) {
                return $checkDir;
            }
        }
        return false;
    }

    public function getBodyCode(): string
    {
        if (file_exists(DIR_WS_MODULES . 'pages/' . $this->mainPage . '/main_template_vars.php')) {
            return DIR_WS_MODULES . 'pages/' . $this->mainPage . '/main_template_vars.php';
        }
        return $this->getTemplateDirectory('tpl_' . preg_replace('/.php/', '', $this->mainPage) . '_default.php', DIR_WS_TEMPLATE, $this->mainPage, 'templates') . '/tpl_' . $this->mainPage . '_default.php';
    }
}
