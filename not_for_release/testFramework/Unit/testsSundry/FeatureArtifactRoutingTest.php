<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Support\zcFeatureTestCase;

final class FeatureArtifactRoutingHarness extends zcFeatureTestCase
{
    public static function copyArtifact(string $file): void
    {
        parent::moveLogFileForArtifacts($file);
    }

    public static function clearArtifactsAndLogs(): void
    {
        parent::removeLogFiles();
    }
}

class FeatureArtifactRoutingTest extends TestCase
{
    private static string $rootPath;
    private static bool $ownsRootPath = false;

    public static function setUpBeforeClass(): void
    {
        self::$rootPath = defined('ROOTCWD')
            ? ROOTCWD
            : sys_get_temp_dir() . '/zc-feature-artifacts-' . uniqid('', true) . '/';

        self::$ownsRootPath = !defined('ROOTCWD');

        if (!is_dir(self::$rootPath . 'logs')) {
            mkdir(self::$rootPath . 'logs', 0777, true);
        }

        if (!defined('ROOTCWD')) {
            define('ROOTCWD', self::$rootPath);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (!self::$ownsRootPath) {
            return;
        }

        self::removeDirectory(rtrim(self::$rootPath, '/'));
    }

    protected function tearDown(): void
    {
        putenv('ZC_TEST_WORKER');
        putenv('TEST_TOKEN');

        self::removeDirectoryContents(rtrim(self::$rootPath, '/'));

        parent::tearDown();
    }

    public function testMoveLogFileForArtifactsCopiesStoreLogToDefaultArtifactDirectory(): void
    {
        $sourceFile = ROOTCWD . 'logs/myDEBUG-test.log';
        file_put_contents($sourceFile, 'store log');

        FeatureArtifactRoutingHarness::copyArtifact($sourceFile);

        $artifactFile = ROOTCWD . 'not_for_release/testFramework/logs/console/store/myDEBUG-test.log';
        $this->assertFileExists($artifactFile);
        $this->assertSame('store log', file_get_contents($artifactFile));
    }

    public function testMoveLogFileForArtifactsCopiesAdminLogToWorkerScopedArtifactDirectory(): void
    {
        putenv('ZC_TEST_WORKER=2');

        mkdir(ROOTCWD . 'logs/2', 0777, true);
        $sourceFile = ROOTCWD . 'logs/2/myDEBUG-adm-test.log';
        file_put_contents($sourceFile, 'admin log');

        FeatureArtifactRoutingHarness::copyArtifact($sourceFile);

        $artifactFile = ROOTCWD . 'not_for_release/testFramework/logs/console/admin/2/myDEBUG-adm-test.log';
        $this->assertFileExists($artifactFile);
        $this->assertSame('admin log', file_get_contents($artifactFile));
    }

    public function testMoveLogFileForArtifactsFallsBackToTestTokenScopedArtifactDirectory(): void
    {
        putenv('TEST_TOKEN=worker-2');

        mkdir(ROOTCWD . 'logs/worker_2', 0777, true);
        $sourceFile = ROOTCWD . 'logs/worker_2/myDEBUG-test-token.log';
        file_put_contents($sourceFile, 'token log');

        FeatureArtifactRoutingHarness::copyArtifact($sourceFile);

        $artifactFile = ROOTCWD . 'not_for_release/testFramework/logs/console/store/worker_2/myDEBUG-test-token.log';
        $this->assertFileExists($artifactFile);
        $this->assertSame('token log', file_get_contents($artifactFile));
    }

    public function testRemoveLogFilesOnlyTouchesWorkerScopedLogDirectory(): void
    {
        putenv('ZC_TEST_WORKER=2');

        mkdir(ROOTCWD . 'logs/2', 0777, true);
        file_put_contents(ROOTCWD . 'logs/2/myDEBUG-worker.log', 'worker log');
        file_put_contents(ROOTCWD . 'logs/myDEBUG-root.log', 'root log');

        FeatureArtifactRoutingHarness::clearArtifactsAndLogs();

        $this->assertFileDoesNotExist(ROOTCWD . 'logs/2/myDEBUG-worker.log');
        $this->assertFileExists(ROOTCWD . 'logs/myDEBUG-root.log');
        $this->assertFileExists(ROOTCWD . 'not_for_release/testFramework/logs/console/store/2/myDEBUG-worker.log');
    }

    private static function removeDirectoryContents(string $path): void
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
                self::removeDirectory($currentPath);
                continue;
            }

            unlink($currentPath);
        }
    }

    private static function removeDirectory(string $path): void
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
                self::removeDirectory($currentPath);
                continue;
            }

            unlink($currentPath);
        }

        rmdir($path);
    }
}
