<?php

declare(strict_types=1);
/**
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\InitSystem;

use Zencart\FileSystem\FileSystem;
use Zencart\PluginManager\PluginManager;

/**
 * @since ZC v1.5.7
 */
class InitSystem
{
    private bool $debug = false;
    private array $debugList = [];
    private array $actionList = [];

    public function __construct(private string $context, private string $loaderPrefix, private FileSystem $fileSystem, private PluginManager $pluginManager, private array $installedPlugins) {}

    /**
     * @since ZC v1.5.7
     */
    public function loadAutoLoaders(): array
    {
        $coreLoaderList = $this->loadAutoLoadersFromSystem('core', DIR_WS_INCLUDES . 'auto_loaders');
        $pluginLoaderList = $this->loadPluginAutoLoaders('plugin');
        $mainLoaderList = $this->mergeAutoLoaders($coreLoaderList, $pluginLoaderList);
        return $mainLoaderList;
    }

    /**
     * @since ZC v1.5.7
     */
    public function setDebug(bool $debug = false): void
    {
        $this->debug = $debug;
    }

    /**
     * @since ZC v1.5.7
     */
    public function processLoaderList(array $loaderList): array
    {
        ksort($loaderList);
        foreach ($loaderList as $actionPoint => $entries) {
            $this->debugList[] = '##################################################################';
            $this->debugList[] = 'Action Point - ' . $actionPoint;
            $this->processActionPointEntries($entries);
        }
        if ($this->debug) {
            echo 'function processLoaderList:<pre>';
            print_r($this->debugList);
            echo '</pre>';
        }
        return $this->actionList;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function processActionPointEntries(array $entries): void
    {
        foreach ($entries as $entry) {
            if (!isset($entry['forceLoad'])) {
                $entry['forceLoad'] = false;
            }
            $this->processActionPointEntry($entry);
            $this->debugList[] = '=================================================================';
        }
    }

    /**
     * @since ZC v1.5.7
     */
    protected function processActionPointEntry(array $entry): void
    {
        $autoTypeMethod = 'processAutoType' . ucfirst($entry['autoType']);
        $this->debugList[] = 'Auto Type Method - ' . $autoTypeMethod;
        if (!method_exists($this, $autoTypeMethod)) {
            return;
        }
        $this->$autoTypeMethod($entry);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function processAutoTypeClass(array $entry): void
    {
        $filePath = DIR_FS_CATALOG . DIR_WS_CLASSES;
        if (isset($entry['classPath'])) {
            $filePath = $entry['classPath'];
        }
        if ($entry['loaderType'] === 'plugin') {
            $classPath = $entry['classPath'] ?? DIR_WS_CLASSES;
            $pluginPath = $this->findPluginDirectory($classPath, $entry['pluginInfo']['unique_key']);
            // We check the joined fragment, to avoid misfires from concatenation.
            if ($pluginPath === null || !$this->isPluginRelativePath($classPath . $entry['loadFile'])) {
                $this->debugList[] = 'loading class - ' . $entry['loadFile'] . ' - REFUSED';
                return;
            }
            $filePath = $pluginPath;
        }
        $this->debugList[] = 'processing class - ' . $filePath . $entry['loadFile'];
        $result = 'FAILED';
        // is_file() rather than file_exists(), which also accepts a directory
        if (is_file($filePath . $entry['loadFile'])) {
            $result = 'SUCCESS';
            $this->actionList[] = ['type' => 'include', 'filePath' => $filePath . $entry['loadFile'], 'forceLoad' => $entry['forceLoad']];
        }
        $this->debugList[] = 'loading class - ' . $filePath . $entry['loadFile'] . ' - ' . $result;
    }

    /**
     * @param $entry
     * @todo should deprecate session bound classes.
     * @since ZC v1.5.7
     */
    protected function processAutoTypeClassInstantiate(array $entry): void
    {
        $objectName = $entry['objectName'];
        $className = $entry['className'];
        $this->debugList[] = 'processing class instantiate - class = ' . $className . ' object name = ' . $objectName;
        $classSession = (isset($entry['classSession']) && $entry['classSession'] === true);
        $checkInstantiated = (isset($entry['checkInstantiated']) && $entry['checkInstantiated'] === true);
        /**
         * loaderType travels with the action so that the autoloader loop can tell a
         * broken plugin from a broken core install. Only the former is recoverable:
         * continuing without a core class leaves the request half-initialised.
         */
        if (!$classSession) {
            $this->debugList[] = 'instantiating normal class - ' . $className . ' as ' . $objectName;
            $this->actionList[] = ['type' => 'class', 'object' => $objectName, 'class' => $className, 'loaderType' => $entry['loaderType']];
            return;
        }
        $this->debugList[] = 'instantiating session bound class - ' . $className . ' as ' . $objectName;
        $this->actionList[] = ['type' => 'sessionClass', 'object' => $objectName, 'class' => $className, 'checkInstantiated' => $checkInstantiated, 'loaderType' => $entry['loaderType']];
        return;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function processAutoTypeObjectMethod(array $entry): void
    {
        $objectName = $entry['objectName'];
        $methodName = $entry['methodName'];
        $this->debugList[] = 'processing object method - ' . $objectName . ' => ' . $methodName;
        $this->actionList[] = ['type' => 'objectMethod', 'object' => $objectName, 'method' => $methodName, 'loaderType' => $entry['loaderType']];
    }

    /**
     * Loads a file named in full by the auto_loader entry.
     *
     * Unlike the class and init_script types, loadFile requires a path that is usable
     * as-is: core entries pass DIR_FS_CATALOG . DIR_WS_INCLUDES . '...' from the admin
     * and a working-directory-relative path from the storefront. A plugin wanting a
     * file from its own tree should use the class or init_script types, which do
     * resolve, or autoload it through the Zencart\Plugins namespaces registered for
     * every installed plugin at bootstrap, or psr4Autoload.
     *
     * @since ZC v1.5.7
     */
    protected function processAutoTypeRequire(array $entry): void
    {
        $filePath = $entry['loadFile'];
        $this->debugList[] = 'processing require - ' . $entry['loadFile'];
        $result = 'FAILED';
        if (file_exists($filePath)) {
            $result = 'SUCCESS';
            $this->actionList[] = ['type' => 'require', 'filePath' => $filePath, 'forceLoad' => $entry['forceLoad']];
        }
        $this->debugList[] = 'loading require - ' . $filePath . ' - ' . $result;
    }

    /**
     * Loads a file named in full by the auto_loader entry.
     *
     * The same contract as processAutoTypeRequire(): loadFile is used as written and
     * is not resolved against the plugin's directory.
     *
     * @since ZC v1.5.7
     */
    protected function processAutoTypeInclude(array $entry): void
    {
        $filePath = $entry['loadFile'];
        $this->debugList[] = 'processing include - ' . $entry['loadFile'];
        $result = 'FAILED';
        if (file_exists($filePath)) {
            $result = 'SUCCESS';
            $this->actionList[] = ['type' => 'include', 'filePath' => $filePath, 'forceLoad' => $entry['forceLoad']];
        }
        $this->debugList[] = 'loading include - ' . $filePath . ' - ' . $result;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function processAutoTypeInit_script(array $entry): void
    {
        $isPluginEntry = ($entry['loaderType'] === 'plugin');
        $relativeDir = DIR_WS_INCLUDES . 'init_includes/';
        $actualDir = $relativeDir;
        if ($isPluginEntry) {
            $pluginDir = $this->findPluginDirectory($relativeDir, $entry['pluginInfo']['unique_key']);
            /**
             * The joined fragment is checked, not the directory and loadFile
             * separately, to avoid traversal on concatenation.
             */
            if ($pluginDir === null || !$this->isPluginRelativePath($relativeDir . $entry['loadFile'])) {
                $this->debugList[] = 'loading init_script - ' . $entry['loadFile'] . ' - REFUSED';
                return;
            }
            $actualDir = $pluginDir;
        }
        if (is_file($actualDir . 'overrides/' . $entry['loadFile'])) {
            $actualDir .= 'overrides/';
        }

        /**
         * Missing core files fail fast here.
         * But for plugins missing classes are only warned about here, to avoid crashing during bootstrapping.
         *
         * Uses is_file() because file_exists() accepts a directory which would fail on require.
         */
        $filePath = $actualDir . $entry['loadFile'];
        if ($isPluginEntry && !is_file($filePath)) {
            trigger_error('Autoloader: init_script "' . $filePath . '" was not found; skipping.', E_USER_WARNING);
            $this->debugList[] = 'loading init_script - ' . $filePath . ' - FAILED';
            return;
        }
        $this->actionList[] = ['type' => 'require', 'filePath' => $filePath, 'forceLoad' => $entry['forceLoad']];
        $this->debugList[] = 'loading init_script - ' . $filePath . ' - SUCCESS';
    }

    /**
     * @since ZC v1.5.7
     */
    protected function loadAutoLoadersFromSystem(string $loaderType, string $rootDir, $plugin = []): array
    {
        $fileList = $this->fileSystem->listFilesFromDirectoryAlphaSorted($rootDir);
        $fileList = $this->processForOverrides($loaderType, $fileList, $rootDir);
        $loaderList = $this->getLoadersFromFileList($fileList);
        return $this->processLoaderListForType($loaderType, $loaderList, $plugin);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function loadPluginAutoLoaders(string $loaderType): array
    {
        $pluginLoaderList = [];
        foreach ($this->installedPlugins as $plugin) {
            $baseDir = $this->pluginManager->getPluginVersionDirectory($plugin['unique_key'], $this->installedPlugins);
            $rootDir = $baseDir . $this->context . '/includes/auto_loaders';
            $loaderList = $this->loadAutoLoadersFromSystem($loaderType, $rootDir, $plugin);
            $pluginLoaderList = $this->mergeAutoLoaders($pluginLoaderList, $loaderList);
        }
        return $pluginLoaderList;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function processForOverrides(string $loaderType, array $fileList, string $rootDir): array
    {
        $newFileList = [];
        $baseDir = $rootDir;
        $overrideDir = $baseDir . '/overrides';
        $core_loader_file = '';
        if ($loaderType === 'core') {
            $core_loader_file = $this->loaderPrefix . '.core.php';
            if ($this->overrideFileExists($core_loader_file, $overrideDir)) {
                $newFileList[] = $overrideDir . '/' . $core_loader_file;
            } else {
                $newFileList[] = $baseDir . '/' . $core_loader_file;
            }
        }
        foreach ($fileList as $file) {
            if ($file === $core_loader_file || !$this->fileMatchesLoaderPrefix($file)) {
                continue;
            }
            $filePath = $baseDir . '/' . $file;
            if ($this->overrideFileExists($file, $overrideDir)) {
                $filePath = $overrideDir . '/' . $file;
            }
            $newFileList[] = $filePath;
        }
        return $newFileList;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function fileMatchesLoaderPrefix(string $file): bool
    {
        $fileParts = explode('.', $file);
        return ($fileParts[0] ?? '') === $this->loaderPrefix;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function overrideFileExists(string $file, string $overrideDir): bool
    {
        return (file_exists($overrideDir . '/' . $file));
    }

    /**
     * @since ZC v1.5.7
     */
    protected function getLoadersFromFilelist(array $fileList): array
    {
        $autoLoadConfig = [];
        foreach ($fileList as $file) {
            require $file;
        }
        return $autoLoadConfig;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function processLoaderListForType(string $type, array $loaderList, $plugin = []): array
    {
        $newList = [];
        foreach ($loaderList as $breakPoint => $loaders) {
            foreach ($loaders as $key => $loader) {
                $loader['loaderType'] = $type;
                $loader['pluginInfo'] = $plugin;
                $newList[$breakPoint][$key] = $loader;
            }
        }

        return $newList;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function mergeAutoLoaders(array $coreLoaders, array $pluginLoaders): array
    {
        foreach ($pluginLoaders as $breakpoint => $pluginLoaderForBreakpoint) {
            if (array_key_exists($breakpoint, $coreLoaders)) {
                $coreLoaders = $this->addPluginLoaderToBreakPoint(
                    $breakpoint,
                    $coreLoaders,
                    $pluginLoaderForBreakpoint
                );
            } else {
                $coreLoaders[$breakpoint] = $pluginLoaderForBreakpoint;
            }
        }
        return $coreLoaders;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function addPluginLoaderToBreakPoint($breakpoint, array $coreLoaders, array $pluginLoaderForBreakpoint): array
    {
        foreach ($pluginLoaderForBreakpoint as $pluginLoader) {
            $coreLoaders[$breakpoint][] = $pluginLoader;
        }
        return $coreLoaders;
    }

    /**
     * @since ZC v1.5.7
     */
    /**
     * The plugin directory $filePath names, or null if it does not name one.
     *
     * Null rather than a fallback directory: substituting the plugin's context root
     * would leave the caller free to find some other file of the same name sitting
     * there and load that instead, which is the silent-wrong-file behaviour this is
     * meant to prevent. A refused entry loads nothing.
     *
     * @since ZC v1.5.7
     */
    protected function findPluginDirectory(string $filePath, string $pluginName): ?string
    {
        $pluginDir = $this->pluginManager->getPluginVersionDirectory($pluginName, $this->installedPlugins);
        if ($pluginDir === null) {
            $this->debugList[] = 'rejected plugin path - no installed directory for plugin - ' . $pluginName;
            return null;
        }
        if (!$this->isPluginRelativePath($filePath)) {
            $this->debugList[] = 'rejected plugin path - not relative to the plugin - ' . $filePath;
            return null;
        }
        return $pluginDir . $this->context . '/' . $filePath;
    }

    /**
     * Whether $path may be appended to a plugin's own directory.
     *
     * An auto_loader addresses files inside its own plugin, in whichever context is
     * loading it, so its classPath is a plain relative fragment.
     * An absolute path cannot be honoured. The plugin's tree is the only thing it may name.
     *
     * Both path-separator conventions are rejected regardless of platform.
     * A validator can afford to be conservative where a path comparison cannot:
     * there is no legitimate auto_loader path that this turns away,
     * and being wrong about which convention applies would be the only way to let one through.
     *
     * To reach files outside its own tree a plugin should use the PSR-4 namespaces
     * registered for it at bootstrap: Zencart\Plugins\Admin\<Key> and
     * Zencart\Plugins\Catalog\<Key> are both available from either context.
     *
     * @since ZC v3.0.0
     */
    protected function isPluginRelativePath(string $path): bool
    {
        if ($path === '') {
            return true;
        }
        if (str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return false;
        }
        if (preg_match('#^[A-Za-z]:[/\\\\]#', $path) === 1) {
            return false; // Windows drive-rooted, e.g. C:\store or C:/store
        }
        return preg_match('#(^|[/\\\\])\.\.([/\\\\]|$)#', $path) !== 1;
    }
}
