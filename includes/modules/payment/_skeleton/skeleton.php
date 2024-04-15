<?php

namespace _skeleton;

use Carbon\Carbon;
use Zencart\ModuleSupport\PaymentModuleAbstract;
use Zencart\ModuleSupport\PaymentModuleContract;
use Zencart\ModuleSupport\PaymentModuleConcerns;

class skeleton extends PaymentModuleAbstract implements PaymentModuleContract
{
    use PaymentModuleConcerns;

    protected const CURRENT_VERSION = '1.0.0-alpha';
    public string $MODULE_ID = 'SKELETON';
    public string $code = 'skeleton';

}
