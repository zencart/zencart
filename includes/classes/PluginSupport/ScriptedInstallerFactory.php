<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2022 Sep 17 Modified in v1.5.8 $
 */

namespace Zencart\PluginSupport;

class ScriptedInstallerFactory
{

    /**
     * $dbConn is a database object 
     * @var object
     */
    protected $dbConn;
    /**
     * $errorContainer is a PluginErrorContainer object
     * @var object
     */
    protected $errorContainer;

    public function __construct($dbConn, $errorContainer)
    {
        $this->dbConn = $dbConn;
        $this->errorContainer = $errorContainer;
    }

    public function make($pluginDir)
    {
        require_once $pluginDir . '/Installer/ScriptedInstaller.php';
        $scriptedInstaller = new \ScriptedInstaller($this->dbConn, $this->errorContainer);
        return $scriptedInstaller;
    }
}