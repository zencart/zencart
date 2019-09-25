<?php
$psr4Autoloader->addPrefix('Zencart\FileSystem', DIR_FS_CATALOG . DIR_WS_CLASSES );
$psr4Autoloader->addPrefix('Zencart\InitSystem', DIR_FS_CATALOG . DIR_WS_CLASSES );
$psr4Autoloader->addPrefix('Zencart\Traits', DIR_FS_CATALOG . DIR_WS_CLASSES . 'traits');
$psr4Autoloader->addPrefix('Zencart\LanguageLoader', DIR_FS_CATALOG . DIR_WS_CLASSES . 'ResourceLoaders');
$psr4Autoloader->addPrefix('Zencart\PluginManager', DIR_FS_CATALOG . DIR_WS_CLASSES);
$psr4Autoloader->addPrefix('Illuminate\Database', DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/illuminate/database');
$psr4Autoloader->addPrefix('Illuminate\Support', DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/illuminate/support');
$psr4Autoloader->addPrefix('Illuminate\Container', DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/illuminate/container');
$psr4Autoloader->addPrefix('Illuminate\Contracts', DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/illuminate/contracts');
$psr4Autoloader->addPrefix('Psr\Container', DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/psr/container/src');
