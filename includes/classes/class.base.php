<?php
/**
 * File contains just the base class
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zcwilt 2020 Jul 01 Modified in v1.5.8-alpha $
 */
use Zencart\Traits\NotifierManager;
use Zencart\Traits\ObserverManager;

class base
{
    use NotifierManager;
    use ObserverManager;

    public static function camelize($rawName, $camelFirst = false)
    {
        if ($rawName == "")
            return $rawName;
        if ($camelFirst) {
            $rawName[0] = strtoupper($rawName[0]);
        }
        return preg_replace_callback('/[_-]([0-9,a-z])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $rawName);
    }
}
