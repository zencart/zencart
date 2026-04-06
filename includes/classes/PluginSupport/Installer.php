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
     * @param string $pluginDir
     * @return array
     * @since ZC v3.0.0
     */
    public function executePreInstallers(string $pluginDir): array
    {
        return $this->executeScriptedPreInstaller($pluginDir);
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
     * @param string $pluginDir
     * @return array
     * @since ZC v3.0.0
     */
    public function executePreUninstallers(string $pluginDir): array
    {
        return $this->executeScriptedPreUninstaller($pluginDir);
    }

    /**
     * @since ZC v1.5.8
     */
    public function executeUpgraders($pluginDir, $oldVersion): void
    {
        $this->executeScriptedUpgrader($pluginDir, $oldVersion);
    }

    /**
     * @param string $pluginDir
     * @param string $oldVersion
     * @return array
     * @since ZC v3.0.0
     */
    public function executePreUpgraders(string $pluginDir, string $oldVersion): array
    {
        return $this->executeScriptedPreUpgrader($pluginDir, $oldVersion);
    }

    /**
     * @param string $pluginDir
     * @param string $oldVersion
     * @return array
     * @since ZC v3.0.0
     */
    public function executePreConfirmUpgraders(string $pluginDir, string $oldVersion): array
    {
        return $this->executeScriptedPreConfirmUpgrader($pluginDir, $oldVersion);
    }

    /**
     * @param string $pluginDir
     * @return array
     * @since ZC v3.0.0
     */
    public function executePreDisablers(string $pluginDir): array
    {
        return $this->executeScriptedPreDisabler($pluginDir);
    }

    /**
     * @param string $pluginDir
     * @return void
     * @since ZC v3.0.0
     */
    public function executeDisablers(string $pluginDir): void
    {
        $this->executeScriptedDisabler($pluginDir);
    }

    /**
     * @param string $pluginDir
     * @return array
     * @since ZC v3.0.0
     */
    public function executePreEnablers(string $pluginDir): array
    {
        return $this->executeScriptedPreEnabler($pluginDir);
    }

    /**
     * @param string $pluginDir
     * @return void
     * @since ZC v3.0.0
     */
    public function executeEnablers(string $pluginDir): void
    {
        $this->executeScriptedEnabler($pluginDir);
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
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreInstaller(string $pluginDir): array
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return [];
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        return $scriptedInstaller->doPreInstall();
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
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreUninstaller(string $pluginDir): array
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return [];
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        return $scriptedInstaller->doPreUninstall();
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
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreUpgrader(string $pluginDir, string $oldVersion): array
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return [];
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        return $scriptedInstaller->doPreUpgrade($oldVersion);
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreConfirmUpgrader(string $pluginDir, string $oldVersion): array
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return [];
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        return $scriptedInstaller->doPreUpgrade($oldVersion);
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedDisabler(string $pluginDir): void
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return;
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        $scriptedInstaller->doDisable();
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreDisabler(string $pluginDir): array
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return [];
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        return $scriptedInstaller->doPreDisable();
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedEnabler(string $pluginDir): void
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return;
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        $scriptedInstaller->doEnable();
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreEnabler(string $pluginDir): array
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return [];
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        return $scriptedInstaller->doPreEnable();
    }

    /**
     * @since ZC v1.5.8a
     */
    public function getErrorContainer(): PluginErrorContainer
    {
        return $this->errorContainer;
    }
}
