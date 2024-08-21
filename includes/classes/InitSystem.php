<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jul 17 Modified in v2.1.0-alpha1 $
 */

namespace Zencart\InitSystem;

class InitSystem
{
    private $installedPlugins;
    private bool $debug;
    private array $debugList;
    private array $actionList;

    private string $context;
    private string $loaderPrefix;
    private $fileSystem;
    private $pluginManager;

    public function __construct(string $context, string $loaderPrefix, $fileSystem, $pluginManager, $installedPlugins)
    {
        $this->context = $context;
        $this->loaderPrefix = $loaderPrefix;
        $this->fileSystem = $fileSystem;
        $this->pluginManager = $pluginManager;
        $this->installedPlugins = $installedPlugins;
        $this->debug = false;
        $this->debugList = [];
        $this->actionList = [];
    }

    public function loadAutoLoaders(): array
    {
        $coreLoaderList = $this->loadAutoLoadersFromSystem('core', DIR_WS_INCLUDES . 'auto_loaders');
        $pluginLoaderList = $this->loadPluginAutoLoaders('plugin');
        $mainLoaderList = $this->mergeAutoLoaders($coreLoaderList, $pluginLoaderList);
        return $mainLoaderList;
    }

    public function setDebug(bool $debug = false): void
    {
        $this->debug = $debug;
    }

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

    protected function processActionPointEntry(array $entry): void
    {
        $autoTypeMethod = 'processAutoType' . ucfirst($entry['autoType']);
        $this->debugList[] = 'Auto Type Method - ' . $autoTypeMethod;
        if (!method_exists($this, $autoTypeMethod)) {
            return;
        }
        $this->$autoTypeMethod($entry);
    }

    protected function processAutoTypeClass(array $entry): void
    {
        $filePath = DIR_FS_CATALOG . DIR_WS_CLASSES;
        if (isset($entry['classPath'])) {
            $filePath = $entry['classPath'];
        }
        if ($entry['loaderType'] === 'plugin') {
            $filePath = $this->findPluginDirectory($entry['classPath'] ?? DIR_WS_CLASSES, $entry['pluginInfo']['unique_key']);
        }
        $this->debugList[] = 'processing class - ' . $filePath  . $entry['loadFile'];
        $result = 'FAILED';
        if (file_exists($filePath . $entry['loadFile'])) {
            $result = 'SUCCESS';
            $this->actionList[] = ['type' => 'include', 'filePath' => $filePath . $entry['loadFile'], 'forceLoad' => $entry['forceLoad']];
        }
        $this->debugList[] = 'loading class - ' . $filePath . $entry['loadFile'] . ' - ' . $result;
    }

    /**
     * @param $entry
     * @todo should deprecate session bound classes.
     */
    protected function processAutoTypeClassInstantiate(array $entry): void
    {
        $objectName = $entry['objectName'];
        $className = $entry['className'];
        $this->debugList[] = 'processing class instantiate - class = ' . $className . ' object name = ' . $objectName;
        $classSession = (isset($entry['classSession']) && $entry['classSession'] === true);
        $checkInstantiated = (isset($entry['checkInstantiated']) && $entry['checkInstantiated'] === true);
        if (!$classSession) {
            $this->debugList[] = 'instantiating normal class - ' . $className . ' as ' . $objectName;
            $this->actionList[] = ['type' => 'class', 'object' => $objectName, 'class' => $className];
            return;
        }
        $this->debugList[] = 'instantiating session bound class - ' . $className . ' as ' . $objectName;
        $this->actionList[] = ['type' => 'sessionClass', 'object' => $objectName, 'class' => $className, 'checkInstantiated' => $checkInstantiated];
        return;
    }

    protected function processAutoTypeObjectMethod(array $entry): void
    {
        $objectName = $entry['objectName'];
        $methodName = $entry['methodName'];
        $this->debugList[] = 'processing object method - ' . $objectName . ' => ' . $methodName;
        $this->actionList[] = ['type' => 'objectMethod', 'object' => $objectName, 'method' => $methodName];
    }

