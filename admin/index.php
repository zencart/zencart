<?php
/**
 * @package admin
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
if (!isset($_GET['cmd']))
{
  require('includes/application_top.php');
  zen_redirect(zen_href_link(str_replace('.php', '', basename($_SERVER ['SCRIPT_FILENAME'])), zen_get_all_get_params()));
}
$controllerCommand = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET ['cmd']);
$foundAction = FALSE;
if ($controllerCommand != $_GET['cmd'])
{
  $controllerCommand = 'index';
}
$zcActionClassName = 'zcActionAdmin' . ucfirst(zcCamelize($controllerCommand, true));
$zcActionFileName = 'includes/classes/actions/admin/class.' . $zcActionClassName . '.php';
if (file_exists($zcActionFileName))
{
  require('includes/application_top.php');
  require_once ($zcActionFileName);
  if (class_exists($zcActionClassName))
  {
    $foundAction = TRUE;
    $actionClass = new $zcActionClassName($controllerCommand);
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
