<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */

namespace Zencart\CheckoutFlow;

use ZenCart\Request\Request;
use ZenCart\View\StoreView as View;

/**
 * Interface FlowFactoryInterface
 * @package Zencart\CheckoutFlow
 */
interface FlowFactoryInterface
{
    /**
     * @param $flow
     * @param CheckoutManager $manager
     * @param Request $request
     * @param View $view
     * @return mixed
     */
    public function getFlow($flow, CheckoutManager $manager, Request $request, View $view);
}
