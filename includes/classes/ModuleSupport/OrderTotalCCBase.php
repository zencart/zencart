<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\ModuleSupport;

abstract class OrderTotalCCBase extends OrderTotalBase
{
    public bool $credit_class = true;
    public string $calculate_tax;
    public string|null $include_shipping;
    public string $include_tax;
    public string $tax_class;
    public float|null $deduction;

    public function __construct()
    {
        parent::__construct();
        $this->include_shipping = $this->getDefine('INC_SHIPPING');
        $this->include_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX;
        $this->calculate_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX;
        $this->tax_class = MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS;
    
    }
    protected function setCommonConfigurationKeys(): array
    {
        $configKeys = parent::setCommonConfigurationKeys();
        $key = $this->buildDefine('INC_SHIPPIMG');
        $configKeys[$key] = [
            'configuration_value' => 'false',
            'configuration_title' => 'Include Shipping',
            'configuration_description' => 'Include Shipping value in amount before discount calculation?',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'set_function' => 'zen_cfg_select_option(array(\'true\', \'false\'), ',
        ];
        $key = $this->buildDefine('INC_TAX');
        $configKeys[$key] = [
            'configuration_value' => 'true',
            'configuration_title' => 'Include Tax',
            'configuration_description' => 'Include Tax value in amount before discount calculation?',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'set_function' => 'zen_cfg_select_option(array(\'true\', \'false\'), ',
        ];
        $key = $this->buildDefine('CALC_TAX');
        $configKeys[$key] = [
            'configuration_value' => 'Standard',
            'configuration_title' => 'Re-calculate Tax',
            'configuration_description' => 'How to Re-calculate Tax',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'set_function' => 'zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ',
        ];
        $key = $this->buildDefine('TAX_CLASS');
        $configKeys[$key] = [
            'configuration_value' => '0',
            'configuration_title' => 'Tax Class',
            'configuration_description' => 'Use the following tax class when treating Group Discount as Credit Note.',
            'configuration_group_id' => 6,
            'sort_order' => 1,
            'use_function' => 'zen_get_tax_class_title',
            'set_function' => 'zen_cfg_pull_down_tax_classes(',
        ];
        return $configKeys;
    }
}
