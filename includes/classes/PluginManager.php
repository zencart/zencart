<?php
/**
 *
 * @package classes
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */

namespace Zencart\PluginManager;

class PluginManager
{

    public function __construct($dbConn)
    {
        $this->dbConn = $dbConn;
    }

    public function inspectAndUpdate()
    {
        $pluginsFromFilesystem = $this->getPluginsFromFileSystem();
        $pluginsFromDb = $this->getPluginsFromDb();
        $newPlugins = $this->getNewPlugins($pluginsFromDb, $pluginsFromFilesystem);

        $this->updateDbForNewPlugins($pluginsFromFilesystem, $newPlugins);

    }

    public function getInstalledPlugins()
    {
        $sql = "SELECT * FROM " . TABLE_PLUGIN_CONTROL . " WHERE status = 1";
        $results = $this->dbConn->execute($sql, false, true, 150);
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

    protected function getPluginsFromFileSystem()
    {
        $pluginDir = DIR_FS_CATALOG . 'zc_plugins';
        $pluginList = [];
        $dir = new \DirectoryIterator($pluginDir);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDot() || !$fileinfo->isDir()) {
                continue;
            }
            $versionInfo = $this->getPluginVersionDirectories($fileinfo);
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
            $manifest = require $fileinfo->getPathname() . '/manifest.php';
            $versionList[$fileinfo->getFilename()] = $manifest;
        }
        return $versionList;
    }

    protected function getPluginsFromDb()
    {
        $pluginList = [];
        $sql = "SELECT * FROM " . TABLE_PLUGIN_CONTROL;
        $results = $this->dbConn->execute($sql);
        foreach ($results as $result) {
            $pluginList[$result['unique_key']] = $result;
        }
        return $pluginList;
    }

    protected function getNewPlugins($dbPlugins, $fsPlugins)
    {
        $newPlugins = [];
        foreach ($fsPlugins as $uniquekey => $fsPlugin) {
            if (key_exists($uniquekey, $dbPlugins)) {
                continue;
            }
            $newPlugins[] = $uniquekey;
        }
        return $newPlugins;
    }

    protected function updateDbForNewPlugins($pluginsFromFilesystem, $newPlugins)
    {
        if (count($newPlugins) === 0) {
            return;
        }
// @todo validate plugin entries here
        $this->updateNewPluginControl($pluginsFromFilesystem, $newPlugins);
        $this->updateNewPluginControlVersions($pluginsFromFilesystem, $newPlugins);
    }

    protected function updateNewPluginControl($pluginsFromFilesystem, $newPlugins)
    {
        $sqlPluginControl = "INSERT INTO " . TABLE_PLUGIN_CONTROL . " 
        (unique_key, name, description, type, status, author, version, zc_versions) 
        VALUES ";

        foreach ($newPlugins as $uniqueKey) {
            $currentPlugin = $pluginsFromFilesystem[$uniqueKey];
            $pluginVersion = $currentPlugin['versions'][0];
            $sqlPartial = "(:unique_key:, :name:, :description:, '', 0, :author:, '', ''),";
            $sqlPartial = $this->dbConn->bindVars($sqlPartial, ':unique_key:', $uniqueKey, 'string');
            $sqlPartial = $this->dbConn->bindVars($sqlPartial, ':name:', $currentPlugin[$pluginVersion]['pluginName'],
                                                  'string');
            $sqlPartial = $this->dbConn->bindVars($sqlPartial, ':description:', $currentPlugin[$pluginVersion]['pluginDescription'],
                                                  'string');
            $sqlPartial = $this->dbConn->bindVars($sqlPartial, ':author:', $currentPlugin[$pluginVersion]['pluginAuthor'], 'string');
            $sqlPluginControl .= $sqlPartial;
        }
        $sqlPluginControl = rtrim($sqlPluginControl, ',');
        $this->dbConn->execute($sqlPluginControl);
    }

    protected function updateNewPluginControlVersions($pluginsFromFilesystem, $newPlugins)
    {
        $sqlPluginVersion = "INSERT INTO " . TABLE_PLUGIN_CONTROL_VERSIONS . "
        (unique_key, author, version, zc_versions) VALUES ";

        foreach ($newPlugins as $uniqueKey) {
            $sqlPluginVersion .= $this->processUpdateNewVersions($uniqueKey, $pluginsFromFilesystem);
        }
        $sqlPluginVersion = rtrim($sqlPluginVersion, ',');
        $this->dbConn->execute($sqlPluginVersion);
    }

    protected function processUpdateNewVersions($uniqueKey, $pluginsFromFilesystem)
    {
        $currentPlugin = $pluginsFromFilesystem[$uniqueKey];
        $extraSql = '';
        foreach ($currentPlugin as $version => $versionInfo) {
            if ($version == 'versions') {
                continue;
            }
            $extraSql .= "(:unique_key:, :author:, :version:, :zc_versions:),";
            $extraSql = $this->dbConn->bindVars($extraSql, ':unique_key:', $uniqueKey, 'string');
            $extraSql = $this->dbConn->bindVars($extraSql, ':author:', $versionInfo['pluginAuthor'], 'string');
            $extraSql = $this->dbConn->bindVars($extraSql, ':version:', $version, 'string');
            $extraSql = $this->dbConn->bindVars($extraSql, ':zc_versions:', json_encode($versionInfo['zcVersions']),
                                                'string');
        }
        return $extraSql;
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
        $sql = "SELECT * FROM " . TABLE_PLUGIN_CONTROL_VERSIONS . " WHERE unique_key = :uniqueKey:";
        $sql = $this->dbConn->bindVars($sql, ':uniqueKey:', $uniqueKey, 'string');
        $results = $this->dbConn->execute($sql, false, true, 150);
        $versions = [];
        foreach ($results as $result) {
            $versions[$result['version']] = $result;
        }
        ksort($versions);
        $versions = array_reverse($versions);
        return $versions;
    }


}