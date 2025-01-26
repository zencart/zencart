<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Sep 20 Modified in v2.1.0-beta1 $
 */

namespace Zencart\PluginSupport;

use queryFactory;
use Zencart\Exceptions\PluginInstallerException;

class InstallerFactory
{
    public function __construct(protected queryFactory $dbConn, protected Installer $pluginInstaller, protected PluginErrorContainer $errorContainer)
    {
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

        if (!file_exists($versionDir . 'Installer/Installer.php')) {
            $installer = new BasePluginInstaller($this->dbConn, $this->pluginInstaller, $this->errorContainer);
            return $installer;
        }

        require_once $versionDir . 'Installer/Installer.php';
        $installer = new \Installer($this->dbConn, $this->pluginInstaller, $this->errorContainer);
        return $installer;
    }
}
