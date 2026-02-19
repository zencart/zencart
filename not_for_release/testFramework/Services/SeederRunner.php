<?php

namespace Tests\Services;

/**
 * @since ZC v2.0.0
 */
class SeederRunner
{

    /**
     * @since ZC v2.0.0
     */
    public function  run($seederClass, $parameters = [])
    {
        $namespace = '\\Seeders\\';
        $class = $namespace . $seederClass;
        $seeder = new $class;
        $seeder->run($parameters);
    }
}
