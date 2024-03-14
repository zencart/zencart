<?php

namespace Tests\Support\Traits;

trait LowOrderFeeConcerns
{
    public function switchLowOrderFee($mode)
    {
        $this->setConfiguration('MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE', $mode == 'on' ? 'true' : 'false');
        $this->setConfiguration('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', $mode == 'on' ? 'true' : 'false');
    }
}
