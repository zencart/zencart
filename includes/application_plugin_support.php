<?php
use App\Models\PluginControl;
use App\Models\PluginControlVersion;
use Zencart\FileSystem\FileSystem;
use Zencart\PluginManager\PluginManager;
use Zencart\PageLoader\PageLoader;
use Aura\Autoload\Loader;

/** @var string $PHP_SELF */
/** @var Loader $psr4Autoloader */

$context = (IS_ADMIN_FLAG === true) ? 'admin' : 'catalog';

$pluginManager = new PluginManager(new PluginControl, new PluginControlVersion);
$installedPlugins = $pluginManager->getInstalledPlugins();
$pluginManager = new PluginManager(new App\Models\PluginControl, new App\Models\PluginControlVersion);
$pageLoader = PageLoader::getInstance();
$pageLoader->init($installedPlugins, $PHP_SELF, new FileSystem);

$fs = new FileSystem;
$fs->loadFilesFromPluginsDirectory($installedPlugins, $context . '/includes/extra_configures', '~^[^\._].*\.php$~i');
$fs->loadFilesFromPluginsDirectory($installedPlugins, $context . '/includes/extra_datafiles', '~^[^\._].*\.php$~i');
$fs->loadFilesFromPluginsDirectory($installedPlugins, $context . '/includes/functions/extra_functions', '~^[^\._].*\.php$~i');

$filePathPluginAdmin = [];
$filePathPluginCatalog = [];

foreach ($installedPlugins as $plugin) {
    $namespaceAdmin = 'Zencart\Plugins\Admin\\' . ucfirst($plugin['unique_key']);
    $namespaceCatalog = 'Zencart\Plugins\Catalog\\' . ucfirst($plugin['unique_key']);
    $filePath = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/';
    $filePathAdmin = $filePath . 'admin/includes/classes/';
    $filePathCatalog = $filePath . 'includes/classes/';
    $psr4Autoloader->addPrefix($namespaceAdmin, $filePathAdmin);
    $psr4Autoloader->addPrefix($namespaceCatalog, $filePathCatalog);

    $filePathPluginAdmin[$plugin['unique_key']] = $filePathAdmin;
    $filePathPluginCatalog[$plugin['unique_key']] = $filePathCatalog;

    if (file_exists($filePath . 'psr4AutoLoader.php')) {
        require_once($filePath . 'psr4AutoLoader.php');
    }
}

unset($context);
