<?php
/**
 * Load in any user functions
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 26 Modified in v2.2.1 $
 */
use Zencart\FileSystem\FileSystem;

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

$extraFuncsMain = (new FileSystem)->listFilesFromDirectoryAlphaSorted(DIR_WS_FUNCTIONS . 'extra_functions/', '~^[^\._].*\.php$~i');
$extraFuncsMain = array_map(static function ($item) {
    return DIR_WS_FUNCTIONS . 'extra_functions/' . $item;
}, $extraFuncsMain);
$context = IS_ADMIN_FLAG ? 'admin' : 'catalog';
$extraFuncsPlugins = [];
foreach ($installedPlugins as $plugin) {
    $path = DIR_FS_CATALOG . 'zc_plugins/' . $plugin['unique_key'] . '/' . $plugin['version'] . '/' . $context . '/' . DIR_WS_FUNCTIONS . 'extra_functions/';
    $efPluginFile = (new FileSystem)->listFilesFromDirectoryAlphaSorted($path, '~^[^\._].*\.php$~i');
    $efPluginFile = array_map(static function ($item) use ($path) {
        return $path . $item;
    }, $efPluginFile);
    $extraFuncsPlugins = array_merge($extraFuncsPlugins, $efPluginFile);
}
$extraFuncsFiles = array_merge($extraFuncsPlugins, $extraFuncsMain);

foreach ($extraFuncsFiles as $file) {
    if (!file_exists($file)) {
        continue;
    }
    include($file);
}

unset($extraFuncsMain, $extraFuncsPlugins, $extraFuncsFiles, $efPluginFile, $file);
