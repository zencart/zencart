<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Mar 17 Modified in v2.2.1 $
 */

namespace Zencart\Traits;

use Zencart\DbRepositories\PluginControlRepository;
use Zencart\DbRepositories\PluginControlVersionRepository;
use Zencart\PageLoader\PageLoader;
use Zencart\PluginManager\PluginManager;

/**
 * @since ZC v2.1.0
 */
trait InteractsWithPlugins
{
    protected bool $isAZcPlugin = false;
    protected string $zcPluginDirName;
    protected string $zcPluginVersionDir;
    protected string $zcPluginPath;

    /** @var string catalog, admin, or Installer */
    protected string $zcPluginContext;

    /** @var ?string working directory of currently installed version; null if not installed */
    protected ?string $pluginManagerInstalledVersionDirectory;

    /** @var string will be null if no 'catalog' dir present (no catalog features) */
    protected string $zcPluginCatalogPath;
    /** @var string will be null if no 'admin' dir present (no admin features) */
    protected string $zcPluginAdminPath;
    /** @var string will be null if no 'Installer' dir present (should never be) */
    protected string $zcPluginInstallerPath;

    /**
     * Determine the plugin's currently-installed zc_plugin directory.
     * @since ZC v2.1.0
     */
    protected function detectZcPluginDetails(string $__dir__path): void
    {
        $is_in_zc_plugins_directory = \str_contains($__dir__path, 'zc_plugins');
        if (!$is_in_zc_plugins_directory) {
            return;
        }
        $__dir__path = str_replace('\\', '/', $__dir__path);
        $match = str_replace(rtrim(DIR_FS_CATALOG, '\\/') . '/zc_plugins/', '', $__dir__path);
        $matches = explode('/', $match);
        $this->zcPluginDirName = $matches[0];
        $this->zcPluginVersionDir = $matches[1];
        $this->zcPluginContext = $matches[2]; // 'admin' or 'catalog' or 'Installer'

        $this->zcPluginPath = str_replace('//', '/', DIR_FS_CATALOG . '/zc_plugins/' . $this->zcPluginDirName . '/' . $this->zcPluginVersionDir . '/');
        $this->isAZcPlugin = \file_exists($this->zcPluginPath . 'manifest.php');

        global $db;
        $plugin_manager = new PluginManager(new PluginControlRepository($db), new PluginControlVersionRepository($db));
        $this->pluginManagerInstalledVersionDirectory = $plugin_manager->getPluginVersionDirectory($this->zcPluginDirName, $plugin_manager->getInstalledPlugins());

        if ($this->pluginManagerInstalledVersionDirectory === null) {
            // plugin not installed, so we won't be able to determine the installed version's path or whether we're in the context of an installed version's files or not, so just return here.
            return;
        }

        $installedPluginPath = rtrim(str_replace(DIR_FS_CATALOG, '', $this->pluginManagerInstalledVersionDirectory), '/');
        if ($this->zcPluginContext === 'catalog') {
            $this->zcPluginCatalogPath = $installedPluginPath . '/catalog/';
        }
        if ($this->zcPluginContext === 'admin') {
            $this->zcPluginAdminPath = $installedPluginPath . '/admin/';
        }
        if ($this->zcPluginContext === 'Installer') {
            $this->zcPluginInstallerPath = $installedPluginPath . '/Installer/';
        }
    }

    /**
     * Link/output a stylesheet file from the plugin's css directory.
     * Checks first in the plugin's own css directory, then in the active template's css directory.
     *
     * @since ZC v2.1.0
     */
    protected function linkCatalogStylesheet(string $stylesheet_filename, ?string $current_page): bool
    {
        /** @var \template_func $template */
        global $template, $pageLoader, $current_page_base;
        if (!$pageLoader) {
            $pageLoader = PageLoader::getInstance();
        }

        $found = false;

        // link zc_plugin stylesheet
        $stylesheet_filename = basename($stylesheet_filename);
        if (file_exists($file = $pageLoader->getTemplatePluginDir($stylesheet_filename, 'css', $this->zcPluginDirName) . $stylesheet_filename)) {
            echo '<link rel="stylesheet" href="' . $file . '">' . "\n";
            $found = true;
        }

        // if catalog template contains a stylesheet of the same name, load it as well, to apply any overrides it may contain
        $stylesheet_dir = $template->get_template_dir($stylesheet_filename, DIR_WS_TEMPLATE, $current_page ?? $current_page_base, 'css') . '/';
        if (!str_contains($stylesheet_dir, $this->zcPluginCatalogPath) && file_exists($stylesheet_dir . $stylesheet_filename)) {
            echo '<link rel="stylesheet" href="' . $stylesheet_dir . $stylesheet_filename . '">' . "\n";
            $found = true;
        }

        return $found;
    }

    /**
     * Link/output a javascript file from the plugin's jscript directory.
     * If the filename ends with .js it is loaded as src=
     * If the filename ends with .php it is executed via require_once().
     *
     * @since ZC v3.0.0
     */
    protected function linkCatalogJscript(string $jsFilename, ?string $current_page): bool
    {
        global $pageLoader;
        if (!$pageLoader) {
            $pageLoader = PageLoader::getInstance();
        }

        $jsFilename = basename($jsFilename);
        if (file_exists($file = $pageLoader->getTemplatePluginDir($jsFilename, 'jscript', $this->zcPluginDirName) . $jsFilename)) {
            if (str_ends_with($jsFilename, '.js')) {
                echo '<script title="' . \zen_output_string_protected($this->zcPluginDirName) . '" src="' . $file . '">' . "\n";
                return true;
            }

            if (str_ends_with($jsFilename, '.php')) {
                require_once $file;
                return true;
            }
        }
        return false;
    }
}
