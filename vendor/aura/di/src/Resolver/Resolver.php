<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Resolver;

use Aura\Di\Exception;
use Aura\Di\Injection\LazyInterface;
use ReflectionParameter;

/**
 *
 * Resolves class creation specifics based on constructor params and setter
 * definitions, unified across class defaults, inheritance hierarchies, and
 * configuration.
 *
 * @package Aura.Di
 *
 */
class Resolver
{
    /**
     *
     * Constructor params in the form `$params[$class][$name] = $value`.
     *
     * @var array
     *
     */
    protected $params = [];

    /**
     *
     * Setter definitions in the form of `$setters[$class][$method] = $value`.
     *
     * @var array
     *
     */
    protected $setters = [];

    /**
     *
     * Arbitrary values in the form of `$values[$key] = $value`.
     *
     * @var array
     *
     */
    protected $values = [];

    /**
     *
     * A Reflector.
     *
     * @var Reflector
     *
     */
    protected $reflector = [];

    /**
     *
     * Constructor params and setter definitions, unified across class
     * defaults, inheritance hierarchies, and configuration.
     *
     * @var array
     *
     */
    protected $unified = [];

    /**
     *
     * Constructor.
     *
     * @param Reflector $reflector A collection point for Reflection data.
     *
     */
    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    /**
     *
     * Returns a reference to various property arrays.
     *
     * @param string $key The property name to return.
     *
     * @return array
     *
     * @throws Exception\NoSuchProperty
     *
     */
    public function &__get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }
        throw Exception::noSuchProperty($key);
    }

    /**
     *
     * Creates and returns a new instance of a class using reflection and
     * the configuration parameters, optionally with overrides, invoking Lazy
     * values along the way.
     *
     * @param string $class The class to instantiate.
     *
     * @param array $mergeParams An array of override parameters; the key may
     * be the name *or* the numeric position of the constructor parameter, and
     * the value is the parameter value to use.
     *
     * @param array $mergeSetters An array of override setters; the key is the
     * name of the setter method to call and the value is the value to be
     * passed to the setter method.
     *
     * @return object
     *
     * @throws Exception\SetterMethodNotFound
     *
     */
    public function resolve(
        $class,
        array $mergeParams = [],
        array $mergeSetters = []
    ) {
        list($params, $setters) = $this->getUnified($class);
        $this->mergeParams($class, $params, $mergeParams);
        $this->mergeSetters($class, $setters, $mergeSetters);
        return (object) [
            'reflection' => $this->reflector->getClass($class),
            'params' => $params,
            'setters' => $setters,
        ];
    }

    /**
     *
     * Merges the setters with overrides; also invokes Lazy values.
     *
     * @param string $class The setters are on this class.
     *
     * @param array $setters The class setters.
     *
     * @param array $mergeSetters Override with these setters.
     *
     * @return null
     *
     */
    protected function mergeSetters($class, &$setters, array $mergeSetters = [])
    {
        $setters = array_merge($setters, $mergeSetters);
        foreach ($setters as $method => $value) {
            if (! method_exists($class, $method)) {
                throw Exception::setterMethodNotFound($class, $method);
            }
            if ($value instanceof LazyInterface) {
                $setters[$method] = $value();
            }
        }
    }

    /**
     *
     * Merges the params with overides; also invokes Lazy values.
     *
     * @param string $class The params are on this class.
     *
     * @param array $params The constructor parameters.
     *
     * @param array $mergeParams An array of override parameters; the key may
     * be the name *or* the numeric position of the constructor parameter, and
     * the value is the parameter value to use.
     *
     * @return array
     *
     * @throws \Aura\Di\Exception\MissingParam if a constructor param is missing.
     *
     */
    protected function mergeParams($class, &$params, array $mergeParams = [])
    {
        if (! $mergeParams) {
            // no params to merge, micro-optimize the loop
            $this->mergeParamsEmpty($class, $params);
            return;
        }

        $pos = 0;
        foreach ($params as $key => $val) {

            // positional overrides take precedence over named overrides
            if (array_key_exists($pos, $mergeParams)) {
                // positional override
                $val = $mergeParams[$pos];
            } elseif (array_key_exists($key, $mergeParams)) {
                // named override
                $val = $mergeParams[$key];
            }

            // is the param missing?
            if ($val instanceof UnresolvedParam) {
                throw Exception::missingParam($class, $val->getName());
            }

            // load lazy objects as we go
            if ($val instanceof LazyInterface) {
                $val = $val();
            }

            // retain the merged value
            $params[$key] = $val;

            // next position
            $pos += 1;
        }
    }

    /**
     *
     * Load the Lazy values in params when the mergeParams are empty.
     *
     * @param string $class The params are on this class.
     *
     * @param array $params The constructor parameters.
     *
     * @return null
     *
     * @throws \Aura\Di\Exception\MissingParam if a constructor param is missing.
     *
     */
    protected function mergeParamsEmpty($class, &$params)
    {
        foreach ($params as $key => $val) {
            // is the param missing?
            if ($val instanceof UnresolvedParam) {
                throw Exception::missingParam($class, $val->getName());
            }
            // load lazy objects as we go
            if ($val instanceof LazyInterface) {
                $params[$key] = $val();
            }
        }
    }

    /**
     *
     * Returns the unified constructor params and setters for a class.
     *
     * @param string $class The class name to return values for.
     *
     * @return array An array with two elements; 0 is the constructor params
     * for the class, and 1 is the setter methods and values for the class.
     *
     */
    public function getUnified($class)
    {
        // have values already been unified for this class?
        if (isset($this->unified[$class])) {
            return $this->unified[$class];
        }

        // default to an an array of two empty arrays
        // (one for params, one for setters)
        $spec = [[], []];

        // fetch the values for parents so we can inherit them
        $parent = get_parent_class($class);
        if ($parent) {
            $spec = $this->getUnified($parent);
        }

        // stores the unified params and setters
        $this->unified[$class][0] = $this->getUnifiedParams($class, $spec[0]);
        $this->unified[$class][1] = $this->getUnifiedSetters($class, $spec[1]);

        // done, return the unified values
        return $this->unified[$class];
    }

    /**
     *
     * Returns the unified constructor params for a class.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @return array The unified params.
     *
     */
    protected function getUnifiedParams($class, array $parent)
    {
        // reflect on what params to pass, in which order
        $unified = [];
        $rparams = $this->reflector->getParams($class);
        foreach ($rparams as $rparam) {
            $unified[$rparam->name] = $this->getUnifiedParam(
                $rparam,
                $class,
                $parent
            );
        }

        // done
        return $unified;
    }

    /**
     *
     * Returns a unified param.
     *
     * @param ReflectionParameter $rparam A parameter reflection.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @return mixed The unified param value.
     *
     */
    protected function getUnifiedParam(ReflectionParameter $rparam, $class, $parent)
    {
        $name = $rparam->getName();
        $pos = $rparam->getPosition();

        // is there a positional value explicitly from the current class?
        $explicitPos = isset($this->params[$class])
                 && array_key_exists($pos, $this->params[$class])
                 && ! $this->params[$class][$pos] instanceof UnresolvedParam;
        if ($explicitPos) {
            return $this->params[$class][$pos];
        }

        // is there a named value explicitly from the current class?
        $explicitNamed = isset($this->params[$class])
                 && array_key_exists($name, $this->params[$class])
                 && ! $this->params[$class][$name] instanceof UnresolvedParam;
        if ($explicitNamed) {
            return $this->params[$class][$name];
        }

        // is there a named value implicitly inherited from the parent class?
        // (there cannot be a positional parent. this is because the unified
        // values are stored by name, not position.)
        $implicitNamed = array_key_exists($name, $parent)
                 && ! $parent[$name] instanceof UnresolvedParam;
        if ($implicitNamed) {
            return $parent[$name];
        }

        // is a default value available for the current class?
        if ($rparam->isDefaultValueAvailable()) {
            return $rparam->getDefaultValue();
        }

        // param is missing
        return new UnresolvedParam($name);
    }

    /**
     *
     * Returns the unified setters for a class.
     *
     * Class-specific setters take precendence over trait-based setters, which
     * take precedence over interface-based setters.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified setters.
     *
     * @return array The unified setters.
     *
     */
    protected function getUnifiedSetters($class, array $parent)
    {
        $unified = $parent;

        // look for interface setters
        $interfaces = class_implements($class);
        foreach ($interfaces as $interface) {
            if (isset($this->setters[$interface])) {
                $unified = array_merge(
                    $this->setters[$interface],
                    $unified
                );
            }
        }

        // look for trait setters
        $traits = $this->reflector->getTraits($class);
        foreach ($traits as $trait) {
            if (isset($this->setters[$trait])) {
                $unified = array_merge(
                    $this->setters[$trait],
                    $unified
                );
            }
        }

        // look for class setters
        if (isset($this->setters[$class])) {
            $unified = array_merge(
                $unified,
                $this->setters[$class]
            );
        }

        // done
        return $unified;
    }
}
