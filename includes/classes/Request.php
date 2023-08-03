<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2021 Jan 29 New in v1.5.8-alpha $
 */

namespace Zencart\Request;

use Zencart\Traits\Singleton;

class Request
{
    use Singleton;

    protected $paramBag;

    /**
     * @return mixed|Request
     */
    static function capture()
    {
        $self = self::getInstance();
        $self->paramBag = $_REQUEST;
        return self::getInstance();
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function input($key, $default = null)
    {
        return (isset($this->paramBag[$key]) ? $this->paramBag[$key] : $default);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return (isset($this->paramBag[$key]));
    }
}
