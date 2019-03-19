<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */

namespace Zencart\CheckoutFlow;

use ZenCart\Request\Request;

/**
 * Interface FlowStepFactoryInterface
 * @package Zencart\CheckoutFlow
 */
interface FlowStepFactoryInterface
{
    /**
     * @param $step
     * @param CheckoutManager $manager
     * @param Request $request
     * @param $dbConn
     * @return mixed
     */
    public function getFlowStep($step, CheckoutManager $manager, Request $request, $dbConn, $view);
}
