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
        $plugin_return = $this->pluginInstaller->executeInstallers($this->pluginDir);
        if ($this->checkPluginReturn($plugin_return, ERROR_UNKNOWN_FAILURE_INSTALL) === false) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $version, PluginStatus::ENABLED);
        return true;
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
        $plugin_return = $this->pluginInstaller->executeUninstallers($this->pluginDir);
        if ($this->checkPluginReturn($plugin_return, ERROR_UNKNOWN_FAILURE_UNINSTALL) === false) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, '', PluginStatus::NOT_INSTALLED);
        return true;
    }

    /**
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
        $this->processSetup($pluginKey, $version, $oldVersion);
        $plugin_return = $this->pluginInstaller->executeUpgraders($this->pluginDir, $oldVersion);
        if ($this->checkPluginReturn($plugin_return, ERROR_UNKNOWN_FAILURE_UPGRADE) === false) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $oldVersion, PluginStatus::NOT_INSTALLED);
        $this->setPluginVersionStatus($pluginKey, $version, PluginStatus::ENABLED);
        return true;
    }

    /**
     * @since ZC v3.0.0
     */
    public function processPreConfirmUpgrade(string $pluginKey, string $version, string $oldVersion): array
    {
        if (empty($pluginKey) || empty($version)) {
            return [];
        }
        $this->processSetup($pluginKey, $version, $oldVersion);
        return $this->pluginInstaller->executePreConfirmUpgraders($this->pluginDir, $version, $oldVersion);
    }

    /**
     * @since ZC v3.0.0
     */
    public function processPreDisable(string $pluginKey, string $version): array
    {
        if (empty($pluginKey) || empty($version)) {
            return [];
        }
        $this->processSetup($pluginKey, $version);
        return $this->pluginInstaller->executePreDisablers($this->pluginDir);
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
        $plugin_return = $this->pluginInstaller->executeDisablers($this->pluginDir);
        if ($this->checkPluginReturn($plugin_return, ERROR_UNKNOWN_FAILURE_DISABLE) === false) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $version, PluginStatus::DISABLED);
        return true;
    }

    /**
     * @since ZC v3.0.0
     */
    public function processPreEnable(string $pluginKey, string $version): array
    {
        if (empty($pluginKey) || empty($version)) {
            return [];
        }
        $this->processSetup($pluginKey, $version);
        return $this->pluginInstaller->executePreEnablers($this->pluginDir);
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
        $plugin_return = $this->pluginInstaller->executeEnablers($this->pluginDir);
        if ($this->checkPluginReturn($plugin_return, ERROR_UNKNOWN_FAILURE_ENABLE) === false) {
            return false;
        }
        $this->setPluginVersionStatus($pluginKey, $version, PluginStatus::ENABLED);
        return true;
    }

    /**
     * @since ZC v3.0.0
     */
    protected function processSetup(string $pluginKey, string $version, ?string $oldVersion = null): void
    {
        $this->pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/' . $version;
        $this->loadInstallerLanguageFile('main.php');
        $this->pluginInstaller->setVersions($this->pluginDir, $pluginKey, $version, $oldVersion);
    }

    /**
     * @since ZC v3.0.0
     */
    protected function checkPluginReturn(?bool $plugin_return, string $plugin_action): bool
    {
        if ($plugin_return !== false) {
            return true;
        }
        if (!$this->errorContainer->hasErrors()) {
            $default_message = sprintf(ERROR_UNKNOWN_FAILURE, $plugin_action);
            $this->errorContainer->addError(0, $default_message, false, $default_message);
        }
        return false;
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
