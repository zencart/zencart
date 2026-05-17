<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\Commands\VersionShowCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class VersionShowCommandTest extends TestCase
{
    protected $preserveGlobalState = false;

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';
    }

    public function testVersionShowFormatsApplicationAndDatabaseVersions(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new VersionShowCommand(static fn(): array => [
            'Zen-Cart Main' => [
                'project_version_major' => '2.2.1',
                'project_version_minor' => 'build 2026-04-23',
            ],
            'Zen-Cart Database' => [
                'project_version_major' => '2.2.1',
                'project_version_minor' => 'schema 2026-04-23',
            ],
        ]);

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'version:show']), $output);
        $stdoutOutput = stream_get_contents($stdout, -1, 0);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Version information:', $stdoutOutput);
        $this->assertStringContainsString('application  2.2.1 build 2026-04-23', $stdoutOutput);
        $this->assertStringContainsString('database     2.2.1 schema 2026-04-23', $stdoutOutput);
        $this->assertSame('', stream_get_contents($stderr, -1, 0));
    }

    public function testVersionShowReportsUnavailableValuesWhenProviderMissing(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new VersionShowCommand();

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'version:show']), $output);

        $this->assertSame(0, $exitCode);
        $stdoutOutput = stream_get_contents($stdout, -1, 0);
        $this->assertStringContainsString('application  unavailable', $stdoutOutput);
        $this->assertStringContainsString('database     unavailable', $stdoutOutput);
        $this->assertSame('', stream_get_contents($stderr, -1, 0));
    }

    /**
     * @return array{resource, resource, ConsoleOutput}
     */
    private function makeOutput(): array
    {
        $stdout = fopen('php://temp', 'w+');
        $stderr = fopen('php://temp', 'w+');

        return [$stdout, $stderr, new ConsoleOutput($stdout, $stderr)];
    }
}
