<?php
/**
 * Moneyorder Payment Module
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version
 */


use Zencart\ModuleSupport\PaymentBase;
use Zencart\ModuleSupport\PaymentContract;
use Zencart\ModuleSupport\PaymentConcerns;

class moneyorder extends PaymentBase implements PaymentContract
{
    use PaymentConcerns;

    public string $version = '1.0.0';
    public string $code = 'moneyorder';
    public string $defineName = 'MONEYORDER';

    protected function checkNonFatalConfigureStatus(): void
    {
        if ($this->getDefine('PAYTO') == 'the Store Owner/Website Name' || $this->getDefine('PAYTO') == '') {
            $this->configureErrors[] = '(not configured - needs pay-to)';
        }
    }
    protected function addCustomConfigurationKeys(): array
    {
        $configKeys = [];
        $key = $this->buildDefine('PAYTO');
        $configKeys[$key] = [
            'configuration_value' => 'the Store Owner/Website Name',
            'configuration_title' => 'Make Payable to:',
            'configuration_description' => 'Who should payments be made payable to?',
            'configuration_group_id' => 6,
            'sort_order' => 1,
        ];
        return $configKeys;
    }
}
