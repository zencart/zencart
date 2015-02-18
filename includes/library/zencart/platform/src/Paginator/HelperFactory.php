<?php
/**
 * Class HelperFactory
 *
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
namespace ZenCart\Platform\Paginator;
/**
 * Class HelperFactory
 * @package ZenCart\Platform\Paginator
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
        $className = __NAMESPACE__ . '\\dataSources\\' . ucfirst($type);
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
        $className = __NAMESPACE__ . '\\scrollers\\' . ucfirst($type);
        $obj = new $className($data, $parameters);
        return $obj;
    }
} 
