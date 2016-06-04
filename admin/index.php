<?php
/**
 * @package admin
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
require('includes/application_bootstrap.php');

if (!isset($_GET['cmd']))
{
    $cmd = str_replace('.php', '', basename($_SERVER['SCRIPT_FILENAME']));
    $_GET['cmd'] = $cmd;

    // Only redirect if not a request for "index.php"
    if($cmd != 'index') {
        require('includes/application_top.php');
        zen_redirect(zen_admin_href_link(str_replace('.php', '', basename($_SERVER ['SCRIPT_FILENAME'])), zen_get_all_get_params()));
    }

    // Populate the command and continue
    unset($cmd);
}
$controllerCommand = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET ['cmd']);
$foundAction = FALSE;
if ($controllerCommand != $_GET['cmd'])
{
    $controllerCommand = 'index';
}
$controllerName = 'ZenCart\\Controllers\\'. ucfirst(zcCamelize($controllerCommand, true));
$controllerFile =  DIR_CATALOG_LIBRARY . URL_CONTROLLERS . '/admin/' . ucfirst(zcCamelize($controllerCommand, true)) . '.php';
if (file_exists($controllerFile))
{
    require('includes/application_top.php');
    require_once ($controllerFile);
    if (class_exists($controllerName))
    {
        $foundAction = TRUE;
        $actionClass = new $controllerName($controllerCommand, $zcRequest, $db);
        $actionClass->invoke();
    }
}
if ($foundAction)
{
    die(0);
} else
{
    require(preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET ['cmd']) . '.php');
}
function zcCamelize($rawName, $camelFirst = FALSE)
{
    if ($rawName == "")
        return $rawName;
    if ($camelFirst)
    {
        $rawName[0] = strtoupper($rawName[0]);
    }
    return preg_replace_callback('/[_-]([0-9,a-z])/', create_function('$matches', 'return strtoupper($matches[1]);'), $rawName);
}
