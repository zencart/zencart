<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Resolver;

/**
 *
 * A placeholder object to indicate a constructor param is missing.
 *
 * @package Aura.Di
 *
 */
class UnresolvedParam
{
    /**
     *
     * The name of the missing param.
     *
     * @var string
     *
     */
    protected $name;

    /**
     *
     * Constructor.
     *
     * @param string $name The name of the missing param.
     *
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     *
     * Returns the name of the missing param.
     *
     * @param string $class Prefix the param name with this class name.
     *
     * @return string
     *
     */
    public function getName()
    {
        return $this->name;
    }
}
