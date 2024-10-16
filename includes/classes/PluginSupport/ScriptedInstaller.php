<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 21 Modified in v2.1.0-beta1 $
 */

namespace Zencart\PluginSupport;

use queryFactory;

class ScriptedInstaller
{
    use ScriptedInstallHelpers;

    // Extended classes can access these variables to understand what version/etc they are operating on.
    protected string $pluginDir;
    protected string $pluginKey;
    protected string $version;
    protected ?string $oldVersion; // null if not in upgrade mode

    public function __construct(protected queryFactory $dbConn, protected PluginErrorContainer $errorContainer)
    {
    }

    /***** THESE ARE THE 3 METHODS FOR IMPLEMENTATION IN EXTENDED CLASSES *********/
    /***** There is no need to implement any other methods in extended classes ****/

    /**
     * @return bool
     */
    protected function executeInstall()
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function executeUninstall()
    {
        return true;
    }

    /**
     * @return bool
     */
    protected function executeUpgrade($oldVersion)
    {
        return true;
    }

    /******** Internal methods ***********/
    public function setVersionDetails(array $versionDetails): void
    {
        $this->pluginKey = $versionDetails['pluginKey'];
        $this->pluginDir = $versionDetails['pluginDir'];
        $this->version = $versionDetails['version'];
        $this->oldVersion = $versionDetails['oldVersion'];
    }

    public function doInstall(): ?bool
    {
        $installed = $this->executeInstall();
        return $installed;
    }

    public function doUninstall(): ?bool
    {
        $uninstalled = $this->executeUninstall();
        return $uninstalled;
    }

    public function doUpgrade($oldVersion): ?bool
    {
        $upgraded = $this->executeUpgrade($oldVersion);
        return $upgraded;
    }

    protected function executeInstallerSql($sql): bool
    {
        $this->dbConn->dieOnErrors = false;
        $this->dbConn->Execute($sql);
        if ($this->dbConn->error_number !== 0) {
            $this->errorContainer->addError(0, $this->dbConn->error_text, true, PLUGIN_INSTALL_SQL_FAILURE);
            return false;
        }
        $this->dbConn->dieOnErrors = true;
        return true;
    }
}
