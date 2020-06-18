<?php
/**
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jun 09 Modified in v1.5.7 $
 */

namespace Zencart\InitSystem;

class InitSystem
{

    protected $context;
    protected $loaderPrefix;
    protected $fileSystem;
    protected $pluginManager;

    public function __construct($context, $loaderPrefix, $fileSystem, $pluginManager, $installedPlugins)
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

    public function loadAutoLoaders()
    {
        $coreLoaderList = $this->loadAutoLoadersFromSystem('core', DIR_WS_INCLUDES . 'auto_loaders');
        $pluginLoaderList = $this->loadPluginAutoLoaders('plugin');
        $mainLoaderList = $this->mergeAutoLoaders($coreLoaderList, $pluginLoaderList);
        return $mainLoaderList;
    }

    public function setDebug($debug = false)
    {
        $this->debug = $debug;
    }

    public function processLoaderList($loaderList)
    {
        ksort($loaderList);
        foreach ($loaderList as $actionPoint => $entries) {
            $this->debugList[] = '##################################################################';
            $this->debugList[] = 'Action Point - ' . $actionPoint;
            $this->processActionPointEntries($entries);
        }
        if ($this->debug) {
            print_r($this->debugList);
        }
        return $this->actionList;
    }

    protected function processActionPointEntries($entries)
    {
        foreach ($entries as $entry) {
            if (!isset($entry['forceLoad'])) $entry['forceLoad'] = false;
            $this->processActionPointEntry($entry);
            $this->debugList[] = '=================================================================';
        }
    }

    protected function processActionPointEntry($entry)
    {
        $autoTypeMethod = 'processAutoType' . ucfirst($entry['autoType']);
        $this->debugList[] = 'Auto Type Method - ' . $autoTypeMethod;
        if (!method_exists($this, $autoTypeMethod)) return;
        $this->$autoTypeMethod($entry);
    }

    protected function processAutoTypeClass($entry)
    {
        $filePath = DIR_FS_CATALOG . DIR_WS_CLASSES;
        if (isset($entry['classPath'])) {
            $filePath = $entry['classPath'];
        }
        if ($entry['loaderType'] == 'plugin') {
            $filePath = $this->findPluginDirectory($filePath, $entry['pluginInfo']['unique_key']);
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
    protected function processAutoTypeClassInstantiate($entry)
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

    protected function processAutoTypeObjectMethod($entry)
    {
        $objectName = $entry['objectName'];
        $methodName = $entry['methodName'];
        $this->debugList[] = 'processing object method - ' . $objectName . ' => ' . $methodName;
        $this->actionList[] = ['type' => 'objectMethod', 'object' => $objectName, 'method' => $methodName];
    }

    protected function processAutoTypeRequire($entry)
    {
        $filePath = $entry['loadFile'];
        $this->debugList[] = 'processing require - ' . $entry['loadFile'];
        if ($entry['loaderType'] == 'plugin') {

        }
        $result = 'FAILED';
        if (file_exists($filePath)) {
            $result = 'SUCCESS';
            $this->actionList[] = ['type' => 'require', 'filePath' => $filePath, 'forceLoad' => $entry['forceLoad']];
        }
        $this->debugList[] = 'loading require - ' . $filePath . ' - ' . $result;

    }

    protected function processAutoTypeInclude($entry)
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

    protected function processAutoTypeInit_script($entry)
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

    protected function loadAutoLoadersFromSystem($loaderType, $rootDir, $plugin = [])
    {
        $fileList = $this->fileSystem->listFilesFromDirectory($rootDir, '~^[^\._].*\.php$~i');
        $fileList = $this->processForOverrides($fileList, $rootDir);
        $loaderList = $this->getLoadersFromFileList($fileList);
        $loaderList = $this->processLoaderListForType($loaderType, $loaderList, $plugin);
        return $loaderList;
    }

    protected function loadPluginAutoLoaders($loaderType)
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

    protected function processForOverrides($fileList, $rootDir)
    {
        $newFileList = [];
        $baseDir = $rootDir;
        $overrideDir = $baseDir . '/overrides';
        foreach ($fileList as $file) {
            if (!$this->fileMatchesLoaderPrefix($file)) continue;
            $filePath = $baseDir . '/' . $file;
            if ($this->overrideFileExists($file, $overrideDir)) $filePath = $overrideDir . '/' . $file;
            $newFileList[] = $filePath;
        }
        return $newFileList;
    }

    protected function fileMatchesLoaderPrefix($file)
    {
        $fileParts = explode('.', $file);
        if ($fileParts[0] !== $this->loaderPrefix) return false;
        return true;
    }

    protected function overrideFileExists($file, $overrideDir)
    {
        if (file_exists($overrideDir . '/' . $file)) return true;
        return false;
    }

    protected function getLoadersFromFilelist($fileList)
    {
        foreach ($fileList as $file) {
            require($file);
        }
        return $autoLoadConfig;
    }

    protected function processLoaderListForType($type, $loaderList, $plugin = [])
    {
        $newList = [];
        if (!is_array($loaderList)) return [];
        foreach ($loaderList as $breakPoint => $loaders) {
            foreach ($loaders as $key => $loader) {
                $loader['loaderType'] = $type;
                $loader['pluginInfo'] = $plugin;
                $newList[$breakPoint][$key] = $loader;
            }
        }

        return $newList;
    }

    protected function mergeAutoLoaders($coreLoaders, $pluginLoaders)
    {
        foreach ($pluginLoaders as $breakpoint => $pluginLoaderForBreakpoint) {
            if (array_key_exists($breakpoint, $coreLoaders)) {
                $coreLoaders = $this->addPluginLoaderToBreakPoint($breakpoint, $coreLoaders,
                                                                  $pluginLoaderForBreakpoint);
            } else {
                $coreLoaders[$breakpoint] = $pluginLoaderForBreakpoint;
            }
        }
        return $coreLoaders;
    }

    protected function addPluginLoaderToBreakPoint($breakpoint, $coreLoaders, $pluginLoaderForBreakpoint)
    {
        foreach ($pluginLoaderForBreakpoint as $pluginLoader) {
            $coreLoaders[$breakpoint][] = $pluginLoader;
        }
        return $coreLoaders;
    }

    protected function findPluginDirectory($filePath, $pluginName)
    {
        $relDir = $this->fileSystem->getRelativeDir($filePath);
        $pluginDir = $this->pluginManager->getPluginVersionDirectory($pluginName, $this->installedPlugins);
        $actualDir = $pluginDir . $this->context . '/' . $relDir;
        return $actualDir;
    }

}
