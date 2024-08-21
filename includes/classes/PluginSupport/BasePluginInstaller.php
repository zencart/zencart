<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 May 22 Modified in v2.1.0-alpha1 $
 */

namespace Zencart\PluginSupport;

class BasePluginInstaller
{

    /**
     * $dbConn is a database object
     * @var object
     */
    protected $dbConn;
    /**
     * $errorContainer is a PluginErrorContainer object
     * @var object
     */
    protected $errorContainer;
    /**
     * $errorContainer is a pluginInstaller object
     * @var object
     */
    protected $pluginInstaller;
    /**
     * $pluginDir is the directory where the plugin is located
     * @var string
     */
    protected $pluginDir;

    public function __construct($dbConn, $pluginInstaller, $errorContainer)
    {
        $this->dbConn = $dbConn;
        $this->pluginInstaller = $pluginInstaller;
        $this->errorContainer = $errorContainer;
    }

    public function processInstall($pluginKey, $version)
    {
        $this->pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version;
        $this->loadInstallerLanguageFile('main.php', $this->pluginDir);
        $this->pluginInstaller->executeInstallers($this->pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $version, 1);
        return true;
    }

    public function processUninstall($pluginKey, $version)
    {
        $this->pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version;
        $this->loadInstallerLanguageFile('main.php', $this->pluginDir);
        $this->setPluginVersionStatus($pluginKey, '', 0);
        $this->pluginInstaller->executeUninstallers($this->pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        return true;
    }

    public function processUpgrade($pluginKey, $version, $oldVersion)
    {
        $this->pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version;
        $this->loadInstallerLanguageFile('main.php', $this->pluginDir);
        $this->pluginInstaller->executeUpgraders($this->pluginDir, $oldVersion);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $oldVersion, 0);
        $this->setPluginVersionStatus($pluginKey, $version, 1);
        return true;
    }

    public function processDisable($pluginKey, $version)
    {
        $this->setPluginVersionStatus($pluginKey, $version, 2);
    }

    public function processEnable($pluginKey, $version)
    {
        $this->setPluginVersionStatus($pluginKey, $version, 1);
    }

    protected function setPluginVersionStatus($pluginKey, $version, $status)
    {
        $sql = "UPDATE " . TABLE_PLUGIN_CONTROL . " SET status = :status:, version = :version: WHERE unique_key = :uniqueKey:";
        $sql = $this->dbConn->bindVars($sql, ':status:', $status, 'integer');
        $sql = $this->dbConn->bindVars($sql, ':uniqueKey:', $pluginKey, 'string');
        $sql = $this->dbConn->bindVars($sql, ':version:', $version, 'string');
        $this->dbConn->execute($sql);
    }

    protected function loadInstallerLanguageFile($file)
    {
        $lng = $_SESSION['language'];
        $filename = $this->pluginDir . '/Installer/languages/' . $lng . '/' . $file;
        if (file_exists($filename)) {
            require_once $filename;
            return;
        }

        if ($lng === 'english') {
            return;
        }

        $filename = $this->pluginDir . '/Installer/languages/english/' . $file;
        if (file_exists($filename)) {
            require_once $filename;
        }
    }

    public function getErrorContainer()
    {
        return $this->errorContainer;
    }
}
