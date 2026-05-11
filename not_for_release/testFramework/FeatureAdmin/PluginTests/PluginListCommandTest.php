<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\FeatureAdmin\PluginTests;

use PHPUnit\Framework\Attributes\Group;
use Tests\Support\zcInProcessFeatureTestCaseAdmin;
use Zencart\Console\Commands\PluginListCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;
use Zencart\DbRepositories\PluginControlRepository;

#[Group('serial')]
#[Group('custom-seeder')]
#[Group('plugin-filesystem')]
class PluginListCommandTest extends zcInProcessFeatureTestCaseAdmin
{
    public const TEST_PLUGIN_NAME = 'zenTestPlugin';
    public const TEST_PLUGIN_VERSION = 'v1.0.0';

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

    public function testPluginListShowsInstalledPluginManagerState(): void
    {
        $this->runCustomSeeder('StoreWizardSeeder');
        $this->submitAdminLogin([
            'admin_name' => 'Admin',
            'admin_pass' => 'password',
        ])->assertOk()
            ->assertSee('Admin Home');

        $this->installPluginToFilesystem(self::TEST_PLUGIN_NAME, self::TEST_PLUGIN_VERSION);
        $this->visitAdminCommand('plugin_manager')->assertOk();

        $response = $this->visitAdminCommand('plugin_manager&page=1&colKey=zenTestPlugin&action=install')
            ->assertOk()
            ->assertSee('Test plugin for testing purposes');

        $this->submitAdminForm($response, 'plugininstall')
            ->assertOk()
            ->assertSee('Version Installed:</strong> v1.0.0');

        $db = $this->bootstrapLegacyDbConnection();

        $repository = new PluginControlRepository($db);
        $command = new PluginListCommand(static fn(): array => $repository->getAll());
        [$stdoutHandle, $stderrHandle, $output] = $this->makeOutput();
        $status = $command->handle(new ConsoleInput(['zc_cli.php', 'plugin:list']), $output);
        $stdout = stream_get_contents($stdoutHandle, -1, 0);
        $stderr = stream_get_contents($stderrHandle, -1, 0);

        $this->assertSame(0, $status, trim($stderr . PHP_EOL . $stdout));
        $this->assertStringContainsString('Installed plugins:', $stdout);
        $this->assertMatchesRegularExpression(
            '/enabled\s+zenTestPlugin\s+v1\.0\.0\s+Zen Cart Test Plugin/',
            $stdout
        );
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
