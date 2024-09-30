<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 20 Modified in v2.1.0-beta1 $
 */

namespace Zencart\PluginSupport;

use queryFactory;

class ScriptedInstallerFactory
{
    public function __construct(protected queryFactory $dbConn, protected PluginErrorContainer $errorContainer)
    {
    }

    public function make($pluginDir): ScriptedInstaller
    {
        require_once $pluginDir . '/Installer/ScriptedInstaller.php';
        $scriptedInstaller = new \ScriptedInstaller($this->dbConn, $this->errorContainer);
        return $scriptedInstaller;
    }
}
