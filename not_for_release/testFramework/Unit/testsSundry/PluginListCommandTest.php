<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\Commands\PluginListCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;
use Zencart\PluginSupport\PluginStatus;

class PluginListCommandTest extends TestCase
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

    public function testPluginListFormatsInstalledPluginRows(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new PluginListCommand(static fn(): array => [
            [
                'unique_key' => 'sample_disabled',
                'name' => 'Sample Disabled',
                'status' => PluginStatus::DISABLED,
                'version' => '2.0.0',
            ],
            [
                'unique_key' => 'sample_enabled',
                'name' => 'Sample Enabled',
                'status' => PluginStatus::ENABLED,
                'version' => '1.0.0',
            ],
        ]);

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'plugin:list']), $output);
        $stdoutOutput = stream_get_contents($stdout, -1, 0);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Installed plugins:', $stdoutOutput);
        $this->assertStringContainsString('enabled      sample_enabled', $stdoutOutput);
        $this->assertStringContainsString('disabled     sample_disabled', $stdoutOutput);
        $this->assertStringContainsString('Sample Enabled', $stdoutOutput);
        $this->assertSame('', stream_get_contents($stderr, -1, 0));
    }

    public function testPluginListReportsUnavailableRuntimeWhenProviderMissing(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new PluginListCommand();

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'plugin:list']), $output);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', stream_get_contents($stdout, -1, 0));
        $this->assertStringContainsString(
            'Plugin list unavailable in the current CLI runtime.',
            stream_get_contents($stderr, -1, 0)
        );
    }

    public function testPluginListReportsEmptyPluginState(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new PluginListCommand(static fn(): array => []);

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'plugin:list']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No plugins found in plugin manager state.', stream_get_contents($stdout, -1, 0));
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
