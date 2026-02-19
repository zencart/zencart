<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 29 Modified in v2.2.0 $
 */

namespace Zencart\ViewBuilders;

use Zencart\DbRepositories\PluginControlRepository;
use Zencart\PluginSupport\PluginStatus;

/**
 * @since ZC v1.5.8
 */
class PluginManagerDataSource extends DataTableDataSource
{
    /**
     * @since ZC v1.5.8
     */
    protected function buildInitialQuery(): array
    {
        global $db;
        $statusSort = [
            PluginStatus::ENABLED, // enabled
            PluginStatus::DISABLED, // disabled
            PluginStatus::NOT_INSTALLED, // not installed
        ];

        $rows = (new PluginControlRepository($db))->getAll();
        $statusOrder = array_flip($statusSort);

        usort($rows, function (array $a, array $b) use ($statusOrder): int {
            $statusA = $statusOrder[(int)($a['status'] ?? PluginStatus::NOT_INSTALLED)] ?? 999;
            $statusB = $statusOrder[(int)($b['status'] ?? PluginStatus::NOT_INSTALLED)] ?? 999;
            if ($statusA !== $statusB) {
                return $statusA <=> $statusB;
            }

            $nameCmp = strcasecmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
            if ($nameCmp !== 0) {
                return $nameCmp;
            }

            return strcasecmp((string)($a['unique_key'] ?? ''), (string)($b['unique_key'] ?? ''));
        });

        return $rows;
    }
}
