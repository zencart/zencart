<?php
/**
 * Class HelperFactory
 *
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Paginator;
/**
 * Class HelperFactory
 * @package ZenCart\Paginator
 */
class HelperFactory
{
    /**
     * makeDataSource method
     *
     * @param $type
     * @param $data
     * @param $parameters
     * @return mixed
     */
    static public function makeDataSource($type, $data, $parameters)
    {
        $className = '\\ZenCart\\Paginator\\dataSources\\' . ucfirst($type);
        $obj = new $className($data, $parameters);
        return $obj;
    }

    /**
     * makeScroller method
     *
     * @param $type
     * @param $data
     * @param $parameters
     * @return mixed
     */
    static public function makeScroller($type, $data, $parameters)
    {
        $className = '\\ZenCart\\Paginator\\scrollers\\' . ucfirst($type);
        $obj = new $className($data, $parameters);
        return $obj;
    }
} 
