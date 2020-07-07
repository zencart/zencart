<?php
namespace Zcwilt\Api;

use Illuminate\Database\Eloquent\Model;
use Zcwilt\Api\Exceptions\InvalidModelException;

class ModelMakerFactory
{
    public function make(string $className): Model
    {
        $namespacePrefix = "";
        if (app()->runningUnitTests()) {
            $namespacePrefix = "\\Tests\\Fixtures";
        }
        if (class_exists($namespacePrefix . '\\App\\' . ucfirst($className), true)) {
            $className = $namespacePrefix . '\\App\\' . ucfirst($className);
            return new $className;
        }
        if (class_exists($namespacePrefix . '\\App\\Models\\' . ucfirst($className), true)) {
            $className = $namespacePrefix . '\\App\\Models\\' . ucfirst($className);
            return new $className;
        }
        if (class_exists(ucfirst($className), true)) {
            $className = ucfirst($className);
            return new $className;
        }
        throw new InvalidModelException($className);
    }
}
