<?php
// -----
// Part of the "Product Options Stock" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2014-2024 Vinos de Frutas Tropicales
//
// Last updated: POSM v5.0.0
//
$autoLoadConfig[200][] = [
    'autoType' => 'class',
    'loadFile' => 'observers/class.products_options_stock_admin_observer.php',
    'classPath' => DIR_WS_CLASSES
];
$autoLoadConfig[200][] = [
    'autoType' => 'classInstantiate',
    'className' => 'products_options_stock_observer',
    'objectName' => 'posObserver'
];
$autoLoadConfig[200][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_posm_admin.php'
];
