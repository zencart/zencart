<?php

namespace Tests\Support;
use base;

class zcObserverAliasTestObject extends base
{
    function __construct()
    {
        $this->attach($this, array('NOTIFY_DOWNLOAD_READY_TO_STREAM'));
        $this->attach($this, array('NOTIFIY_ORDER_CART_SUBTOTAL_CALCULATE'));
    }

    public function updateNotifiyOrderCartSubtotalCalculate(&$class, $eventID, $paramsArray = array())
    {
        $class->foo = $eventID;
    }
}
