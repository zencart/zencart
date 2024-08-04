<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jun 18 Modified in v2.1.0-alpha1 $
 */

namespace Zencart\PluginManager;

class PluginManager
{
    private
        $pluginControl,
        $pluginControlVersion;

    public function __construct($pluginControl, $pluginControlVersion)
    {
        $this->pluginControl = $pluginControl;
        $this->pluginControlVersion = $pluginControlVersion;
    }

    public function inspectAndUpdate()
    {
        $pluginsFromFilesystem = $this->getPluginsFromFileSystem();
        $this->updateDbPlugins($pluginsFromFilesystem);
    }

    public function getInstalledPlugins()
    {
        $results = $this->pluginControl->where(['status' => 1])->orderBy('name')->orderBy('unique_key')->get();
        $pluginList = [];
        foreach ($results as $result) {
            $pluginList[$result['unique_key']] = $result;
        }
        return $pluginList;
    }

    public function getPluginVersionDirectory($pluginName, $installedPlugins)
    {
        if (!array_key_exists($pluginName, $installedPlugins)) {
            return null;
        }
        $filePath = DIR_FS_CATALOG . 'zc_plugins/' . $pluginName . '/' . $installedPlugins[$pluginName]['version'] . '/';
        return $filePath;
    }

    public function isUpgradeAvailable($uniqueKey, $currentVersion)
    {
        if (empty($currentVersion)) {
            return false;
        }
        $versionList = $this->getVersionsForUpgrade($uniqueKey, $currentVersion);
        return count($versionList);
    }

    public function getVersionsForUpgrade($uniqueKey, $currentVersion)
    {
        if (empty($currentVersion)) {
            return [];
        }
        $versions = $this->getPluginVersions($uniqueKey);
        $versionList = [];
        foreach ($versions as $version) {
            if (version_compare($version['version'], $currentVersion, '<=')) continue;
            $versionList[$version['version']] = $version['version'];
        }
        return $versionList;
    }

    public function isNewDownloadAvailable($pluginId, $currentVersion)
    {
        $isAvailable = plugin_version_check_for_updates($pluginId, $currentVersion);
        return $isAvailable;
    }
    public function getPluginsAfterCheckingForNewVersionsOnline()
    {
        $plugins = $this->getPluginsFromDb();

        // new array for reverse-lookup after getting results back
        $pluginsById = [];

        $ids_csv = '';
        foreach($plugins as $plugin) {
            $pluginsById[$plugin['zc_contrib_id']] = $plugin;
            $ids_csv .= (int)trim($plugin['zc_contrib_id']) . ',';
        }

        $results = $this->getLatestPluginVersionsOnline($ids_csv);

        // if no results or invalid format, abort
        // @TODO - is this the right return type? or should we return the unaltered $plugins array?
        if (empty($results)) {
            return false;
        }

        // make sure $results is the actual array we want to iterate over, and not a sub-array
        if (is_array($results) && !isset($results[0]['id']) && isset($results[0][0]['id'])) {
            $results = $results[0];
        }

        if (!isset($results[0]['id'])) {
            return false; // @TODO or return original $plugins array?
        }

        $present_zc_version = 'v' . preg_replace('/[^0-9.]/', '', zen_get_zcversion());

        foreach($results as $result) {
            $unique_key = $pluginsById[$result['id']]['unique_key'];

            if (version_compare($pluginsById[$result['id']]['version'], $result['latest_plugin_version'], '<')) {
                $plugins[$unique_key]['new_online_version_exists'] = true;
                $plugins[$unique_key]['latest_plugin_version'] = $result['latest_plugin_version'];
                $plugins[$unique_key]['zcversions'] = $result['zcversions'];

                if (in_array($present_zc_version, $result['zcversions'], $strict = false)) {
                    $plugins[$unique_key]['new_plugin_exists_for_this_zc_version'] = true;
                }
            }
        }

        return $plugins;
    }

    protected function getLatestPluginVersionsOnline($plugin_ids_csv = 0)
    {
        if (empty(trim($plugin_ids_csv, ','))) {
            return false;
        }

        $versionServer = new \VersionServer();
        $data = json_decode($versionServer->getPluginVersion($plugin_ids_csv), true);

        if (null === $data || isset($data['error'])) {
            if (LOG_PLUGIN_VERSIONCHECK_FAILURES) {
                error_log('CURL error checking plugin versions (in batch): ' . print_r(!empty($data)? $data : 'null', true));
            }
            return false;
        }

        if (!is_array($data)) {
            try {
                $data = json_decode($data, true);
            } catch (\Exception $exception) {
                if (LOG_PLUGIN_VERSIONCHECK_FAILURES) {
                    error_log('CURL error checking plugin versions (in batch): ' . print_r(!empty($data) ? $data : 'null', true));
                }
                return false;
            }
        }

        return $data;
    }

