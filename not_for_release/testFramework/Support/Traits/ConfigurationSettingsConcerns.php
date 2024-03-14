<?php

namespace Tests\Support\Traits;

use App\Models\Configuration;

trait ConfigurationSettingsConcerns
{

    protected static bool $compoundDone = false;

    public function setConfiguration($configKey, $configValue)
    {
        $config = Configuration::where('configuration_key', $configKey)->first();
        $config->configuration_value = $configValue;
        $config->save();
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
        $this->setConfiguration('MODULE_SHIPPING_ITEM_TAX_CLASS', $mode == 'on' ? '2' : '0');
    }

    public function switchFlatShippingTax($mode = 'on')
    {
        $this->setConfiguration('MODULE_SHIPPING_FLAT_TAX_CLASS', $mode == 'on' ? '2' : '0');
    }

    public function switchSplitTaxMode($mode = 'on')
    {
        $this->setConfiguration('SHOW_SPLIT_TAX_CHECKOUT', $mode == 'on' ? 'true' : 'false');
    }
}
