<?php

use Carbon\Carbon;
use Zencart\ModuleSupport\PaymentModuleAbstract;
use Zencart\ModuleSupport\PaymentModuleContract;
use Zencart\ModuleSupport\PaymentModuleConcerns;

class moneyorder extends PaymentModuleAbstract implements PaymentModuleContract
{
    use PaymentModuleConcerns;

    public string $email_footer = "";
    public string $version = '1.0.0';
    public string $code = 'moneyorder';

    public function __construct()
    {
        parent::__construct();
        $this->email_footer = $this->getDefine('MODULE_PAYMENT_%%_TEXT_EMAIL_FOOTER', '');
    }

    protected function checkNonFatalConfigureStatus(): void
    {
        if ($this->getDefine('MODULE_PAYMENT_%%_PAYTO') == 'the Store Owner/Website Name' || $this->getDefine('MODULE_PAYMENT_%%_PAYTO') == '') {
            $this->configureErrors[] = '(not configured - needs pay-to)';
        }
    }
    protected function addCustomConfigurationKeys(): array
    {
        $configKeys = [];
        $key = $this->buildDefine('MODULE_PAYMENT_%%_PAYTO');
        $configKeys[$key] = [
            'configuration_value' => 'the Store Owner/Website Name',
            'configuration_title' => 'Make Payable to:',
            'configuration_description' => 'Who should payments be made payable to?',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'date_added' => Carbon::now(),
        ];
        return $configKeys;
    }
}
