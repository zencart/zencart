<?php


namespace App\Models;


class ModelFactory
{

    public function make($modelName, $namespace = null)
    {
        if (!$namespace) {
            $namespace = '\\App\\Models\\';
        }
        $className = $namespace . $modelName;
        $modelClass = new $className;
        return $modelClass;
    }
}
