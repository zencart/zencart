<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

use Aura\Di\Injection\Factory;
use Aura\Di\Injection\InjectionFactory;
use Aura\Di\Injection\Lazy;
use Aura\Di\Injection\LazyGet;
use Aura\Di\Injection\LazyInclude;
use Aura\Di\Injection\LazyInterface;
use Aura\Di\Injection\LazyNew;
use Aura\Di\Injection\LazyRequire;
use Aura\Di\Injection\LazyValue;
use Closure;
use Interop\Container\ContainerInterface;

/**
 *
 * Dependency injection container.
 *
 * @package Aura.Di
 *
 * @property array $params A reference to the Resolver $params.
 *
 * @property array $setters A reference to the Resolver $setters.
 *
 * @property array $types A reference to the Resolver $types.
 *
 * @property array $values A reference to the Resolver $values.
 *
 */
class Container implements ContainerInterface
{
    /**
     *
     * A factory to create objects and values for injection.
     *
     * @var InjectionFactory
     *
     */
    protected $injectionFactory;

    /**
     *
     * A container that will be used instead of the main container
     * to fetch dependencies.
     *
     * @var ContainerInterface
     *
     */
    protected $delegateContainer;

    /**
     *
     * A Resolver obtained from the InjectionFactory.
     *
     * @var Resolver\Resolver
     *
     */
    protected $resolver;

    /**
     *
     * Retains named service definitions.
     *
     * @var array
     *
     */
    protected $services = [];

    /**
     *
     * Retains the actual service object instances.
     *
     * @var array
     *
     */
    protected $instances = [];

    /**
     *
     * Is the Container locked?  (When locked, you cannot access configuration
     * properties from outside the object, and cannot set services.)
     *
     * @var bool
     *
     * @see __get()
     *
     * @see set()
     *
     */
    protected $locked = false;

    /**
     *
     * Constructor.
     *
     * @param InjectionFactory $injectionFactory A factory to create objects and
     * values for injection.
     *
     * @param ContainerInterface $delegateContainer An optional container
     * that will be used to fetch dependencies (i.e. lazy gets)
     *
     */
    public function __construct(
        InjectionFactory $injectionFactory,
        ContainerInterface $delegateContainer = null
    ) {
        $this->injectionFactory = $injectionFactory;
        $this->resolver = $this->injectionFactory->getResolver();
        $this->delegateContainer = $delegateContainer;
    }

    /**
     *
     * Magic get to provide access to the Resolver properties.
     *
     * @param string $key The Resolver property to retrieve.
     *
     * @return mixed
     *
     * @throws Exception\ContainerLocked
     *
     */
    public function &__get($key)
    {
        if ($this->locked) {
            throw Exception::containerLocked();
        }

        return $this->resolver->__get($key);
    }

    /**
     *
     * Returns the InjectionFactory.
     *
     * @return InjectionFactory
     *
     */
    public function getInjectionFactory()
    {
        return $this->injectionFactory;
    }

    /**
     *
     * Returns the secondary delegate container.
     *
     * @return mixed
     *
     */
    public function getDelegateContainer()
    {
        return $this->delegateContainer;
    }

    /**
     *
     * Locks the Container so that is it read-only.
     *
     * @return null
     *
     */
    public function lock()
    {
        $this->locked = true;
    }

    /**
     *
     * Is the Container locked?
     *
     * @return bool
     *
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     *
     * Does a particular service definition exist?
     *
     * @param string $service The service key to look up.
     *
     * @return bool
     *
     */
    public function has($service)
    {
        if (isset($this->services[$service])) {
            return true;
        }

        return isset($this->delegateContainer)
            && $this->delegateContainer->has($service);
    }

    /**
     *
     * Sets a service definition by name. If you set a service as a Closure,
     * it is automatically treated as a Lazy. (Note that is has to be a
     * Closure, not just any callable, to be treated as a Lazy; this is
     * because the actual service object itself might be callable via an
     * __invoke() method.)
     *
     * @param string $service The service key.
     *
     * @param object|callable $val The service object; if a Closure, is treated as a
     * Lazy.
     *
     * @throws Exception\ContainerLocked when the Container is locked.
     *
     * @throws Exception\ServiceNotObject
     *
     * @return $this
     *
     */
    public function set($service, $val)
    {
        if ($this->locked) {
            throw Exception::containerLocked();
        }

        if (! is_object($val)) {
            throw Exception::serviceNotObject($service, $val);
        }

        if ($val instanceof Closure) {
            $val = $this->injectionFactory->newLazy($val);
        }

        $this->services[$service] = $val;

        return $this;
    }

