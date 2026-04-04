<?php
// -----
// Part of the "Product Options Stock Manager" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2016-2022 Vinos de Frutas Tropicales
//
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
/*
** Point 80 is where the shopping cart class is loaded and instantiated, need to be there during cart processing.
*/ 
$autoLoadConfig[78][] = [
    'autoType'=>'class',
    'loadFile'=>'observers/class.products_options_stock_observer.php'
];
$autoLoadConfig[78][] = [
    'autoType'=>'classInstantiate',
    'className'=>'products_options_stock_observer',
    'objectName'=>'posObserver'
];
