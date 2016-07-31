<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */

namespace ZenCart\CheckoutFlow;

use ZenCart\Request\Request;
use ZenCart\View\StoreView as View;

/**
 * Class FlowFactory
 * @package ZenCart\CheckoutFlow
 */
class FlowFactory implements FlowFactoryInterface
{
    /**
     * @param $flow
     * @param CheckoutManager $manager
     * @param Request $request
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getFlow($flow, CheckoutManager $manager, Request $request, View $view)
    {
        $fileName = DIR_CATALOG_LIBRARY . 'zencart/CheckoutFlow/src/flows/' . ucfirst($flow) . '.php';
        if (!file_exists($fileName)) {
            throw new InvalidArgumentException();
        }
        $flowClass = '\\ZenCart\\CheckoutFlow\\flows\\' . ucfirst($flow);
        $flowObject = new $flowClass($manager, $request, $view);
        return $flowObject;
    }
}
