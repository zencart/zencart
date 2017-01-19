<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */
namespace App\Controllers;

/**
 * Class ControllerFinder
 * @package App\Controllers
 */
class ControllerFinder
{

    /**
     * @var string
     */
    private $controllerFile;

    /**
     * @param $controllerMap
     * @param $controllerName
     * @return bool|string
     */
    public function getControllerName($controllerMap, $controllerName)
    {
        if (!isset($controllerMap[$controllerName])) {
            return false;
        }
        $scope = isset($controllerMap[$controllerName]['scope']) ? $controllerMap[$controllerName]['scope']: 'admin';
        $realName = ucfirst(zcCamelize($controllerName, true));
        $this->controllerFile =  DIR_FS_CATALOG . URL_CONTROLLERS . $scope . '/' . $realName . '.php';
        if (file_exists($this->controllerFile)) {
            return 'App\\Controllers\\' . $realName;
        }
        $baseClass = 'Base' . ucfirst($controllerMap[$controllerName]['type']) . 'Controller';
        $this->controllerFile =  DIR_FS_CATALOG . URL_CONTROLLERS . $scope . '/' . $baseClass . '.php';
        return 'App\\Controllers\\'. $baseClass;
    }

    /**
     * @return mixed
     */
    public function getControllerFile()
    {
        return $this->controllerFile;
    }
}
