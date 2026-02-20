<?php


namespace Tests\Models;


/**
 * @since ZC v1.5.8
 */
class ModelFactory
{

    /**
     * @since ZC v1.5.8
     */
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
