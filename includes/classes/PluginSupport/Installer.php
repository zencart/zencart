<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\PluginSupport;

/**
 * @since ZC v1.5.7
 */
class Installer
{
    protected string $pluginDir;
    protected string $pluginKey;
    protected string $version;
    protected ?string $oldVersion;

    public function __construct(protected SqlPatchInstaller $patchInstaller, protected ScriptedInstallerFactory $scriptedInstallerFactory, protected PluginErrorContainer $errorContainer)
    {
    }

    /**
     * @since ZC v2.1.0
     */
    public function setVersions(string $pluginDir, string $pluginKey, string $version, ?string $oldVersion = null): void
    {
        $this->pluginDir = $pluginDir;
        $this->pluginKey = $pluginKey;
        $this->version = $version;
        $this->oldVersion = $oldVersion;
    }

    /**
     * @since ZC v2.1.0
     */
    public function getVersionInformation(): array
    {
        return [
            'pluginKey' => $this->pluginKey,
            'pluginDir' => $this->pluginDir,
            'version' => $this->version,
            'oldVersion' => $this->oldVersion,
        ];
    }

    /**
     * @since ZC v1.5.7
     */
    public function executeInstallers($pluginDir): void
    {
        $this->executePatchInstaller($pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return;
        }
        $this->executeScriptedInstaller($pluginDir);
    }

    /**
     * @since ZC v1.5.7
     */
    public function executeUninstallers($pluginDir): void
    {
        $this->executePatchUninstaller($pluginDir);
        if ($this->errorContainer->hasErrors()) {
            return;
        }
        $this->executeScriptedUninstaller($pluginDir);
    }

    /**
     * @since ZC v1.5.8
     */
    public function executeUpgraders($pluginDir, $oldVersion): void
    {
        $this->executeScriptedUpgrader($pluginDir, $oldVersion);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executePatchInstaller($pluginDir): void
    {
        $patchFile = 'install.sql';
        $this->executePatchFile($pluginDir, $patchFile);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executePatchUninstaller($pluginDir): void
    {
        $patchFile = 'uninstall.sql';
        $this->executePatchFile($pluginDir, $patchFile);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executePatchFile($pluginDir, $patchFile): void
    {
        if (!file_exists($pluginDir . '/Installer/' . $patchFile)) {
            return;
        }
        $lines = file($pluginDir . '/Installer/' . $patchFile);
        $paramLines = $this->patchInstaller->parse($lines);
        if ($this->errorContainer->hasErrors()) {
            return;
        }
        $this->patchInstaller->executePatchSql($paramLines);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executeScriptedInstaller($pluginDir): void
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return;
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        $scriptedInstaller->doInstall();
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executeScriptedUninstaller($pluginDir): void
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return;
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        $scriptedInstaller->doUninstall();
    }

    /**
     * @since ZC v1.5.8
     */
    protected function executeScriptedUpgrader($pluginDir, $oldVersion): void
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return;
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        $scriptedInstaller->doUpgrade($oldVersion);
    }

    /**
     * @since ZC v1.5.8a
     */
    public function getErrorContainer(): PluginErrorContainer
    {
        return $this->errorContainer;
    }
}
