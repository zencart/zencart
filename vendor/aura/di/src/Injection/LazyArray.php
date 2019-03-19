<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Injection;

use ArrayObject;

/**
 *
 * Returns the value of a callable when invoked (thereby invoking the callable).
 *
 * @package Aura.Di
 *
 */
class LazyArray extends ArrayObject implements LazyInterface
{

    /**
     *
     * Invoke any LazyInterface in the array.
     *
     * @return array The array of potentially invoked items.
     *
     */
    public function __invoke()
    {
        // convert Lazy objects in the callables
        foreach ($this as $key => $val) {
            if ($val instanceof LazyInterface) {
                $this[$key] = $val();
            }
        }

        // return array
        return $this->getArrayCopy();
    }
}
