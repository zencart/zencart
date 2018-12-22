<?php
/**
 * @package admin
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

use App\Controllers\ControllerFinder;

require('includes/application_bootstrap.php');

$cmd = ! empty($_GET['cmd']) ? $_GET['cmd'] : 'index';

$controllerMap = $configLoader->get('controllermap');
$controllerCommand = preg_replace('/[^a-zA-Z0-9_-]/', '', $cmd);
$foundAction = false;
if ($controllerCommand != $cmd) {
    $controllerCommand = 'index';
}
$controllerFinder = new ControllerFinder();
$actualController = $controllerFinder->getControllerName($controllerMap, $controllerCommand);
if ($actualController) {
    require('includes/application_top.php');
    require_once($controllerFinder->getControllerFile());
    $foundAction = true;
    $actionClass = $di->newInstance($actualController);
    $response = $actionClass->dispatch();
    $actionClass->handleResponse($response);
}

if ($foundAction) {
    exit(0);
} else {
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $cmd) . '.php';
    if (file_exists($filename)) { 
       require $filename;
    } else {
      require_once 'includes/application_top.php';
      zen_redirect(zen_admin_href_link(FILENAME_DEFAULT));
    }
}
