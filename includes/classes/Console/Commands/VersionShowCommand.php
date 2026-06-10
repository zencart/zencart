<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\Console\Commands;

use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class VersionShowCommand extends ConsoleCommand
{
    /**
     * @since ZC v3.0.0
     *
     * @param null|callable(): array<string, ?array<string, mixed>> $versionProvider
     */
    public function __construct(private $versionProvider = null)
    {
    }

    /**
     * @since ZC v3.0.0
     */
    public function getName(): string
    {
        return 'version:show';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getDescription(): string
    {
        return 'Show project and database version information.';
    }

    /**
     * @since ZC v3.0.0
     */
    public function getUsageLines(): array
    {
        return [
            'php zc_cli.php version:show',
        ];
    }

    /**
     * @since ZC v3.0.0
     */
    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        $rows = $this->versionProvider !== null ? ($this->versionProvider)() : [];

        $output->writeln('Version information:');

        $main = $rows['Zen-Cart Main'] ?? null;
        $database = $rows['Zen-Cart Database'] ?? null;

        $output->writeln(sprintf('  %-12s %s', 'application', $this->formatVersion($main)));
        $output->writeln(sprintf('  %-12s %s', 'database', $this->formatVersion($database)));

        return 0;
    }

    /**
     * @since ZC v3.0.0
     */
    private function formatVersion(?array $version): string
    {
        if ($version === null) {
            return 'unavailable';
        }

        $major = trim((string)($version['project_version_major'] ?? ''));
        $minor = trim((string)($version['project_version_minor'] ?? ''));
        $combined = trim($major . ' ' . $minor);

        return $combined !== '' ? $combined : 'unavailable';
    }
}
