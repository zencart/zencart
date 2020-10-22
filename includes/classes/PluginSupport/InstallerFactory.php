<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 May 20 New in v1.5.7 $
 */

namespace Zencart\PluginSupport;

use Zencart\AdminExceptions\PluginInstallerException;

class InstallerFactory
{
    public function __construct($dbConn, $pluginInstaller, $errorContainer)
    {
        $this->dbConn = $dbConn;
        $this->pluginInstaller = $pluginInstaller;
        $this->errorContainer = $errorContainer;
    }

    public function make($plugin, $version)
    {
        $pluginDir = DIR_FS_CATALOG . 'zc_plugins/' . $plugin . '/';
        $versionDir = $pluginDir . $version . '/';

        if (!is_dir($pluginDir)) {
            throw new PluginInstallerException('NO PLUGIN DIRECTORY');
        }
        if (!is_dir($versionDir)) {
            throw new PluginInstallerException('NO PLUGIN VERSION DIRECTORY');
        }
        if (!file_exists($versionDir . 'manifest.php')) {
            throw new PluginInstallerException('NO VERSION MANIFEST');
        }
        if (!file_exists($versionDir . 'installer/' . 'Installer.php')) {
            $installer = new BasePluginInstaller($this->dbConn, $this->pluginInstaller, $this->errorContainer);
            return $installer;
        }
        require_once($versionDir . 'Installer');
        $installer = new Installer($this->dbConn, $this->pluginInstaller, $this->errorContainer);
        return $installer;
    }
}