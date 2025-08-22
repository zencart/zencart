<?php

namespace Tests\Support\Traits;

use App\Services\MigrationsRunner;
use App\Services\SeederRunner;
use Doctrine\DBAL\Configuration;
use Illuminate\Database\Capsule\Manager as Capsule;
use InitialSeeders\DatabaseSeeder;

trait DatabaseConcerns
{
    public static function databaseSetup(): void
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => DB_TYPE,
            'host'      => DB_SERVER,
            'database'  => DB_DATABASE,
            'username'  => DB_SERVER_USERNAME,
            'password'  => DB_SERVER_PASSWORD,
            'charset'   => DB_CHARSET,
            // do not pass prefix; this is included in the table definition
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
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
