<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.5.8 $
 */

$psr4Autoloader->addPrefix('Zencart\QueryBuilder', DIR_FS_CATALOG . DIR_WS_CLASSES);
$psr4Autoloader->addPrefix('Zencart\Traits', DIR_FS_CATALOG . DIR_WS_CLASSES . 'traits');
$psr4Autoloader->addPrefix('Zencart\FileSystem', DIR_FS_CATALOG . DIR_WS_CLASSES );
$psr4Autoloader->addPrefix('Zencart\InitSystem', DIR_FS_CATALOG . DIR_WS_CLASSES );
$psr4Autoloader->addPrefix('Zencart\PluginManager', DIR_FS_CATALOG . DIR_WS_CLASSES);
$psr4Autoloader->addPrefix('Zencart\LanguageLoader', DIR_FS_CATALOG . DIR_WS_CLASSES . 'ResourceLoaders');
$psr4Autoloader->addPrefix('Zencart\ResourceLoaders', DIR_FS_CATALOG . DIR_WS_CLASSES . 'ResourceLoaders');
$psr4Autoloader->addPrefix('Zencart\PageLoader', DIR_FS_CATALOG . DIR_WS_CLASSES . 'ResourceLoaders');
$psr4Autoloader->addPrefix('Zencart\Events', DIR_FS_CATALOG . DIR_WS_CLASSES );
$psr4Autoloader->addPrefix('Zencart\PluginSupport', DIR_FS_CATALOG . DIR_WS_CLASSES . 'PluginSupport');
$psr4Autoloader->addPrefix('Zencart\TableViewControllers', DIR_FS_CATALOG . DIR_WS_CLASSES . 'TableViewControllers');
$psr4Autoloader->addPrefix('Zencart\Exceptions', DIR_FS_CATALOG . DIR_WS_CLASSES . 'Exceptions');
$psr4Autoloader->addPrefix('Zencart\Filters', DIR_FS_CATALOG . DIR_WS_CLASSES . 'Filters');
if (defined('DIR_FS_ADMIN')) {
    $psr4Autoloader->addPrefix('Zencart\Paginator', DIR_FS_ADMIN . DIR_WS_CLASSES);
}
