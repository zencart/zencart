<?php
/**
 *
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 */
namespace Aura\Web\Request;

/**
 *
 * Represents path-info parameters, typically via a router map.
 *
 * @package Aura.Web
 *
 */
class Params extends Values
{
    /**
     *
     * Sets the param values.
     *
     * @param array $params The new param values.
     *
     * @return null
     *
     */
    public function set(array $params)
    {
        $this->exchangeArray($params);
    }
}
