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
 * An interface for a set of Container configuration instructions.
 *
 * @package Aura.Di
 *
 */
interface ContainerConfigInterface
{
    /**
     *
     * Define params, setters, and services before the Container is locked.
     *
     * @param Container $di The DI container.
     *
     * @return null
     *
     */
    public function define(Container $di);

    /**
     *
     * Modify service objects after the Container is locked.
     *
     * @param Container $di The DI container.
     *
     * @return null
     *
     */
    public function modify(Container $di);
}
