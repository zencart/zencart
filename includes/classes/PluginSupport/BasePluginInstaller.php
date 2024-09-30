<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 20 Modified in v2.1.0-beta1 $
 */

namespace Zencart\PluginSupport;

use queryFactory;

class BasePluginInstaller
{
    /**
     * $pluginDir is the directory where the plugin is located
     * @var string
     */
    protected string $pluginDir;

    public function __construct(protected queryFactory $dbConn, protected Installer $pluginInstaller, protected PluginErrorContainer $errorContainer)
    {
    }

    public function processInstall($pluginKey, $version): bool
    {
        $this->pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version;
        $this->loadInstallerLanguageFile('main.php', $this->pluginDir);
        $this->pluginInstaller->setVersions($this->pluginDir, $pluginKey, $version);
        $this->pluginInstaller->executeInstallers($this->pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $version, 1);
        return true;
    }

    public function processUninstall($pluginKey, $version): bool
    {
        $this->pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version;
        $this->loadInstallerLanguageFile('main.php', $this->pluginDir);
        $this->setPluginVersionStatus($pluginKey, '', 0);
        $this->pluginInstaller->setVersions($this->pluginDir, $pluginKey, $version);
        $this->pluginInstaller->executeUninstallers($this->pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        return true;
    }

    public function processUpgrade($pluginKey, $version, $oldVersion): bool
    {
        $this->pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version;
        $this->loadInstallerLanguageFile('main.php', $this->pluginDir);
        $this->pluginInstaller->setVersions($this->pluginDir, $pluginKey, $version, $oldVersion);
        $this->pluginInstaller->executeUpgraders($this->pluginDir, $oldVersion);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $oldVersion, 0);
        $this->setPluginVersionStatus($pluginKey, $version, 1);
        return true;
    }

    public function processDisable($pluginKey, $version): void
    {
        $this->setPluginVersionStatus($pluginKey, $version, 2);
    }

    public function processEnable($pluginKey, $version): void
    {
        $this->setPluginVersionStatus($pluginKey, $version, 1);
    }

    protected function setPluginVersionStatus($pluginKey, $version, $status): void
    {
        $sql = "UPDATE " . TABLE_PLUGIN_CONTROL . " SET status = :status:, version = :version: WHERE unique_key = :uniqueKey:";
        $sql = $this->dbConn->bindVars($sql, ':status:', $status, 'integer');
        $sql = $this->dbConn->bindVars($sql, ':uniqueKey:', $pluginKey, 'string');
        $sql = $this->dbConn->bindVars($sql, ':version:', $version, 'string');
        $this->dbConn->execute($sql);
    }

    /**
     * Loads the "main.php" language file. This handles "defines" for language-strings. It does NOT handle language-arrays.
     */
    protected function loadInstallerLanguageFile(string $file): void
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

    public function getErrorContainer(): PluginErrorContainer
    {
        return $this->errorContainer;
    }
}
