<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\Commands\ConfigGetCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class ConfigGetCommandTest extends TestCase
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

    public function testConfigGetFormatsConfigurationValue(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new ConfigGetCommand(static fn(string $key): ?array => [
            'configuration_key' => $key,
            'configuration_value' => 'Example Value',
        ]);

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'config:get', 'store_name']), $output);
        $stdoutOutput = stream_get_contents($stdout, -1, 0);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Configuration value:', $stdoutOutput);
        $this->assertStringContainsString('STORE_NAME', $stdoutOutput);
        $this->assertStringContainsString('Example Value', $stdoutOutput);
        $this->assertSame('', stream_get_contents($stderr, -1, 0));
    }

    public function testConfigGetRequiresAKey(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new ConfigGetCommand();

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'config:get']), $output);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', stream_get_contents($stdout, -1, 0));
        $stderrOutput = stream_get_contents($stderr, -1, 0);
        $this->assertStringContainsString('Missing required configuration key.', $stderrOutput);
        $this->assertStringContainsString('php zc_cli.php config:get <CONFIGURATION_KEY>', $stderrOutput);
    }

    public function testConfigGetReportsMissingKey(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new ConfigGetCommand(static fn(string $key): ?array => null);

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'config:get', 'DOES_NOT_EXIST']), $output);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', stream_get_contents($stdout, -1, 0));
        $this->assertStringContainsString('Configuration key not found: DOES_NOT_EXIST', stream_get_contents($stderr, -1, 0));
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
