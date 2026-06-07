<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\TestFrameworkFilesystem;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\Commands\CurrencyRatesUpdateCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class CurrencyRatesUpdateCommandTest extends TestCase
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

    protected function tearDown(): void
    {
        (new TestFrameworkFilesystem())->removePlugin('zenTestCurrencyPlugin', 'v1.0.0', DIR_FS_CATALOG);

        parent::tearDown();
    }

    /**
     * @runInSeparateProcess
     */
    public function testCurrencyRatesUpdateLoadsLegacyPluginCurrencyProvider(): void
    {
        $this->installCurrencyUpdateStubs();
        (new TestFrameworkFilesystem())->installPlugin('zenTestCurrencyPlugin', DIR_FS_CATALOG, DIR_FS_CATALOG);
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new CurrencyRatesUpdateCommand($this->makeConfigurationProvider([
            'DEFAULT_CURRENCY' => 'USD',
            'CURRENCY_SERVER_PRIMARY' => 'zztestcli',
            'CURRENCY_SERVER_BACKUP' => '',
            'CURRENCY_UPLIFT_RATIO' => '0',
        ]), null, static fn(): array => ['zenTestCurrencyPlugin' => 'v1.0.0']);

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'currency-rates:update']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertTrue($GLOBALS['zcCurrencyUpdateInvoked'] ?? false);
        $this->assertSame('', stream_get_contents($stdout, -1, 0));
        $this->assertSame('', stream_get_contents($stderr, -1, 0));
    }

    /**
     * @runInSeparateProcess
     */
    public function testCurrencyRatesUpdateAllowsBackupProviderWhenPrimaryFunctionIsMissing(): void
    {
        $this->installCurrencyUpdateStubs();
        (new TestFrameworkFilesystem())->installPlugin('zenTestCurrencyPlugin', DIR_FS_CATALOG, DIR_FS_CATALOG);
        [$stdout, $stderr, $output] = $this->makeOutput();
        $command = new CurrencyRatesUpdateCommand($this->makeConfigurationProvider([
            'DEFAULT_CURRENCY' => 'USD',
            'CURRENCY_SERVER_PRIMARY' => 'missingprimary',
            'CURRENCY_SERVER_BACKUP' => 'zztestbackup',
            'CURRENCY_UPLIFT_RATIO' => '0',
        ]), null, static fn(): array => ['zenTestCurrencyPlugin' => 'v1.0.0']);

        $exitCode = $command->handle(new ConsoleInput(['zc_cli.php', 'currency-rates:update']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertTrue($GLOBALS['zcCurrencyUpdateInvoked'] ?? false);
        $this->assertSame('', stream_get_contents($stdout, -1, 0));
        $this->assertSame('', stream_get_contents($stderr, -1, 0));
    }

    private function installCurrencyUpdateStubs(): void
    {
        $GLOBALS['zcCurrencyUpdateInvoked'] = false;

        if (!function_exists('zc_cli_get_db_context')) {
            eval(<<<'PHP'
namespace {
    function zc_cli_get_db_context(): array
    {
        return ['db' => (object)[], 'warnings' => []];
    }
}
PHP);
        }

        if (!function_exists('zen_update_currencies')) {
            eval(<<<'PHP'
namespace {
    function zen_update_currencies(bool $outputMessagesToCommandLine = false): void
    {
        $GLOBALS['zcCurrencyUpdateInvoked'] = true;
    }
}
PHP);
        }
    }

    /**
     * @param array<string, string> $configuration
     * @return callable(string): ?array<string, string>
     */
    private function makeConfigurationProvider(array $configuration): callable
    {
        return static function (string $key) use ($configuration): ?array {
            if (!array_key_exists($key, $configuration)) {
                return null;
            }

            return [
                'configuration_key' => $key,
                'configuration_value' => $configuration[$key],
            ];
        };
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
