<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

use Interop\Container\Exception\ContainerException;

/**
 *
 * Generic package exception.
 *
 * @package Aura.Di
 *
 */
class Exception extends \Exception implements ContainerException
{
    /**
     *
     * The container is locked and connot be modified.
     *
     * @return Exception\ContainerLocked
     *
     */
    static public function containerLocked()
    {
        throw new Exception\ContainerLocked("Cannot modify container when locked.");
    }

    /**
     *
     * A class constructor param was not defined.
     *
     * @param string $class The class name.
     *
     * @param string $param The constructor param name.
     *
     * @return Exception\MissingParam
     *
     */
    static public function missingParam($class, $param)
    {
        throw new Exception\MissingParam("Param missing: {$class}::\${$param}");
    }

    /**
     *
     * The container does not have a requested service.
     *
     * @param string $service The service name.
     *
     * @return Exception\ServiceNotFound
     *
     */
    static public function serviceNotFound($service)
    {
        throw new Exception\ServiceNotFound("Service not defined: '{$service}'");
    }

    /**
     *
     * The service was defined as something other than an object.
     *
     * @param string $service The service name.
     *
     * @param mixed $val The service definition.
     *
     * @return Exception\ServiceNotObject
     *
     */
    static public function serviceNotObject($service, $val)
    {
        $type = gettype($val);
        $message = "Expected service '{$service}' to be of type 'object', got '{$type}' instead.";
        throw new Exception\ServiceNotObject($message);
    }

    /**
     *
     * A setter method was defined, but it not available on the class.
     *
     * @param string $class The class name.
     *
     * @param string $method The method name.
     *
     * @return Exception\SetterMethodNotFound
     *
     */
    static public function setterMethodNotFound($class, $method)
    {
        throw new Exception\SetterMethodNotFound("Setter method not found: {$class}::{$method}()");
    }

    /**
     *
     * A requested property does not exist.
     *
     * @param string $name The property name.
     *
     * @return Exception\NoSuchProperty
     *
     */
    static public function noSuchProperty($name)
    {
        throw new Exception\NoSuchProperty("Property does not exist: \${$name}");
    }
}
