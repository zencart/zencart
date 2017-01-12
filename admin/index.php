<?php
use ZenCart\AdminUser\AdminUser;
/**
 * @package admin
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
require('includes/application_bootstrap.php');

$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'index';

$controllerCommand = preg_replace('/[^a-zA-Z0-9_-]/', '', $cmd);
$foundAction = false;
if ($controllerCommand != $cmd) {
    $controllerCommand = 'index';
}
$controllerName = 'ZenCart\\Controllers\\'. ucfirst(zcCamelize($controllerCommand, true));
$controllerFile =  DIR_CATALOG_LIBRARY . URL_CONTROLLERS . '/admin/' . ucfirst(zcCamelize($controllerCommand, true)) . '.php';
if (file_exists($controllerFile)) {
    require('includes/application_top.php');
    require_once ($controllerFile);
    if (class_exists($controllerName)) {
        $foundAction = true;
        $actionClass = $di->newInstance($controllerName);
        $response = $actionClass->dispatch();
        $actionClass->handleResponse($response);
    }
}
if ($foundAction) {
    exit(0);
} else {
    require(preg_replace('/[^a-zA-Z0-9_-]/', '', $cmd) . '.php');
}

/**
 * @param $rawName
 * @param bool $camelFirst
 * @return mixed
 */
function zcCamelize($rawName, $camelFirst = false)
{
    if ($rawName == "")
        return $rawName;
    if ($camelFirst) {
        $rawName[0] = strtoupper($rawName[0]);
    }
    return preg_replace_callback('/[_-]([0-9,a-z])/', create_function('$matches', 'return strtoupper($matches[1]);'), $rawName);
}
