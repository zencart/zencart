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
 * Returns the value of a callable with parameters supplied at calltime (thereby
 * invoking the callable).
 *
 * @package Aura.Di
 *
 */
class LazyCallable implements LazyInterface
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
     * Whether or not the callable has been checked for instances of LazyInterface.
     *
     * @var bool
     *
     */
    protected $callableChecked = false;

    /**
     *
     * Constructor.
     *
     * @param callable $callable The callable to invoke.
     *
     */
    public function __construct($callable)
    {
        $this->callable = $callable;
    }

    /**
     *
     * Invokes the closure (which may return a value).
     *
     * @return mixed The value returned by the invoked callable (if any).
     *
     */
    public function __invoke()
    {
        if ($this->callableChecked === false) {
            // convert Lazy objects in the callable
            if (is_array($this->callable)) {
                foreach ($this->callable as $key => $val) {
                    if ($val instanceof LazyInterface) {
                        $this->callable[$key] = $val();
                    }
                }
            } elseif ($this->callable instanceof LazyInterface) {
                $this->callable = $this->callable->__invoke();
            }
            $this->callableChecked = true;
        }

        // make the call
        return call_user_func_array($this->callable, func_get_args());
    }
}
