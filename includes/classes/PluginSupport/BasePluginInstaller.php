<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: torvista 2026 Mar 13 Modified in v2.2.1 $
 */

namespace Zencart\PluginSupport;

use queryFactory;
use Zencart\PluginSupport\PluginStatus;

/**
 * @since ZC v1.5.7
 */
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

    /**
     * @since ZC v1.5.7
     */
    public function processInstall($pluginKey, $version): bool
    {
        if (empty($pluginKey) || empty($version)) {
            return false;
        }
        $this->processSetup($pluginKey, $version);
        $this->pluginInstaller->executeInstallers($this->pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $version, PluginStatus::ENABLED);
        return true;
    }

    /**
     * @param string $pluginKey
     * @param string $version
     * @return array
     * @since ZC v3.0.0
     */
    public function processPreInstall($pluginKey, $version): array
    {
        if (empty($pluginKey) || empty($version)) {
            return [];
        }
        $this->processSetup($pluginKey, $version);
        return $this->pluginInstaller->executePreInstallers($this->pluginDir);
    }

    /**
     * @since ZC v1.5.7
     */
    public function processUninstall($pluginKey, $version): bool
    {
        if (empty($pluginKey) || empty($version)) {
            return false;
        }
        $this->processSetup($pluginKey, $version);
        $this->pluginInstaller->executeUninstallers($this->pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, '', PluginStatus::NOT_INSTALLED);
        return true;
    }

    /**
     * @param string $pluginKey
     * @param string $version
     * @return array
     * @since ZC v3.0.0
     */
    public function processPreUninstall($pluginKey, $version): array
    {
        if (empty($pluginKey) || empty($version)) {
            return [];
        }
        $this->processSetup($pluginKey, $version);
        return $this->pluginInstaller->executePreUninstallers($this->pluginDir);
    }

    /**
     * @since ZC v1.5.8
     */
    public function processUpgrade($pluginKey, $version, $oldVersion): bool
    {
        if (empty($pluginKey) || empty($version) || empty($oldVersion)) {
            return false;
        }
        $this->pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version;
        $this->loadInstallerLanguageFile('main.php');
        $this->pluginInstaller->setVersions($this->pluginDir, $pluginKey, $version, $oldVersion);
        $this->pluginInstaller->executeUpgraders($this->pluginDir, $oldVersion);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $oldVersion, PluginStatus::NOT_INSTALLED);
        $this->setPluginVersionStatus($pluginKey, $version, PluginStatus::ENABLED);
        return true;
    }

    /**
     * @param string $pluginKey
     * @param string $version
     * @return array
     * @since ZC v3.0.0
     */
    public function processPreUpgrade(string $pluginKey, string $version): array
    {
        if (empty($pluginKey) || empty($version)) {
            return [];
        }
        $this->processSetup($pluginKey, $version);
        return $this->pluginInstaller->executePreUpgraders($this->pluginDir, $version);
    }

    /**
     * @param string $pluginKey
     * @param string $version
     * @return array
     * @since ZC v3.0.0
     */
    public function processPreConfirmUpgrade(string $pluginKey, string $version): array
    {
        if (empty($pluginKey) || empty($version)) {
            return [];
        }
        $this->processSetup($pluginKey, $version);
        return $this->pluginInstaller->executePreConfirmUpgraders($this->pluginDir, $version);
    }

    /**
     * @param string $pluginKey
     * @param string $version
     * @return array
     * @since ZC v3.0.0
     */
    public function processPreDisable(string $pluginKey, string $version): array
    {
        if (empty($pluginKey) || empty($version)) {
            return [];
        }
        $this->processSetup($pluginKey, $version);
        return $this->pluginInstaller->executePreDisablers($this->pluginDir, $version);
    }

    /**
     * @since ZC v3.0.0
     */
    public function processDisable($pluginKey, $version): bool
    {
        if (empty($pluginKey) || empty($version)) {
            return false;
        }
        $this->processSetup($pluginKey, $version);
        $this->pluginInstaller->executeDisablers($this->pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $version, PluginStatus::DISABLED);
        return true;
    }

    /**
     * @param string $pluginKey
     * @param string $version
     * @return array
     * @since ZC v3.0.0
     */
    public function processPreEnable(string $pluginKey, string $version): array
    {
        if (empty($pluginKey) || empty($version)) {
            return [];
        }
        $this->processSetup($pluginKey, $version);
        return $this->pluginInstaller->executePreEnablers($this->pluginDir, $version);
    }

    /**
     * @since ZC v3.0.0
     */
    public function processEnable($pluginKey, $version): bool
    {
        if (empty($pluginKey) || empty($version)) {
            return false;
        }
        $this->processSetup($pluginKey, $version);
        $this->pluginInstaller->executeEnablers($this->pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $version, PluginStatus::ENABLED);
        return true;
    }

    /**
     * @param string $pluginKey
     * @param string $version
     * @return void
     * @since ZC v3.0.0
     */
    public function processSetup(string $pluginKey, string $version): void
    {
        $this->pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version;
        $this->loadInstallerLanguageFile('main.php');
        $this->pluginInstaller->setVersions($this->pluginDir, $pluginKey, $version);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function setPluginVersionStatus($pluginKey, $version, $status): void
    {
        if (empty($pluginKey)) {
            return;
        }
        $sql = "UPDATE " . TABLE_PLUGIN_CONTROL . " SET status = :status:, version = :version: WHERE unique_key = :uniqueKey:";
        $sql = $this->dbConn->bindVars($sql, ':status:', $status, 'integer');
        $sql = $this->dbConn->bindVars($sql, ':uniqueKey:', $pluginKey, 'string');
        $sql = $this->dbConn->bindVars($sql, ':version:', $version, 'string');
        $this->dbConn->Execute($sql);
    }

    /**
     * Loads the "main.php" language file. This handles "defines" for language-strings. It does NOT handle language-arrays.
     * @since ZC v1.5.7
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

    /**
     * @since ZC v1.5.8a
     */
    public function getErrorContainer(): PluginErrorContainer
    {
        return $this->errorContainer;
    }
}
