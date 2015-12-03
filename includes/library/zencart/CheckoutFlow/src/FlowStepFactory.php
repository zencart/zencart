<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */

namespace ZenCart\CheckoutFlow;

use ZenCart\Request\Request;

/**
 * Class FlowStepFactory
 * @package ZenCart\CheckoutFlow
 */
class FlowStepFactory implements FlowStepFactoryInterface
{
    /**
     * @param $step
     * @param CheckoutManager $manager
     * @param Request $request
     * @param $dbConn
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getFlowStep($step, CheckoutManager $manager, Request $request, $dbConn, $view)
    {
        $fileName = DIR_CATALOG_LIBRARY . 'zencart/CheckoutFlow/src/flowSteps/' . ucfirst($step) . '.php';
        if (!file_exists($fileName)) {
            throw new InvalidArgumentException();
        }
        $flowStepClass = '\\ZenCart\\CheckoutFlow\\flowSteps\\' . ucfirst($step);
        $flowStepObject = new $flowStepClass($manager, $request, $dbConn, $view);
        return $flowStepObject;
    }
}
