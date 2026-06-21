<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

declare(strict_types=1);

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\UnitTestBootstrap;
use Zencart\FileSystem\FileSystem;

class FileSystemPluginAdminPageTest extends TestCase
{
    private const TEST_PLUGIN = 'UnitTestAdminPagePlugin';
    private const TEST_VERSION = 'v1.0.0';

    private string $pluginRoot;

    public static function setUpBeforeClass(): void
    {
        UnitTestBootstrap::initialize();
        require_once DIR_FS_CATALOG . 'includes/classes/FileSystem.php';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->pluginRoot = DIR_FS_CATALOG . 'zc_plugins/' . self::TEST_PLUGIN . '/' . self::TEST_VERSION;
        $this->removeDirectory($this->pluginRoot);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->pluginRoot);
        parent::tearDown();
    }

    public function testReturnsNullWhenPluginAdminPageDoesNotExist(): void
    {
        mkdir($this->pluginRoot . '/admin', 0777, true);

        $page = (new FileSystem())->findPluginAdminPage($this->getInstalledPlugins(), 'display_logs');

        $this->assertNull($page);
    }

    public function testReturnsNullWhenInstalledPluginDirectoryIsMissing(): void
    {
        $page = (new FileSystem())->findPluginAdminPage($this->getInstalledPlugins(), 'display_logs');

        $this->assertNull($page);
    }

    private function getInstalledPlugins(): array
    {
        return [
            [
                'unique_key' => self::TEST_PLUGIN,
                'version' => self::TEST_VERSION,
            ],
        ];
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($directory);
    }
}
