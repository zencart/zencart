<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 20 New in v1.5.7 $
 */

namespace Zencart\PluginSupport;

class ScriptedInstaller
{
    public function __construct($dbConn, $errorContainer)
    {
        $this->dbConn = $dbConn;
        $this->errorContainer = $errorContainer;
    }

    public function doInstall()
    {
        $installed = $this->executeInstall();
        return $installed;
    }

    public function doUninstall()
    {
        $uninstalled = $this->executeUninstall();
        return $uninstalled;
    }

    protected function executeInstall()
    {
        return true;
    }

    protected function executeUninstall()
    {
        return true;
    }

    protected function executeInstallerSql($sql)
    {
        $this->dbConn->dieOnErrors = false;
        $this->dbConn->Execute($sql);
        if ($this->dbConn->error_number !== 0) {
            $this->errorContainer->addError(0, $this->dbConn->error_text, false, PLUGIN_INSTALL_SQL_FAILURE);
            return false;
        }
        $this->dbConn->dieOnErrors = true;
        return true;
    }
}