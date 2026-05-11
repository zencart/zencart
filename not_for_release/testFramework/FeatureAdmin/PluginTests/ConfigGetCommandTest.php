<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\PluginTests;

use PHPUnit\Framework\Attributes\Group;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;
use Zencart\Console\Commands\ConfigGetCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;
use Zencart\DbRepositories\ConfigurationRepository;

#[Group('serial')]
#[Group('custom-seeder')]
class ConfigGetCommandTest extends zcInProcessFeatureTestCaseAdmin
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once ROOTCWD . 'includes/defined_paths.php';
        require_once ROOTCWD . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require ROOTCWD . 'includes/psr4Autoload.php';
    }

    public function testConfigGetReadsSeededConfigurationValue(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');
        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Admin Home');

        $db = $this->bootstrapLegacyDbConnection();
        $repository = new ConfigurationRepository($db);
        $command = new ConfigGetCommand(static fn(string $key): ?array => $repository->getByKey($key));

        [$stdoutHandle, $stderrHandle, $output] = $this->makeOutput();
        $status = $command->handle(new ConsoleInput(['zc_cli.php', 'config:get', 'STORE_NAME']), $output);
        $stdout = stream_get_contents($stdoutHandle, -1, 0);
        $stderr = stream_get_contents($stderrHandle, -1, 0);

        $this->assertSame(0, $status, trim($stderr . PHP_EOL . $stdout));
        $this->assertStringContainsString('Configuration value:', $stdout);
        $this->assertStringContainsString('STORE_NAME', $stdout);
        $this->assertStringContainsString('Zencart Store Name', $stdout);
        $this->assertSame('', $stderr);
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

    private function bootstrapLegacyDbConnection(): \queryFactory
    {
        if (!class_exists('queryFactory')) {
            require_once ROOTCWD . 'includes/classes/class.base.php';
            require_once ROOTCWD . 'includes/classes/db/' . DB_TYPE . '/query_factory.php';
        }

        $db = new \queryFactory();
        if (!defined('USE_PCONNECT')) {
            define('USE_PCONNECT', 'false');
        }

        $db->connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, USE_PCONNECT, false);

        return $db;
    }
}
