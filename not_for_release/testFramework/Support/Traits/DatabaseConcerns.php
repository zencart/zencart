<?php

namespace Tests\Support\Traits;

use App\Services\MigrationsRunner;
use App\Services\SeederRunner;
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
    }

    public static function runMigrations()
    {
        echo 'Running Migrations' . PHP_EOL;
        $runner = new MigrationsRunner(ROOTCWD . 'not_for_release/testFramework/Support/database/migrations/');
        $runner->run();
    }

    public static function runInitialSeeders()
    {
        echo 'Running Initial Seeders' . PHP_EOL;
        $runner = new SeederRunner();
        $runner->run('InitialSeeders', 'DatabaseSeeder');
    }

    public static function runCustomSeeder($seederClass)
    {
        echo 'Running Custom Seeder' . PHP_EOL;
        $runner = new SeederRunner();
        $runner->run('CustomSeeders', $seederClass);
    }

}
