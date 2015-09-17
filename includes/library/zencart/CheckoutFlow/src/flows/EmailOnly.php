<?php
/**
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version  $Id: New in v1.6.0 $
 */
namespace ZenCart\CheckoutFlow\flows;

/**
 * Class Standard
 * @package Zencart\CheckoutFlow\flows
 */
class EmailOnly extends AbstractFlow
{

    /**
     *
     */
    protected function setInitialStepsList()
    {
        $this->stepsList = array('confirmation', 'process' => array('type' => 'hidden'), 'finished');
        $this->notify('NOTIFY_CHECKOUTFLOW_SET_INITIAL_STEPSLIST', array());
    }

    /**
     * @return string
     */
    public function getInitialStep()
    {
        $step = 'process';
        $this->notify('NOTIFY_CHECKOUTFLOW_GET_INITIAL_STEP', array(), $step);
        return $step;
    }
}