    /**
     *
     * Gets a service object by key.
     *
     * @param string $service The service to get.
     *
     * @return object
     *
     * @throws Exception\ServiceNotFound when the requested service
     * does not exist.
     *
     */
    public function get($service)
    {
        $this->locked = true;

        if (isset($this->instances[$service])) {
            return $this->instances[$service];
        }

        $this->instances[$service] = $this->getServiceInstance($service);
        return $this->instances[$service];
    }

    /**
     *
     * Instantiates a service object by key, lazy-loading it as needed.
     *
     * @param string $service The service to get.
     *
     * @return object
     *
     * @throws Exception\ServiceNotFound when the requested service
     * does not exist.
     *
     */
    protected function getServiceInstance($service)
    {
        // does the definition exist?
        if (! $this->has($service)) {
            throw Exception::serviceNotFound($service);
        }

        // is it defined in this container?
        if (! isset($this->services[$service])) {
            // no, get the instance from the delegate container
            return $this->delegateContainer->get($service);
        }

        // instantiate it from its definition
        $instance = $this->services[$service];

        // lazy-load as needed
        if ($instance instanceof LazyInterface) {
            $instance = $instance();
        }

        // done
        return $instance;
    }

    /**
     *
     * Gets the list of instantiated services.
     *
     * @return array
     *
     */
    public function getInstances()
    {
        return array_keys($this->instances);
    }

    /**
     *
     * Gets the list of service definitions.
     *
     * @return array
     *
     */
    public function getServices()
    {
        return array_keys($this->services);
    }

    /**
     *
     * Returns a lazy object that calls a callable, optionally with arguments.
     *
     * @param callable $callable The callable.
     *
     * @return Lazy
     *
     */
    public function lazy($callable)
    {
        $params = func_get_args();
        array_shift($params);
        return $this->injectionFactory->newLazy($callable, $params);
    }

    /**
     *
     * Returns a lazy object that gets a service.
     *
     * @param string $service The service name; it does not need to exist yet.
     *
     * @return LazyGet
     *
     */
    public function lazyGet($service)
    {
        return $this->injectionFactory->newLazyGet($this, $service);
    }

    /**
     *
     * Returns a lazy object that gets a service and calls a method on it,
     * optionally with paramters.
     *
     * @param string $service The service name.
     *
     * @param string $method The method to call on the service object.
     *
     * @param ...$params Parameters to use in the method call.
     *
     * @return Lazy
     *
     */
    public function lazyGetCall($service, $method)
    {
        $callable = [$this->lazyGet($service), $method];

        $params = func_get_args();
        array_shift($params); // $service
        array_shift($params); // $method

        return $this->injectionFactory->newLazy($callable, $params);
    }

    /**
     *
     * Returns a lazy object that creates a new instance.
     *
     * @param string $class The type of class of instantiate.
     *
     * @param array $params Override parameters for the instance.
     *
     * @param array $setters Override setters for the instance.
     *
     * @return LazyNew
     *
     */
    public function lazyNew(
        $class,
        array $params = [],
        array $setters = []
    ) {
        return $this->injectionFactory->newLazyNew($class, $params, $setters);
    }

    /**
     *
     * Returns a lazy that requires a file.
     *
     * @param string $file The file to require.
     *
     * @return LazyRequire
     *
     */
    public function lazyRequire($file)
    {
        return $this->injectionFactory->newLazyRequire($file);
    }

    /**
     *
     * Returns a lazy that includes a file.
     *
     * @param string $file The file to include.
     *
     * @return LazyInclude
     *
     */
    public function lazyInclude($file)
    {
        return $this->injectionFactory->newLazyInclude($file);
    }

    /**
     *
     * Returns a lazy for an arbitrary value.
     *
     * @param string $key The arbitrary value key.
     *
     * @return LazyValue
     *
     */
    public function lazyValue($key)
    {
        return $this->injectionFactory->newLazyValue($key);
    }

    /**
     *
     * Returns a factory that creates an object over and over again (as vs
     * creating it one time like the lazyNew() or newInstance() methods).
     *
     * @param string $class The factory will create an instance of this class.
     *
     * @param array $params Override parameters for the instance.
     *
     * @param array $setters Override setters for the instance.
     *
     * @return Factory
     *
     */
    public function newFactory(
        $class,
        array $params = [],
        array $setters = []
    ) {
        return $this->injectionFactory->newFactory(
            $class,
            $params,
            $setters
        );
    }

    /**
     *
     * Creates and returns a new instance of a class using reflection and
     * the configuration parameters, optionally with overrides, invoking Lazy
     * values along the way.
     *
     * Note the that container must be locked before creating a new instance.
     * This prevents premature resolution of params and setters.
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
    public function newInstance(
        $class,
        array $mergeParams = [],
        array $mergeSetters = []
    ) {
        $this->locked = true;
        return $this->injectionFactory->newInstance(
            $class,
            $mergeParams,
            $mergeSetters
        );
    }
}
