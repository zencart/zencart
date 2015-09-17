<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
$routeMap = array(
    FILENAME_CHECKOUT_SHIPPING => 'shipping',
    FILENAME_CHECKOUT_PAYMENT => 'payment',
    FILENAME_CHECKOUT_CONFIRMATION => 'confirmation',
    FILENAME_CHECKOUT_PROCESS => 'process',
);
$mainPage = $zcRequest->readGet('main_page');
if (array_key_exists($mainPage, $routeMap)) {
    $zcRequest->set('main_page', FILENAME_CHECKOUT_FLOW);
    $zcRequest->set('step', $routeMap[$mainPage]);
}
