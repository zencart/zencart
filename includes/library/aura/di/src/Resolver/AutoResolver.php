<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Aura\Di\Resolver;

use Aura\Di\Injection\LazyNew;
use ReflectionParameter;

/**
 *
 * This extension of the Resolver additionally auto-resolves unspecified
 * constructor params according to their typehints; use with caution as it can
 * be very difficult to debug.
 *
 * @package Aura.Di
 *
 */
class AutoResolver extends Resolver
{
    /**
     *
     * Auto-resolve these typehints to these values.
     *
     * @var array
     *
     */
    protected $types = [];

    /**
     *
     * Auto-resolves params typehinted to classes.
     *
     * @param ReflectionParameter $rparam A parameter reflection.
     *
     * @param string $class The class name to return values for.
     *
     * @param array $parent The parent unified params.
     *
     * @return mixed The auto-resolved param value, or UnresolvedParam.
     *
     */
    protected function getUnifiedParam(ReflectionParameter $rparam, $class, $parent)
    {
        $unified = parent::getUnifiedParam($rparam, $class, $parent);

        // already resolved?
        if (! $unified instanceof UnresolvedParam) {
            return $unified;
        }

        // use an explicit auto-resolution?
        $rtype = $rparam->getClass();
        if ($rtype && isset($this->types[$rtype->name])) {
            return $this->types[$rtype->name];
        }

        // use a lazy-new-instance of the typehinted class?
        if ($rtype && $rtype->isInstantiable()) {
            return new LazyNew($this, $rtype->name);
        }

        // $unified is still an UnresolvedParam
        return $unified;
    }
}
