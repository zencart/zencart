<?php

namespace Tests\Support\Traits;

use Tests\Services\SeederRunner;
use Tests\Support\Database\TestDb;

trait DatabaseConcerns
{
    public static function databaseSetup(): void
    {
        TestDb::resetConnection();
        TestDb::pdo();
        if (!defined('DIR_FS_ROOT')) {
            define('DIR_FS_ROOT', ROOTCWD);
        }
        if (!defined('DIR_FS_LOGS')) {
            define('DIR_FS_LOGS', ROOTCWD);
        }
        if (!defined('DEBUG_LOG_FOLDER')) define('DEBUG_LOG_FOLDER', DIR_FS_LOGS);
        if (!defined('IS_ADMIN_FLAG')) {
            define('IS_ADMIN_FLAG', false);
        }
    }

    public static function runDatabaseLoader($mainConfigs)
    {
        $options = [
            'db_host' => DB_SERVER,
            'db_user' => DB_SERVER_USERNAME,
            'db_password' => DB_SERVER_PASSWORD,
            'db_name' => DB_DATABASE,
            'db_charset' => DB_CHARSET,
            'db_prefix' => '',
            'db_type' => DB_TYPE,
        ];
        require_once ROOTCWD . 'zc_install/includes/classes/class.zcDatabaseInstaller.php';
        $extendedOptions = [
            'doJsonProgressLogging' => false,
            'doJsonProgressLoggingFileName' => \zcDatabaseInstaller::$initialProgressMeterFilename,
            'id' => 'main',
            'message' => '',
        ];

        // Debug utils
        require_once ROOTCWD . 'zc_install/includes/functions/general.php';
        require_once ROOTCWD . 'zc_install/includes/functions/password_funcs.php';

        echo 'Running mysql_zencart.sql' . PHP_EOL;
        $file = ROOTCWD . 'zc_install/sql/install/mysql_zencart.sql';
        $dbInstaller = new \zcDatabaseInstaller($options);
        $conn = $dbInstaller->getConnection();
        $error = $dbInstaller->parseSqlFile($file, $extendedOptions);
        echo 'Running mysql_utf8.sql' . PHP_EOL;
        $file = ROOTCWD . 'zc_install/sql/install/mysql_utf8.sql';
        $dbInstaller = new \zcDatabaseInstaller($options);
        $conn = $dbInstaller->getConnection();
        $error = $dbInstaller->parseSqlFile($file, $extendedOptions);
        echo 'Running mysql_demo.sql' . PHP_EOL;
        $file = ROOTCWD . 'zc_install/sql/demo/mysql_demo.sql';
        $dbInstaller = new \zcDatabaseInstaller($options);
        $conn = $dbInstaller->getConnection();
        $error = $dbInstaller->parseSqlFile($file, $extendedOptions);
        TestDb::resetConnection();
        $runner = new SeederRunner();
        $runner->run('InitialSetupSeeder', $mainConfigs);
    }

    public static function runCustomSeeder($seederClass)
    {
        echo 'Running Custom Seeder' . PHP_EOL;
        $runner = new SeederRunner();
        $runner->run($seederClass);
    }

}
