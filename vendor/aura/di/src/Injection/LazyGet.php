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
use Interop\Container\ContainerInterface;

/**
 *
 * Returns a Container service when invoked.
 *
 * @package Aura.Di
 *
 */
class LazyGet implements LazyInterface
{
    /**
     *
     * The service container.
     *
     * @var ContainerInterface
     *
     */
    protected $container;

    /**
     *
     * The service name to retrieve.
     *
     * @var string
     *
     */
    protected $service;

    /**
     *
     * Constructor.
     *
     * @param ContainerInterface $container The service container.
     *
     * @param string $service The service to retrieve.
     *
     */
    public function __construct(ContainerInterface $container, $service)
    {
        $this->container = $container;
        $this->service = $service;
    }

    /**
     *
     * Invokes the closure to create the instance.
     *
     * @return object The object created by the closure.
     *
     */
    public function __invoke()
    {
        return $this->container->get($this->service);
    }
}
