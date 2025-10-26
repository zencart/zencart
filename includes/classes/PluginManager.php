<?php
/**
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jun 18 Modified in v2.1.0-alpha1 $
 */

namespace Zencart\PluginManager;

use Zencart\PluginSupport\PluginStatus;

/**
 * @since ZC v1.5.7
 */
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

    /**
     * @since ZC v1.5.7
     */
    public function inspectAndUpdate()
    {
        $pluginsFromFilesystem = $this->getPluginsFromFileSystem();
        $this->updateDbPlugins($pluginsFromFilesystem);
    }

    /**
     * @since ZC v1.5.7
     */
    public function getInstalledPlugins()
    {
        $results = $this->pluginControl->where(['status' => PluginStatus::ENABLED])->orderBy('name')->orderBy('unique_key')->get();
        $pluginList = [];
        foreach ($results as $result) {
            $pluginList[$result['unique_key']] = $result;
        }
        return $pluginList;
    }

    /**
     * @since ZC v1.5.7
     */
    public function getPluginVersionDirectory($pluginName, $installedPlugins)
    {
        if (!array_key_exists($pluginName, $installedPlugins)) {
            return null;
        }

        return DIR_FS_CATALOG . 'zc_plugins/' . $pluginName . '/' . $installedPlugins[$pluginName]['version'] . '/';
    }

    /**
     * @since ZC v1.5.7
     */
    public function isUpgradeAvailable($uniqueKey, $currentVersion)
    {
        if (empty($currentVersion)) {
            return false;
        }
        $versionList = $this->getVersionsForUpgrade($uniqueKey, $currentVersion);
        return count($versionList);
    }

    /**
     * @since ZC v1.5.7
     */
    public function getVersionsForUpgrade($uniqueKey, $currentVersion)
    {
        if (empty($currentVersion)) {
            return [];
        }
        $versions = $this->getPluginVersions($uniqueKey);
        $versionList = [];
        foreach ($versions as $version) {
            if (version_compare($version['version'], $currentVersion, '<=')) {
                continue;
            }
            $versionList[$version['version']] = $version['version'];
        }
        return $versionList;
    }

    /**
     * @since ZC v2.0.0
     */
    public function isNewDownloadAvailable($pluginId, $currentVersion)
    {
        if (empty($pluginId)) {
            return false;
        }
        $isAvailable = plugin_version_check_for_updates($pluginId, $currentVersion);
        return $isAvailable;
    }

    /**
     * @since ZC v1.5.7
     */
    public function getPluginsAfterCheckingForNewVersionsOnline()
    {
        $plugins = $this->getPluginsFromDb();

        // new array for reverse-lookup after getting results back
        $pluginsById = [];

        $ids_csv = '';
        foreach ($plugins as $plugin) {
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

        foreach ($results as $result) {
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

    /**
     * @since ZC v1.5.7
     */
    protected function getLatestPluginVersionsOnline($plugin_ids_csv = 0)
    {
        if (empty(trim($plugin_ids_csv, ','))) {
            return false;
        }

        $versionServer = new \VersionServer();
        $data = json_decode($versionServer->getPluginVersion($plugin_ids_csv), true);

        if (null === $data || isset($data['error'])) {
            if (LOG_PLUGIN_VERSIONCHECK_FAILURES) {
                error_log('CURL error checking plugin versions (in batch): ' . print_r(!empty($data) ? $data : 'null', true));
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

    /**
     * @since ZC v1.5.7
     */
    protected function getPluginVersions($uniqueKey)
    {
        return $this->pluginControlVersion->where(['unique_key' => $uniqueKey])->get();
    }

    /**
     * @since ZC v1.5.7
     */
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

    /**
     * @since ZC v1.5.7
     */
    protected function getPluginVersionDirectories($parent)
    {
        $versionList = [];
        $dir = new \DirectoryIterator($parent->getPathName());
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            if (!file_exists($fileinfo->getPathname() . '/manifest.php')) {
                continue; //@todo consider throwing exception/trigger_error here
            }
            $manifest = require $fileinfo->getPathname() . '/manifest.php';
            $versionList[$fileinfo->getFilename()] = $manifest;
            if ($_SESSION['languages_code'] !== 'en') {
                $this->loadPluginLanguageConstants($fileinfo->getPathname());
            }
        }
        return $versionList;
    }

    /**
     * @since ZC v1.5.7
     */
    public function getPluginsFromDb()
    {
        $pluginList = [];
        $results = $this->pluginControl->all();
        foreach ($results as $result) {
            $pluginList[$result['unique_key']] = $result;
        }
        return $pluginList;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function updateDbPlugins($pluginsFromFilesystem)
    {
        $this->updatePluginControl($pluginsFromFilesystem);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function updatePluginControl($pluginsFromFilesystem)
    {
        // Mark all existing plugins as not found on filesystem
        $this->pluginControl::query()->update(['infs' => 0]);
        $this->pluginControlVersion::query()->update(['infs' => 0]);

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
                    'status' => PluginStatus::NOT_INSTALLED,
                    'author' => $plugin[$pluginVersion]['pluginAuthor'],
                    'version' => '',
                    'zc_versions' => '',
                    'infs' => 1,
                    'zc_contrib_id' => $plugin[$pluginVersion]['pluginId'],
                ];
        }
        // Insert new, and update existing, plugins
        $this->pluginControl::query()->upsert(
            $insertValues,
            ['id'],
            ['name', 'description', 'infs', 'author', 'zc_contrib_id']
        );
        $this->pluginControlVersion::query()->upsert(
            $versionInsertValues,
            ['id'],
            ['infs' => 1]
        );
        // Remove any plugins no longer found on filesystem
        $this->pluginControl->where(['infs' => 0])->delete();
        $this->pluginControlVersion->where(['infs' => 0])->delete();
    }

    /**
     * @since ZC v1.5.8
     */
    protected function processUpdatePluginControlVersions($uniqueKey, $pluginsFromFilesystem, $versionInsertValues)
    {
        $currentPlugin = $pluginsFromFilesystem[$uniqueKey];
        foreach ($currentPlugin as $version => $versionInfo) {
            if ($version === 'versions') {
                continue;
            }
            $versionInsertValues[] = [
                'unique_key' => $uniqueKey,
                'author' => $versionInfo['pluginAuthor'],
                'version' => $version,
                'zc_versions' => json_encode($versionInfo['zcVersions']),
                'infs' => 1,
            ];
        }
        return $versionInsertValues;
    }

    /**
     * @since ZC v1.5.7
     */
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

    /**
     * @since ZC v1.5.7
     */
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

    /**
     * @since ZC v1.5.7
     */
    public function getPluginVersionsToClean($uniqueKey, $version)
    {
        $versions = $this->getPluginVersionsForPlugin($uniqueKey);
        unset($versions[$version]);
        return $versions;
    }

    /**
     * @since ZC v1.5.7
     */
    public function hasPluginVersionsToClean($uniqueKey, $version)
    {
        return count($this->getPluginVersionsToClean($uniqueKey, $version));
    }

    /**
     * @since ZC v1.5.8
     */
    public function getPluginControl()
    {
        return $this->pluginControl;
    }

    /**
     * @since ZC v2.2.0
     */
    protected function loadPluginLanguageConstants(string $pluginpath): void // Load plugins names and description when they are not installed or de-activated
    {
        $pluginpath = str_replace('\\', '/', $pluginpath);
        $filePath = [];
        foreach ($this->getInstalledPlugins() as $plugin) { // make an array of all installed plugins paths
            $filePath[$plugin['unique_key']] = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'];
        }
        if (!in_array($pluginpath, $filePath)) {
            $explodedpath = explode('/', $pluginpath);
            $pluginuniquekey = strtoupper($explodedpath[count($explodedpath) - 2]); // retrieve plugin's unique key
            $pluginconstantspath = $pluginpath . '/admin/includes/languages/' . $_SESSION['language'] . '/extra_definitions/lang.menu.php'; // The language constant file 'lang.menu.php' must be in this folder
            if (is_file($pluginconstantspath)) {
                $pluginsconstants = require_once $pluginconstantspath; // Load language override constants definitions
                $pluginnameconstant = 'ADMIN_PLUGIN_MANAGER_NAME_FOR_' . $pluginuniquekey;
                $plugindescriptionconstant = 'ADMIN_PLUGIN_MANAGER_DESCRIPTION_FOR_' . $pluginuniquekey;
                if (!defined($pluginnameconstant) && array_key_exists($pluginnameconstant, $pluginsconstants)) {
                    define($pluginnameconstant, $pluginsconstants[$pluginnameconstant]);
                }
                if (!defined($plugindescriptionconstant) && array_key_exists($plugindescriptionconstant, $pluginsconstants)) {
                    define($plugindescriptionconstant, $pluginsconstants[$plugindescriptionconstant]);
                }
            }
        }
    }
}
