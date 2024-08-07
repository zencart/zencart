<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jul 15 Modified in v2.1.0-alpha1 $
 */

if (PHP_VERSION_ID < 80002) {
    die('Sorry, this version of Zen Cart requires PHP 8.0.2 or greater. <a href="https://www.zen-cart.com/requirements" rel="noopener" target="_blank">Please refer to our website</a> for the PHP versions supported.');
}

/**
 * Bootstrap file contains former application_top code
 *
 * Initializes common classes & methods. Controlled by an array which describes
 * the elements to be initialised and the order in which that happens.
 *
 */
require_once('includes/application_bootstrap.php');
/**
 * Prepare init-system
 */

use Zencart\InitSystem\InitSystem;
use Zencart\FileSystem\FileSystem;

if (isset($loaderPrefix)) {
    $loaderPrefix = preg_replace('/[^a-z_]/', '', $loaderPrefix);
} else {
    $loaderPrefix = 'config';
}
$initSystem = new InitSystem('admin', $loaderPrefix, new FileSystem, $pluginManager, $installedPlugins);

if (defined('DEBUG_AUTOLOAD') && DEBUG_AUTOLOAD == true) $initSystem->setDebug(true);

$loaderList = $initSystem->loadAutoLoaders();
$initSystemList = $initSystem->processLoaderList($loaderList);

require(DIR_FS_CATALOG . 'includes/autoload_func.php');
