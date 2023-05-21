<?php

namespace App\Services;

class SeederRunner
{

    public function  run($seederNamespace, $seederClass)
    {
        $namespace = '\\Seeders\\' . $seederNamespace . '\\';
        $class = $namespace . $seederClass;

        $seeder = new $class;
        $seeder->run();

    }
}
