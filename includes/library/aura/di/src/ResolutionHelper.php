<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

/**
 *
 * Resolves object specifications using the DI container.
 *
 * @package aura/di
 *
 */
class ResolutionHelper
{
    /**
     *
     * The DI container.
     *
     * @var Container
     *
     */
    protected $container;

    /**
     *
     * Constructor.
     *
     * @param Container $container The DI container.
     *
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     *
     * Resolves an object specification.
     *
     * @param mixed $spec The object specification.
     *
     * @return mixed
     *
     */
    public function __invoke($spec)
    {
        if (is_string($spec)) {
            return $this->resolve($spec);
        }

        if (is_array($spec) && is_string($spec[0])) {
            $spec[0] = $this->resolve($spec[0]);
        }

        return $spec;
    }

    /**
     *
     * Get a named service or a new instance from the Container
     *
     * @param string $spec the name of the service or class to instantiate
     *
     * @return mixed
     *
     */
    protected function resolve($spec)
    {
        if ($this->container->has($spec)) {
            return $this->container->get($spec);
        }

        return $this->container->newInstance($spec);
    }
}

