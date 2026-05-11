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
        putenv('ZC_TEST_DB_BASE_NAME');
        putenv('ZC_TEST_DB_DATABASE');
        putenv('ZC_TEST_USE_MAILSERVER');
        putenv('ZC_TEST_MAILSERVER_HOST');
        putenv('ZC_TEST_MAILSERVER_PORT');
        putenv('ZC_TEST_MAILSERVER_USER');
        putenv('ZC_TEST_MAILSERVER_PASSWORD');

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
        $config = dirname(__DIR__, 2) . '/Support/configs/runner.store.configure.php';
        $command = sprintf(
            'GITHUB_WORKSPACE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_WORKER=%s php -r %s',
            escapeshellarg('/tmp/zc-runner-config'),
            escapeshellarg('zencarttests'),
            escapeshellarg('2'),
            escapeshellarg(sprintf(
                'require %s; echo DB_DATABASE . PHP_EOL . DIR_FS_LOGS . PHP_EOL;',
                var_export($config, true)
            ))
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertSame(['zencarttests_2', '/tmp/zc-runner-config/logs/2'], $output);
    }

    public function testAdminRunnerConfigureFallsBackToTestTokenForWorkerScopedPaths(): void
    {
        $config = dirname(__DIR__, 2) . '/Support/configs/runner.admin.configure.php';
        $command = sprintf(
            'GITHUB_WORKSPACE=%s ZC_TEST_DB_BASE_NAME=%s TEST_TOKEN=%s php -r %s',
            escapeshellarg('/tmp/zc-runner-config'),
            escapeshellarg('zencarttests'),
            escapeshellarg('worker-2'),
            escapeshellarg(sprintf(
                'require %s; echo DIR_FS_CATALOG . PHP_EOL . DB_DATABASE . PHP_EOL . DIR_FS_LOGS . PHP_EOL;',
                var_export($config, true)
            ))
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertSame(
            ['/tmp/zc-runner-config/', 'zencarttests_worker_2', '/tmp/zc-runner-config/logs/worker_2'],
            $output
        );
    }

    public function testMainRunnerConfigureUsesMailserverEnvironmentOverrides(): void
    {
        putenv('ZC_TEST_USE_MAILSERVER=1');
        putenv('ZC_TEST_MAILSERVER_HOST=mailpit');
        putenv('ZC_TEST_MAILSERVER_PORT=2525');
        putenv('ZC_TEST_MAILSERVER_USER=test-user');
        putenv('ZC_TEST_MAILSERVER_PASSWORD=test-password');

        $config = require dirname(__DIR__, 2) . '/Support/configs/runner.main.configure.php';

        $this->assertSame(
            [
                'use-mailserver' => true,
                'mailserver-port' => 2525,
                'mailserver-host' => 'mailpit',
                'mailserver-user' => 'test-user',
                'mailserver-password' => 'test-password',
            ],
            $config
        );
    }
}
