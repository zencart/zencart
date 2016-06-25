<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web\Request;

use ArrayObject;

/**
 *
 * A representation of read-only values, generally superglobal values.
 *
 * @package Aura.Web
 *
 */
class Values extends ArrayObject
{
    /**
     *
     * Pseudo-true representations.
     *
     * @var array
     *
     */
    protected $true = array('1', 'on', 'true', 't', 'yes', 'y');

    /**
     *
     * Pseudo-false representations.
     *
     * @var array
     *
     */
    protected $false = array('', '0', 'off', 'false', 'f', 'no', 'n');


    /**
     *
     * Constructor; identical to the parent ArrayObject, and copied here so
     * that DI mechanisms can read the constructor param names.
     *
     * @param mixed $input An array or an object.
     *
     * @param int $flags Flags to control the behavior of the ArrayObject.
     *
     * @param string $iterator_class The class that will be used for iteration
     * of the ArrayObject.
     *
     */
    public function __construct(
        $input = array(),
        $flags = 0,
        $iterator_class = 'ArrayIterator'
    ) {
        parent::__construct($input, $flags, $iterator_class);
    }

    /**
     *
     * Returns the value of an array key, or an alternative value if not set.
     *
     * @param string $key The array key to return.
     *
     * @param string $alt The alternative value if the key is not set.
     *
     * @return mixed The value of the array key, or the alternative value if
     * not set.
     *
     */
    public function get($key = null, $alt = null)
    {
        if (! $key) {
            return $this->getArrayCopy();
        }

        if (isset($this[$key])) {
            return $this[$key];
        }

        return $alt;
    }

    /**
     *
     * Returns the value of an array key intended as a boolean.
     *
     * @param string $key The array key to return.
     *
     * @param string $alt The alternative value if the key is not set, or if it
     * does not match one of the pseudo-boolean values.
     *
     * @return bool The value of the array key, or the alternative value.
     *
     */
    public function getBool($key, $alt = null)
    {
        if (! isset($this[$key])) {
            return $alt;
        }

        $val = $this[$key];
        if (in_array($val, $this->true, true)) {
            return true;
        }

        if (in_array($val, $this->false, true)) {
            return false;
        }

        return $alt;
    }

    /**
     *
     * Returns the value of an array key, casting it to an integer.
     *
     * @param string $key The array key to return.
     *
     * @param string $alt The alternative value if the key is not set.
     *
     * @return int The value of the array key cast to an integer, or the
     * alternative value.
     *
     */
    public function getInt($key, $alt = null)
    {
        if (! isset($this[$key])) {
            return $alt;
        }

        return (int) $this[$key];
    }

    /**
     *
     * Returns the value of an array key, casting it to a float.
     *
     * @param string $key The array key to return.
     *
     * @param string $alt The alternative value if the key is not set.
     *
     * @return int The value of the array key cast to a float, or the
     * alternative value.
     *
     */
    public function getFloat($key, $alt = null)
    {
        if (! isset($this[$key])) {
            return $alt;
        }

        return (float) $this[$key];
    }
}
