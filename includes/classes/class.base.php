<?php
/**
 * File contains just the base class
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 * @since ZC v1.3.0
 */
use Zencart\Traits\NotifierManager;
use Zencart\Traits\ObserverManager;

class base
{
    use NotifierManager;
    use ObserverManager;

    /**
     * @since ZC v1.5.2
     */
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
