<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 24 New in v2.1.0-beta1 $
 */

namespace Zencart\Traits;

use App\Models\PluginControl;
use App\Models\PluginControlVersion;
use Zencart\PageLoader\PageLoader;
use Zencart\PluginManager\PluginManager;

trait InteractsWithPlugins
{
    protected bool $isAZcPlugin = false;
    protected string $zcPluginDirName;
    protected string $zcPluginVersionDir;
    protected string $zcPluginPath;

    /** @var string catalog, admin, or Installer */
    protected string $zcPluginContext;

    /** @var string working directory of currently installed version */
    protected string $pluginManagerInstalledVersionDirectory;

    /** @var string will be null if no 'catalog' dir present (no catalog features) */
    protected string $zcPluginCatalogPath;
    /** @var string will be null if no 'admin' dir present (no admin features) */
    protected string $zcPluginAdminPath;
    /** @var string will be null if no 'Installer' dir present (should never be) */
    protected string $zcPluginInstallerPath;

    /**
     * Determine the plugin's currently-installed zc_plugin directory.
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

        $plugin_manager = new PluginManager(new PluginControl(), new PluginControlVersion());
        $this->pluginManagerInstalledVersionDirectory = $plugin_manager->getPluginVersionDirectory($this->zcPluginDirName, $plugin_manager->getInstalledPlugins());

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
     * @param string $stylesheet_filename
     * @param string|null $current_page
     * @return bool
     *
     * @var \template_func $template
     * @var PageLoader $pageLoader
     */
    protected function linkCatalogStylesheet(string $stylesheet_filename, ?string $current_page): bool
    {
        global $template, $pageLoader;
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
        $stylesheet_dir = $template->get_template_dir($stylesheet_filename, DIR_WS_TEMPLATE, $current_page, 'css') . '/';
        if (!str_contains($stylesheet_dir, $this->zcPluginCatalogPath) && file_exists($stylesheet_dir . $stylesheet_filename)) {
            echo '<link rel="stylesheet" href="' . $stylesheet_dir . $stylesheet_filename . '">' . "\n";
            $found = true;
        }

        return $found;
    }
}
