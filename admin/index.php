<?php
use ZenCart\AdminUser\AdminUser;
use App\Controllers\ControllerFinder;
/**
 * @package admin
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
require('includes/application_bootstrap.php');

$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'index';

$controllerMap = [
    'countries' => ['type' => 'lead'],
    'group_pricing' => ['type' => 'lead'],
    'currencies' => ['type' => 'lead'],
    'zones' => ['type' => 'lead'],
    'geo_zones' => ['type' => 'lead'],
    'geo_zones_detail' => ['type' => 'lead'],
    'tax_classes' => ['type' => 'lead'],
    'tax_rates' => ['type' => 'lead'],
    'languages' => ['type' => 'lead'],
    'orders_status' => ['type' => 'lead'],
    'record_artists' => ['type' => 'lead'],
    'record_company' => ['type' => 'lead'],
    'music_genre' => ['type' => 'lead'],
    'media_manager' => ['type' => 'lead'],
    'media_manager_clips' => ['type' => 'lead'],
    'media_manager_products' => ['type' => 'lead'],
    'media_types' => ['type' => 'lead'],
    'dup_models' => ['type' => 'lead'],
    'gv_queue' => ['type' => 'lead'],
    'gv_sent' => ['type' => 'report'],
    'stats_customers' => ['type' => 'report'],
    'stats_customers_referrals' => ['type' => 'report'],
    'stats_products_lowstock' => ['type' => 'report'],
    'stats_products_purchased' => ['type' => 'report'],
    'stats_products_viewed' => ['type' => 'report'],
    'index' => ['type' => 'admin'],
    'server_info' => ['type' => 'info'],
    'system_inspection' => ['type' => 'info'],
];


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