    protected function getPluginVersions($uniqueKey)
    {
        $result = $this->pluginControlVersion->where(['unique_key' => $uniqueKey])->get();
        return $result;
    }

    protected function getPluginsFromFileSystem()
    {
        $pluginDir = DIR_FS_CATALOG . 'zc_plugins';
        $pluginList = [];
        if (!is_dir($pluginDir)) {
            return $pluginList;
        }
        $dir = new \DirectoryIterator($pluginDir);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            $versionInfo = $this->getPluginVersionDirectories($fileinfo);
            if (count($versionInfo) === 0) {
                continue;
            }
            $pluginList = $this->mergeInVersionInfo($pluginList, $fileinfo->getFilename(), $versionInfo);
        }
        return $pluginList;
    }

    protected function getPluginVersionDirectories($parent)
    {
        $versionList = [];
        $dir = new \DirectoryIterator($parent->getPathName());
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            if (!file_exists($fileinfo->getPathname() . '/manifest.php')) {
                continue; //@todo consider throwing exception/triger_error here
            }
            $manifest = require $fileinfo->getPathname() . '/manifest.php';
            $versionList[$fileinfo->getFilename()] = $manifest;
        }
        return $versionList;
    }

    public function getPluginsFromDb()
    {
        $pluginList = [];
        $results = $this->pluginControl->all();
        foreach ($results as $result) {
            $pluginList[$result['unique_key']] = $result;
        }
        return $pluginList;
    }

    protected function updateDbPlugins($pluginsFromFilesystem)
    {
        $this->updatePluginControl($pluginsFromFilesystem);
    }

    protected function updatePluginControl($pluginsFromFilesystem)
    {
        $this->pluginControl->query()->update(['infs' => 0]);
        $this->pluginControlVersion->query()->update(['infs' => 0]);
        $insertValues = [];
        $versionInsertValues = [];
        foreach ($pluginsFromFilesystem as $uniqueKey => $plugin) {
            $pluginVersion = $plugin['versions'][0];
            $versionInsertValues = $this->processUpdatePluginControlVersions($uniqueKey, $pluginsFromFilesystem, $versionInsertValues);
            $insertValues[] =
                [
                    'unique_key' => $uniqueKey,
                    'name' => $plugin[$pluginVersion]['pluginName'],
                    'description' => $plugin[$pluginVersion]['pluginDescription'],
                    'type' => '',
                    'status' => 0,
                    'author' => $plugin[$pluginVersion]['pluginAuthor'],
                    'version' => '',
                    'zc_versions' => '',
                    'infs' => 1,
                    'zc_contrib_id' => $plugin[$pluginVersion]['pluginId']
                ];

        }
        $this->pluginControl->upsert(
            $insertValues,
            ['id'],
            ['infs']
        );
        $this->pluginControlVersion->upsert(
            $versionInsertValues,
            ['id'],
            ['infs' => 1]
        );
        $this->pluginControl->where(['infs' => 0])->delete();
        $this->pluginControlVersion->where(['infs' => 0])->delete();
    }

    protected function processUpdatePluginControlVersions($uniqueKey, $pluginsFromFilesystem, $versionInsertValues)
    {
        $currentPlugin = $pluginsFromFilesystem[$uniqueKey];
        foreach ($currentPlugin as $version => $versionInfo) {
            if ($version == 'versions') {
                continue;
            }
            $versionInsertValues[] = [
                'unique_key' => $uniqueKey,
                'author' => $versionInfo['pluginAuthor'],
                'version' => $version,
                'zc_versions' => json_encode($versionInfo['zcVersions']),
                'infs' => 1
            ];
        }
        return $versionInsertValues;
    }

    protected function mergeInVersionInfo($pluginList, $uniqueKey, $versionInfo)
    {
        $versionList = [];
        foreach ($versionInfo as $version => $detail) {
            $pluginList[$uniqueKey][$version] = $detail;
            $versionList[] = $version;
        }
        usort($versionList, 'version_compare');
        $versionList = array_reverse($versionList);
        $pluginList[$uniqueKey]['versions'] = $versionList;
        return $pluginList;
    }

    public function getPluginVersionsForPlugin($uniqueKey)
    {
        $results = $this->pluginControlVersion->where(['unique_key' => $uniqueKey])->get();
        $versions = [];
        foreach ($results as $result) {
            $versions[$result['version']] = $result;
        }
        ksort($versions);
        $versions = array_reverse($versions);
        return $versions;
    }

    public function getPluginVersionsToClean($uniqueKey, $version)
    {
        $versions = $this->getPluginVersionsForPlugin($uniqueKey);
        unset($versions[$version]);
        return $versions;
    }

    public function hasPluginVersionsToClean($uniqueKey, $version)
    {
        return count($this->getPluginVersionsToClean($uniqueKey, $version));
    }

    public function getPluginControl()
    {
        return $this->pluginControl;
    }
}
