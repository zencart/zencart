<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */

namespace ZenCart\Services;
use Zencart\Controllers\AbstractAdminController as Controller;
use ZenCart\Request\Request as Request;

/**
 * Class ServiceFactory
 * @package ZenCart\Services
 */
class ServiceFactory
{

    /**
     * @param $servicePrefix
     * @param $serviceSuffix
     * @param Controller $listener
     * @param Request $request
     * @param $dbConn
     * @return mixed
     */
    public function factory($servicePrefix, $serviceSuffix, Controller $listener, Request $request, $dbConn)
    {
        $classname = get_class($listener);
        if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) {
            $classname = $matches[1];
        }
        $testClass = __NAMESPACE__ . '\\' . $servicePrefix . $classname . $serviceSuffix;
        if (class_exists($testClass)) {
            return new $testClass($listener, $request, $dbConn);
        } else {
            $serviceName = __NAMESPACE__ . '\\' . $servicePrefix . $serviceSuffix;
            return new $serviceName($listener, $request, $dbConn);
        }
    }
}
