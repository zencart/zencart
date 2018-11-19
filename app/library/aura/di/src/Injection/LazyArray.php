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
class LazyArray implements LazyInterface
{
    /**
     *
     * Array of callables to invoke.
     *
     * @var array
     *
     */
    protected $callables = [];

    /**
     *
     * Constructor.
     *
     * @param array $callables The callables to invoke.
     *
     */
    public function __construct(array $callables)
    {
        $this->callables = $callables;
    }

    /**
     *
     * Invokes the array of closures to create the instance array.
     *
     * @return array The array of objects created by the closures.
     *
     */
    public function __invoke()
    {
        // convert Lazy objects in the callables
        foreach ($this->callables as $key => $val) {
            if ($val instanceof LazyInterface) {
                $this->callables[$key] = $val();
            }
        }

        // return array
        return $this->callables;
    }
}
