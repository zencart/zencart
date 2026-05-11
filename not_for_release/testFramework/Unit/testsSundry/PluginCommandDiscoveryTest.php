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

            unlink($currentPath);
        }

        rmdir($path);
    }
}
