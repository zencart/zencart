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
 * Returns the value of a callable when invoked (thereby invoking the callable).
 *
 * @package Aura.Di
 *
 */
class Lazy implements LazyInterface
{
    /**
     *
     * The callable to invoke.
     *
     * @var callable
     *
     */
    protected $callable;

    /**
     *
     * Arguments for the callable.
     *
     * @var array
     *
     */
    protected $params;

    /**
     *
     * Constructor.
     *
     * @param callable $callable The callable to invoke.
     *
     * @param array $params Arguments for the callable.
     *
     */
    public function __construct($callable, array $params = [])
    {
        $this->callable = $callable;
        $this->params = $params;
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
        // convert Lazy objects in the callable
        if (is_array($this->callable)) {
            foreach ($this->callable as $key => $val) {
                if ($val instanceof LazyInterface) {
                    $this->callable[$key] = $val();
                }
            }
        }

        // convert Lazy objects in the params
        foreach ($this->params as $key => $val) {
            if ($val instanceof LazyInterface) {
                $this->params[$key] = $val();
            }
        }

        // make the call
        return call_user_func_array($this->callable, $this->params);
    }
}
