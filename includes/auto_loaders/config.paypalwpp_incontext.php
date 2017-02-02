<?php
/**
 * Autoloader array for PayPal Express In Context functionality. Makes sure that PayPalWPP In Context is instantiated at the
 * right point of the Zen Cart initsystem.
 * 
 * @package     paypal_incontext
 * @copyright   Copyright 2003-2016 Zen Cart Development Team
 * @copyright   Portions Copyright 2003 osCommerce
 * @copyright   Portions Copyright 2012-2016 mc12345678
 * @license     http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version     $Id: config.paypalwpp_incontext.php xxxx 2016-08-20 11:15:10Z mc12345678 $
 */

    $autoLoadConfig[0][] = array(
        'autoType' => 'class',
        'loadFile' => 'observers/class.paypalwpp_incontext.php'
    );
    $autoLoadConfig[199][] = array(
        'autoType' => 'classInstantiate',
        'className' => 'paypalwpp_incontext',
        'objectName' => 'paypalwpp_ic'
    ); 
