<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Injection;

use Aura\Di\Container;
use Aura\Di\Resolver\Resolver;
use Interop\Container\ContainerInterface;
use ReflectionException;

/**
 *
 * A factory to create objects and values for injection into the Container.
 *
 * @package Aura.Di
 *
 */
class InjectionFactory
{
    /**
     *
     * A Resolver to provide class-creation specifics.
     *
     * @var Resolver
     *
     */
    protected $resolver;

    /**
     *
     * Constructor.
     *
     * @param Resolver $resolver A Resolver to provide class-creation specifics.
     *
     */
    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     *
     * Returns the Resolver.
     *
     * @return Resolver
     *
     */
    public function getResolver()
    {
        return $this->resolver;
    }

    /**
     *
     * Returns a new class instance.
     *
     * @param string $class The class to instantiate.
     *
     * @param array $params Params for the instantiation.
     *
     * @param array $setters Setters for the instantiation.
     *
     * @return mixed
     *
     */
    public function newInstance(
        $class,
        array $params = [],
        array $setters = []
    ) {
        $resolve = $this->resolver->resolve($class, $params, $setters);
        $object = $resolve->reflection->newInstanceArgs($resolve->params);
        foreach ($resolve->setters as $method => $value) {
            $object->$method($value);
        }
        return $object;
    }

    /**
     *
     * Returns a new Factory.
     *
     * @param string $class The class to create.
     *
     * @param array $params Override params for the class.
     *
     * @param array $setters Override setters for the class.
     *
     * @return Factory
     *
     */
    public function newFactory(
        $class,
        array $params = [],
        array $setters = []
    ) {
        return new Factory($this->resolver, $class, $params, $setters);
    }

    /**
     *
     * Returns a new Lazy.
     *
     * @param callable $callable The callable to invoke.
     *
     * @param array $params Arguments for the callable.
     *
     * @return Lazy
     *
     */
    public function newLazy($callable, array $params = [])
    {
        return new Lazy($callable, $params);
    }

    /**
     *
     * Returns a new LazyArray.
     *
     * @param array $callables The callables to invoke.
     *
     * @return LazyArray
     *
     */
    public function newLazyArray(array $callables)
    {
        return new LazyArray($callables);
    }

    /**
     *
     * Returns a new LazyCallable.
     *
     * @param callable $callable The callable to invoke.
     *
     * @return LazyCallable
     *
     */
    public function newLazyCallable($callable)
    {
        return new LazyCallable($callable);
    }

    /**
     *
     * Returns a new LazyGet.
     *
     * @param ContainerInterface $container The service container.
     *
     * @param string $service The service to retrieve.
     *
     * @return LazyGet
     *
     */
    public function newLazyGet(ContainerInterface $container, $service)
    {
        return new LazyGet($container, $service);
    }

    /**
     *
     * Returns a new LazyInclude.
     *
     * @param string $file The file to include.
     *
     * @return LazyInclude
     *
     */
    public function newLazyInclude($file)
    {
        return new LazyInclude($file);
    }

    /**
     *
     * Returns a new LazyNew.
     *
     * @param string $class The class to instantiate.
     *
     * @param array $params Params for the instantiation.
     *
     * @param array $setters Setters for the instantiation.
     *
     * @return Lazy
     *
     */
    public function newLazyNew(
        $class,
        array $params = [],
        array $setters = []
    ) {
        return new LazyNew($this->resolver, $class, $params, $setters);
    }

    /**
     *
     * Returns a new LazyRequire.
     *
     * @param string $file The file to require.
     *
     * @return LazyRequire
     *
     */
    public function newLazyRequire($file)
    {
        return new LazyRequire($file);
    }

    /**
     *
     * Returns a new LazyValue.
     *
     * @param string $key The value key to use.
     *
     * @return LazyValue
     *
     */
    public function newLazyValue($key)
    {
        return new LazyValue($this->resolver, $key);
    }
}
