<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di;

use Aura\Di\Injection\InjectionFactory;
use Aura\Di\Resolver\AutoResolver;
use Aura\Di\Resolver\Reflector;
use Aura\Di\Resolver\Resolver;

/**
 *
 * Creates and configures a new DI container.
 *
 * @package Aura.Di
 *
 */
class ContainerBuilder
{
    /**
     *
     * Use the auto-resolver.
     *
     * @const true
     *
     */
    const AUTO_RESOLVE = true;

    /**
     *
     * Returns a new Container instance.
     *
     * @param bool $autoResolve Use the auto-resolver?
     *
     * @return Container
     *
     */
    public function newInstance($autoResolve = false)
    {
        $resolver = $this->newResolver($autoResolve);
        return new Container(new InjectionFactory($resolver));
    }

    /**
     *
     * Returns a new Resolver instance.
     *
     * @param bool $autoResolve Use the auto-resolver?
     *
     * @return Resolver
     *
     */
    protected function newResolver($autoResolve = false)
    {
        if ($autoResolve) {
            return new AutoResolver(new Reflector());
        }

        return new Resolver(new Reflector());
    }

    /**
     *
     * Creates a new Container, applies ContainerConfig classes to define()
     * services, locks the container, and applies the ContainerConfig instances
     * to modify() services.
     *
     * @param array $configClasses A list of ContainerConfig classes to
     * instantiate and invoke for configuring the Container.
     *
     * @param bool $autoResolve Use the auto-resolver?
     *
     * @return Container
     *
     * @throws Exception\SetterMethodNotFound
     *
     */
    public function newConfiguredInstance(
        array $configClasses = [],
        $autoResolve = false
    ) {
        $di = $this->newInstance($autoResolve);

        $configs = [];
        foreach ($configClasses as $configClass) {
            /** @var ContainerConfigInterface $config */
            $config = $this->getConfig($configClass);
            $config->define($di);
            $configs[] = $config;
        }

        $di->lock();

        foreach ($configs as $config) {
            $config->modify($di);
        }

        return $di;
    }

    /**
     *
     * Get config object from connfig class or return the object
     *
     * @param mixed $config name of class to instantiate
     *
     * @return Object
     * @throws InvalidArgumentException if invalid config
     *
     * @access protected
     */
    protected function getConfig($config)
    {
        if (is_string($config)) {
            $config = new $config;
        }

        if (! $config instanceof ContainerConfigInterface) {
            throw new \InvalidArgumentException(
                'Container configs must implement ContainerConfigInterface'
            );
        }

        return $config;
    }
}
