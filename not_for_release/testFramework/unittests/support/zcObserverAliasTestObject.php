<?php

class zcObserverAliasTestObject extends base
{
    function __construct()
    {
        $this->attach($this, array('NOTIFIY_ORDER_CART_SUBTOTAL_CALCULATE'));
    }

    public function updateNotifiyOrderCartSubtotalCalculate(&$class, $eventID, $paramsArray = array())
    {
        $class->foo = 'foobar';
    }

    public function fireNotifierValid()
    {
        $this->foo = 'bar';
        $this->notify('NOTIFY_ORDER_CART_SUBTOTAL_CALCULATE');
        return $this->foo;
    }
    public function fireNotifierInvalid()
    {
        $this->foo = 'bar';
        $this->notify('NOTIFIYFOO_ORDER_CART_SUBTOTAL_CALCULATE');
        return $this->foo;
    }
}