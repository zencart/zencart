<?php

namespace Tests\Services;

use Illuminate\Support\Str;

/**
 * @since ZC v2.0.0
 */
class MigrationsRunner
{
    public function __construct(protected $migrationDir)
    {
    }

    /**
     * @since ZC v2.0.0
     */
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
