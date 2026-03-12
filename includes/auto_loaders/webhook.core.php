<?php
/**
 * autoloader array for webhooks
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Last updated: v1.2.2
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$autoLoadConfig[0][] = [
    'autoType' => 'include',
    'loadFile' => DIR_WS_INCLUDES . 'version.php',
];
//- notifier class loaded via psr4Autoload.php; reloading
//- here for older versions of Zen Cart that don't load the
//- class via an autoloader.
$autoLoadConfig[0][] = [
    'autoType' => 'class',
    'loadFile' => 'class.notifier.php',
];
$autoLoadConfig[0][] = [
    'autoType' => 'classInstantiate',
    'className' => 'notifier',
    'objectName' => 'zco_notifier',
];
$autoLoadConfig[0][] = [
    'autoType' => 'class',
    'loadFile' => 'class.phpmailer.php',
];
/**
 * Breakpoint 30.
 *
 * $zc_cache = new cache();
 *
 */
$autoLoadConfig[30][] = [
    'autoType' => 'classInstantiate',
    'className' => 'cache',
    'objectName' => 'zc_cache',
];
/**
 * Breakpoint 40.
 *
 * require 'includes/init_includes/init_db_config_read.php';
 *
 */
$autoLoadConfig[40][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_db_config_read.php',
];
//- sniffer class loaded via psr4Autoload.php; reloading
//- here for older versions of Zen Cart that don't load the
//- class via an autoloader.
$autoLoadConfig[50][] = [
    'autoType' => 'class',
    'loadFile' => 'sniffer.php',
];
$autoLoadConfig[50][] = [
    'autoType' => 'classInstantiate',
    'className' => 'sniffer',
    'objectName' => 'sniffer',
];
$autoLoadConfig[50][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_sefu.php',
];
/**
 * Breakpoint 60.
 *
 * require 'includes/init_includes/init_general_funcs.php';
 * require 'includes/init_includes/init_tlds.php';
 *
 */
$autoLoadConfig[60][] = [
    'autoType' => 'require',
    'loadFile' => DIR_WS_FUNCTIONS . 'functions_osh_update.php',
];
$autoLoadConfig[60][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_general_funcs.php',
];
$autoLoadConfig[60][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_tlds.php',
];
/**
 * Breakpoint 70.
 *
 * require 'includes/init_includes/init_sessions.php';
 *
 */
$autoLoadConfig[70][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_sessions.php',
];
/**
 * Breakpoint 90.
 *
 * currencies = new currencies();
 *
 */
//- currencies class loaded via psr4Autoload.php; reloading
//- here for older versions of Zen Cart that don't load the
//- class via an autoloader.
$autoLoadConfig[90][] = [
    'autoType' => 'class',
    'loadFile' => 'currencies.php',
];
$autoLoadConfig[90][] = [
    'autoType' => 'classInstantiate',
    'className' => 'currencies',
    'objectName' => 'currencies',
];
/**
 * Breakpoints 95,96.
 *
 * require 'includes/init_includes/init_languages.php';
 * require 'includes/init_includes/init_sanitize.php';
 *
 */
$autoLoadConfig[95][] = [
    'autoType' => 'class',
    'loadFile' => 'language.php',
];
$autoLoadConfig[95][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_languages.php',
];
$autoLoadConfig[96][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_sanitize.php',
];
/**
 * Breakpoint 100.
 *
 */
//- template_func class loaded via psr4Autoload.php; reloading
//- here for older versions of Zen Cart that don't load the
//- class via an autoloader.
$autoLoadConfig[100][] = [
    'autoType' => 'class',
    'loadFile' => 'template_func.php',
];
$autoLoadConfig[100][] = [
    'autoType' => 'classInstantiate',
    'className' => 'template_func',
    'objectName' => 'template',
];
/**
 * Breakpoint 110.
 *
 */
$autoLoadConfig[110][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_templates.php',
];
/**
 * Breakpoint 120.
 *
 */
$autoLoadConfig[120][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_currencies.php',
];
/**
 * Breakpoint 130.
 *
 * messageStack = new messageStack();
 *
 */
//- messageStack class loaded via psr4Autoload.php; reloading
//- here for older versions of Zen Cart that don't load the
//- class via an autoloader.
$autoLoadConfig[130][] = [
    'autoType' => 'class',
    'loadFile' => 'message_stack.php',
];
$autoLoadConfig[130][] = [
    'autoType' => 'classInstantiate',
    'className' => 'messageStack',
    'objectName' => 'messageStack',
];
/**
 * Breakpoint 175.
 *
 * require 'includes/init_includes/init_observers.php';
 *
 */
$autoLoadConfig[175][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_observers.php',
];
