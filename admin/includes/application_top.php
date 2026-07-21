<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 19 Modified in v2.2.1 $
 */

if (PHP_VERSION_ID < 80200) {
    die('Sorry, this version of Zen Cart requires PHP 8.2 or greater. <a href="https://www.zen-cart.com/requirements" rel="noopener" target="_blank">Please refer to our website</a> for the PHP versions supported.');
}

/**
 * Capture the genuine TCP peer address before any other code runs.
 *
 * This must happen before application_bootstrap.php (which loads the init system) and before
 * init_sessions.php later overwrites $_SERVER['REMOTE_ADDR'], so that trust decisions about
 * forwarded headers always see the real proxy/peer address. Request is not autoloadable this early
 * (the psr-4 autoloader is set up inside the bootstrap below), so the class and its trait are
 * required explicitly here via __DIR__-relative paths into the storefront classes directory. Only
 * the PHP-version guard above runs before this point, and it does not read $_SERVER['REMOTE_ADDR'].
 */
require_once __DIR__ . '/../../includes/classes/traits/Singleton.php';
require_once __DIR__ . '/../../includes/classes/Request.php';
\Zencart\Request\Request::captureOriginalRemoteAddr();

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
