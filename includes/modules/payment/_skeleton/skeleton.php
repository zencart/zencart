<?php

namespace _skeleton;

use Carbon\Carbon;
use Zencart\ModuleSupport\PaymentModuleAbstract;
use Zencart\ModuleSupport\PaymentContract;
use Zencart\ModuleSupport\PaymentConcerns;

class skeleton extends PaymentModuleAbstract implements PaymentContract
{
    use PaymentConcerns;

    protected const CURRENT_VERSION = '1.0.0-alpha';
    public string $MODULE_ID = 'SKELETON';
    public string $code = 'skeleton';

}
