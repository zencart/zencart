<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\TestFrameworkFilesystem;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\CommandRegistry;
use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleKernel;
use Zencart\Console\ConsoleOutput;
use Zencart\Console\PluginCommandDiscovery;

class ConsoleKernelTest extends TestCase
{
    protected $preserveGlobalState = false;
    private string $basePath = '';
    private string $catalogPath = '';
    /**
     * @var string[]
     */
    private array $pluginRootsToRemove = [];

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/zc-console-kernel-' . uniqid('', true);
        $this->catalogPath = $this->basePath . '/catalog';
        mkdir($this->catalogPath . '/zc_plugins', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->basePath);
        (new TestFrameworkFilesystem())->removePlugin('zenTestPlugin', 'v1.0.0', DIR_FS_CATALOG);
        foreach ($this->pluginRootsToRemove as $pluginRoot) {
            $this->removeDirectory(dirname($pluginRoot));
        }
        $this->pluginRootsToRemove = [];
        parent::tearDown();
    }

    public function testListCommandShowsCoreCommands(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();

        $kernel = new ConsoleKernel();
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'list']), $output);

        $this->assertSame(0, $exitCode);
        $stdoutOutput = stream_get_contents($stdout, -1, 0);
        $this->assertStringContainsString('Available commands:', $stdoutOutput);
        $this->assertStringContainsString('config:get', $stdoutOutput);
        $this->assertStringContainsString('plugin:list', $stdoutOutput);
        $this->assertStringContainsString('version:show', $stdoutOutput);
        $this->assertSame('', stream_get_contents($stderr, -1, 0));
    }

    public function testHelpFlagRoutesThroughHelpCommand(): void
    {
        [$stdout, , $output] = $this->makeOutput();

        $kernel = new ConsoleKernel();
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'list', '--help']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('List available console commands.', stream_get_contents($stdout, -1, 0));
    }

    public function testGlobalHelpFallsBackToCommandListing(): void
    {
        [$stdout, , $output] = $this->makeOutput();

        $kernel = new ConsoleKernel();
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', '--help']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Available commands:', stream_get_contents($stdout, -1, 0));
    }

    public function testPluginCommandRunsThroughKernel(): void
    {
        [$stdout, , $output] = $this->makeOutput();
        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $discovery = new PluginCommandDiscovery(
            DIR_FS_CATALOG . 'not_for_release/testFramework/Support/plugins',
            $psr4Autoloader
        );

        $kernel = new ConsoleKernel(null, $discovery);
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'zen-test:demo', 'team']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Hello team', stream_get_contents($stdout, -1, 0));
    }

    public function testBrokenTrustedPluginAutoloaderBecomesBootWarning(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        (new TestFrameworkFilesystem())->installPlugin('zenTestPlugin', DIR_FS_CATALOG, DIR_FS_CATALOG);
        $pluginRoot = DIR_FS_CATALOG . 'zc_plugins/zenTestPlugin/v1.0.0';

        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            "<?php\nthrow new RuntimeException('autoload exploded');\n"
        );

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $kernel = new ConsoleKernel(
            null,
            null,
            [],
            null,
            null,
            null,
            $psr4Autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'list']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Available commands:', stream_get_contents($stdout, -1, 0));
        $this->assertStringContainsString(
            'Failed loading plugin autoloader from zenTestPlugin/v1.0.0/psr4Autoload.php',
            stream_get_contents($stderr, -1, 0)
        );
    }

    public function testTrustedPluginAutoloaderCanReadCliConfigurationDuringBoot(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        (new TestFrameworkFilesystem())->installPlugin('zenTestPlugin', DIR_FS_CATALOG, DIR_FS_CATALOG);
        $pluginRoot = DIR_FS_CATALOG . 'zc_plugins/zenTestPlugin/v1.0.0';
        $markerFile = $pluginRoot . '/config-marker.txt';

        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            "<?php\nfile_put_contents(" . var_export($markerFile, true) . ", (string) zen_config('CURL_PROXY_REQUIRED'));\n"
        );

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $db = new \queryFactory();
        $cliConfigurationLoader = new \Zencart\Console\CliConfigurationLoader(
            new class ($db) extends \Zencart\DbRepositories\ConfigurationRepository {
                public function loadConfigSettings(): void
                {
                }

                public function get(string $configurationKey): mixed
                {
                    return $configurationKey === 'CURL_PROXY_REQUIRED' ? 'True' : null;
                }
            },
            new class ($db) extends \Zencart\DbRepositories\ProductTypeLayoutRepository {
                public function loadConfigSettings(): void
                {
                }

                public function get(string $configurationKey): mixed
                {
                    return null;
                }
            }
        );

        $kernel = new ConsoleKernel(
            null,
            null,
            [],
            null,
            null,
            null,
            $psr4Autoloader,
            ['zenTestPlugin' => 'v1.0.0'],
            $db,
            $cliConfigurationLoader
        );
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'list']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertSame('True', file_get_contents($markerFile));
        $this->assertStringContainsString('Available commands:', stream_get_contents($stdout, -1, 0));
        $this->assertSame('', stream_get_contents($stderr, -1, 0));
    }

    public function testTrustedPluginAutoloaderLoadsPluginDataFilesBeforeCommandDiscovery(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $pluginKey = 'zenTestPluginBootstrapOrder';
        $pluginRoot = $this->createPluginFixture($pluginKey);

        mkdir($pluginRoot . '/catalog/includes/extra_configures', 0777, true);
        mkdir($pluginRoot . '/Console/Commands', 0777, true);
        mkdir($pluginRoot . '/support', 0777, true);

        file_put_contents(
            $pluginRoot . '/catalog/includes/extra_configures/bootstrap.php',
            "<?php\ndefine('ZEN_TEST_PLUGIN_BOOTSTRAP_READY', 'yes');\n"
        );
        file_put_contents(
            $pluginRoot . '/filenames.php',
            "<?php\ndefine('FILENAME_ZEN_TEST_PLUGIN_BOOTSTRAP_READY', 'zen_test_bootstrap_ready.php');\n"
        );
        file_put_contents(
            $pluginRoot . '/support/Status.php',
            <<<'PHP'
<?php

namespace ZenTestPluginBootstrapOrder\Support;

class Status
{
    public static function message(): string
    {
        return FILENAME_ZEN_TEST_PLUGIN_BOOTSTRAP_READY . ':' . ZEN_TEST_PLUGIN_BOOTSTRAP_READY;
    }
}
PHP
        );
        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            <<<'PHP'
<?php

if (!defined('ZEN_TEST_PLUGIN_BOOTSTRAP_READY') || !defined('FILENAME_ZEN_TEST_PLUGIN_BOOTSTRAP_READY')) {
    throw new RuntimeException('plugin bootstrap constants missing');
}

/** @var \Aura\Autoload\Loader $psr4Autoloader */
$psr4Autoloader->addPrefix('ZenTestPluginBootstrapOrder\\Support', __DIR__ . '/support/');
PHP
        );
        file_put_contents(
            $pluginRoot . '/Console/Commands/BootstrapAwareCommand.php',
            <<<'PHP'
<?php

namespace Zencart\Plugins\Console\ZenTestPluginBootstrapOrder\Commands;

use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;
use ZenTestPluginBootstrapOrder\Support\Status;

class BootstrapAwareCommand extends ConsoleCommand
{
    public function getName(): string
    {
        return 'zen-test:bootstrap-aware';
    }

    public function getDescription(): string
    {
        return 'Uses plugin constants during trusted CLI bootstrap.';
    }

    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        $output->writeln(Status::message());

        return 0;
    }
}
PHP
        );
        file_put_contents(
            $pluginRoot . '/Console/commands.php',
            <<<'PHP'
<?php

return [
    \Zencart\Plugins\Console\ZenTestPluginBootstrapOrder\Commands\BootstrapAwareCommand::class,
];
PHP
        );

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $discovery = new PluginCommandDiscovery(
            DIR_FS_CATALOG . 'zc_plugins',
            $psr4Autoloader,
            [$pluginKey => 'v1.0.0']
        );

        $kernel = new ConsoleKernel(
            null,
            $discovery,
            [],
            null,
            null,
            null,
            $psr4Autoloader,
            [$pluginKey => 'v1.0.0']
        );
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'zen-test:bootstrap-aware']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertSame('', stream_get_contents($stderr, -1, 0));
        $this->assertStringContainsString(
            'zen_test_bootstrap_ready.php:yes',
            stream_get_contents($stdout, -1, 0)
        );
    }

    public function testPluginCommandDiscoveryCanUseTrustedCatalogPluginClasses(): void
    {
        [$stdout, , $output] = $this->makeOutput();
        (new TestFrameworkFilesystem())->installPlugin('zenTestPlugin', DIR_FS_CATALOG, DIR_FS_CATALOG);
        $pluginRoot = DIR_FS_CATALOG . 'zc_plugins/zenTestPlugin/v1.0.0';

        mkdir($pluginRoot . '/catalog/includes/classes/Support', 0777, true);
        file_put_contents(
            $pluginRoot . '/catalog/includes/classes/Support/Label.php',
            <<<'PHP'
<?php

namespace Zencart\Plugins\Catalog\ZenTestPlugin\Support;

class Label
{
    public static function message(): string
    {
        return 'plugin-aware';
    }
}
PHP
        );

        file_put_contents(
            $pluginRoot . '/Console/Commands/PluginAwareCommand.php',
            <<<'PHP'
<?php

namespace Zencart\Plugins\Console\ZenTestPlugin\Commands;

use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;
use Zencart\Plugins\Catalog\ZenTestPlugin\Support\Label;

class PluginAwareCommand extends ConsoleCommand
{
    public function getName(): string
    {
        return 'zen-test:plugin-aware';
    }

    public function getDescription(): string
    {
        return 'Uses normal catalog plugin classes during console discovery.';
    }

    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        $output->writeln(Label::message());

        return 0;
    }
}
PHP
        );

        file_put_contents(
            $pluginRoot . '/Console/commands.php',
            <<<'PHP'
<?php

return [
    \Zencart\Plugins\Console\ZenTestPlugin\Commands\PluginAwareCommand::class,
];
PHP
        );

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $discovery = new PluginCommandDiscovery(
            DIR_FS_CATALOG . 'zc_plugins',
            $psr4Autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $kernel = new ConsoleKernel(
            null,
            $discovery,
            [],
            null,
            null,
            null,
            $psr4Autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'zen-test:plugin-aware']), $output);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('plugin-aware', stream_get_contents($stdout, -1, 0));
    }

    public function testCommandExceptionReturnsControlledFailureWithoutRawTraceByDefault(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        $registry = new CommandRegistry();
        $registry->register(new class extends ConsoleCommand {
            public function getName(): string
            {
                return 'explode';
            }

            public function getDescription(): string
            {
                return 'Throw an exception for testing.';
            }

            public function handle(ConsoleInput $input, ConsoleOutput $output): int
            {
                throw new \RuntimeException('sensitive failure details');
            }
        });

        $kernel = new ConsoleKernel($registry);
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'explode']), $output);
        $stderrOutput = stream_get_contents($stderr, -1, 0);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', stream_get_contents($stdout, -1, 0));
        $this->assertStringContainsString('Command failed: explode', $stderrOutput);
        $this->assertStringContainsString('Re-run with --verbose for more detail.', $stderrOutput);
        $this->assertStringNotContainsString('sensitive failure details', $stderrOutput);
        $this->assertStringNotContainsString('Stack trace', $stderrOutput);
    }

    public function testVerboseCommandExceptionShowsMessageWithoutTrace(): void
    {
        [, $stderr, $output] = $this->makeOutput();
        $registry = new CommandRegistry();
        $registry->register(new class extends ConsoleCommand {
            public function getName(): string
            {
                return 'explode';
            }

            public function getDescription(): string
            {
                return 'Throw an exception for testing.';
            }

            public function handle(ConsoleInput $input, ConsoleOutput $output): int
            {
                throw new \RuntimeException('verbose failure details');
            }
        });

        $kernel = new ConsoleKernel($registry);
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'explode', '--verbose']), $output);
        $stderrOutput = stream_get_contents($stderr, -1, 0);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Command failed: explode', $stderrOutput);
        $this->assertStringContainsString('RuntimeException: verbose failure details', $stderrOutput);
        $this->assertStringNotContainsString('Stack trace', $stderrOutput);
    }

    public function testBrokenPluginDefinitionWarnsAndStillAllowsCoreListCommand(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        (new TestFrameworkFilesystem())->installPlugin('zenTestPlugin', $this->catalogPath, DIR_FS_CATALOG);
        file_put_contents(
            $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0/Console/commands.php',
            "<?php\nthrow new RuntimeException('boom');\n"
        );

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $psr4Autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $kernel = new ConsoleKernel(null, $discovery);
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'list']), $output);
        $stderrOutput = stream_get_contents($stderr, -1, 0);
        $stdoutOutput = stream_get_contents($stdout, -1, 0);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Available commands:', $stdoutOutput);
        $this->assertStringContainsString('Warning: Failed loading plugin commands from zenTestPlugin/v1.0.0/Console/commands.php: boom', $stderrOutput);
        $this->assertStringNotContainsString($this->catalogPath, $stderrOutput);
    }

    public function testDuplicatePluginCommandNameIsWarnedAndCoreCommandWins(): void
    {
        [$stdout, $stderr, $output] = $this->makeOutput();
        (new TestFrameworkFilesystem())->installPlugin('zenTestPlugin', $this->catalogPath, DIR_FS_CATALOG);
        file_put_contents(
            $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0/Console/commands.php',
            <<<'PHP'
<?php
use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

return [
    new class extends ConsoleCommand {
        public function getName(): string
        {
            return 'list';
        }

        public function getDescription(): string
        {
            return 'Attempt to shadow the core list command.';
        }

        public function handle(ConsoleInput $input, ConsoleOutput $output): int
        {
            $output->writeln('plugin list shadow');
            return 0;
        }
    },
];
PHP
        );

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $psr4Autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $kernel = new ConsoleKernel(null, $discovery);
        $exitCode = $kernel->run(new ConsoleInput(['zc_cli.php', 'list']), $output);
        $stderrOutput = stream_get_contents($stderr, -1, 0);
        $stdoutOutput = stream_get_contents($stdout, -1, 0);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Warning: Console command name already registered: list', $stderrOutput);
        $this->assertStringContainsString('Available commands:', $stdoutOutput);
        $this->assertStringNotContainsString('plugin list shadow', $stdoutOutput);
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

    private function createPluginFixture(string $pluginKey): string
    {
        $pluginRoot = DIR_FS_CATALOG . 'zc_plugins/' . $pluginKey . '/v1.0.0';
        mkdir($pluginRoot . '/Console', 0777, true);
        file_put_contents(
            $pluginRoot . '/manifest.php',
            <<<'PHP'
<?php

return [
    'pluginVersion' => 'v1.0.0',
    'pluginName' => 'Fixture Plugin',
    'pluginDescription' => 'Fixture plugin',
    'pluginAuthor' => 'Zen Cart Development Team',
    'pluginId' => null,
    'zcVersions' => [],
    'changelog' => '',
    'github_repo' => '',
    'pluginGroups' => [],
];
PHP
        );

        $this->pluginRootsToRemove[] = $pluginRoot;

        return $pluginRoot;
    }

    private function removeDirectory(string $path): void
    {
        if ($path === '' || !is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $currentPath = $path . '/' . $item;
            if (is_dir($currentPath)) {
                $this->removeDirectory($currentPath);
                continue;
            }

            unlink($currentPath);
        }

        rmdir($path);
    }
}
