<?php
/**
 * ajaxGetProgressValues.php
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', realpath(dirname(__FILE__) . '/') . '/');
define('DIR_FS_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

require(DIR_FS_INSTALL . 'includes/application_top.php');

$fp = fopen('../logs/progress.json', "r"); 
$json = fread($fp, 1000);
fclose($fp);
echo $json;
