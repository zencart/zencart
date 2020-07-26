<?php
/**
 * Load in any user functions
 *
 * @package initSystem
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.5.8 $
 */
use Zencart\FileSystem\FileSystem;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$extraFuncsMain = (new FileSystem)->listFilesFromDirectory(DIR_WS_FUNCTIONS . 'extra_functions/', '~^[^\._].*\.php$~i');
$extraFuncsMain = collect($extraFuncsMain)->map(function ($item, $key) {
    return DIR_WS_FUNCTIONS . 'extra_functions/' . $item;
})->toArray();
$context = (new FileSystem)->isAdminDir(__DIR__) ? 'admin' : 'catalog';
$extraFuncsPlugins = [];
foreach ($installedPlugins as $plugin) {
    $path = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/' . $context . '/' . DIR_WS_FUNCTIONS . 'extra_functions/';
    $observersPlugin = (new FileSystem)->listFilesFromDirectory($path, '~^[^\._].*\.php$~i');
    $observersPlugin = collect($observersPlugin)->map(function ($item, $key) use ($path) {
        return $path . $item;
    })->toArray();
    $extraFuncsPlugins = array_merge($extraFuncsPlugins, $observersPlugin);
}
$extraFuncsFiles = array_merge($extraFuncsPlugins, $extraFuncsMain);

foreach ($extraFuncsFiles as $file) {
    if (!file_exists($file)) {
        continue;
    }
    include($file);
}

unset($extraFuncsMain, $extraFuncsPlugins, $extraFuncsFiles, $file);