    protected function processAutoTypeRequire(array $entry): void
    {
        $filePath = $entry['loadFile'];
        $this->debugList[] = 'processing require - ' . $entry['loadFile'];
        if ($entry['loaderType'] === 'plugin') {

        }
        $result = 'FAILED';
        if (file_exists($filePath)) {
            $result = 'SUCCESS';
            $this->actionList[] = ['type' => 'require', 'filePath' => $filePath, 'forceLoad' => $entry['forceLoad']];
        }
        $this->debugList[] = 'loading require - ' . $filePath . ' - ' . $result;
    }

    protected function processAutoTypeInclude(array $entry): void
    {
        $filePath = $entry['loadFile'];
        $this->debugList[] = 'processing include - ' . $entry['loadFile'];
        if ($entry['loaderType'] == 'plugin') {

        }
        $result = 'FAILED';
        if (file_exists($filePath)) {
            $result = 'SUCCESS';
            $this->actionList[] = ['type' => 'include', 'filePath' => $filePath, 'forceLoad' => $entry['forceLoad']];
        }
        $this->debugList[] = 'loading include - ' . $filePath . ' - ' . $result;
    }

    protected function processAutoTypeInit_script(array $entry): void
    {
        $actualDir = DIR_WS_INCLUDES . 'init_includes/';
        if ($entry['loaderType'] == 'plugin') {
            $actualDir = $this->findPluginDirectory($actualDir, $entry['pluginInfo']['unique_key']);
        }
        if (file_exists($actualDir . 'overrides/' . $entry['loadFile'])) {
            $actualDir = $actualDir . 'overrides/';
        }
        $this->actionList[] = ['type' => 'require', 'filePath' => $actualDir . $entry['loadFile'], 'forceLoad' => $entry['forceLoad']];
        $this->debugList[] = 'loading init_script - ' . $actualDir . $entry['loadFile'];

    }

    protected function loadAutoLoadersFromSystem(string $loaderType, string $rootDir, $plugin = []): array
    {
        $fileList = $this->fileSystem->listFilesFromDirectoryAlphaSorted($rootDir);
        $fileList = $this->processForOverrides($loaderType, $fileList, $rootDir);
        $loaderList = $this->getLoadersFromFileList($fileList);
        $loaderList = $this->processLoaderListForType($loaderType, $loaderList, $plugin);
        return $loaderList;
    }

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

    protected function processForOverrides(string $loaderType, array $fileList, string $rootDir): array
    {
        $newFileList = [];
        $baseDir = $rootDir;
        $overrideDir = $baseDir . '/overrides';
        $core_loader_file = '';
        if ($loaderType === 'core') {
            $core_loader_file = $this->loaderPrefix . '.core.php';
            if ($this->overrideFileExists($core_loader_file, $overrideDir)) {
                $newFileList[] = $orderrideDir . '/' . $core_loader_file;
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

    protected function fileMatchesLoaderPrefix(string $file): bool
    {
        $fileParts = explode('.', $file);
        if (($fileParts[0] ?? '') !== $this->loaderPrefix) {
            return false;
        }
        return true;
    }

    protected function overrideFileExists(string $file, string $overrideDir): bool
    {
        return (file_exists($overrideDir . '/' . $file));
    }

    protected function getLoadersFromFilelist(array $fileList): array
    {
        $autoLoadConfig = [];
        foreach ($fileList as $file) {
            require $file;
        }
        return $autoLoadConfig;
    }

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

    protected function addPluginLoaderToBreakPoint($breakpoint, array $coreLoaders, array $pluginLoaderForBreakpoint): array
    {
        foreach ($pluginLoaderForBreakpoint as $pluginLoader) {
            $coreLoaders[$breakpoint][] = $pluginLoader;
        }
        return $coreLoaders;
    }

    protected function findPluginDirectory(string $filePath, string $pluginName): string
    {
        $relDir = $this->fileSystem->getRelativeDir($filePath);
        $pluginDir = $this->pluginManager->getPluginVersionDirectory($pluginName, $this->installedPlugins);
        $actualDir = $pluginDir . $this->context . '/' . $relDir;
        return $actualDir;
    }
}
