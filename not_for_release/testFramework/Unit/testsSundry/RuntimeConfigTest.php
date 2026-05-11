<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;

class RuntimeConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        putenv('ZC_TEST_DB_BASE_NAME');
        require_once dirname(__DIR__, 2) . '/Support/configs/runtime_config.php';
    }

    protected function tearDown(): void
    {
        putenv('GITHUB_WORKSPACE');
        putenv('ZC_TEST_DB_BASE_NAME');
        putenv('ZC_TEST_DB_DATABASE');
        putenv('ZC_TEST_USE_MAILSERVER');
        putenv('ZC_TEST_MAILSERVER_HOST');
        putenv('ZC_TEST_MAILSERVER_PORT');
        putenv('ZC_TEST_MAILSERVER_USER');
        putenv('ZC_TEST_MAILSERVER_PASSWORD');
        putenv('ZC_TEST_WORKER');
        putenv('TEST_TOKEN');

        parent::tearDown();
    }

    public function testCatalogPathUsesGithubWorkspaceWhenAvailable(): void
    {
        putenv('GITHUB_WORKSPACE=/tmp/github-workspace');

        $this->assertSame('/tmp/github-workspace/', zc_test_config_catalog_path());
    }

    public function testCatalogPathFallsBackToRepositoryRoot(): void
    {
        putenv('GITHUB_WORKSPACE');

        $expected = rtrim(str_replace('\\', '/', realpath(dirname(__DIR__, 4)) ?: dirname(__DIR__, 4)), '/') . '/';

        $this->assertSame($expected, zc_test_config_catalog_path());
    }

    public function testDatabaseNameDefaultsWhenNoWorkerIsConfigured(): void
    {
        $this->assertNull(zc_test_config_worker_token());
        $this->assertSame('db_testing', zc_test_config_database_name('db_testing'));
    }

    public function testDatabaseNameUsesExplicitOverrideWhenProvided(): void
    {
        putenv('ZC_TEST_DB_DATABASE=db_testing_override');
        putenv('ZC_TEST_DB_BASE_NAME=db_testing_base');
        putenv('ZC_TEST_WORKER=5');

        $this->assertSame('db_testing_override', zc_test_config_database_name('db_testing'));
    }

    public function testDatabaseNameUsesBaseNameOverrideWhenProvided(): void
    {
        putenv('ZC_TEST_DB_BASE_NAME=db_testing_base');

        $this->assertSame('db_testing_base', zc_test_config_database_name('db_testing'));
    }

    public function testDatabaseNameUsesBaseNameWithWorkerSuffixWhenConfigured(): void
    {
        putenv('ZC_TEST_DB_BASE_NAME=db_testing_base');
        putenv('ZC_TEST_WORKER=3');

        $this->assertSame('db_testing_base_3', zc_test_config_database_name('db_testing'));
    }

    public function testDatabaseNameUsesWorkerSuffixWhenConfigured(): void
    {
        putenv('ZC_TEST_WORKER=3');

        $this->assertSame('3', zc_test_config_worker_token());
        $this->assertSame('db_testing_3', zc_test_config_database_name('db_testing'));
    }

    public function testDatabaseNameFallsBackToTestToken(): void
    {
        putenv('TEST_TOKEN=worker-2');

        $this->assertSame('worker_2', zc_test_config_worker_token());
        $this->assertSame('db_testing_worker_2', zc_test_config_database_name('db_testing'));
    }

    public function testWorkerTokenReturnsNullWhenNormalizationRemovesAllContent(): void
    {
        putenv('ZC_TEST_WORKER=---');

        $this->assertNull(zc_test_config_worker_token());
    }

    public function testMailserverOptionsDefaultToDisabledLocalSmtpCatcherSettings(): void
    {
        $this->assertSame(
            [
                'use-mailserver' => false,
                'mailserver-port' => 1025,
                'mailserver-host' => 'localhost',
                'mailserver-user' => 'ddev',
                'mailserver-password' => 'mailpit',
            ],
            zc_test_config_mailserver_options()
        );
    }

    public function testMailserverOptionsCanBeConfiguredFromEnvironment(): void
    {
        putenv('ZC_TEST_USE_MAILSERVER=true');
        putenv('ZC_TEST_MAILSERVER_HOST=mailpit');
        putenv('ZC_TEST_MAILSERVER_PORT=2525');
        putenv('ZC_TEST_MAILSERVER_USER=test-user');
        putenv('ZC_TEST_MAILSERVER_PASSWORD=test-password');

        $this->assertSame(
            [
                'use-mailserver' => true,
                'mailserver-port' => 2525,
                'mailserver-host' => 'mailpit',
                'mailserver-user' => 'test-user',
                'mailserver-password' => 'test-password',
            ],
            zc_test_config_mailserver_options()
        );
    }

    public function testProgressFileDefaultsWhenNoWorkerIsConfigured(): void
    {
        $this->assertSame('/tmp/zencart/progress.json', zc_test_config_progress_file('/tmp/zencart'));
    }

    public function testProgressFileUsesWorkerSuffixWhenConfigured(): void
    {
        putenv('ZC_TEST_WORKER=3');

        $this->assertSame('/tmp/zencart/progress_3.json', zc_test_config_progress_file('/tmp/zencart'));
    }

    public function testProgressFileFallsBackToTestToken(): void
    {
        putenv('TEST_TOKEN=worker-2');

        $this->assertSame('/tmp/zencart/progress_worker_2.json', zc_test_config_progress_file('/tmp/zencart'));
    }

    public function testArtifactDirectoryDefaultsWhenNoWorkerIsConfigured(): void
    {
        $this->assertSame(
            '/tmp/zencart/not_for_release/testFramework/logs/console/store/',
            zc_test_config_artifact_directory('/tmp/zencart', 'store')
        );
    }

    public function testArtifactDirectoryUsesWorkerSuffixWhenConfigured(): void
    {
        putenv('ZC_TEST_WORKER=3');

        $this->assertSame(
            '/tmp/zencart/not_for_release/testFramework/logs/console/store/3/',
            zc_test_config_artifact_directory('/tmp/zencart', 'store')
        );
    }

    public function testArtifactDirectoryFallsBackToTestToken(): void
    {
        putenv('TEST_TOKEN=worker-2');

        $this->assertSame(
            '/tmp/zencart/not_for_release/testFramework/logs/console/admin/worker_2/',
            zc_test_config_artifact_directory('/tmp/zencart', 'admin')
        );
    }

    public function testPluginDirectoryDefaultsWhenNoWorkerIsConfigured(): void
    {
        $this->assertSame(
            '/tmp/zencart/zc_plugins/ExamplePlugin',
            zc_test_config_plugin_directory('/tmp/zencart', 'ExamplePlugin')
        );
    }

    public function testLogDirectoryDefaultsWhenNoWorkerIsConfigured(): void
    {
        $this->assertSame('/tmp/zencart/logs', zc_test_config_log_directory('/tmp/zencart'));
    }

    public function testLogDirectoryUsesWorkerSuffixWhenConfigured(): void
    {
        putenv('ZC_TEST_WORKER=3');

        $this->assertSame('/tmp/zencart/logs/3', zc_test_config_log_directory('/tmp/zencart'));
    }

    public function testLogDirectoryFallsBackToTestToken(): void
    {
        putenv('TEST_TOKEN=worker-2');

        $this->assertSame('/tmp/zencart/logs/worker_2', zc_test_config_log_directory('/tmp/zencart'));
    }

    public function testPluginDirectoryUsesWorkerSuffixWhenConfigured(): void
    {
        putenv('ZC_TEST_WORKER=3');

        $this->assertSame(
            '/tmp/zencart/zc_plugins/3/ExamplePlugin',
            zc_test_config_plugin_directory('/tmp/zencart', 'ExamplePlugin')
        );
    }

    public function testPluginDirectoryFallsBackToTestToken(): void
    {
        putenv('TEST_TOKEN=worker-2');

        $this->assertSame(
            '/tmp/zencart/zc_plugins/worker_2/ExamplePlugin',
            zc_test_config_plugin_directory('/tmp/zencart', 'ExamplePlugin')
        );
    }
}
