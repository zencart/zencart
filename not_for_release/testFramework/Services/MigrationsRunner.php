<?php

namespace Tests\Services;

use Tests\Services\Contracts\TestMigrationInterface;

/**
 * @since ZC v2.0.0
 */
class MigrationsRunner
{
    public function __construct(protected string $migrationDir)
    {
    }

    /**
     * @since ZC v2.0.0
     */
    public function run(): void
    {
        $files = glob(rtrim($this->migrationDir, '/') . '/*_migration.php') ?: [];
        sort($files);

        foreach ($files as $migration)
        {
            require_once $migration;

            $className = 'Migrations\\Create' . self::camel(str_replace(['.php', 'migration'], '', basename($migration))) . 'Table';

            if (!class_exists($className)) {
                throw new TestFrameworkRunnerException(
                    sprintf('Migration class "%s" was not found for file "%s".', $className, $migration)
                );
            }

            $class = new $className;

            if (!$class instanceof TestMigrationInterface) {
                throw new TestFrameworkRunnerException(
                    sprintf('Migration class "%s" must implement %s.', $className, TestMigrationInterface::class)
                );
            }

            $class->down();
            $class->up();
        }
    }

    /**
     * @since ZC v2.2.0
     */
    private static function camel(string $value): string
    {
        $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value);
        $value = ucwords(strtolower(trim($value)));
        return str_replace(' ', '', $value);
    }
}
