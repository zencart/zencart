<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Injection;

use Aura\Di\Resolver\Resolver;

/**
 *
 * A generic factory to create repeated instances of a single class. Note that
 * it does not implement the LazyInterface, so it is not automatically invoked
 * when resolving params and setters.
 *
 * @package Aura.Di
 *
 */
class Factory
{
    /**
     *
     * The Resolver.
     *
     * @var Resolver
     *
     */
    protected $resolver;

    /**
     *
     * The class to create.
     *
     * @var string
     *
     */
    protected $class;

    /**
     *
     * Override params for the class.
     *
     * @var array
     *
     */
    protected $params;

    /**
     *
     * Override setters for the class.
     *
     * @var array
     *
     */
    protected $setters;

    /**
     *
     * Constructor.
     *
     * @param Resolver $resolver A Resolver to provide class-creation specifics.
     *
     * @param string $class The class to create.
     *
     * @param array $params Override params for the class.
     *
     * @param array $setters Override setters for the class.
     *
     */
    public function __construct(
        Resolver $resolver,
        $class,
        array $params = [],
        array $setters = []
    ) {
        $this->resolver = $resolver;
        $this->class = $class;
        $this->params = $params;
        $this->setters = $setters;
    }

    /**
     *
     * Invoke the Factory object as a function to use the Factory to create
     * a new instance of the specified class; pass sequential parameters as
     * as yet another set of constructor parameter overrides.
     *
     * Why the overrides for the overrides?  So that any package that needs a
     * factory can make its own, using sequential params in a function; then
     * the factory call can be replaced by a call to this Factory.
     *
     * @return object
     *
     */
    public function __invoke()
    {
        $params = array_merge($this->params, func_get_args());
        $resolve = $this->resolver->resolve(
            $this->class,
            $params,
            $this->setters
        );

        $object = $resolve->reflection->newInstanceArgs($resolve->params);
        foreach ($resolve->setters as $method => $value) {
            $object->$method($value);
        }
        return $object;
    }
}
