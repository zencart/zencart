<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console\Commands;

use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;
use Zencart\PluginSupport\PluginStatus;

class PluginListCommand extends ConsoleCommand
{
    /**
     * @since ZC v3.0.0
     *
     * @param null|callable(): ?array<int|string, array<string, mixed>> $pluginProvider
     */
    public function __construct(private $pluginProvider = null)
    {
    }

    /**
     * @since ZC v3.0.0
     */
    public function getName(): string
    {
        return 'plugin:list';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getDescription(): string
    {
        return 'List plugins known to plugin manager state.';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getUsageLines(): array
    {
        return [
            'php zc_cli.php plugin:list',
        ];
    }

    /**
     * @since ZC v3.0.0
     */
    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        $rows = $this->pluginProvider !== null ? ($this->pluginProvider)() : null;
        if ($rows === null) {
            $output->errorln('Plugin list unavailable in the current CLI runtime.');
            return 1;
        }

        $plugins = array_values($rows);
        usort($plugins, function (array $left, array $right): int {
            $leftName = strtolower((string)($left['name'] ?? $left['unique_key'] ?? ''));
            $rightName = strtolower((string)($right['name'] ?? $right['unique_key'] ?? ''));

            return [$leftName, (string)($left['unique_key'] ?? '')] <=> [$rightName, (string)($right['unique_key'] ?? '')];
        });

        if ($plugins === []) {
            $output->writeln('No plugins found in plugin manager state.');
            return 0;
        }

        $output->writeln('Installed plugins:');
        $output->writeln(sprintf('  %-12s %-24s %-12s %s', 'status', 'key', 'version', 'name'));

        foreach ($plugins as $plugin) {
            $output->writeln(sprintf(
                '  %-12s %-24s %-12s %s',
                $this->formatStatus((int)($plugin['status'] ?? PluginStatus::NOT_INSTALLED)),
                (string)($plugin['unique_key'] ?? ''),
                (string)($plugin['version'] ?? ''),
                (string)($plugin['name'] ?? $plugin['unique_key'] ?? '')
            ));
        }

        return 0;
    }

    /**
     * @since ZC v3.0.0
     */
    private function formatStatus(int $status): string
    {
        return match ($status) {
            PluginStatus::ENABLED => 'enabled',
            PluginStatus::DISABLED => 'disabled',
            PluginStatus::NOT_INSTALLED => 'not-installed',
            default => 'unknown',
        };
    }
}
