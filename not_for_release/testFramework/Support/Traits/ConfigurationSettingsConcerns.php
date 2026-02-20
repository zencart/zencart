<?php

namespace Tests\Support\Traits;

use Tests\Models\Configuration;
use Tests\Models\TaxClass;

trait ConfigurationSettingsConcerns
{

    protected static bool $compoundDone = false;

    public function setConfiguration($configKey, $configValue)
    {
        $config = Configuration::where('configuration_key', $configKey)->first();
        $config->configuration_value = $configValue;
        $config->save();
    }

    public function getConfigurationSetting($configKey)
    {
        return (string)Configuration::select('configuration_value')->where('configuration_key', $configKey)->first();
    }

    public function switchToTaxInclusive()
    {
        $this->setConfiguration('DISPLAY_PRICE_WITH_TAX', 'true');
    }
    public function switchToTaxNonInclusive()
    {
        $this->setConfiguration('DISPLAY_PRICE_WITH_TAX', 'false');
    }

    public function switchItemShippingTax($mode = 'on')
    {
        $taxClass = TaxClass::where('tax_class_title', 'Taxable Shipping')->first();
        $this->setConfiguration('MODULE_SHIPPING_ITEM_TAX_CLASS', $mode == 'on' ? $taxClass->tax_class_id : '0');
    }

    public function switchFlatShippingTax($mode = 'on')
    {
        $this->setConfiguration('MODULE_SHIPPING_FLAT_TAX_CLASS', $mode == 'on' ? '2' : '0');
    }

    public function switchSplitTaxMode($mode = 'on')
    {
        $this->setConfiguration('SHOW_SPLIT_TAX_CHECKOUT', $mode == 'on' ? 'true' : 'false');
    }

    public function displaySignificantSettings()
    {
        $settings = ['DISPLAY_PRICE_WITH_TAX', 'MODULE_SHIPPING_ITEM_TAX_CLASS', 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE'];
        foreach ($settings as $item) {
            var_dump($item, $this->getConfigurationSetting($item));
        }

    }
}
