<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Injection;

/**
 *
 * Indicates a Lazy to be invoked when resolving params and setters.
 *
 * @package Aura.Di
 *
 */
interface LazyInterface
{
    /**
     *
     * Invokes the Lazy to return a result, usually an object.
     *
     * @return mixed
     *
     */
    public function __invoke();
}
