<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\TestFrameworkFilesystem;
use Tests\Support\UnitTestBootstrap;
use Zencart\Console\PluginCommandDiscovery;

class PluginCommandDiscoveryTest extends TestCase
{
    protected $preserveGlobalState = false;

    private string $basePath;
    private string $catalogPath;

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir() . '/zc-console-plugin-' . uniqid('', true);
        $this->catalogPath = $this->basePath . '/catalog';
        mkdir($this->catalogPath . '/zc_plugins', 0777, true);

        (new TestFrameworkFilesystem())->installPlugin('zenTestPlugin', $this->catalogPath, DIR_FS_CATALOG);

        require_once DIR_FS_CATALOG . 'includes/classes/vendors/AuraAutoload/src/Loader.php';
        $psr4Autoloader = new \Aura\Autoload\Loader();
        $psr4Autoloader->register();
        require DIR_FS_CATALOG . 'includes/psr4Autoload.php';
        $this->autoloader = $psr4Autoloader;
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->basePath);
        parent::tearDown();
    }

    private \Aura\Autoload\Loader $autoloader;

    public function testDiscoversPluginCommandsFromConventionFile(): void
    {
        $discovery = new PluginCommandDiscovery($this->catalogPath . '/zc_plugins', $this->autoloader);

        $commands = $discovery->discover();

        $this->assertCount(1, $commands);
        $this->assertSame('zen-test:demo', $commands[0]->getName());
        $this->assertSame([], $discovery->getErrors());
    }

    public function testIgnoresPluginsThatAreNotInTrustedAllowlist(): void
    {
        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['someOtherPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertSame([], $discovery->getErrors());
    }

    public function testErrorsUsePluginRelativePathInsteadOfAbsoluteFilesystemPath(): void
    {
        file_put_contents(
            $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0/Console/commands.php',
            "<?php\nthrow new RuntimeException('boom');\n"
        );

        $discovery = new PluginCommandDiscovery($this->catalogPath . '/zc_plugins', $this->autoloader);
        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertCount(1, $discovery->getErrors());
        $this->assertStringContainsString('zenTestPlugin/v1.0.0/Console/commands.php', $discovery->getErrors()[0]);
        $this->assertStringNotContainsString($this->catalogPath, $discovery->getErrors()[0]);
    }

    public function testLoadsPluginRootAutoloaderBeforeResolvingCommands(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        mkdir($pluginRoot . '/vendor/ZenTestVendor/src', 0777, true);

        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            <<<'PHP'
<?php
/** @var \Aura\Autoload\Loader $psr4Autoloader */
$psr4Autoloader->addPrefix('ZenTestVendor', __DIR__ . '/vendor/ZenTestVendor/src');
PHP
        );

        file_put_contents(
            $pluginRoot . '/vendor/ZenTestVendor/src/Dependency.php',
            <<<'PHP'
<?php

namespace ZenTestVendor;

class Dependency
{
    public static function label(): string
    {
        return 'vendor-backed';
    }
}
PHP
        );

        file_put_contents(
            $pluginRoot . '/Console/Commands/VendorBackedCommand.php',
            <<<'PHP'
<?php

namespace Zencart\Plugins\Console\ZenTestPlugin\Commands;

use ZenTestVendor\Dependency;
use Zencart\Console\ConsoleCommand;
use Zencart\Console\ConsoleInput;
use Zencart\Console\ConsoleOutput;

class VendorBackedCommand extends ConsoleCommand
{
    public function __construct()
    {
        Dependency::label();
    }

    public function getName(): string
    {
        return 'zen-test:vendor';
    }

    public function getDescription(): string
    {
        return 'Loads through the plugin root autoloader.';
    }

    public function handle(ConsoleInput $input, ConsoleOutput $output): int
    {
        $output->writeln(Dependency::label());
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
    \Zencart\Plugins\Console\ZenTestPlugin\Commands\VendorBackedCommand::class,
];
PHP
        );

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertCount(1, $commands);
        $this->assertSame('zen-test:vendor', $commands[0]->getName());
        $this->assertSame([], $discovery->getErrors());
    }

    public function testDoesNotLoadPluginRootAutoloaderForUntrustedPlugin(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        $markerFile = $this->basePath . '/plugin-autoloader-marker.txt';

        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            "<?php\nfile_put_contents(" . var_export($markerFile, true) . ", 'loaded');\n"
        );

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['someOtherPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertFileDoesNotExist($markerFile);
        $this->assertSame([], $discovery->getErrors());
    }

    public function testDoesNotLoadPluginRootAutoloaderWithoutConsoleCommandsFile(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        $markerFile = $this->basePath . '/plugin-autoloader-marker.txt';

        unlink($pluginRoot . '/Console/commands.php');
        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            "<?php\nfile_put_contents(" . var_export($markerFile, true) . ", 'loaded');\n"
        );

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertFileDoesNotExist($markerFile);
        $this->assertSame([], $discovery->getErrors());
    }

    public function testSkipsPluginRootAutoloaderWhenNoAutoloaderIsAvailable(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        $markerFile = $this->basePath . '/plugin-autoloader-marker.txt';

        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            "<?php\nfile_put_contents(" . var_export($markerFile, true) . ", 'loaded');\n"
        );
        require_once DIR_FS_CATALOG . 'includes/classes/Console/ConsoleCommand.php';
        require_once DIR_FS_CATALOG . 'includes/classes/Console/ConsoleInput.php';
        require_once DIR_FS_CATALOG . 'includes/classes/Console/ConsoleOutput.php';
        file_put_contents(
            $pluginRoot . '/Console/commands.php',
            <<<'PHP'
<?php

return [
    new class extends \Zencart\Console\ConsoleCommand {
        public function getName(): string
        {
            return 'zen-test:demo';
        }

        public function getDescription(): string
        {
            return 'Self-contained test command.';
        }

        public function handle(\Zencart\Console\ConsoleInput $input, \Zencart\Console\ConsoleOutput $output): int
        {
            return 0;
        }
    },
];
PHP
        );

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            null,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertCount(1, $commands);
        $this->assertSame('zen-test:demo', $commands[0]->getName());
        $this->assertFileDoesNotExist($markerFile);
        $this->assertSame([], $discovery->getErrors());
    }

    public function testAutoloaderErrorsDoNotLeakAbsoluteFilesystemPaths(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        $autoloadFile = $pluginRoot . '/psr4Autoload.php';

        file_put_contents($autoloadFile, "<?php\nif (\n");

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertCount(1, $discovery->getErrors());
        $this->assertStringContainsString('zenTestPlugin/v1.0.0/psr4Autoload.php', $discovery->getErrors()[0]);
        $this->assertStringNotContainsString($autoloadFile, $discovery->getErrors()[0]);
    }

    public function testUnreadablePluginRootAutoloaderIsReportedWithoutAbortingDiscovery(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        $autoloadFile = $pluginRoot . '/psr4Autoload.php';

        file_put_contents($autoloadFile, "<?php\n");
        chmod($autoloadFile, 0000);
        if (is_readable($autoloadFile)) {
            $this->markTestSkipped('Unable to make psr4Autoload.php unreadable on this platform.');
        }

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertCount(1, $discovery->getErrors());
        $this->assertStringContainsString('zenTestPlugin/v1.0.0/psr4Autoload.php', $discovery->getErrors()[0]);
        $this->assertStringNotContainsString($autoloadFile, $discovery->getErrors()[0]);
    }

    public function testAutoloaderErrorsSanitizeNestedPluginPaths(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        $nestedFile = $pluginRoot . '/vendor/bootstrap.php';

        mkdir($pluginRoot . '/vendor', 0777, true);
        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            "<?php\nrequire __DIR__ . '/vendor/bootstrap.php';\n"
        );
        file_put_contents(
            $nestedFile,
            "<?php\nthrow new RuntimeException(__FILE__);\n"
        );

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertCount(1, $discovery->getErrors());
        $this->assertStringContainsString('zenTestPlugin/v1.0.0/vendor/bootstrap.php', $discovery->getErrors()[0]);
        $this->assertStringNotContainsString($nestedFile, $discovery->getErrors()[0]);
    }

    public function testAutoloaderErrorsSanitizeWindowsStyleNestedPluginPaths(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        $windowsNestedFile = str_replace('/', '\\', $pluginRoot . '/vendor/bootstrap.php');

        mkdir($pluginRoot . '/vendor', 0777, true);
        file_put_contents(
            $pluginRoot . '/psr4Autoload.php',
            "<?php\nthrow new RuntimeException(" . var_export($windowsNestedFile, true) . ");\n"
        );

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertCount(1, $discovery->getErrors());
        $this->assertStringContainsString('zenTestPlugin/v1.0.0/vendor/bootstrap.php', $discovery->getErrors()[0]);
        $this->assertStringNotContainsString($windowsNestedFile, $discovery->getErrors()[0]);
    }

    public function testCommandFileErrorsDoNotLeakAbsoluteFilesystemPaths(): void
    {
        $commandFile = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0/Console/commands.php';

        file_put_contents($commandFile, "<?php\nif (\n");

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertCount(1, $discovery->getErrors());
        $this->assertStringContainsString('zenTestPlugin/v1.0.0/Console/commands.php', $discovery->getErrors()[0]);
        $this->assertStringNotContainsString($commandFile, $discovery->getErrors()[0]);
    }

    public function testUnreadableCommandFileIsReportedWithoutAbortingDiscovery(): void
    {
        $commandFile = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0/Console/commands.php';

        chmod($commandFile, 0000);
        if (is_readable($commandFile)) {
            $this->markTestSkipped('Unable to make Console/commands.php unreadable on this platform.');
        }

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertCount(1, $discovery->getErrors());
        $this->assertStringContainsString('zenTestPlugin/v1.0.0/Console/commands.php', $discovery->getErrors()[0]);
        $this->assertStringNotContainsString($commandFile, $discovery->getErrors()[0]);
    }

    public function testCommandFileErrorsSanitizeNestedPluginPaths(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        $nestedFile = $pluginRoot . '/Console/bootstrap.php';

        file_put_contents(
            $pluginRoot . '/Console/commands.php',
            "<?php\nrequire __DIR__ . '/bootstrap.php';\n"
        );
        file_put_contents(
            $nestedFile,
            "<?php\nthrow new RuntimeException(__FILE__);\n"
        );

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertCount(1, $discovery->getErrors());
        $this->assertStringContainsString('zenTestPlugin/v1.0.0/Console/bootstrap.php', $discovery->getErrors()[0]);
        $this->assertStringNotContainsString($nestedFile, $discovery->getErrors()[0]);
    }

    public function testCommandFileErrorsSanitizeWindowsStyleNestedPluginPaths(): void
    {
        $pluginRoot = $this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0';
        $windowsNestedFile = str_replace('/', '\\', $pluginRoot . '/Console/bootstrap.php');

        file_put_contents(
            $pluginRoot . '/Console/commands.php',
            "<?php\nthrow new RuntimeException(" . var_export($windowsNestedFile, true) . ");\n"
        );

        $discovery = new PluginCommandDiscovery(
            $this->catalogPath . '/zc_plugins',
            $this->autoloader,
            ['zenTestPlugin' => 'v1.0.0']
        );

        $commands = $discovery->discover();

        $this->assertSame([], $commands);
        $this->assertCount(1, $discovery->getErrors());
        $this->assertStringContainsString('zenTestPlugin/v1.0.0/Console/bootstrap.php', $discovery->getErrors()[0]);
        $this->assertStringNotContainsString($windowsNestedFile, $discovery->getErrors()[0]);
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
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

            chmod($currentPath, 0666);
            unlink($currentPath);
        }

        rmdir($path);
    }
}
