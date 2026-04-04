<?php

namespace Tests\Support\Traits;

use Tests\Services\DatabaseBootstrapper;
use Tests\Services\SeederRunner;
use Tests\Support\Database\TestDb;

require_once dirname(__DIR__) . '/configs/runtime_config.php';

trait DatabaseConcerns
{
    public static function databaseSetup(): void
    {
        TestDb::resetConnection();
        TestDb::pdo();
        self::defineDatabaseRuntimeConstants();
    }

    protected static function defineDatabaseRuntimeConstants(): void
    {
        if (!defined('DIR_FS_ROOT')) {
            define('DIR_FS_ROOT', ROOTCWD);
        }
        if (!defined('DIR_FS_LOGS')) {
            define('DIR_FS_LOGS', zc_test_config_log_directory(ROOTCWD));
        }
        if (!is_dir(DIR_FS_LOGS)) {
            mkdir(DIR_FS_LOGS, 0777, true);
        }
        if (!defined('DEBUG_LOG_FOLDER')) define('DEBUG_LOG_FOLDER', DIR_FS_LOGS);
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', false);
        }
    }

    public static function runDatabaseLoader(array $mainConfigs): void
    {
        (new DatabaseBootstrapper())->run($mainConfigs);
    }

    public static function runCustomSeeder(string $seederClass): void
    {
        echo 'Running Custom Seeder' . PHP_EOL;
        $runner = new SeederRunner();
        $runner->run($seederClass);
    }

}
