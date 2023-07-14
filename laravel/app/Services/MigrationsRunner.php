<?php

namespace App\Services;

use Illuminate\Support\Str;

class MigrationsRunner
{

    public function __construct($migrationsDir)
    {
        $this->migrationDir = $migrationsDir;
    }

    public function  run()
    {

        $files = glob($this->migrationDir . '*_migration.php');
        foreach ($files as $migration)
        {
            $className = 'Migrations\\Create' . ucfirst(Str::camel(str_replace(['.php', 'migration'], '', basename($migration)))) . 'Table';
            $class =  new $className;
            $class->down();
            $class->up();
        }
    }
}
