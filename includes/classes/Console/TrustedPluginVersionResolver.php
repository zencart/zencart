<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console;

use Zencart\DbRepositories\PluginControlRepository;
use Zencart\PluginSupport\PluginStatus;

class TrustedPluginVersionResolver
{
    /**
     * @since ZC v3.0.0
     */
    public function __construct(private PluginControlRepository $repository)
    {
    }

    /**
     * @since ZC v3.0.0
     *
     * @return array<string, string>
     */
    public function resolveEnabledPluginVersions(): array
    {
        $plugins = [];

        foreach ($this->repository->getInstalledPlugins(PluginStatus::ENABLED) as $plugin) {
            $pluginKey = $plugin['unique_key'] ?? '';
            $pluginVersion = $plugin['version'] ?? '';

            if ($pluginKey === '' || $pluginVersion === '') {
                continue;
            }

            $plugins[$pluginKey] = $pluginVersion;
        }

        return $plugins;
    }
}
