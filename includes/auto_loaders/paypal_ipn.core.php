<?php
/**
 * autoloader array for paypal
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2019 Dec 15 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
 die('Illegal Access');
}

  $autoLoadConfig[0][] = array('autoType'=>'include',
                               'loadFile'=> DIR_WS_INCLUDES . 'version.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                               'loadFile'=>'class.notifier.php');
  $autoLoadConfig[0][] = array('autoType'=>'classInstantiate',
                               'className'=>'notifier',
                               'objectName'=>'zco_notifier');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                                'loadFile'=> 'class.phpmailer.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                                'loadFile'=>'template_func.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                                'loadFile'=>'language.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                                'loadFile'=>'sniffer.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                                'loadFile'=>'shopping_cart.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                                'loadFile'=>'navigation_history.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                                'loadFile'=>'currencies.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                                'loadFile'=>'message_stack.php');
  $autoLoadConfig[0][] = array('autoType'=>'class',
                                'loadFile'=>'breadcrumb.php');
/**
 * Breakpoint 30.
 *
 * $zc_cache = new cache();
 *
 */
  $autoLoadConfig[30][] = array('autoType'=>'classInstantiate',
                                'className'=>'cache',
                                'objectName'=>'zc_cache');
/**
 * Breakpoint 40.
 *
 * require('includes/init_includes/init_db_config_read.php');
 *
 */
  $autoLoadConfig[40][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_db_config_read.php');
/**
 * Breakpoint 50.
 *
 * $sniffer = new sniffer();
 * require('includes/init_includes/init_sefu.php');
 */
  $autoLoadConfig[50][] = array('autoType'=>'classInstantiate',
                                'className'=>'sniffer',
                                'objectName'=>'sniffer');

  $autoLoadConfig[50][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_sefu.php');
/**
 * Breakpoint 60.
 *
 * require('includes/init_includes/init_general_funcs.php');
 * require('includes/init_includes/init_tlds.php');
 *
 */
  $autoLoadConfig[60][] = array('autoType' => 'require',
                                'loadFile' => DIR_WS_FUNCTIONS . 'functions_osh_update.php');
  $autoLoadConfig[60][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_general_funcs.php');
  $autoLoadConfig[60][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_tlds.php');
/**
 * Include PayPal-specific functions
 * require('includes/modules/payment/paypal/paypal_functions.php');
 */

  $autoLoadConfig[60][] = array('autoType'=>'include',
                                'loadFile'=> DIR_WS_MODULES . 'payment/paypal/paypal_functions.php');

/**
 * Breakpoint 70.
 *
 * require('includes/init_includes/init_sessions.php');
 *
 */
  $autoLoadConfig[70][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_sessions.php');
  $autoLoadConfig[71][] = array('autoType'=>'init_script',
                                'loadFile'=> 'init_paypal_ipn_sessions.php');
/**
 * Breakpoint 80.
 *
 * if(!$_SESSION['cart']) $_SESSION['cart'] = new shoppingCart();
 *
 */
  $autoLoadConfig[80][] = array('autoType'=>'classInstantiate',
                                'className'=>'shoppingCart',
                                'objectName'=>'cart',
                                'checkInstantiated'=>true,
                                'classSession'=>true);
/**
 * Breakpoint 90.
 *
 * currencies = new currencies();
 *
 */
  $autoLoadConfig[90][] = array('autoType'=>'classInstantiate',
                                'className'=>'currencies',
                                'objectName'=>'currencies');
/**
 * Breakpoint 100.
 *
 * require('includes/init_includes/init_sanitize.php');
 * $template = new template_func();
 *
 */
  $autoLoadConfig[100][] = array('autoType'=>'classInstantiate',
                                 'className'=>'template_func',
                                 'objectName'=>'template');
  $autoLoadConfig[100][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_sanitize.php');
/**
 * Breakpoint 110.
 *
 * require('includes/init_includes/init_languages.php');
 * require('includes/init_includes/init_templates.php');
 *
 */
  $autoLoadConfig[110][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_languages.php');
  $autoLoadConfig[110][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_templates.php');
/**
 * Breakpoint 120.
 *
 * require('includes/init_includes/init_currencies.php');
 *
 */
  $autoLoadConfig[120][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_currencies.php');
/**
 * Breakpoint 130.
 *
 * messageStack = new messageStack();
 *
 */
  $autoLoadConfig[130][] = array('autoType'=>'classInstantiate',
                                 'className'=>'messageStack',
                                 'objectName'=>'messageStack');
/**
 * Breakpoint 170.
 *
 * require('includes/languages/english/checkout_process.php');
 *
 */
  $autoLoadConfig[170][] = array('autoType'=>'init_script',
                                 'loadFile'=> 'init_ipn_postcfg.php');
