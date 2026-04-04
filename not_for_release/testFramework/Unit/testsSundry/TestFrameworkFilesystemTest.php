<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\TestFrameworkFilesystem;

class TestFrameworkFilesystemTest extends TestCase
{
    private string $rootPath;
    private string $catalogPath;
    private TestFrameworkFilesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $basePath = sys_get_temp_dir() . '/zc-test-filesystem-' . uniqid('', true);
        $this->rootPath = $basePath . '/repo';
        $this->catalogPath = $basePath . '/catalog';
        $this->filesystem = new TestFrameworkFilesystem();

        mkdir($this->rootPath . '/not_for_release/testFramework/Support/plugins/zenTestPlugin/v1.0.0', 0777, true);
        mkdir($this->catalogPath . '/logs', 0777, true);
        file_put_contents(
            $this->rootPath . '/not_for_release/testFramework/Support/plugins/zenTestPlugin/v1.0.0/manifest.php',
            "<?php\nreturn [];\n"
        );
    }

    protected function tearDown(): void
    {
        putenv('ZC_TEST_WORKER');
        putenv('TEST_TOKEN');
        $this->removeDirectory(dirname($this->rootPath));
        parent::tearDown();
    }

    public function testListDebugLogFilesReturnsMatchingFiles(): void
    {
        file_put_contents($this->catalogPath . '/logs/myDEBUG-test.log', 'debug');
        file_put_contents($this->catalogPath . '/logs/not-debug.log', 'ignore');

        $files = $this->filesystem->listDebugLogFiles($this->catalogPath);

        $this->assertCount(1, $files);
        $this->assertSame($this->catalogPath . '/logs/myDEBUG-test.log', $files[0]);
    }

    public function testListDebugLogFilesUsesWorkerScopedLogDirectoryWhenConfigured(): void
    {
        putenv('ZC_TEST_WORKER=2');

        mkdir($this->catalogPath . '/logs/2', 0777, true);
        file_put_contents($this->catalogPath . '/logs/2/myDEBUG-test.log', 'debug');
        file_put_contents($this->catalogPath . '/logs/myDEBUG-root.log', 'ignore');

        $files = $this->filesystem->listDebugLogFiles($this->catalogPath);

        $this->assertCount(1, $files);
        $this->assertSame($this->catalogPath . '/logs/2/myDEBUG-test.log', $files[0]);
    }

    public function testInstallPluginCopiesFixtureTreeToCatalog(): void
    {
        $this->filesystem->installPlugin('zenTestPlugin', $this->catalogPath, $this->rootPath);

        $this->assertFileExists($this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0/manifest.php');
    }

    public function testInstallPluginThrowsForMissingFixtureDirectory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Plugin fixture directory not found');

        $this->filesystem->installPlugin('missingPlugin', $this->catalogPath, $this->rootPath);
    }

    public function testRemovePluginDeletesVersionAndEmptyParentDirectory(): void
    {
        mkdir($this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0', 0777, true);
        file_put_contents($this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0/manifest.php', 'fixture');

        $this->filesystem->removePlugin('zenTestPlugin', 'v1.0.0', $this->catalogPath);

        $this->assertDirectoryDoesNotExist($this->catalogPath . '/zc_plugins/zenTestPlugin');
    }

    public function testRemovePluginKeepsParentWhenOtherVersionsExist(): void
    {
        mkdir($this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0', 0777, true);
        mkdir($this->catalogPath . '/zc_plugins/zenTestPlugin/v2.0.0', 0777, true);

        $this->filesystem->removePlugin('zenTestPlugin', 'v1.0.0', $this->catalogPath);

        $this->assertDirectoryDoesNotExist($this->catalogPath . '/zc_plugins/zenTestPlugin/v1.0.0');
        $this->assertDirectoryExists($this->catalogPath . '/zc_plugins/zenTestPlugin/v2.0.0');
    }

    public function testInstallPluginUsesWorkerScopedPluginDirectoryWhenConfigured(): void
    {
        putenv('ZC_TEST_WORKER=2');

        $this->filesystem->installPlugin('zenTestPlugin', $this->catalogPath, $this->rootPath);

        $this->assertFileExists($this->catalogPath . '/zc_plugins/2/zenTestPlugin/v1.0.0/manifest.php');
    }

    public function testRemovePluginUsesWorkerScopedPluginDirectoryWhenConfigured(): void
    {
        putenv('ZC_TEST_WORKER=2');

        mkdir($this->catalogPath . '/zc_plugins/2/zenTestPlugin/v1.0.0', 0777, true);
        file_put_contents($this->catalogPath . '/zc_plugins/2/zenTestPlugin/v1.0.0/manifest.php', 'fixture');

        $this->filesystem->removePlugin('zenTestPlugin', 'v1.0.0', $this->catalogPath);

        $this->assertDirectoryDoesNotExist($this->catalogPath . '/zc_plugins/2/zenTestPlugin');
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
