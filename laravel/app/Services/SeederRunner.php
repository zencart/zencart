<?php

namespace App\Services;

class SeederRunner
{

    public function  run($seederClass, $parameters = [])
    {
        $namespace = '\\Seeders\\';
        $class = $namespace . $seederClass;
        $seeder = new $class;
        $seeder->run($parameters);
    }
}
