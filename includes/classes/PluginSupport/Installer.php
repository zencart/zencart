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
     * @since ZC v3.0.0
     */
    public function executePreConfirmUpgraders(string $pluginDir, string $version, string $oldVersion): array
    {
        return $this->executeScriptedPreConfirmUpgrader($pluginDir, $version, $oldVersion);
    }

    /**
     * @since ZC v3.0.0
     */
    public function executePreDisablers(string $pluginDir): array
    {
        return $this->executeScriptedPreDisabler($pluginDir);
    }

    /**
     * @since ZC v3.0.0
     */
    public function executeDisablers(string $pluginDir): void
    {
        $this->executeScriptedDisabler($pluginDir);
    }

    /**
     * @since ZC v3.0.0
     */
    public function executePreEnablers(string $pluginDir): array
    {
        return $this->executeScriptedPreEnabler($pluginDir);
    }

    /**
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
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return;
        }
        $scriptedInstaller->doInstall();
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreInstaller(string $pluginDir): array
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return [];
        }
        return $scriptedInstaller->doPreInstall();
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executeScriptedUninstaller($pluginDir): void
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return;
        }
        $scriptedInstaller->doUninstall();
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreUninstaller(string $pluginDir): array
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return [];
        }
        return $scriptedInstaller->doPreUninstall();
    }

    /**
     * @since ZC v1.5.8
     */
    protected function executeScriptedUpgrader($pluginDir, $oldVersion): void
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return;
        }
        $scriptedInstaller->doUpgrade($oldVersion);
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreConfirmUpgrader(string $pluginDir, string $version, string $oldVersion): array
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return [];
        }
        return $scriptedInstaller->doPreConfirmUpgrade($version, $oldVersion);
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedDisabler(string $pluginDir): void
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return;
        }
        $scriptedInstaller->doDisable();
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreDisabler(string $pluginDir): array
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return [];
        }
        return $scriptedInstaller->doPreDisable();
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedEnabler(string $pluginDir): void
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return;
        }
        $scriptedInstaller->doEnable();
    }

    /**
     * @since ZC v3.0.0
     */
    protected function executeScriptedPreEnabler(string $pluginDir): array
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return [];
        }
        return $scriptedInstaller->doPreEnable();
    }

    /**
     * @since ZC v3.0.0
     */
    protected function scriptedSetup(string $pluginDir): ?ScriptedInstaller
    {
        if (!file_exists($pluginDir . '/Installer/ScriptedInstaller.php')) {
            return null;
        }
        $scriptedInstaller = $this->scriptedInstallerFactory->make($pluginDir);
        $scriptedInstaller->setVersionDetails($this->getVersionInformation());
        return $scriptedInstaller;
    }

    /**
     * @since ZC v1.5.8a
     */
    public function getErrorContainer(): PluginErrorContainer
    {
        return $this->errorContainer;
    }
}
