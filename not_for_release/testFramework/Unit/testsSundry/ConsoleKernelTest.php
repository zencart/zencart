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
