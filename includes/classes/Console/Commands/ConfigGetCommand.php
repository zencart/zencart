<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console\Commands;

use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class ConfigGetCommand extends ConsoleCommand
{
    /**
     * @since ZC v3.0.0
     *
     * @param null|callable(string): ?array<string, mixed> $configurationProvider
     */
    public function __construct(private $configurationProvider = null)
    {
    }

    /**
     * @since ZC v3.0.0
     */
    public function getName(): string
    {
        return 'config:get';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getDescription(): string
    {
        return 'Show a single configuration value by key.';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getUsageLines(): array
    {
        return [
            'php zc_cli.php config:get <CONFIGURATION_KEY>',
        ];
    }

    /**
     * @since ZC v3.0.0
     */
    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        $key = strtoupper(trim((string)$input->getArgument(0, '')));
        if ($key === '') {
            $output->errorln('Missing required configuration key.');
            $output->errorln('Usage:');
            foreach ($this->getUsageLines() as $usageLine) {
                $output->errorln('  ' . $usageLine);
            }

            return 1;
        }

        if ($this->configurationProvider === null) {
            $output->errorln('Configuration lookup unavailable in the current CLI runtime.');
            return 1;
        }

        $row = ($this->configurationProvider)($key);
        if ($row === null) {
            $output->errorln('Configuration key not found: ' . $key);
            return 1;
        }

        $output->writeln('Configuration value:');
        $output->writeln(sprintf('  %-24s %s', (string)$row['configuration_key'], (string)$row['configuration_value']));

        return 0;
    }
}
