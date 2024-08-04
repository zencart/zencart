<?php

/**
 * autoloader array for paypal
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Jun 18 Modified in v2.1.0-alpha1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$autoLoadConfig[0][] = [
    'autoType' => 'include',
    'loadFile' => DIR_WS_INCLUDES . 'version.php',
];
//- notifier class loaded via psr4Autoload.php
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
 * Breakpoint 5.
 *
 * $zcDate = new zcDate(); ... will be re-initialized when/if the require_languages.php module is run.
 *
 */
//- zcDate class loaded via psr4Autoload.php
$autoLoadConfig[5][] = [
    'autoType' => 'classInstantiate',
    'className' => 'zcDate',
    'objectName' => 'zcDate',
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
 * require('includes/init_includes/init_db_config_read.php');
 *
 */
$autoLoadConfig[40][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_db_config_read.php',
];
/**
 * Breakpoint 50.
 *
 * $sniffer = new sniffer();
 * require('includes/init_includes/init_sefu.php');
 */
//- sniffer class loaded via psr4Autoload.php
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
 * require('includes/init_includes/init_general_funcs.php');
 * require('includes/init_includes/init_tlds.php');
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
 * Include PayPal-specific functions
 * require('includes/modules/payment/paypal/paypal_functions.php');
 */
$autoLoadConfig[60][] = [
    'autoType' => 'include',
    'loadFile' => DIR_WS_MODULES . 'payment/paypal/paypal_functions.php',
];
/**
 * Breakpoint 70.
 *
 * require('includes/init_includes/init_sessions.php');
 *
 */
$autoLoadConfig[70][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_sessions.php',
];
$autoLoadConfig[71][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_paypal_ipn_sessions.php',
];
/**
 * Breakpoint 80.
 *
 * if(!$_SESSION['cart']) $_SESSION['cart'] = new shoppingCart();
 *
 */
//- shoppingCart class loaded via psr4Autoload.php
$autoLoadConfig[80][] = [
    'autoType' => 'classInstantiate',
    'className' => 'shoppingCart',
    'objectName' => 'cart',
    'checkInstantiated' => true,
    'classSession' => true,
];
/**
 * Breakpoint 90.
 *
 * currencies = new currencies();
 *
 */
//- currencies class loaded via psr4Autoload.php
$autoLoadConfig[90][] = [
    'autoType' => 'classInstantiate',
    'className' => 'currencies',
    'objectName' => 'currencies',
];
/**
 * Breakpoint 100.
 *
 * require('includes/init_includes/init_sanitize.php');
 * $template = new template_func();
 *
 */
//- template_func class loaded via psr4Autoload.php
$autoLoadConfig[100][] = [
    'autoType' => 'classInstantiate',
    'className' => 'template_func',
    'objectName' => 'template',
];
$autoLoadConfig[100][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_sanitize.php',
];
/**
 * Breakpoint 110.
 *
 * require('includes/init_includes/init_languages.php');
 * require('includes/init_includes/init_templates.php');
 *
 */
$autoLoadConfig[110][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_languages.php',
];
$autoLoadConfig[110][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_templates.php',
];
/**
 * Breakpoint 120.
 *
 * require('includes/init_includes/init_currencies.php');
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
//- messageStack class loaded via psr4Autoload.php
$autoLoadConfig[130][] = [
    'autoType' => 'classInstantiate',
    'className' => 'messageStack',
    'objectName' => 'messageStack',
];
/**
 * Breakpoint 170.
 *
 * require('includes/languages/english/checkout_process.php');
 *
 */
$autoLoadConfig[170][] = [
    'autoType' => 'init_script',
    'loadFile' => 'init_ipn_postcfg.php',
];
