<?php

namespace Tests\Services;

use Tests\Services\Contracts\TestSeederInterface;

/**
 * @since ZC v2.0.0
 */
class SeederRunner
{

    /**
     * @since ZC v2.0.0
     */
    public function run(string $seederClass, array $parameters = []): void
    {
        $namespace = '\\Seeders\\';
        $class = $namespace . $seederClass;

        if (!class_exists($class)) {
            throw new TestFrameworkRunnerException(
                sprintf('Seeder class "%s" could not be loaded.', $class)
            );
        }

        $seeder = new $class;

        if (!$seeder instanceof TestSeederInterface) {
            throw new TestFrameworkRunnerException(
                sprintf('Seeder class "%s" must implement %s.', $class, TestSeederInterface::class)
            );
        }

        $seeder->run($parameters);
    }
}
