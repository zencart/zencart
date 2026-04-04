<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;

class RunnerConfigureFilesTest extends TestCase
{
    protected $runTestInSeparateProcess = true;
    protected $preserveGlobalState = false;

    protected function tearDown(): void
    {
        putenv('GITHUB_WORKSPACE');
        putenv('ZC_TEST_WORKER');
        putenv('TEST_TOKEN');
        putenv('ZC_TEST_DB_DATABASE');

        parent::tearDown();
    }

    public function testStoreRunnerConfigureUsesDefaultDatabaseAndLogDirectory(): void
    {
        putenv('GITHUB_WORKSPACE=/tmp/zc-runner-config');

        require dirname(__DIR__, 2) . '/Support/configs/runner.store.configure.php';

        $this->assertSame('/tmp/zc-runner-config/', DIR_FS_CATALOG);
        $this->assertSame('db', DB_DATABASE);
        $this->assertSame('/tmp/zc-runner-config/logs', DIR_FS_LOGS);
    }

    public function testStoreRunnerConfigureUsesWorkerScopedDatabaseAndLogDirectory(): void
    {
        putenv('GITHUB_WORKSPACE=/tmp/zc-runner-config');
        putenv('ZC_TEST_WORKER=2');

        require dirname(__DIR__, 2) . '/Support/configs/runner.store.configure.php';

        $this->assertSame('db_2', DB_DATABASE);
        $this->assertSame('/tmp/zc-runner-config/logs/2', DIR_FS_LOGS);
    }

    public function testAdminRunnerConfigureFallsBackToTestTokenForWorkerScopedPaths(): void
    {
        putenv('GITHUB_WORKSPACE=/tmp/zc-runner-config');
        putenv('TEST_TOKEN=worker-2');

        require dirname(__DIR__, 2) . '/Support/configs/runner.admin.configure.php';

        $this->assertSame('/tmp/zc-runner-config/', DIR_FS_CATALOG);
        $this->assertSame('db_worker_2', DB_DATABASE);
        $this->assertSame('/tmp/zc-runner-config/logs/worker_2', DIR_FS_LOGS);
    }
}
