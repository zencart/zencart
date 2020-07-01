<?php

class zcNotifierBaseAliasTestObject extends base
{
    public function fireNotifierValid()
    {
        $this->foo = 'valid';
        $this->notify('NOTIFY_ORDER_CART_SUBTOTAL_CALCULATE');
        return $this->foo;
    }
    public function fireNotifierInvalid()
    {
        $this->foo = 'invalid';
        $this->notify('NOTIFIYFOO_ORDER_CART_SUBTOTAL_CALCULATE');
        return $this->foo;
    }
}
