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
 * A collection of Container config instructions
 *
 * @package Aura.Di
 *
 */
class ConfigCollection extends ContainerConfig
{
    /**
     * Configs
     *
     * @var ContainerConfigInterface[]
     *
     * @access protected
     */
    protected $configs = [];

    /**
     * __construct
     *
     * @param array $configs A list of ContainerConfig classes to
     * instantiate and invoke for configuring the Container.
     *
     * @access public
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $config) {
            $config = $this->getConfig($config);
            $this->configs[] = $config;
        }
    }


    /**
     *
     * Get config object from connfig class or return the object
     *
     * @param mixed $config name of class to instantiate
     *
     * @return ContainerConfigInterface
     *
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

    /**
     *
     * Define params, setters, and services for each of the configs before the
     * Container is locked.
     *
     * @param Container $di The DI container.
     *
     * @return null
     *
     */
    public function define(Container $di)
    {
        foreach ($this->configs as $config) {
            $config->define($di);
        }
    }

    /**
     *
     * Modify service objects for each config after the Container is locked.
     *
     * @param Container $di The DI container.
     *
     * @return null
     *
     */
    public function modify(Container $di)
    {
        foreach ($this->configs as $config) {
            $config->modify($di);
        }
    }
}




