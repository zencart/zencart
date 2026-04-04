<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\PluginSupport;

use queryFactory;

/**
 * @since ZC v1.5.7
 */
class ScriptedInstallerFactory
{
    public function __construct(protected queryFactory $dbConn, protected PluginErrorContainer $errorContainer)
    {
    }

    /**
     * @since ZC v1.5.7
     */
    public function make($pluginDir): ScriptedInstaller
    {
        require_once $pluginDir . '/Installer/ScriptedInstaller.php';
        $scriptedInstaller = new \ScriptedInstaller($this->dbConn, $this->errorContainer);
        return $scriptedInstaller;
    }
}
