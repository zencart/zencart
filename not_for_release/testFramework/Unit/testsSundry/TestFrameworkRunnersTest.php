<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Tests\Unit\testsSundry;

use PHPUnit\Framework\TestCase;
use Tests\Services\MigrationsRunner;
use Tests\Services\SeederRunner;
use Tests\Services\TestFrameworkRunnerException;

class TestFrameworkRunnersTest extends TestCase
{
    private string $rootPath;
    private array $tempPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        require_once __DIR__ . '/../fixtures/Services/ValidTestSeeder.php';
        require_once __DIR__ . '/../fixtures/Services/InvalidTestSeeder.php';

        $this->rootPath = realpath(dirname(__DIR__, 4)) ?: dirname(__DIR__, 4);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempPaths as $path) {
            $this->removeDirectory($path);
        }

        $this->tempPaths = [];

        parent::tearDown();
    }

    public function testSeederRunnerPassesParametersToValidSeeder(): void
    {
        \Seeders\ValidTestSeeder::$receivedParameters = [];

        $runner = new SeederRunner();
        $runner->run('ValidTestSeeder', ['feature' => 'mailpit']);

        $this->assertSame(['feature' => 'mailpit'], \Seeders\ValidTestSeeder::$receivedParameters);
    }

    public function testSeederRunnerThrowsForMissingSeederClass(): void
    {
        $this->expectException(TestFrameworkRunnerException::class);
        $this->expectExceptionMessage('Seeder class "\\Seeders\\MissingSeeder" could not be loaded.');

        (new SeederRunner())->run('MissingSeeder');
    }

    public function testSeederRunnerThrowsWhenSeederDoesNotImplementContract(): void
    {
        $this->expectException(TestFrameworkRunnerException::class);
        $this->expectExceptionMessage('must implement Tests\Services\Contracts\TestSeederInterface');

        (new SeederRunner())->run('InvalidTestSeeder');
    }

    public function testMigrationsRunnerExecutesDownThenUp(): void
    {
        $directory = sys_get_temp_dir() . '/zc-migration-valid-' . uniqid('', true);
        mkdir($directory, 0777, true);
        copy(__DIR__ . '/../fixtures/Services/ValidMigration_migration.php', $directory . '/ValidMigration_migration.php');

        try {
            require_once $directory . '/ValidMigration_migration.php';
            \Migrations\CreateValidMigrationTable::$calls = [];

            $runner = new MigrationsRunner($directory);
            $runner->run();

            $this->assertSame(['down', 'up'], \Migrations\CreateValidMigrationTable::$calls);
        } finally {
            unlink($directory . '/ValidMigration_migration.php');
            rmdir($directory);
        }
    }

    public function testMigrationsRunnerThrowsWhenClassIsMissingForMigrationFile(): void
    {
        $directory = sys_get_temp_dir() . '/zc-migration-missing-' . uniqid('', true);
        mkdir($directory, 0777, true);
        file_put_contents($directory . '/Missing_migration.php', "<?php\n");

        try {
            $this->expectException(TestFrameworkRunnerException::class);
            $this->expectExceptionMessage('Migration class "Migrations\\CreateMissingTable" was not found');

            (new MigrationsRunner($directory))->run();
        } finally {
            unlink($directory . '/Missing_migration.php');
            rmdir($directory);
        }
    }

    public function testMigrationsRunnerThrowsWhenMigrationDoesNotImplementContract(): void
    {
        $directory = sys_get_temp_dir() . '/zc-migration-invalid-' . uniqid('', true);
        $migrationName = 'InvalidContract' . str_replace('.', '', uniqid('', true));
        $fileName = $migrationName . '_migration.php';
        $className = 'Create' . $migrationName . 'Table';

        mkdir($directory, 0777, true);
        file_put_contents(
            $directory . '/' . $fileName,
            "<?php\n\nnamespace Migrations;\n\nclass $className\n{\n}\n"
        );

        try {
            $this->expectException(TestFrameworkRunnerException::class);
            $this->expectExceptionMessage('must implement Tests\Services\Contracts\TestMigrationInterface');

            (new MigrationsRunner($directory))->run();
        } finally {
            unlink($directory . '/' . $fileName);
            rmdir($directory);
        }
    }

    public function testPrepareWorkerDatabasesDryRunPrintsPlannedDatabases(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_TEST_ENV_FILE=%s env -u ZC_TEST_DB_BASE_NAME -u ZC_TEST_DB_WORKERS -u ZC_TEST_DB_INCLUDE_BASE -u ZC_FEATURE_PARALLEL_PROCESSES bash %s --dry-run --base %s --workers %d',
            escapeshellarg('runner'),
            escapeshellarg('/dev/null'),
            escapeshellarg($script),
            escapeshellarg('db_testing'),
            2
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Dry run for 3 planned test database(s) on 127.0.0.1:3306 for user root.', $output);
        $this->assertContains('RESET db_testing', $output);
        $this->assertContains('RESET db_testing_1', $output);
        $this->assertContains('RESET db_testing_2', $output);
        $this->assertContains('Planned databases:', $output);
    }

    public function testPrepareWorkerDatabasesDryRunCanSkipBaseDatabase(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_TEST_ENV_FILE=%s bash %s --dry-run --skip-base --base %s --workers %d',
            escapeshellarg('runner'),
            escapeshellarg('/dev/null'),
            escapeshellarg($script),
            escapeshellarg('db_testing'),
            2
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Dry run for 2 planned test database(s) on 127.0.0.1:3306 for user root.', $output);
        $this->assertFalse(in_array('RESET db_testing', $output, true));
        $this->assertContains('RESET db_testing_1', $output);
        $this->assertContains('RESET db_testing_2', $output);
    }

    public function testPrepareWorkerDatabasesHelpPrintsUsage(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $command = sprintf('ZC_TEST_ENV_FILE=%s bash %s --help', escapeshellarg('/dev/null'), escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Usage: prepare-worker-databases.sh [--base NAME] [--workers COUNT] [--skip-base] [--dry-run]', $output);
        $this->assertStringContainsString('--dry-run', implode(PHP_EOL, $output));
    }

    public function testPrepareWorkerDatabasesRejectsInvalidWorkerCount(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $command = sprintf(
            'ZC_TEST_ENV_FILE=%s bash %s --dry-run --workers %s',
            escapeshellarg('/dev/null'),
            escapeshellarg($script),
            escapeshellarg('0')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(2, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Worker count must be a positive integer.', $output);
    }

    public function testPrepareWorkerDatabasesRejectsUnknownOption(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $command = sprintf('ZC_TEST_ENV_FILE=%s bash %s --nope', escapeshellarg('/dev/null'), escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(2, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Unknown option: --nope', $output);
        $this->assertContains('Usage: prepare-worker-databases.sh [--base NAME] [--workers COUNT] [--skip-base] [--dry-run]', $output);
    }

    public function testPrepareWorkerDatabasesRejectsInvalidBaseDatabaseName(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $command = sprintf(
            'ZC_TEST_ENV_FILE=%s bash %s --dry-run --base %s',
            escapeshellarg('/dev/null'),
            escapeshellarg($script),
            escapeshellarg('bad`name')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(2, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Database name must contain only letters, numbers, and underscores: bad`name', $output);
    }

    public function testPrepareWorkerDatabasesDryRunUsesEnvironmentOverrides(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $envFile = tempnam(sys_get_temp_dir(), 'zc-test-env-');
        file_put_contents(
            $envFile,
            implode(
                PHP_EOL,
                [
                    'ZC_TEST_DB_BASE_NAME=ignored_testing',
                    'ZC_TEST_DB_WORKERS=9',
                    'ZC_TEST_DB_INCLUDE_BASE=1',
                    'ZC_TEST_DB_HOST=ignored-host',
                    'ZC_TEST_DB_PORT=9999',
                    'ZC_TEST_DB_USER=ignored-user',
                    '',
                ]
            )
        );

        try {
            $command = sprintf(
                'ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s ZC_TEST_DB_HOST=%s ZC_TEST_DB_PORT=%s ZC_TEST_DB_USER=%s bash %s --dry-run',
                escapeshellarg($envFile),
                escapeshellarg('ci_testing'),
                escapeshellarg('2'),
                escapeshellarg('0'),
                escapeshellarg('db-host'),
                escapeshellarg('3307'),
                escapeshellarg('ci-user'),
                escapeshellarg($script)
            );

            exec($command . ' 2>&1', $output, $exitCode);

            $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
            $this->assertContains('Dry run for 2 planned test database(s) on db-host:3307 for user ci-user.', $output);
            $this->assertFalse(in_array('RESET ci_testing', $output, true));
            $this->assertContains('RESET ci_testing_1', $output);
            $this->assertContains('RESET ci_testing_2', $output);
        } finally {
            unlink($envFile);
        }
    }

    public function testRunFeatureTestsCiLocalPromotesBaseCliOptionToEnvironment(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-feature-tests-ci-local.sh';
        $command = sprintf(
            'ZC_TEST_ENV_FILE=%s USER=%s IS_DDEV_PROJECT= bash %s --dry-run --base %s --workers %s',
            escapeshellarg('/dev/null'),
            escapeshellarg('runner'),
            escapeshellarg($script),
            escapeshellarg('db_local'),
            escapeshellarg('2')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Database: db_local', $output);
        $this->assertFalse(in_array('RESET db_local', $output, true));
        $this->assertContains('RESET db_local_1', $output);
        $this->assertContains('RESET db_local_2', $output);
    }

    public function testPrepareWorkerDatabasesDryRunLoadsOverridesFromEnvironmentFile(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $envFile = tempnam(sys_get_temp_dir(), 'zc-test-env-');
        file_put_contents(
            $envFile,
            implode(
                PHP_EOL,
                [
                    'ZC_TEST_DB_BASE_NAME=compose_testing',
                    'ZC_TEST_DB_WORKERS=2',
                    'ZC_TEST_DB_INCLUDE_BASE=0',
                    'ZC_TEST_DB_HOST=compose-db',
                    'ZC_TEST_DB_PORT=4406',
                    'ZC_TEST_DB_USER=compose-user',
                    '',
                ]
            )
        );

        try {
            $command = sprintf(
                'ZC_TEST_ENV_FILE=%s env -u ZC_TEST_DB_BASE_NAME -u ZC_TEST_DB_WORKERS -u ZC_TEST_DB_INCLUDE_BASE -u ZC_FEATURE_PARALLEL_PROCESSES bash %s --dry-run',
                escapeshellarg($envFile),
                escapeshellarg($script)
            );

            exec($command . ' 2>&1', $output, $exitCode);

            $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
            $this->assertContains('Dry run for 2 planned test database(s) on compose-db:4406 for user compose-user.', $output);
            $this->assertContains('RESET compose_testing_1', $output);
            $this->assertContains('RESET compose_testing_2', $output);
        } finally {
            unlink($envFile);
        }
    }

    public function testTestEnvironmentLoaderAllowsLocalEnvToOverrideDefaultsWhileKeepingShellOverrides(): void
    {
        $root = sys_get_temp_dir() . '/zc-test-env-root-' . uniqid('', true);
        $configDir = $root . '/not_for_release/testFramework/Support/configs';
        mkdir($configDir, 0777, true);

        file_put_contents(
            $configDir . '/test-runner.env',
            implode(PHP_EOL, [
                'ZC_TEST_DB_HOST=default-db',
                'ZC_TEST_USE_MAILSERVER=false',
                '',
            ])
        );
        file_put_contents(
            $configDir . '/test-runner.local.env',
            implode(PHP_EOL, [
                'ZC_TEST_DB_HOST=local-db',
                'ZC_TEST_USE_MAILSERVER=true',
                '',
            ])
        );

        try {
            $loader = $this->rootPath . '/not_for_release/testFramework/load-test-environment.sh';
            $command = sprintf(
                'ZC_TEST_DB_HOST=%s bash -c %s bash %s %s',
                escapeshellarg('shell-db'),
                escapeshellarg('. "$1"; load_test_framework_env "$2"; printf "%s\n" "$ZC_TEST_DB_HOST"; printf "%s\n" "$ZC_TEST_USE_MAILSERVER"'),
                escapeshellarg($loader),
                escapeshellarg($root)
            );

            exec($command . ' 2>&1', $output, $exitCode);

            $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
            $this->assertSame(['shell-db', 'true'], $output);
        } finally {
            unlink($configDir . '/test-runner.local.env');
            unlink($configDir . '/test-runner.env');
            rmdir($configDir);
            rmdir($root . '/not_for_release/testFramework/Support');
            rmdir($root . '/not_for_release/testFramework');
            rmdir($root . '/not_for_release');
            rmdir($root);
        }
    }

    public function testTestEnvironmentLoaderFallsBackToProfileDatabaseSettings(): void
    {
        $loader = $this->rootPath . '/not_for_release/testFramework/load-test-environment.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_TEST_ENV_FILE=%s bash -c %s bash %s %s',
            escapeshellarg('runner'),
            escapeshellarg('/dev/null'),
            escapeshellarg('. "$1"; load_test_framework_env "$2"; printf "HOST=%s\n" "$ZC_TEST_DB_HOST"; printf "PORT=%s\n" "${ZC_TEST_DB_PORT-}"; printf "USER=%s\n" "$ZC_TEST_DB_USER"; printf "PASSWORD=%s\n" "$ZC_TEST_DB_PASSWORD"; printf "BASE=%s\n" "$ZC_TEST_DB_BASE_NAME"'),
            escapeshellarg($loader),
            escapeshellarg($this->rootPath)
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertSame([
            'HOST=127.0.0.1',
            'PORT=',
            'USER=root',
            'PASSWORD=root',
            'BASE=db',
        ], $output);
    }

    public function testParallelUnitRunnerHelpPrintsUsage(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-unit-tests.sh';
        $command = sprintf('bash %s --help', escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Usage: run-parallel-unit-tests.sh [phpunit-args...]', $output);
        $this->assertStringContainsString('ZC_PARALLEL_PROCESSES', implode(PHP_EOL, $output));
        $this->assertStringContainsString('ZC_UNIT_TEST_FILTER', implode(PHP_EOL, $output));
        $this->assertStringContainsString('composer tests-unit -- --filter RuntimeConfigTest', implode(PHP_EOL, $output));
    }

    public function testParallelUnitRunnerEnvFilterNarrowsFileSelection(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-unit-tests.sh';
        $command = sprintf(
            'ZC_UNIT_TEST_FILTER=%s ZC_PARALLEL_PROCESSES=%s bash %s --filter %s',
            escapeshellarg('RuntimeConfig'),
            escapeshellarg('2'),
            escapeshellarg($script),
            escapeshellarg('RuntimeConfigTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Running 1 unit test files in parallel with 2 worker(s).', $output);
        $this->assertContains('CLI filter narrowed file selection using substring: RuntimeConfigTest', $output);
        $this->assertContains('Env filter narrowed file selection using substring: RuntimeConfig', $output);
        $this->assertContains('START not_for_release/testFramework/Unit/testsSundry/RuntimeConfigTest.php', $output);
        $this->assertContains('PASS  not_for_release/testFramework/Unit/testsSundry/RuntimeConfigTest.php', $output);
    }

    public function testParallelUnitRunnerFailsWhenNoFilesMatchFilter(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-unit-tests.sh';
        $command = sprintf(
            'ZC_UNIT_TEST_FILTER=%s bash %s',
            escapeshellarg('DoesNotExist'),
            escapeshellarg($script)
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('No unit test files matched the requested filter.', $output);
    }

    public function testParallelStorefrontFeatureRunnerHelpPrintsUsage(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-storefront-feature-tests.sh';
        $command = sprintf('bash %s --help', escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Usage: run-parallel-storefront-feature-tests.sh [--dry-run] [--prepare-databases] [phpunit-args...]', $output);
        $this->assertStringContainsString('ZC_FEATURE_PARALLEL_PROCESSES', implode(PHP_EOL, $output));
        $this->assertStringContainsString('ZC_TEST_DB_WORKERS', implode(PHP_EOL, $output));
        $this->assertStringContainsString('ZC_TEST_DB_BASE_NAME', implode(PHP_EOL, $output));
        $this->assertStringContainsString('ZC_FEATURE_TEST_FILTER', implode(PHP_EOL, $output));
        $this->assertStringContainsString('composer tests-feature-store-parallel -- --prepare-databases', implode(PHP_EOL, $output));
        $this->assertStringContainsString('composer tests-feature-store-parallel -- --filter SearchInProcessTest', implode(PHP_EOL, $output));
    }

    public function testParallelStorefrontFeatureRunnerDryRunNarrowsFileSelection(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-storefront-feature-tests.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_FEATURE_TEST_FILTER=%s ZC_FEATURE_PARALLEL_PROCESSES=%s bash %s --dry-run --filter %s',
            escapeshellarg('runner'),
            escapeshellarg('StoreEndpoints'),
            escapeshellarg('2'),
            escapeshellarg($script),
            escapeshellarg('SearchInProcessTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Dry run for 1 storefront parallel-candidate feature test file(s) with 2 worker(s).', $output);
        $this->assertContains('CLI filter narrowed file selection using substring: SearchInProcessTest', $output);
        $this->assertContains('Env filter narrowed file selection using substring: StoreEndpoints', $output);
        $this->assertContains('Worker DB base: db', $output);
        $this->assertContains('Worker database preparation: disabled', $output);
        $this->assertContains('DRY   [worker 1] not_for_release/testFramework/FeatureStore/StoreEndpoints/SearchInProcessTest.php', $output);
        $this->assertStringNotContainsString('AdvancedSearchInProcessTest.php', implode(PHP_EOL, $output));
    }

    public function testParallelStorefrontFeatureRunnerDryRunCyclesWorkers(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-storefront-feature-tests.sh';
        $command = sprintf(
            'ZC_TEST_DB_WORKERS=%s bash %s --dry-run --filter %s',
            escapeshellarg('2'),
            escapeshellarg($script),
            escapeshellarg('AdvancedSearch')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('DRY   [worker 1] not_for_release/testFramework/FeatureStore/StoreEndpoints/AdvancedSearchInProcessTest.php', $output);
        $this->assertContains('DRY   [worker 2] not_for_release/testFramework/FeatureStore/StoreEndpoints/AdvancedSearchResultInProcessTest.php', $output);
        $dryLines = array_values(array_filter($output, static fn (string $line): bool => str_starts_with($line, 'DRY   ')));
        $this->assertSame(
            [
                'DRY   [worker 1] not_for_release/testFramework/FeatureStore/StoreEndpoints/AdvancedSearchInProcessTest.php',
                'DRY   [worker 2] not_for_release/testFramework/FeatureStore/StoreEndpoints/AdvancedSearchResultInProcessTest.php',
            ],
            $dryLines
        );
    }

    public function testParallelStorefrontFeatureRunnerDryRunCanAutoPrepareWorkerDatabases(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-storefront-feature-tests.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s bash %s --dry-run --prepare-databases --filter %s',
            escapeshellarg('runner'),
            escapeshellarg('/dev/null'),
            escapeshellarg('db_local'),
            escapeshellarg('2'),
            escapeshellarg($script),
            escapeshellarg('SearchInProcessTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Worker DB base: db_local', $output);
        $this->assertContains('Worker database preparation: enabled', $output);
        $this->assertContains('Dry run for 2 planned test database(s) on 127.0.0.1:3306 for user root.', $output);
        $this->assertContains('RESET db_local_1', $output);
        $this->assertContains('RESET db_local_2', $output);
        $this->assertContains('DRY   [worker 1] not_for_release/testFramework/FeatureStore/StoreEndpoints/SearchInProcessTest.php', $output);
    }

    public function testPrepareWorkerDatabasesDryRunFallsBackToFeatureParallelProcessCount(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_TEST_ENV_FILE=%s ZC_FEATURE_PARALLEL_PROCESSES=%s ZC_TEST_DB_INCLUDE_BASE=%s env -u ZC_TEST_DB_WORKERS bash %s --dry-run --base %s',
            escapeshellarg('runner'),
            escapeshellarg('/dev/null'),
            escapeshellarg('3'),
            escapeshellarg('0'),
            escapeshellarg($script),
            escapeshellarg('db_testing')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Dry run for 3 planned test database(s) on 127.0.0.1:3306 for user root.', $output);
        $this->assertContains('RESET db_testing_1', $output);
        $this->assertContains('RESET db_testing_2', $output);
        $this->assertContains('RESET db_testing_3', $output);
    }

    public function testParallelStorefrontFeatureRunnerDryRunFallsBackToSubstringMatchingWhenNoExactFileExists(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-storefront-feature-tests.sh';
        $command = sprintf(
            'bash %s --dry-run --filter %s',
            escapeshellarg($script),
            escapeshellarg('SearchInProcess')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Dry run for 2 storefront parallel-candidate feature test file(s) with 2 worker(s).', $output);
        $this->assertContains('DRY   [worker 1] not_for_release/testFramework/FeatureStore/StoreEndpoints/AdvancedSearchInProcessTest.php', $output);
        $this->assertContains('DRY   [worker 2] not_for_release/testFramework/FeatureStore/StoreEndpoints/SearchInProcessTest.php', $output);
    }

    public function testParallelStorefrontFeatureRunnerFailsWhenNoFilesMatchFilter(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-storefront-feature-tests.sh';
        $command = sprintf(
            'ZC_FEATURE_TEST_FILTER=%s bash %s --dry-run',
            escapeshellarg('DoesNotExist'),
            escapeshellarg($script)
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('No storefront parallel-candidate feature test files matched the requested filter.', $output);
    }

    public function testParallelAdminFeatureRunnerHelpPrintsUsage(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-admin-feature-tests.sh';
        $command = sprintf('bash %s --help', escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Usage: run-parallel-admin-feature-tests.sh [--dry-run] [--prepare-databases] [phpunit-args...]', $output);
        $this->assertStringContainsString('ZC_FEATURE_PARALLEL_PROCESSES', implode(PHP_EOL, $output));
        $this->assertStringContainsString('composer tests-feature-admin-parallel -- --prepare-databases', implode(PHP_EOL, $output));
        $this->assertStringContainsString('composer tests-feature-admin-parallel -- --filter AdminEndpointsTest', implode(PHP_EOL, $output));
    }

    public function testParallelAdminFeatureRunnerDryRunNarrowsFileSelection(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-admin-feature-tests.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_FEATURE_TEST_FILTER=%s ZC_FEATURE_PARALLEL_PROCESSES=%s bash %s --dry-run --filter %s',
            escapeshellarg('runner'),
            escapeshellarg('AdminEndpointsTest'),
            escapeshellarg('2'),
            escapeshellarg($script),
            escapeshellarg('AdminEndpointsTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Running 1 admin parallel-candidate feature test file(s) in parallel with 2 worker(s).', $output);
        $this->assertContains('CLI filter narrowed file selection using substring: AdminEndpointsTest', $output);
        $this->assertContains('Env filter narrowed file selection using substring: AdminEndpointsTest', $output);
        $this->assertContains('Worker DB base: db', $output);
        $this->assertContains('Worker database preparation: disabled', $output);
        $this->assertContains('DRY   [worker 1] not_for_release/testFramework/FeatureAdmin/AdminEndpoints/AdminEndpointsTest.php', $output);
    }

    public function testParallelAdminFeatureRunnerDryRunCanAutoPrepareWorkerDatabases(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-admin-feature-tests.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s bash %s --dry-run --prepare-databases --filter %s',
            escapeshellarg('runner'),
            escapeshellarg('/dev/null'),
            escapeshellarg('db_admin'),
            escapeshellarg('2'),
            escapeshellarg($script),
            escapeshellarg('AdminEndpointsTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Worker DB base: db_admin', $output);
        $this->assertContains('Worker database preparation: enabled', $output);
        $this->assertContains('Dry run for 2 planned test database(s) on 127.0.0.1:3306 for user root.', $output);
        $this->assertContains('RESET db_admin_1', $output);
        $this->assertContains('RESET db_admin_2', $output);
        $this->assertContains('DRY   [worker 1] not_for_release/testFramework/FeatureAdmin/AdminEndpoints/AdminEndpointsTest.php', $output);
    }

    public function testParallelAdminFeatureRunnerDryRunCyclesWorkers(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-admin-feature-tests.sh';
        $command = sprintf(
            'ZC_TEST_DB_WORKERS=%s bash %s --dry-run --filter %s',
            escapeshellarg('2'),
            escapeshellarg($script),
            escapeshellarg('Admin')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $dryLines = array_values(array_filter($output, static fn (string $line): bool => str_starts_with($line, 'DRY   ')));
        $this->assertContains('DRY   [worker 1] not_for_release/testFramework/FeatureAdmin/AdminEndpoints/AdminAccountPagesTest.php', $dryLines);
        $this->assertContains('DRY   [worker 2] not_for_release/testFramework/FeatureAdmin/AdminEndpoints/AdminAuthLifecycleTest.php', $dryLines);
        $this->assertGreaterThan(2, count($dryLines));
    }

    public function testParallelFeatureAggregateDryRunSkipsStorefrontForAdminOnlyFilter(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-feature-tests.sh';
        $command = sprintf(
            'bash %s --dry-run --filter %s',
            escapeshellarg($script),
            escapeshellarg('AdminEndpointsTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('SKIP  [store] no matching storefront parallel-candidate files', $output);
        $this->assertContains('RUN   [admin] run-parallel-admin-feature-tests.sh', $output);
        $this->assertContains('SKIP  [admin-plugin] no matching admin plugin-filesystem files', $output);
        $this->assertContains('DRY   [worker 1] not_for_release/testFramework/FeatureAdmin/AdminEndpoints/AdminEndpointsTest.php', $output);
    }

    public function testParallelFeatureAggregateTargetsPluginFilesystemBucketForPluginFilter(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-feature-tests.sh';
        $command = sprintf(
            'bash %s --dry-run --filter %s',
            escapeshellarg($script),
            escapeshellarg('BasicPluginInstallTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('SKIP  [store] no matching storefront parallel-candidate files', $output);
        $this->assertContains('SKIP  [admin] no matching admin parallel-candidate files', $output);
        $this->assertContains('RUN   [admin-plugin] tests-feature-admin-plugin-filesystem (dry run)', $output);
        $this->assertContains('DRY   [admin-plugin] not_for_release/testFramework/FeatureAdmin/PluginTests/BasicPluginInstallTest.php', $output);
    }

    public function testParallelFeatureAggregateDryRunIncludesPluginFilesystemBucketWithoutFilter(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-feature-tests.sh';
        $command = sprintf('bash %s --dry-run', escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('RUN   [store] run-parallel-storefront-feature-tests.sh', $output);
        $this->assertContains('RUN   [admin] run-parallel-admin-feature-tests.sh', $output);
        $this->assertContains('RUN   [admin-plugin] tests-feature-admin-plugin-filesystem (dry run)', $output);
        $this->assertContains('DRY   [admin-plugin] not_for_release/testFramework/FeatureAdmin/PluginTests/BasicPluginInstallTest.php', $output);
        $this->assertContains('DRY   [admin-plugin] not_for_release/testFramework/FeatureAdmin/Security/PluginsLFITest.php', $output);
    }

    public function testFeatureTestsCiRunnerHelpPrintsUsage(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-feature-tests-ci.sh';
        $command = sprintf('bash %s --help', escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Usage: run-feature-tests-ci.sh [--dry-run] [feature-runner-args...]', $output);
        $this->assertStringContainsString('composer tests-feature -- --filter SearchInProcessTest', implode(PHP_EOL, $output));
        $this->assertStringContainsString('composer tests-feature -- --dry-run --filter BasicPluginInstallTest', implode(PHP_EOL, $output));
    }

    public function testFeatureTestsCiRunnerDryRunForwardsFilterToAggregateFeatureRunner(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-feature-tests-ci.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s bash %s --dry-run --filter %s',
            escapeshellarg('runner'),
            escapeshellarg('/dev/null'),
            escapeshellarg('db'),
            escapeshellarg('2'),
            escapeshellarg('0'),
            escapeshellarg($script),
            escapeshellarg('BasicPluginInstallTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Dry run for 2 planned test database(s) on 127.0.0.1:3306 for user root.', $output);
        $this->assertContains('SKIP  [store] no matching storefront parallel-candidate files', $output);
        $this->assertContains('SKIP  [admin] no matching admin parallel-candidate files', $output);
        $this->assertContains('DRY   [admin-plugin] not_for_release/testFramework/FeatureAdmin/PluginTests/BasicPluginInstallTest.php', $output);
        $this->assertStringNotContainsString('PluginsLFITest.php', implode(PHP_EOL, $output));
    }

    public function testFeatureTestsCiRunnerStopsWhenDatabasePreparationFails(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-feature-tests-ci.sh';
        $command = sprintf(
            'ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s ZC_TEST_DB_HOST=%s ZC_TEST_DB_PORT=%s bash %s',
            escapeshellarg('db'),
            escapeshellarg('2'),
            escapeshellarg('0'),
            escapeshellarg('127.0.0.1'),
            escapeshellarg('65000'),
            escapeshellarg($script)
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertNotSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertStringNotContainsString('RUN   [store]', implode(PHP_EOL, $output));
        $this->assertStringNotContainsString('RUN   [admin]', implode(PHP_EOL, $output));
    }

    public function testStoreFeatureTestsCiRunnerDryRunForwardsFilter(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-store-feature-tests-ci.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s bash %s --dry-run --filter %s',
            escapeshellarg('runner'),
            escapeshellarg('/dev/null'),
            escapeshellarg('db'),
            escapeshellarg('2'),
            escapeshellarg('0'),
            escapeshellarg($script),
            escapeshellarg('SearchInProcessTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Database: db', $output);
        $this->assertContains('Dry run for 2 planned test database(s) on 127.0.0.1:3306 for user root.', $output);
        $this->assertContains('Dry run for 1 storefront parallel-candidate feature test file(s) with 2 worker(s).', $output);
        $this->assertContains('DRY   [worker 1] not_for_release/testFramework/FeatureStore/StoreEndpoints/SearchInProcessTest.php', $output);
        $this->assertStringNotContainsString('AdvancedSearchInProcessTest.php', implode(PHP_EOL, $output));
    }

    public function testAdminFeatureTestsCiRunnerDryRunForwardsFilter(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-admin-feature-tests-ci.sh';
        $command = sprintf(
            'USER=%s IS_DDEV_PROJECT= ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s bash %s --dry-run --filter %s',
            escapeshellarg('runner'),
            escapeshellarg('/dev/null'),
            escapeshellarg('db'),
            escapeshellarg('2'),
            escapeshellarg('0'),
            escapeshellarg($script),
            escapeshellarg('BasicPluginInstallTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Database: db', $output);
        $this->assertContains('Dry run for 2 planned test database(s) on 127.0.0.1:3306 for user root.', $output);
        $this->assertContains('SKIP  [admin] no matching admin parallel-candidate files', $output);
        $this->assertContains('RUN   [admin-plugin] tests-feature-admin-plugin-filesystem (dry run)', $output);
        $this->assertContains('DRY   [admin-plugin] not_for_release/testFramework/FeatureAdmin/PluginTests/BasicPluginInstallTest.php', $output);
        $this->assertStringNotContainsString('PluginsLFITest.php', implode(PHP_EOL, $output));
    }

    public function testPrepareWorkerDatabasesAllowsExplicitBlankPassword(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $binDir = sys_get_temp_dir() . '/zc-mysql-stub-' . uniqid('', true);
        $argsFile = $binDir . '/mysql-args.txt';
        mkdir($binDir, 0777, true);
        file_put_contents(
            $binDir . '/mysql',
            "#!/usr/bin/env bash\nprintf '%s\n' \"\$@\" > " . escapeshellarg($argsFile) . "\nexit 0\n"
        );
        chmod($binDir . '/mysql', 0755);

        try {
            $command = sprintf(
                'PATH=%s:$PATH ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s ZC_TEST_DB_PASSWORD= bash %s',
                escapeshellarg($binDir),
                escapeshellarg('/dev/null'),
                escapeshellarg('db'),
                escapeshellarg('1'),
                escapeshellarg('0'),
                escapeshellarg($script)
            );

            exec($command . ' 2>&1', $output, $exitCode);

            $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
            $this->assertFileExists($argsFile);
            $mysqlArgs = file($argsFile, FILE_IGNORE_NEW_LINES) ?: [];
            $this->assertContains('--password=', $mysqlArgs);
            $this->assertNotContains('--password=root', $mysqlArgs);
        } finally {
            if (is_file($argsFile)) {
                unlink($argsFile);
            }
            if (is_file($binDir . '/mysql')) {
                unlink($binDir . '/mysql');
            }
            if (is_dir($binDir)) {
                rmdir($binDir);
            }
        }
    }

    public function testPrepareWorkerDatabasesWarnsAndContinuesWhenRecreateIsDeniedButDatabasesExist(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $binDir = sys_get_temp_dir() . '/zc-mysql-stub-' . uniqid('', true);
        mkdir($binDir, 0777, true);
        file_put_contents(
            $binDir . '/mysql',
            <<<'BASH'
#!/usr/bin/env bash
sql=""
next_is_sql=0
for arg in "$@"; do
  if [ "$next_is_sql" = "1" ]; then
    sql="$arg"
    break
  fi
  if [ "$arg" = "-e" ]; then
    next_is_sql=1
  fi
done

if [[ "$sql" == DROP\ DATABASE* ]]; then
  echo "ERROR 1044 (42000): Access denied for user 'app'@'localhost' to database 'db_testing'" >&2
  exit 1
fi

if [[ "$sql" =~ SCHEMA_NAME\ =\ \'([A-Za-z0-9_]+)\' ]]; then
  db_name="${BASH_REMATCH[1]}"
  if [[ ",${EXISTING_DATABASES}," == *",$db_name,"* ]]; then
    echo "$db_name"
  fi
fi
BASH
        );
        chmod($binDir . '/mysql', 0755);

        try {
            $command = sprintf(
                'PATH=%s:$PATH EXISTING_DATABASES=%s ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s bash %s',
                escapeshellarg($binDir),
                escapeshellarg('db_testing_1,db_testing_2'),
                escapeshellarg('/dev/null'),
                escapeshellarg('db_testing'),
                escapeshellarg('2'),
                escapeshellarg('0'),
                escapeshellarg($script)
            );

            exec($command . ' 2>&1', $output, $exitCode);

            $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
            $this->assertStringContainsString('does not have permission to recreate test databases.', implode(PHP_EOL, $output));
            $this->assertContains('Existing worker databases detected; continuing without resetting them.', $output);
            $this->assertContains('Existing databases:', $output);
        } finally {
            if (is_file($binDir . '/mysql')) {
                unlink($binDir . '/mysql');
            }
            if (is_dir($binDir)) {
                rmdir($binDir);
            }
        }
    }

    public function testPrepareWorkerDatabasesFailsWhenRecreateIsDeniedAndDatabasesAreMissing(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/prepare-worker-databases.sh';
        $binDir = sys_get_temp_dir() . '/zc-mysql-stub-' . uniqid('', true);
        mkdir($binDir, 0777, true);
        file_put_contents(
            $binDir . '/mysql',
            <<<'BASH'
#!/usr/bin/env bash
sql=""
next_is_sql=0
for arg in "$@"; do
  if [ "$next_is_sql" = "1" ]; then
    sql="$arg"
    break
  fi
  if [ "$arg" = "-e" ]; then
    next_is_sql=1
  fi
done

if [[ "$sql" == DROP\ DATABASE* ]]; then
  echo "ERROR 1044 (42000): Access denied for user 'app'@'localhost' to database 'db_testing'" >&2
  exit 1
fi

if [[ "$sql" =~ SCHEMA_NAME\ =\ \'([A-Za-z0-9_]+)\' ]]; then
  db_name="${BASH_REMATCH[1]}"
  if [[ ",${EXISTING_DATABASES}," == *",$db_name,"* ]]; then
    echo "$db_name"
  fi
fi
BASH
        );
        chmod($binDir . '/mysql', 0755);

        try {
            $command = sprintf(
                'PATH=%s:$PATH EXISTING_DATABASES=%s ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s bash %s',
                escapeshellarg($binDir),
                escapeshellarg('db_testing_1'),
                escapeshellarg('/dev/null'),
                escapeshellarg('db_testing'),
                escapeshellarg('2'),
                escapeshellarg('0'),
                escapeshellarg($script)
            );

            exec($command . ' 2>&1', $output, $exitCode);

            $this->assertSame(1, $exitCode, implode(PHP_EOL, $output));
            $this->assertStringContainsString('does not have permission to recreate test databases.', implode(PHP_EOL, $output));
            $this->assertContains('Missing worker databases: db_testing_2', $output);
            $this->assertContains('Pre-create them with a privileged MySQL user, or rerun with a user that can CREATE/DROP databases.', $output);
        } finally {
            if (is_file($binDir . '/mysql')) {
                unlink($binDir . '/mysql');
            }
            if (is_dir($binDir)) {
                rmdir($binDir);
            }
        }
    }

    public function testTestsCiRunnerHelpPrintsUsage(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-tests-ci.sh';
        $command = sprintf('bash %s --help', escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Usage: run-tests-ci.sh [--dry-run] [test-runner-args...]', $output);
        $this->assertStringContainsString('composer tests-ci -- --dry-run --filter BasicPluginInstallTest', implode(PHP_EOL, $output));
    }

    public function testTestsCiRunnerRunsBothLanesWithoutFilter(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-tests-ci.sh';
        $command = sprintf(
            'ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s ZC_UNIT_TEST_FILTER=%s ZC_FEATURE_TEST_FILTER=%s bash %s --dry-run',
            escapeshellarg('db'),
            escapeshellarg('2'),
            escapeshellarg('0'),
            escapeshellarg('RuntimeConfigTest'),
            escapeshellarg('BasicPluginInstallTest'),
            escapeshellarg($script)
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Running 1 unit test files in parallel with 4 worker(s).', $output);
        $this->assertContains('Env filter narrowed file selection using substring: RuntimeConfigTest', $output);
        $this->assertContains('Parallel unit test summary: 0 failing file(s), 23 test(s), 26 assertion(s).', $output);
        $this->assertContains('Feature Test Group Report', $output);
        $this->assertContains('DRY   [admin-plugin] not_for_release/testFramework/FeatureAdmin/PluginTests/BasicPluginInstallTest.php', $output);
    }

    public function testTestsCiRunnerDryRunSkipsUnitForFeatureOnlyFilter(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-tests-ci.sh';
        $command = sprintf(
            'ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_INCLUDE_BASE=%s bash %s --dry-run --filter %s',
            escapeshellarg('db'),
            escapeshellarg('2'),
            escapeshellarg('0'),
            escapeshellarg($script),
            escapeshellarg('BasicPluginInstallTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('SKIP  [unit] no matching unit test files', $output);
        $this->assertContains('DRY   [admin-plugin] not_for_release/testFramework/FeatureAdmin/PluginTests/BasicPluginInstallTest.php', $output);
    }

    public function testTestsCiRunnerDryRunRunsUnitWhenFilterMatchesUnitSuite(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-tests-ci.sh';
        $command = sprintf(
            'bash %s --dry-run --filter %s',
            escapeshellarg($script),
            escapeshellarg('RuntimeConfigTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Running 1 unit test files in parallel with 4 worker(s).', $output);
        $this->assertContains('PASS  not_for_release/testFramework/Unit/testsSundry/RuntimeConfigTest.php', $output);
        $this->assertContains('SKIP  [feature] no matching feature test files', $output);
        $this->assertStringNotContainsString('Unknown option "--dry-run"', implode(PHP_EOL, $output));
    }

    public function testTestsCiRunnerFailsWhenNoUnitOrFeatureFilesMatch(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-tests-ci.sh';
        $command = sprintf(
            'bash %s --dry-run --filter %s',
            escapeshellarg($script),
            escapeshellarg('DoesNotExistAnywhere')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('SKIP  [unit] no matching unit test files', $output);
        $this->assertContains('SKIP  [feature] no matching feature test files', $output);
        $this->assertContains('No unit or feature test files matched the requested filter.', $output);
    }

    public function testParallelStorefrontFeatureRunnerFailsFastWhenWorkerDatabasesCannotBeVerified(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/run-parallel-storefront-feature-tests.sh';
        $command = sprintf(
            'ZC_TEST_ENV_FILE=%s ZC_TEST_DB_BASE_NAME=%s ZC_TEST_DB_WORKERS=%s ZC_TEST_DB_HOST=%s bash %s --filter %s',
            escapeshellarg('/dev/null'),
            escapeshellarg('db_local'),
            escapeshellarg('2'),
            escapeshellarg('invalid-host-for-tests'),
            escapeshellarg($script),
            escapeshellarg('SearchInProcessTest')
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Unable to verify worker databases on invalid-host-for-tests:3306 for user root.', $output);
        $this->assertContains('Try: ZC_TEST_DB_BASE_NAME=db_local ZC_TEST_DB_WORKERS=2 composer tests-db-prepare-workers -- --dry-run', $output);
    }

    public function testDescribeWorkerRuntimePrintsDefaultDerivedPaths(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/describe-worker-runtime.php';
        $command = sprintf('IS_DDEV_PROJECT= USER=%s php %s', escapeshellarg('runner'), escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Worker Runtime Description', $output);
        $this->assertContains('Detected shell user: runner', $output);
        $this->assertContains('Main config profile: runner', $output);
        $this->assertContains('Main config: ' . $this->rootPath . '/not_for_release/testFramework/Support/configs/runner.main.configure.php', $output);
        $this->assertContains('Store config profile: runner', $output);
        $this->assertContains('Store config: ' . $this->rootPath . '/not_for_release/testFramework/Support/configs/runner.store.configure.php', $output);
        $this->assertContains('Admin config profile: runner', $output);
        $this->assertContains('Admin config: ' . $this->rootPath . '/not_for_release/testFramework/Support/configs/runner.admin.configure.php', $output);
        $this->assertContains('Worker token: (none)', $output);
        $this->assertContains('Database: db_testing', $output);
        $this->assertContains('Progress file: ' . $this->rootPath . '/progress.json', $output);
        $this->assertContains('Log directory: ' . $this->rootPath . '/logs', $output);
        $this->assertContains('Plugin directory: ' . $this->rootPath . '/zc_plugins/ExamplePlugin', $output);
    }

    public function testDescribeWorkerRuntimePrintsWorkerScopedDerivedPaths(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/describe-worker-runtime.php';
        $command = sprintf(
            'IS_DDEV_PROJECT= USER=%s ZC_TEST_WORKER=%s ZC_TEST_RUNTIME_ROOT=%s ZC_TEST_RUNTIME_DB_BASE=%s ZC_TEST_RUNTIME_PLUGIN=%s php %s',
            escapeshellarg('runner'),
            escapeshellarg('2'),
            escapeshellarg('/tmp/zc-runtime'),
            escapeshellarg('db_ci'),
            escapeshellarg('WorkerPlugin'),
            escapeshellarg($script)
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Detected shell user: runner', $output);
        $this->assertContains('Main config profile: runner', $output);
        $this->assertContains('Store config profile: runner', $output);
        $this->assertContains('Admin config profile: runner', $output);
        $this->assertContains('Worker token: 2', $output);
        $this->assertContains('Database: db_ci_2', $output);
        $this->assertContains('Progress file: /tmp/zc-runtime/progress_2.json', $output);
        $this->assertContains('Log directory: /tmp/zc-runtime/logs/2', $output);
        $this->assertContains('Store artifacts: /tmp/zc-runtime/not_for_release/testFramework/logs/console/store/2/', $output);
        $this->assertContains('Admin artifacts: /tmp/zc-runtime/not_for_release/testFramework/logs/console/admin/2/', $output);
        $this->assertContains('Plugin directory: /tmp/zc-runtime/zc_plugins/2/WorkerPlugin', $output);
    }

    public function testZcCliListRunsAsExternalProcess(): void
    {
        $script = $this->rootPath . '/zc_cli.php';
        $command = sprintf('%s %s list', escapeshellarg(PHP_BINARY), escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $joinedOutput = implode(PHP_EOL, $output);
        $this->assertSame(0, $exitCode, $joinedOutput);
        $this->assertContains('Available commands:', $output);
        $this->assertStringContainsString('help', $joinedOutput);
        $this->assertStringContainsString('list', $joinedOutput);
    }

    public function testZcCliListFailsClosedWhenPhpRunsWithoutMySqlExtension(): void
    {
        $script = $this->rootPath . '/zc_cli.php';
        $command = sprintf('%s -n %s list', escapeshellarg(PHP_BINARY), escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $joinedOutput = implode(PHP_EOL, $output);
        $this->assertSame(0, $exitCode, $joinedOutput);
        $this->assertContains('Available commands:', $output);
        $this->assertStringContainsString(
            'Warning: Plugin command discovery disabled: the MySQL connector for PHP is unavailable.',
            $joinedOutput
        );
        $this->assertStringContainsString('help', $joinedOutput);
        $this->assertStringContainsString('list', $joinedOutput);
    }

    public function testBinZencartWrapperRunsAsExternalProcess(): void
    {
        $script = $this->rootPath . '/bin/zencart';
        $command = sprintf('%s %s list', escapeshellarg(PHP_BINARY), escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $joinedOutput = implode(PHP_EOL, $output);
        $this->assertSame(0, $exitCode, $joinedOutput);
        $this->assertContains('Available commands:', $output);
        $this->assertStringContainsString('help', $joinedOutput);
        $this->assertStringContainsString('list', $joinedOutput);
    }

    public function testZcCliListWarnsWhenDatabaseConfigIsMissing(): void
    {
        $tempRoot = $this->createMinimalCliRoot();
        $script = $tempRoot . '/zc_cli.php';
        $command = sprintf('%s %s list', escapeshellarg(PHP_BINARY), escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $joinedOutput = implode(PHP_EOL, $output);
        $this->assertSame(0, $exitCode, $joinedOutput);
        $this->assertContains('Available commands:', $output);
        $this->assertStringContainsString(
            'Warning: Plugin command discovery disabled: store database configuration is unavailable.',
            $joinedOutput
        );
    }

    public function testZcCliListWarnsWhenDatabaseConnectionFails(): void
    {
        if (!function_exists('mysqli_connect')) {
            $this->markTestSkipped('The DB connection failure path requires the MySQL connector for PHP.');
        }

        $tempRoot = $this->createMinimalCliRoot(
            <<<'PHP'
<?php
define('DB_TYPE', 'mysql');
define('DB_SERVER', 'invalid-host-for-cli-tests');
define('DB_SERVER_USERNAME', 'root');
define('DB_SERVER_PASSWORD', 'root');
define('DB_DATABASE', 'db');
PHP
        );
        $script = $tempRoot . '/zc_cli.php';
        $command = sprintf('%s %s list', escapeshellarg(PHP_BINARY), escapeshellarg($script));

        exec($command . ' 2>&1', $output, $exitCode);

        $joinedOutput = implode(PHP_EOL, $output);
        $this->assertSame(0, $exitCode, $joinedOutput);
        $this->assertContains('Available commands:', $output);
        $this->assertStringContainsString(
            'Warning: Plugin command discovery disabled: unable to connect to the store database.',
            $joinedOutput
        );
    }

    public function testDescribeWorkerRuntimeUsesTestDatabaseBaseEnvironmentFallback(): void
    {
        $script = $this->rootPath . '/not_for_release/testFramework/describe-worker-runtime.php';
        $command = sprintf(
            'IS_DDEV_PROJECT= USER=%s ZC_TEST_DB_BASE_NAME=%s php %s',
            escapeshellarg('runner'),
            escapeshellarg('db_local'),
            escapeshellarg($script)
        );

        exec($command . ' 2>&1', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode(PHP_EOL, $output));
        $this->assertContains('Database: db_local', $output);
    }

    private function createMinimalCliRoot(?string $configureContents = null): string
    {
        $root = sys_get_temp_dir() . '/zc-cli-root-' . uniqid('', true);
        $this->tempPaths[] = $root;

        mkdir($root, 0777, true);
        mkdir($root . '/bin', 0777, true);
        mkdir($root . '/includes/functions', 0777, true);
        mkdir($root . '/includes/classes/traits', 0777, true);
        mkdir($root . '/includes/classes/vendors/AuraAutoload/src', 0777, true);
        mkdir($root . '/includes/classes/vendors/polyfill-mbstring/Resources/unidata', 0777, true);
        mkdir($root . '/includes/classes/Console/Commands', 0777, true);
        mkdir($root . '/includes/classes/db/mysql', 0777, true);

        copy($this->rootPath . '/zc_cli.php', $root . '/zc_cli.php');
        copy($this->rootPath . '/bin/zencart', $root . '/bin/zencart');
        copy($this->rootPath . '/includes/application_cli_bootstrap.php', $root . '/includes/application_cli_bootstrap.php');
        copy($this->rootPath . '/includes/defined_paths.php', $root . '/includes/defined_paths.php');
        copy($this->rootPath . '/includes/psr4Autoload.php', $root . '/includes/psr4Autoload.php');
        copy($this->rootPath . '/includes/database_tables.php', $root . '/includes/database_tables.php');
        copy($this->rootPath . '/includes/functions/php_polyfills.php', $root . '/includes/functions/php_polyfills.php');
        copy($this->rootPath . '/includes/functions/zen_define_default.php', $root . '/includes/functions/zen_define_default.php');
        copy($this->rootPath . '/includes/classes/class.base.php', $root . '/includes/classes/class.base.php');
        copy($this->rootPath . '/includes/classes/EventDto.php', $root . '/includes/classes/EventDto.php');
        copy($this->rootPath . '/includes/classes/traits/NotifierManager.php', $root . '/includes/classes/traits/NotifierManager.php');
        copy($this->rootPath . '/includes/classes/traits/ObserverManager.php', $root . '/includes/classes/traits/ObserverManager.php');
        copy($this->rootPath . '/includes/classes/vendors/AuraAutoload/src/Loader.php', $root . '/includes/classes/vendors/AuraAutoload/src/Loader.php');
        copy($this->rootPath . '/includes/classes/vendors/polyfill-mbstring/Mbstring.php', $root . '/includes/classes/vendors/polyfill-mbstring/Mbstring.php');
        copy($this->rootPath . '/includes/classes/vendors/polyfill-mbstring/bootstrap80.php', $root . '/includes/classes/vendors/polyfill-mbstring/bootstrap80.php');
        copy($this->rootPath . '/includes/classes/vendors/polyfill-mbstring/Resources/unidata/caseFolding.php', $root . '/includes/classes/vendors/polyfill-mbstring/Resources/unidata/caseFolding.php');
        copy($this->rootPath . '/includes/classes/vendors/polyfill-mbstring/Resources/unidata/lowerCase.php', $root . '/includes/classes/vendors/polyfill-mbstring/Resources/unidata/lowerCase.php');
        copy($this->rootPath . '/includes/classes/vendors/polyfill-mbstring/Resources/unidata/titleCaseRegexp.php', $root . '/includes/classes/vendors/polyfill-mbstring/Resources/unidata/titleCaseRegexp.php');
        copy($this->rootPath . '/includes/classes/vendors/polyfill-mbstring/Resources/unidata/upperCase.php', $root . '/includes/classes/vendors/polyfill-mbstring/Resources/unidata/upperCase.php');
        copy($this->rootPath . '/includes/classes/db/mysql/query_factory.php', $root . '/includes/classes/db/mysql/query_factory.php');

        foreach ([
            'CommandRegistry.php',
            'CommandResolver.php',
            'ConsoleCommand.php',
            'ConsoleInput.php',
            'ConsoleKernel.php',
            'ConsoleOutput.php',
            'PluginCommandDiscovery.php',
            'TrustedPluginVersionResolver.php',
        ] as $file) {
            copy($this->rootPath . '/includes/classes/Console/' . $file, $root . '/includes/classes/Console/' . $file);
        }

        foreach (['ConfigGetCommand.php', 'HelpCommand.php', 'ListCommand.php', 'PluginListCommand.php', 'VersionShowCommand.php'] as $file) {
            copy(
                $this->rootPath . '/includes/classes/Console/Commands/' . $file,
                $root . '/includes/classes/Console/Commands/' . $file
            );
        }

        if ($configureContents !== null) {
            file_put_contents($root . '/includes/configure.php', $configureContents . PHP_EOL);
        }

        return $root;
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
