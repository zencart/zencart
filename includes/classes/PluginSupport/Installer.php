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
    public function executeInstallers($pluginDir): ?bool
    {
        $patch_status = $this->executePatchInstaller($pluginDir);
        if ($patch_status !== null) {
            return $patch_status;
        }
        return $this->executeScriptedInstaller($pluginDir);
    }

    /**
     * @since ZC v1.5.7
     */
    public function executeUninstallers($pluginDir): ?bool
    {
        $patch_status = $this->executePatchUninstaller($pluginDir);
        if ($patch_status !== null) {
            return $patch_status;
        }
        return $this->executeScriptedUninstaller($pluginDir);
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
    public function executeUpgraders($pluginDir, $oldVersion): ?bool
    {
        return $this->executeScriptedUpgrader($pluginDir, $oldVersion);
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
    public function executeDisablers(string $pluginDir): ?bool
    {
        return $this->executeScriptedDisabler($pluginDir);
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
    public function executeEnablers(string $pluginDir): ?bool
    {
        return $this->executeScriptedEnabler($pluginDir);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executePatchInstaller($pluginDir): ?bool
    {
        $patchFile = 'install.sql';
        return $this->executePatchFile($pluginDir, $patchFile);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executePatchUninstaller($pluginDir): ?bool
    {
        $patchFile = 'uninstall.sql';
        return $this->executePatchFile($pluginDir, $patchFile);
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executePatchFile($pluginDir, $patchFile): ?bool
    {
        if (!file_exists($pluginDir . '/Installer/' . $patchFile)) {
            return null;
        }
        $lines = file($pluginDir . '/Installer/' . $patchFile);
        $paramLines = $this->patchInstaller->parse($lines);
        if ($this->errorContainer->hasErrors()) {
            return false;
        }
        $this->patchInstaller->executePatchSql($paramLines);
        return true;
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executeScriptedInstaller($pluginDir): ?bool
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return null;
        }
        return $scriptedInstaller->doInstall();
    }

    /**
     * @since ZC v1.5.7
     */
    protected function executeScriptedUninstaller($pluginDir): ?bool
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return null;
        }
        return $scriptedInstaller->doUninstall();
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
    protected function executeScriptedUpgrader($pluginDir, $oldVersion): ?bool
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return null;
        }
        return $scriptedInstaller->doUpgrade($oldVersion);
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
    protected function executeScriptedDisabler(string $pluginDir): ?bool
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return null;
        }
        return $scriptedInstaller->doDisable();
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
    protected function executeScriptedEnabler(string $pluginDir): ?bool
    {
        $scriptedInstaller = $this->scriptedSetup($pluginDir);
        if (empty($scriptedInstaller)) {
            return null;
        }
        return $scriptedInstaller->doEnable();
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
