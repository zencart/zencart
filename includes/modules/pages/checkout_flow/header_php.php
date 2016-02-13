<?php
/**
 * Main Checkout Flow Page
 *
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0  $
 */

use ZenCart\CheckoutFlow\CheckoutManager as CheckoutManager;
use ZenCart\CheckoutFlow\FlowFactory as FlowFactory;
use ZenCart\CheckoutFlow\FlowStepFactory as FlowStepFactory;

require(DIR_WS_MODULES . 'require_languages.php');

$zcCheckoutManager = new CheckoutManager($zcRequest, $zcView, $db, new FlowFactory, new FlowStepFactory);

if ($zcCheckoutManager->getRedirectNeeded() == true )
{
    $redirectDestination = $zcCheckoutManager->getRedirectDestination();
    if (isset($redirectDestination['redirect']))
    {
        zen_redirect($redirectDestination['redirect']);
    }
}
