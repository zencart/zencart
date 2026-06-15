<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Zencart\Console;

use Aura\Autoload\Loader;

class TrustedPluginClassLoader
{
    public function __construct(private ?Loader $psr4Autoloader = null)
    {
    }

    /**
     * @param array<string, string> $trustedPlugins
     */
    public function registerPluginClassNamespaces(array $trustedPlugins): void
    {
        if ($this->psr4Autoloader === null) {
            return;
        }

        foreach ($trustedPlugins as $uniqueKey => $version) {
            $namespaceAdmin = 'Zencart\\Plugins\\Admin\\' . ucfirst($uniqueKey);
            $namespaceCatalog = 'Zencart\\Plugins\\Catalog\\' . ucfirst($uniqueKey);
            $filePath = DIR_FS_CATALOG . 'zc_plugins/' . $uniqueKey . '/' . $version . '/';

            $this->psr4Autoloader->addPrefix($namespaceAdmin, $filePath . 'admin/includes/classes/');
            $this->psr4Autoloader->addPrefix($namespaceCatalog, $filePath . 'catalog/includes/classes/');
        }
    }
}
