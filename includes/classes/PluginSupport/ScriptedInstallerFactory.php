<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Oct 16 Modified in v1.5.8a $
 */

namespace Zencart\PluginSupport;

use queryFactory;

class ScriptedInstallerFactory
{
    protected queryFactory $dbConn;

    protected PluginErrorContainer $errorContainer;

    public function __construct($dbConn, $errorContainer)
    {
        $this->dbConn = $dbConn;
        $this->errorContainer = $errorContainer;
    }

    public function make($pluginDir): \ScriptedInstaller
    {
        require_once $pluginDir . '/Installer/ScriptedInstaller.php';
        $scriptedInstaller = new \ScriptedInstaller($this->dbConn, $this->errorContainer);
        return $scriptedInstaller;
    }
}
